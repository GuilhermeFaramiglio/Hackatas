<?php
include("utils/conectadb.php");
session_start();

$lang = $_COOKIE["lang"] ?? "en";
$labels = include "$lang.php";

if (!isset($_SESSION['idusuario'])) {
    echo "<script>alert('Usuário não logado!');</script>";
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
    <title>Orçamentos - MENA Freight Hub</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <h1><?php echo $labels["budget"] ?? "Orçamentos"; ?></h1>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php"><?php echo $labels["logout"]; ?></a>
    </nav>
</header>
<main>
    <h2><?php echo $labels["registered_budgets_list"] ?? "Orçamentos Cadastrados"; ?></h2>
    <div class="orcamento-cards-container">
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
                    <?php echo htmlspecialchars($row["ORC_DATAINICIO"]); ?>
                </div>
                <div class="orc-info">
                    <span class="orc-label"><?php echo $labels["end_date"] ?? "Data Fim"; ?>:</span>
                    <?php echo htmlspecialchars($row["ORC_DATAFIM"]); ?>
                </div>
                <div class="orc-valor">
                    USD <?php echo number_format($row["ORC_VALOR"], 2); ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</main>
</body>
</html>
