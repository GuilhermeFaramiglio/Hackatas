<?php
include("utils/conectadb.php");
session_start();

$lang = $_COOKIE["lang"] ?? "en";
$labels = include "$lang.php";

if (isset($_SESSION['idusuario'])) {
    $idusuario = $_SESSION['idusuario'];
    $sql = "SELECT USU_NOME FROM usuario WHERE USU_ID = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $idusuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $nomeusuario = mysqli_fetch_array($result)['USU_NOME'] ?? 'Usuário';
    mysqli_stmt_close($stmt);
} else {
    echo "<script>alert('Usuário não logado!');</script>";
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}

// Handle delete
if (isset($_POST["delete_id"])) {
    $delete_id = intval($_POST["delete_id"]);
    $sql = "DELETE FROM empresa WHERE emp_id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $delete_id);
        if (mysqli_stmt_execute($stmt)) {
            $success_msg = $labels["company_deleted"] ?? "Empresa excluída com sucesso!";
        } else {
            $error_msg = $labels["company_delete_error"] ?? "Erro ao excluir empresa.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle edit (update)
if (isset($_POST["edit_id"]) && !empty($_POST["edit_id"])) {
    $edit_id = intval($_POST["edit_id"]);
    $edit_nome = $_POST["emp_nome"];
    $edit_cnpj = $_POST["emp_cnpj"];
    $edit_telefone = $_POST["emp_telefone"];
    $sql = "UPDATE empresa SET emp_nome = ?, emp_cnpj = ?, emp_telefone = ? WHERE emp_id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "sssi", $edit_nome, $edit_cnpj, $edit_telefone, $edit_id);
        if (mysqli_stmt_execute($stmt)) {
            $success_msg = $labels["company_updated"] ?? "Empresa atualizada com sucesso!";
        } else {
            $error_msg = $labels["company_update_error"] ?? "Erro ao atualizar empresa.";
        }
        mysqli_stmt_close($stmt);
    }
}
// Handle insert (new)
elseif ($_SERVER["REQUEST_METHOD"] == "POST" && empty($_POST["edit_id"]) && empty($_POST["delete_id"])) {
    $emp_nome = $_POST["emp_nome"];
    $emp_cnpj = $_POST["emp_cnpj"];
    $emp_telefone = $_POST["emp_telefone"];
    $sql = "INSERT INTO empresa (emp_nome, emp_cnpj, emp_telefone) VALUES (?, ?, ?)";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "sss", $emp_nome, $emp_cnpj, $emp_telefone);
        if (mysqli_stmt_execute($stmt)) {
            $success_msg = $labels["company_registered"] ?? "Empresa cadastrada com sucesso!";
        } else {
            $error_msg = $labels["company_error"] ?? "Erro ao cadastrar empresa.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Fetch all companies
$sql = "SELECT emp_id, emp_nome, emp_cnpj, emp_telefone FROM empresa ORDER BY emp_id ASC";
$result = mysqli_query($link, $sql);
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empresas - MENA Freight Hub</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const defaultSubmitText = '<?php echo $labels["register_company_btn"] ?? "Cadastrar Empresa"; ?>';
        const editSubmitText = '<?php echo $labels["edit"] ?? "Editar"; ?>';

        document.querySelectorAll('.edit-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('emp_nome').value = this.dataset.nome;
                document.getElementById('emp_cnpj').value = this.dataset.cnpj;
                document.getElementById('emp_telefone').value = this.dataset.telefone;
                document.getElementById('edit_id').value = this.dataset.id;
                document.getElementById('submit-btn').textContent = editSubmitText;
            });
        });

        document.getElementById('clear-btn').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('empresa-form').reset(); 
            document.getElementById('edit_id').value = '';
            document.getElementById('submit-btn').textContent = defaultSubmitText;
        });
    });
</script>
   
</head>
<body style="background-size: cover; background-attachment: fixed; background-image: url('img/marcasp.png');">
    <header>
        <h1><?php echo $labels["company"]; ?></h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php"><?php echo $labels["logout"]; ?></a>
        </nav>
    </header>
   <main>
    <h2><?php echo $labels["company_registration"] ?? "Cadastro de Empresas"; ?></h2>
    
    <?php if (isset($success_msg)): ?>
        <div class="success"><?php echo $success_msg; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_msg)): ?>
        <div class="error"><?php echo $error_msg; ?></div>
    <?php endif; ?>
    
    <form method="POST" id="empresa-form">
        <input type="hidden" name="edit_id" id="edit_id">
        <div class="form-group">
            <label for="emp_nome"><?php echo $labels["company_name_label"] ?? "Nome da Empresa:"; ?></label>
            <input type="text" id="emp_nome" name="emp_nome" required>
        </div>
        <div class="form-group">
            <label for="emp_cnpj"><?php echo $labels["company_cnpj_label"] ?? "CNPJ:"; ?></label>
            <input type="text" id="emp_cnpj" name="emp_cnpj" required>
        </div>
        <div class="form-group">
            <label for="emp_telefone"><?php echo $labels["company_phone_label"] ?? "Telefone:"; ?></label>
            <input type="text" id="emp_telefone" name="emp_telefone" required>
        </div>
        <button type="submit" id="submit-btn"><?php echo $labels["register_company_btn"] ?? "Cadastrar Empresa"; ?></button>
        <button type="button" id="clear-btn"><?php echo $labels["clear_btn"] ?? "Limpar"; ?></button>
    </form>
    
    <h3><?php echo $labels["registered_companies_list"] ?? "Empresas Cadastradas"; ?></h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th><?php echo $labels["name"] ?? "Nome"; ?></th>
                <th><?php echo $labels["cnpj_id_header"] ?? "CNPJ/ID"; ?></th>
                <th><?php echo $labels["phone"] ?? "Telefone"; ?></th>
                <th><?php echo $labels["actions"] ?? "Ações"; ?></th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo $row["emp_id"]; ?></td>
                <td><?php echo htmlspecialchars($row["emp_nome"]); ?></td>
                <td><?php echo htmlspecialchars($row["emp_cnpj"]); ?></td>
                <td><?php echo htmlspecialchars($row["emp_telefone"]); ?></td>
                <td>
                    <button class="edit-btn" 
                        type="button"
                        data-id="<?php echo $row["emp_id"]; ?>" 
                        data-nome="<?php echo htmlspecialchars($row["emp_nome"]); ?>" 
                        data-cnpj="<?php echo htmlspecialchars($row["emp_cnpj"]); ?>" 
                        data-telefone="<?php echo htmlspecialchars($row["emp_telefone"]); ?>">
                        <?php echo $labels["edit"] ?? "Editar"; ?>
                    </button>
                    <form method="POST" class="delete-form" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?php echo $row["emp_id"]; ?>">
                        <button type="submit" onclick="return confirm('<?php echo $labels["confirm_delete_message"] ?? "Tem certeza?"; ?>')">
                            <?php echo $labels["delete"] ?? "Excluir"; ?>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>
</body>
</html>
