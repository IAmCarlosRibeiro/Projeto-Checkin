<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x=ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="./styles/stylecadastro.css">
    <link rel="shortcut icon" href="./styles/raposa.ico" type="image/x-icon">
    <title>Painel de Cadastro</title>
</head>
<body>
    <div class="container">
        <div class="form-image">
            
        </div>
        <div class="form">
            <form action="backcadastro.php" method="post" id="cadastroForm">
                <div class="form-header">
                    <div class="title">
                        <h1>Cadastre-se</h1>
                    </div>
                </div>

                <div class="input-group">
                    <div class="input-box">
                        <label for="name">Nome</label>
                        <input id="name" type="name" name="name" placeholder="Nome completo" required>
                    </div>

                    <div class="input-box">
                        <label for="number">CPF</label>
                        <input id="number" type="text" name="number" placeholder="CPF" onblur="ValidaCPF()" required>
                    </div>

                    <div class="input-box">
                        <label for="password">Senha</label>
                        <input id="password" type="password" name="password" placeholder="Digite sua Senha" required>
                        <div id="capsLockAlert" style="display: none;">Caps Lock est� ligado!</div>
                    </div>

                    <div class="input-box">
                        <label for="Confirmpassword">Confirme sua Senha</label>
                        <input id="Confirmpassword" type="password" name="Confirmpassword" placeholder="Digite sua Senha" required>
                    </div>
                    <br>
                    <br>
                    <br>
                    <div class="login-button">
                        <button type="button" id="submitButton"><a>Cadastrar</a></button>
                        <button><a href="index.php">Entrar</a></button>
                    </div>
                    <div class="infos">
                        <?php
                        if(isset($_GET['message']))
                        {
                            $message = $_GET['message'];
                            if ($message == 1){
                                echo "<script>alert('Cadastro realizado com sucesso!');</script>";
                            }
                            if ($message == 2){
                                echo "<script>alert('Erro ao cadastrar. Tente novamente.');</script>";
                            }
                            if ($message == 3){
                                echo "<script>alert('As senhas não coincidem. Tente novamente.');</script>";
                            }
                            if ($message == 4){
                                echo "<script>alert('Erro ao cadastrar. Usu�rio j� cadastrado.');</script>";
                            }
                        }
                        ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Adicionar evento de clique ao botão
        document.getElementById("submitButton").addEventListener("click", function() {
            // Enviar o formulário quando o botão for clicado
            document.getElementById("cadastroForm").submit();
        });

        // Adicionar evento de input ao campo de nome
        var nameInput = document.getElementById("name");
        nameInput.addEventListener("input", function() {
            var words = this.value.toLowerCase().split(" ");
            for (var i = 0; i < words.length; i++) {
                words[i] = words[i].charAt(0).toUpperCase() + words[i].slice(1);
            }
            this.value = words.join(" ");
        });

        // Adicionar evento de input ao campo de n�mero
        var numberInput = document.getElementById("number");
        numberInput.addEventListener("input", function() {
            // Remove todos os caracteres n�o num�ricos
            this.value = this.value.replace(/\D/g, "");
        });
    </script>
</body>
</html>