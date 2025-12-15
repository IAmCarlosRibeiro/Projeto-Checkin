<?php
    // Verifica se a mensagem est� presente na URL e � um valor v�lido
    if (isset($_GET['message'])) {
        $message = $_GET['message'];

        // Verifica se a mensagem � um dos valores permitidos (1, 2, 3 ou 4)
        if (in_array($message, array(1, 2, 3, 4))) {
            // Define a URL de redirecionamento ap�s 5 segundos
            echo "<script>
                setTimeout(function() {
                    window.location.href = 'index.php';
                }, 5000);
            </script>";
        }
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="./styles/stylepontos.css">
    <link rel="stylesheet" type="text/css" href="./styles/style/style.css">
    <link rel="shortcut icon" href="./styles/raposa.ico" type="image/x-icon">
    <title>Controle de Ponto</title>
</head>
<body>
    <div class="container">
        <div class="form-image">
            
        </div>
        <div class="form">
            <form action="backpontos.php" method="post" id="batepontoForm">
                <div class="form-header">
                    <div class="title">
                        <h1>Controle de Ponto </h1>
                    </div>
                    
                </div>
                <div class="input-box">
                    <div class="form__group field">
                        <input type="text" class="form__field" placeholder="Name" id="cpf" name="cpf" required>
                        <label for="cpf" class="form__label">CPF</label>
                    </div>
                    <div class="form__group field">
                        <input type="password" class="form__field" placeholder="Name" id="password" name="password" required>
                        <label for="name" for="password" class="form__label">Senha</label>
                    </div>
                    <div class="login-button">
                        <button type="button" id="submitButton"><a>Entrar</a></button>
                        <button><a href="cadastro.php">Cadastre-se</a></button>
                        <button><a href="relatorios.php">Relatorios</a></button>
                    </div>
                    <br>
                    <br>
                    <br>
                    <div class="infos">
                        <?php
                        date_default_timezone_set('America/Sao_Paulo');
                        if (isset($_GET['message'])) {
                            $message = $_GET['message'];
                            if ($message == 1) {
                                echo '<div class="hora"><h3>Entrada: </h3><span>' . date("d/m/y H:i:s") . '</span></div>';
                            }
                            if ($message == 2) {
                                echo '<div class="hora"><h3>Saída: </h3><span>' . date("d/m/y H:i:s") . '</span></div>';
                            }
                            if ($message == 3) {
                                echo "<script>alert('Houve um erro no banco de dados');</script>";
                            }
                            if ($message == 4) {
                                echo "<script>alert('Usuário não encontrado, CPF ou Senha incorretos');</script>";
                            }
                        }
                        ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Reconhece o botão como input do form
        document.getElementById("submitButton").addEventListener("click", function (event) {
            // Impede o envio do formulário
            event.preventDefault();

            // Exibe o popup de confirmação
            if (confirm("Deseja realmente bater o ponto?")) {
                // Envia o formulário se o usuário confirmar
                document.getElementById("batepontoForm").submit();
            } else {
                return; // Caso contrário, não faz nada
            }
        });

        // Adicionar evento de input ao campo de n�mero
        var numberInput = document.getElementById("cpf");
        numberInput.addEventListener("input", function() {
            // Remove todos os caracteres n�o num�ricos
            this.value = this.value.replace(/\D/g, "");
        });
    </script>
</body>
</html>