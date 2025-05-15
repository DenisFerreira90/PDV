<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sistema de Estoque e Vendas</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f0f6ff;
            margin: 0;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #004d99;
        }

        .abas {
            text-align: center;
            margin-bottom: 20px;
        }

        .abas button {
            padding: 10px 20px;
            margin: 0 10px;
            border: 2px solid #007bff;
            background-color: #e6f0ff;
            color: #004d99;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }

        .abas button:hover {
            background-color: #cce0ff;
        }

        .conteudo {
            display: none;
            background-color: white;
            border: 4px solid #007bff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
            max-width: 900px;
            margin: 0 auto;
        }

        .ativo {
            display: block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table, th, td {
            border: 1px solid #007bff;
        }

        th {
            background-color: #e6f0ff;
            color: #003366;
            padding: 8px;
        }

        td {
            padding: 8px;
        }

        input[type="text"], input[type="number"], input[type="password"] {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        .btn {
            background-color: #007bff;
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn:hover {
            background-color: #005bb5;
        }
    </style>
</head>
<body>

<h2>Sistema de Estoque e Vendas</h2>

<div class="abas">
    <button onclick="mostrarAba('estoque')">📦 Estoque</button>
    <button onclick="mostrarAba('vendas')">💰 Vendas</button>
    <button onclick="mostrarAba('relatorio')">📊 Relatórios</button>
</div>

<div id="estoque" class="conteudo ativo">
    <?php include 'estoque.php'; ?>
</div>

<div id="vendas" class="conteudo">
    <?php include 'venda.php'; ?>
</div>

<div id="relatorio" class="conteudo">
    <?php
    if (!isset($_SESSION['relatorio_liberado'])) {
        // Processar a senha se enviada
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['senha'])) {
            if ($_POST['senha'] === '1234') {
                $_SESSION['relatorio_liberado'] = true;
                header("Location: index.php");
                exit;
            } else {
                $erro = "❌ Senha incorreta!";
            }
        }
        ?>
        <h3>🔐 Digite a senha para acessar o relatório:</h3>
        <form method="POST">
            <input type="password" name="senha" placeholder="Senha" required>
            <button class="btn" type="submit">Acessar</button>
        </form>
        <?php if (isset($erro)) echo "<p style='color:red;'>$erro</p>"; ?>
        <?php
    } else {
        include 'relatorio_conteudo.php';
        echo '
        <form method="POST" action="logout_relatorio.php">
            <input class="btn" type="submit" value="🚪 Sair do Relatório">
        </form>';
    }
    ?>
</div>

<script>
function mostrarAba(id) {
    document.querySelectorAll('.conteudo').forEach(el => el.classList.remove('ativo'));
    document.getElementById(id).classList.add('ativo');
}
</script>

</body>
</html>
