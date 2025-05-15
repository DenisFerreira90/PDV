<?php include 'conecta.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Nova Venda</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f4f7fb;
            color: #333;
        }
        h2 {
            color: #2a4d87;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            margin-bottom: 20px;
            border-radius: 8px;
            overflow: hidden;
        }
        th {
            background-color: #2a4d87;
            color: #fff;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }
        input[type='number'], select {
            padding: 6px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 100%;
        }
        button, input[type="submit"] {
            background-color: #2a4d87;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover, input[type="submit"]:hover {
            background-color: #1f3766;
        }
        .resumo-venda {
            margin-top: 20px;
            background-color: #fff;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        .totais-container {
            display: flex;
            gap: 20px;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        .totais-container div {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .totais-container input {
            width: 100px;
        }
    </style>
</head>
<body>

<h2>🛒 Nova Venda</h2>

<form method="POST" action="processa_venda.php" onsubmit="return prepararEnvio()">

    <table id="tabelaProdutos">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Preço</th>
                <th>Qtd</th>
                <th>Subtotal</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody id="produtosBody"></tbody>
    </table>

    <button type="button" onclick="adicionarLinha()">+ Adicionar Produto</button>

    <div class="resumo-venda">
        <div class="totais-container">
            <div><strong>Total:</strong> R$ <span id="total">0.00</span></div>

            <div>
                <label for="pago"><strong>Valor pago:</strong></label>
                <input type="number" id="pago" name="pago" step="0.01" required oninput="atualizarTroco()">
            </div>

            <div><strong>Troco:</strong> R$ <span id="troco">0.00</span></div>
			<div>
    <label for="forma_pagamento"><strong>Forma de Pagamento:</strong></label>
    <select id="forma_pagamento" name="forma_pagamento" required>
        <option value="">Selecione</option>
        <option value="Dinheiro">Dinheiro</option>
        <option value="Cartão">Cartão</option>
        <option value="PIX">PIX</option>
        <option value="Outros">Outros</option>
    </select>
</div>

        </div>

        <input type="hidden" name="itens_json" id="itens_json">
        <input type="submit" value="✅ Finalizar Venda">
    </div>
</form>

<script>
let produtos = <?php
    $res = $conn->query("SELECT * FROM produtos");
    $lista = [];
    while ($row = $res->fetch_assoc()) {
        $lista[] = $row;
    }
    echo json_encode($lista);
?>;

function adicionarLinha() {
    const tbody = document.getElementById("produtosBody");

    const tr = document.createElement("tr");

    const tdProduto = document.createElement("td");
    const select = document.createElement("select");
    select.innerHTML = '<option value="">-- Selecione --</option>';
    produtos.forEach(p => {
        select.innerHTML += `<option value="${p.id}" data-preco="${p.preco}" data-nome="${p.nome}" data-estoque="${p.estoque}">
                                ${p.nome} (Estoque: ${p.estoque})
                            </option>`;
    });
    tdProduto.appendChild(select);

    const tdPreco = document.createElement("td");
    tdPreco.innerText = "0.00";

    const tdQtd = document.createElement("td");
    const inputQtd = document.createElement("input");
    inputQtd.type = "number";
    inputQtd.min = "1";
    inputQtd.value = "1";
    inputQtd.disabled = true;
    tdQtd.appendChild(inputQtd);

    const tdSubtotal = document.createElement("td");
    tdSubtotal.innerText = "0.00";

    const tdAcao = document.createElement("td");
    const btnRemover = document.createElement("button");
    btnRemover.type = "button";
    btnRemover.innerText = "Remover";
    btnRemover.onclick = () => {
        tr.remove();
        calcularTotais();
    };
    tdAcao.appendChild(btnRemover);

    tr.append(tdProduto, tdPreco, tdQtd, tdSubtotal, tdAcao);
    tbody.appendChild(tr);

    // Eventos
    select.onchange = () => {
        const preco = parseFloat(select.selectedOptions[0].dataset.preco || 0);
        const estoque = parseInt(select.selectedOptions[0].dataset.estoque || 1);
        tdPreco.innerText = preco.toFixed(2);
        inputQtd.disabled = false;
        inputQtd.max = estoque;
        inputQtd.value = ""; // Campo começa vazio
        inputQtd.placeholder = "0"; // Só orientação visual

        tdSubtotal.innerText = preco.toFixed(2);
        calcularTotais();
    };

    inputQtd.oninput = () => {
        const preco = parseFloat(select.selectedOptions[0].dataset.preco || 0);
        const qtd = parseInt(inputQtd.value || 1);
        const estoque = parseInt(select.selectedOptions[0].dataset.estoque || 1);
        if (qtd > estoque) {
            alert("⚠ Estoque insuficiente.");
            inputQtd.value = estoque;
        }
        tdSubtotal.innerText = (preco * qtd).toFixed(2);
        calcularTotais();
    };
}

function calcularTotais() {
    const tbody = document.getElementById("produtosBody");
    let total = 0;

    for (let row of tbody.rows) {
        const subtotal = parseFloat(row.cells[3].innerText || 0);
        total += subtotal;
    }

    document.getElementById("total").innerText = total.toFixed(2);
    atualizarTroco();
}

function atualizarTroco() {
    const total = parseFloat(document.getElementById("total").innerText);
    const pago = parseFloat(document.getElementById("pago").value || 0);
    const troco = pago - total;
    document.getElementById("troco").innerText = troco.toFixed(2);
}

function prepararEnvio() {
    const tbody = document.getElementById("produtosBody");
    let itens = [];

    for (let row of tbody.rows) {
        const select = row.cells[0].querySelector("select");
        const id = parseInt(select.value);
        const qtd = parseInt(row.cells[2].querySelector("input").value);
        const subtotal = parseFloat(row.cells[3].innerText);

        if (!id || qtd < 1) continue;

        itens.push({ id, qtd, subtotal });
    }

    if (itens.length === 0) {
        alert("⚠ Adicione ao menos um produto.");
        return false;
    }

    document.getElementById("itens_json").value = JSON.stringify(itens);
    return true;
}
</script>

</body>
</html>
