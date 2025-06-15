<?php
include("utils/conectadb.php");
session_start();

$lang = $_COOKIE["lang"] ?? "en";
$labels = include "$lang.php";

if (isset($_SESSION["idusuario"])) {
    $idusuario = $_SESSION["idusuario"];
    $nomeusuario = $_SESSION["nomeusuario"] ?? "Usuário";
} else {
    $alert_msg = $labels["not_logged_in_error"] ?? "Usuário não logado!";
    echo "<script>alert(\'{$alert_msg}\');</script>";
    echo "<script>window.location.href = \'login.php\';</script>";
    exit;
}

// Deleta o registro de veículo
if (isset($_POST["delete_id"])) {
    $delete_id = intval($_POST["delete_id"]);
    $sql = "DELETE FROM veiculo WHERE VEI_ID = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $delete_id);
        if (mysqli_stmt_execute($stmt)) {
            $success_msg = $labels["vehicle_deleted"] ?? "Veículo excluído com sucesso!";
        } else {
            $error_msg = $labels["vehicle_delete_error"] ?? "Erro ao excluir veículo.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Edita o registro selecionado
if (isset($_POST["edit_id"]) && !empty($_POST["edit_id"])) {
    $edit_id = intval($_POST["edit_id"]);
    $edit_modelo = $_POST["vei_modelo"];
    $edit_marca = $_POST["vei_marca"];
    $edit_placa = $_POST["vei_placa"];
    
    $sql = "UPDATE veiculo SET VEI_MODELO = ?, VEI_MARCA = ?, VEI_PLACA = ? WHERE VEI_ID = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "sssi", $edit_modelo, $edit_marca, $edit_placa, $edit_id);
        if (mysqli_stmt_execute($stmt)) {
            $success_msg = $labels["vehicle_updated"] ?? "Veículo atualizado com sucesso!";
        } else {
            $error_msg = $labels["vehicle_update_error"] ?? "Erro ao atualizar veículo.";
        }
        mysqli_stmt_close($stmt);
    }
}
// Insere um novo registro no banco de dados (novo veículo)
elseif ($_SERVER["REQUEST_METHOD"] == "POST" && empty($_POST["edit_id"]) && empty($_POST["delete_id"])) {
    $vei_modelo = $_POST["vei_modelo"];
    $vei_marca = $_POST["vei_marca"];
    $vei_placa = $_POST["vei_placa"];
    
    $sql = "INSERT INTO veiculo (VEI_MODELO, VEI_MARCA, VEI_PLACA) VALUES (?, ?, ?)";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "sss", $vei_modelo, $vei_marca, $vei_placa);
        if (mysqli_stmt_execute($stmt)) {
            $success_msg = $labels["vehicle_registered"] ?? "Veículo cadastrado com sucesso!";
        } else {
            $error_msg = $labels["vehicle_error"] ?? "Erro ao cadastrar veículo.";
        }
        mysqli_stmt_close($stmt);
    }
}


