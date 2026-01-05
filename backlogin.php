<!--
"""
    Ponto Online
    Copyright (c) 2026 Carlos Eduardo Santos Ribeiro.
    All Rights Reserved.

    This software is PROPRIETARY. Use is subject to the terms in the LICENSE file.
    Unauthorized distribution, modification, or commercial use is strictly prohibited.
"""
-->

<?php
session_start();
$dbPath = "./DB/db_pontos.db";
$db = new SQLite3($dbPath);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Limpeza do CPF (remove pontos e traços)
    $cpf = preg_replace('/\D/', '', $_POST["cpf"]); 
    $password = $_POST["password"];

    // Validação básica
    if (empty($cpf) || empty($password)) {
        $_SESSION['msg_login'] = "Preencha todos os campos.";
        $_SESSION['type_login'] = "error";
        header("Location: login.php");
        exit;
    }

    // Busca usuário no banco
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE cpf = :cpf");
    $stmt->bindValue(':cpf', $cpf);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);

    // Verifica Usuário e Senha (Hash)
    if ($user && password_verify($password, $user['senha'])) {
        
        $nivel = $user['admin']; // 0, 1 ou 2

        // --- ROTEAMENTO POR NÍVEL ---
        
        if ($nivel == 1) {
            // ADMIN TOTAL (Acesso a tudo)
            $_SESSION['logged_in'] = true;  // Permissão Dashboard
            $_SESSION['logged_in2'] = true; // Permissão Lixeira
            $_SESSION['admin_nome'] = $user['nome'];
            
            header("Location: admin.php");
            exit;

        } elseif ($nivel == 2) {
            // MODERADOR (Apenas Lixeira)
            $_SESSION['logged_in2'] = true; // Permissão Lixeira
            $_SESSION['admin_nome'] = $user['nome'];
            
            header("Location: lixeira.php");
            exit;

        } else {
            // USUÁRIO COMUM (Nível 0) - Acesso Negado aqui
            $_SESSION['msg_login'] = "Você não tem permissão administrativa.";
            $_SESSION['type_login'] = "error";
            header("Location: login.php");
            exit;
        }

    } else {
        // Senha errada ou usuário inexistente
        $_SESSION['msg_login'] = "CPF ou Senha incorretos.";
        $_SESSION['type_login'] = "error";
        header("Location: login.php");
        exit;
    }
}
$db->close();
?>