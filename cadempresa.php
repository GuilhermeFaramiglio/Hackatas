<?php
include("utils/conectadb.php");
session_start();

$lang = $_SESSION["lang"] ?? "en";
$labels = include "$lang.php";

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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
</head>
<body>
    <header>
        <h1><?php echo $labels["company"]; ?></h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php"><?php echo $labels["logout"]; ?></a>
        </nav>
    </header>
    <main>
        <h2><?php echo $labels["company_register"] ?? "Cadastro de Empresas"; ?></h2>
        
        <?php if (isset($success_msg)): ?>
            <div class="success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_msg)): ?>
            <div class="error"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="emp_nome"><?php echo $labels["company_name"] ?? "Nome da Empresa:"; ?></label>
                <input type="text" id="emp_nome" name="emp_nome" required>
            </div>
            <div class="form-group">
                <label for="emp_cnpj"><?php echo $labels["company_cnpj"] ?? "CNPJ/ID:"; ?></label>
                <input type="text" id="emp_cnpj" name="emp_cnpj" required>
            </div>
            <div class="form-group">
                <label for="emp_telefone"><?php echo $labels["company_phone"] ?? "Telefone:"; ?></label>
                <input type="text" id="emp_telefone" name="emp_telefone" required>
            </div>
            <button type="submit"><?php echo $labels["company_register_btn"] ?? "Cadastrar Empresa"; ?></button>
        </form>
        
        <h3><?php echo $labels["company_list"] ?? "Empresas Cadastradas"; ?></h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th><?php echo $labels["company_name"] ?? "Nome"; ?></th>
                    <th><?php echo $labels["company_cnpj"] ?? "CNPJ/ID"; ?></th>
                    <th><?php echo $labels["company_phone"] ?? "Telefone"; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row["emp_id"]; ?></td>
                    <td><?php echo $row["emp_nome"]; ?></td>
                    <td><?php echo $row["emp_cnpj"]; ?></td>
                    <td><?php echo $row["emp_telefone"]; ?></td>
                    <td>
                        <a href="edit_empresa.php?id=<?php echo $row["emp_id"]; ?>"><?php echo $labels["edit"] ?? "Editar"; ?></a>
                        <a href="delete_empresa.php?id=<?php echo $row["emp_id"]; ?>" onclick="return confirm('<?php echo $labels["confirm_delete"] ?? "Tem certeza?"; ?>')"><?php echo $labels["delete"] ?? "Excluir"; ?></a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
