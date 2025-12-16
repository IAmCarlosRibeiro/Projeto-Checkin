<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- CSS Atualizado -->
    <link rel="stylesheet" type="text/css" href="./styles/stylecadastro.css">
    <link rel="shortcut icon" href="./styles/clock.ico" type="image/x-icon">
    <title>Painel de Cadastro</title>
</head>
<body>
    <div class="container">
        
        <!-- Lado Esquerdo: Imagem -->
        <div class="form-image"></div>

        <!-- Lado Direito: Formulário -->
        <div class="form">
            <form action="backcadastro.php" method="post" id="cadastroForm">
                
                <div class="form-header">
                    <div class="title">
                        <h1>Cadastre-se</h1>
                    </div>
                </div>

                <div class="input-group">
                    <div class="input-box">
                        <label for="name">Nome Completo</label>
                        <input id="name" type="text" name="name" placeholder="Digite seu nome completo" required>
                    </div>

                    <div class="input-box">
                        <label for="number">CPF</label>
                        <!-- Aumentei o maxlength para 14 para caber a formatação -->
                        <input id="number" type="text" name="number" placeholder="000.000.000-00" required maxlength="14">
                    </div>

                    <div class="input-box">
                        <label for="password">Senha</label>
                        <input id="password" type="password" name="password" placeholder="Crie uma senha" required>
                        <div id="capsLockAlert" style="display: none; color: #d9534f; font-size: 0.8rem; margin-top: 5px; font-weight: bold;">⚠️ Caps Lock está ligado!</div>
                    </div>

                    <div class="input-box">
                        <label for="Confirmpassword">Confirme sua Senha</label>
                        <input id="Confirmpassword" type="password" name="Confirmpassword" placeholder="Repita a senha" required>
                    </div>

                    <!-- Botão de Ação -->
                    <div class="login-button">
                        <button type="button" id="submitButton">Cadastrar</button>
                    </div>

                    <!-- Link para Voltar ao Login -->
                    <div class="login-link">
                        <span>Já tem uma conta? </span>
                        <a href="index.php">Entrar</a>
                    </div>

                    <div class="infos">
                        <?php
                        if(isset($_GET['message']))
                        {
                            $message = $_GET['message'];
                            if ($message == 1){
                                echo "<script>alert('Cadastro realizado com sucesso!'); window.location.href = 'index.php';</script>";
                            }
                            if ($message == 2){
                                echo "<script>alert('Erro ao cadastrar. Tente novamente.');</script>";
                            }
                            if ($message == 3){
                                echo "<script>alert('As senhas não coincidem. Tente novamente.');</script>";
                            }
                            if ($message == 4){
                                echo "<script>alert('Erro ao cadastrar. Usuário já cadastrado.');</script>";
                            }
                        }
                        ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Função matemática para validar CPF
        function validaCPF(cpf) {
            cpf = cpf.replace(/[^\d]+/g, ''); // Remove formatação para validar
            
            if (cpf == '') return false;
            
            // Elimina CPFs invalidos conhecidos
            if (cpf.length != 11 || 
                cpf == "00000000000" || cpf == "11111111111" || 
                cpf == "22222222222" || cpf == "33333333333" || 
                cpf == "44444444444" || cpf == "55555555555" || 
                cpf == "66666666666" || cpf == "77777777777" || 
                cpf == "88888888888" || cpf == "99999999999")
                    return false;
            
            // Valida 1o digito
            var add = 0;
            for (var i = 0; i < 9; i++) 
                add += parseInt(cpf.charAt(i)) * (10 - i);
            var rev = 11 - (add % 11);
            if (rev == 10 || rev == 11) rev = 0;
            if (rev != parseInt(cpf.charAt(9))) return false;
            
            // Valida 2o digito
            add = 0;
            for (var i = 0; i < 10; i++) 
                add += parseInt(cpf.charAt(i)) * (11 - i);
            rev = 11 - (add % 11);
            if (rev == 10 || rev == 11) rev = 0;
            if (rev != parseInt(cpf.charAt(10))) return false;
                
            return true;
        }

        // --- LÓGICA DE ENVIO DO FORMULÁRIO ---
        document.getElementById("submitButton").addEventListener("click", function(event) {
            event.preventDefault(); // Impede o envio imediato

            var name = document.getElementById("name").value.trim();
            var cpfField = document.getElementById("number"); // O elemento input
            var cpfValue = cpfField.value; // O valor digitado (com pontos)
            var pass = document.getElementById("password").value;
            var confirmPass = document.getElementById("Confirmpassword").value;

            // 1. Verifica se campos estão vazios
            if (name === "" || cpfValue === "" || pass === "" || confirmPass === "") {
                alert("Por favor, preencha todos os campos.");
                return;
            }

            // 2. Valida o CPF (A função validaCPF já sabe lidar com pontos e traços)
            if (!validaCPF(cpfValue)) {
                alert("CPF Inválido! Por favor verifique o número digitado.");
                cpfField.focus();
                return;
            }

            // 3. Verifica se senhas batem
            if (pass !== confirmPass) {
                alert("As senhas não coincidem.");
                return;
            }

            // --- IMPORTANTE: LIMPEZA DO CPF ANTES DE ENVIAR ---
            // Remove tudo que não é número do valor do input
            cpfField.value = cpfValue.replace(/\D/g, "");

            // Agora sim, envia o formulário com apenas números no CPF
            document.getElementById("cadastroForm").submit();
        });

        // --- MÁSCARA DO CPF (VISUAL) ---
        var numberInput = document.getElementById("number");
        numberInput.addEventListener("input", function() {
            var v = this.value;
            
            // Remove tudo o que não é dígito
            v = v.replace(/\D/g, "");

            // Coloca um ponto entre o terceiro e o quarto dígitos
            v = v.replace(/(\d{3})(\d)/, "$1.$2");

            // Coloca um ponto entre o terceiro e o quarto dígitos
            // de novo (para o segundo bloco de números)
            v = v.replace(/(\d{3})(\d)/, "$1.$2");

            // Coloca um hífen entre o terceiro e o quarto dígitos
            v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");

            this.value = v;
        });

        // Capitalizar Nome (Primeira letra maiúscula)
        var nameInput = document.getElementById("name");
        nameInput.addEventListener("input", function() {
            var words = this.value.toLowerCase().split(" ");
            for (var i = 0; i < words.length; i++) {
                if (words[i].length > 0) {
                    words[i] = words[i].charAt(0).toUpperCase() + words[i].slice(1);
                }
            }
            this.value = words.join(" ");
        });

        // Detector de Caps Lock
        var passwordInput = document.getElementById("password");
        var capsLockAlert = document.getElementById("capsLockAlert");

        passwordInput.addEventListener("keyup", function(event) {
            if (event.getModifierState("CapsLock")) {
                capsLockAlert.style.display = "block";
            } else {
                capsLockAlert.style.display = "none";
            }
        });
    </script>
</body>
</html>