<?php
// Bagian redirect dinonaktifkan
include 'includes/header.php';
include 'includes/loader.php';
?>

<script>
    // Mengubah judul browser menjadi Beranda
    document.title = "Beranda | LKH Kemenag Simeulue";
</script>

<style>
    /* CSS hanya untuk area konten spesifik (Hero Section) */
    .section-lkh-landing {
        min-height: 80vh;
        display: flex;
        align-items: center;
        background-color: #ffffff;
    }

    .brand-group {
        display: flex;
        align-items: center;
        margin-bottom: 25px;
    }

    .brand-logo-custom {
        height: 65px; 
        margin-right: 15px;
        object-fit: contain;
    }

    .brand-divider {
        padding-left: 0; 
    }

    /* Judul LKH Online (Atas) & Kemenag Simeulue (Bawah) */
    .title-main {
        color: #004d29; /* Emerald Dark */
        font-weight: 700;
        margin-bottom: 0;
        line-height: 1.2;
    }

    .subtitle-gold {
        color: #d4af37; /* Emerald Gold */
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        margin-top: 2px;
    }

    /* Gambar Landing Statis & Tajam */
    .img-landing-static {
        max-width: 95%;
        height: auto;
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
        opacity: 1;
    }

    /* Tombol Masuk - Shadow dipertegas sedikit lagi */
    .btn-outline-custom {
        background-color: transparent;
        color: #004d29;
        padding: 12px 35px;
        border-radius: 10px;
        font-weight: bold;
        /* Border tetap 1.5px samar */
        border: 1.5px solid rgba(0, 77, 41, 0.3); 
        /* Shadow dipertegas (naik ke 0.18 agar lebih solid di layar) */
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.18);
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s ease-in-out;
    }

    /* Hover Tombol */
    .btn-outline-custom:hover {
        background-color: transparent;
        color: #d4af37; 
        border-color: rgba(212, 175, 55, 0.5); 
        /* Shadow saat hover juga dipertegas */
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.22);
        transform: translateY(-2px); 
    }

    .content-column {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }
</style>

<div class="section-lkh-landing">
    <div class="container">
        <div class="row align-items-center justify-content-center">
            
            <div class="col-lg-5 offset-lg-1 mb-5 mb-lg-0">
                <div class="content-column">
                    <div class="brand-group">
                        <img src="assets/logo1.png" alt="Logo Kemenag" class="brand-logo-custom">
                        <div class="brand-divider">
                            <h1 class="title-main">LKH Online</h1>
                            <h3 class="subtitle-gold">Kemenag Simeulue</h3>
                        </div>
                    </div>

                    <p class="lead text-muted mb-4">
                        <strong>Selamat datang!</strong> di sistem pelaporan kinerja harian pegawai di lingkungan 
                        <strong>Kantor Kementerian Agama Kabupaten Simeulue</strong>
                    </p>
                    
                    <a href="login.php" class="btn-outline-custom">
                        <i class="fas fa-sign-in-alt me-2"></i> Silahkan Login
                    </a>
                </div>
            </div>

            <div class="col-lg-6">
                <img src="assets/landing_page.png" alt="LKH Dashboard" class="img-landing-static">
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>