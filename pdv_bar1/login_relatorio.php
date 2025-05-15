<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senha_digitada = $_POST['senha'];

    if ($senha_digitada === '1234') {
        $_SESSION['acesso_liberado'] = true;
        header("Location: relatorio.php");
        exit;
    } else {
        $erro = "❌ Senha incorreta!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login do Relatório</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef5ff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-box {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            border: 2px solid #007bff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        input[type="password"] {
            padding: 10px;
            width: 200px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            margin-top: 10px;
            cursor: pointer;
        }

        .erro {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>🔐 Acesso ao Relatório</h2>
    <?php if (!empty($erro)) echo "<div class='erro'>$erro</div>"; ?>
    <form method="POST">
        <input type="password" name="senha" placeholder="Digite a senha">
        <br>
        <input type="submit" value="Entrar">
    </form>
</div>

</body>
</html>
