<?php
session_start();
include 'config/koneksi.php';

// Proteksi Login
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'];
$user_id = $_SESSION['id'];

// Ambil data LKH sekalian JOIN dengan tabel users untuk mengambil Nama Lengkap
$query = mysqli_query($conn, "SELECT lkh.*, users.nama_lengkap 
                              FROM lkh 
                              JOIN users ON lkh.user_id = users.id 
                              WHERE lkh.id='$id' AND lkh.user_id='$user_id'");
$data = mysqli_fetch_array($query);

// Validasi: Jika data tidak ditemukan atau status BUKAN 'proses', tendang balik ke dashboard
if (!$data || $data['status'] != 'proses') {
    echo "<script>alert('Akses ditolak! Laporan tidak ditemukan atau sudah diverifikasi pimpinan.'); window.location.href='dashboard.php';</script>";
    exit;
}

// Logika Update Data
if (isset($_POST['update'])) {
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $kegiatan = mysqli_real_escape_string($conn, $_POST['kegiatan']);
    $hasil_kegiatan = mysqli_real_escape_string($conn, $_POST['hasil_kegiatan']);
    $link_bukti = mysqli_real_escape_string($conn, $_POST['link_bukti']);

    $update = "UPDATE lkh SET 
                tanggal='$tanggal', 
                kegiatan='$kegiatan',
                hasil_kegiatan='$hasil_kegiatan',
                link_bukti_dukung='$link_bukti' 
               WHERE id='$id' AND user_id='$user_id'";
    
    if (mysqli_query($conn, $update)) {
        echo "<script>alert('Laporan berhasil diperbarui!'); window.location.href='dashboard.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui laporan.');</script>";
    }
}

// PANGGIL HEADER
include 'includes/header.php';
include 'includes/loader.php';
?>

<style>
    /* Sinkronisasi tema Emerald & Gold */
    body { background-color: #f4f7f6; }
    .kemenag-gradient { background: linear-gradient(135deg, #006837 0%, #004d29 100%); color: white; }
    .card-custom { border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.08); overflow: hidden; }
    
    .edit-icon-area {
        width: 80px; height: 80px;
        background: linear-gradient(135deg, #ffc107, #d39e00);
        color: white; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.8rem;
        margin: -40px auto 15px;
        border: 5px solid white;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .form-label { font-size: 0.75rem; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; }
    .btn-emerald { background-color: #006837; color: white; border: none; padding: 12px; border-radius: 10px; transition: all 0.3s; font-weight: bold; width: 100%; }
    .btn-emerald:hover { background-color: #004d29; color: #ffda6a; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    
    .input-group-custom { background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 10px; padding: 5px 15px; transition: all 0.3s; }
    .input-group-custom:focus-within { border-color: #ffc107; background-color: #fff; box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.05); }
    .input-group-custom input, .input-group-custom textarea { background: transparent; border: none; padding: 10px 5px; font-size: 0.9rem; width: 100%; }
    .input-group-custom input:focus, .input-group-custom textarea:focus { outline: none; box-shadow: none; }
    .input-group-custom i { color: #adb5bd; width: 25px; text-align: center; }
</style>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            
            <a href="dashboard.php" class="btn btn-sm text-muted mb-3 text-decoration-none">
                <i class="fas fa-arrow-left me-2"></i>Batal & Kembali
            </a>

            <div class="card card-custom mt-4">
                <div class="card-header kemenag-gradient" style="height: 80px; border: none;"></div>
                
                <div class="card-body p-4 p-lg-5 pt-0">
                    
                    <div class="edit-icon-area">
                        <i class="fas fa-sync-alt"></i>
                    </div>

                    <div class="text-center mb-4">
                        <h4 class="fw-bold text-dark mb-1">Koreksi LKH</h4>
                        <p class="text-muted small">Laporan: <?= $data['nama_lengkap']; ?> - Masih dalam status proses</p>
                    </div>

                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label">Tanggal Kegiatan</label>
                            <div class="d-flex align-items-center input-group-custom">
                                <i class="fas fa-calendar-check"></i>
                                <input type="date" name="tanggal" value="<?= $data['tanggal']; ?>" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Deskripsi Kegiatan</label>
                            <div class="d-flex align-items-start input-group-custom">
                                <i class="fas fa-pen-nib mt-3"></i>
                                <textarea name="kegiatan" rows="4" required><?= $data['kegiatan']; ?></textarea>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Hasil / Satuan Output</label>
                            <div class="d-flex align-items-center input-group-custom">
                                <i class="fas fa-bullseye"></i>
                                <input type="text" name="hasil_kegiatan" value="<?= $data['hasil_kegiatan']; ?>" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Link Bukti Dukung</label>
                            <div class="d-flex align-items-center input-group-custom">
                                <i class="fas fa-external-link-alt"></i>
                                <input type="url" name="link_bukti" value="<?= $data['link_bukti_dukung']; ?>" placeholder="https://...">
                            </div>
                        </div>

                        <div class="alert alert-light border-0 small py-2 mb-4 d-flex align-items-center" style="background-color: #fff9e6;">
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                            <span class="text-muted">Laporan yang sudah diverifikasi atasan tidak dapat dikoreksi lagi.</span>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="update" class="btn btn-emerald shadow-sm py-3">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                            </button>
                            <a href="dashboard.php" class="btn btn-link text-muted small text-decoration-none mt-2">Abaikan & Keluar</a>
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