<?php
include("utils/conectadb.php");
session_start();

$lang = $_COOKIE["lang"] ?? "en";
$labels = include "$lang.php";

if (!isset($_SESSION["idusuario"])) {
    $alert_msg = $labels["not_logged_in_error"] ?? "Usuário não logado!";
    echo "<script>alert(\'{$alert_msg}\');</script>";
    echo "<script>window.location.href = \'login.php\';</script>";
    exit;
}

// Busca empresas e veículos para os dropdowns
$empresas = [];
$res_emp = mysqli_query($link, "SELECT emp_id, emp_nome FROM empresa ORDER BY emp_nome");
while ($row = mysqli_fetch_assoc($res_emp)) $empresas[] = $row;

$veiculos = [];
$res_vei = mysqli_query($link, "SELECT VEI_ID, VEI_MODELO FROM veiculo ORDER BY VEI_MODELO");
while ($row = mysqli_fetch_assoc($res_vei)) $veiculos[] = $row;

// CRUD Orçamento
if (isset($_POST["delete_orc_id"])) {
    $delete_id = intval($_POST["delete_orc_id"]);
    // CORREÇÃO DE SEGURANÇA: Usando prepared statement para evitar SQL Injection
    $sql = "DELETE FROM orcamento WHERE ORC_ID = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "i", $delete_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $success_msg = $labels["budget_deleted"] ?? "Orçamento excluído!";
} elseif (isset($_POST["edit_orc_id"]) && !empty($_POST["edit_orc_id"])) {
    // Editar orçamento (código de edição já estava seguro)
    $orc_id = intval($_POST["edit_orc_id"]);
    $empresa_id = intval($_POST["orc_empresa"]);
    $veiculo_id = intval($_POST["orc_veiculo"]);
    $origem = $_POST["orc_origem"];
    $destino = $_POST["orc_destino"];
    $datainicio = $_POST["orc_datainicio"];
    $datafim = $_POST["orc_datafim"];
    $valor = calcularValor($datainicio, $datafim);

    $sql = "UPDATE orcamento SET ORC_FK_EMPRESA_ID=?, ORC_FK_VEICULO_ID=?, ORC_ORIGEM=?, ORC_DESTINO=?, ORC_DATAINICIO=?, ORC_DATAFIM=?, ORC_VALOR=? WHERE ORC_ID=?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "iissssdi", $empresa_id, $veiculo_id, $origem, $destino, $datainicio, $datafim, $valor, $orc_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $success_msg = $labels["budget_updated"] ?? "Orçamento atualizado!";
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && empty($_POST["edit_orc_id"]) && empty($_POST["delete_orc_id"])) {
    // Novo orçamento (código de inserção já estava seguro)
    $empresa_id = intval($_POST["orc_empresa"]);
    $veiculo_id = intval($_POST["orc_veiculo"]);
    $origem = $_POST["orc_origem"];
    $destino = $_POST["orc_destino"];
    $datainicio = $_POST["orc_datainicio"];
    $datafim = $_POST["orc_datafim"];
    $valor = calcularValor($datainicio, $datafim);

    $sql = "INSERT INTO orcamento (ORC_FK_EMPRESA_ID, ORC_FK_VEICULO_ID, ORC_ORIGEM, ORC_DESTINO, ORC_DATAINICIO, ORC_DATAFIM, ORC_VALOR) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "iissssd", $empresa_id, $veiculo_id, $origem, $destino, $datainicio, $datafim, $valor);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $success_msg = $labels["budget_registered"] ?? "Orçamento cadastrado!";
}

// Função para calcular valor do orçamento
function calcularValor($inicio, $fim) {
    if(empty($inicio) || empty($fim)) return 0.00;
    try {
        $start = new DateTime($inicio);
        $end = new DateTime($fim);
        if($start > $end) return 0.00;
        $diff = $start->diff($end)->days + 1;
        if ($diff <= 7) return 1000.00;
        if ($diff <= 31) return 3000.00;
        return 5000.00;
    } catch (Exception $e) {
        return 0.00;
    }
}

// Busca orçamentos cadastrados
$sql = "SELECT o.*, e.emp_nome, v.VEI_MODELO FROM orcamento o
        JOIN empresa e ON o.ORC_FK_EMPRESA_ID = e.emp_id
        JOIN veiculo v ON o.ORC_FK_VEICULO_ID = v.VEI_ID
        ORDER BY o.ORC_ID DESC";
