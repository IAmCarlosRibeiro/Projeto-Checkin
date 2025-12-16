<?php
session_start();

// Senha hardcoded para acesso à página de administração
$senha_administracao = '9270';

// Verificar se o formulário de login foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST["password"];

    // Verificar se a senha inserida corresponde à senha hardcoded
    if ($password === $senha_administracao) {
        // Autenticação bem-sucedida, criar uma sessão de usuário
        $_SESSION['logged_in'] = true;
        header("Location: adm.php"); // Redirecionar para a página de administração
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
    <link rel="shortcut icon" href="./styles/clock.ico" type="image/x-icon">
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