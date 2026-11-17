<?php
$judulHalaman = 'Isi Formulir';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/form_helper.php';

function berhenti(string $pesan): void
{
    echo '<div class="alert alert-danger">' . e($pesan) . '</div>';
    echo '<a href="index.php" class="btn btn-secondary">Beranda</a>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$id = $_GET['id'] ?? '';
if ($id === '') berhenti('ID pengisian tidak ada.');

$sa = api('/assessments/' . urlencode($id));
if (!$sa['success']) berhenti($sa['message'] ?? 'Gagal memuat pengisian.');
$asesmen = $sa['data'];

// Yang sudah dikirim tidak boleh diubah (API membalas 409)
if (($asesmen['status_pengiriman']['kode'] ?? '') !== 'draft') {
    header('Location: status.php?id=' . urlencode($id));
    exit;
}

$formId  = $asesmen['form']['id'] ?? ($asesmen['form_id'] ?? '');
$formRes = api('/forms/' . urlencode($formId));
if (!$formRes['success']) berhenti($formRes['message'] ?? 'Gagal memuat formulir.');

$form   = $formRes['data'];
$bagian = $form['sections'] ?? [];
if (!$bagian) berhenti('Formulir tidak memiliki bagian.');

$jawabanLama = is_array($asesmen['answers'] ?? null) ? $asesmen['answers'] : [];

// Bagian yang sedang dibuka (lewati bagian yang disembunyikan)
$b = max(0, min((int) ($_GET['b'] ?? 0), count($bagian) - 1));
if (!terlihat($bagian[$b]['show_if'] ?? null, $jawabanLama)) {
    $b = cariBagian($b, 1, $bagian, $jawabanLama) ?? cariBagian($b, -1, $bagian, $jawabanLama) ?? 0;
}
$pertanyaanBagian = $bagian[$b]['questions'] ?? [];

$jawaban = $jawabanLama;
$errors  = [];
$flash   = isset($_GET['ok']) ? 'Draf tersimpan.' : null;
$gagal   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? 'simpan';
    $post = $_POST['jawab'] ?? [];

    // 1) tampung jawaban baru, lalu hitung ulang visibilitas
    $baru   = [];
    $merged = $jawabanLama;
    foreach ($pertanyaanBagian as $q) {
        $baru[$q['id']] = bersihkan($q, $post[$q['id']] ?? null);
        if ($baru[$q['id']] === null) unset($merged[$q['id']]);
        else $merged[$q['id']] = $baru[$q['id']];
    }

    // 2) pertanyaan tersembunyi dikosongkan (null)
    $payload = [];
    foreach ($pertanyaanBagian as $q) {
        $payload[$q['id']] = terlihat($q['show_if'] ?? null, $merged) ? $baru[$q['id']] : null;
        if ($payload[$q['id']] === null) unset($merged[$q['id']]);
    }

    // 3) simpan draf (PATCH digabung dengan jawaban lama di server)
    $simpan = api('/assessments/' . urlencode($id), 'PATCH', ['answers' => $payload]);
    $jawaban = $merged;

    if (!$simpan['success']) {
        $gagal = $simpan['message'] ?? 'Gagal menyimpan draf.';
    } elseif ($aksi === 'prev') {
        $p = cariBagian($b - 1, -1, $bagian, $merged);
        header('Location: isi.php?id=' . urlencode($id) . '&b=' . ($p ?? $b));
        exit;
    } elseif ($aksi === 'simpan') {
        header('Location: isi.php?id=' . urlencode($id) . '&b=' . $b . '&ok=1');
        exit;
    } else { // lanjut: cek validasi bagian ini lewat endpoint validate
        $val = api('/forms/' . urlencode($formId) . '/validate', 'POST', [
            'nim'     => mhs()['nim'],
            'answers' => $merged ?: new stdClass(),
        ]);
        $semua = $val['data']['errors'] ?? [];
        foreach ($pertanyaanBagian as $q) {
            if (isset($semua[$q['id']]) && terlihat($q['show_if'] ?? null, $merged)) {
                $errors[$q['id']] = $semua[$q['id']];
            }
        }
        if (!$errors) {
            $n = cariBagian($b + 1, 1, $bagian, $merged);
            header('Location: ' . ($n === null
                ? 'periksa.php?id=' . urlencode($id)
                : 'isi.php?id=' . urlencode($id) . '&b=' . $n));
            exit;
        }
    }
}

