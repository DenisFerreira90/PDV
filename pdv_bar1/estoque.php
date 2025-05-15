<?php include 'conecta.php'; ?>

<h3>➕ Cadastrar Novo Produto</h3>

<form method="POST" enctype="multipart/form-data" style="margin-bottom: 30px;">
    <label>Nome:</label>
    <input type="text" name="nome" required style="width: 200px;">

    <label>Preço (R$):</label>
    <input type="number" name="preco" step="0.01" required style="width: 120px;">

    <label>Estoque:</label>
    <input type="number" name="estoque" required style="width: 80px;">

    <label>Tipo:</label>
    <select name="tipo" id="tipo" required onchange="atualizarLitragem()">
        <option value="">Selecione</option>
        <option value="Cerveja">Cerveja</option>
        <option value="Refrigerante">Refrigerante</option>
    </select>

<label>Litragem:</label>
<select name="litragem" id="litragem" required>
    <option value="">Selecione o tipo primeiro</option>
</select>


    <label>Litragem:</label>
    <input type="text" name="litragem" placeholder="Ex: 600ml, 2L" required style="width: 100px;">

    <label>Imagem do produto:</label>
    <input type="file" name="imagem" required>

    <button type="submit" class="btn">Cadastrar</button>
</form>


<?php
// Inserção de novo produto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nome'])) {
    $nome = $_POST['nome'];
    $preco = $_POST['preco'];
    $estoque = $_POST['estoque'];
    $tipo = $_POST['tipo'];
    $litragem = $_POST['litragem'];

    // Upload da imagem
    $imagem_nome = $_FILES['imagem']['name'];
    $imagem_tmp = $_FILES['imagem']['tmp_name'];
    $caminho_imagem = "imagens/" . basename($imagem_nome);
    move_uploaded_file($imagem_tmp, $caminho_imagem);

    $conn->query("INSERT INTO produtos (nome, preco, estoque, tipo, litragem, imagem)
                  VALUES ('$nome', $preco, $estoque, '$tipo', '$litragem', '$caminho_imagem')");
    echo "<p style='color:green;'>✅ Produto cadastrado com sucesso!</p>";
}



// Atualização de estoque
if (isset($_GET['id']) && isset($_GET['novo_estoque'])) {
    $id = $_GET['id'];
    $novo_estoque = $_GET['novo_estoque'];
    $conn->query("UPDATE produtos SET estoque = estoque + $novo_estoque WHERE id = $id");
    echo "<p style='color:blue;'>📦 Estoque atualizado!</p>";
}

// Exclusão de produto
if (isset($_GET['excluir_id'])) {
    $excluir_id = $_GET['excluir_id'];
    $conn->query("DELETE FROM produtos WHERE id = $excluir_id");
    echo "<p style='color:red;'>❌ Produto excluído com sucesso!</p>";
}

// Barra de busca
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

// Listar produtos
$query = "SELECT * FROM produtos WHERE nome LIKE '%$search%' OR preco LIKE '%$search%' ORDER BY id DESC";
$result = $conn->query($query);

echo "<h3>📋 Produtos em Estoque</h3>";

// Barra de busca
echo "<form method='GET' style='margin-bottom: 20px;'>
    <input type='text' name='search' value='$search' placeholder='Buscar produto...' style='width: 250px; padding: 5px;'>
    <button type='submit' class='btn' style='padding: 5px 10px;'>Buscar</button>
</form>";

echo "<table style='width: 100%; border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Nome</th><th>Preço (R$)</th><th>Estoque</th><th>Incrementar</th><th>Excluir</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>".$row['id']."</td>";
    echo "<td>".$row['nome']."</td>";
    echo "<td>".number_format($row['preco'], 2, ',', '.')."</td>";
    echo "<td>".$row['estoque']."</td>";
    echo "<td>
        <form method='GET' style='display:flex; gap:5px;'>
            <input type='hidden' name='id' value='".$row['id']."'>
            <input type='number' name='novo_estoque' min='1' style='width: 60px;' required>
            <button class='btn' style='padding: 5px 10px;'>+</button>
        </form>
    </td>";
    echo "<td><button onclick='confirmarExclusao(".$row['id'].")' class='btn' style='background-color: red; color: white;'>Excluir</button></td>";
    echo "</tr>";
}

echo "</table>";
?>

<script>
function confirmarExclusao(id) {
    if (confirm("Tem certeza que deseja excluir este produto?")) {
        window.location.href = "?excluir_id=" + id;
    }
}
function atualizarLitragem() {
    const tipo = document.getElementById("tipo").value;
    const litragem = document.getElementById("litragem");

    const opcoes = {
        Cerveja: ["269 ml", "350 ml", "473 ml", "330 ml (Long Neck)", "600 ml", "1 litro"],
        Refrigerante: ["269 ml", "350 ml", "500 ml", "1 litro", "1,5 litros", "2 litros", "2,5 litros", "3 litros"]
    };

    litragem.innerHTML = ""; // Limpa as opções

    if (opcoes[tipo]) {
        opcoes[tipo].forEach(valor => {
            const option = document.createElement("option");
            option.value = valor;
            option.text = valor;
            litragem.appendChild(option);
        });
    } else {
        const option = document.createElement("option");
        option.value = "";
        option.text = "Selecione o tipo primeiro";
        litragem.appendChild(option);
    }
}
</script>

<style>
    .btn {
        background-color: #2a4d87;
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .btn:hover {
        background-color: #1f3766;
    }
</style>
