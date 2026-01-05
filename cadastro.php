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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="./styles/stylecadastro.css">
    <link rel="shortcut icon" href="./styles/clock.ico" type="image/x-icon">
    <title>Painel de Cadastro</title>
</head>
<body>
    <div class="container">
        <div class="form-image"></div>
        <div class="form">
            <form action="backcadastro.php" method="post" id="cadastroForm">
                <div class="form-header">
                    <div class="title"><h1>Cadastre-se</h1></div>
                </div>

                <div class="input-group">
                    <div class="input-box">
                        <label for="name">Nome Completo</label>
                        <input id="name" type="text" name="name" placeholder="Nome completo" required>
                    </div>

                    <!-- NOVO CAMPO EMAIL -->
                    <div class="input-box">
                        <label for="email">E-mail</label>
                        <input id="email" type="email" name="email" placeholder="seu@email.com" required>
                    </div>

                    <div class="input-box">
                        <label for="number">CPF</label>
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

                    <div class="login-button">
                        <button type="button" id="submitButton">Cadastrar</button>
                    </div>

                    <div class="login-link">
                        <span>Já tem uma conta? </span>
                        <a href="index.php">Entrar</a>
                    </div>

                    <div class="infos">
                        <?php if (isset($_SESSION['msg_cadastro'])): ?>
                            <div style="padding: 10px; margin-top: 15px; border-radius: 5px; color: #fff; font-weight: bold; background-color: <?php echo ($_SESSION['type_cadastro'] == 'success') ? '#27ae60' : '#e74c3c'; ?>;">
                                <?php 
                                    echo $_SESSION['msg_cadastro']; 
                                    unset($_SESSION['msg_cadastro']);
                                    unset($_SESSION['type_cadastro']);
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Validação de CPF
        function validaCPF(cpf) {
            cpf = cpf.replace(/[^\d]+/g, '');
            if (cpf == '') return false;
            if (cpf.length != 11 || /^(\d)\1{10}$/.test(cpf)) return false;
            var add = 0; for (var i = 0; i < 9; i++) add += parseInt(cpf.charAt(i)) * (10 - i);
            var rev = 11 - (add % 11); if (rev == 10 || rev == 11) rev = 0; if (rev != parseInt(cpf.charAt(9))) return false;
            add = 0; for (var i = 0; i < 10; i++) add += parseInt(cpf.charAt(i)) * (11 - i);
            rev = 11 - (add % 11); if (rev == 10 || rev == 11) rev = 0; if (rev != parseInt(cpf.charAt(10))) return false;
            return true;
        }

        // Validação de Nome Completo
        function validaNome(nome) {
            return nome.trim().indexOf(' ') !== -1;
        }

        document.getElementById("submitButton").addEventListener("click", function(event) {
            event.preventDefault();
            var nameField = document.getElementById("name");
            var cpfField = document.getElementById("number");
            var emailField = document.getElementById("email");
            var passField = document.getElementById("password");
            var confirmField = document.getElementById("Confirmpassword");

            if (nameField.value === "" || emailField.value === "" || cpfField.value === "" || passField.value === "" || confirmField.value === "") {
                alert("Por favor, preencha todos os campos."); return;
            }

            if (!validaNome(nameField.value)) {
                alert("Por favor, digite seu Nome e Sobrenome comletos.");
                nameField.focus();
                return;
            }

            if (!validaCPF(cpfField.value)) {
                alert("CPF Inválido!"); cpfField.focus(); return;
            }

            cpfField.value = cpfField.value.replace(/\D/g, "");
            document.getElementById("cadastroForm").submit();
        });

        // Máscaras e Formatações
        var numberInput = document.getElementById("number");
        numberInput.addEventListener("input", function() {
            var v = this.value.replace(/\D/g, "");
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
            this.value = v;
        });

        var nameInput = document.getElementById("name");
        nameInput.addEventListener("input", function() {
            var words = this.value.toLowerCase().split(" ");
            for (var i = 0; i < words.length; i++) if (words[i].length > 0) words[i] = words[i].charAt(0).toUpperCase() + words[i].slice(1);
            this.value = words.join(" ");
        });
        
        var passwordInput = document.getElementById("password");
        var capsLockAlert = document.getElementById("capsLockAlert");
        passwordInput.addEventListener("keyup", function(event) {
            if (event.getModifierState("CapsLock")) capsLockAlert.style.display = "block"; else capsLockAlert.style.display = "none";
        });
    </script>
</body>
</html>