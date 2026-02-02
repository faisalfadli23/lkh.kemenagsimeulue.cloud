<?php
session_start();
include 'config/koneksi.php';

// Proteksi: Hanya boleh diakses oleh Admin/Atasan
if (!isset($_SESSION['id']) || $_SESSION['level'] == 'pegawai') {
    header("Location: dashboard.php");
    exit;
}

$my_id = $_SESSION['id'];
$my_level = $_SESSION['level'];

/** * LOGIKA FILTER AKSES SATKER & NAMA SATKER (Disamakan dengan dashboard_admin) */
if ($my_id == 1 || $my_level == 'super_admin') { 
    // Khusus Kepala Kantor atau Super Admin: Memantau Satker 1 sampai 5
    $nama_satker = "Seluruh Satuan Kerja";
    $filter_akses = "u.satker_id IN (1, 2, 3, 4, 5)"; 
} else if ($my_level == 'admin') {
    // Ambil Nama Satker dari tabel satuan_kerja dan atasan_id dari user referensi
    $sql_ref = mysqli_query($conn, "SELECT s.nama_satker, u.atasan_id 
                                     FROM users u 
                                     LEFT JOIN satuan_kerja s ON u.satker_id = s.id 
                                     WHERE u.id = '$my_id'");
    $ref = mysqli_fetch_assoc($sql_ref);
    $target_atasan = $ref['atasan_id'];
    $nama_satker = $ref['nama_satker'] ?? "Satker"; 
    $filter_akses = "u.atasan_id = '$target_atasan'";
} else {
    // Jika Atasan, ambil nama_satker dari tabel satuan_kerja
    $sql_ref = mysqli_query($conn, "SELECT s.nama_satker 
                                     FROM users u 
                                     LEFT JOIN satuan_kerja s ON u.satker_id = s.id 
                                     WHERE u.id = '$my_id'");
    $ref = mysqli_fetch_assoc($sql_ref);
    $nama_satker = $ref['nama_satker'] ?? "Satker";
    $filter_akses = "u.atasan_id = '$my_id'";
}

// Logika Filter (Tetap mempertahankan fungsi asli Anda)
$filter_pegawai = isset($_GET['pegawai']) ? $_GET['pegawai'] : '';
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// FITUR TAMBAHAN: Limit Baris
$limit_pilih = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

include 'includes/header.php';
include 'includes/loader.php';
?>

<style>
    .kemenag-gradient { background: linear-gradient(135deg, #006837 0%, #004d29 100%); color: white; }
    .card-custom { border-radius: 15px; border: none; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
    .page-title { border-bottom: 2px solid rgba(0, 104, 55, 0.1); padding-bottom: 15px; }
    .btn-gold { background-color: #d4af37; color: #fff; border: none; }
    .btn-gold:hover { background-color: #c19b2e; color: #fff; transform: translateY(-1px); }
    .btn-emerald { background-color: #006837; color: white; border: none; }
    .btn-emerald:hover { background-color: #004d29; color: #ffda6a; }
</style>

<div class="container mt-4">
    <div class="row align-items-center mb-4 page-title">
        <div class="col-md-7">
            <h3 class="fw-bold mb-0 text-dark">
                <i class="fas fa-history me-2 text-success"></i>Riwayat Persetujuan
            </h3>
            <p class="text-muted small mb-0">Daftar laporan LKH yang telah disetujui sebelumnya</p>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            <a href="dashboard_admin.php" class="btn btn-white btn-sm border shadow-sm">
                <i class="fas fa-arrow-left me-1 text-secondary"></i> Kembali ke Panel
            </a>
        </div>
    </div>

    <div class="card card-custom mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <input type="hidden" name="limit" value="<?php echo $limit_pilih; ?>">
                
                <div class="col-md-4">
                    <label class="small fw-bold text-muted">Pilih Pegawai</label>
                    <select name="pegawai" class="form-select shadow-sm">
                        <option value="">Semua Pegawai <?php echo $nama_satker; ?></option>
                        <?php
                        // Filter dropdown pegawai sesuai akses satker
                        $u = mysqli_query($conn, "SELECT id, nama_lengkap FROM users u WHERE $filter_akses ORDER BY nama_lengkap ASC");
                        while($user = mysqli_fetch_assoc($u)){
                            $sel = ($filter_pegawai == $user['id']) ? 'selected' : '';
                            echo "<option value='".$user['id']."' $sel>".$user['nama_lengkap']."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold text-muted">Bulan</label>
                    <select name="bulan" class="form-select shadow-sm">
                        <?php
                        for($m=1; $m<=12; $m++){
                            $sel = ($filter_bulan == $m) ? 'selected' : '';
                            echo "<option value='$m' $sel>".date('F', mktime(0,0,0,$m,1))."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold text-muted">Tahun</label>
                    <select name="tahun" class="form-select shadow-sm">
                        <?php
                        for($y=date('Y'); $y>=2023; $y--){
                            $sel = ($filter_tahun == $y) ? 'selected' : '';
                            echo "<option value='$y' $sel>$y</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-emerald w-100 shadow-sm">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-custom shadow-sm border-0 mb-4">
        <div class="card-header kemenag-gradient py-3 d-flex justify-content-between align-items-center border-0">
            <h6 class="mb-0 small fw-bold text-white"><i class="fas fa-clipboard-check me-2"></i>Data LKH Disetujui - <?php echo $nama_satker; ?></h6>
            <span class="badge bg-white text-success shadow-sm px-3 py-2">Status: Disetujui</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted">
                            <th class="ps-3" width="5%">NO</th>
                            <th width="20%">PEGAWAI</th>
                            <th width="15%">TANGGAL</th>
                            <th width="45%">KEGIATAN</th>
                            <th class="text-center" width="15%">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php
                        $no = 1;
                        // Query disesuaikan dengan filter_akses
                        $query = "SELECT l.*, u.nama_lengkap FROM lkh l 
                                  JOIN users u ON l.user_id = u.id 
                                  WHERE l.status='disetujui' AND $filter_akses";
                        
                        if($filter_pegawai != '') $query .= " AND l.user_id = '$filter_pegawai'";
                        if($filter_bulan != '') $query .= " AND MONTH(l.tanggal) = '$filter_bulan'";
                        if($filter_tahun != '') $query .= " AND YEAR(l.tanggal) = '$filter_tahun'";
                        
                        $query .= " ORDER BY l.tanggal DESC LIMIT $limit_pilih";
                        $res = mysqli_query($conn, $query);

                        if(mysqli_num_rows($res) > 0){
                            while($row = mysqli_fetch_assoc($res)){
                                ?>
                                <tr>
                                    <td class="ps-3 text-muted"><?php echo $no++; ?></td>
                                    <td class="fw-bold text-dark"><?php echo $row['nama_lengkap']; ?></td>
                                    <td><span class="badge bg-light text-dark fw-normal"><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></span></td>
                                    <td class="text-wrap"><?php echo nl2br($row['kegiatan']); ?></td>
                                    <td class="text-center">
                                        <button onclick="konfirmasiBatal(<?php echo $row['id']; ?>)" class="btn btn-sm btn-outline-danger shadow-sm">
                                            <i class="fas fa-undo me-1"></i> Batalkan
                                        </button>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center py-5 text-muted'>Tidak ada riwayat persetujuan ditemukan di $nama_satker.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 py-3">
            <div class="d-flex align-items-center">
                <span class="small text-muted me-2">Tampilkan:</span>
                <form method="GET" action="" class="d-flex align-items-center">
                    <input type="hidden" name="pegawai" value="<?php echo $filter_pegawai; ?>">
                    <input type="hidden" name="bulan" value="<?php echo $filter_bulan; ?>">
                    <input type="hidden" name="tahun" value="<?php echo $filter_tahun; ?>">
                    
                    <select name="limit" class="form-select form-select-sm w-auto shadow-sm" onchange="this.form.submit()">
                        <?php
                        $options = [10, 25, 50, 75, 100];
                        foreach($options as $opt) {
                            $selected = ($limit_pilih == $opt) ? 'selected' : '';
                            echo "<option value='$opt' $selected>$opt Baris</option>";
                        }
                        ?>
                    </select>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function konfirmasiBatal(id) {
    Swal.fire({
        title: 'Batalkan Persetujuan?',
        text: "LKH akan dikembalikan ke status 'PROSES'.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Batalkan!',
        cancelButtonText: 'Tutup',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'batal_setuju.php?id=' + id;
        }
    })
}
</script>

<?php include 'includes/footer.php'; ?>