<?php
include("utils/conectadb.php");
session_start();

$lang = $_COOKIE["lang"] ?? "en";
$labels = include "$lang.php";

if (!isset($_SESSION['idusuario'])) {
    $alert_msg = $labels["not_logged_in_error"] ?? 'Usuário não logado!';
    echo "<script>alert('{$alert_msg}');</script>";
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}

// Busca orçamentos cadastrados
$sql = "SELECT o.*, e.emp_nome, v.VEI_MODELO FROM orcamento o
        JOIN empresa e ON o.ORC_FK_EMPRESA_ID = e.emp_id
        JOIN veiculo v ON o.ORC_FK_VEICULO_ID = v.VEI_ID
        ORDER BY o.ORC_ID DESC";
$res_orc = mysqli_query($link, $sql);
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $labels["budgets_title"] ?? "Orçamentos"; ?> - MENA Freight Hub</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Estilos para os cards de orçamento */
        .orcamento-cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
        }
        .orcamento-card {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .orcamento-card h4 {
            margin-top: 0;
            margin-bottom: 1rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 0.5rem;
            color: #0d6efd;
        }
        .orc-info {
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        .orc-label {
            font-weight: 600;
            color: #333;
        }
        .orc-valor {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
            font-size: 1.5rem;
            font-weight: 700;
            text-align: right;
            color: #198754;
        }
    </style>
</head>
<body style="background-size: cover; background-attachment: fixed; background-image: url('img/marcasp.png');">
<header>
    <h1><?php echo $labels["budgets_title"] ?? "Orçamentos"; ?></h1>
    <nav>
        <a href="dashboard.php">Dashboard</a>
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
    <h2><?php echo $labels["registered_budgets_list"] ?? "Orçamentos Cadastrados"; ?></h2>
    <div class="orcamento-cards-container">
        <?php if(mysqli_num_rows($res_orc) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($res_orc)): ?>
                <div class="orcamento-card">
                    <h4>#<?php echo $row["ORC_ID"]; ?> - <?php echo htmlspecialchars($row["emp_nome"]); ?></h4>
                    <div class="orc-info">
                        <span class="orc-label"><?php echo $labels["vehicle"] ?? "Veículo"; ?>:</span>
                        <?php echo htmlspecialchars($row["VEI_MODELO"]); ?>
                    </div>
                    <div class="orc-info">
                        <span class="orc-label"><?php echo $labels["origin"] ?? "Origem"; ?>:</span>
                        <?php echo htmlspecialchars($row["ORC_ORIGEM"]); ?>
                    </div>
                    <div class="orc-info">
                        <span class="orc-label"><?php echo $labels["destination"] ?? "Destino"; ?>:</span>
                        <?php echo htmlspecialchars($row["ORC_DESTINO"]); ?>
                    </div>
                    <div class="orc-info">
                        <span class="orc-label"><?php echo $labels["start_date"] ?? "Data Início"; ?>:</span>
                        <?php echo htmlspecialchars(date("d/m/Y", strtotime($row["ORC_DATAINICIO"]))); ?>
                    </div>
                    <div class="orc-info">
                        <span class="orc-label"><?php echo $labels["end_date"] ?? "Data Fim"; ?>:</span>
                        <?php echo htmlspecialchars(date("d/m/Y", strtotime($row["ORC_DATAFIM"]))); ?>
                    </div>
                    <div class="orc-valor">
                        USD <?php echo number_format($row["ORC_VALOR"], 2, ',', '.'); ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p><?php echo $labels['no_budgets_found'] ?? 'Nenhum orçamento encontrado.'; ?></p>
        <?php endif; ?>
    </div>
</main>
<script src="js/language.js"></script>
</body>
</html>