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

// Deleta o registro de empresa
if (isset($_POST["delete_id"])) {
    $delete_id = intval($_POST["delete_id"]);

    // Primeiro, deleta os endereços relacionados
    $sql_end = "DELETE FROM endereco WHERE END_FK_EMPRESA_ID = ?";
    if ($stmt_end = mysqli_prepare($link, $sql_end)) {
        mysqli_stmt_bind_param($stmt_end, "i", $delete_id);
        mysqli_stmt_execute($stmt_end);
        mysqli_stmt_close($stmt_end);
    }

    // Agora deleta a empresa
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

// Edita o registro selecionado
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
    // Atualizar endereços: (simples, remove todos e insere novamente)
    if (isset($_POST["end_pais"]) && is_array($_POST["end_pais"])) {
        // Remove antigos
        $sql_del = "DELETE FROM endereco WHERE END_FK_EMPRESA_ID = ?";
        if ($stmt_del = mysqli_prepare($link, $sql_del)) {
            mysqli_stmt_bind_param($stmt_del, "i", $edit_id);
            mysqli_stmt_execute($stmt_del);
            mysqli_stmt_close($stmt_del);
        }
        // Insere novos
        $sql_end = "INSERT INTO endereco (END_PAIS, END_CIDADE, END_RUA, END_NUMERO, END_FK_EMPRESA_ID) VALUES (?, ?, ?, ?, ?)";
        foreach ($_POST["end_pais"] as $i => $pais) {
            if(!empty($pais)) { // Garante que não insere endereços vazios
                $cidade = $_POST["end_cidade"][$i];
                $rua = $_POST["end_rua"][$i];
                $numero = $_POST["end_numero"][$i];
                if ($stmt_end = mysqli_prepare($link, $sql_end)) {
                    mysqli_stmt_bind_param($stmt_end, "ssssi", $pais, $cidade, $rua, $numero, $edit_id);
                    mysqli_stmt_execute($stmt_end);
                    mysqli_stmt_close($stmt_end);
                }
            }
        }
    }
}
// Insere um novo registro no banco de dados (nova empresa)
elseif ($_SERVER["REQUEST_METHOD"] == "POST" && empty($_POST["edit_id"]) && empty($_POST["delete_id"])) {
    $emp_nome = $_POST["emp_nome"];
    $emp_cnpj = $_POST["emp_cnpj"];
    $emp_telefone = $_POST["emp_telefone"];
    $sql = "INSERT INTO empresa (emp_nome, emp_cnpj, emp_telefone) VALUES (?, ?, ?)";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "sss", $emp_nome, $emp_cnpj, $emp_telefone);
        if (mysqli_stmt_execute($stmt)) {
            $empresa_id = mysqli_insert_id($link);
            $success_msg = $labels["company_registered"] ?? "Empresa cadastrada com sucesso!";

            if (!empty($_POST["end_pais"][0]) && is_array($_POST["end_pais"])) {
                foreach ($_POST["end_pais"] as $i => $end_pais) {
                    if(!empty($end_pais)) { // Garante que não insere endereços vazios
                        $end_cidade = $_POST["end_cidade"][$i];
                        $end_rua = $_POST["end_rua"][$i];
                        $end_numero = $_POST["end_numero"][$i];
                        $sql_end = "INSERT INTO endereco (END_PAIS, END_CIDADE, END_RUA, END_NUMERO, END_FK_EMPRESA_ID) VALUES (?, ?, ?, ?, ?)";
                        if ($stmt_end = mysqli_prepare($link, $sql_end)) {
                            mysqli_stmt_bind_param($stmt_end, "ssssi", $end_pais, $end_cidade, $end_rua, $end_numero, $empresa_id);
                            mysqli_stmt_execute($stmt_end);
                            mysqli_stmt_close($stmt_end);
                        }
                    }
                }
            }
        } else {
            $error_msg = $labels["company_error"] ?? "Erro ao cadastrar empresa.";
        }
        mysqli_stmt_close($stmt);
    }
}

