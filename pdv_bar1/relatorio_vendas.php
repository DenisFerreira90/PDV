<?php
include 'conecta.php';

$result = $conn->query("SELECT v.*, p.nome AS produto_nome, c.nome AS categoria_nome 
                        FROM vendas v 
                        JOIN itens_venda iv ON v.id = iv.venda_id 
                        JOIN produtos p ON iv.produto_id = p.id
                        JOIN categorias c ON p.categoria_id = c.id
                        ORDER BY v.data_venda DESC");

echo "<h2>📊 Relatório de Vendas</h2>";
echo "<table border='1' cellpadding='8'>";
echo "<tr><th>ID Venda</th><th>Data</th><th>Produto</th><th>Categoria</th><th>Total</th><th>Pago</th><th>Troco</th></tr>";

while ($venda = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$venda['id']}</td>";
    echo "<td>{$venda['data_venda']}</td>";
    echo "<td>{$venda['produto_nome']}</td>";
    echo "<td>{$venda['categoria_nome']}</td>";
    echo "<td>R$ " . number_format($venda['total'], 2, ',', '.') . "</td>";
    echo "<td>R$ " . number_format($venda['pago'], 2, ',', '.') . "</td>";
    echo "<td>R$ " . number_format($venda['troco'], 2, ',', '.') . "</td>";
    echo "</tr>";
}

echo "</table>";
?>
