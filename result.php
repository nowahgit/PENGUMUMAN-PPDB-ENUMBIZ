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
    $stmt = $pdo->prepare("SELECT id_periode, nama_periode, tanggal_buka, tanggal_tutup, tanggal_pengumuman_berkas, tanggal_pengumuman_lulus, status FROM selection_periods WHERE status = :status ORDER BY id_periode DESC LIMIT 1");
    $stmt->execute(['status' => 'AKTIF']);
    $period = $stmt->fetch();

    if (!$period) {
        // Fallback ambil terbaru jika gak ada yang aktif
        $stmt = $pdo->prepare("SELECT id_periode, nama_periode, tanggal_buka, tanggal_tutup, tanggal_pengumuman_berkas, tanggal_pengumuman_lulus, status FROM selection_periods ORDER BY id_periode DESC LIMIT 1");
        $stmt->execute();
        $period = $stmt->fetch();
    }


    if ($period) {
        // Karena tanggal di database Anda adalah besok (8 April), 
        // kita PAKSA aktifkan pengumuman hasil akhir hari ini (7 April) 
        // lewat kode ini selama status periode adalah AKTIF.
        $fase = 2; 
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
<html lang="id" class="light">

<head>
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Seleksi — Enumbiz School</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Nunito', 'sans-serif'] },
                    colors: {
                        snpmb: { light: '#ffffff', dark: '#171d2b', gray: '#64748b', blue: '#2563eb' }
                    }
                }
            }
        }
    </script>

    <style>
        .bg-snpmb { background-color: #ffffff; }
        .dark .bg-snpmb { background-color: #171d2b; }
        .chakra-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
        }
        .dark .chakra-card {
            background: rgba(23, 29, 43, 0.8);
            border-color: rgba(255, 255, 255, 0.05);
        }
    </style>
</head>

<body class="bg-snpmb text-gray-900 dark:text-white min-h-screen flex flex-col antialiased transition-colors duration-300">

    <!-- Header -->
    <header class="flex justify-between items-center px-8 py-6 lg:px-16 w-full">
        <a href="index.php" class="flex items-center gap-2 text-gray-500 hover:text-blue-500 transition-colors font-bold">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
        <button id="theme-toggle" class="text-gray-400 hover:text-gray-600 dark:hover:text-white transition-colors p-2 rounded-lg bg-gray-100 dark:bg-gray-800">
            <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
            <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 100 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
        </button>
    </header>

    <main class="flex-1 flex items-center justify-center px-6 py-12">
        <div class="max-w-4xl w-full">
            
            <?php if ($state === 'DB_ERROR'): ?>
                <div class="chakra-card p-10 text-center border-red-500/20">
                    <div class="w-20 h-20 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h2 class="text-2xl font-bold mb-2">Sistem Sedang Maintenance</h2>
                    <p class="text-gray-500 dark:text-gray-400">Koneksi database terputus. Silakan hubungi dministrator.</p>
                    <p class="text-xs text-red-500 mt-4 opacity-70">Error: <?= $e->getMessage() ?></p>
                </div>

            <?php elseif ($state === 'TIDAK_DITEMUKAN'): ?>
                <div class="chakra-card p-10 text-center">
                    <div class="w-20 h-20 bg-yellow-100 dark:bg-yellow-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <h2 class="text-2xl font-bold mb-2">Data Tidak Ditemukan</h2>
                    <p class="text-gray-500">Nomor NISN/Registrasi <b><?= e($query) ?></b> tidak terdaftar.</p>
                    <a href="index.php" class="mt-8 inline-block bg-blue-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-700 transition-all">Coba Lagi</a>
                </div>

            <?php elseif ($state === 'HASIL'): ?>
                <?php 
                    $is_lulus = ($fase === 2 && ($pendaftar['status_seleksi'] ?? '') === 'LULUS');
                    $is_valid = ($fase === 1 && ($pendaftar['status_validasi'] ?? '') === 'VALID');
                    $theme_color = ($is_lulus || $is_valid) ? 'blue' : 'red';
                ?>
                
                <div class="chakra-card overflow-hidden shadow-2xl transition-all duration-500 hover:shadow-blue-500/10">
                    <!-- Top Status Banner -->
                    <div class="<?= $theme_color === 'blue' ? 'bg-blue-600' : 'bg-red-600' ?> p-8 text-center text-white">
                        <?php if (!empty($illustration)): ?>
                            <img src="<?= $illustration ?>" alt="Status" class="h-32 mx-auto mb-4 drop-shadow-xl">
                        <?php endif; ?>
                        <h1 class="text-2xl lg:text-3xl font-black uppercase tracking-widest">
                            <?php 
                                if ($fase === 1) echo $is_valid ? 'BERKAS VALID' : 'BERKAS TIDAK VALID';
                                else echo $is_lulus ? 'SELAMAT! ANDA LULUS' : 'MOHON MAAF, TIDAK LULUS';
                            ?>
                        </h1>
                    </div>

                    <div class="p-8 lg:p-12">
                        <div class="grid lg:grid-cols-2 gap-12">
                            <!-- Student Info -->
                            <div class="space-y-6">
                                <h3 class="text-sm font-bold text-blue-500 uppercase tracking-widest">Identitas Pendaftar</h3>
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-xs text-gray-400 uppercase tracking-tighter">Nama Lengkap</p>
                                        <p class="text-xl font-extrabold"><?= e($pendaftar['nama_pendaftar']) ?></p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-xs text-gray-400 uppercase tracking-tighter">NISN</p>
                                            <p class="font-bold"><?= e($pendaftar['nisn_pendaftar']) ?></p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-400 uppercase tracking-tighter">No. Registrasi</p>
                                            <p class="font-bold"><?= e($pendaftar['nomor_pendaftaran']) ?></p>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400 uppercase tracking-tighter">Asal Sekolah</p>
                                        <p class="font-bold"><?= e($pendaftar['asal_sekolah']) ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Action / Details -->
                            <div class="flex flex-col justify-center p-8 bg-gray-50 dark:bg-gray-800/50 rounded-3xl border border-gray-100 dark:border-gray-700/50">
                                <?php if ($is_lulus || $is_valid): ?>
                                    <div class="text-center">
                                        <div class="mb-4 inline-block p-4 bg-green-100 dark:bg-green-900/30 rounded-2xl text-green-600">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        </div>
                                        <h4 class="text-lg font-bold mb-2">Langkah Selanjutnya</h4>
                                        <p class="text-sm text-gray-500 mb-6">Silakan login ke portal pendaftaran untuk mengunduh bukti seleksi dan informasi daftar ulang.</p>
                                        <a href="https://enumbizsch.elnoahmananalu.my.id/login" class="block w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-500/30 transition-all">Portal Pendaftaran &rarr;</a>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center">
                                        <div class="mb-4 inline-block p-4 bg-red-100 dark:bg-red-900/30 rounded-2xl text-red-600">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </div>
                                        <h4 class="text-lg font-bold mb-2">Tetap Semangat</h4>
                                        <p class="text-sm text-gray-500">Hasil seleksi ini bersifat final. Jangan menyerah dan tetap kembangkan potensi diri Anda!</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="mt-12 pt-8 border-t border-gray-100 dark:border-gray-800 text-center">
                            <p class="text-xs text-gray-400">Dicetak secara sistematis pada <?= date('d/m/Y H:i:s') ?> (WIB)</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="py-8 text-center text-xs text-gray-400">
        &copy; <?= date('Y') ?> Tim Pelaksana PPDB Enumbiz. Ilustrasi oleh Storyset.
    </footer>

    <script>
        const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            themeToggleLightIcon.classList.remove('hidden');
        } else {
            themeToggleDarkIcon.classList.remove('hidden');
        }
        document.getElementById('theme-toggle').addEventListener('click', function() {
            themeToggleDarkIcon.classList.toggle('hidden');
            themeToggleLightIcon.classList.toggle('hidden');
            if (localStorage.getItem('theme')) {
                if (localStorage.getItem('theme') === 'light') {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                }
            } else {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                }
            }
        });
    </script>
</body>
</html>
