<?php
include("utils/conectadb.php");
session_start();

$lang = $_COOKIE["lang"] ?? "en";
$labels = include "$lang.php";

if (!isset($_SESSION["idusuario"])) {
    $alert_msg = $labels["not_logged_in_error"] ?? "Usuário não logado!";
    echo "<script>alert(\'{$alert_msg}\');</script>";
    echo "<script>window.location.href = \'login.php\';</script>";
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-dark text-light">
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-darker border-bottom border-danger">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
                <i class="fas fa-truck text-danger me-2 fs-4"></i>
                <span class="text-danger fw-bold fs-4">MENA Freight Hub</span>
            </a>
            
            <div class="navbar-nav ms-auto d-flex flex-row align-items-center">
                <a href="dashboard.php" class="btn btn-outline-light me-3">
                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                </a>
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
                <script>
                function setLanguage(lang) {
                    document.cookie = "lang=" + lang + ";path=/";
                    location.reload();
                }
                </script>
            </div>
        </div>
    </nav>

    
    <div class="container-fluid py-4">
      
        <div class="row mb-4">
            <div class="col-12">
                <div class="page-header text-center">
                    <h1 class="text-danger mb-2">
                        <i class="fas fa-file-invoice-dollar me-2"></i>
                        <?php echo $labels["registered_budgets_list"] ?? "Orçamentos Cadastrados"; ?>
                    </h1>
                    <p class="text-light-gray"><?php echo $labels["view_all_budgets_description"]; ?></p>
                </div>
            </div>
        </div>

       
        <div class="row mt-5">
            <div class="col-12">
                <div class="form-container p-4 rounded shadow-lg">
                    <div class="table-responsive">
                        <table class="table table-dark table-striped table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col"><?php echo $labels["header_company"] ?? "Empresa"; ?></th>
                                    <th scope="col"><?php echo $labels["header_vehicle"] ?? "Veículo"; ?></th>
                                    <th scope="col"><?php echo $labels["header_origin"] ?? "Origem"; ?></th>
                                    <th scope="col"><?php echo $labels["header_destination"] ?? "Destino"; ?></th>
                                    <th scope="col"><?php echo $labels["header_start_date"] ?? "Data Início"; ?></th>
                                    <th scope="col"><?php echo $labels["header_end_date"] ?? "Data Fim"; ?></th>
                                    <th scope="col"><?php echo $labels["header_value"] ?? "Valor (USD)"; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($res_orc)): ?>
                                <tr>
                                    <td><?php echo $row["ORC_ID"]; ?></td>
                                    <td><?php echo htmlspecialchars($row["emp_nome"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["VEI_MODELO"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["ORC_ORIGEM"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["ORC_DESTINO"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["ORC_DATAINICIO"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["ORC_DATAFIM"]); ?></td>
                                    <td><?php echo number_format($row["ORC_VALOR"], 2); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/language.js"></script>
</body>
</html>

