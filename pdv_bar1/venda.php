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
        /* Produtos com imagem grid */
        #listaProdutos {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .produtoItem {
            border: 1px solid #ccc;
            background: #fff;
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
            user-select: none;
            text-align: center;
            transition: box-shadow 0.3s;
        }
        .produtoItem:hover {
            box-shadow: 0 0 10px #2a4d87;
        }
        .produtoItem img {
            max-width: 100%;
            height: 100px;
            object-fit: contain;
            margin-bottom: 8px;
        }
        .produtoNome {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 4px;
        }
        /* Filtro */
        #filtros button {
            margin-right: 10px;
            padding: 6px 14px;
            border: none;
            background: #2a4d87;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        #filtros button:hover {
            background: #1f3766;
        }
        #filtros button.active {
            background: #14529e;
        }
    </style>
</head>
<body>

<h2>🛒 Nova Venda</h2>

<div id="filtros">
    <button data-tipo="" class="active">Todos</button>
    <button data-tipo="Cerveja">Cervejas</button>
    <button data-tipo="Refrigerante">Refrigerantes</button>
</div>

<div id="listaProdutos"></div>

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
                    <option value="Cartão à vista">Cartão à vista</option>
                    <option value="Cartão crédito">Cartão crédito</option>
                    <option value="PIX">PIX</option>
                </select>
            </div>
        </div>

        <input type="hidden" name="itens_json" id="itens_json">
        <input type="submit" value="✅ Finalizar Venda">
    </div>
</form>

<script>
// Dados dos produtos vindos do PHP
let produtos = <?php
    $res = $conn->query("SELECT * FROM produtos");
    $lista = [];
    while ($row = $res->fetch_assoc()) {
        $lista[] = $row;
    }
    echo json_encode($lista);
?>;

const listaProdutosDiv = document.getElementById('listaProdutos');
const filtros = document.getElementById('filtros');
const produtosBody = document.getElementById('produtosBody');

let filtroAtual = '';

function renderizarProdutos() {
    listaProdutosDiv.innerHTML = '';
    const filtrados = filtroAtual ? produtos.filter(p => p.tipo === filtroAtual) : produtos;
    filtrados.forEach((p, i) => {
        const div = document.createElement('div');
        div.className = 'produtoItem';
        div.setAttribute('data-index', i);
        div.setAttribute('data-id', p.id);
        div.setAttribute('data-nome', p.nome);
        div.setAttribute('data-preco', p.preco);
        div.setAttribute('data-estoque', p.estoque);

        div.innerHTML = `
            <img src="imagens/${p.imagem}" alt="${p.nome}">
            <div class="produtoNome">${p.nome}</div>
            <div>R$ ${parseFloat(p.preco).toFixed(2)}</div>
            <div>Estoque: ${p.estoque}</div>
            <div><small>Tecla: ${i+1}</small></div>
        `;

        // Clicar adiciona produto no carrinho
        div.onclick = () => {
            adicionarProdutoCarrinho(p.id);
        }

        listaProdutosDiv.appendChild(div);
    });
}

