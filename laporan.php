<?php
session_start();
include 'config/koneksi.php';

if (!isset($_SESSION['id'])) { 
    header("Location: login.php"); 
    exit; 
}

$is_admin = ($_SESSION['level'] != 'pegawai');
$my_id = $_SESSION['id'];

// Ambil data atasan otomatis untuk tampilan awal (khusus role pegawai)
$nama_atasan_otomatis = "Otomatis Sesuai Satker";
if (!$is_admin) {
    $query_atasan_default = mysqli_query($conn, "SELECT a.nama_lengkap FROM users u JOIN users a ON u.atasan_id = a.id WHERE u.id = '$my_id'");
    $data_atasan = mysqli_fetch_assoc($query_atasan_default);
    $nama_atasan_otomatis = $data_atasan['nama_lengkap'] ?? 'Belum Diatur';
}

include 'includes/header.php';
include 'includes/loader.php';
?>

<style>
    .kemenag-gradient { background: linear-gradient(135deg, #006837 0%, #004d29 100%); color: white; }
    .card-custom { border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .form-label { font-size: 0.85rem; color: #555; margin-bottom: 8px; }
    .form-control-lg, .form-select-lg { border-radius: 10px; font-size: 1rem; border: 1px solid #dee2e6; }
    .form-control-lg:focus, .form-select-lg:focus { border-color: #006837; box-shadow: 0 0 0 0.25 margin rgba(0, 104, 55, 0.1); }
    .btn-emerald { background-color: #006837; color: white; border: none; transition: all 0.3s; }
    .btn-emerald:hover { background-color: #004d29; color: #ffda6a; transform: translateY(-2px); }
    .icon-box { width: 35px; height: 35px; background: rgba(0, 104, 55, 0.1); color: #006837; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; margin-right: 10px; }
    .locked-field { background-color: #f8f9fa; cursor: not-allowed; }
</style>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            
            <div class="mb-4">
                <a href="<?php echo ($is_admin) ? 'dashboard_admin.php' : 'dashboard.php'; ?>" class="text-decoration-none text-muted small">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
                </a>
            </div>

            <div class="card card-custom overflow-hidden">
                <div class="card-header kemenag-gradient py-4 text-center border-0">
                    <h4 class="mb-1 fw-bold">Cetak Laporan Kinerja</h4>
                    <p class="mb-0 small text-white-50">Generate laporan capaian kinerja pegawai (PDF/Excel)</p>
                </div>
                
                <div class="card-body p-4 p-lg-5">
                    <form action="proses_cetak.php" method="GET" target="_blank" id="formLaporan">
                        <div class="row g-4">
                            
                            <div class="col-12">
                                <label class="form-label fw-bold">
                                    <div class="icon-box"><i class="fas fa-user border-0"></i></div>Nama Pegawai
                                </label>
                                <?php if($is_admin): ?>
                                    <select name="user_id" id="user_id_select" class="form-select form-select-lg shadow-sm" required onchange="updateAtasan()">
                                        <option value="" data-atasan="Otomatis Sesuai Satker">-- Pilih Pegawai --</option>
                                        <?php
                                        // Ambil semua user dan nama atasan mereka sekaligus
                                        $u = mysqli_query($conn, "SELECT u.id, u.nama_lengkap, u.level, a.nama_lengkap as nama_atasan 
                                                                  FROM users u 
                                                                  LEFT JOIN users a ON u.atasan_id = a.id 
                                                                  ORDER BY u.level ASC, u.nama_lengkap ASC");
                                        while($row = mysqli_fetch_assoc($u)) {
                                            $level_tag = ($row['level'] != 'pegawai') ? " (".strtoupper($row['level']).")" : "";
                                            $atasan_val = $row['nama_atasan'] ?? 'Atasan Belum Diatur';
                                            $selected = ($row['id'] == $my_id) ? "" : ""; // Default tidak terpilih agar muncul "Otomatis Sesuai Satker"
                                            echo "<option value='".$row['id']."' data-atasan='".$atasan_val."'>".$row['nama_lengkap'].$level_tag."</option>";
                                        }
                                        ?>
                                    </select>
                                <?php else: ?>
                                    <input type="hidden" name="user_id" value="<?php echo $_SESSION['id']; ?>">
                                    <div class="p-3 rounded-3 bg-light border border-start-4 border-success shadow-sm">
                                        <small class="text-muted d-block">Mencetak laporan untuk:</small>
                                        <span class="fw-bold text-dark fs-5"><?php echo $_SESSION['nama']; ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">
                                    <div class="icon-box"><i class="fas fa-signature"></i></div>Pejabat Penandatangan (Atasan)
                                </label>
                                <div class="p-3 rounded-3 locked-field border shadow-sm">
                                    <small class="text-muted d-block">Penandatangan Laporan:</small>
                                    <span class="fw-bold text-dark" id="nama_atasan_display"><?php echo $nama_atasan_otomatis; ?></span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Dari Tanggal</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-calendar-alt text-muted"></i></span>
                                    <input type="date" name="tgl_awal" id="tgl_awal" class="form-control form-control-lg border-start-0 shadow-sm" value="<?php echo date('Y-m-01'); ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Sampai Tanggal</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-calendar-check text-muted"></i></span>
                                    <input type="date" name="tgl_akhir" id="tgl_akhir" class="form-control form-control-lg border-start-0 shadow-sm" value="<?php echo date('Y-m-t'); ?>" required>
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <div class="alert alert-light border shadow-sm rounded-3 py-2 px-3 mb-4">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-magic text-warning me-3 fa-lg"></i>
                                        <small class="text-muted">
                                            Sistem akan <strong>otomatis menjumlahkan volume</strong> kegiatan yang memiliki uraian yang sama dalam rentang tanggal yang dipilih.
                                        </small>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <button type="submit" name="format" value="print" class="btn btn-danger btn-lg w-100 shadow-sm py-3">
                                            <i class="fas fa-file-pdf me-2"></i> Cetak PDF
                                        </button>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="submit" name="format" value="excel" class="btn btn-success btn-lg w-100 shadow-sm py-3">
                                            <i class="fas fa-file-excel me-2"></i> Export Excel
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
            
            <p class="text-center text-muted small mt-4">
                Pastikan data LKH sudah <strong>disetujui</strong> oleh atasan agar muncul dalam laporan.
            </p>
        </div>
    </div>
</div>

<script>
// Fungsi untuk update Nama Atasan secara otomatis saat Pegawai dipilih (Admin/Atasan Only)
function updateAtasan() {
    var select = document.getElementById("user_id_select");
    var display = document.getElementById("nama_atasan_display");
    var selectedOption = select.options[select.selectedIndex];
    var namaAtasan = selectedOption.getAttribute("data-atasan");
    
    display.innerText = namaAtasan;
}

document.getElementById('formLaporan').onsubmit = function() {
    var tglAwal = document.getElementById('tgl_awal').value;
    var tglAkhir = document.getElementById('tgl_akhir').value;
    
    if (tglAkhir < tglAwal) {
        alert('Format tanggal salah: Tanggal Akhir tidak boleh mendahului Tanggal Awal!');
        return false;
    }
    return true;
};
</script>

<?php include 'includes/footer.php'; ?>