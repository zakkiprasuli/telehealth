<?php
$judulHalaman = 'Beranda';
require __DIR__ . '/includes/header.php';

$me    = api('/me?nim=' . urlencode(mhs()['nim']));
$forms = api('/forms');
?>

<?php if (!$me['success']): ?>
    <div class="alert alert-danger"><?= e($me['message'] ?? 'Terjadi kesalahan') ?></div>
<?php else: $p = $me['data']; $s = $p['statistik']; ?>

    <h4>Halo, <?= e($p['nama']) ?></h4>
    <p class="text-muted">No. RM: <?= e($p['no_rm']) ?> · NIM: <?= e(mhs()['nim']) ?></p>

    <div class="row g-3 mb-4">
        <?php foreach ([
            'Total' => $s['total'], 'Draf' => $s['draf'], 'Menunggu' => $s['menunggu'],
            'Perlu klarifikasi' => $s['perlu_klarifikasi'], 'Sudah ditinjau' => $s['sudah_ditinjau'],
        ] as $label => $jumlah): ?>
            <div class="col-6 col-md">
                <div class="card text-center"><div class="card-body">
                    <div class="fs-3"><?= e($jumlah) ?></div>
                    <div class="small text-muted"><?= e($label) ?></div>
                </div></div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<h5>Pilih Formulir</h5>
<?php if (!$forms['success']): ?>
    <div class="alert alert-danger"><?= e($forms['message'] ?? 'Gagal memuat formulir') ?></div>
<?php else: foreach ($forms['data'] as $f): ?>
    <div class="card mb-3"><div class="card-body d-flex justify-content-between align-items-center">
        <div>
            <strong><?= e($f['judul'] ?? $f['id']) ?></strong><br>
            <small class="text-muted"><?= e($f['id']) ?></small>
        </div>
        <a class="btn btn-primary" href="petunjuk.php?id=<?= urlencode($f['id']) ?>">Mulai</a>
    </div></div>
<?php endforeach; endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>