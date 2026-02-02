<?php
session_start();
include 'config/koneksi.php';

// Proteksi: Hanya boleh diakses oleh Admin/Atasan/Super Admin
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
    // Admin melihat pegawai yang memiliki atasan yang sama dengannya
    $sql_ref = mysqli_query($conn, "SELECT s.nama_satker, u.atasan_id 
                                     FROM users u 
                                     LEFT JOIN satuan_kerja s ON u.satker_id = s.id 
                                     WHERE u.id = '$my_id'");
    $ref = mysqli_fetch_assoc($sql_ref);
    $target_atasan = $ref['atasan_id'];
    $nama_satker = $ref['nama_satker'] ?? "Satker Tidak Terdefinisi"; 
    $filter_akses = "u.atasan_id = '$target_atasan'";
} else {
    // Jika Atasan, ambil nama_satker dari tabel satuan_kerja dan filter bawahan langsung
    $sql_ref = mysqli_query($conn, "SELECT s.nama_satker 
                                     FROM users u 
                                     LEFT JOIN satuan_kerja s ON u.satker_id = s.id 
                                     WHERE u.id = '$my_id'");
    $ref = mysqli_fetch_assoc($sql_ref);
    $nama_satker = $ref['nama_satker'] ?? "Satker Tidak Terdefinisi";
    $filter_akses = "u.atasan_id = '$my_id'";
}

// Logika Filter (Fungsi asli verifikasi.php)
$filter_pegawai = isset($_GET['pegawai']) ? $_GET['pegawai'] : '';
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Logika Limit dan Pagination (Fungsi asli verifikasi.php)
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$offset = ($halaman - 1) * $limit;

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
    .pagination .page-link { color: #006837; border-radius: 5px; margin: 0 2px; }
    .pagination .page-item.active .page-link { background-color: #006837; border-color: #006837; color: white; }
</style>

<div class="container mt-4">
    <div class="row align-items-center mb-4 page-title">
        <div class="col-md-7">
            <h3 class="fw-bold mb-0 text-dark">
                <i class="fas fa-tasks me-2 text-success"></i>Verifikasi LKH
            </h3>
            <p class="text-muted small mb-0">Filter dan setujui laporan kinerja harian pegawai</p>
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
                <div class="col-md-4">
                    <label class="small fw-bold text-muted">Pilih Pegawai</label>
                    <select name="pegawai" class="form-select shadow-sm">
                        <option value="">Semua Pegawai <?php echo $nama_satker; ?></option>
                        <?php
                        $sql_user = "SELECT id, nama_lengkap FROM users u WHERE $filter_akses ORDER BY nama_lengkap ASC";
                        $u = mysqli_query($conn, $sql_user);
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
                <input type="hidden" name="limit" value="<?php echo $limit; ?>">
            </form>
        </div>
    </div>

    <div class="card card-custom shadow-sm border-0 mb-4">
        <div class="card-header kemenag-gradient py-3 d-flex justify-content-between align-items-center border-0">
            <h6 class="mb-0 small fw-bold text-white"><i class="fas fa-clipboard-check me-2"></i>Hasil Filter LKH - <?php echo $nama_satker; ?></h6>
            <span class="badge bg-white text-success shadow-sm px-3 py-2">Status: Proses</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted">
                            <th class="ps-3" width="20%">PEGAWAI</th>
                            <th width="15%">TANGGAL</th>
                            <th width="35%">KEGIATAN</th>
                            <th width="15%">BUKTI</th>
                            <th class="text-center" width="15%">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php
                        $base_where = "WHERE l.status='proses' AND $filter_akses";
                        
                        $query_total = "SELECT COUNT(*) as total FROM lkh l JOIN users u ON l.user_id = u.id $base_where";
                        if($filter_pegawai != '') $query_total .= " AND l.user_id = '$filter_pegawai'";
                        if($filter_bulan != '') $query_total .= " AND MONTH(l.tanggal) = '$filter_bulan'";
                        if($filter_tahun != '') $query_total .= " AND YEAR(l.tanggal) = '$filter_tahun'";
                        
                        $res_total = mysqli_query($conn, $query_total);
                        $total_data = mysqli_fetch_assoc($res_total)['total'];
                        $total_halaman = ceil($total_data / $limit);

                        $query = "SELECT l.*, u.nama_lengkap FROM lkh l 
                                  JOIN users u ON l.user_id = u.id 
                                  $base_where";
                        
                        if($filter_pegawai != '') $query .= " AND l.user_id = '$filter_pegawai'";
                        if($filter_bulan != '') $query .= " AND MONTH(l.tanggal) = '$filter_bulan'";
                        if($filter_tahun != '') $query .= " AND YEAR(l.tanggal) = '$filter_tahun'";
                        
                        $query .= " ORDER BY l.tanggal DESC LIMIT $offset, $limit";
                        $res = mysqli_query($conn, $query);

                        if(mysqli_num_rows($res) > 0){
                            while($row = mysqli_fetch_assoc($res)){
                                ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-dark"><?php echo $row['nama_lengkap']; ?></td>
                                    <td><span class="badge bg-light text-dark fw-normal"><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></span></td>
                                    <td class="text-wrap"><?php echo $row['kegiatan']; ?></td>
                                    <td>
                                        <div class="btn-group">
                                        <?php if(!empty($row['link_bukti_dukung'])): ?>
                                            <a href="<?php echo $row['link_bukti_dukung']; ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-link"></i></a>
                                        <?php endif; ?>
                                        <?php if(!empty($row['lampiran'])): ?>
                                            <a href="uploads/<?php echo $row['lampiran']; ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-file-alt"></i></a>
                                        <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group shadow-sm border rounded">
                                            <a href="approve_lkh.php?id=<?php echo $row['id']; ?>&status=disetujui" class="btn btn-sm btn-success"><i class="fas fa-check"></i></a>
                                            <a href="approve_lkh.php?id=<?php echo $row['id']; ?>&status=ditolak" class="btn btn-sm btn-danger"><i class="fas fa-times"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center py-5 text-muted'>Data tidak ditemukan di lingkup $nama_satker.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3 border-0">
            <div class="row align-items-center">
                <div class="col-md-6 mb-3 mb-md-0">
                    <form method="GET" action="" class="d-flex align-items-center">
                        <input type="hidden" name="pegawai" value="<?php echo $filter_pegawai; ?>">
                        <input type="hidden" name="bulan" value="<?php echo $filter_bulan; ?>">
                        <input type="hidden" name="tahun" value="<?php echo $filter_tahun; ?>">
                        <span class="small text-muted me-2">Tampilkan:</span>
                        <select name="limit" class="form-select form-select-sm shadow-sm" style="width: auto;" onchange="this.form.submit()">
                            <?php foreach([10, 25, 50, 75, 100] as $opt): ?>
                                <option value="<?php echo $opt; ?>" <?php echo ($limit == $opt) ? 'selected' : ''; ?>><?php echo $opt; ?> baris</option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
                <div class="col-md-6">
                    <?php if($total_halaman > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm justify-content-md-end justify-content-center mb-0">
                            <?php for($i=1; $i<=$total_halaman; $i++): ?>
                                <li class="page-item <?php echo ($halaman == $i) ? 'active' : ''; ?>">
                                    <a class="page-link shadow-sm" href="?pegawai=<?php echo $filter_pegawai; ?>&bulan=<?php echo $filter_bulan; ?>&tahun=<?php echo $filter_tahun; ?>&limit=<?php echo $limit; ?>&halaman=<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>