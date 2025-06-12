<?php

include('utils/conectadb.php');
session_start();

$lang = $_SESSION["lang"] ?? "en";
$labels = include "$lang.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['txtUsuario'];
    $senha = $_POST['txtSenha'];

    //verifica usuario e senha se existe
    $sql = "SELECT COUNT(*) FROM usuario 
    WHERE USU_NOME = '$usuario' AND USU_SENHA = '$senha'";
    
    $enviaquery = mysqli_query($link, $sql);
    $retorno = mysqli_fetch_array($enviaquery) [0];

    //coleta o nome do usuario  
    $sqlfun = "SELECT USU_NOME FROM usuario 
    WHERE USU_NOME = '$usuario' AND USU_SENHA = '$senha'";

    $enviaquery2 = mysqli_query($link, $sqlfun);
    $idusuario = mysqli_fetch_array($enviaquery2) [0];

    //validar o retorno se existe login e senha
    if ($retorno == 1) 
    {
        $_SESSION['idusuario'] = $idusuario;
        Header("Location: dashboard.php");
    }
    else 
    {
        echo "<script>alert('Usuário ou senha incorretos!');</script>";
        echo "<script>window.location.href = 'login.php';</script>";
    }
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
        <h2>Login</h2>
        <form action="login.php" method="POST">
            <div class="form-group">
                <input type="text" id="nome" name="txtUsuario" placeholder="Usuario" required>
            </div>
            <div class="form-group">
                <input type="password" id="senha" name="txtSenha" placeholder="Senha" required>
            </div>
            <button type="submit">Entrar</button>
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