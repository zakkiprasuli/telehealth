<?php
$judulHalaman = 'Status Peninjauan';
require __DIR__ . '/includes/header.php';

$id = $_GET['id'] ?? '';
if ($id === '') { header('Location: riwayat.php'); exit; }

// Jawab klarifikasi
$flash = null; $gagal = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['cid'])) {
    $jawab = trim($_POST['jawaban'] ?? '');
    if ($jawab === '') {
        $gagal = 'Jawaban klarifikasi tidak boleh kosong.';
    } else {
        $r = api('/assessments/' . urlencode($id) . '/clarifications/' . urlencode($_POST['cid']) . '/reply',
                 'POST', ['jawaban' => $jawab]);
        if ($r['success']) { header('Location: status.php?id=' . urlencode($id) . '&ok=1'); exit; }
        $gagal = $r['message'] ?? 'Gagal mengirim jawaban.';
    }
}
if (isset($_GET['ok'])) $flash = 'Jawaban klarifikasi terkirim.';

$res = api('/assessments/' . urlencode($id) . '/status');
if (!$res['success']) {
    echo '<div class="alert alert-danger">' . e($res['message'] ?? 'Gagal memuat status.') . '</div>';
    require __DIR__ . '/includes/footer.php'; exit;
}
$d  = $res['data'];
$tl = $d['tindak_lanjut'] ?? null;
?>

<h4>Status <?= e($id) ?></h4>

<?php if ($flash): ?><div class="alert alert-success"><?= e($flash) ?></div><?php endif; ?>
<?php if ($gagal): ?><div class="alert alert-danger"><?= e($gagal) ?></div><?php endif; ?>

<div class="card mb-3"><div class="card-body">
    <div class="row">
        <div class="col-md-6"><div class="small text-muted">Status pengiriman</div><?= badge($d['status_pengiriman'] ?? null) ?></div>
        <div class="col-md-6"><div class="small text-muted">Status peninjauan</div><?= badge($d['status_peninjauan'] ?? null) ?></div>
    </div>
    <?php if (!empty($d['pesan'])): ?><p class="mt-3 mb-0"><?= e($d['pesan']) ?></p><?php endif; ?>
</div></div>

<?php if (!empty($d['klarifikasi_menunggu'])): ?>
    <div class="card border-danger mb-3">
        <div class="card-header text-bg-danger">Pertanyaan klarifikasi dari petugas</div>
        <div class="card-body">
        <?php foreach ($d['klarifikasi_menunggu'] as $k): ?>
            <form method="post" class="mb-3">
                <input type="hidden" name="cid" value="<?= e($k['id']) ?>">
                <label class="form-label fw-semibold"><?= e($k['pertanyaan']) ?></label>
                <textarea name="jawaban" class="form-control mb-2" rows="2" required></textarea>
                <button class="btn btn-danger btn-sm">Kirim jawaban</button>
            </form>
        <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php if ($tl): ?>
    <div class="card border-success mb-3">
        <div class="card-header text-bg-success">Tindak lanjut</div>
        <div class="card-body">
            <p class="mb-1"><strong><?= e($tl['jenis']['label'] ?? '-') ?></strong></p>
            <p><?= e($tl['arahan'] ?? '') ?></p>
            <?php if (!empty($tl['jadwal_konsultasi'])): ?>
                <p class="mb-1">Jadwal konsultasi: <strong><?= e(tgl($tl['jadwal_konsultasi'])) ?> WIB</strong></p>
            <?php endif; ?>
            <div class="small text-muted">
                Dicatat oleh <?= e($tl['dicatat_oleh']['nama'] ?? '-') ?> · <?= e(tgl($tl['dicatat_pada'] ?? null)) ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-header fw-semibold">Linimasa</div>
    <ul class="list-group list-group-flush">
    <?php foreach (array_reverse($d['timeline'] ?? []) as $t): ?>
        <li class="list-group-item">
            <div class="small text-muted"><?= e(tgl($t['waktu'] ?? null)) ?></div>
            <?= e($t['keterangan'] ?? $t['event'] ?? '') ?>
        </li>
    <?php endforeach; ?>
    </ul>
</div>

<a href="riwayat.php" class="btn btn-outline-secondary">Riwayat</a>
<a href="hasil.php?id=<?= urlencode($id) ?>" class="btn btn-primary">Lihat hasil</a>

<?php require __DIR__ . '/includes/footer.php'; ?>