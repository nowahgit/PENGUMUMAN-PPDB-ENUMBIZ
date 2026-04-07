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
} catch (PDOException $e) {
    echo "❌ <b>Gagal Koneksi PDO:</b> " . $e->getMessage() . "<br>";
    echo "Host: " . DB_HOST . "<br>";
    echo "User: " . DB_USER . "<br>";
    die();
} catch (Exception $e) {
    echo "❌ <b>Error:</b> " . $e->getMessage() . "<br>";
    die();
}

// Test 3: Struktur Tabel
$tables = ['selection_periods', 'users', 'berkas', 'seleksis'];
foreach ($tables as $table) {
    echo "<h3>Tabel: $table</h3>";
    try {
        $cols = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $col) echo "- " . $col['Field'] . " (" . $col['Type'] . ")<br>";
    } catch (Exception $e) {
        echo "❌ Gagal: " . $e->getMessage() . "<br>";
    }
}