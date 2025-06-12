<?php

include('utils/conectadb.php'); 
session_start();

if (isset($_SESSION['idusuario'])) {

    $idusuario = $_SESSION['idusuario'];

    $sql = "SELECT USU_NOME FROM usuario 
        WHERE USU_ID = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $idusuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $nomeusuario = mysqli_fetch_array($result)['USU_NOME'] ?? 'Usuário';

    mysqli_stmt_close($stmt);
} 
else {
    echo "<script>alert('Usuário não logado!');</script>";
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}

$lang = $_SESSION["lang"] ?? "en";
$labels = include "$lang.php";

// Definir rótulos para localização
$labels = [
    "welcome" => "Bem-vindo ao Dashboard",
    "logout" => "Sair"
];

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
    <title>Dashboard - MENA Freight Hub</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1><?php echo $labels["welcome"]; ?></h1>
        <nav>
            <a href="cadempresa.php">Empresas</a>
            <a href="#">Veículos</a>
            <a href="#">Orçamentos</a>
            <a href="logout.php"><?php echo $labels["logout"]; ?></a>

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
        <h2>Dashboard</h2>
        <p>Bem-vindo, <?php echo $_SESSION["idusuario"]; ?>!</p>

    <div class="carrossel-container">
        <div class="carrossel">
            <div class="slides">
                <div class="slide">
                    <img src="img/volvo.jpg" height="250px" width="250px" alt="Caminhão">
                </div>
                <div class="slide">
                    <img src="img/scania.jpg" height="250px" width="250px" alt="Caminhão">
                </div>
                <div class="slide">
                    <img src="img/volvo.jpg" height="250px" width="250px" alt="Caminhão">
                </div>
                <div class="slide">
                    <img src="img/scania.jpg" height="250px" width="250px" alt="Caminhão">
                </div>
                <div class="slide">
                    <img src="img/volvo.jpg" height="250px" width="250px" alt="Caminhão">
                </div>
                <div class="slide">
                    <img src="img/scania.jpg" height="250px" width="250px" alt="Caminhão">
                </div>
            </div>
        </div>
    </div>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Empresas Cadastradas</h3>
                <p class="stat-number"><?php echo $stats['empresas']; ?></p>
                <a href="cadempresa.php" class="button">Gerenciar Empresas</a>
            </div>
            
            <div class="stat-card">
                <h3>Veículos Cadastrados</h3>
                <p class="stat-number"><?php echo $stats['veiculos']; ?></p>
                <a href="veiculos/" class="button">Gerenciar Veículos</a>
            </div>
            
            <div class="stat-card">
                <h3>Orçamentos Gerados</h3>
                <p class="stat-number"><?php echo $stats['orcamentos']; ?></p>
                <a href="orcamentos/" class="button">Gerenciar Orçamentos</a>
            </div>
            
            <div class="stat-card">
                <h3>Valor Total</h3>
                <p class="stat-number">$<?php echo number_format($stats['valor_total'], 2); ?></p>
                <a href="orcamentos/" class="button">Ver Relatórios</a>
            </div>
        </div>
        
        <div class="quick-actions">
            <h3>Ações Rápidas</h3>
            <a href="clientes/" class="button">Cadastrar Nova Empresa</a>
            <a href="veiculos/" class="button">Cadastrar Novo Veículo</a>
            <a href="orcamentos/" class="button">Gerar Novo Orçamento</a>
        </div>
    </main>
    <script src="js/language.js"></script>
</body>
</html>

