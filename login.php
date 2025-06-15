<?php

include('utils/conectadb.php');
session_start();

$lang = $_COOKIE["lang"] ?? "en";
$labels = include "$lang.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['txtUsuario'];
    $senha = $_POST['txtSenha'];

    $sql = "SELECT USU_ID, USU_NOME FROM usuario WHERE USU_NOME = ? AND USU_SENHA = ?";
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $usuario, $senha);
        
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) == 1) {
                $user_data = mysqli_fetch_assoc($result);
                
                
                $_SESSION['idusuario'] = $user_data['USU_ID']; 
                $_SESSION['nomeusuario'] = $user_data['USU_NOME'];
                
                header("Location: dashboard.php");
                exit;
            }
        }
    }
    
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-dark">
    <div class="login-wrapper d-flex align-items-center justify-content-center min-vh-100">
        <div class="login-container">
            <!-- Logo da empresa -->
            <div class="text-center mb-4">
                <div class="company-logo">
                    <i class="fas fa-truck text-danger me-2"></i>
                    <span class="company-name text-danger fw-bold">MENA Freight Hub</span>
                </div>
                <p class="text-light-gray mt-2"><?php echo $labels['system_title']; ?></p>
            </div>

            <!-- Título do login -->
            <h2 class="text-center text-light mb-4">
                <i class="fas fa-sign-in-alt text-danger me-2"></i>
                <?php echo $labels["login"]; ?>
            </h2>

            <!-- Formulário de login -->
            <form action="login.php" method="POST" class="login-form">
                <!-- Input de usuário -->
                <div class="form-group mb-3">
                    <div class="input-group">
                        <span class="input-group-text bg-darker border-secondary">
                            <i class="fas fa-user text-danger"></i>
                        </span>
                        <input type="text" 
                               id="nome" 
                               name="txtUsuario" 
                               class="form-control bg-darker text-light border-secondary" 
                               placeholder="<?php echo $labels['username_placeholder']; ?>" 
                               required>
                    </div>
                </div>

                <!-- Input de senha -->
                <div class="form-group mb-4">
                    <div class="input-group">
                        <span class="input-group-text bg-darker border-secondary">
                            <i class="fas fa-lock text-danger"></i>
                        </span>
                        <input type="password" 
                               id="senha" 
                               name="txtSenha" 
                               class="form-control bg-darker text-light border-secondary" 
                               placeholder="<?php echo $labels['password_placeholder']; ?>" 
                               required>
                    </div>
                </div>

                <!-- Botão de login -->
                <button type="submit" class="btn btn-danger btn-lg w-100 mb-4">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    <?php echo $labels['login_button']; ?>
                </button>
            </form>

            <!-- Seletor de idioma -->
            <div class="language-selector text-center">
                <p class="text-light-gray mb-2">
                    <i class="fas fa-globe text-danger me-1"></i>
                    <?php echo $labels['language'] ?? 'Language'; ?>
                </p>
                <div class="language-buttons">
                    <a href="#" onclick="setLanguage('en')" class="btn btn-outline-danger btn-sm me-2">
                        <img src="img/eng.webp" height="16" width="16" class="me-1">EN
                    </a>
                    <a href="#" onclick="setLanguage('ar')" class="btn btn-outline-danger btn-sm me-2">
                        <img src="img/arabe.webp" height="16" width="16" class="me-1">AR
                    </a>
                    <a href="#" onclick="setLanguage('fr')" class="btn btn-outline-danger btn-sm">
                        <img src="img/France_Flag.PNG.webp" height="16" width="16" class="me-1">FR
                    </a>
                </div>
            </div>

            <!-- Link para voltar -->
            <div class="text-center mt-4">
                <a href="inicial.php" class="text-danger text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i>
                    <?php echo $labels['back_to_home'] ?? 'Voltar à página inicial'; ?>
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/language.js"></script>
</body>
</html>