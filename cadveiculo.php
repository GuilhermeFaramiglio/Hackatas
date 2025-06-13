<?php
include("utils/conectadb.php");
session_start();

$lang = $_COOKIE["lang"] ?? "en";
$labels = include "$lang.php";

if (isset($_SESSION['idusuario'])) {
    $idusuario = $_SESSION['idusuario'];
    $nomeusuario = $_SESSION['nomeusuario'] ?? 'Usuário';
} else {
    $alert_msg = $labels["not_logged_in_error"] ?? 'Usuário não logado!';
    echo "<script>alert('{$alert_msg}');</script>";
    echo "<script>window.location.href = 'login.php';</script>";
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
    .
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
<body style="background-size: cover; background-attachment: fixed; background-image: url('img/marcasp.png');">
<header>
    <h1><?php echo $labels["vehicles_title"] ?? "Veículos"; ?></h1>
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
    <h2><?php echo $labels["vehicle_registration"] ?? "Cadastro de Veículos"; ?></h2>

    <?php if (isset($success_msg)): ?>
        <div class="success"><?php echo $success_msg; ?></div>
    <?php endif; ?>

    <?php if (isset($error_msg)): ?>
        <div class="error"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <form method="POST" id="veiculo-form" autocomplete="off">
        <input type="hidden" name="edit_id" id="edit_id">
        <div class="form-group">
            <label for="vei_modelo"><?php echo $labels["vehicle_model_label"] ?? "Modelo:"; ?></label>
            <input type="text" id="vei_modelo" name="vei_modelo" required>
        </div>
        <div class="form-group">
            <label for="vei_marca"><?php echo $labels["vehicle_brand_label"] ?? "Marca:"; ?></label>
            <input type="text" id="vei_marca" name="vei_marca" required>
        </div>
        <div class="form-group">
            <label for="vei_placa"><?php echo $labels["vehicle_plate_label"] ?? "Placa:"; ?></label>
            <input type="text" id="vei_placa" name="vei_placa" required>
        </div>
        <button type="submit" id="submit-btn"><?php echo $labels["register_vehicle_btn"] ?? "Cadastrar Veículo"; ?></button>
        <button type="button" id="clear-btn"><?php echo $labels["clear_btn"] ?? "Limpar"; ?></button>
    </form>

    <h3 style="margin-top: 40px; border-top: 1px solid #ccc; padding-top: 20px;"><?php echo $labels["registered_vehicles_list"] ?? "Veículos Cadastrados"; ?></h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th><?php echo $labels["model"] ?? "Modelo"; ?></th>
                <th><?php echo $labels["brand"] ?? "Marca"; ?></th>
                <th><?php echo $labels["plate"] ?? "Placa"; ?></th>
                <th><?php echo $labels["actions"] ?? "Ações"; ?></th>
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
                    <button class="edit-btn" 
                        type="button"
                        data-id="<?php echo $row["VEI_ID"]; ?>" 
                        data-modelo="<?php echo htmlspecialchars($row["VEI_MODELO"]); ?>" 
                        data-marca="<?php echo htmlspecialchars($row["VEI_MARCA"]); ?>" 
                        data-placa="<?php echo htmlspecialchars($row["VEI_PLACA"]); ?>">
                        <?php echo $labels["edit"] ?? "Editar"; ?>
                    </button>
                    <form method="POST" class="delete-form" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?php echo $row["VEI_ID"]; ?>">
                        <button type="submit" onclick="return confirm('<?php echo addslashes($labels["confirm_delete_message"] ?? "Tem certeza?"); ?>')">
                            <?php echo $labels["delete"] ?? "Excluir"; ?>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>
<script src="js/language.js"></script>
</body>
</html>