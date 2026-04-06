<?php
try {
    $pdo = new PDO(
        "mysql:host=127.0.0.1;port=3306;dbname=elnoahma_portalppdbenumbiz;charset=utf8mb4",
        'elnoahma_portaluser',
        'pudingcoklatpakhambali'
    );
    echo "✅ Koneksi berhasil!<br>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tabel: " . (empty($tables) ? "❌ Database kosong, belum ada tabel!" : implode(', ', $tables));
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}