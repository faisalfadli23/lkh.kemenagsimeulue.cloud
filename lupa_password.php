<?php
// Pastikan koneksi disertakan
include 'config/koneksi.php';
// Panggil header (pastikan header.php Anda berisi tag <head> dan library Bootstrap/FontAwesome)
include 'includes/header.php';
include 'includes/loader.php';
?>

<style>
    /* Sinkronisasi dengan gaya profil.php */
    body { background-color: #f4f7f6; }
    .kemenag-gradient { background: linear-gradient(135deg, #006837 0%, #004d29 100%); color: white; }
    .card-custom { border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.08); overflow: hidden; }
    
    .reset-icon-area {
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
    .input-group-custom input { background: transparent; border: none; padding: 10px 5px; font-size: 0.9rem; }
    .input-group-custom input:focus { outline: none; box-shadow: none; }
    .input-group-custom i { color: #adb5bd; width: 20px; }
    
    /* Menghilangkan navbar jika diakses dari halaman luar (opsional) */
    nav { display: none; } 
</style>

<div class="container mt-1 pt-1 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            
            <div class="card card-custom mt-4">
                <div class="card-header kemenag-gradient" style="height: 80px; border: none;"></div>
                
                <div class="card-body p-4 pt-0">
                    
                    <div class="reset-icon-area">
                        <i class="fas fa-shield-alt"></i>
                    </div>

                    <div class="text-center mb-4">
                        <h4 class="fw-bold text-dark mb-1">Pemulihan Akun</h4>
                        <p class="text-muted small">Verifikasi data Anda untuk mereset password</p>
                    </div>

                    <?php 
                    if(isset($_GET['pesan'])){
                        if($_GET['pesan'] == "notfound") {
                            echo "<div class='alert alert-danger border-0 small text-center py-2 mb-3'><i class='fas fa-times-circle me-1'></i> Username & NIP tidak cocok!</div>";
                        }
                        if($_GET['pesan'] == "success") {
                            echo "<div class='alert alert-success border-0 small text-center py-2 mb-3'><i class='fas fa-check-circle me-1'></i> Password berhasil diperbarui!</div>";
                        }
                    }
                    ?>

                    <form action="proses_lupa_password.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <div class="d-flex align-items-center input-group-custom">
                                <i class="fas fa-user"></i>
                                <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nomor Induk Pegawai (NIP)</label>
                            <div class="d-flex align-items-center input-group-custom">
                                <i class="fas fa-id-card"></i>
                                <input type="text" name="nip" class="form-control" placeholder="Masukkan NIP sebagai verifikasi" required>
                            </div>
                        </div>

                        <div class="py-2"><hr class="opacity-10"></div>

                        <div class="mb-4">
                            <label class="form-label">Password Baru</label>
                            <div class="d-flex align-items-center input-group-custom">
                                <i class="fas fa-lock text-warning"></i>
                                <input type="password" name="password_baru" class="form-control" placeholder="Gunakan minimal 8 karakter" minlength="8" required>
                            </div>
                            <small class="text-info d-block mt-2" style="font-size: 0.7rem;">
                                <i class="fas fa-lightbulb me-1"></i> Gunakan kombinasi huruf dan angka.
                            </small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-emerald shadow-sm">
                                <i class="fas fa-save me-2"></i> Perbarui Password
                            </button>
                            <a href="login.php" class="btn btn-link text-decoration-none text-muted small mt-2">
                                <i class="fas fa-arrow-left me-1"></i> Kembali ke Halaman Login
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="text-center mt-4">
                <div class="d-inline-block p-2 px-4 rounded-pill bg-white shadow-sm border">
                    <p class="small text-muted mb-0">Butuh bantuan? 
                        <a href="https://wa.me/6281337525661" target="_blank" class="text-success text-decoration-none fw-bold">
                            <i class="fab fa-whatsapp me-1"></i>Admin IT
                        </a>
                    </p>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php 
// Panggil footer (pastikan footer.php berisi penutup tag body dan html)
include 'includes/footer.php'; 
?>