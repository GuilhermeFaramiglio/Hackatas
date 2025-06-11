<?php
session_start();
include_once("../config.php");

$lang = $_SESSION["lang"] ?? "en";
$labels = include "../lang/$lang.php";

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.html");
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $cnpj = $_POST["cnpj"];
    $email = $_POST["email"];
    $telefone = $_POST["telefone"];
    
    $sql = "INSERT INTO empresa (nome, cnpj, email, telefone) VALUES (?, ?, ?, ?)";
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssss", $nome, $cnpj, $email, $telefone);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_msg = "Empresa cadastrada com sucesso!";
        } else {
            $error_msg = "Erro ao cadastrar empresa.";
        }
        
        mysqli_stmt_close($stmt);
    }
}

// Fetch all companies
$sql = "SELECT * FROM empresa ORDER BY nome";
$result = mysqli_query($link, $sql);
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empresas - MENA Freight Hub</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header>
        <h1><?php echo $labels["company"]; ?></h1>
        <nav>
            <a href="../dashboard.php">Dashboard</a>
            <a href="../logout.php"><?php echo $labels["logout"]; ?></a>
        </nav>
    </header>
    <main>
        <h2>Cadastro de Empresas</h2>
        
        <?php if (isset($success_msg)): ?>
            <div class="success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_msg)): ?>
            <div class="error"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="nome">Nome da Empresa:</label>
                <input type="text" id="nome" name="nome" required>
            </div>
            <div class="form-group">
                <label for="cnpj">CNPJ/ID:</label>
                <input type="text" id="cnpj" name="cnpj" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="telefone">Telefone:</label>
                <input type="text" id="telefone" name="telefone" required>
            </div>
            <button type="submit">Cadastrar Empresa</button>
        </form>
        
        <h3>Empresas Cadastradas</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>CNPJ/ID</th>
                    <th>Email</th>
                    <th>Telefone</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_array($result)): ?>
                <tr>
                    <td><?php echo $row["id"]; ?></td>
                    <td><?php echo $row["nome"]; ?></td>
                    <td><?php echo $row["cnpj"]; ?></td>
                    <td><?php echo $row["email"]; ?></td>
                    <td><?php echo $row["telefone"]; ?></td>
                    <td>
                        <a href="edit_empresa.php?id=<?php echo $row["id"]; ?>">Editar</a>
                        <a href="delete_empresa.php?id=<?php echo $row["id"]; ?>" onclick="return confirm('Tem certeza?')">Excluir</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>
</body>
</html>

