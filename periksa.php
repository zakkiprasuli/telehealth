<?php
$judulHalaman = 'Periksa Jawaban';
require __DIR__ . '/includes/header.php';

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

// Yang sudah dikirim tidak bisa diperiksa/diubah lagi
if (($asesmen['status_pengiriman']['kode'] ?? '') !== 'draft') {
    header('Location: konfirmasi.php?id=' . urlencode($id));
    exit;
}

$formId  = $asesmen['form']['id'] ?? ($asesmen['form_id'] ?? '');
$jawaban = is_array($asesmen['answers'] ?? null) ? $asesmen['answers'] : [];

// Kirim formulir
$errorKirim = [];
$pesanKirim = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kirim = api('/assessments/' . urlencode($id) . '/submit', 'POST');
    if ($kirim['success']) {
        header('Location: konfirmasi.php?id=' . urlencode($id));
        exit;
    }
    $pesanKirim = $kirim['message'] ?? 'Formulir belum dapat dikirim.';
    $errorKirim = $kirim['errors'] ?? [];
}

// Periksa jawaban tanpa menyimpan (bukti fitur "validasi")
$val = api('/forms/' . urlencode($formId) . '/validate', 'POST', [
    'nim'     => mhs()['nim'],
    'answers' => $jawaban ?: new stdClass(),
]);
if (!$val['success']) berhenti($val['message'] ?? 'Gagal memeriksa jawaban.');

$data       = $val['data'];
$valid      = !empty($data['valid']);
$errors     = $errorKirim ?: ($data['errors'] ?? []);
$kelengkapan = $data['kelengkapan'] ?? [];
$ringkasan  = $data['hasil_pratinjau']['ringkasan'] ?? [];
$persen     = (int) ($kelengkapan['persen'] ?? 0);
?>

<h4>Periksa Jawaban</h4>
<p class="text-muted">Draf <?= e($id) ?> · pastikan semua jawaban sudah benar sebelum dikirim.</p>

<div class="alert alert-warning">
    <strong>Penting:</strong> hasil self-assessment bukan diagnosis.
</div>

<?php if ($pesanKirim): ?>
    <div class="alert alert-danger"><?= e($pesanKirim) ?></div>
<?php endif; ?>

<div class="mb-3">
    <div class="d-flex justify-content-between small">
        <span>Kelengkapan: <?= e($kelengkapan['label'] ?? '-') ?></span>
        <span><?= $persen ?>%</span>
    </div>
    <div class="progress"><div class="progress-bar <?= $valid ? 'bg-success' : 'bg-warning' ?>"
         style="width: <?= $persen ?>%"></div></div>
</div>

<?php if (!$valid): ?>
    <div class="alert alert-danger">
        <strong>Masih ada jawaban yang perlu diperbaiki:</strong>
        <ul class="mb-0">
            <?php foreach ($errors as $qid => $pesan): ?>
                <li><?= e($pesan) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header fw-semibold">Ringkasan jawaban</div>
    <div class="table-responsive">
    <table class="table mb-0 align-middle">
        <tbody>
        <?php foreach ($ringkasan as $r): ?>
            <tr>
                <td style="width:45%">
                    <?= e($r['pertanyaan'] ?? $r['label'] ?? $r['question_id']) ?>
                    <?php if (!empty($r['wajib'])): ?><span class="text-danger">*</span><?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($r['terisi'])): ?>
                        <?= e($r['jawaban_teks'] ?? $r['jawaban']) ?>
                        <?php if (!empty($r['satuan'])): ?> <?= e($r['satuan']) ?><?php endif; ?>
                    <?php else: ?>
                        <span class="text-muted fst-italic">Belum diisi</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$ringkasan): ?>
            <tr><td class="text-muted">Tidak ada ringkasan untuk ditampilkan.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<form method="post" class="d-flex gap-2 mb-5">
    <a href="isi.php?id=<?= urlencode($id) ?>&b=0" class="btn btn-outline-secondary">Ubah jawaban</a>
    <button type="submit" class="btn btn-success ms-auto" <?= $valid ? '' : 'disabled' ?>
            onclick="return confirm('Setelah dikirim, jawaban tidak dapat diubah. Kirim sekarang?')">
        Kirim formulir
    </button>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>