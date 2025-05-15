<?php
include 'conecta.php';

// Lógica para cadastro, atualização e exclusão simplificada para o exemplo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cadastro
    $nome = $_POST['nome'] ?? '';
    $preco = $_POST['preco'] ?? 0;
    $estoque = $_POST['estoque'] ?? 0;
    $tipo = $_POST['tipo'] ?? '';
    $litragem = $_POST['litragem'] ?? '';

    // Upload imagem
    $imagemNome = '';
    if (!empty($_FILES['imagem']['name'])) {
        $uploadDir = 'imagens_produtos/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $imagemNome = basename($_FILES['imagem']['name']);
        $uploadFile = $uploadDir . $imagemNome;
        move_uploaded_file($_FILES['imagem']['tmp_name'], $uploadFile);
    }

    // Inserir no banco
    $stmt = $conn->prepare("INSERT INTO produtos (nome, preco, estoque, tipo, litragem, imagem) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sdisss", $nome, $preco, $estoque, $tipo, $litragem, $imagemNome);
    $stmt->execute();
    $stmt->close();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

if (isset($_GET['excluir_id'])) {
    $idExcluir = intval($_GET['excluir_id']);
    $stmt = $conn->prepare("DELETE FROM produtos WHERE id = ?");
    $stmt->bind_param("i", $idExcluir);
    $stmt->execute();
    $stmt->close();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

if (isset($_GET['id']) && isset($_GET['novo_estoque'])) {
    $idAtualiza = intval($_GET['id']);
    $incremento = intval($_GET['novo_estoque']);
    $stmt = $conn->prepare("UPDATE produtos SET estoque = estoque + ? WHERE id = ?");
    $stmt->bind_param("ii", $incremento, $idAtualiza);
    $stmt->execute();
    $stmt->close();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM produtos";
if ($search) {
    $search = $conn->real_escape_string($search);
    $sql .= " WHERE nome LIKE '%$search%'";
}
$sql .= " ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Estoque de Produtos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f9;
            margin: 0; padding: 0;
        }
        .container {
            max-width: 700px;
            margin: 30px auto;
            padding: 0 15px;
        }
        h3 {
            text-align: center;
            color: #2a4d87;
        }
        form.cadastro, form.busca {
            max-width: 650px;
            margin: 0 auto 30px auto;
            background:#fff;
            padding:20px;
            border-radius:10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        form.cadastro div.flex-row, form.cadastro div.flex-row2 {
            display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; align-items: flex-start;
        }
        form.cadastro div.flex-row > div, form.cadastro div.flex-row2 > div {
            flex: 1 1 250px;
            min-width: 150px;
        }
        label {
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"], input[type="number"], select, input[type="file"] {
            width: 100%;
            padding: 8px;
            border-radius: 6px;
            border: 1.5px solid #ccc;
            box-sizing: border-box;
        }
        button.btn {
            background-color: #2a4d87;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            width: 100%;
            font-size: 18px;
            transition: background-color 0.3s ease;
        }
        button.btn:hover {
            background-color: #1f3766;
        }
        form.busca {
            display: flex;
            gap: 10px;
            padding: 10px 20px;
        }
        form.busca input[type="text"] {
            flex-grow: 1;
        }
        table {
            width: 100%;
            max-width: 650px;
            margin: 0 auto;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 12px rgba(0,0,0,0.08);
        }
        th, td {
            padding: 10px 12px;
            border-bottom: 1px solid #ddd;
            text-align: center;
            font-size: 14px;
        }
        th {
            background-color: #2a4d87;
            color: white;
            font-weight: 700;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        td form {
            display: flex;
            gap: 5px;
            justify-content: center;
            align-items: center;
        }
        td form input[type="number"] {
            width: 60px;
            padding: 5px;
        }
        td button.btn.excluir {
            background-color: #d9534f;
            padding: 6px 10px;
            font-size: 14px;
            font-weight: 700;
        }
        td button.btn.excluir:hover {
            background-color: #c9302c;
        }
        @media (max-width: 700px) {
            .container {
                padding: 0 10px;
            }
            form.cadastro, form.busca, table {
                max-width: 100%;
            }
            th, td {
                font-size: 12px;
                padding: 8px;
            }
            form.cadastro div.flex-row > div, form.cadastro div.flex-row2 > div {
                flex: 1 1 100%;
                min-width: auto;
            }
        }
    </style>
</head>
<body>

<div class="container">

    <h3>➕ Cadastrar Novo Produto</h3>

    <form method="POST" enctype="multipart/form-data" class="cadastro">

        <div class="flex-row">
            <div>
                <label for="nome">Nome:</label>
                <input type="text" name="nome" id="nome" required />
            </div>
            <div>
                <label for="preco">Preço (R$):</label>
                <input type="number" name="preco" id="preco" step="0.01" required />
            </div>
            <div>
                <label for="estoque">Estoque:</label>
                <input type="number" name="estoque" id="estoque" required />
            </div>
        </div>

        <div class="flex-row2" style="margin-top:15px; margin-bottom:15px;">
            <div>
                <label for="tipo">Tipo:</label>
                <select name="tipo" id="tipo" required onchange="atualizarLitragem()">
                    <option value="">Selecione</option>
                    <option value="Cerveja">Cerveja</option>
                    <option value="Refrigerante">Refrigerante</option>
                </select>
            </div>
            <div>
                <label for="litragem">Litragem:</label>
                <select name="litragem" id="litragem" required>
                    <option value="">Selecione o tipo primeiro</option>
                </select>
            </div>
        </div>

        <div style="margin-bottom: 20px;">
            <label for="imagem">Imagem do produto:</label>
            <input type="file" name="imagem" id="imagem" required />
        </div>

        <button type="submit" class="btn">Cadastrar</button>
    </form>

    <h3>📋 Produtos em Estoque</h3>

    <form method="GET" class="busca">
        <input type="text" name="search" placeholder="Buscar produto..." value="<?php echo htmlspecialchars($search); ?>" />
        <button type="submit" class="btn">Buscar</button>
    </form>

    <table>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Preço (R$)</th>
            <th>Estoque</th>
            <th>Incrementar</th>
            <th>Excluir</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['nome']); ?></td>
            <td><?php echo number_format($row['preco'], 2, ',', '.'); ?></td>
            <td><?php echo $row['estoque']; ?></td>
            <td>
                <form method="GET" onsubmit="return validarIncremento(this);">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>" />
                    <input type="number" name="novo_estoque" min="1" required />
                    <button type="submit" class="btn">+</button>
                </form>
            </td>
            <td>
                <button onclick="confirmarExclusao(<?php echo $row['id']; ?>)" class="btn excluir">Excluir</button>
            </td>
        </tr>
        <?php } ?>
    </table>

</div>

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
        Cerveja: ["269 ml", "330 ml (Long Neck)", "350 ml", "473 ml", "600 ml", "1 litro"],
        Refrigerante: ["200 ml", "237 ml", "250 ml", "290 ml", "350 ml", "500 ml", "600 ml", "1 litro", "1,5 litros", "2 litros", "2,25 litros", "3 litros"]
    };
    litragem.innerHTML = "";
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

function validarIncremento(form) {
    const val = form.novo_estoque.value;
    if (val <= 0 || isNaN(val)) {
        alert("Por favor, informe um número válido para incrementar o estoque.");
        return false;
    }
    return true;
}
</script>

</body>
</html>
