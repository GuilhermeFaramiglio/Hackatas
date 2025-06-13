<?php
include("utils/conectadb.php");
session_start();

$lang = $_COOKIE["lang"] ?? "en";
$labels = include "$lang.php";

if (!isset($_SESSION['idusuario'])) {
    $alert_msg = $labels["not_logged_in_error"] ?? 'Usuário não logado!';
    echo "<script>alert('{$alert_msg}');</script>";
    echo "<script>window.location.href = 'login.php';</script>";
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
                    html += `<div style="padding: 5px; border-bottom: 1px dashed #ccc;">
                        <b><?php echo addslashes($labels["country_label"] ?? 'País:'); ?></b> ${e.END_PAIS} | 
                        <b><?php echo addslashes($labels["city_label"] ?? 'Cidade:'); ?></b> ${e.END_CIDADE} | 
                        <b><?php echo addslashes($labels["street_label"] ?? 'Rua:'); ?></b> ${e.END_RUA} | 
                        <b><?php echo addslashes($labels["number_label"] ?? 'Número:'); ?></b> ${e.END_NUMERO}
                    </div>`;
                });
            } else {
                html = '<?php echo addslashes($labels["no_addresses_found"] ?? "Nenhum endereço cadastrado."); ?>';
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
    });
    </script>
</head>
<body style="background-size: cover; background-attachment: fixed; background-image: url('img/marcasp.png');">
<header>
    <h1><?php echo $labels["budget"] ?? "Orçamentos"; ?></h1>
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
    <h2><?php echo $labels["budget_registration"] ?? "Cadastro de Orçamento"; ?></h2>
    <?php if (isset($success_msg)): ?>
        <div class="success"><?php echo $success_msg; ?></div>
    <?php endif; ?>
    <form method="POST" id="orcamento-form" autocomplete="off">
        <input type="hidden" name="edit_orc_id" id="edit_orc_id">
        <div class="form-group">
            <label for="orc_empresa"><?php echo $labels["company_label"] ?? "Empresa:"; ?></label>
            <select name="orc_empresa" id="orc_empresa" required>
                <option value=""><?php echo $labels["select_option"] ?? "Selecione"; ?></option>
                <?php foreach ($empresas as $e): ?>
                    <option value="<?php echo $e['emp_id']; ?>"><?php echo htmlspecialchars($e['emp_nome']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div id="enderecos-empresa" style="margin-bottom:15px; font-size:90%; padding:10px; background:#f9f9f9; border-radius:4px;"></div>
        <div class="form-group">
            <label for="orc_veiculo"><?php echo $labels["vehicle_label"] ?? "Veículo:"; ?></label>
            <select name="orc_veiculo" id="orc_veiculo" required>
                <option value=""><?php echo $labels["select_option"] ?? "Selecione"; ?></option>
                <?php foreach ($veiculos as $v): ?>
                    <option value="<?php echo $v['VEI_ID']; ?>"><?php echo htmlspecialchars($v['VEI_MODELO']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="orc_origem"><?php echo $labels["origin_label"] ?? "Origem:"; ?></label>
            <input type="text" name="orc_origem" id="orc_origem" required>
        </div>
        <div class="form-group">
            <label for="orc_destino"><?php echo $labels["destination_label"] ?? "Destino:"; ?></label>
            <input type="text" name="orc_destino" id="orc_destino" required>
        </div>
        <div class="form-group">
            <label for="orc_datainicio"><?php echo $labels["start_date_label"] ?? "Data Início:"; ?></label>
            <input type="date" name="orc_datainicio" id="orc_datainicio" required>
        </div>
        <div class="form-group">
            <label for="orc_datafim"><?php echo $labels["end_date_label"] ?? "Data Fim:"; ?></label>
            <input type="date" name="orc_datafim" id="orc_datafim" required>
        </div>
        <div class="form-group">
            <label for="orc_valor"><?php echo $labels["value_label"] ?? "Valor (USD):"; ?></label>
            <input type="text" name="orc_valor" id="orc_valor" readonly>
        </div>
        <button type="submit" id="submit-btn"><?php echo $labels["register_budget_btn"] ?? "Cadastrar Orçamento"; ?></button>
        <button type="reset" id="clear-btn"><?php echo $labels["clear_btn"] ?? "Limpar"; ?></button>
    </form>

    <h3 style="margin-top: 40px; border-top: 1px solid #ccc; padding-top: 20px;"><?php echo $labels["registered_budgets_list"] ?? "Orçamentos Cadastrados"; ?></h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th><?php echo $labels["header_company"] ?? "Empresa"; ?></th>
                <th><?php echo $labels["header_vehicle"] ?? "Veículo"; ?></th>
                <th><?php echo $labels["header_origin"] ?? "Origem"; ?></th>
                <th><?php echo $labels["header_destination"] ?? "Destino"; ?></th>
                <th><?php echo $labels["header_start_date"] ?? "Data Início"; ?></th>
                <th><?php echo $labels["header_end_date"] ?? "Data Fim"; ?></th>
                <th><?php echo $labels["header_value"] ?? "Valor (USD)"; ?></th>
                <th><?php echo $labels["actions"] ?? "Ações"; ?></th>
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
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="delete_orc_id" value="<?php echo $row["ORC_ID"]; ?>">
                        <button type="submit" onclick="return confirm('<?php echo addslashes($labels['confirm_delete_budget'] ?? 'Excluir este orçamento?'); ?>')">
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