function adicionarProdutoCarrinho(id) {
    const produto = produtos.find(p => p.id == id);
    if (!produto) return;

    // Verificar se já existe no carrinho
    for (let row of produtosBody.rows) {
        let select = row.cells[0].querySelector('select');
        if (select.value == id) {
            let inputQtd = row.cells[2].querySelector('input');
            let novaQtd = parseInt(inputQtd.value) + 1;
            if (novaQtd > produto.estoque) {
                alert('⚠ Estoque insuficiente.');
                return;
            }
            inputQtd.value = novaQtd;
            inputQtd.dispatchEvent(new Event('input'));
            calcularTotais();
            return;
        }
    }

    // Caso não exista, adiciona nova linha e seleciona o produto
    const tr = document.createElement('tr');

    // Produto (select)
    const tdProduto = document.createElement('td');
    const select = document.createElement('select');
    select.innerHTML = '<option value="">-- Selecione --</option>';
    produtos.forEach(p => {
        select.innerHTML += `<option value="${p.id}" data-preco="${p.preco}" data-nome="${p.nome}" data-estoque="${p.estoque}">${p.nome} (Estoque: ${p.estoque})</option>`;
    });
    select.value = produto.id;
    tdProduto.appendChild(select);

    // Preço
    const tdPreco = document.createElement('td');
    tdPreco.innerText = parseFloat(produto.preco).toFixed(2);

    // Quantidade
    const tdQtd = document.createElement('td');
    const inputQtd = document.createElement('input');
    inputQtd.type = 'number';
    inputQtd.min = 1;
    inputQtd.max = produto.estoque;
    inputQtd.value = 1;
    tdQtd.appendChild(inputQtd);

    // Subtotal
    const tdSubtotal = document.createElement('td');
    tdSubtotal.innerText = parseFloat(produto.preco).toFixed(2);

    // Ação
    const tdAcao = document.createElement('td');
    const btnRemover = document.createElement('button');
    btnRemover.type = 'button';
    btnRemover.innerText = 'Remover';
    btnRemover.onclick = () => {
        tr.remove();
        calcularTotais();
    };
    tdAcao.appendChild(btnRemover);

    tr.append(tdProduto, tdPreco, tdQtd, tdSubtotal, tdAcao);
    produtosBody.appendChild(tr);

    // Eventos
    select.onchange = () => {
        const preco = parseFloat(select.selectedOptions[0].dataset.preco || 0);
        const estoque = parseInt(select.selectedOptions[0].dataset.estoque || 1);
        tdPreco.innerText = preco.toFixed(2);
        inputQtd.disabled = false;
        inputQtd.max = estoque;
        inputQtd.value = 1;
        tdSubtotal.innerText = preco.toFixed(2);
        calcularTotais();
    };

    inputQtd.oninput = () => {
        const preco = parseFloat(select.selectedOptions[0].dataset.preco || 0);
        const qtd = parseInt(inputQtd.value || 1);
        const estoque = parseInt(select.selectedOptions[0].dataset.estoque || 1);
        if (qtd > estoque) {
            alert('⚠ Estoque insuficiente.');
            inputQtd.value = estoque;
        }
        tdSubtotal.innerText = (preco * qtd).toFixed(2);
        calcularTotais();
    };

    calcularTotais();
}

function calcularTotais() {
    let total = 0;
    for (let row of produtosBody.rows) {
        total += parseFloat(row.cells[3].innerText || 0);
    }
    document.getElementById('total').innerText = total.toFixed(2);
    atualizarTroco();
}

function atualizarTroco() {
    const total = parseFloat(document.getElementById('total').innerText);
    const pago = parseFloat(document.getElementById('pago').value || 0);
    const troco = pago - total;
    document.getElementById('troco').innerText = troco.toFixed(2);
}

function prepararEnvio() {
    let itens = [];
    for (let row of produtosBody.rows) {
        const select = row.cells[0].querySelector('select');
        const qtd = row.cells[2].querySelector('input').value;
        if (select.value && qtd > 0) {
            itens.push({
                id: select.value,
                nome: select.selectedOptions[0].dataset.nome,
                preco: parseFloat(select.selectedOptions[0].dataset.preco),
                quantidade: parseInt(qtd)
            });
        }
    }
    if (itens.length === 0) {
        alert('Adicione ao menos um produto para vender.');
        return false;
    }

    document.getElementById('itens_json').value = JSON.stringify(itens);
    return true;
}

// Filtrar produtos por tipo
filtros.querySelectorAll('button').forEach(btn => {
    btn.onclick = () => {
        filtros.querySelectorAll('button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        filtroAtual = btn.getAttribute('data-tipo');
        renderizarProdutos();
    };
});

// Atalho teclado 1-9 para adicionar produto direto (da lista filtrada)
document.addEventListener('keydown', e => {
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT') return; // evita em inputs
    const key = e.key;
    if (/^[1-9]$/.test(key)) {
        let index = parseInt(key) - 1;
        let filtrados = filtroAtual ? produtos.filter(p => p.tipo === filtroAtual) : produtos;
        if (index < filtrados.length) {
            adicionarProdutoCarrinho(filtrados[index].id);
        }
    }
});

// Inicializa com todos produtos
renderizarProdutos();

</script>

</body>
</html>
