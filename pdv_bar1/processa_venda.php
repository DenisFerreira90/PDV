<?php
include 'conecta.php';

$itens = json_decode($_POST['itens_json'], true);
$pago = isset($_POST['pago']) ? floatval($_POST['pago']) : 0;
$total = 0;

foreach ($itens as $item) {
    $total += $item['subtotal'];
}

if ($pago < $total) {
    die("<div class='message error'><h2>❌ Valor pago insuficiente.</h2></div>");
}

$troco = $pago - $total;

// Verifique se a forma de pagamento foi enviada corretamente
if (empty($_POST['forma_pagamento'])) {
    die("<div class='message error'><h2>❌ Forma de pagamento não fornecida.</h2></div>");
}

// Proteja contra SQL Injection usando real_escape_string
$forma_pagamento = $conn->real_escape_string($_POST['forma_pagamento']);

// Inserir a venda na tabela 'vendas' (forma_pagamento, valor_pago, troco)
$sql = "INSERT INTO vendas (forma_pagamento, valor_pago, troco) 
        VALUES ('$forma_pagamento', $pago, $troco)";

// Verifique se a consulta foi executada corretamente
if ($conn->query($sql) === TRUE) {
    // Obter o ID da venda recém-criada
    $venda_id = $conn->insert_id;

    // Inserir os itens de venda
    foreach ($itens as $item) {
        $produto = $conn->query("SELECT * FROM produtos WHERE id = {$item['id']}")->fetch_assoc();

        if ($produto['estoque'] < $item['qtd']) {
            die("<div class='message error'><h2>❌ Estoque insuficiente para o produto: " . htmlspecialchars($produto['nome']) . "</h2></div>");
        }

        $conn->query("INSERT INTO itens_venda (venda_id, produto_id, quantidade, subtotal)
                      VALUES ($venda_id, {$item['id']}, {$item['qtd']}, {$item['subtotal']})");

        // Atualizar o estoque do produto
        $conn->query("UPDATE produtos SET estoque = estoque - {$item['qtd']} WHERE id = {$item['id']}");
    }

    // Exibe uma mensagem de sucesso
    echo "<div class='message success'>
            <h2>✅ Venda realizada com sucesso!</h2>
            <p><strong>Total: </strong>R$ " . number_format($total, 2, ',', '.') . "</p>
            <p><strong>Pago: </strong>R$ " . number_format($pago, 2, ',', '.') . "</p>
            <p><strong>Troco: </strong>R$ " . number_format($troco, 2, ',', '.') . "</p>
            <a href='index.php' class='btn-retorno'>Voltar ao Início</a>
          </div>";
} else {
    // Caso haja um erro na execução da consulta
    die("<div class='message error'><h2>❌ Erro ao registrar a venda. Tente novamente.</h2></div>");
}
?>

<style>
    /* Estilo básico */
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f0f6ff;
        margin: 0;
        padding: 0;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .message {
        background-color: #fff;
        border-radius: 20px;
        padding: 40px;
        width: 450px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .success {
        border: 3px solid #28a745;
        background-color: #d4edda;
        color: #155724;
    }

    .error {
        border: 3px solid #dc3545;
        background-color: #f8d7da;
        color: #721c24;
    }

    h2 {
        font-size: 24px;
        color: #333;
        margin-bottom: 20px;
    }

    p {
        font-size: 18px;
        color: #333;
        margin-bottom: 15px;
    }

    .btn-retorno {
        display: inline-block;
        background-color: #007bff;
        color: white;
        padding: 12px 24px;
        border-radius: 10px;
        text-decoration: none;
        font-size: 16px;
        transition: background-color 0.3s ease;
    }

    .btn-retorno:hover {
        background-color: #0056b3;
    }
</style>
