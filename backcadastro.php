<?php
session_start();

$dbPath = "./DB/db_pontos.db";
$db = new SQLite3($dbPath);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $name = trim($_POST["name"]);
    $cpf = preg_replace('/\D/', '', $_POST["number"]);
    $password = $_POST["password"];
    $confirmpassword = $_POST["Confirmpassword"];

    if (empty($name) || empty($cpf) || empty($password)) {
        $_SESSION['msg_cadastro'] = "Preencha todos os campos.";
        $_SESSION['type_cadastro'] = "error";
        header("Location: cadastro.php");
        exit;
    }

    if ($password !== $confirmpassword) {
        $_SESSION['msg_cadastro'] = "As senhas não coincidem.";
        $_SESSION['type_cadastro'] = "error";
        header("Location: cadastro.php");
        exit;
    }

    $checkStmt = $db->prepare("SELECT cpf FROM usuarios WHERE cpf = :cpf");
    $checkStmt->bindValue(':cpf', $cpf);
    $result = $checkStmt->execute();

    if ($result->fetchArray()) {
        $_SESSION['msg_cadastro'] = "Este CPF já possui cadastro.";
        $_SESSION['type_cadastro'] = "error";
        header("Location: cadastro.php");
        exit;
    }

    $senhaHash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (nome, cpf, senha, admin) VALUES (:nome, :cpf, :senha, 0)";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':nome', $name);
    $stmt->bindValue(':cpf', $cpf);
    $stmt->bindValue(':senha', $senhaHash);

    if ($stmt->execute()) {
        // SUCESSO: MANDA MENSAGEM PARA O LOGIN (INDEX)
        $_SESSION['msg_index'] = "Cadastro realizado! Faça login.";
        $_SESSION['type_index'] = "success";
        header("Location: index.php"); 
        exit;
    } else {
        // ERRO: MANDA MENSAGEM PARA O CADASTRO
        $_SESSION['msg_cadastro'] = "Erro no banco de dados.";
        $_SESSION['type_cadastro'] = "error";
        header("Location: cadastro.php"); 
        exit;
    }
}
$db->close();
?>