<?php
session_start();

$senha_correta = '1234';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senha = $_POST['senha'] ?? '';
    if ($senha === $senha_correta) {
        $_SESSION['relatorio_liberado'] = true;
        echo 'ok';
    } else {
        echo 'erro';
    }
}
