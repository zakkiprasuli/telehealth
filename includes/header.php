<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/api.php';
$judulHalaman = $judulHalaman ?? 'Self-Assessment Telehealth';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judulHalaman) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Telehealth</a>
        <div class="navbar-nav">
            <a class="nav-link" href="index.php">Beranda</a>
            <a class="nav-link" href="riwayat.php">Riwayat</a>
            <a class="nav-link" href="uts.php">Status UTS</a>
        </div>
    </div>
</nav>
<main class="container">