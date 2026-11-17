<?php
function ambil(array $arr, array $keys, $default = null)
{
    foreach ($keys as $k) {
        if (isset($arr[$k]) && $arr[$k] !== '') return $arr[$k];
    }
    return $default;
}

// Padanan isVisible() di dokumentasi (pertanyaan bercabang)
function terlihat(?array $cond, array $jawaban): bool
{
    if (!$cond) return true;
    if (isset($cond['any'])) {
        foreach ($cond['any'] as $c) if (terlihat($c, $jawaban)) return true;
        return false;
    }
    if (isset($cond['all'])) {
        foreach ($cond['all'] as $c) if (!terlihat($c, $jawaban)) return false;
        return true;
    }
    $v      = $jawaban[$cond['question']] ?? null;
    $terisi = !($v === null || $v === '' || $v === []);

    if (array_key_exists('equals', $cond))   return $v === $cond['equals'];
    if (isset($cond['in']))                  return in_array($v, $cond['in'], true);
    if (isset($cond['includes']))            return is_array($v) && in_array($cond['includes'], $v, true);
    if (array_key_exists('filled', $cond))   return $terisi === $cond['filled'];
    return true;
}

// Cari bagian terdekat yang tampil (arah +1 maju, -1 mundur)
function cariBagian(int $dari, int $arah, array $bagian, array $jawaban): ?int
{
    for ($i = $dari; $i >= 0 && $i < count($bagian); $i += $arah) {
        if (terlihat($bagian[$i]['show_if'] ?? null, $jawaban)) return $i;
    }
    return null;
}

function opsi(array $q): array
{
    $hasil = [];
    foreach (($q['options'] ?? $q['opsi'] ?? []) as $o) {
        if (is_array($o)) {
            $nilai = ambil($o, ['value', 'nilai', 'kode', 'id']);
            $hasil[] = [
                'value'     => $nilai,
                'label'     => ambil($o, ['label', 'teks', 'text'], (string) $nilai),
                'exclusive' => !empty($o['exclusive']) || $nilai === 'tidak_ada',
            ];
        } else {
            $hasil[] = ['value' => $o, 'label' => $o, 'exclusive' => false];
        }
    }
    // Skala tanpa daftar opsi: buat dari min..max
    if (!$hasil && ($q['type'] ?? '') === 'scale' && isset($q['min'], $q['max'])) {
        for ($i = (int) $q['min']; $i <= (int) $q['max']; $i++) {
            $hasil[] = ['value' => $i, 'label' => (string) $i, 'exclusive' => false];
        }
    }
    return $hasil;
}

// Ubah nilai dari <form> ke tipe yang benar. Kosong = null (BUKAN "tidak")
function bersihkan(array $q, $v)
{
    $tipe = $q['type'] ?? 'text';
    if ($tipe === 'multi_choice') {
        $v = is_array($v) ? array_values(array_filter($v, fn($x) => $x !== '')) : [];
        return $v ?: null;
    }
    if ($v === null || $v === '') return null;
    if ($tipe === 'scale' || $tipe === 'number') return is_numeric($v) ? $v + 0 : null;
    $v = trim((string) $v);
    return $v === '' ? null : $v;
}

function renderPertanyaan(array $q, $nilai, ?string $error): void
{
    $qid    = $q['id'];
    $tipe   = $q['type'] ?? 'text';
    $label  = ambil($q, ['pertanyaan', 'question', 'label', 'text'], $qid);
    $wajib  = !empty($q['required']) || !empty($q['wajib']);
    $showIf = isset($q['show_if']) ? json_encode($q['show_if']) : '';
    $invalid = $error ? ' is-invalid' : '';
    $name   = 'jawab[' . $qid . ']';
    ?>
    <div class="mb-4 pertanyaan" data-qid="<?= e($qid) ?>"
         <?= $showIf ? 'data-showif="' . e($showIf) . '"' : '' ?>>
        <label class="form-label fw-semibold">
            <?= e($label) ?>
            <?php if ($wajib): ?><span class="text-danger">*</span><?php endif; ?>
            <?php if (($q['data_type'] ?? '') === 'pengukuran'): ?>
                <span class="badge text-bg-info">Pengukuran</span>
            <?php endif; ?>
        </label>

        <?php if ($tipe === 'text'): ?>
            <input type="text" class="form-control<?= $invalid ?>" name="<?= e($name) ?>"
                   value="<?= e($nilai ?? '') ?>"
                   <?= isset($q['max_length']) ? 'maxlength="' . (int) $q['max_length'] . '"' : '' ?>>

        <?php elseif ($tipe === 'textarea'): ?>
            <textarea class="form-control<?= $invalid ?>" name="<?= e($name) ?>" rows="3"
                <?= isset($q['max_length']) ? 'maxlength="' . (int) $q['max_length'] . '"' : '' ?>><?= e($nilai ?? '') ?></textarea>

        <?php elseif ($tipe === 'number'): ?>
            <div class="input-group">
                <input type="number" step="any" class="form-control<?= $invalid ?>" name="<?= e($name) ?>"
                       value="<?= e($nilai ?? '') ?>"
                       <?= isset($q['min']) ? 'min="' . e($q['min']) . '"' : '' ?>
                       <?= isset($q['max']) ? 'max="' . e($q['max']) . '"' : '' ?>>
                <?php if (!empty($q['satuan'])): ?>
                    <span class="input-group-text"><?= e($q['satuan']) ?></span>
                <?php endif; ?>
            </div>

        <?php elseif ($tipe === 'date'): ?>
            <input type="date" class="form-control<?= $invalid ?>" name="<?= e($name) ?>"
                   value="<?= e($nilai ?? '') ?>" max="<?= date('Y-m-d') ?>">

        <?php elseif ($tipe === 'single_choice' || $tipe === 'scale'): ?>
            <div class="<?= $tipe === 'scale' ? 'd-flex flex-wrap gap-3' : '' ?>">
            <?php foreach (opsi($q) as $i => $o): $oid = $qid . '_' . $i; ?>
                <div class="form-check">
                    <input class="form-check-input<?= $invalid ?>" type="radio" name="<?= e($name) ?>"
                           id="<?= e($oid) ?>" value="<?= e($o['value']) ?>"
                           <?= ($nilai !== null && (string) $nilai === (string) $o['value']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="<?= e($oid) ?>"><?= e($o['label']) ?></label>
                </div>
            <?php endforeach; ?>
            </div>

        <?php elseif ($tipe === 'multi_choice'): ?>
            <?php foreach (opsi($q) as $i => $o): $oid = $qid . '_' . $i; ?>
                <div class="form-check">
                    <input class="form-check-input<?= $invalid ?>" type="checkbox" name="<?= e($name) ?>[]"
                           id="<?= e($oid) ?>" value="<?= e($o['value']) ?>"
                           data-exclusive="<?= $o['exclusive'] ? '1' : '0' ?>"
                           <?= (is_array($nilai) && in_array($o['value'], $nilai, true)) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="<?= e($oid) ?>"><?= e($o['label']) ?></label>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="invalid-feedback d-block"><?= e($error) ?></div>
        <?php endif; ?>
    </div>
    <?php
}