$sql = "SELECT emp_id, emp_nome, emp_cnpj, emp_telefone FROM empresa ORDER BY emp_id ASC";
$result = mysqli_query($link, $sql);
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $labels["companies_title"] ?? "Empresas"; ?> - MENA Freight Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script>
    // Função para adicionar bloco de endereço
    function adicionarEndereco(valores = {}) {
        const container = document.getElementById('enderecos-container');
        const block = document.createElement('div');
        block.className = 'endereco-block mb-4 p-3 border border-secondary rounded bg-darker';

        // Aplicando addslashes em todas as traduções dentro do JavaScript
        block.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-danger mb-0">
                    <?php echo $labels["addresses_title"] ?? "Endereço"; ?>
                </h6>
                <button type="button" class="btn btn-sm btn-outline-danger remove-endereco-btn" onclick="this.parentNode.parentNode.remove()">
                    <i class="fas fa-times me-1"></i><?php echo addslashes($labels["remove_address_btn"] ?? "Remover"); ?>
                </button>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-light">
                        <i class="fas fa-globe text-danger me-1"></i>
                        <?php echo addslashes($labels["country_label"] ?? "País:"); ?>
                    </label>
                    <input type="text" name="end_pais[]" value="${valores.end_pais || ''}" class="form-control bg-darker text-light border-secondary">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-light">
                        <i class="fas fa-city text-danger me-1"></i>
                        <?php echo addslashes($labels["city_label"] ?? "Cidade:"); ?>
                    </label>
                    <input type="text" name="end_cidade[]" value="${valores.end_cidade || ''}" class="form-control bg-darker text-light border-secondary">
                </div>
                <div class="col-md-8">
                    <label class="form-label text-light">
                        <i class="fas fa-road text-danger me-1"></i>
                        <?php echo addslashes($labels["street_label"] ?? "Rua:"); ?>
                    </label>
                    <input type="text" name="end_rua[]" value="${valores.end_rua || ''}" class="form-control bg-darker text-light border-secondary">
                </div>
                <div class="col-md-4">
                    <label class="form-label text-light">
                        <i class="fas fa-hashtag text-danger me-1"></i>
                        <?php echo addslashes($labels["number_label"] ?? "Número:"); ?>
                    </label>
                    <input type="text" name="end_numero[]" value="${valores.end_numero || ''}" class="form-control bg-darker text-light border-secondary">
                </div>
            </div>
        `;
        container.appendChild(block);
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Aplicando addslashes nas variáveis de texto
        const defaultSubmitText = '<?php echo addslashes($labels["register_company_btn"] ?? "Cadastrar Empresa"); ?>';
        const editSubmitText = '<?php echo addslashes($labels["edit"] ?? "Editar"); ?>';

        if (document.getElementById('enderecos-container').children.length === 0) {
            adicionarEndereco();
        }

        document.getElementById('btn-add-endereco').addEventListener('click', function(e) {
            e.preventDefault();
            adicionarEndereco();
        });

        document.querySelectorAll('.edit-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('emp_nome').value = this.dataset.nome;
                document.getElementById('emp_cnpj').value = this.dataset.cnpj;
                document.getElementById('emp_telefone').value = this.dataset.telefone;
                document.getElementById('edit_id').value = this.dataset.id;
                document.getElementById('submit-btn').textContent = editSubmitText;
                document.querySelectorAll('#empresa-form input, #empresa-form button').forEach(el => el.removeAttribute('disabled'));

                fetch(`buscar_enderecos.php?empresa_id=${this.dataset.id}`)
                .then(r => r.json())
                .then(data => {
                    const cont = document.getElementById('enderecos-container');
                    cont.innerHTML = '';
                    if (data.length === 0) {
                        adicionarEndereco();
                    } else {
                        data.forEach(e => {
                            adicionarEndereco({
                                end_pais: e.END_PAIS,
                                end_cidade: e.END_CIDADE,
                                end_rua: e.END_RUA,
                                end_numero: e.END_NUMERO
                            });
                        });
                    }
                });
            });
        });

        document.querySelectorAll('.view-address-btn').forEach(btn => {
            btn.addEventListener('click', e => {
                e.preventDefault();
                document.getElementById('emp_nome').value = btn.dataset.nome;
                document.getElementById('emp_cnpj').value = btn.dataset.cnpj;
                document.getElementById('emp_telefone').value = btn.dataset.telefone;
                document.getElementById('edit_id').value = '';
                
                fetch(`buscar_enderecos.php?empresa_id=${btn.dataset.id}`)
                .then(r => r.json())
                .then(data => {
                    const cont = document.getElementById('enderecos-container');
                    cont.innerHTML = '';
                    if (data.length === 0) {
                        adicionarEndereco();
                    } else {
                        data.forEach(e => {
                            adicionarEndereco({
                                end_pais: e.END_PAIS,
                                end_cidade: e.END_CIDADE,
                                end_rua: e.END_RUA,
                                end_numero: e.END_NUMERO
                            });
                        });
                    }
                    document.querySelectorAll('#empresa-form input, #empresa-form button').forEach(function(el) {
                        el.setAttribute('disabled', 'true');
                    });
                    document.getElementById('clear-btn').removeAttribute('disabled');
                });
            });
        });

        document.getElementById('clear-btn').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('empresa-form').reset();
            document.getElementById('edit_id').value = '';
            document.getElementById('submit-btn').textContent = defaultSubmitText;
            document.querySelectorAll('#empresa-form input, #empresa-form button').forEach(el => el.removeAttribute('disabled'));
            
            const cont = document.getElementById('enderecos-container');
            cont.innerHTML = '';
            adicionarEndereco();
        });
    });
</script>
</head>
<body class="bg-dark">
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
            </div>
        </div>
    </nav>

   
    <div class="container-fluid py-4">
        
        <div class="row mb-4">
            <div class="col-12">
                <div class="page-header text-center">
                    <h1 class="text-danger mb-2">
                        <i class="fas fa-building me-2"></i>
                        <?php echo $labels["company_registration"] ?? "Cadastro de Empresas"; ?>
                    </h1>
                    
                </div>
            </div>
        </div>

        
        <?php if (isset($success_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $success_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto">
                <div class="form-container">
                    <h3 class="text-danger mb-4">
                        <i class="fas fa-plus-circle me-2"></i>
                        <?php echo $labels["company_data_title"] ?? "Dados da Empresa"; ?>
                    </h3>
                    
                    <form method="POST" id="empresa-form" autocomplete="off">
                        <input type="hidden" name="edit_id" id="edit_id">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="emp_nome" class="form-label text-light">
                                    <i class="fas fa-building text-danger me-1"></i>
                                    <?php echo $labels["company_name_label"] ?? "Nome da Empresa:"; ?>
                                </label>
                                <input type="text" id="emp_nome" name="emp_nome" class="form-control bg-darker text-light border-secondary" required>
                            </div>
                            <div class="col-md-6">
                                <label for="emp_cnpj" class="form-label text-light">
                                    <i class="fas fa-id-card text-danger me-1"></i>
                                    <?php echo $labels["company_cnpj_label"] ?? "CNPJ:"; ?>
                                </label>
                                <input type="text" id="emp_cnpj" name="emp_cnpj" class="form-control bg-darker text-light border-secondary" required>
                            </div>
                            <div class="col-md-6">
                                <label for="emp_telefone" class="form-label text-light">
                                    <i class="fas fa-phone text-danger me-1"></i>
                                    <?php echo $labels["company_phone_label"] ?? "Telefone:"; ?>
                                </label>
                                <input type="text" id="emp_telefone" name="emp_telefone" class="form-control bg-darker text-light border-secondary" required>
                            </div>
                        </div>

                        
                        <div class="addresses-section mt-4">
                            <h4 class="text-danger mb-3">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <?php echo $labels["addresses_title"] ?? "Endereços"; ?>
                            </h4>
                            <div id="enderecos-container"></div>
                            
                            <button type="button" id="btn-add-endereco" class="btn btn-outline-danger mb-3">
                                <i class="fas fa-plus me-1"></i>
                                <?php echo $labels["add_address_btn"] ?? "Adicionar Endereço"; ?>
                            </button>
                        </div>

                        
                        <div class="form-actions d-flex gap-2 flex-wrap">
                            <button type="submit" id="submit-btn" class="btn btn-danger">
                                <i class="fas fa-save me-1"></i>
                                <?php echo $labels["register_company_btn"] ?? "Cadastrar Empresa"; ?>
                            </button>
                            <button type="button" id="clear-btn" class="btn btn-outline-secondary">
                                <i class="fas fa-eraser me-1"></i>
                                <?php echo $labels["clear_btn"] ?? "Limpar"; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        
        <div class="row">
            <div class="col-12">
                <div class="table-container">
                    <h3 class="text-danger mb-4">
                        <i class="fas fa-list me-2"></i>
                        <?php echo $labels["registered_companies_list"] ?? "Empresas Cadastradas"; ?>
                    </h3>
                    
                    <div class="table-responsive">
                        <table class="table table-dark table-striped table-hover">
                            <thead class="table-danger">
                                <tr>
                                    <th><i class="fas fa-hashtag me-1"></i>ID</th>
                                    <th><i class="fas fa-building me-1"></i><?php echo $labels["name"] ?? "Nome"; ?></th>
                                    <th><i class="fas fa-id-card me-1"></i><?php echo $labels["cnpj_id_header"] ?? "CNPJ/ID"; ?></th>
                                    <th><i class="fas fa-phone me-1"></i><?php echo $labels["phone"] ?? "Telefone"; ?></th>
                                    <th><i class="fas fa-cogs me-1"></i><?php echo $labels["actions"] ?? "Ações"; ?></th>
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
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-warning edit-btn" 
                                                type="button"
                                                data-id="<?php echo $row["emp_id"]; ?>" 
                                                data-nome="<?php echo htmlspecialchars($row["emp_nome"]); ?>" 
                                                data-cnpj="<?php echo htmlspecialchars($row["emp_cnpj"]); ?>" 
                                                data-telefone="<?php echo htmlspecialchars($row["emp_telefone"]); ?>">
                                                <i class="fas fa-edit me-1"></i><?php echo $labels["edit"] ?? "Editar"; ?>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info view-address-btn" 
                                                type="button"
                                                data-id="<?php echo $row["emp_id"]; ?>" 
                                                data-nome="<?php echo htmlspecialchars($row["emp_nome"]); ?>" 
                                                data-cnpj="<?php echo htmlspecialchars($row["emp_cnpj"]); ?>" 
                                                data-telefone="<?php echo htmlspecialchars($row["emp_telefone"]); ?>">
                                                <i class="fas fa-eye me-1"></i><?php echo $labels["view_addresses_btn"] ?? "Ver Endereços"; ?>
                                            </button>
                                            <form method="POST" class="delete-form d-inline">
                                                <input type="hidden" name="delete_id" value="<?php echo $row["emp_id"]; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                    onclick="return confirm('<?php echo addslashes($labels["confirm_delete_message"] ?? "Tem certeza?"); ?>')">
                                                    <i class="fas fa-trash me-1"></i><?php echo $labels["delete"] ?? "Excluir"; ?>
                                                </button>
                                            </form>
                                        </div>
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