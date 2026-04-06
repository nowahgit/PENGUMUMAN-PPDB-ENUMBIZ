<?php 
    $st = $result_data['status_seleksi'] ?? 'MENUNGGU'; 
    $is_lulus = ($st === 'LULUS');
?>

<div class="result-card-container w-full flex justify-center py-6">
    
    <?php if($st === 'MENUNGGU'): ?>
        <!-- WAITING STATE -->
        <div class="chakra-card p-12 text-center max-w-2xl mx-auto border-t-4 border-[#88ccf0]">
            <h2 class="text-3xl font-black text-white mb-6 uppercase tracking-widest">Sedang Ditinjau</h2>
            <p class="text-[#999999] text-xl font-medium">Data kelulusan Anda sedang dalam tahap verifikasi akhir oleh panitia seleksi.</p>
        </div>
        
    <?php else: ?>
        <!-- PASS / FAIL REPLICATING WEB-KELULUSAN-V2 -->
        <div class="chakra-card result-card-v2">
            
            <!-- SECTION A: Header Banner -->
            <div class="banner-v2 <?php echo $is_lulus ? 'lulus' : 'fail'; ?>">
                <div class="banner-text">
                    <h1 class="text-white font-[900] text-[1.1rem,1.3rem,1.7rem] uppercase tracking-wider">
                        <?php if($is_lulus): ?>
                            SELAMAT! ANDA DINYATAKAN LULUS!
                        <?php else: ?>
                            MOHON MAAF, ANDA DINYATAKAN TIDAK LULUS
                        <?php endif; ?>
                    </h1>
                    <?php if(!$is_lulus): ?>
                        <p class="text-[#999999] font-[700] text-[0.8rem,1rem,1.2rem] mt-2 uppercase">
                            TETAP SEMANGAT DAN JANGAN MENYERAH
                        </p>
                    <?php endif; ?>
                </div>
                <!-- No school logo per request -->
            </div>

            <!-- SECTION B: Student Identity -->
            <div class="identity-section">
                <div>
                   <p class="text-[#88ccf0] font-[900] text-[0.9rem] uppercase tracking-widest mb-1">NISN <?php echo e($result_data['nisn_pendaftar']); ?></p>
                   <h2 class="text-white font-[900] tracking-[2px] text-[1.8rem, 1.9rem, 2.3rem] uppercase leading-none mb-3">
                       <?php echo e(strtoupper($result_data['nama_pendaftar'])); ?>
                   </h2>
                   <p class="text-white font-[300] text-[0.9rem,1rem,1.2rem]">KELAS XII-A</p>
                   <p class="text-white font-[300] text-[0.9rem,1rem,1.2rem]">PPDB ENUMBIZ SCHOOL 2026</p>
                </div>
                
                <?php if($is_lulus): ?>
                <div class="qr-container bg-white p-2 rounded">
                    <!-- Standardized SVG QR Placeholder -->
                    <svg width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="1.5"><path d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z M7 7h.01 M17 7h.01 M7 17h.01 M17 17h.01"/></svg>
                </div>
                <?php endif; ?>
            </div>

            <!-- SECTION C: Data Grid + SKL BOX -->
            <div class="data-grid-v2">
                <!-- Col 1 -->
                <div class="info-col">
                    <div class="info-item">
                        <p class="info-label">Tanggal Lahir</p>
                        <p class="info-val"><?php echo date('d-m-Y', strtotime($result_data['tanggallahir_pendaftar'])); ?></p>
                    </div>
                    <div class="info-item">
                        <p class="info-label">Kelas / Jurusan</p>
                        <p class="info-val">XII / JALUR AKADEMIK</p>
                    </div>
                </div>

                <!-- Col 2 -->
                <div class="info-col">
                    <div class="info-item">
                        <p class="info-label">Kabupaten/Kota</p>
                        <p class="info-val">SIMULATED CITY</p>
                    </div>
                    <div class="info-item">
                        <p class="info-label">Provinsi</p>
                        <p class="info-val">SIMULATED PROVINCE</p>
                    </div>
                </div>

                <!-- Col 3: SKL Box -->
                <div class="skl-col">
                    <?php if($is_lulus): ?>
                        <div class="skl-white-box shadow-xl border-l-[6px] border-[#008acf]">
                            <h3 class="text-[#2d2d2d] text-[1.2rem] font-[700] mb-2">Silahkan Download Bukti Seleksi</h3>
                            <p class="text-[#2d2d2d] text-[0.9rem] font-[300] mb-6 leading-relaxed">Kartu pendaftaran dan bukti seleksi dapat di download pada link berikut untuk keperluan daftar ulang:</p>
                            <button class="w-full bg-[#008acf] hover:bg-[#0070a8] text-white font-[900] text-[1.1rem] py-4 rounded-lg transition-all shadow-md">
                                DOWNLOAD PDF SELEKSI
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- SECTION D: Footer Note -->
            <?php if($is_lulus): ?>
            <div class="footer-v2">
                <p>Status kelulusan Anda ditetapkan setelah Sekolah melakukan verifikasi data akademik (rapor dan/atau nilai ujian). Silakan Anda membaca peraturan tentang kelulusan siswa demi kelancaran proses administrasi di PPDB Enumbiz School.</p>
            </div>
            <?php endif; ?>

            <!-- PRINT / BACK BAR -->
            <div class="px-8 pb-8 flex justify-between items-center opacity-40">
                <a href="index.php" class="text-[9px] font-black tracking-widest uppercase hover:underline">← Kembali Ke Halaman Utama</a>
                <p class="text-[9px] font-black tracking-widest uppercase">Dicetak pada: <?php echo date('d/m/Y H:i'); ?></p>
            </div>

        </div>
    <?php endif; ?>

</div>
