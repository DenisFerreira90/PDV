<?php
include 'conecta.php';
$data = $_GET['data'] ?? date('Y-m-d');

$sql = "SELECT * FROM vendas WHERE DATE(data_venda) = '$data'";
$res = $conn->query($sql);
$total_dia = 0;

echo "<h3>📆 Relatório de Vendas do dia: " . date('d/m/Y', strtotime($data)) . "</h3>";
echo '<form method="GET" action="index.php">
        <input type="date" name="data" value="' . $data . '">
        <input class="btn" type="submit" value="🔍 Buscar">
      </form>';

if ($res->num_rows > 0) {
    echo "<table><thead>
            <tr><th>ID</th><th>Total</th><th>Pago</th><th>Troco</th><th>Forma</th><th>Data</th></tr>
          </thead><tbody>";
    while ($row = $res->fetch_assoc()) {
        $valor_pago = $row['valor_pago'] ?? 0;
        $forma = $row['forma_pagamento'] ?? 'N/I';
        $troco = $row['troco'] ?? 0;
        $total_dia += $valor_pago;
        echo "<tr>
                <td>{$row['id']}</td>
                <td>R$ " . number_format($valor_pago, 2, ',', '.') . "</td>
                <td>R$ " . number_format($valor_pago, 2, ',', '.') . "</td>
                <td>R$ " . number_format($troco, 2, ',', '.') . "</td>
                <td>{$forma}</td>
                <td>" . date('d/m/Y H:i', strtotime($row['data_venda'])) . "</td>
              </tr>";
    }
    echo "</tbody></table>";
    echo "<h4>Total vendido no dia: R$ " . number_format($total_dia, 2, ',', '.') . "</h4>";
} else {
    echo "<p>Nenhuma venda encontrada nesta data.</p>";
}
?>