$sql = "SELECT VEI_ID, VEI_MODELO, VEI_MARCA, VEI_PLACA FROM veiculo ORDER BY VEI_ID ASC";
$result = mysqli_query($link, $sql);
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $labels["vehicles_title"] ?? "Veículos"; ?> - MENA Freight Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const defaultSubmitText = '<?php echo addslashes($labels["register_vehicle_btn"] ?? "Cadastrar Veículo"); ?>';
        const editSubmitText = '<?php echo addslashes($labels["edit"] ?? "Editar"); ?>';

        document.querySelectorAll('.edit-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('vei_modelo').value = this.dataset.modelo;
                document.getElementById('vei_marca').value = this.dataset.marca;
                document.getElementById('vei_placa').value = this.dataset.placa;
                document.getElementById('edit_id').value = this.dataset.id;
                document.getElementById('submit-btn').textContent = editSubmitText;
                document.querySelectorAll('#veiculo-form input').forEach(el => el.removeAttribute('disabled'));
            });
        });

        document.getElementById('clear-btn').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('veiculo-form').reset();
            document.getElementById('edit_id').value = '';
            document.getElementById('submit-btn').textContent = defaultSubmitText;
            document.querySelectorAll('#veiculo-form input').forEach(el => el.removeAttribute('disabled'));
        });
    });
    </script>
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
                        <i class="fas fa-truck me-2"></i>
                        <?php echo $labels["vehicle_registration"] ?? "Cadastro de Veículos"; ?>
                    </h1>
                    <p class="text-light-gray"><?php echo $labels["manage_vehicles_description"]; ?></p>
                </div>
            </div>
        </div>

        
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="form-container p-4 rounded shadow-lg">
                    <?php if (isset($success_msg)): ?>
                        <div class="alert alert-success" role="alert"><?php echo $success_msg; ?></div>
                    <?php endif; ?>
                    <?php if (isset($error_msg)): ?>
                        <div class="alert alert-danger" role="alert"><?php echo $error_msg; ?></div>
                    <?php endif; ?>
                    <form method="POST" id="veiculo-form" autocomplete="off">
                        <input type="hidden" name="edit_id" id="edit_id">
                        <div class="mb-3">
                            <label for="vei_modelo" class="form-label text-light">
                                <i class="fas fa-truck-pickup text-danger me-1"></i>
                                <?php echo $labels["vehicle_model_label"] ?? "Modelo:"; ?>
                            </label>
                            <input type="text" id="vei_modelo" name="vei_modelo" class="form-control bg-darker text-light border-secondary" required>
                        </div>
                        <div class="mb-3">
                            <label for="vei_marca" class="form-label text-light">
                                <i class="fas fa-tags text-danger me-1"></i>
                                <?php echo $labels["vehicle_brand_label"] ?? "Marca:"; ?>
                            </label>
                            <input type="text" id="vei_marca" name="vei_marca" class="form-control bg-darker text-light border-secondary" required>
                        </div>
                        <div class="mb-3">
                            <label for="vei_placa" class="form-label text-light">
                                <i class="fas fa-id-card text-danger me-1"></i>
                                <?php echo $labels["vehicle_plate_label"] ?? "Placa:"; ?>
                            </label>
                            <input type="text" id="vei_placa" name="vei_placa" class="form-control bg-darker text-light border-secondary" required>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" id="submit-btn" class="btn btn-danger mt-3">
                                <i class="fas fa-save me-1"></i><?php echo $labels["register_vehicle_btn"] ?? "Cadastrar Veículo"; ?>
                            </button>
                            <button type="button" id="clear-btn" class="btn btn-outline-danger mt-3">
                                <i class="fas fa-eraser me-1"></i><?php echo $labels["clear_btn"] ?? "Limpar"; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        
        <div class="row mt-5">
            <div class="col-12">
                <div class="form-container p-4 rounded shadow-lg">
                    <h3 class="text-danger mb-4 text-center">
                        <i class="fas fa-list me-2"></i><?php echo $labels["registered_vehicles_list"] ?? "Veículos Cadastrados"; ?>
                    </h3>
                    <div class="table-responsive">
                        <table class="table table-dark table-striped table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col"><?php echo $labels["model"] ?? "Modelo"; ?></th>
                                    <th scope="col"><?php echo $labels["brand"] ?? "Marca"; ?></th>
                                    <th scope="col"><?php echo $labels["plate"] ?? "Placa"; ?></th>
                                    <th scope="col"><?php echo $labels["actions"] ?? "Ações"; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo $row["VEI_ID"]; ?></td>
                                    <td><?php echo htmlspecialchars($row["VEI_MODELO"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["VEI_MARCA"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["VEI_PLACA"]); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-warning edit-btn" 
                                            type="button"
                                            data-id="<?php echo $row["VEI_ID"]; ?>" 
                                            data-modelo="<?php echo htmlspecialchars($row["VEI_MODELO"]); ?>" 
                                            data-marca="<?php echo htmlspecialchars($row["VEI_MARCA"]); ?>" 
                                            data-placa="<?php echo htmlspecialchars($row["VEI_PLACA"]); ?>">
                                            <i class="fas fa-edit me-1"></i><?php echo $labels["edit"] ?? "Editar"; ?>
                                        </button>
                                        <form method="POST" class="d-inline" onsubmit="return confirm(\'<?php echo addslashes($labels["confirm_delete_message"] ?? "Tem certeza?"); ?>\')">
                                            <input type="hidden" name="delete_id" value="<?php echo $row["VEI_ID"]; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash-alt me-1"></i><?php echo $labels["delete"] ?? "Excluir"; ?>
                                            </button>
                                        </form>
                                    </td>
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

