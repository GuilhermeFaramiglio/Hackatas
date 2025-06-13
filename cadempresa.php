<?php
include("utils/conectadb.php");
session_start();

$lang = $_COOKIE["lang"] ?? "en";
$labels = include "$lang.php";

// A lógica de verificação de sessão foi movida para depois da carga de $labels
// para que a mensagem de erro possa ser traduzida, se desejado.
if (isset($_SESSION['idusuario'])) {
    $idusuario = $_SESSION['idusuario'];
    // Esta consulta foi simplificada em uma etapa anterior, assumindo que o nome já está na sessão
    $nomeusuario = $_SESSION['nomeusuario'] ?? 'Usuário';
} else {
    // Exemplo de como usar a tradução no alerta
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
    <link rel="stylesheet" href="css/style.css">
    <script>
    // Função para adicionar bloco de endereço
    function adicionarEndereco(valores = {}) {
        const container = document.getElementById('enderecos-container');
        const block = document.createElement('div');
        block.className = 'endereco-block';

        // Aplicando addslashes em todas as traduções dentro do JavaScript
        block.innerHTML = `
            <hr>
            <div class="form-group">
                <label><?php echo addslashes($labels["country_label"] ?? "País:"); ?></label>
                <input type="text" name="end_pais[]" value="${valores.end_pais || ''}">
            </div>
            <div class="form-group">
                <label><?php echo addslashes($labels["city_label"] ?? "Cidade:"); ?></label>
                <input type="text" name="end_cidade[]" value="${valores.end_cidade || ''}">
            </div>
            <div class="form-group">
                <label><?php echo addslashes($labels["street_label"] ?? "Rua:"); ?></label>
                <input type="text" name="end_rua[]" value="${valores.end_rua || ''}">
            </div>
            <div class="form-group">
                <label><?php echo addslashes($labels["number_label"] ?? "Número:"); ?></label>
                <input type="text" name="end_numero[]" value="${valores.end_numero || ''}">
            </div>
            <button type="button" class="remove-endereco-btn" onclick="this.parentNode.remove()"><?php echo addslashes($labels["remove_address_btn"] ?? "Remover Endereço"); ?></button>
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
<body style="background-size: cover; background-attachment: fixed; background-image: url('img/marcasp.png');">
<header>
    <h1><?php echo $labels["company"]; ?></h1>
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
    <h2><?php echo $labels["company_registration"] ?? "Cadastro de Empresas"; ?></h2>

    <?php if (isset($success_msg)): ?>
        <div class="success"><?php echo $success_msg; ?></div>
    <?php endif; ?>

    <?php if (isset($error_msg)): ?>
        <div class="error"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <form method="POST" id="empresa-form" autocomplete="off">
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

        <h3 style="margin-top: 30px; border-top: 1px solid #ccc; padding-top: 20px;"><?php echo $labels["addresses_title"] ?? "Endereços"; ?></h3>
        <div id="enderecos-container"></div>
        
        <div style="margin-top: 20px;">
            <button type="button" id="btn-add-endereco"><?php echo $labels["add_address_btn"] ?? "Adicionar Endereço"; ?></button>
            <button type="submit" id="submit-btn"><?php echo $labels["register_company_btn"] ?? "Cadastrar Empresa"; ?></button>
            <button type="button" id="clear-btn"><?php echo $labels["clear_btn"] ?? "Limpar"; ?></button>
        </div>
    </form>

    <h3 style="margin-top: 40px; border-top: 1px solid #ccc; padding-top: 20px;"><?php echo $labels["registered_companies_list"] ?? "Empresas Cadastradas"; ?></h3>
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
                    <button class="view-address-btn" 
                        type="button"
                        data-id="<?php echo $row["emp_id"]; ?>" 
                        data-nome="<?php echo htmlspecialchars($row["emp_nome"]); ?>" 
                        data-cnpj="<?php echo htmlspecialchars($row["emp_cnpj"]); ?>" 
                        data-telefone="<?php echo htmlspecialchars($row["emp_telefone"]); ?>">
                        <?php echo $labels["view_addresses_btn"] ?? "Ver Endereços"; ?>
                    </button>
                    <form method="POST" class="delete-form" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?php echo $row["emp_id"]; ?>">
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