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
$nama_user = $_SESSION['nama'];
$bulan_ini = date('m');
$tahun_ini = date('Y');

/** * LOGIKA FILTER AKSES SATKER & NAMA SATKER: */
// Menambahkan kondisi $my_level == 'super_admin' sesuai perintah
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
    $nama_satker = $ref['nama_satker']; 
    $filter_akses = "u.atasan_id = '$target_atasan'";
} else {
    // Jika Atasan, ambil nama_satker dari tabel satuan_kerja
    $sql_ref = mysqli_query($conn, "SELECT s.nama_satker 
                                     FROM users u 
                                     LEFT JOIN satuan_kerja s ON u.satker_id = s.id 
                                     WHERE u.id = '$my_id'");
    $ref = mysqli_fetch_assoc($sql_ref);
    $nama_satker = $ref['nama_satker'];
    $filter_akses = "u.atasan_id = '$my_id'";
}

// Ambil nilai limit dari URL, default adalah 10
$limit_pilih = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

// --- DATA UNTUK GRAFIK (DIFILTER AKSES) ---
$sql_grafik = "SELECT u.nama_lengkap, 
                COUNT(CASE WHEN l.status = 'disetujui' AND MONTH(l.tanggal) = '$bulan_ini' THEN 1 END) as setuju,
                COUNT(CASE WHEN l.status = 'proses' AND MONTH(l.tanggal) = '$bulan_ini' THEN 1 END) as proses
                FROM users u
                LEFT JOIN lkh l ON u.id = l.user_id
                WHERE $filter_akses
                GROUP BY u.id 
                ORDER BY setuju DESC";
$res_grafik = mysqli_query($conn, $sql_grafik);
$names = []; $data_setuju = []; $data_proses = [];
while($row = mysqli_fetch_assoc($res_grafik)) {
    $names[] = $row['nama_lengkap'];
    $data_setuju[] = $row['setuju'];
    $data_proses[] = $row['proses'];
}

// --- DATA RINGKASAN (DIFILTER AKSES) ---
$total_pegawai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users u WHERE $filter_akses"))['total'];
$pending_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM lkh l JOIN users u ON l.user_id = u.id WHERE l.status='proses' AND $filter_akses"))['total'];
$laporan_hari_ini = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM lkh l JOIN users u ON l.user_id = u.id WHERE l.tanggal=CURDATE() AND $filter_akses"))['total'];

include 'includes/header.php';
include 'includes/loader.php';
?>

<style>
    .kemenag-gradient { background: linear-gradient(135deg, #006837 0%, #004d29 100%); color: white; }
    .btn-emerald { background-color: #006837; color: white; border: none; }
    .btn-emerald:hover { background-color: #004d29; color: #d4af37; }
    .btn-gold { background-color: #d4af37; color: #fff; border: none; }
    .btn-gold:hover { background-color: #c19b2e; color: #fff; }
    .page-title { border-bottom: 2px solid #dee2e6; padding-bottom: 10px; }
    .card-custom { border-radius: 12px; border: none; }
    .stats-box { border-left: 4px solid #d4af37; }
    .hover-effect { transition: all 0.3s ease; border-radius: 12px !important; }
    .hover-effect:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
</style>

<div class="container mt-2">
    <div class="row align-items-center mb-4 page-title">
        <div class="col-md-7">
            <h3 class="fw-bold mb-0">
                <i class="fas fa-chart-line me-2 text-success"></i>Panel <?php echo $nama_satker; ?>
            </h3>
            <p class="text-muted small mb-0">Monitoring & Verifikasi LKH Pegawai</p>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            <a href="dashboard.php" class="btn btn-outline-success btn-sm shadow-sm me-1">
                <i class="fas fa-tasks me-1"></i> LKH Pribadi
            </a>
            <a href="profil.php" class="btn btn-outline-secondary btn-sm shadow-sm me-1">
                <i class="fas fa-user-cog me-1"></i> Profil
            </a>
            <a href="logout.php" class="btn btn-outline-danger btn-sm shadow-sm" onclick="return confirm('Keluar dari aplikasi?')">
                <i class="fas fa-sign-out-alt me-1"></i> Keluar
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card card-custom shadow-sm stats-box h-100">
                <div class="card-body">
                    <h6 class="text-muted small fw-bold">TOTAL PEGAWAI SATKER</h6>
                    <h2 class="fw-bold mb-0 text-dark"><?php echo $total_pegawai; ?> <small class="fs-6 fw-normal">Orang</small></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom shadow-sm border-start border-4 border-warning h-100">
                <div class="card-body">
                    <h6 class="text-muted small fw-bold">MENUNGGU VERIFIKASI</h6>
                    <h2 class="fw-bold mb-0 text-warning"><?php echo $pending_total; ?> <small class="fs-6 fw-normal">Laporan</small></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom shadow-sm border-start border-4 border-success h-100">
                <div class="card-body">
                    <h6 class="text-muted small fw-bold">MASUK HARI INI</h6>
                    <h2 class="fw-bold mb-0 text-success"><?php echo $laporan_hari_ini; ?> <small class="fs-6 fw-normal">Laporan</small></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-custom shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold text-center text-muted">PERBANDINGAN KINERJA <?php echo strtoupper($nama_satker); ?> (BULAN INI)</h6>
        </div>
        <div class="card-body">
            <div style="position: relative; height: 300px;">
                <canvas id="chartPimpinan"></canvas>
            </div>
        </div>
    </div>

    <div class="card card-custom shadow-sm border-0 mb-4">
        <div class="card-header kemenag-gradient py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 small fw-bold"><i class="fas fa-hourglass-half me-2"></i>Daftar Tunggu Verifikasi Satker</h6>
            
            <?php if($pending_total > 0): ?>
            <a href="approve_all.php" class="btn btn-sm btn-gold px-3 shadow-sm" onclick="return confirm('Apakah Anda yakin ingin menyetujui SEMUA laporan bulan ini sekaligus?')">
                <i class="fas fa-check-double me-1"></i> Setujui Semua
            </a>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted">
                            <th class="ps-3">PEGAWAI</th>
                            <th>TANGGAL</th>
                            <th>KEGIATAN</th>
                            <th>BUKTI</th>
                            <th class="text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php
                        $q_pending = mysqli_query($conn, "SELECT l.*, u.nama_lengkap FROM lkh l 
                                                         JOIN users u ON l.user_id = u.id 
                                                         WHERE l.status='proses' AND $filter_akses
                                                         ORDER BY l.tanggal DESC LIMIT $limit_pilih");
                        if(mysqli_num_rows($q_pending) > 0){
                            while($dp = mysqli_fetch_assoc($q_pending)){
                                ?>
                                <tr>
                                    <td class="ps-3 fw-bold"><?php echo $dp['nama_lengkap']; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($dp['tanggal'])); ?></td>
                                    <td><?php echo $dp['kegiatan']; ?></td>
                                    <td>
                                        <div class="btn-group">
                                        <?php if(!empty($dp['link_bukti_dukung'])): ?>
                                            <a href="<?php echo $dp['link_bukti_dukung']; ?>" target="_blank" class="btn btn-sm btn-outline-info" title="Link Drive"><i class="fas fa-link"></i></a>
                                        <?php endif; ?>
                                        <?php if(!empty($dp['lampiran'])): ?>
                                            <a href="uploads/<?php echo $dp['lampiran']; ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="File"><i class="fas fa-file-alt"></i></a>
                                        <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group shadow-sm">
                                            <a href="approve_lkh.php?id=<?php echo $dp['id']; ?>&status=disetujui" class="btn btn-sm btn-success" title="Setujui"><i class="fas fa-check"></i></a>
                                            <a href="approve_lkh.php?id=<?php echo $dp['id']; ?>&status=ditolak" class="btn btn-sm btn-danger" title="Tolak"><i class="fas fa-times"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center py-5 text-muted'>Alhamdulillah, semua laporan sudah diverifikasi.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 py-3">
            <div class="d-flex align-items-center justify-content-start">
                <span class="small text-muted me-2">Tampilkan:</span>
                <form method="GET" action="" class="d-flex align-items-center">
                    <select name="limit" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        <?php
                        $options = [25, 50, 75, 100];
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

    <div class="row g-3">
        <div class="col-md-3">
            <a href="verifikasi.php" class="card text-decoration-none shadow-sm border-0 hover-effect h-100 text-center py-4">
                <i class="fas fa-check-circle fa-2x text-warning mb-2"></i>
                <h6 class="text-dark small fw-bold mb-0">Verifikasi Filter</h6>
            </a>
        </div>
        <div class="col-md-3">
            <a href="riwayat_persetujuan.php" class="card text-decoration-none shadow-sm border-0 hover-effect h-100 text-center py-4">
                <i class="fas fa-history fa-2x text-danger mb-2"></i>
                <h6 class="text-dark small fw-bold mb-0">Riwayat & Batal</h6>
            </a>
        </div>
        <div class="col-md-3">
            <a href="monitoring.php" class="card text-decoration-none shadow-sm border-0 hover-effect h-100 text-center py-4">
                <i class="fas fa-trophy fa-2x mb-2 text-warning"></i>
                <h6 class="text-dark small fw-bold mb-0">Leaderboard</h6>
            </a>
        </div>
        <div class="col-md-3">
            <a href="laporan.php" class="card text-decoration-none shadow-sm border-0 hover-effect h-100 text-center py-4">
                <i class="fas fa-print fa-2x text-info mb-2"></i>
                <h6 class="text-dark small fw-bold mb-0">Cetak Rekap LKH</h6>
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('chartPimpinan').getContext('2d');
    
    const gradientSetuju = ctx.createLinearGradient(0, 0, 400, 0);
    gradientSetuju.addColorStop(0, '#006837');
    gradientSetuju.addColorStop(1, '#198754');

    const gradientProses = ctx.createLinearGradient(0, 0, 400, 0);
    gradientProses.addColorStop(0, '#d4af37');
    gradientProses.addColorStop(1, '#ffc107');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($names); ?>,
            datasets: [
                { 
                    label: 'Disetujui', 
                    data: <?php echo json_encode($data_setuju); ?>, 
                    backgroundColor: gradientSetuju,
                    hoverBackgroundColor: '#004d29',
                    borderRadius: 10,
                    barThickness: 15
                },
                { 
                    label: 'Proses', 
                    data: <?php echo json_encode($data_proses); ?>, 
                    backgroundColor: gradientProses,
                    hoverBackgroundColor: '#c19b2e',
                    borderRadius: 10,
                    barThickness: 15
                }
            ]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: { 
                    position: 'top',
                    align: 'end',
                    labels: { 
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 20,
                        font: { size: 12, family: 'Poppins, sans-serif' } 
                    } 
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#333',
                    bodyColor: '#666',
                    borderColor: '#dee2e6',
                    borderWidth: 1,
                    padding: 12,
                    boxPadding: 8,
                    usePointStyle: true
                }
            },
            scales: {
                x: { 
                    stacked: true,
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                },
                y: { 
                    stacked: true,
                    grid: { color: '#f8f9fa' },
                    ticks: { 
                        font: { size: 12, weight: '500' },
                        color: '#444'
                    }
                }
            }
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>