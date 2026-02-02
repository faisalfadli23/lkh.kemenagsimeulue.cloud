<?php
session_start();
include 'config/koneksi.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['id'];

// PANGGIL HEADER
include 'includes/header.php';
include 'includes/loader.php';
?>

<style>
    /* Sinkronisasi tema Emerald & Gold */
    body { background-color: #f4f7f6; }
    .kemenag-gradient { background: linear-gradient(135deg, #006837 0%, #004d29 100%); color: white; }
    .card-custom { border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.08); overflow: hidden; }
    
    .input-icon-area {
        width: 80px; height: 80px;
        background: linear-gradient(135deg, #d4af37, #b8860b);
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
    .input-group-custom:focus-within { border-color: #006837; background-color: #fff; box-shadow: 0 0 0 0.25rem rgba(0, 104, 55, 0.05); }
    .input-group-custom input, .input-group-custom select, .input-group-custom textarea { background: transparent; border: none; padding: 10px 5px; font-size: 0.9rem; width: 100%; }
    .input-group-custom input:focus, .input-group-custom textarea:focus { outline: none; box-shadow: none; }
    .input-group-custom i { color: #adb5bd; width: 25px; text-align: center; }
    
    .info-box { background-color: #e8f4fd; border-left: 4px solid #3498db; border-radius: 8px; padding: 12px; }
    
    /* Style Tambahan untuk Fitur Import */
    .btn-import { background: #fff; color: #004d29; border: 2px dashed #004d29; border-radius: 10px; padding: 10px; font-weight: 600; transition: all 0.3s; }
    .btn-import:hover { background: #fff9e6; color: #b8860b; border-color: #b8860b; }
</style>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="dashboard.php" class="btn btn-sm text-muted text-decoration-none">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
                <button type="button" class="btn btn-sm btn-import shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImport">
                    <i class="fas fa-file-excel me-2"></i>Import Sekaligus (Excel/CSV)
                </button>
            </div>

            <div class="card card-custom mt-4">
                <div class="card-header kemenag-gradient" style="height: 80px; border: none;"></div>
                
                <div class="card-body p-4 p-lg-5 pt-0">
                    
                    <div class="input-icon-area">
                        <i class="fas fa-edit"></i>
                    </div>

                    <div class="text-center mb-4">
                        <h4 class="fw-bold text-dark mb-1">Input LKH</h4>
                        <p class="text-muted small">Catat aktivitas harian Anda secara akurat</p>
                    </div>

                    <form action="simpan_lkh.php" method="POST" enctype="multipart/form-data" id="formLKH">
                        
                        <div class="mb-4">
                            <label class="form-label">Tanggal Kegiatan</label>
                            <div class="d-flex align-items-center input-group-custom">
                                <i class="fas fa-calendar-alt"></i>
                                <input type="date" name="tanggal" value="<?= date('Y-m-d'); ?>" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Uraian Tugas / Deskripsi Kegiatan</label>
                            <div class="d-flex align-items-start input-group-custom">
                                <i class="fas fa-tasks mt-3"></i>
                                <input list="list_kegiatan" name="kegiatan" id="input_kegiatan" placeholder="Pilih atau ketik uraian tugas..." required autocomplete="off">
                                <datalist id="list_kegiatan">
                                    <?php
                                    $sql_kegiatan = mysqli_query($conn, "SELECT DISTINCT kegiatan FROM lkh WHERE user_id = '$user_id' ORDER BY kegiatan ASC");
                                    if(mysqli_num_rows($sql_kegiatan) > 0) {
                                        while($row = mysqli_fetch_assoc($sql_kegiatan)) {
                                            echo "<option value='".$row['kegiatan']."'>";
                                        }
                                    } else {
                                        echo '<option value="Mengelola administrasi persuratan dan kearsipan">
                                              <option value="Menyusun draf laporan capaian kinerja">
                                              <option value="Melakukan koordinasi data dengan bagian keuangan">';
                                    }
                                    ?>
                                </datalist>
                            </div>
                            <div class="info-box mt-2">
                                <small class="text-primary" style="font-size: 0.75rem;">
                                    <i class="fas fa-info-circle me-1"></i> Gunakan kalimat yang konsisten agar sistem dapat menjumlahkan volume otomatis.
                                </small>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Hasil / Satuan Output</label>
                            <div class="d-flex align-items-center input-group-custom">
                                <i class="fas fa-file-alt"></i>
                                <input type="text" name="hasil_kegiatan" placeholder="Contoh: Berkas, Dokumen, Laporan" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Bukti Dukung (Link Cloud)</label>
                            <div class="d-flex align-items-center input-group-custom">
                                <i class="fas fa-link"></i>
                                <input type="url" name="link_bukti_dukung" placeholder="https://drive.google.com/...">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Upload Bukti Fisik (Opsional)</label>
                            <div class="d-flex align-items-center input-group-custom bg-light border-dashed">
                                <i class="fas fa-upload"></i>
                                <input type="file" name="lampiran" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <small class="text-muted" style="font-size: 0.65rem;">Format: PDF, JPG, PNG (Maks 2MB)</small>
                        </div>

                        <div class="d-grid gap-2 mt-5">
                            <button type="submit" class="btn btn-emerald shadow-sm">
                                <i class="fas fa-save me-2"></i> Simpan Laporan Kinerja
                            </button>
                        </div>

                    </form>
                </div>
            </div>

            <p class="text-center mt-4 text-muted small">Pastikan data yang Anda input sudah sesuai sebelum menyimpan.</p>
        </div>
    </div>
</div>

<div class="modal fade" id="modalImport" tabindex="-1" aria-labelledby="modalImportLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="modalImportLabel"><i class="fas fa-file-import text-success me-2"></i>Import Massal LKH</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="proses_import_lkh.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div class="alert alert-warning border-0 small mb-4" style="background-color: #fff9e6;">
                        <i class="fas fa-info-circle me-2 text-warning"></i>
                        Gunakan file <strong>Excel (.xlsx)</strong> atau <strong>CSV</strong> sesuai format kolom: <br>
                        <code class="ms-4">Tanggal | Kegiatan | Hasil | Link Bukti</code>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Pilih File Laporan</label>
                        <div class="input-group-custom d-flex align-items-center">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <input type="file" name="file_lkh" accept=".xlsx, .xls, .csv" required>
                        </div>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="export_excel.php" class="text-decoration-none small fw-bold text-success">
                            <i class="fas fa-download me-1"></i> Download Format Template Excel
                        </a>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-3 fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="import" class="btn btn-emerald rounded-3 px-4 shadow-sm">Mulai Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Merapikan input otomatis
document.getElementById('input_kegiatan').addEventListener('blur', function() {
    this.value = this.value.trim().replace(/\s+/g, ' ');
});
</script>

<?php include 'includes/footer.php'; ?>