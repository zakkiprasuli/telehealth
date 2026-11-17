<?php
$judulHalaman = 'Petunjuk Pengisian';
require __DIR__ . '/includes/header.php';

$formId = $_GET['id'] ?? '';
if ($formId === '') {
    header('Location: index.php');
    exit;
}

$res    = api('/forms/' . urlencode($formId));
$error  = null;

// Proses saat tombol Mulai ditekan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $res['success']) {
    if (empty($_POST['persetujuan'])) {
        $error = 'Anda harus mencentang persetujuan terlebih dahulu.';
    } else {
        $draf = api('/assessments', 'POST', [
            'form_id'     => $formId,
            'persetujuan' => true,            // harus boolean true, bukan string
            'mahasiswa'   => mhs(),
            'answers'     => new stdClass(),  // objek kosong -> {} di JSON
        ]);

        if ($draf['success']) {
            header('Location: isi.php?id=' . urlencode($draf['data']['id']));
            exit;
        }
        $error = $draf['message'] ?? 'Gagal membuat draf.';
    }
}

// Petunjuk/persetujuan bisa berupa teks atau array, jadi diratakan dulu
function teks($v): string
{
    if (is_array($v)) {
        return implode("\n", array_map(fn($x) => is_array($x) ? ($x['teks'] ?? json_encode($x)) : $x, $v));
    }
    return (string) $v;
}
?>

<?php if (!$res['success']): ?>
    <div class="alert alert-danger"><?= e($res['message'] ?? 'Formulir tidak ditemukan') ?></div>
    <a href="index.php" class="btn btn-secondary">Kembali</a>
<?php else: $f = $res['data']; ?>

    <h4><?= e($f['judul'] ?? $formId) ?></h4>
    <p class="text-muted"><?= e($f['id'] ?? '') ?> · versi <?= e($f['versi'] ?? '-') ?></p>

    <div class="alert alert-warning">
        <strong>Penting:</strong> hasil self-assessment bukan diagnosis.
    </div>

    <?php if (!empty($f['petunjuk'])): ?>
        <div class="card mb-3">
            <div class="card-header">Petunjuk pengisian</div>
            <div class="card-body"><?= nl2br(e(teks($f['petunjuk']))) ?></div>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="card mb-3"><div class="card-body">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="persetujuan" id="persetujuan" value="1">
                <label class="form-check-label" for="persetujuan">
                    <?= nl2br(e(teks($f['persetujuan'] ?? 'Saya bersedia mengisi formulir ini dengan jujur.'))) ?>
                </label>
            </div>
        </div></div>

        <a href="index.php" class="btn btn-outline-secondary">Kembali</a>
        <button type="submit" class="btn btn-primary">Mulai mengisi</button>
    </form>

<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>