// Info progres
$tampil = [];
foreach ($bagian as $i => $s) {
    if (terlihat($s['show_if'] ?? null, $jawaban)) $tampil[] = $i;
}
$posisi    = array_search($b, $tampil, true);
$posisi    = $posisi === false ? 0 : $posisi + 1;
$adaBerikut = cariBagian($b + 1, 1, $bagian, $jawaban) !== null;
$adaSebelum = cariBagian($b - 1, -1, $bagian, $jawaban) !== null;
$persen     = (int) round($posisi / max(1, count($tampil)) * 100);
?>

<h4><?= e($form['judul'] ?? $formId) ?></h4>
<p class="text-muted mb-2">Bagian <?= $posisi ?> dari <?= count($tampil) ?> · Draf <?= e($id) ?></p>
<div class="progress mb-4"><div class="progress-bar" style="width: <?= $persen ?>%"></div></div>

<?php if ($flash): ?><div class="alert alert-success"><?= e($flash) ?></div><?php endif; ?>
<?php if ($gagal): ?><div class="alert alert-danger"><?= e($gagal) ?></div><?php endif; ?>
<?php if ($errors): ?>
    <div class="alert alert-warning">Masih ada jawaban yang perlu diperbaiki. Lihat pesan di bawah pertanyaan.</div>
<?php endif; ?>

<form method="post" id="formIsi" novalidate>
    <div class="card mb-3">
        <div class="card-header fw-semibold">
            <?= e(ambil($bagian[$b], ['title', 'judul', 'nama', 'label'], 'Bagian ' . ($b + 1))) ?>
        </div>
        <div class="card-body">
            <?php foreach ($pertanyaanBagian as $q): ?>
                <?php renderPertanyaan($q, $jawaban[$q['id']] ?? null, $errors[$q['id']] ?? null); ?>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="d-flex gap-2 mb-5">
        <?php if ($adaSebelum): ?>
            <button name="aksi" value="prev" class="btn btn-outline-secondary">Sebelumnya</button>
        <?php endif; ?>
        <button name="aksi" value="simpan" class="btn btn-outline-primary">Simpan draf</button>
        <button name="aksi" value="lanjut" class="btn btn-primary ms-auto">
            <?= $adaBerikut ? 'Simpan & lanjut' : 'Periksa jawaban' ?>
        </button>
    </div>
</form>

<script>
const saved = <?= json_encode($jawaban ?: new stdClass()) ?>;
const form  = document.getElementById('formIsi');

function nilaiForm() {
    const a = { ...saved };
    form.querySelectorAll('.pertanyaan').forEach(w => delete a[w.dataset.qid]);
    for (const [k, v] of new FormData(form).entries()) {
        const m = k.match(/^jawab\[(.+?)\](\[\])?$/);
        if (!m) continue;
        if (m[2]) { (a[m[1]] ||= []).push(v); }
        else if (v !== '') a[m[1]] = v;
    }
    return a;
}

function terlihat(c, a) {
    if (!c) return true;
    if (c.any) return c.any.some(x => terlihat(x, a));
    if (c.all) return c.all.every(x => terlihat(x, a));
    const v = a[c.question];
    const terisi = !(v === undefined || v === null || v === '' || (Array.isArray(v) && !v.length));
    if ('equals' in c)   return String(v) === String(c.equals);
    if ('in' in c)       return c.in.map(String).includes(String(v));
    if ('includes' in c) return Array.isArray(v) && v.map(String).includes(String(c.includes));
    if ('filled' in c)   return terisi === c.filled;
    return true;
}

function perbarui() {
    const a = nilaiForm();
    document.querySelectorAll('.pertanyaan[data-showif]').forEach(w => {
        const tampil = terlihat(JSON.parse(w.dataset.showif), a);
        w.style.display = tampil ? '' : 'none';
        w.querySelectorAll('input,textarea,select').forEach(i => i.disabled = !tampil);
    });
}

form.addEventListener('change', e => {
    const t = e.target;
    if (t.type === 'checkbox' && t.checked) {   // opsi eksklusif ("Tidak ada") tidak boleh digabung
        const grup = form.querySelectorAll('input[name="' + t.name + '"]');
        grup.forEach(i => {
            if (i === t) return;
            if (t.dataset.exclusive === '1' || i.dataset.exclusive === '1') i.checked = false;
        });
    }
    perbarui(); perbarui();   // dua kali supaya pertanyaan berantai ikut terbarui
});
form.addEventListener('input', () => { perbarui(); perbarui(); });
perbarui(); perbarui();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>