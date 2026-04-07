<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';

echo "<h2>Portal Debugging — PPDB Enumbiz</h2>";

try {
    $pdo = getDB();
    echo "✅ <b>Koneksi Database</b>: Berhasil!<br>";
    echo "Host: " . DB_HOST . "<br>";
    echo "Waktu Server: " . date('Y-m-d H:i:s') . "<br><br>";

    // Dump Data Periode Aktif
    echo "<h3>1. Periode Aktif (selection_periods)</h3>";
    $period = $pdo->query("SELECT id_periode, nama_periode, tanggal_pengumuman_berkas, tanggal_pengumuman_lulus, status FROM selection_periods WHERE status = 'AKTIF' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    
    if ($period) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Nama</th><th>Tgl Pengumuman Berkas</th><th>Tgl Pengumuman Lulus</th><th>Status</th></tr>";
        echo "<tr>";
        echo "<td>{$period['id_periode']}</td>";
        echo "<td>{$period['nama_periode']}</td>";
        echo "<td>{$period['tanggal_pengumuman_berkas']}</td>";
        echo "<td>{$period['tanggal_pengumuman_lulus']}</td>";
        echo "<td>{$period['status']}</td>";
        echo "</html>";
        echo "</table>";
        
        $now = time();
        $berkas = strtotime($period['tanggal_pengumuman_berkas']);
        $lulus = strtotime($period['tanggal_pengumuman_lulus']);
        
        echo "<br><b>Pengecekan Waktu:</b><br>";
        echo "Sekarang: " . date('Y-m-d H:i:s', $now) . "<br>";
        echo "Status Fase 1 (Berkas): " . ($now >= $berkas ? "✅ SUDAH DIBUKA" : "❌ BELUM DIBUKA") . "<br>";
        echo "Status Fase 2 (Final): " . ($now >= $lulus ? "✅ SUDAH DIBUKA" : "❌ BELUM DIBUKA") . "<br>";
        
    } else {
        echo "❌ Tidak ada periode dengan status 'AKTIF'!";
    }

} catch (Exception $e) {
    echo "❌ <b>Error:</b> " . $e->getMessage();
}