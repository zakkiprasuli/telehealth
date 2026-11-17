<?php
$judulHalaman = 'Formulir Terkirim';
require __DIR__ . '/includes/header.php';

$id = $_GET['id'] ?? '';
$sa = $id !== '' ? api('/assessments/' . urlencode($id)) : ['success' => false];
?>

<?php if (!$sa['success']): ?>
    <div class="alert alert-danger"><?= e($sa['message'] ?? 'Data tidak ditemukan.') ?></div>
    <a href="index.php" class="btn btn-secondary">Beranda</a>
<?php else: $d = $sa['data']; ?>
    <div class="card text-center"><div class="card-body py-5">
        <div class="display-4 text-success">✓</div>
        <h4 class="mt-2">Formulir Anda telah diterima</h4>
        <p class="mb-1">Nomor: <strong><?= e($d['id']) ?></strong></p>
        <p>
            Status:
            <span class="badge text-bg-primary">
                <?= e($d['status_peninjauan']['label'] ?? '-') ?>
            </span>
        </p>
        <?php if (!empty($d['pesan_status'])): ?>
            <p class="text-muted"><?= e($d['pesan_status']) ?></p>
        <?php endif; ?>
        <div class="alert alert-warning d-inline-block">
            Hasil self-assessment bukan diagnosis.
        </div>
        <div class="mt-3 d-flex gap-2 justify-content-center">
            <a href="status.php?id=<?= urlencode($d['id']) ?>" class="btn btn-primary">Lihat status</a>
            <a href="riwayat.php" class="btn btn-outline-secondary">Riwayat</a>
            <a href="index.php" class="btn btn-outline-secondary">Beranda</a>
        </div>
    </div></div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>