<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test 1: Load config
echo "1. Load config...<br>";
require_once __DIR__ . '/config/database.php';
echo "✅ Config loaded<br><br>";

// Test 2: Koneksi DB
echo "2. Test koneksi...<br>";
try {
    $pdo = getDB();
    echo "✅ Koneksi berhasil!<br><br>";
} catch (Exception $e) {
    echo "❌ Gagal: " . $e->getMessage() . "<br>";
    die();
}

// Test 3: Cek kolom selection_periods
echo "3. Struktur tabel selection_periods:<br>";
$cols = $pdo->query("DESCRIBE selection_periods")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    echo "- " . $col['Field'] . " (" . $col['Type'] . ")<br>";
}