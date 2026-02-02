<style>
    #loader-wrapper {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background-color: #fff;
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        transition: opacity 0.5s ease;
    }

    .loader-content { text-align: center; }

    /* Animasi Spinner Khas Kemenag (Hijau) */
    .custom-spinner {
        width: 50px; height: 50px;
        border: 5px solid #f3f3f3;
        border-top: 5px solid #006837; /* Warna Emerald */
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 15px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<div id="loader-wrapper">
    <div class="loader-content">
        <div class="custom-spinner"></div>
        <p class="text-muted small fw-bold">Memuat Halaman...</p>
    </div>
</div>

<script>
    window.addEventListener('load', function() {
        const loader = document.getElementById('loader-wrapper');
        loader.style.opacity = '0';
        setTimeout(() => {
            loader.style.display = 'none';
        }, 500);
    });
</script>