<?php
// config_demo.php - Configuração para demonstração sem MySQL

// Simular dados em arrays para demonstração
$_SESSION['demo_empresas'] = [
    ['id' => 1, 'nome' => 'Empresa Demo', 'cnpj' => '12345678901234', 'email' => 'demo@example.com', 'telefone' => '+971-123-456-789']
];

$_SESSION['demo_veiculos'] = [
    ['id' => 1, 'tipo' => 'Caminhão', 'capacidade' => 5000, 'placa' => 'ABC-1234', 'empresa_id' => 1]
];

$_SESSION['demo_orcamentos'] = [
    ['id' => 1, 'empresa_id' => 1, 'veiculo_id' => 1, 'origem' => 'Dubai', 'destino' => 'Abu Dhabi', 'data_inicio' => '2025-01-15', 'data_fim' => '2025-01-17', 'valor' => 450.00]
];

// Função para simular conexão com banco
function demo_query($sql) {
    // Retorna dados simulados baseados na query
    if (strpos($sql, 'COUNT') !== false) {
        if (strpos($sql, 'empresa') !== false) return [['count' => count($_SESSION['demo_empresas'])]];
        if (strpos($sql, 'veiculo') !== false) return [['count' => count($_SESSION['demo_veiculos'])]];
        if (strpos($sql, 'orcamento') !== false) return [['count' => count($_SESSION['demo_orcamentos'])]];
    }
    
    if (strpos($sql, 'SUM') !== false) {
        $total = 0;
        foreach ($_SESSION['demo_orcamentos'] as $orc) {
            $total += $orc['valor'];
        }
        return [['total' => $total]];
    }
    
    return [];
}
?>

