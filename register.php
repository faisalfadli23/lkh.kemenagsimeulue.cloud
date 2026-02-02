<?php
// Pastikan koneksi disertakan
include 'config/koneksi.php';
// Panggil header (Navigasi biasanya otomatis muncul, jika ingin disembunyikan pakai CSS di bawah)
include 'includes/header.php';
include 'includes/loader.php';
?>

<style>
    /* Sinkronisasi dengan gaya profil.php dan pemulihan akun */
    body { background-color: #f4f7f6; }
    .kemenag-gradient { background: linear-gradient(135deg, #006837 0%, #004d29 100%); color: white; }
    .card-custom { border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.08); overflow: hidden; }
    
    .register-icon-area {
        width: 90px; height: 90px;
        background: linear-gradient(135deg, #d4af37, #b8860b);
        color: white; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem;
        margin: -45px auto 15px;
        border: 5px solid white;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .form-label { font-size: 0.75rem; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; }
    .btn-emerald { background-color: #006837; color: white; border: none; padding: 12px; border-radius: 10px; transition: all 0.3s; font-weight: bold; }
    .btn-emerald:hover { background-color: #004d29; color: #ffda6a; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    
    .input-group-custom { background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 10px; padding: 5px 15px; }
    .input-group-custom input, .input-group-custom select { background: transparent; border: none; padding: 10px 5px; font-size: 0.9rem; }
    .input-group-custom input:focus, .input-group-custom select:focus { outline: none; box-shadow: none; }
    .input-group-custom i { color: #adb5bd; width: 20px; }
    
    /* Menyembunyikan navbar agar fokus pada form (Opsional) */
    nav { display: none; } 
</style>

<div class="container mt-1 pt-1 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            
            <div class="card card-custom mt-4">
                <div class="card-header kemenag-gradient" style="height: 80px; border: none;"></div>
                
                <div class="card-body p-4 pt-0">
                    
                    <div class="register-icon-area">
                        <i class="fas fa-user-plus"></i>
                    </div>

                    <div class="text-center mb-4">
                        <h4 class="fw-bold text-dark mb-1">Registrasi Pegawai</h4>
                        <p class="text-muted small">Buat akun untuk mulai melaporkan kinerja harian</p>
                    </div>

                    <form action="proses_register.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nomor Induk Pegawai (NIP)</label>
                            <div class="d-flex align-items-center input-group-custom">
                                <i class="fas fa-id-card"></i>
                                <input type="text" name="nip" class="form-control" placeholder="Masukkan NIP Anda" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <div class="d-flex align-items-center input-group-custom">
                                <i class="fas fa-user"></i>
                                <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama tanpa gelar" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <div class="d-flex align-items-center input-group-custom">
                                <i class="fas fa-at"></i>
                                <input type="text" name="username" class="form-control" placeholder="Untuk keperluan login" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="d-flex align-items-center input-group-custom">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password" class="form-control" placeholder="Minimal 8 karakter" minlength="8" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jabatan</label>
                            <div class="d-flex align-items-center input-group-custom">
                                <i class="fas fa-briefcase"></i>
                                <input type="text" name="jabatan" class="form-control" placeholder="Contoh: Analis Kepegawaian">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Satuan Kerja</label>
                            <div class="d-flex align-items-center input-group-custom">
                                <i class="fas fa-building"></i>
                                <select name="satker_id" class="form-control" required>
                                    <option value="">Pilih Satuan Kerja</option>
                                    <?php
                                    $sql_satker = mysqli_query($conn, "SELECT * FROM satuan_kerja");
                                    while($s = mysqli_fetch_assoc($sql_satker)){
                                        echo "<option value='".$s['id']."'>".$s['nama_satker']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-emerald shadow-sm">
                                <i class="fas fa-check-circle me-2"></i> Daftar Sekarang
                            </button>
                            <a href="login.php" class="btn btn-link text-decoration-none text-muted small mt-2">
                                Sudah punya akun? <span class="text-success fw-bold">Login di sini</span>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Panggil footer
include 'includes/footer.php'; 
?>