<?php
session_start();
$dbPath = "./DB/db_pontos.db";
$db = new SQLite3($dbPath);

$solicitacao_sucesso = false;
$dados_usuario = [];
$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cpf = preg_replace('/\D/', '', $_POST['cpf']);

    $stmt = $db->prepare("SELECT nome, cpf FROM usuarios WHERE cpf = :cpf");
    $stmt->bindValue(':cpf', $cpf);
    $result = $stmt->execute();
    
    if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        // Usuário encontrado: Muda status para 'solicitando'
        $update = $db->prepare("UPDATE usuarios SET status = 'solicitando' WHERE cpf = :cpf");
        $update->bindValue(':cpf', $cpf);
        $update->execute();

        $solicitacao_sucesso = true;
        $dados_usuario = $row;
    } else {
        $erro = "CPF não encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Recuperação</title>
    <link rel="stylesheet" type="text/css" href="./styles/stylepontos.css">
    <link rel="stylesheet" type="text/css" href="./styles/stylerecupera.css">
    <link rel="shortcut icon" href="./styles/clock.ico" type="image/x-icon">
</head>
<body>
    <div class="container">
        <div class="form-image"></div>
        <div class="form">
            <div class="form-header center-mode">
                <div class="title"><h1>Recuperar Senha</h1></div>
            </div>

            <?php if (!$solicitacao_sucesso): ?>
                <!-- TELA 1: DIGITAR CPF -->
                <form method="post" action="">
                    <p class="instruction-text">Digite seu CPF para solicitar um reset ao administrador.</p>
                    <div class="input-box">
                        <div class="form__group field">
                            <input type="text" class="form__field" placeholder="CPF" id="cpf" name="cpf" required maxlength="14">
                            <label for="cpf" class="form__label">CPF</label>
                        </div>
                    </div>
                    <?php if ($erro): ?>
                        <div class="error-msg"><?php echo $erro; ?></div>
                    <?php endif; ?>
                    <div class="action-buttons" style="margin-top: 30px;">
                        <button type="submit" id="submitButton">Solicitar Reset</button>
                    </div>
                </form>
                <a href="index.php" class="back-link">← Voltar para o Login</a>

            <?php else: ?>
                <!-- TELA 2: ENVIAR ZAP E IR PARA TOKEN -->
                <div class="result-card">
                    <h3 class="result-name">Solicitação Enviada!</h3>
                    <p class="result-text">
                        O administrador foi notificado no sistema.<br>
                        Para agilizar, envie a mensagem abaixo no WhatsApp dele e peça o seu <b>Código de Token</b>.
                    </p>

                    <?php
                        // SEU NÚMERO AQUI
                        $numero_admin = "5511999999999"; 
                        $msg = "Olá, sou " . $dados_usuario['nome'] . ". Solicitei o reset de senha no sistema. Pode gerar meu Token?";
                        $link = "https://wa.me/" . $numero_admin . "?text=" . urlencode($msg);
                    ?>

                    <a href="<?php echo $link; ?>" target="_blank" class="btn-whatsapp">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="white" style="margin-right:10px"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.585 1.909.896 3.056.896 3.179 0 5.767-2.587 5.767-5.766.001-3.187-2.846-6.083-6.027-6.083zm0 13.891c-3.831 0-6.965-3.104-6.965-7s3.134-7 6.965-7c3.833 0 6.966 3.104 6.966 7 0 3.898-3.134 7.001-6.966 7.001zm16.887-19.064c-2.604-2.603-6.069-4.037-9.754-4.037-7.601 0-13.784 6.182-13.784 13.783 0 2.43.633 4.8 1.836 6.885l-1.952 7.128 7.292-1.913c2.012 1.097 4.275 1.675 6.608 1.675 7.603 0 13.787-6.182 13.787-13.783 0-3.682-1.433-7.146-4.033-9.738z"/></svg>
                        1. Avisar no WhatsApp
                    </a>

                    <br><br>

                    <a href="trocarsenha.php" style="display:block; background:#1b9aaa; color:white; padding:12px; border-radius:50px; text-decoration:none; font-weight:bold;">
                        2. Já tenho o Token
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script>
        var cpfInput = document.getElementById("cpf");
        if(cpfInput){
            cpfInput.addEventListener("input", function() {
                var v = this.value.replace(/\D/g, "");
                v = v.replace(/(\d{3})(\d)/, "$1.$2");
                v = v.replace(/(\d{3})(\d)/, "$1.$2");
                v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
                this.value = v;
            });
        }
    </script>
</body>
</html>