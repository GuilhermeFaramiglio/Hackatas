<?php
include("utils/conectadb.php");

header('Content-Type: application/json');

if (!isset($_GET['empresa_id'])) {
    echo json_encode([]);
    exit;
}

$empresa_id = intval($_GET['empresa_id']);
$sql = "SELECT END_PAIS, END_CIDADE, END_RUA, END_NUMERO FROM endereco WHERE END_FK_EMPRESA_ID = ?";
if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $empresa_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $enderecos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $enderecos[] = $row;
    }
    mysqli_stmt_close($stmt);

    echo json_encode($enderecos);
} else {
    echo json_encode([]);
}
?>
