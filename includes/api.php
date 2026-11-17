<?php
function api(string $path, string $method = 'GET', ?array $body = null): array
{
    $cfg = require __DIR__ . '/../config.php';

    $ch = curl_init($cfg['api_url'] . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $cfg['token'],
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    $res    = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error  = curl_error($ch);
    curl_close($ch);

    if ($res === false) {
        return ['success' => false, 'status' => 0, 'message' => 'Gagal terhubung: ' . $error];
    }

    $json = json_decode($res, true)
        ?? ['success' => false, 'message' => 'Respons bukan JSON'];
    $json['status'] = $status;
    return $json;
}

function mhs(): array
{
    return (require __DIR__ . '/../config.php')['mhs'];
}

function e($s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

date_default_timezone_set('Asia/Jakarta');

function badge(?array $s): string
{
    $kode  = $s['kode'] ?? '';
    $warna = [
        'draft' => 'secondary', 'belum_dikirim' => 'secondary',
        'terkirim' => 'primary', 'menunggu_peninjauan' => 'warning',
        'sedang_ditinjau' => 'info', 'perlu_klarifikasi' => 'danger',
        'klarifikasi_dijawab' => 'primary', 'sudah_ditinjau' => 'success',
    ][$kode] ?? 'secondary';
    return '<span class="badge text-bg-' . $warna . '">' . e($s['label'] ?? '-') . '</span>';
}

function tgl(?string $s): string
{
    return $s ? date('d M Y, H:i', strtotime($s)) : '-';
}