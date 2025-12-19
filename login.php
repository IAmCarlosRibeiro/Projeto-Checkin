<?php
session_start();

// 1. Se for ADMIN, manda pro Dashboard
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: admin.php");
    exit;
}

// 2. Se for MODERADOR, manda pra Lixeira
// (O Admin também tem logged_in2, mas ele cai no if de cima primeiro, então funciona)
if (isset($_SESSION['logged_in2']) && $_SESSION['logged_in2'] === true) {
    header("Location: lixeira.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área Restrita</title>
    
    <!-- Seu CSS Exclusivo de Login -->
    <link rel="stylesheet" href="./styles/stylelogin.css">
    <link rel="shortcut icon" href="./styles/clock.ico" type="image/x-icon">

    <style>
        /* Pequeno ajuste para garantir que o campo de Texto (CPF) 
           fique idêntico ao de Senha do seu CSS original */
        input[type="text"] {
            border: none;
            background: #f2f2f2;
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            box-sizing: border-box;
            font-family: inherit;
        }

        /* Estilo das mensagens de erro/sucesso (Sessão) */
        .msg-box {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-size: 0.9rem;
            text-align: center;
            font-weight: bold;
        }
        .error { background-color: #ffcccc; color: #cc0000; }
        .success { background-color: #ccffcc; color: #006600; }

        /* Link de voltar discreto */
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
            color: #333;
            font-size: 0.85rem;
            opacity: 0.7;
        }
        .back-link:hover { opacity: 1; text-decoration: underline; }
    </style>
</head>

<body>
    <h1>Área Restrita</h1>
    
    <form method="post" action="backlogin.php">
        
        <!-- Exibição de Mensagens (Flash Messages) -->
        <?php if (isset($_SESSION['msg_login'])): ?>
            <div class="msg-box <?php echo ($_SESSION['type_login'] == 'success') ? 'success' : 'error'; ?>">
                <?php 
                    echo $_SESSION['msg_login']; 
                    unset($_SESSION['msg_login']);
                    unset($_SESSION['type_login']);
                ?>
            </div>
        <?php endif; ?>

        <label for="cpf">CPF do Administrador:</label>
        <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" required maxlength="14">
        
        <br><br>

        <label for="password">Senha:</label>
        <input type="password" id="password" name="password" placeholder="Sua senha" required>
        
        <button type="submit">Acessar Painel</button>

        <a href="index.php" class="back-link">← Voltar para o Site</a>
    </form>

    <script>
        // Máscara de CPF para facilitar a digitação
        var cpfInput = document.getElementById("cpf");
        cpfInput.addEventListener("input", function() {
            var v = this.value.replace(/\D/g, "");
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
            this.value = v;
        });
    </script>
</body>
</html>