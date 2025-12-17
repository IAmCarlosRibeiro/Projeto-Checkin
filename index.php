<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="./styles/stylepontos.css">
    <link rel="stylesheet" type="text/css" href="./styles/style/style.css"> 
    <link rel="shortcut icon" href="./styles/clock.ico" type="image/x-icon">
    <title>Controle de Ponto</title>
</head>
<body>
    <div class="container">
        <div class="form-image"></div>
        <div class="form">
            <div class="top-buttons">
                <a href="relatorios.php" class="btn-top">Relatórios</a>
                <a href="adm.php" class="btn-top">Dashboard</a>
            </div>

            <form action="backpontos.php" method="post" id="batepontoForm">
                <div class="form-header">
                    <div class="title"><h1>Controle de Ponto</h1></div>
                </div>

                <div class="input-box">
                    <div class="form__group field">
                        <input type="text" class="form__field" placeholder="CPF" id="cpf" name="cpf" required maxlength="14">
                        <label for="cpf" class="form__label">CPF</label>
                    </div>
                    <div class="form__group field">
                        <input type="password" class="form__field" placeholder="Senha" id="password" name="password" required>
                        <label for="password" class="form__label">Senha</label>
                    </div>
                    <div class="forgot-pass">
                        <a href="recupera.php">Esqueci a senha</a>
                    </div>
                </div>

                <div class="action-buttons">
                    <button type="button" id="submitButton">Entrar</button>
                    <div class="register-link">
                        <span>Não tem uma conta? </span>
                        <a href="cadastro.php">Cadastre-se</a>
                    </div>
                </div>

                <!-- ÁREA DE MENSAGENS -->
                <div class="infos">
                    <?php if (isset($_SESSION['msg_index'])): ?>
                        <div style="padding: 10px; margin-top: 15px; border-radius: 5px; color: #fff; font-weight: bold; background-color: <?php echo ($_SESSION['type_index'] == 'success') ? '#27ae60' : '#e74c3c'; ?>;">
                            <?php 
                                echo $_SESSION['msg_index']; 
                                unset($_SESSION['msg_index']); // Limpa após exibir
                                unset($_SESSION['type_index']);
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <script>
        var cpfInput = document.getElementById("cpf");
        cpfInput.addEventListener("input", function() {
            var v = this.value.replace(/\D/g, "");
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
            this.value = v;
        });

        document.getElementById("submitButton").addEventListener("click", function (event) {
            event.preventDefault();
            if (confirm("Deseja realmente bater o ponto?")) {
                var cpfField = document.getElementById("cpf");
                cpfField.value = cpfField.value.replace(/\D/g, ""); 
                document.getElementById("batepontoForm").submit();
            }
        });
    </script>
</body>
</html>