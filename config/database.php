<?php
/**
 * Project B — Portal Pengumuman PPDB Enumbiz
 * Database Configuration (READ-ONLY)
 */

date_default_timezone_set('Asia/Jakarta');

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3307');
define('DB_NAME', 'ppdb_enumbiz');
define('DB_USER', 'root');
define('DB_PASS', '');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

/**
 * XSS-safe output helper
 */
function e(?string $text): string {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Format tanggal Indonesia
 */
function formatTanggal(?string $date): string {
    if (!$date) return '-';
    $bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
              'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $ts = strtotime($date);
    if (!$ts) return '-';
    return date('j', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

/**
 * Rate Limiting
 */
function enforceRateLimit() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['ip_rate_limit'])) {
        $_SESSION['ip_rate_limit'] = [];
    }
    $now = time();
    $valid_requests = array_filter($_SESSION['ip_rate_limit'], function($timestamp) use ($now) {
        return $timestamp > ($now - 60);
    });
    
    if (count($valid_requests) >= 10) {
        die("Sistem sedang dalam pemeliharaan (Terlalu banyak permintaan. Silakan coba 1 menit lagi).");
    }
    
    $valid_requests[] = $now;
    $_SESSION['ip_rate_limit'] = $valid_requests;
}
