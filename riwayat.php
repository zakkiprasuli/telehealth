<?php
$judulHalaman = 'Riwayat';
require __DIR__ . '/includes/header.php';

// Hapus draf (laporan terkirim tidak bisa dihapus)
$pesan = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['hapus'])) {
    $del   = api('/assessments/' . urlencode($_POST['hapus']), 'DELETE');
    $pesan = $del['success'] ? 'Draf dihapus.' : ($del['message'] ?? 'Gagal menghapus.');
}

$res = api('/assessments?nim=' . urlencode(mhs()['nim']));
?>

<h4>Riwayat Pengisian</h4>
<?php if ($pesan): ?><div class="alert alert-info"><?= e($pesan) ?></div><?php endif; ?>

<?php if (!$res['success']): ?>
    <div class="alert alert-danger"><?= e($res['message'] ?? 'Gagal memuat riwayat.') ?></div>
<?php elseif (!$res['data']): ?>
    <div class="alert alert-secondary">Belum ada pengisian. <a href="index.php">Mulai dari Beranda</a>.</div>
<?php else: foreach ($res['data'] as $a):
    $draft = ($a['status_pengiriman']['kode'] ?? '') === 'draft'; ?>
    <div class="card mb-3"><div class="card-body">
        <div class="d-flex justify-content-between flex-wrap gap-2">
            <div>
                <strong><?= e($a['judul'] ?: ($a['form']['judul'] ?? $a['id'])) ?></strong>
                <div class="small text-muted"><?= e($a['id']) ?> · <?= e($a['form']['judul'] ?? '') ?></div>
                <div class="small text-muted">Dibuat <?= e(tgl($a['dibuat_pada'] ?? null)) ?></div>
            </div>
            <div class="text-end">
                <?= badge($a['status_pengiriman'] ?? null) ?>
                <?= badge($a['status_peninjauan'] ?? null) ?>
                <?php if (!empty($a['klarifikasi_menunggu'])): ?>
                    <div class="small text-danger mt-1"><?= (int) $a['klarifikasi_menunggu'] ?> klarifikasi menunggu jawaban</div>
                <?php endif; ?>
            </div>
        </div>
        <div class="mt-3 d-flex gap-2">
            <?php if ($draft): ?>
                <a class="btn btn-sm btn-primary" href="isi.php?id=<?= urlencode($a['id']) ?>">Lanjutkan</a>
                <form method="post" onsubmit="return confirm('Hapus draf ini?')">
                    <input type="hidden" name="hapus" value="<?= e($a['id']) ?>">
                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                </form>
            <?php else: ?>
                <a class="btn btn-sm btn-primary" href="status.php?id=<?= urlencode($a['id']) ?>">Lihat status</a>
                <a class="btn btn-sm btn-outline-primary" href="hasil.php?id=<?= urlencode($a['id']) ?>">Lihat hasil</a>
            <?php endif; ?>
        </div>
    </div></div>
<?php endforeach; endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>