<?php
/**
 * Project B — Portal Pengumuman PPDB Enumbiz
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/database.php';

// Rate limit logic (per current system)
if (function_exists('enforceRateLimit')) {
    enforceRateLimit();
}

$db_error = false;
$isRegistrationOpen = false;
$period = null;

try {
    $pdo = getDB();

    // Current periods status logic based on native project structure
    $stmt = $pdo->prepare("
        SELECT id_periode, nama_periode, tanggal_buka, tanggal_tutup, tanggal_pengumuman_berkas, tanggal_pengumuman_lulus 
        FROM selection_periods 
        WHERE status = 'AKTIF' 
        ORDER BY id_periode DESC 
        LIMIT 1
    ");
    $stmt->execute();
    $period = $stmt->fetch();

    if ($period) {
        $now = date('Y-m-d H:i:s');
        // Anggap terbuka jika statusnya AKTIF (karena kolom tanggal tidak ada)
        $isRegistrationOpen = true;
    } else {
        $isRegistrationOpen = false;
    }
    $target_date = null;
    if ($period) {
        $now_ts = time();
        $isRegistrationOpen = ($period['status'] === 'AKTIF'); // Buka jika AKTIF tanpa cek tanggal
        $target_date = null; // Countdown dihilangkan karena sudah buka
    }
} catch (Exception $e) {
    $db_error = $e->getMessage();
}


// Redirect if it's already an auth context? Not applicable for native portal.
?>
<!DOCTYPE html>
<html lang="id" class="light">

<head>
    <script>
        // Check local storage or system preference to apply the theme before everything else
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            document.documentElement.classList.remove('light');
        } else {
            document.documentElement.classList.remove('dark');
            document.documentElement.classList.add('light');
        }
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Pengumuman PPDB Enumbiz School</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN (Replacing Vite for Native PHP) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Nunito', 'sans-serif'],
                    },
                    colors: {
                        snpmb: {
                            light: '#ffffff',
                            dark: '#171d2b',
                            gray: '#64748b',
                            blue: '#2563eb'
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Custom white theme */
        .bg-snpmb { background-color: #ffffff; }
        .text-snpmb-gray { color: #64748b; }
        .btn-snpmb-blue { background-color: #2563eb; }
        .btn-snpmb-blue:hover { background-color: #1d4ed8; }
        .btn-snpmb-dark { background-color: #f8fafc; }
        .btn-snpmb-dark:hover { background-color: #f1f5f9; }

        /* Dark mode overrides */
        .dark .bg-snpmb { background-color: #171d2b; }
        .dark .text-snpmb-gray { color: #8e9aab; }
        .dark .btn-snpmb-blue { background-color: #3b82f6; }
        .dark .btn-snpmb-blue:hover { background-color: #2563eb; }
        .dark .btn-snpmb-dark { background-color: #262c38; }
        .dark .btn-snpmb-dark:hover { background-color: #323a49; }
        
        /* Ensure dark mode text changes reliably */
        .dark { color: #ffffff !important; }
        .dark h1, .dark .title-text { color: #ffffff !important; }
        
        /* Fallback for missing tailwind classes */
        .title-text { color: #111827; }
        
        /* Foolproof responsive layout */
        .layout-wrapper {
            display: flex;
            flex-direction: column-reverse; /* Put Image on top on Mobile */
            gap: 3rem;
            align-items: center;
            width: 100%;
        }
        .layout-wrapper > div { width: 100%; }
        @media (min-width: 1024px) {
            .layout-wrapper {
                flex-direction: row; /* Normal row for Desktop */
                gap: 5rem;
                justify-content: space-between;
            }
            .layout-wrapper > div {
                flex: 1;
            }
        }
        
    </style>
</head>

<body class="bg-snpmb text-gray-900 dark:text-white min-h-screen flex flex-col antialiased transition-colors duration-300">

    <!-- Header -->
    <header class="flex justify-between items-center px-8 py-6 lg:px-16 w-full">
      

        <div>
            <!-- Dark/Light Mode Toggle Icon -->
            <button id="theme-toggle" class="text-gray-400 hover:text-gray-600 dark:hover:text-white transition-colors p-2 rounded-lg bg-gray-100 dark:bg-gray-800 focus:outline-none">
                <!-- Moon icon (for light mode) -->
                <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                </svg>
                <!-- Sun icon (for dark mode) -->
                <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 100 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex items-center justify-center px-8 lg:px-16 w-full relative -mt-8">
        <div class="max-w-7xl mx-auto w-full layout-wrapper">

            <!-- Left Side: Copywriting & Search Form -->
            <div class="space-y-6 z-10">

                <?php if ($db_error): ?>
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4 rounded-xl text-red-700 dark:text-red-400">
                        <p class="font-bold">Koneksi Database Gagal</p>
                        <p class="text-xs opacity-80 mt-1">Error: <?= $db_error ?></p>
                        <p class="text-sm mt-2">Mohon hubungi administrator jika masalah berlanjut.</p>
                    </div>
                <?php endif; ?>

                <h1 class="text-4xl lg:text-5xl font-extrabold leading-[1.1] tracking-tight title-text mb-4">
                    <?php if ($isRegistrationOpen): ?>
                        Raih masa depanmu di Portal PPDB Enumbiz
                    <?php else: ?>
                        Portal belum dibuka, silahkan cek kembali jadwal yang tertera
                    <?php endif; ?>
                </h1>

                <p class="text-snpmb-gray text-base lg:text-lg max-w-lg leading-relaxed">
                    Masukkan NISN atau Nomor Registrasi Anda di bawah ini untuk melihat hasil seleksi. 
                    Pastikan data yang Anda masukkan sesuai dengan kartu pendaftaran.
                </p>

                <!-- NEW: Countdown Section -->
                <?php if (!$isRegistrationOpen && $target_date): ?>
                    <div id="countdown-wrapper" class="py-4 px-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-3xl inline-flex flex-col gap-2">
                        <p class="text-xs font-bold text-blue-600 dark:text-blue-400 tracking-widest uppercase">Pengumuman Dibuka Dalam:</p>
                        <div class="flex gap-4 text-center">
                            <div class="flex flex-col"><span id="days" class="text-2xl font-black text-blue-700 dark:text-blue-300">00</span><span class="text-[10px] uppercase font-bold opacity-50">Hari</span></div>
                            <div class="text-2xl font-black opacity-30">:</div>
                            <div class="flex flex-col"><span id="hours" class="text-2xl font-black text-blue-700 dark:text-blue-300">00</span><span class="text-[10px] uppercase font-bold opacity-50">Jam</span></div>
                            <div class="text-2xl font-black opacity-30">:</div>
                            <div class="flex flex-col"><span id="minutes" class="text-2xl font-black text-blue-700 dark:text-blue-300">00</span><span class="text-[10px] uppercase font-bold opacity-50">Menit</span></div>
                            <div class="text-2xl font-black opacity-30">:</div>
                            <div class="flex flex-col"><span id="seconds" class="text-2xl font-black text-blue-700 dark:text-blue-300">00</span><span class="text-[10px] uppercase font-bold opacity-50">Detik</span></div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Search Form -->
                <div class="pt-2 w-full max-w-md">
                    <form action="result.php" method="GET" class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" name="query" required 
                            class="block w-full pl-12 pr-4 py-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700/50 rounded-2xl shadow-sm focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 dark:text-white transition-all outline-none"
                            placeholder="Ketik NISN Kamu disini...">
                        <button type="submit" 
                            class="absolute right-2 top-2 bottom-2 px-6 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-all shadow-lg shadow-blue-500/30 flex items-center gap-2">
                            Cek
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </form>
                </div>

                <div class="flex flex-wrap items-center gap-4 pt-4">
                    <a href="https://enumbizsch.elnoahmananalu.my.id/login"
                        class="btn-snpmb-dark border border-gray-200 dark:border-gray-700/50 text-gray-700 dark:text-white px-6 py-3 rounded-lg text-sm font-bold transition-colors">
                        Sistem Pendaftaran &rarr;
                    </a>
                </div>

                <div class="pt-8 space-y-3 text-sm text-snpmb-gray transition-colors duration-300">
                    <p>Baca pengumuman terbaru dan informasi penting di <a href="#"
                            class="text-[#3f79ff] dark:text-blue-400 hover:underline transition-colors">Beranda Enumbiz ↗</a></p>
                    <p>Laman panduan PPDB <a href="#"
                            class="text-[#3f79ff] dark:text-blue-400 hover:underline transition-colors">https://enumbizsch.elnoahmananalu.my.id↗</a></p>
                </div>
            </div>

            <!-- Right Side: Illustration -->
            <div class="flex justify-center lg:justify-end items-center relative w-full h-full max-w-md lg:max-w-lg mx-auto">
                <img src="assets/images/INDEX.png" alt="PPDB Enumbiz Illustration" class="w-full h-auto drop-shadow-2xl hover:scale-[1.02] transition-transform duration-500">
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-8 text-center text-xs text-snpmb-gray w-full mt-auto transition-colors duration-300">
        <p>&copy; <?php echo date('Y'); ?> Tim Pelaksana PPDB Enumbiz. v2.1.0-AER. Ilustrasi oleh Storyset.</p>
    </footer>

    <script>
        var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

        // Change the icons inside the button based on previous settings
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            themeToggleLightIcon.classList.remove('hidden');
        } else {
            themeToggleDarkIcon.classList.remove('hidden');
        }

        var themeToggleBtn = document.getElementById('theme-toggle');

        themeToggleBtn.addEventListener('click', function() {
            // toggle icons inside button
            themeToggleDarkIcon.classList.toggle('hidden');
            themeToggleLightIcon.classList.toggle('hidden');

            // if set via local storage previously
            if (localStorage.getItem('theme')) {
                if (localStorage.getItem('theme') === 'light') {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                }

            // if NOT set via local storage previously
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
    <?php if (!$isRegistrationOpen && $target_date): ?>
    <script>
        const targetDate = new Date("<?= $target_date ?>").getTime();

        const updateCountdown = setInterval(function() {
            const now = new Date().getTime();
            const distance = targetDate - now;

            if (distance < 0) {
                clearInterval(updateCountdown);
                location.reload(); // Reload saat waktu habis
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById("days").innerText = days.toString().padStart(2, '0');
            document.getElementById("hours").innerText = hours.toString().padStart(2, '0');
            document.getElementById("minutes").innerText = minutes.toString().padStart(2, '0');
            document.getElementById("seconds").innerText = seconds.toString().padStart(2, '0');
        }, 1000);
    </script>
    <?php endif; ?>
</body>
</html>
