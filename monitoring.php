<?php
session_start();
include 'config/koneksi.php';

// Proteksi: Hanya admin/atasan yang bisa mengakses monitoring
if (!isset($_SESSION['id']) || $_SESSION['level'] == 'pegawai') {
    header("Location: dashboard.php");
    exit;
}

$my_id = $_SESSION['id'];
$my_level = $_SESSION['level'];

/** * LOGIKA FILTER AKSES SATKER (Disamakan dengan dashboard_admin & riwayat) */
if ($my_id == 1 || $my_level == 'super_admin') { 
    // Khusus Kepala Kantor atau Super Admin
    $nama_satker = "Seluruh Satuan Kerja";
    $filter_akses = "u.satker_id IN (1, 2, 3, 4, 5)"; 
} else if ($my_level == 'admin') {
    // Ambil referensi dari atasan_id di user
    $sql_ref = mysqli_query($conn, "SELECT s.nama_satker, u.atasan_id 
                                     FROM users u 
                                     LEFT JOIN satuan_kerja s ON u.satker_id = s.id 
                                     WHERE u.id = '$my_id'");
    $ref = mysqli_fetch_assoc($sql_ref);
    $target_atasan = $ref['atasan_id'];
    $nama_satker = $ref['nama_satker'] ?? "Satker"; 
    $filter_akses = "u.atasan_id = '$target_atasan'";
} else {
    // Jika Atasan (User Biasa dengan level atasan)
    $sql_ref = mysqli_query($conn, "SELECT s.nama_satker FROM users u 
                                     LEFT JOIN satuan_kerja s ON u.satker_id = s.id 
                                     WHERE u.id = '$my_id'");
    $ref = mysqli_fetch_assoc($sql_ref);
    $nama_satker = $ref['nama_satker'] ?? "Satker";
    $filter_akses = "u.atasan_id = '$my_id'";
}

// --- LOGIKA FILTER WAKTU ---
$bulan_pilih = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun_pilih = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// FITUR TAMBAHAN: Limit Data
$limit_pilih = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

$nama_bulan = [
    '01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', '05'=>'Mei', '06'=>'Juni',
    '07'=>'Juli', '08'=>'Agustus', '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'
];

include 'includes/header.php';
include 'includes/loader.php';
?>

<style>
    .kemenag-gradient { background: linear-gradient(135deg, #006837 0%, #004d29 100%); color: white; }
    .card-custom { border-radius: 15px; border: none; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
    .page-title { border-bottom: 2px solid rgba(0, 104, 55, 0.1); padding-bottom: 15px; }
    .btn-emerald { background-color: #006837; color: white; border: none; transition: all 0.3s; }
    .btn-emerald:hover { background-color: #004d29; color: #ffda6a; transform: translateY(-2px); }

    /* Style Leaderboard */
    .card-leaderboard { transition: all 0.3s; border: none; border-radius: 15px; border-left: 5px solid #eee; }
    .card-leaderboard:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
    
    .avatar-circle { 
        width: 50px; height: 50px; 
        background: linear-gradient(135deg, #006837, #d4af37); 
        border-radius: 50%; display: flex; align-items: center; justify-content: center; 
        font-weight: bold; color: white; font-size: 1.2rem;
    }
    
    .rank-badge { 
        width: 32px; height: 32px; display: flex; align-items: center; 
        justify-content: center; border-radius: 50%; background: #f8f9fa; 
        font-weight: bold; border: 2px solid #dee2e6; font-size: 0.85rem;
    }
    
    .gold { background: #ffd700; color: #000; border-color: #e6c200; }
    .silver { background: #c0c0c0; color: #000; border-color: #a9a9a9; }
    .bronze { background: #cd7f32; color: #fff; border-color: #b87333; }
    
    .progress-custom { height: 10px; border-radius: 10px; background-color: #f0f0f0; }
</style>

<div class="container mt-4">
    <div class="row align-items-center mb-4 page-title">
        <div class="col-md-7">
            <h3 class="fw-bold mb-0 text-dark">
                <i class="fas fa-trophy me-2 text-success"></i>Monitoring & Leaderboard
            </h3>
            <p class="text-muted small mb-0">Statistik laporan disetujui - <strong><?= $nama_satker ?></strong> (<?= $nama_bulan[$bulan_pilih] ?> <?= $tahun_pilih ?>)</p>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            <a href="dashboard_admin.php" class="btn btn-white btn-sm border shadow-sm px-3">
                <i class="fas fa-arrow-left me-1 text-secondary"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card card-custom mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <input type="hidden" name="limit" value="<?= $limit_pilih ?>">
                <div class="col-md-5">
                    <label class="small fw-bold text-muted">Bulan</label>
                    <select name="bulan" class="form-select shadow-sm">
                        <?php foreach($nama_bulan as $m => $nama) : ?>
                            <option value="<?= $m ?>" <?= ($m == $bulan_pilih) ? 'selected' : '' ?>><?= $nama ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="small fw-bold text-muted">Tahun</label>
                    <select name="tahun" class="form-select shadow-sm">
                        <?php for($y = date('Y'); $y >= 2023; $y--) : ?>
                            <option value="<?= $y ?>" <?= ($y == $tahun_pilih) ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
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

    <div class="row">
        <?php
        // Tambahkan filter_akses ke dalam query utama
        $query = mysqli_query($conn, "SELECT u.id, u.nama_lengkap, u.jabatan, u.level,
                 (SELECT COUNT(*) FROM lkh WHERE user_id = u.id AND status = 'disetujui' AND MONTH(tanggal) = '$bulan_pilih' AND YEAR(tanggal) = '$tahun_pilih') as total_lkh
                 FROM users u 
                 WHERE $filter_akses
                 ORDER BY total_lkh DESC, u.nama_lengkap ASC LIMIT $limit_pilih");

        $rank = 1;
        if(mysqli_num_rows($query) > 0) :
            while($row = mysqli_fetch_assoc($query)):
                $target = 22; 
                $persen = ($target > 0) ? ($row['total_lkh'] / $target) * 100 : 0;
                if($persen > 100) $persen = 100;
                
                $color_class = "bg-danger";
                $border_color = "#dc3545";
                if($persen >= 50) { $color_class = "bg-warning"; $border_color = "#ffc107"; }
                if($persen >= 85) { $color_class = "bg-success"; $border_color = "#198754"; }

                $rank_style = "";
                if($rank == 1 && $row['total_lkh'] > 0) $rank_style = "gold";
                elseif($rank == 2 && $row['total_lkh'] > 0) $rank_style = "silver";
                elseif($rank == 3 && $row['total_lkh'] > 0) $rank_style = "bronze";
        ?>
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm card-leaderboard" style="border-left-color: <?= ($rank <= 3 && $row['total_lkh'] > 0) ? '#d4af37' : $border_color ?>;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div class="rank-badge <?= $rank_style ?>"><?= $rank++; ?></div>
                        </div>
                        <div class="avatar-circle me-3 shadow-sm">
                            <?= strtoupper(substr($row['nama_lengkap'] ?? 'U', 0, 1)); ?>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">
                                        <?= $row['nama_lengkap']; ?>
                                        <?php if($row['level'] != 'pegawai'): ?>
                                            <span class="badge bg-light text-primary border ms-1" style="font-size: 0.6rem;"><?= strtoupper($row['level']); ?></span>
                                        <?php endif; ?>
                                    </h6>
                                    <small class="text-muted small"><?= $row['jabatan'] ?? 'Staf'; ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="badge <?= $color_class ?> rounded-pill shadow-sm"><?= round($persen); ?>%</span>
                                </div>
                            </div>
                            
                            <div class="progress progress-custom mt-3">
                                <div class="progress-bar <?= $color_class ?> progress-bar-striped progress-bar-animated" 
                                     role="progressbar" style="width: <?= $persen; ?>%"></div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-2">
                                <small class="text-muted" style="font-size: 0.7rem;">
                                    Disetujui: <span class="fw-bold text-dark"><?= $row['total_lkh']; ?></span> / <?= $target; ?> Hari
                                </small>
                                <?php if($row['total_lkh'] >= $target) : ?>
                                    <small class="text-success fw-bold" style="font-size: 0.7rem;">
                                        <i class="fas fa-check-circle"></i> Selesai
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-user-slash fa-3x text-light mb-3"></i>
                <p class="text-muted">Tidak ada data pengguna di satker ini.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="d-flex justify-content-center mb-5">
        <div class="card card-custom shadow-sm px-3 py-2">
            <div class="d-flex align-items-center">
                <span class="small text-muted me-2">Tampilkan:</span>
                <form method="GET" action="" class="d-flex align-items-center">
                    <input type="hidden" name="bulan" value="<?= $bulan_pilih ?>">
                    <input type="hidden" name="tahun" value="<?= $tahun_pilih ?>">
                    <select name="limit" class="form-select form-select-sm w-auto shadow-sm" onchange="this.form.submit()">
                        <?php
                        $options = [10, 20, 30, 40, 50];
                        foreach($options as $opt) {
                            $selected = ($limit_pilih == $opt) ? 'selected' : '';
                            echo "<option value='$opt' $selected>$opt Peringkat</option>";
                        }
                        ?>
                    </select>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>