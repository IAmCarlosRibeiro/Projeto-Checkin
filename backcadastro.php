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
    
    // 1. Sanitização
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]); // Novo campo
    $cpf = preg_replace('/\D/', '', $_POST["number"]);
    $password = $_POST["password"];
    $confirmpassword = $_POST["Confirmpassword"];

    // 2. Validação: Campos Vazios
    if (empty($name) || empty($email) || empty($cpf) || empty($password)) {
        $_SESSION['msg_cadastro'] = "Preencha todos os campos.";
        $_SESSION['type_cadastro'] = "error";
        header("Location: cadastro.php");
        exit;
    }

    // 3. Validação: Nome Completo (Pelo menos um espaço)
    if (strpos($name, ' ') === false) {
        $_SESSION['msg_cadastro'] = "Por favor, digite seu Nome e Sobrenome.";
        $_SESSION['type_cadastro'] = "error";
        header("Location: cadastro.php");
        exit;
    }

    // 4. Validação: Senhas
    if ($password !== $confirmpassword) {
        $_SESSION['msg_cadastro'] = "As senhas não coincidem.";
        $_SESSION['type_cadastro'] = "error";
        header("Location: cadastro.php");
        exit;
    }

    // 5. Validação: Duplicidade (CPF)
    $checkStmt = $db->prepare("SELECT cpf FROM usuarios WHERE cpf = :cpf");
    $checkStmt->bindValue(':cpf', $cpf);
    $result = $checkStmt->execute();

    if ($result->fetchArray()) {
        $_SESSION['msg_cadastro'] = "Este CPF já possui cadastro.";
        $_SESSION['type_cadastro'] = "error";
        header("Location: cadastro.php");
        exit;
    }

    // 6. Criptografia e Inserção
    $senhaHash = password_hash($password, PASSWORD_DEFAULT);

    // Inserindo o email e definindo status padrão como 'ativo'
    $sql = "INSERT INTO usuarios (nome, email, cpf, senha, admin, status) VALUES (:nome, :email, :cpf, :senha, 0, 'ativo')";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':nome', $name);
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':cpf', $cpf);
    $stmt->bindValue(':senha', $senhaHash);

    if ($stmt->execute()) {
        $_SESSION['msg_index'] = "Cadastro realizado! Faça login.";
        $_SESSION['type_index'] = "success";
        header("Location: index.php"); 
        exit;
    } else {
        $_SESSION['msg_cadastro'] = "Erro no banco de dados.";
        $_SESSION['type_cadastro'] = "error";
        header("Location: cadastro.php"); 
        exit;
    }
}
$db->close();
?>