$res_orc = mysqli_query($link, $sql);
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $labels["budgets_title"] ?? "Orçamentos"; ?> - MENA Freight Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
<script>
function buscarEnderecos(empresa_id) {
    if (!empresa_id) {
        document.getElementById('enderecos-empresa').innerHTML = '';
        return;
    }
    fetch('buscar_enderecos.php?empresa_id=' + empresa_id)
    .then(r => r.json())
    .then(data => {
        let html = '';
        if (data.length > 0) {
            data.forEach(e => {
                html += `<div class="p-2 border-bottom border-secondary text-light-gray">
                    <b><?php echo addslashes($labels["country_label"] ?? 'País:'); ?></b> ${e.END_PAIS} | 
                    <b><?php echo addslashes($labels["city_label"] ?? 'Cidade:'); ?></b> ${e.END_CIDADE} | 
                    <b><?php echo addslashes($labels["street_label"] ?? 'Rua:'); ?></b> ${e.END_RUA} | 
                    <b><?php echo addslashes($labels["number_label"] ?? 'Número:'); ?></b> ${e.END_NUMERO}
                </div>`;
            });
        } else {
            html = '<p class="text-light-gray"><?php echo addslashes($labels["no_addresses_found"] ?? "Nenhum endereço cadastrado."); ?></p>';
        }
        document.getElementById('enderecos-empresa').innerHTML = html;
    });
}

