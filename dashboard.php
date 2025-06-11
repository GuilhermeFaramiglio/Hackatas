<?php
session_start();
include_once("utils/conectadb.php");

$lang = $_SESSION["lang"] ?? "en";
$labels = include "lang/$lang.php";

// Basic authentication (for demonstration purposes)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    // In a real application, you would query a database for user credentials
    // and use password hashing.
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

// Get some statistics
$stats = [];

// Count companies
$result = mysqli_query($link, "SELECT COUNT(*) as count FROM empresa");
$stats['empresas'] = mysqli_fetch_array($result)['count'];

// Count vehicles
$result = mysqli_query($link, "SELECT COUNT(*) as count FROM veiculo");
$stats['veiculos'] = mysqli_fetch_array($result)['count'];

// Count quotes
$result = mysqli_query($link, "SELECT COUNT(*) as count FROM orcamento");
$stats['orcamentos'] = mysqli_fetch_array($result)['count'];

// Total value of quotes
$result = mysqli_query($link, "SELECT SUM(valor) as total FROM orcamento");
$stats['valor_total'] = mysqli_fetch_array($result)['total'] ?? 0;
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
            <a href="clientes/">Empresas</a>
            <a href="veiculos/">Veículos</a>
            <a href="orcamentos/">Orçamentos</a>
            <a href="logout.php"><?php echo $labels["logout"]; ?></a>
        </nav>
    </header>
    <main>
        <h2>Dashboard</h2>
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
                <a href="clientes/" class="button">Gerenciar Empresas</a>
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
    <script src="scripts/language.js"></script>
</body>
</html>

