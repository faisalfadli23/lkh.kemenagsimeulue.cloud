<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/logo1.png">
    <title>
        <?php 
            $current_page = basename($_SERVER['PHP_SELF'], ".php"); 
            $title = str_replace(['_', '-'], ' ', $current_page);
            $title = ucwords($title);

            if ($title == "Index") { $title = "Login"; }
            
            echo $title . " | LKH Kemenag Simeulue"; 
        ?>
    </title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
    body { 
        background-color: #f4f7f6; 
        font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; 
    }
    
    /* MODIFIKASI: Ultra-Clear Liquid Glass iPhone Style */
    .navbar { 
        /* Background dibuat lebih transparan agar lebih bening */
        background: rgba(255, 255, 255, 0.15); 
        backdrop-filter: blur(25px) saturate(180%) contrast(90%);
        -webkit-backdrop-filter: blur(25px) saturate(180%) contrast(90%);
        
        /* Garis bawah tipis sesuai perintah awal */
        border-bottom: 1px solid rgba(212, 175, 55, 0.25); 
        padding: 1rem 0;
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        
        /* Highlight halus di bagian atas untuk efek liquid */
        background-image: linear-gradient(
            to bottom, 
            rgba(255, 255, 255, 0.2) 0%, 
            rgba(255, 255, 255, 0.02) 100%
        );
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03); 
    }

    /* Kondisi saat di-scroll */
    .navbar.scrolled {
        background: rgba(255, 255, 255, 0.3); 
        padding: 0.6rem 0;
        backdrop-filter: blur(35px) saturate(210%);
        border-bottom: 1px solid rgba(212, 175, 55, 0.4);
        box-shadow: 0 8px 32px rgba(0,0,0,0.06);
    }

    .filter-shadow {
        filter: drop-shadow(0px 2px 4px rgba(0,0,0,0.2));
    }

    .navbar-brand {
        font-size: 1.15rem;
        letter-spacing: 0.5px;
    }

    .brand-subtext {
        font-size: 0.7rem;
        display: block;
        font-weight: 500;
        color: #d4af37; 
        letter-spacing: 1.2px;
    }

    .user-profile-nav {
        /* User box dibuat sedikit glass juga agar serasi */
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(5px);
        padding: 6px 18px;
        border-radius: 50px;
        border: 1px solid rgba(0, 104, 55, 0.15);
        transition: all 0.3s ease;
    }

    .brand-text span:first-child, .user-profile-nav strong {
        color: #006837; 
    }

    .card { border-radius: 12px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="dashboard">
            <img src="assets/logo1.png" alt="Logo" width="45" height="45" class="me-3 filter-shadow">
            <div class="brand-text">
                <span class="d-block">LKH Online</span>
                <span class="brand-subtext text-uppercase">Kemenag Simeulue</span>
            </div>
        </a>
        
        <div class="ms-auto">
            <div class="user-profile-nav d-flex align-items-center">
                <i class="fas fa-user-circle fa-lg me-2 text-success"></i>
                <div class="small">
                    <span class="text-muted d-block" style="font-size: 0.65rem;">Assalamu'alaikum,</span>
                    <strong class="text-dark"><?php echo isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Sahabat Religi!'; ?></strong>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
window.addEventListener('scroll', function() {
    const nav = document.querySelector('.navbar');
    if (window.scrollY > 40) {
        nav.classList.add('scrolled');
    } else {
        nav.classList.remove('scrolled');
    }
});
</script>
</body>
</html>