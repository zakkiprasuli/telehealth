<?php
$judulHalaman = 'Status UTS';
require __DIR__ . '/includes/header.php';

$res = api('/uts/' . urlencode(mhs()['nim']));
if (!$res['success']) {
    echo '<div class="alert alert-danger">' . e($res['message'] ?? 'Gagal memuat status UTS.') . '</div>';
    require __DIR__ . '/includes/footer.php'; exit;
}

$d     = $res['data'];
$bukti = $d['bukti_fitur'] ?? [];
$nilai = $d['penilaian'] ?? [];
$jml   = $d['jumlah'] ?? [];
$warna = ['disetujui' => 'success', 'perlu_revisi' => 'warning', 'ditolak' => 'danger'][$nilai['status']['kode'] ?? ''] ?? 'secondary';
?>

<h4>Status UTS</h4>
<p class="text-muted"><?= e($d['nim'] ?? '') ?> · <?= e($d['nama'] ?? '') ?> · <?= e($d['kelas'] ?? '') ?></p>

<div class="card mb-3"><div class="card-body">
    <div class="small text-muted">Penilaian dosen</div>
    <span class="badge text-bg-<?= $warna ?> fs-6"><?= e($nilai['status']['label'] ?? 'Belum diperiksa') ?></span>
    <?php if (!empty($nilai['catatan'])): ?>
        <p class="mt-2 mb-0"><strong>Catatan:</strong> <?= e($nilai['catatan']) ?></p>
    <?php endif; ?>
    <?php if (!empty($nilai['diperiksa_pada'])): ?>
        <div class="small text-muted">Diperiksa <?= e(tgl($nilai['diperiksa_pada'])) ?></div>
    <?php endif; ?>
</div></div>

<div class="row g-3 mb-3">
    <?php foreach (['total' => 'Total', 'terkirim' => 'Terkirim', 'draf' => 'Draf', 'sudah_ditinjau' => 'Sudah ditinjau'] as $k => $l): ?>
        <div class="col-6 col-md-3"><div class="card text-center"><div class="card-body">
            <div class="fs-3"><?= e($jml[$k] ?? 0) ?></div><div class="small text-muted"><?= e($l) ?></div>
        </div></div></div>
    <?php endforeach; ?>
</div>

<div class="card mb-4">
    <div class="card-header fw-semibold">
        Bukti fitur: <?= e($bukti['tercapai'] ?? 0) ?> / <?= e($bukti['total'] ?? 0) ?>
    </div>
    <ul class="list-group list-group-flush">
    <?php foreach (($bukti['daftar'] ?? []) as $b): ?>
        <li class="list-group-item d-flex justify-content-between">
            <span><?= e($b['label']) ?> <small class="text-muted">(<?= e($b['kode']) ?>)</small></span>
            <?= !empty($b['tercapai']) ? '<span class="text-success">✓ Tercapai</span>' : '<span class="text-muted">Belum</span>' ?>
        </li>
    <?php endforeach; ?>
    </ul>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>