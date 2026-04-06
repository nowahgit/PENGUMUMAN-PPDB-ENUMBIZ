<?php
/**
 * Project B — Portal Pengumuman PPDB Enumbiz
 * Halaman Hasil Pencarian
 */

date_default_timezone_set('Asia/Jakarta');
require_once __DIR__ . '/config/database.php';

$query = trim($_GET['query'] ?? '');
if (empty($query)) {
    header('Location: index.php');
    exit;
}

// Rate limiting on searches
enforceRateLimit();

$now = new DateTime();
$fase = 0;
$state = '';
$pendaftar = null;
$period = null;

try {
    $pdo = getDB();

    // 1. Cari periode aktif
    $stmt = $pdo->prepare("SELECT id_periode, nama_periode, tanggal_pengumuman_berkas, tanggal_pengumuman_lulus FROM selection_periods WHERE status = :status ORDER BY id_periode DESC LIMIT 1");
    $stmt->execute(['status' => 'AKTIF']);
    $period = $stmt->fetch();

    if (!$period) {
        // Fallback ambil terbaru jika gak ada yang aktif
        $stmt = $pdo->prepare("SELECT id_periode, nama_periode, tanggal_pengumuman_berkas, tanggal_pengumuman_lulus FROM selection_periods ORDER BY id_periode DESC LIMIT 1");
        $stmt->execute();
        $period = $stmt->fetch();
    }

    if ($period) {
        if (!empty($period['tanggal_pengumuman_lulus']) && $now >= new DateTime($period['tanggal_pengumuman_lulus'])) {
            $fase = 2; // Kelulusan
        } elseif (!empty($period['tanggal_pengumuman_berkas']) && $now >= new DateTime($period['tanggal_pengumuman_berkas'])) {
            $fase = 1; // Berkas
        }
    }

    // 2. Jika belum buka pengumuman sama sekali
    if ($fase === 0) {
        $state = 'BELUM_BUKA';
    } else {
        // 3. Cari data pendaftar (PDO Prepared Statement Anti-SQL-Injection)
        $stmt = $pdo->prepare("
            SELECT u.id, u.nama_pendaftar, u.nisn_pendaftar, u.nomor_pendaftaran, u.tanggallahir_pendaftar, u.asal_sekolah,
                   b.status_validasi, b.catatan as catatan_berkas, b.tanggal_validasi,
                   s.status_seleksi, s.waktu_seleksi
            FROM users u
            LEFT JOIN berkas b ON b.user_id = u.id
            LEFT JOIN seleksis s ON s.user_id = u.id
            WHERE u.role = 'PENDAFTAR'
              AND (u.nisn_pendaftar = :query OR u.nomor_pendaftaran = :query)
            LIMIT 1
        ");
        $stmt->execute(['query' => $query]);
        $pendaftar = $stmt->fetch();

        if (!$pendaftar) {
            $state = 'TIDAK_DITEMUKAN';
        } else {
            $state = 'HASIL';
        }
    }
} catch (Exception $e) {
    $state = 'DB_ERROR';
}

$illustration = '';
if ($state === 'HASIL') {
    if ($fase === 1) {
        $status_berkas = $pendaftar['status_validasi'] ?? 'MENUNGGU';
        if ($status_berkas === 'VALID') {
            $illustration = 'assets/images/ACCEPTED.png';
        } elseif ($status_berkas === 'DITOLAK') {
            $illustration = 'assets/images/REJECTED.png';
        }
    } elseif ($fase === 2) {
        $status_seleksi = $pendaftar['status_seleksi'] ?? 'MENUNGGU';
        if ($status_seleksi === 'LULUS') {
            $illustration = 'assets/images/ACCEPTED.png';
        } elseif ($status_seleksi === 'TIDAK_LULUS') {
            $illustration = 'assets/images/REJECTED.png';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Hasil Pencarian PPDB — Enumbiz School</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="enterprise-body">

<div class="enterprise-layout">
    <header class="enterprise-header">
        <div class="header-logo">
            <svg width="32" height="32" viewBox="0 0 40 40" fill="none"><rect width="40" height="40" fill="#0A192F"/><rect x="10" y="10" width="20" height="20" fill="#FFFFFF"/></svg>
        </div>
        <div class="header-title">
            <span class="sys-name">SISTEM INFORMASI ADMINISTRASI PPDB</span>
            <span class="inst-name">ENUMBIZ SCHOOL</span>
        </div>
        <a href="index.php" class="btn-back">&larr; KEMBALI</a>
    </header>

    <main class="enterprise-content">
        <div class="doc-container">
            <div class="doc-header">
                <h2>LEMBAR HASIL SELEKSI</h2>
                <div class="doc-meta">TANGGAL CETAK: <?= date('d/m/Y H:i') ?></div>
            </div>

            <?php if ($state === 'DB_ERROR'): ?>
                <div class="alert-box alert-error">
                    <h3>[ERROR] SISTEM DALAM PEMELIHARAAN</h3>
                    <p>Koneksi basis data terputus. Silakan hubungi Administrator Sistem.</p>
                </div>
            <?php elseif ($state === 'BELUM_BUKA'): ?>
                <div class="alert-box alert-warning">
                    <h3>[INFO] PENGUMUMAN BELUM TERSEDIA</h3>
                    <p>Fase pengumuman saat ini belum dibuka. Jadwal terdekat: <?= (!empty($period['tanggal_pengumuman_berkas']) ? formatTanggal($period['tanggal_pengumuman_berkas']) : 'TBA') ?></p>
                </div>
            <?php elseif ($state === 'TIDAK_DITEMUKAN'): ?>
                <div class="alert-box alert-error">
                    <h3>[404] DATA TIDAK DITEMUKAN</h3>
                    <p>Kueri pencarian [<strong><?= e($query) ?></strong>] tidak cocok dengan data pendaftar mana pun.</p>
                </div>
            <?php elseif ($state === 'HASIL'): ?>
                <?php 
                    $nama = strtoupper($pendaftar['nama_pendaftar'] ?? 'NN');
                    $nisn = $pendaftar['nisn_pendaftar'] ?? '-';
                    $noreg = $pendaftar['nomor_pendaftaran'] ?? '-';
                    $tgl_lahir = formatTanggal($pendaftar['tanggallahir_pendaftar']);
                    $asal_sekolah = strtoupper($pendaftar['asal_sekolah'] ?? '-');
                ?>

                <?php if (!empty($illustration)): ?>
                    <div style="text-align: center; margin-bottom: 30px;">
                        <img src="<?= $illustration ?>" alt="Hasil Seleksi" style="max-width: 280px; height: auto; filter: drop-shadow(0 10px 15px rgba(0,0,0,0.5));">
                    </div>
                <?php endif; ?>

                <!-- Data Pribadi Table -->
                <div class="data-group">
                    <h4 class="group-title">A. IDENTITAS PENDAFTAR</h4>
                    <table class="enterprise-table">
                        <tr><th width="30%">NOMOR REGISTRASI</th><td><?= e($noreg) ?></td></tr>
                        <tr><th>NISN</th><td><?= e($nisn) ?></td></tr>
                        <tr><th>NAMA LENGKAP</th><td class="text-bold"><?= e($nama) ?></td></tr>
                        <tr><th>TANGGAL LAHIR</th><td><?= e($tgl_lahir) ?></td></tr>
                        <tr><th>ASAL SEKOLAH</th><td><?= e($asal_sekolah) ?></td></tr>
                    </table>
                </div>

                <!-- Status Seleksi Table -->
                <div class="data-group mt-20">
                    <h4 class="group-title">B. STATUS SELEKSI</h4>
                    <?php if ($fase === 1): ?>
                        <!-- FASE 1 -->
                        <?php $status_berkas = $pendaftar['status_validasi'] ?? 'MENUNGGU'; ?>
                        <table class="enterprise-table table-status">
                            <tr><th width="30%">TAHAPAN</th><td>SELEKSI BERKAS / ADMINISTRASI</td></tr>
                            <tr><th>STATUS VERIFIKASI</th>
                                <?php if ($status_berkas === 'VALID'): ?>
                                    <td class="status-pass text-bold">MEMENUHI SYARAT (VALID)</td>
                                <?php elseif ($status_berkas === 'DITOLAK'): ?>
                                    <td class="status-fail text-bold">TIDAK MEMENUHI SYARAT (DITOLAK)</td>
                                <?php else: ?>
                                    <td class="status-wait text-bold">PROSES VERIFIKASI (MENUNGGU)</td>
                                <?php endif; ?>
                            </tr>
                            <?php if ($status_berkas === 'DITOLAK'): ?>
                            <tr><th>KETERANGAN</th><td class="text-fail"><?= empty($pendaftar['catatan_berkas']) ? 'Berkas tidak lengkap.' : e($pendaftar['catatan_berkas']) ?></td></tr>
                            <?php endif; ?>
                        </table>
                    <?php elseif ($fase === 2): ?>
                        <!-- FASE 2 -->
                        <?php $status_seleksi = $pendaftar['status_seleksi'] ?? 'MENUNGGU'; ?>
                        <table class="enterprise-table table-status">
                            <tr><th width="30%">TAHAPAN</th><td>PENENTUAN KELULUSAN AKHIR</td></tr>
                            <tr><th>STATUS KELULUSAN</th>
                                <?php if ($status_seleksi === 'LULUS'): ?>
                                    <td class="status-pass text-bold highlight">LULUS SELEKSI</td>
                                <?php elseif ($status_seleksi === 'TIDAK_LULUS'): ?>
                                    <td class="status-fail text-bold highlight">TIDAK LULUS SELEKSI</td>
                                <?php else: ?>
                                    <td class="status-wait text-bold">MENUNGGU KEPUTUSAN PANITIA</td>
                                <?php endif; ?>
                            </tr>
                        </table>

                        <?php if ($status_seleksi === 'LULUS'): ?>
                            <div class="action-box box-success mt-20">
                                <strong>TINDAKAN SELANJUTNYA:</strong> Silakan login ke portal utama untuk mencetak formil bukti kelulusan dan melengkapi persyaratan daftar ulang.
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="doc-footer mt-40">
                    <p>Keputusan panitia seleksi bersifat final dan mengikat. Dokumen ini digenerate secara otomatis oleh sistem rekam jejak seleksi PPDB Enumbiz School.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>
