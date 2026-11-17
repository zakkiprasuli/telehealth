<?php
$judulHalaman = 'Hasil Assessment';
require __DIR__ . '/includes/header.php';

$id  = $_GET['id'] ?? '';
$res = $id !== '' ? api('/assessments/' . urlencode($id) . '/hasil') : ['success' => false];
if (!$res['success']) {
    echo '<div class="alert alert-danger">' . e($res['message'] ?? 'Gagal memuat hasil.') . '</div>';
    require __DIR__ . '/includes/footer.php'; exit;
}

$data  = $res['data'];
$h     = $data['hasil'] ?? $data;       // jaga-jaga bila dibungkus "hasil"
$skor  = $h['skor'] ?? null;
$kel   = $h['kelengkapan'] ?? [];
$ubah  = $data['perubahan'] ?? $h['perubahan'] ?? null;
?>

<h4><?= e($h['judul'] ?? 'Hasil Assessment') ?></h4>
<p class="text-muted"><?= e($id) ?></p>

<div class="alert alert-warning">
    <strong>Penting:</strong> <?= e($h['keterbatasan'] ?? 'Hasil self-assessment bukan diagnosis.') ?>
</div>

<?php if ($skor): ?>
    <div class="card mb-3 text-center"><div class="card-body">
        <div class="display-4"><?= e($skor['total']) ?> <small class="fs-5 text-muted">/ <?= e($skor['maks']) ?></small></div>
        <span class="badge text-bg-primary fs-6">Kategori: <?= e($skor['kategori']['label'] ?? '-') ?></span>
        <p class="mt-3 mb-0"><?= e($skor['keterangan'] ?? '') ?></p>
    </div></div>
<?php endif; ?>

<?php if ($kel): ?>
    <div class="mb-3">
        <div class="d-flex justify-content-between small">
            <span>Kelengkapan: <?= e($kel['label'] ?? '-') ?> (<?= e($kel['wajib_terisi'] ?? 0) ?>/<?= e($kel['wajib_total'] ?? 0) ?> wajib)</span>
            <span><?= (int) ($kel['persen'] ?? 0) ?>%</span>
        </div>
        <div class="progress"><div class="progress-bar bg-success" style="width: <?= (int) ($kel['persen'] ?? 0) ?>%"></div></div>
    </div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-header fw-semibold">Ringkasan jawaban</div>
    <div class="table-responsive"><table class="table mb-0 align-middle"><tbody>
    <?php foreach (($h['ringkasan'] ?? []) as $r): ?>
        <tr>
            <td style="width:45%"><?= e($r['pertanyaan'] ?? $r['label'] ?? '') ?>
                <?php if (($r['data_type'] ?? '') === 'pengukuran'): ?><span class="badge text-bg-info">Pengukuran</span><?php endif; ?>
            </td>
            <td>
                <?php if (!empty($r['terisi'])): ?>
                    <?= e($r['jawaban_teks'] ?? $r['jawaban']) ?><?= !empty($r['satuan']) ? ' ' . e($r['satuan']) : '' ?>
                <?php else: ?><span class="text-muted fst-italic">Belum diisi</span><?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody></table></div>
</div>

<?php if (!empty($ubah['perubahan'])): ?>
    <div class="card mb-3">
        <div class="card-header fw-semibold">Perubahan dibanding pengisian sebelumnya (<?= e($ubah['pembanding_id'] ?? '') ?>)</div>
        <ul class="list-group list-group-flush">
        <?php foreach ($ubah['perubahan'] as $p): ?>
            <li class="list-group-item"><?= e($p['label']) ?>: <?= e($p['sebelumnya']) ?> → <strong><?= e($p['sekarang']) ?></strong></li>
        <?php endforeach; ?>
        <?php if (!empty($ubah['skor'])): ?>
            <li class="list-group-item">Skor: <?= e($ubah['skor']['sebelumnya']) ?> → <strong><?= e($ubah['skor']['sekarang']) ?></strong>
                (<?= $ubah['skor']['selisih'] > 0 ? '+' : '' ?><?= e($ubah['skor']['selisih']) ?>)</li>
        <?php endif; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (!empty($h['langkah_berikutnya'])): ?>
    <div class="alert alert-info"><strong>Langkah berikutnya:</strong> <?= e($h['langkah_berikutnya']) ?></div>
<?php endif; ?>

<a href="status.php?id=<?= urlencode($id) ?>" class="btn btn-primary">Lihat status</a>
<a href="riwayat.php" class="btn btn-outline-secondary">Riwayat</a>

<?php require __DIR__ . '/includes/footer.php'; ?>