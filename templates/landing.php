<div id="landing-card" class="chakra-card form-card flex flex-col items-center">
    
    <!-- Heading Section -->
    <div class="mb-10 text-center">
        <h1 class="text-[mobile:1rem, tablet:1.8rem, desktop:2.2rem] font-[900] text-white mb-2 leading-tight tracking-tight uppercase">
            Hasil Seleksi PPDB Enumbiz 2026
        </h1>
        <p class="text-[#999999] font-medium text-lg">Masukkan NISN dan Tanggal Lahir.</p>
    </div>

    <!-- Error Toast Simulation -->
    <?php if ($current_state === STATE_NOT_FOUND): ?>
        <div class="w-full bg-[#dc4e2e] text-white px-6 py-4 rounded-lg mb-8 text-sm flex items-center justify-between font-bold animate-pulse shadow-xl">
            <span>ERROR: Data NISN atau Tanggal Lahir Tidak Ditemukan.</span>
            <button onclick="this.parentElement.remove()" class="opacity-80 hover:opacity-100">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Control Form -->
    <form action="index.php" method="POST" class="w-full space-y-10">
        <input type="hidden" name="action" value="check_result">
        
        <!-- NISN Field -->
        <div class="w-full">
            <label for="no_reg" class="label-chakra mb-4 inline-block tracking-widest">NISN</label>
            <input type="text" id="no_reg" name="no_reg" placeholder="Nomor Induk Siswa Nasional" 
                   class="input-chakra h-14 bg-[rgba(250,250,250,0.18)]" 
                   value="2026000000098" required>
            <p class="text-[#666666] text-xs mt-3 font-medium tracking-wide italic">We will never share your NISN.</p>
        </div>

        <!-- Date Field -->
        <div class="w-full">
            <label class="label-chakra mb-4 inline-block tracking-widest">Tanggal Lahir</label>
            <div class="flex items-center">
                <input type="number" id="birth_day" name="birth_day" placeholder="Tgl" 
                       class="input-chakra h-14 max-w-[110px] text-center" min="1" max="31" required>
                
                <span class="text-[#999999] text-3xl font-bold mx-5">/</span>
                
                <input type="number" id="birth_month" name="birth_month" placeholder="Bln" 
                       class="input-chakra h-14 max-w-[110px] text-center" min="1" max="12" required>
                
                <span class="text-[#999999] text-3xl font-bold mx-5">/</span>
                
                <input type="number" id="birth_year" name="birth_year" placeholder="Thn" 
                       class="input-chakra h-14 max-w-[110px] text-center" min="1990" max="2020" required>
            </div>
            <input type="hidden" name="birth_date" id="birth_date_combined">
        </div>

        <!-- Submit Row -->
        <div class="pt-6">
            <button type="submit" id="submit-btn" class="btn-chakra w-full h-14 uppercase tracking-widest shadow-lg shadow-blue-500/20 active:scale-95 transition-all text-sm font-black">
                LIHAT HASIL SELEKSI
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('submit-btn').closest('form').onsubmit = function(e) {
    const d = document.getElementById('birth_day').value.padStart(2, '0');
    const m = document.getElementById('birth_month').value.padStart(2, '0');
    const y = document.getElementById('birth_year').value;
    document.getElementById('birth_date_combined').value = `${y}-${m}-${d}`;
    
    // Simulating loading state
    const btn = document.getElementById('submit-btn');
    btn.innerHTML = '<span class="inline-block animate-spin mr-2">◌</span> LOADING...';
    btn.disabled = true;
};
</script>
