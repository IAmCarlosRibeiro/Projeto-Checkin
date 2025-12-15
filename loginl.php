<?php
session_start();

// Senhas hard-coded para acesso à página de administração
$senha_administracao_opcao1 = '#DPSKT@2024';
$senha_administracao_opcao2 = '9270';

// Verificar se o formulário de login foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST["password"];

    // Verificar se a senha inserida corresponde a uma das senhas esperadas
    if ($password === $senha_administracao_opcao1 || $password === $senha_administracao_opcao2) {
        // Autenticação bem-sucedida, criar uma sessão de usuário
        $_SESSION['logged_in2'] = true;
        header("Location: lixeira.php"); // Redirecionar para a página de administração
        exit;
    } else {
        // Senha incorreta, exibir mensagem de erro
        $erro_login = "Senha incorreta.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Página de Login</title>
    <link rel="stylesheet" href="./styles/stylelogin.css">
    <link rel="shortcut icon" href="./styles/raposa.ico" type="image/x-icon">
</head>

<body>
    <h1>Login</h1>
    <?php if (isset($erro_login)) echo "<p>$erro_login</p>"; ?>
    <form method="post" action="">
        <label for="password">Senha de administrador:</label><br>
        <input type="password" id="password" name="password"><br><br>
        <button type="submit">Login</button>
    </form>
</body>

</html>