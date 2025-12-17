<?php
session_start();
// Importante: Define o fuso horário para garantir que a comparação de validade funcione
date_default_timezone_set('America/Sao_Paulo');

$dbPath = "./DB/db_pontos.db";
$db = new SQLite3($dbPath);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Limpa o CPF
    $cpf = preg_replace('/\D/', '', $_POST['cpf']);
    $tokenDigitado = trim($_POST['token']);
    $newPass = $_POST['new_password'];
    $confirmPass = $_POST['confirm_password'];

    // 1. Valida se as senhas coincidem
    if ($newPass !== $confirmPass) {
        $_SESSION['flash_msg'] = "As senhas não coincidem."; 
        $_SESSION['flash_type'] = "error";
    } else {
        // 2. Busca usuário, token e a data de expiração
        $stmt = $db->prepare("SELECT token_recuperacao, token_expiracao FROM usuarios WHERE cpf = :cpf");
        $stmt->bindValue(':cpf', $cpf);
        $result = $stmt->execute();
        $user = $result->fetchArray(SQLITE3_ASSOC);

        if ($user) {
            $agora = date("Y-m-d H:i:s"); // Horário atual

            // 3. LÓGICA DE VALIDAÇÃO (Token correto E Não expirado)
            if (
                !empty($user['token_recuperacao']) &&           // Tem token?
                $user['token_recuperacao'] == $tokenDigitado && // É igual ao digitado?
                $user['token_expiracao'] > $agora               // A validade é MAIOR que agora?
            ) {
                
                // --- SUCESSO ---
                
                // Criptografa nova senha
                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                
                // Atualiza senha, limpa token, limpa expiração e volta status para ativo
                $update = $db->prepare("UPDATE usuarios SET senha = :senha, token_recuperacao = NULL, token_expiracao = NULL, status = 'ativo' WHERE cpf = :cpf");
                $update->bindValue(':senha', $hash);
                $update->bindValue(':cpf', $cpf);
                
                if ($update->execute()) {
                    $_SESSION['msg_index'] = "Senha redefinida com sucesso! Faça login.";
                    $_SESSION['type_index'] = "success";
                    header("Location: index.php");
                    exit;
                } else {
                    $_SESSION['flash_msg'] = "Erro ao salvar no banco."; $_SESSION['flash_type'] = "error";
                }

            } else {
                // --- ERRO ---
                
                // Verifica especificamente se expirou para dar uma mensagem melhor
                if (!empty($user['token_expiracao']) && $user['token_expiracao'] <= $agora) {
                    $_SESSION['flash_msg'] = "Este token expirou (validade de 30min). Solicite um novo.";
                } else {
                    $_SESSION['flash_msg'] = "Token inválido ou incorreto.";
                }
                $_SESSION['flash_type'] = "error";
            }
        } else {
            $_SESSION['flash_msg'] = "CPF não encontrado."; $_SESSION['flash_type'] = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Definir Nova Senha</title>
    <!-- Usando o estilo do cadastro para manter o padrão -->
    <link rel="stylesheet" type="text/css" href="./styles/stylecadastro.css">
    <link rel="shortcut icon" href="./styles/clock.ico" type="image/x-icon">
</head>
<body>
    <div class="container">
        
        <div class="form-image"></div>

        <div class="form">
            <div class="form-header">
                <div class="title"><h1>Redefinir Senha</h1></div>
            </div>

            <!-- ÁREA DE MENSAGENS -->
            <div class="infos">
                <?php if (isset($_SESSION['flash_msg'])): ?>
                    <div style="padding: 10px; margin-bottom: 20px; border-radius: 5px; color: #fff; font-weight: bold; background-color: <?php echo ($_SESSION['flash_type'] == 'success') ? '#27ae60' : '#e74c3c'; ?>;">
                        <?php echo $_SESSION['flash_msg']; unset($_SESSION['flash_msg']); unset($_SESSION['flash_type']); ?>
                    </div>
                <?php endif; ?>
            </div>

            <form action="" method="post" id="trocaForm">
                <div class="input-group">
                    
                    <div class="input-box">
                        <label for="number">CPF</label>
                        <input id="number" type="text" name="cpf" placeholder="CPF" required maxlength="14">
                    </div>

                    <!-- CAMPO DO TOKEN -->
                    <div class="input-box">
                        <label for="token" style="color:#1b9aaa; font-weight:bold;">Token Recebido (6 dígitos)</label>
                        <input id="token" type="text" name="token" placeholder="Ex: 123456" required maxlength="6" style="border-bottom: 2px solid #1b9aaa;">
                    </div>

                    <div class="input-box">
                        <label for="password">Nova Senha</label>
                        <input id="password" type="password" name="new_password" placeholder="Nova senha" required>
                    </div>

                    <div class="input-box">
                        <label for="Confirmpassword">Confirme Nova Senha</label>
                        <input id="Confirmpassword" type="password" name="confirm_password" placeholder="Repita a senha" required>
                    </div>

                    <div class="login-button">
                        <button type="button" id="submitButton">Salvar Senha</button>
                    </div>
                    <div class="login-link"><a href="index.php">Cancelar</a></div>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // MÁSCARA CPF
        document.getElementById("number").addEventListener("input", function() {
            var v = this.value.replace(/\D/g, "");
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
            this.value = v;
        });

        // ENVIO COM LIMPEZA DE CPF
        document.getElementById("submitButton").addEventListener("click", function(event) {
            event.preventDefault();
            
            var cpfField = document.getElementById("number");
            var tokenField = document.getElementById("token");
            var passField = document.getElementById("password");
            var confirmField = document.getElementById("Confirmpassword");

            if (cpfField.value === "" || tokenField.value === "" || passField.value === "" || confirmField.value === "") {
                alert("Por favor, preencha todos os campos.");
                return;
            }

            // Limpa formatação do CPF antes de enviar
            var cpfOriginal = cpfField.value;
            cpfField.value = cpfOriginal.replace(/\D/g, ""); 
            
            document.getElementById("trocaForm").submit();
            
            // (Opcional) Restaura visualmente caso o submit falhe ou demore
            setTimeout(function(){ cpfField.value = cpfOriginal; }, 100);
        });
    </script>
</body>
</html>