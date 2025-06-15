<?php
$lang = $_COOKIE["lang"] ?? "en";
$labels = include "$lang.php";
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $labels['home_title']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-dark text-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-darker border-bottom border-danger">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <i class="fas fa-truck text-danger me-2 fs-3"></i>
                <span class="text-danger fw-bold fs-3">MENA Freight Hub</span>
            </a>
            
            <div class="navbar-nav ms-auto d-flex flex-row align-items-center">
                <a href="login.php" class="btn btn-outline-danger me-3">
                    <i class="fas fa-sign-in-alt me-1"></i><?php echo $labels['login']; ?>
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

    
    <section class="hero-section py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold text-danger mb-4">
                        <?php echo $labels['hero_title']; ?>
                    </h1>
                    <p class="lead mb-4 text-light-gray">
                        <?php echo $labels['hero_subtitle']; ?>
                    </p>
                    <a href="login.php" class="btn btn-danger btn-lg px-4 py-3">
                        <i class="fas fa-rocket me-2"></i><?php echo $labels['hero_button']; ?>
                    </a>
                </div>
                <div class="col-lg-6">
                    <!-- Carrossel de ícones -->
                    <div id="iconCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <div class="text-center py-5">
                                    <i class="fas fa-truck icon-carousel text-danger"></i>
                                    <h5 class="mt-3 text-light"><?php echo $labels["road_transport_carousel"]; ?></h5>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="text-center py-5">
                                    <i class="fas fa-shipping-fast icon-carousel text-danger"></i>
                                    <h5 class="mt-3 text-light"><?php echo $labels["express_delivery_carousel"]; ?></h5>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="text-center py-5">
                                    <i class="fas fa-warehouse icon-carousel text-danger"></i>
                                    <h5 class="mt-3 text-light"><?php echo $labels["fleet_management_carousel"]; ?></h5>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="text-center py-5">
                                    <i class="fas fa-route icon-carousel text-danger"></i>
                                    <h5 class="mt-3 text-light"><?php echo $labels["route_optimization_carousel"]; ?></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
    <section class="features-section py-5 bg-darker">
        <div class="container">
            <h2 class="text-center text-danger mb-5 fw-bold">
                <i class="fas fa-star me-2"></i><?php echo $labels['features_title']; ?>
            </h2>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="feature-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-building feature-icon text-danger mb-3"></i>
                            <h5 class="card-title text-light"><?php echo $labels['feature_1']; ?></h5>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="feature-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-truck-moving feature-icon text-danger mb-3"></i>
                            <h5 class="card-title text-light"><?php echo $labels['feature_2']; ?></h5>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="feature-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-calculator feature-icon text-danger mb-3"></i>
                            <h5 class="card-title text-light"><?php echo $labels['feature_3']; ?></h5>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="feature-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-language feature-icon text-danger mb-3"></i>
                            <h5 class="card-title text-light"><?php echo $labels['feature_4']; ?></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
    <section class="contact-section py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h3 class="text-danger mb-4">
                        <i class="fas fa-envelope me-2"></i><?php echo $labels['contact_title']; ?>
                    </h3>
                    <p class="text-light-gray mb-4"><?php echo $labels['contact_subtitle']; ?></p>
                    <div class="contact-info">
                        <i class="fas fa-envelope text-danger me-2"></i>
                        <span class="text-light"><?php echo $labels['contact_email']; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
    <footer class="bg-darker border-top border-danger py-4">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="mb-0 text-light-gray"><?php echo $labels['footer_copyright']; ?></p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/language.js"></script>
</body>
</html>