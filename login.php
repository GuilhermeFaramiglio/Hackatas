<?php

include('utils/conectadb.php');
session_start();

$lang = $_COOKIE["lang"] ?? "en";
$labels = include "$lang.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['txtUsuario'];
    $senha = $_POST['txtSenha'];

    // Query segura que busca o ID e o NOME
    $sql = "SELECT USU_ID, USU_NOME FROM usuario WHERE USU_NOME = ? AND USU_SENHA = ?";
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $usuario, $senha);
        
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) == 1) {
                $user_data = mysqli_fetch_assoc($result);
                
                // --- A CORREÇÃO PRINCIPAL ESTÁ AQUI ---
                // Salva o ID do usuário na sessão 'idusuario'
                $_SESSION['idusuario'] = $user_data['USU_ID']; 
                // Salva o NOME do usuário na sessão 'nomeusuario'
                $_SESSION['nomeusuario'] = $user_data['USU_NOME'];
                
                header("Location: dashboard.php");
                exit;
            }
        }
    }
    
    // Se chegou até aqui, o login falhou
    $error_message = $labels['login_error'] ?? 'Usuário ou senha incorretos!';
    echo "<script>alert('{$error_message}'); window.location.href = 'login.php';</script>";
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MENA Freight Hub</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <h2><?php echo $labels["login"]; ?></h2>
        <form action="login.php" method="POST">
            <div class="form-group">
                <input type="text" id="nome" name="txtUsuario" placeholder="<?php echo $labels['username_placeholder']; ?>" required>
            </div>
         <div class="form-group">
             <input type="password" id="senha" name="txtSenha" placeholder="<?php echo $labels['password_placeholder']; ?>" required>
            </div>
            <button type="submit"><?php echo $labels['login_button']; ?></button>
        </form>
        <div class="language-selector">
            <a href="#" onclick="setLanguage('en')">EN</a> |
            <a href="#" onclick="setLanguage('ar')">AR</a> |
            <a href="#" onclick="setLanguage('fr')">FR</a>
        </div>
    </div>
    <script src="js/language.js"></script>
</body>
</html>