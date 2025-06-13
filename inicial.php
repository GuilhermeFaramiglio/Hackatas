<?php
// Lê o idioma do cookie, com 'en' como padrão
$lang = $_COOKIE["lang"] ?? "en";
// Inclui o arquivo de tradução correspondente
$labels = include "$lang.php";
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $labels['home_title']; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>MENA Freight Hub</h1>
        <nav>
            <a href="login.php"><?php echo $labels['login']; ?></a>
            
            <div class="seletor-idioma">
                <div class="idioma-atual">
                    <span><?php echo strtoupper($lang); ?></span>
                    <svg width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 1L6 6L11 1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="menu-idiomas">
                    <a href="#" onclick="setLanguage('en')"><img src="img/eng.webp" height="25px" width="25px">EN</a>
                    <a href="#" onclick="setLanguage('ar')"><img src="img/arabe.webp" height="25px" width="25px">AR</a>
                    <a href="#" onclick="setLanguage('fr')"><img src="img/France_Flag.PNG.webp" height="25px" width="25px">FR</a>
                </div>
            </div>
        </nav>
    </header>
    <main>
        <section id="hero">
            <h2><?php echo $labels['hero_title']; ?></h2>
            <p><?php echo $labels['hero_subtitle']; ?></p>
            <a href="login.php" class="button"><?php echo $labels['hero_button']; ?></a>
        </section>
        <section id="features">
            <h3><?php echo $labels['features_title']; ?></h3>
            <ul>
                <li><?php echo $labels['feature_1']; ?></li>
                <li><?php echo $labels['feature_2']; ?></li>
                <li><?php echo $labels['feature_3']; ?></li>
                <li><?php echo $labels['feature_4']; ?></li>
            </ul>
        </section>
        <section id="contact">
            <h3><?php echo $labels['contact_title']; ?></h3>
            <p><?php echo $labels['contact_subtitle']; ?></p>
            <p>Email: info@menafreighthub.com</p>
        </section>
    </main>
    <footer>
        <p><?php echo $labels['footer_copyright']; ?></p>
    </footer>

    <script src="js/language.js"></script>
</body>
</html>