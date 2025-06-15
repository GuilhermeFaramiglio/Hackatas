<?php

include('utils/conectadb.php');
session_start();

if (isset($_SESSION['idusuario']) && isset($_SESSION['nomeusuario'])) {
    // Pega as informações diretamente da sessão, sem precisar de nova consulta ao banco
    $idusuario = $_SESSION['idusuario'];
    $nomeusuario = $_SESSION['nomeusuario'];
}  else {
    echo "<script>alert('Usuário não logado!');</script>";
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}

// Carrega o idioma a partir do cookie, com 'en' como padrão
$lang = $_COOKIE["lang"] ?? "en";
$labels = include "$lang.php";

// Obter algumas estatísticas
$stats = [];

// Contar empresas
$result = mysqli_query($link, "SELECT COUNT(*) as count FROM empresa");
$stats['empresas'] = mysqli_fetch_array($result)['count'];

// Contar veículos
$result = mysqli_query($link, "SELECT COUNT(*) as count FROM veiculo");
$stats['veiculos'] = mysqli_fetch_array($result)['count'];

// Contar orçamentos
$result = mysqli_query($link, "SELECT COUNT(*) as count FROM orcamento");
$stats['orcamentos'] = mysqli_fetch_array($result)['count'];

// Valor total dos orçamentos
$result = mysqli_query($link, "SELECT SUM(ORC_VALOR) as total FROM orcamento");
$stats['valor_total'] = mysqli_fetch_array($result)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $labels['dashboard']; ?> - MENA Freight Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
</head>
<body class="bg-dark">
    <!-- Header moderno -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-darker border-bottom border-danger">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <i class="fas fa-truck text-danger me-2 fs-4"></i>
                <span class="text-danger fw-bold fs-4">MENA Freight Hub</span>
            </a>
            
            <div class="navbar-nav ms-auto d-flex flex-row align-items-center">
                <span class="text-light me-3">
                    <i class="fas fa-user-circle text-danger me-1"></i>
                    <?php echo htmlspecialchars($nomeusuario); ?>
                </span>
                <a href="logout.php" class="btn btn-outline-danger me-3">
                    <i class="fas fa-sign-out-alt me-1"></i><?php echo $labels["logout"]; ?>
                </a>
                
                <div class="dropdown">
                    <button class="btn btn-danger dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown">
                        <i class="fas fa-globe me-1"></i><?php echo strtoupper($lang); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark bg-darker">
                        <li><a class="dropdown-item" href="#" onclick="setLanguage('en')">
                            <img src="img/eng.webp" height="20" width="20" class="me-2">EN
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="setLanguage('ar')">
                            <img src="img/arabe.webp" height="20" width="20" class="me-2">AR
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="setLanguage('fr')">
                            <img src="img/France_Flag.PNG.webp" height="20" width="20" class="me-2">FR
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="container-fluid py-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="welcome-section text-center">
                    <h1 class="text-danger mb-2">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        <?php echo $labels["dashboard"]; ?>
                    </h1>
                    <p class="text-light-gray lead">
                        <?php echo $labels["welcome_user"]; ?>, <?php echo htmlspecialchars($nomeusuario); ?>!
                    </p>
                </div>
            </div>
        </div>

        <!-- Vehicle Icons Carousel -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="swiper vehicle-carousel">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <div class="text-center py-4">
                                <i class="fas fa-truck vehicle-icon text-danger"></i>
                                <h5 class="mt-3 text-light"><?php echo $labels["trucks_carousel"]; ?></h5>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="text-center py-4">
                                <i class="fas fa-shipping-fast vehicle-icon text-danger"></i>
                                <h5 class="mt-3 text-light"><?php echo $labels["express_delivery_carousel"]; ?></h5>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="text-center py-4">
                                <i class="fas fa-truck-moving vehicle-icon text-danger"></i>
                                <h5 class="mt-3 text-light"><?php echo $labels["transport_carousel"]; ?></h5>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="text-center py-4">
                                <i class="fas fa-dolly vehicle-icon text-danger"></i>
                                <h5 class="mt-3 text-light"><?php echo $labels["cargo_carousel"]; ?></h5>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="text-center py-4">
                                <i class="fas fa-warehouse vehicle-icon text-danger"></i>
                                <h5 class="mt-3 text-light"><?php echo $labels["warehouse_carousel"]; ?></h5>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="text-center py-4">
                                <i class="fas fa-route vehicle-icon text-danger"></i>
                                <h5 class="mt-3 text-light"><?php echo $labels["routes_carousel"]; ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-pagination"></div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-5">
            <div class="col-lg-3 col-md-6">
                <div class="stat-card h-100">
                    <div class="card-body text-center">
                        <div class="stat-icon mb-3">
                            <i class="fas fa-building text-danger"></i>
                        </div>
                        <h5 class="card-title text-light"><?php echo $labels["registered_companies"]; ?></h5>
                        <p class="stat-number text-danger"><?php echo $stats['empresas']; ?></p>
                        <a href="cadempresa.php" class="btn btn-danger btn-sm">
                            <i class="fas fa-cog me-1"></i><?php echo $labels["manage_companies"]; ?>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="stat-card h-100">
                    <div class="card-body text-center">
                        <div class="stat-icon mb-3">
                            <i class="fas fa-truck text-danger"></i>
                        </div>
                        <h5 class="card-title text-light"><?php echo $labels["registered_vehicles"]; ?></h5>
                        <p class="stat-number text-danger"><?php echo $stats['veiculos']; ?></p>
                        <a href="cadveiculo.php" class="btn btn-danger btn-sm">
                            <i class="fas fa-wrench me-1"></i><?php echo $labels["manage_vehicles"]; ?>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="stat-card h-100">
                    <div class="card-body text-center">
                        <div class="stat-icon mb-3">
                            <i class="fas fa-file-invoice-dollar text-danger"></i>
                        </div>
                        <h5 class="card-title text-light"><?php echo $labels["generated_quotes"]; ?></h5>
                        <p class="stat-number text-danger"><?php echo $stats['orcamentos']; ?></p>
                        <a href="cadorcamento.php" class="btn btn-danger btn-sm">
                            <i class="fas fa-edit me-1"></i><?php echo $labels["manage_quotes"]; ?>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="stat-card h-100">
                    <div class="card-body text-center">
                        <div class="stat-icon mb-3">
                            <i class="fas fa-dollar-sign text-danger"></i>
                        </div>
                        <h5 class="card-title text-light"><?php echo $labels["total_value"]; ?></h5>
                        <p class="stat-number text-danger">$<?php echo number_format($stats['valor_total'], 2); ?></p>
                        <a href="listaorcamentos.php" class="btn btn-danger btn-sm">
                            <i class="fas fa-chart-line me-1"></i><?php echo $labels["view_reports"]; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="quick-actions-section">
                    <h3 class="text-danger mb-4 text-center">
                        <i class="fas fa-bolt me-2"></i><?php echo $labels["quick_actions"]; ?>
                    </h3>
                    <div class="row g-3">
                        <div class="col-lg-4 col-md-6">
                            <a href="cadempresa.php" class="btn btn-outline-danger btn-lg w-100 quick-action-btn">
                                <i class="fas fa-plus-circle me-2"></i>
                                <?php echo $labels["add_new_company"]; ?>
                            </a>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <a href="cadveiculo.php" class="btn btn-outline-danger btn-lg w-100 quick-action-btn">
                                <i class="fas fa-truck me-2"></i>
                                <?php echo $labels["add_new_vehicle"]; ?>
                            </a>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <a href="cadorcamento.php" class="btn btn-outline-danger btn-lg w-100 quick-action-btn">
                                <i class="fas fa-calculator me-2"></i>
                                <?php echo $labels["add_new_quote"]; ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="js/language.js"></script>
    <script>
        const swiper = new Swiper('.vehicle-carousel', {
            loop: true,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            effect: 'coverflow',
            grabCursor: true,
            centeredSlides: true,
            slidesPerView: 'auto',
            coverflowEffect: {
                rotate: 50,
                stretch: 0,
                depth: 100,
                modifier: 1,
                slideShadows: true,
            },
        });
    </script>
</body>
</html>