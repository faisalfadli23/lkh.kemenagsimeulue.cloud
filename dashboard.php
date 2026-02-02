<?php
session_start();
include 'config/koneksi.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['id'];
$nama_user = $_SESSION['nama'];
$level_user = $_SESSION['level'];

// --- LOGIKA FILTER ---
$bulan_pilih = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun_pilih = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$limit_pilih = isset($_GET['limit']) ? $_GET['limit'] : 10; // Default 10 rows

// AMBIL DATA UNTUK GRAFIK (Disesuaikan agar warna sinkron dengan status)
$sql_chart = mysqli_query($conn, "SELECT status, COUNT(*) as jumlah FROM lkh 
             WHERE user_id='$user_id' AND MONTH(tanggal)='$bulan_pilih' AND YEAR(tanggal)='$tahun_pilih' 
             GROUP BY status ORDER BY FIELD(status, 'disetujui', 'proses', 'ditolak') ASC");

$labels = [];
$counts = [];
$colors = [];

// Map warna sesuai instruksi
$color_map = [
    'disetujui' => '#198754', // Hijau
    'proses'    => '#ffc107', // Kuning
    'ditolak'   => '#dc3545'  // Merah
];

while($row = mysqli_fetch_assoc($sql_chart)){
    $labels[] = strtoupper($row['status']);
    $counts[] = $row['jumlah'];
    $colors[] = isset($color_map[$row['status']]) ? $color_map[$row['status']] : '#6c757d';
}

include 'includes/header.php';
include 'includes/loader.php';
?>

<style>
    .kemenag-gradient { background: linear-gradient(135deg, #006837 0%, #004d29 100%); color: white; }
    .btn-emerald { background-color: #006837; color: white; border: none; }
    .btn-emerald:hover { background-color: #004d29; color: #d4af37; }
    .btn-gold { background-color: #d4af37; color: #fff; border: none; }
    .btn-gold:hover { background-color: #c19b2e; color: #fff; }
    .stats-card { border-left: 5px solid #d4af37; }
    .table thead { background-color: #004d29; color: #d4af37; }
    .page-title { border-bottom: 2px solid #dee2e6; padding-bottom: 10px; }
    
    .btn-white-custom { background-color: #ffffff; color: #333; border: 1px solid #dee2e6; }
    .btn-white-custom:hover { background-color: #f8f9fa; color: #006837; border-color: #006837; }
</style>

<div class="container mt-2">
    <div class="row align-items-center mb-4 page-title">
        <div class="col-md-7">
            <h3 class="fw-bold mb-0">
                <i class="fas fa-user-circle me-2 text-success"></i>LKH Pribadi: <?php echo $nama_user; ?>
            </h3>
            <p class="text-muted small mb-0">Laporan Kinerja Harian (LKH) Online</p>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            <?php if($level_user != 'pegawai') : ?>
                <a href="dashboard_admin.php" class="btn btn-outline-dark btn-sm shadow-sm me-1">
                    <i class="fas fa-user-shield me-1"></i> Panel Admin
                </a>
            <?php endif; ?>
            
            <a href="profil.php" class="btn btn-outline-secondary btn-sm shadow-sm me-1">
                <i class="fas fa-user me-1"></i> Profil
            </a>
            
            <a href="laporan.php" target="_blank" class="btn btn-outline-info btn-sm shadow-sm me-1">
                <i class="fas fa-print me-1"></i> Cetak LKH
            </a>
            
            <a href="logout.php" class="btn btn-outline-danger btn-sm shadow-sm" onclick="return confirm('Keluar dari aplikasi?')">
                <i class="fas fa-sign-out-alt"></i> Keluar
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header kemenag-gradient py-3">
                    <h6 class="mb-0 small fw-bold"><i class="fas fa-filter me-2"></i>Filter Periode</h6>
                </div>
                <div class="card-body">
                    <form method="GET" id="formFilter">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Bulan & Tahun</label>
                            <div class="input-group input-group-sm">
                                <select name="bulan" id="bulan" class="form-select">
                                    <?php
                                    $nama_bulan = ['01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus', '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'];
                                    foreach($nama_bulan as $m => $nama) {
                                        $selected = ($m == $bulan_pilih) ? 'selected' : '';
                                        echo "<option value='$m' $selected>$nama</option>";
                                    }
                                    ?>
                                </select>
                                <select name="tahun" id="tahun" class="form-select">
                                    <?php
                                    for($y = date('Y'); $y >= 2023; $y--) {
                                        $selected = ($y == $tahun_pilih) ? 'selected' : '';
                                        echo "<option value='$y' $selected>$y</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Atasan Penandatangan</label>
                            <select name="atasan_id" id="atasan_id" class="form-select form-select-sm" required>
                                <option value="">-- Pilih Atasan --</option>
                                <?php
                                $atasan_query = mysqli_query($conn, "SELECT id, nama_lengkap, jabatan FROM users WHERE level = 'atasan' ORDER BY nama_lengkap ASC");
                                while($at = mysqli_fetch_assoc($atasan_query)) {
                                    $selected_at = (isset($_GET['atasan_id']) && $_GET['atasan_id'] == $at['id']) ? 'selected' : '';
                                    echo "<option value='".$at['id']."' $selected_at>".$at['nama_lengkap']." (".$at['jabatan'].")</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <input type="hidden" name="limit" value="<?php echo $limit_pilih; ?>">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-emerald btn-sm">Tampilkan Data</button>
                            <button type="button" onclick="exportKeExcel()" class="btn btn-gold btn-sm">
                                <i class="fas fa-file-excel me-1"></i> Export ke Excel
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0 stats-card">
                <div class="card-body">
                    <h6 class="fw-bold small mb-3 text-muted text-center">PERSENTASE STATUS LKH</h6>
                    <div style="height: 220px;">
                        <canvas id="myChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Riwayat Kegiatan</h5>
                <a href="input_lkh.php" class="btn btn-emerald btn-sm shadow-sm">
                    <i class="fas fa-plus-circle me-1"></i> Tambah LKH Baru
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3 py-3" width="5%">No</th>
                                    <th width="15%">Tanggal</th>
                                    <th>Uraian Kegiatan</th>
                                    <th width="15%">Status</th>
                                    <th class="text-center" width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM lkh WHERE user_id = '$user_id' AND MONTH(tanggal) = '$bulan_pilih' AND YEAR(tanggal) = '$tahun_pilih' ORDER BY tanggal DESC LIMIT $limit_pilih");
                                
                                if(mysqli_num_rows($query) == 0) {
                                    echo "<tr><td colspan='5' class='text-center py-5 text-muted'>Belum ada laporan pada periode ini.</td></tr>";
                                }

                                while($data = mysqli_fetch_array($query)){
                                ?>
                                <tr>
                                    <td class="ps-3"><?php echo $no++; ?></td>
                                    <td class="small fw-bold text-muted"><?php echo date('d/m/Y', strtotime($data['tanggal'])); ?></td>
                                    <td class="small"><?php echo $data['kegiatan']; ?></td>
                                    <td>
                                        <?php 
                                            $badge = 'bg-warning text-dark';
                                            if($data['status'] == 'disetujui') $badge = 'bg-success';
                                            elseif($data['status'] == 'ditolak') $badge = 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $badge; ?>" style="font-size: 0.7rem;">
                                            <?php echo strtoupper($data['status']); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <?php if($data['status'] == 'proses') : ?>
                                                <a href="edit_lkh.php?id=<?php echo $data['id']; ?>" class="btn btn-sm btn-outline-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                            <?php endif; ?>

                                            <?php if($data['status'] == 'proses' || $data['status'] == 'ditolak') : ?>
                                                <a href="hapus_lkh.php?id=<?php echo $data['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus data ini?')" title="Hapus"><i class="fas fa-trash"></i></a>
                                            <?php else : ?>
                                                <i class="fas fa-lock text-muted" title="Sudah diverifikasi"></i>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="d-flex align-items-center">
                    <span class="small text-muted me-2">Tampilkan:</span>
                    <form method="GET" action="" class="d-inline-block">
                        <input type="hidden" name="bulan" value="<?php echo $bulan_pilih; ?>">
                        <input type="hidden" name="tahun" value="<?php echo $tahun_pilih; ?>">
                        <?php if(isset($_GET['atasan_id'])): ?>
                            <input type="hidden" name="atasan_id" value="<?php echo $_GET['atasan_id']; ?>">
                        <?php endif; ?>
                        
                        <select name="limit" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                            <?php 
                            $options = [25, 50, 75, 100];
                            foreach($options as $opt) {
                                $selected = ($opt == $limit_pilih) ? 'selected' : '';
                                echo "<option value='$opt' $selected>$opt Baris</option>";
                            }
                            ?>
                        </select>
                    </form>
                </div>
                <div class="text-end">
                     <p class="small text-muted mb-0"><i class="fas fa-info-circle me-1"></i> LKH yang sudah <b>Disetujui</b> tidak dapat diubah/dihapus.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportKeExcel() {
    var bulan = document.getElementById('bulan').value;
    var tahun = document.getElementById('tahun').value;
    var atasan = document.getElementById('atasan_id').value;
    if(atasan == "") {
        alert("Pilih Atasan terlebih dahulu!");
        document.getElementById('atasan_id').focus();
        return;
    }
    window.location.href = 'export_excel.php?bulan=' + bulan + '&tahun=' + tahun + '&atasan_id=' + atasan;
}

const ctx = document.getElementById('myChart');
if (ctx) {
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                data: <?php echo json_encode($counts); ?>,
                backgroundColor: <?php echo json_encode($colors); ?>,
                borderWidth: 0
            }]
        },
        options: {
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } }
            }
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>