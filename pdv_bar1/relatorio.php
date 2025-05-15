<?php include 'conecta.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Relatório de Vendas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f4f7fb;
        }
        h2 {
            color: #2a4d87;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            margin-top: 20px;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #2a4d87;
            color: white;
        }
        input[type="date"], input[type="submit"] {
            padding: 8px;
            margin-right: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            background-color: #2a4d87;
            color: white;
            cursor: pointer;
        }
    </style>
</head>
<body>

<h2>📊 Relatório de Vendas por Dia</h2>

<form method="GET" action="relatorio.php">
    <label>Data: </label>
    <input type="date" name="data" value="<?= $_GET['data'] ?? date('Y-m-d') ?>">
    <input type="submit" value="🔍 Buscar">
</form>

<?php
$data = $_GET['data'] ?? date('Y-m-d');
$sql = "SELECT * FROM vendas WHERE DATE(data_venda) = '$data'";  // Corrigido para usar a coluna 'data_venda'
$res = $conn->query($sql);
$total_dia = 0;

if ($res->num_rows > 0) {
    echo "<table><thead>
            <tr><th>ID</th><th>Total</th><th>Pago</th><th>Troco</th><th>Forma de Pagamento</th><th>Data</th></tr>
          </thead><tbody>";
while ($row = $res->fetch_assoc()) {
    $valor_pago = $row['valor_pago'] ?? 0;
    $forma_pagamento = $row['forma_pagamento'] ?? 'Não Informado!.';
    $troco = $row['troco'] ?? 0;

    $total_dia += $valor_pago;

    echo "<tr>
            <td>{$row['id']}</td>
            <td>R$ " . number_format($valor_pago, 2, ',', '.') . "</td>
            <td>R$ " . number_format($valor_pago, 2, ',', '.') . "</td>
            <td>R$ " . number_format($troco, 2, ',', '.') . "</td>
            <td>{$forma_pagamento}</td>
            <td>" . date('d/m/Y H:i', strtotime($row['data_venda'])) . "</td>
          </tr>";

    }
    echo "</tbody></table>";
    echo "<h3>Total vendido em $data: R$ " . number_format($total_dia, 2, ',', '.') . "</h3>";
} else {
    echo "<p>Nenhuma venda encontrada nesta data.</p>";
}
?>
</body>
</html>
