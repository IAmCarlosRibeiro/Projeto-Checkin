<?php
    // Verifica se a mensagem está presente na URL
    if (isset($_GET['message'])) {
        $message = $_GET['message'];
        if (in_array($message, array(1, 2, 3, 4))) {
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
    
    <!-- Seus arquivos de estilo -->
    <link rel="stylesheet" type="text/css" href="./styles/stylepontos.css">
    <!-- Adicionei de volta caso você tenha estilos lá -->
    <link rel="stylesheet" type="text/css" href="./styles/style/style.css"> 
    
    <link rel="shortcut icon" href="./styles/clock.ico" type="image/x-icon">
    <title>Controle de Ponto</title>
</head>
<body>
    <div class="container">
        
        <!-- Lado Esquerdo: Imagem -->
        <div class="form-image"></div>

        <!-- Lado Direito: Formulário -->
        <div class="form">
            
            <!-- Botões do Topo (Agrupados) -->
            <div class="top-buttons">
                <a href="relatorios.php" class="btn-top">Relatórios</a>
                <a href="adm.php" class="btn-top">Dashboard</a>
            </div>

            <form action="backpontos.php" method="post" id="batepontoForm">
                <div class="form-header">
                    <div class="title">
                        <h1>Controle de Ponto</h1>
                    </div>
                </div>

                <!-- ESTRUTURA DOS INPUTS -->
                <div class="input-box">
                    <div class="form__group field">
                        <!-- ADICIONADO MAXLENGTH 14 PARA CABER A FORMATAÇÃO -->
                        <input type="text" class="form__field" placeholder="CPF" id="cpf" name="cpf" required maxlength="14">
                        <label for="cpf" class="form__label">CPF</label>
                    </div>
                    
                    <div class="form__group field">
                        <input type="password" class="form__field" placeholder="Senha" id="password" name="password" required>
                        <label for="password" class="form__label">Senha</label>
                    </div>

                    <!-- Link Esqueci a Senha -->
                    <div class="forgot-pass">
                        <a href="#">Esqueci a senha</a>
                    </div>
                </div>

                <div class="action-buttons">
                    <!-- Botão Principal -->
                    <button type="button" id="submitButton">Entrar</button>
                    
                    <!-- Link Cadastro -->
                    <div class="register-link">
                        <span>Não tem uma conta? </span>
                        <a href="cadastro.php">Cadastre-se</a>
                    </div>
                </div>

                <!-- Área de Mensagens -->
                <div class="infos">
                    <?php
                    date_default_timezone_set('America/Sao_Paulo');
                    if (isset($_GET['message'])) {
                        $message = $_GET['message'];
                        if ($message == 1) echo '<div class="hora"><h3>Entrada: </h3><span>' . date("d/m/y H:i:s") . '</span></div>';
                        if ($message == 2) echo '<div class="hora"><h3>Saída: </h3><span>' . date("d/m/y H:i:s") . '</span></div>';
                        if ($message == 3) echo "<script>alert('Houve um erro no banco de dados');</script>";
                        if ($message == 4) echo "<script>alert('Usuário não encontrado, CPF ou Senha incorretos');</script>";
                    }
                    ?>
                </div>
            </form>
        </div>
    </div>

    <script>
        // MÁSCARA DO CPF (VISUAL ENQUANTO DIGITA)
        var cpfInput = document.getElementById("cpf");
        cpfInput.addEventListener("input", function() {
            var v = this.value;
            
            // Remove tudo o que não é dígito
            v = v.replace(/\D/g, "");

            // Coloca um ponto entre o terceiro e o quarto dígitos
            v = v.replace(/(\d{3})(\d)/, "$1.$2");

            // Coloca um ponto entre o terceiro e o quarto dígitos de novo (para o segundo bloco)
            v = v.replace(/(\d{3})(\d)/, "$1.$2");

            // Coloca um hífen entre o terceiro e o quarto dígitos
            v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");

            this.value = v;
        });

        // ENVIO DO FORMULÁRIO (LIMPEZA DO CPF)
        document.getElementById("submitButton").addEventListener("click", function (event) {
            // Impede o envio do formulário imediato
            event.preventDefault();

            // Exibe o popup de confirmação
            if (confirm("Deseja realmente bater o ponto?")) {
                
                // --- LIMPEZA DO CPF ANTES DE ENVIAR ---
                var cpfField = document.getElementById("cpf");
                // Remove pontos e traços, deixando apenas números
                cpfField.value = cpfField.value.replace(/\D/g, "");

                // Envia o formulário
                document.getElementById("batepontoForm").submit();
            } else {
                return; // Caso contrário, não faz nada
            }
        });
    </script>
</body>
</html>