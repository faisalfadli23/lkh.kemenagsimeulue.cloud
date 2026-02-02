<?php
session_start();
include 'config/koneksi.php';

if (!isset($_SESSION['id'])) { 
    header("Location: login.php"); 
    exit; 
}

$user_id = $_SESSION['id'];
$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'"));

// Proses Update
if(isset($_POST['update_profil'])){
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $jabatan = mysqli_real_escape_string($conn, $_POST['jabatan']);
    $password_baru = $_POST['password_baru'];

    if(!empty($password_baru)){
        $pass_hash = password_hash($password_baru, PASSWORD_DEFAULT);
        $update = mysqli_query($conn, "UPDATE users SET nama_lengkap='$nama', jabatan='$jabatan', password='$pass_hash' WHERE id='$user_id'");
    } else {
        $update = mysqli_query($conn, "UPDATE users SET nama_lengkap='$nama', jabatan='$jabatan' WHERE id='$user_id'");
    }

    if($update){
        $_SESSION['nama'] = $nama; 
        echo "<script>alert('Profil Berhasil Diperbarui!'); window.location.href='profil.php';</script>";
    }
}

include 'includes/header.php';
include 'includes/loader.php';
?>

<style>
    .kemenag-gradient { background: linear-gradient(135deg, #006837 0%, #004d29 100%); color: white; }
    .card-custom { border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
    .profile-avatar {
        width: 100px; height: 100px;
        background: linear-gradient(135deg, #d4af37, #b8860b);
        color: white; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 2.5rem; font-weight: bold;
        margin: -50px auto 15px;
        border: 5px solid white;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .form-label { font-size: 0.8rem; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; }
    .btn-emerald { background-color: #006837; color: white; border: none; padding: 12px; border-radius: 10px; transition: all 0.3s; }
    .btn-emerald:hover { background-color: #004d29; color: #ffda6a; transform: translateY(-2px); }
    .input-group-custom { background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 10px; padding: 5px 15px; }
    .input-group-custom input { background: transparent; border: none; padding: 10px 5px; }
    .input-group-custom input:focus { outline: none; box-shadow: none; }
</style>

<div class="container mt-1 pt-1 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            
            <div class="card card-custom mt-4">
                <div class="card-header kemenag-gradient" style="height: 100px; border-radius: 20px 20px 0 0;"></div>
                <div class="card-body p-4 pt-0">
                    
                    <div class="profile-avatar">
                        <?= strtoupper(substr($data['nama_lengkap'], 0, 1)); ?>
                    </div>

                    <div class="text-center mb-4">
                        <h4 class="fw-bold text-dark mb-1"><?= $data['nama_lengkap']; ?></h4>
                        <span class="badge bg-light text-success border px-3 rounded-pill"><?= strtoupper($data['level']); ?></span>
                    </div>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Username / NIP</label>
                            <div class="d-flex align-items-center input-group-custom bg-light">
                                <i class="fas fa-id-badge text-muted me-2"></i>
                                <input type="text" class="form-control" value="<?= $data['username']; ?>" readonly>
                            </div>
                            <small class="text-muted" style="font-size: 0.7rem;">Username dikunci secara sistem.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <div class="d-flex align-items-center input-group-custom">
                                <i class="fas fa-user text-muted me-2"></i>
                                <input type="text" name="nama_lengkap" class="form-control" value="<?= $data['nama_lengkap']; ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jabatan Saat Ini</label>
                            <div class="d-flex align-items-center input-group-custom">
                                <i class="fas fa-briefcase text-muted me-2"></i>
                                <input type="text" name="jabatan" class="form-control" value="<?= $data['jabatan']; ?>" placeholder="Contoh: Analis SDM">
                            </div>
                        </div>

                        <div class="py-2"><hr class="text-light"></div>
                        
                        <div class="mb-4">
                            <label class="form-label">Ganti Password</label>
                            <div class="d-flex align-items-center input-group-custom">
                                <i class="fas fa-key text-muted me-2"></i>
                                <input type="password" name="password_baru" class="form-control" placeholder="Isi hanya jika ingin mengganti">
                            </div>
                            <small class="text-info" style="font-size: 0.7rem;">Gunakan minimal 8 karakter kombinasi angka & huruf.</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="update_profil" class="btn btn-emerald fw-bold shadow-sm">
                                <i class="fas fa-check-circle me-2"></i> Simpan Perubahan
                            </button>
                            <a href="<?= ($data['level'] == 'pegawai') ? 'dashboard.php' : 'dashboard_admin.php' ?>" class="btn btn-link text-decoration-none text-muted small">
                                <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <p class="text-center mt-4 text-muted small">
                Terdaftar sejak: <strong><?= date('d M Y', strtotime($data['created_at'] ?? date('Y-m-d'))); ?></strong>
            </p>
            
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>