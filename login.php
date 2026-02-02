<?php
session_start();
// Jika user sudah login, arahkan langsung ke dashboard masing-masing
if (isset($_SESSION['id'])) {
    if ($_SESSION['level'] == 'atasan' || $_SESSION['level'] == 'admin') {
        header("Location: dashboard_admin.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}

include 'includes/header.php';
include 'includes/loader.php';
?>

<style>
    /* Sinkronisasi gaya dengan tema E-LKH */
    body { background-color: #f4f7f6; }
    .kemenag-gradient { background: linear-gradient(135deg, #006837 0%, #004d29 100%); color: white; }
    .card-custom { border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.08); overflow: hidden; }
    
    .login-icon-area {
        width: 100px; height: 100px;
        background: white;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: -50px auto 15px;
        border: 5px solid white;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        padding: 10px;
    }

    .form-label { font-size: 0.75rem; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; }
    .btn-emerald { background-color: #006837; color: white; border: none; padding: 12px; border-radius: 10px; transition: all 0.3s; font-weight: bold; }
    .btn-emerald:hover { background-color: #004d29; color: #ffda6a; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    
    .input-group-custom { background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 10px; padding: 5px 15px; }
    .input-group-custom input { background: transparent; border: none; padding: 10px 5px; font-size: 0.9rem; }
    .input-group-custom input:focus { outline: none; box-shadow: none; }
    .input-group-custom i { color: #adb5bd; width: 20px; }
    
    /* Menghilangkan navbar pada halaman login */
    nav { display: none; } 
</style>

<div class="container mt-1 pt-1 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            
            <div class="card card-custom mt-5">
                <div class="card-header kemenag-gradient" style="height: 100px; border: none;"></div>
                
                <div class="card-body p-4 pt-0">
                    
                    <div class="login-icon-area">
                        <img src="assets/logo.png" alt="Logo Kemenag" class="img-fluid">
                    </div>

                    <div class="text-center mb-4">
                        <h4 class="fw-bold text-dark mb-1">Login LKH</h4>
                    </div>

                    <?php if(isset($_GET['pesan']) && $_GET['pesan'] == 'gagal'): ?>
                        <div class="alert alert-danger border-0 small text-center py-2 mb-3">
                            <i class="fas fa-exclamation-circle me-1"></i> Username atau Password salah!
                        </div>
                    <?php endif; ?>

                    <form action="proses_login.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <div class="d-flex align-items-center input-group-custom">
                                <i class="fas fa-user-circle"></i>
                                <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label">Password</label>
                                <a href="lupa_password.php" class="small text-decoration-none text-muted mb-1" style="font-size: 0.7rem;">Lupa Password?</a>
                            </div>
                            <div class="d-flex align-items-center input-group-custom">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-emerald shadow-sm">
                                <i class="fas fa-sign-in-alt me-2"></i> Masuk Sekarang
                            </button>
                        </div>
                        
                        <div class="py-3"><hr class="opacity-10"></div>
                        
                        <div class="text-center">
                            <p class="small text-muted mb-3">Belum memiliki akun pegawai?</p>
                            <a href="register.php" class="btn btn-outline-success w-100 rounded-pill fw-bold py-2 shadow-sm" style="font-size: 0.85rem;">
                                <i class="fas fa-user-plus me-1"></i> Daftar Akun Baru
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
include 'includes/footer.php';
?>