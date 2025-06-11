<?php
session_start();
include_once("config_demo.php");

$lang = $_SESSION["lang"] ?? "en";
$labels = include "lang/$lang.php";

// Basic authentication (for demonstration purposes)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    if ($email == "test@example.com" && $password == "password") {
        $_SESSION["loggedin"] = true;
        $_SESSION["email"] = $email;
    } else {
        $login_err = "Invalid email or password.";
    }
}

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.html");
    exit;
}

// Get demo statistics
$stats = [];
$stats['empresas'] = count($_SESSION['demo_empresas']);
$stats['veiculos'] = count($_SESSION['demo_veiculos']);
$stats['orcamentos'] = count($_SESSION['demo_orcamentos']);

$total = 0;
foreach ($_SESSION['demo_orcamentos'] as $orc) {
    $total += $orc['valor'];
}
$stats['valor_total'] = $total;
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MENA Freight Hub</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header>
        <h1><?php echo $labels["welcome"]; ?></h1>
        <nav>
            <a href="demo_empresas.php">Empresas</a>
            <a href="demo_veiculos.php">Veículos</a>
            <a href="demo_orcamentos.php">Orçamentos</a>
            <a href="logout.php"><?php echo $labels["logout"]; ?></a>
        </nav>
    </header>
    <main>
        <h2>Dashboard - Versão Demonstração</h2>
        <p>Bem-vindo, <?php echo $_SESSION["email"]; ?>!</p>
        
        <div class="language-selector">
            <a href="#" onclick="setLanguage('en')">EN</a> |
            <a href="#" onclick="setLanguage('ar')">AR</a> |
            <a href="#" onclick="setLanguage('fr')">FR</a>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Empresas Cadastradas</h3>
                <p class="stat-number"><?php echo $stats['empresas']; ?></p>
                <a href="demo_empresas.php" class="button">Gerenciar Empresas</a>
            </div>
            
            <div class="stat-card">
                <h3>Veículos Cadastrados</h3>
                <p class="stat-number"><?php echo $stats['veiculos']; ?></p>
                <a href="demo_veiculos.php" class="button">Gerenciar Veículos</a>
            </div>
            
            <div class="stat-card">
                <h3>Orçamentos Gerados</h3>
                <p class="stat-number"><?php echo $stats['orcamentos']; ?></p>
                <a href="demo_orcamentos.php" class="button">Gerenciar Orçamentos</a>
            </div>
            
            <div class="stat-card">
                <h3>Valor Total</h3>
                <p class="stat-number">$<?php echo number_format($stats['valor_total'], 2); ?></p>
                <a href="demo_orcamentos.php" class="button">Ver Relatórios</a>
            </div>
        </div>
        
        <div class="quick-actions">
            <h3>Ações Rápidas</h3>
            <a href="demo_empresas.php" class="button">Cadastrar Nova Empresa</a>
            <a href="demo_veiculos.php" class="button">Cadastrar Novo Veículo</a>
            <a href="demo_orcamentos.php" class="button">Gerar Novo Orçamento</a>
        </div>
        
        <div class="demo-notice">
            <h3>Nota sobre a Demonstração</h3>
            <p>Esta é uma versão de demonstração que utiliza dados simulados em memória. Em um ambiente de produção, os dados seriam armazenados em um banco de dados MySQL conforme especificado no projeto.</p>
            <p>Credenciais de teste: email: test@example.com, senha: password</p>
        </div>
    </main>
    <script src="scripts/language.js"></script>
</body>
</html>