function atualizarValor() {
    const di = document.getElementById('orc_datainicio').value;
    const df = document.getElementById('orc_datafim').value;
    if (di && df) {
        const d1 = new Date(di);
        const d2 = new Date(df);
        if (d1 > d2) {
            document.getElementById('orc_valor').value = (0).toFixed(2);
            return;
        }
        const diff = Math.floor((d2 - d1) / (1000*60*60*24)) + 1;
        let valor = 1000.00;
        if (diff > 7 && diff <= 31) valor = 3000.00;
        else if (diff > 31) valor = 5000.00;
        document.getElementById('orc_valor').value = valor.toFixed(2);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('orc_empresa').addEventListener('change', function() {
        buscarEnderecos(this.value);
    });
    document.getElementById('orc_datainicio').addEventListener('change', atualizarValor);
    document.getElementById('orc_datafim').addEventListener('change', atualizarValor);

    // Lógica para preencher o formulário ao editar
    const editButtons = document.querySelectorAll('.edit-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            document.getElementById('edit_orc_id').value = this.dataset.id;
            document.getElementById('orc_empresa').value = this.dataset.empresaId;
            document.getElementById('orc_veiculo').value = this.dataset.veiculoId;
            document.getElementById('orc_origem').value = this.dataset.origem;
            document.getElementById('orc_destino').value = this.dataset.destino;
            document.getElementById('orc_datainicio').value = this.dataset.datainicio;
            document.getElementById('orc_datafim').value = this.dataset.datafim;
            document.getElementById('orc_valor').value = parseFloat(this.dataset.valor).toFixed(2);
            buscarEnderecos(this.dataset.empresaId);
            document.getElementById('submit-btn').textContent = '<?php echo addslashes($labels["edit"] ?? "Editar"); ?>';
            document.getElementById('clear-btn').textContent = '<?php echo addslashes($labels["cancel"] ?? "Cancelar"); ?>';
        });
    });

    document.getElementById('clear-btn').addEventListener('click', function() {
        document.getElementById('orcamento-form').reset();
        document.getElementById('edit_orc_id').value = "";
        document.getElementById('enderecos-empresa').innerHTML = "";
        document.getElementById('submit-btn').textContent = '<?php echo addslashes($labels["register_budget_btn"] ?? "Cadastrar Orçamento"); ?>';
        document.getElementById('clear-btn').textContent = '<?php echo addslashes($labels["clear_btn"] ?? "Limpar"); ?>';
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
                        <i class="fas fa-file-invoice-dollar me-2"></i>
                        <?php echo $labels["budget_registration"] ?? "Cadastro de Orçamento"; ?>
                    </h1>
                    <p class="text-light-gray"><?php echo $labels["manage_budgets_description"]; ?></p>
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
                    <form method="POST" id="orcamento-form" autocomplete="off">
                        <input type="hidden" name="edit_orc_id" id="edit_orc_id">
                        <div class="mb-3">
                            <label for="orc_empresa" class="form-label text-light">
                                <i class="fas fa-building text-danger me-1"></i>
                                <?php echo $labels["company_label"] ?? "Empresa:"; ?>
                            </label>
                            <select name="orc_empresa" id="orc_empresa" class="form-select bg-darker text-light border-secondary" required>
                                <option value=""><?php echo $labels["select_option"] ?? "Selecione"; ?></option>
                                <?php foreach ($empresas as $e): ?>
                                    <option value="<?php echo $e['emp_id']; ?>"><?php echo htmlspecialchars($e['emp_nome']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div id="enderecos-empresa" class="mb-3 p-3 border border-secondary rounded bg-darker"></div>
                        <div class="mb-3">
                            <label for="orc_veiculo" class="form-label text-light">
                                <i class="fas fa-truck text-danger me-1"></i>
                                <?php echo $labels["vehicle_label"] ?? "Veículo:"; ?>
                            </label>
                            <select name="orc_veiculo" id="orc_veiculo" class="form-select bg-darker text-light border-secondary" required>
                                <option value=""><?php echo $labels["select_option"] ?? "Selecione"; ?></option>
                                <?php foreach ($veiculos as $v): ?>
                                    <option value="<?php echo $v['VEI_ID']; ?>"><?php echo htmlspecialchars($v['VEI_MODELO']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="orc_origem" class="form-label text-light">
                                <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                <?php echo $labels["origin_label"] ?? "Origem:"; ?>
                            </label>
                            <input type="text" name="orc_origem" id="orc_origem" class="form-control bg-darker text-light border-secondary" required>
                        </div>
                        <div class="mb-3">
                            <label for="orc_destino" class="form-label text-light">
                                <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                <?php echo $labels["destination_label"] ?? "Destino:"; ?>
                            </label>
                            <input type="text" name="orc_destino" id="orc_destino" class="form-control bg-darker text-light border-secondary" required>
                        </div>
                        <div class="mb-3">
                            <label for="orc_datainicio" class="form-label text-light">
                                <i class="fas fa-calendar-alt text-danger me-1"></i>
                                <?php echo $labels["start_date_label"] ?? "Data Início:"; ?>
                            </label>
                            <input type="date" name="orc_datainicio" id="orc_datainicio" class="form-control bg-darker text-light border-secondary" required>
                        </div>
                        <div class="mb-3">
                            <label for="orc_datafim" class="form-label text-light">
                                <i class="fas fa-calendar-alt text-danger me-1"></i>
                                <?php echo $labels["end_date_label"] ?? "Data Fim:"; ?>
                            </label>
                            <input type="date" name="orc_datafim" id="orc_datafim" class="form-control bg-darker text-light border-secondary" required>
                        </div>
                        <div class="mb-3">
                            <label for="orc_valor" class="form-label text-light">
                                <i class="fas fa-dollar-sign text-danger me-1"></i>
                                <?php echo $labels["value_label"] ?? "Valor (USD):"; ?>
                            </label>
                            <input type="text" name="orc_valor" id="orc_valor" class="form-control bg-darker text-light border-secondary" readonly>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" id="submit-btn" class="btn btn-danger mt-3">
                                <i class="fas fa-save me-1"></i><?php echo $labels["register_budget_btn"] ?? "Cadastrar Orçamento"; ?>
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
                        <i class="fas fa-list me-2"></i><?php echo $labels["registered_budgets_list"] ?? "Orçamentos Cadastrados"; ?>
                    </h3>
                    <div class="table-responsive">
                        <table class="table table-dark table-striped table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col"><?php echo $labels["header_company"] ?? "Empresa"; ?></th>
                                    <th scope="col"><?php echo $labels["header_vehicle"] ?? "Veículo"; ?></th>
                                    <th scope="col"><?php echo $labels["header_origin"] ?? "Origem"; ?></th>
                                    <th scope="col"><?php echo $labels["header_destination"] ?? "Destino"; ?></th>
                                    <th scope="col"><?php echo $labels["header_start_date"] ?? "Data Início"; ?></th>
                                    <th scope="col"><?php echo $labels["header_end_date"] ?? "Data Fim"; ?></th>
                                    <th scope="col"><?php echo $labels["header_value"] ?? "Valor (USD)"; ?></th>
                                    <th scope="col"><?php echo $labels["actions"] ?? "Ações"; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($res_orc)): ?>
                                <tr>
                                    <td><?php echo $row["ORC_ID"]; ?></td>
                                    <td><?php echo htmlspecialchars($row["emp_nome"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["VEI_MODELO"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["ORC_ORIGEM"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["ORC_DESTINO"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["ORC_DATAINICIO"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["ORC_DATAFIM"]); ?></td>
                                    <td><?php echo number_format($row["ORC_VALOR"], 2); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-warning edit-btn" 
                                            type="button"
                                            data-id="<?php echo $row["ORC_ID"]; ?>" 
                                            data-empresa-id="<?php echo $row["ORC_FK_EMPRESA_ID"]; ?>" 
                                            data-veiculo-id="<?php echo $row["ORC_FK_VEICULO_ID"]; ?>" 
                                            data-origem="<?php echo htmlspecialchars($row["ORC_ORIGEM"]); ?>" 
                                            data-destino="<?php echo htmlspecialchars($row["ORC_DESTINO"]); ?>" 
                                            data-datainicio="<?php echo htmlspecialchars($row["ORC_DATAINICIO"]); ?>" 
                                            data-datafim="<?php echo htmlspecialchars($row["ORC_DATAFIM"]); ?>" 
                                            data-valor="<?php echo $row["ORC_VALOR"]; ?>">
                                            <i class="fas fa-edit me-1"></i><?php echo $labels["edit"] ?? "Editar"; ?>
                                        </button>
                                        <form method="POST" class="d-inline" onsubmit="return confirm(\'<?php echo addslashes($labels["confirm_delete_budget"] ?? "Excluir este orçamento?"); ?>\')">
                                            <input type="hidden" name="delete_orc_id" value="<?php echo $row["ORC_ID"]; ?>">
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

