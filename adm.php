<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$dbPath = "./DB/db_pontos.db";
$db = new SQLite3($dbPath);
date_default_timezone_set('America/Sao_Paulo');

// --- LÓGICA DE MÉTRICAS ---
$countUsers = $db->querySingle("SELECT COUNT(*) FROM usuarios");
$countWorking = $db->querySingle("SELECT COUNT(*) FROM temp");
$today = date('Y-m-d');
$countPontosHoje = $db->querySingle("SELECT COUNT(*) FROM pontos WHERE date(entrada) = '$today'");

// --- PROCESSAMENTO DE AÇÕES (POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $redirect = true;

    // GERAR TOKEN
    if (isset($_POST['gerar_token'])) {
        $cpf = $_POST['reset_id'];
        $token = rand(100000, 999999);
        
        // Define expiração para 30 minutos
        $stmt = $db->prepare("UPDATE usuarios SET token_recuperacao = :token, token_expiracao = datetime('now', '+30 minutes') WHERE cpf = :cpf");
        $stmt->bindValue(':token', $token);
        $stmt->bindValue(':cpf', $cpf);
        
        if ($stmt->execute()) {
            $_SESSION['adm_msg'] = "Token $token gerado! Ele agora aparece na tabela abaixo.";
            $_SESSION['adm_type'] = "success";
        }
    }
    
    // Outras ações (Excluir, Limpar Temp, SQL - Simplificadas aqui)
    elseif (isset($_POST['excluir_usuario'])) {
        $cpf = $_POST['delete_id'];
        $db->exec("DELETE FROM usuarios WHERE cpf = '$cpf'");
        $_SESSION['adm_msg'] = "Usuário excluído."; $_SESSION['adm_type'] = "success";
    }
    elseif (isset($_POST['excluir_registro_temp'])) {
        $id_temp = $_POST['delete_id_temp'];
        $entrada = $db->querySingle("SELECT entrada FROM temp WHERE cpf = '$id_temp'");
        if ($entrada) {
            $saida = date("Y-m-d H:i:s");
            $db->exec("INSERT INTO lixeira (cpf, entrada, saida) VALUES ('$id_temp', '$entrada', '$saida')");
            $db->exec("DELETE FROM temp WHERE cpf = '$id_temp'");
            $_SESSION['adm_msg'] = "Movido para a lixeira."; $_SESSION['adm_type'] = "success";
        }
    }
    elseif (isset($_POST['query']) && !empty($_POST['query'])) {
        if (@$db->exec($_POST['query'])) {
            $_SESSION['adm_msg'] = "SQL executado."; $_SESSION['adm_type'] = "success";
        } else {
            $_SESSION['adm_msg'] = "Erro SQL: " . $db->lastErrorMsg(); $_SESSION['adm_type'] = "error";
        }
    }
    else { $redirect = false; }

    if ($redirect) {
        header("Location: adm.php");
        exit;
    }
}

// Listas
$registrosUsuarios = [];
$res = $db->query("SELECT * FROM usuarios ORDER BY nome ASC");
while ($row = $res->fetchArray(SQLITE3_ASSOC)) $registrosUsuarios[] = $row;

$registrosTemp = [];
$res = $db->query("SELECT * FROM temp");
while ($row = $res->fetchArray(SQLITE3_ASSOC)) $registrosTemp[] = $row;
$db->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="./styles/styleadm.css">
    <link rel="shortcut icon" href="./styles/clock.ico" type="image/x-icon">
    <style>
        /* CSS INTERNO ADICIONAL */
        summary { list-style: none; display: flex; justify-content: space-between; align-items: center; cursor: pointer; outline: none; }
        summary::-webkit-details-marker { display: none; }
        .card h2 { border: none; margin-bottom: 0; padding-bottom: 0; width: 100%; }
        details[open] .card-content { padding-top: 20px; border-top: 1px solid #f0f0f0; margin-top: 15px; }
        .toggle-icon::after { content: '▼'; font-size: 0.8rem; color: #999; }
        details[open] .toggle-icon::after { content: '▲'; }
        
        .header-actions { display: flex; gap: 10px; align-items: center; }
        .btn-trash { text-decoration: none; color: #555; background: #fff; border: 1px solid #ccc; padding: 8px 16px; border-radius: 6px; font-size: 0.9rem; }
        
        /* Estilo do Token na Tabela */
        .token-display { font-family: monospace; font-weight: bold; color: #1b9aaa; background: #e0f2f4; padding: 4px 8px; border-radius: 4px; display: inline-block; }
        .btn-copy { border: none; background: none; cursor: pointer; color: #666; font-size: 1rem; margin-left: 5px; }
        .btn-copy:hover { color: #000; }
        
        /* Destaque linha solicitando */
        .row-solicitando { background-color: #fff8e1 !important; border-left: 4px solid #ffc107; }
        
        /* Indicador de Refresh */
        .live-indicator { font-size: 0.75rem; color: #27ae60; font-weight: bold; display: flex; align-items: center; gap: 5px; }
        .pulse { width: 8px; height: 8px; background: #27ae60; border-radius: 50%; animation: pulse-animation 2s infinite; }
        @keyframes pulse-animation { 0% { opacity: 1; } 50% { opacity: 0.3; } 100% { opacity: 1; } }
    </style>
</head>

<body>
    <div class="main-container">
        
        <div class="header-section">
            <div>
                <h1>Dashboard</h1>
                <div class="live-indicator"><div class="pulse"></div> Atualização Automática Ativa</div>
            </div>
            
            <div class="header-actions">
                <a href="lixeira.php" class="btn-trash" target="_blank">🗑️ Acessar Lixeira</a>
                <a href="login.php" class="btn-logout">Sair</a>
            </div>
        </div>

        <!-- MENSAGENS -->
        <?php if (isset($_SESSION['adm_msg'])): ?>
            <div style="padding: 15px; margin-bottom: 20px; border-radius: 5px; color: #fff; font-weight: bold; background-color: <?php echo ($_SESSION['adm_type'] == 'success') ? '#27ae60' : '#e74c3c'; ?>;">
                <?php echo $_SESSION['adm_msg']; unset($_SESSION['adm_msg']); unset($_SESSION['adm_type']); ?>
            </div>
        <?php endif; ?>

        <!-- MÉTRICAS -->
        <div class="dashboard-grid">
            <div class="stat-card blue"><div class="stat-info"><h3><?php echo $countUsers; ?></h3><p>Funcionários</p></div><div class="stat-icon">👥</div></div>
            <div class="stat-card orange"><div class="stat-info"><h3><?php echo $countWorking; ?></h3><p>Trabalhando Agora</p></div><div class="stat-icon">⏳</div></div>
            <div class="stat-card green"><div class="stat-info"><h3><?php echo $countPontosHoje; ?></h3><p>Pontos Hoje</p></div><div class="stat-icon">✅</div></div>
        </div>

        <!-- PONTOS EM ABERTO -->
        <details class="card" style="border-top-color: #e67e22;" <?php if(count($registrosTemp) > 0) echo 'open'; ?>>
            <summary><h2 style="color: #e67e22;">⏳ Funcionários em Turno (<?php echo count($registrosTemp); ?>)</h2><div class="toggle-icon"></div></summary>
            <div class="card-content">
                <div class="table-responsive">
                    <?php if (count($registrosTemp) > 0): ?>
                    <table>
                        <thead><tr><th>CPF</th><th>Entrada</th><th>Status</th><th style="text-align: center;">Ação</th></tr></thead>
                        <tbody>
                            <?php foreach ($registrosTemp as $reg) { ?>
                            <tr>
                                <td><?php echo $reg['cpf']; ?></td>
                                <td><?php echo $reg['entrada']; ?></td>
                                <td><span class="badge badge-working">Em andamento</span></td>
                                <td style="text-align: center;">
                                    <form method="post" onsubmit="return confirm('Mover para lixeira?');">
                                        <input type="hidden" name="delete_id_temp" value="<?php echo $reg['cpf']; ?>">
                                        <button class="btn-delete" type="submit" name="excluir_registro_temp">Cancelar Ponto</button>
                                    </form>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <?php else: ?><p>Nenhum ponto em aberto.</p><?php endif; ?>
                </div>
            </div>
        </details>

        <!-- GERENCIAR USUÁRIOS -->
        <details class="card" open>
            <summary><h2>👥 Gerenciar Usuários</h2><div class="toggle-icon"></div></summary>
            <div class="card-content">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>CPF</th>
                                <th>Status</th>
                                <th style="text-align: center;">Token Ativo</th> <!-- NOVA COLUNA -->
                                <th style="text-align: center;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registrosUsuarios as $reg) { 
                                $isSoliciting = ($reg['status'] == 'solicitando');
                                $token = $reg['token_recuperacao'];
                                $rowClass = $isSoliciting ? 'row-solicitando' : '';
                            ?>
                            <tr class="<?php echo $rowClass; ?>">
                                <td><?php echo htmlspecialchars($reg['nome']); ?></td>
                                <td><?php echo $reg['cpf']; ?></td>
                                <td>
                                    <?php echo $isSoliciting ? '🔴 <b style="color:#d32f2f">Solicitando</b>' : '🟢 <span style="color:#2e7d32">OK</span>'; ?>
                                </td>
                                
                                <!-- COLUNA DO TOKEN -->
                                <td style="text-align: center;">
                                    <?php if($token): ?>
                                        <span class="token-display" id="token-<?php echo $reg['cpf']; ?>"><?php echo $token; ?></span>
                                        <button class="btn-copy" onclick="copyToken('<?php echo $token; ?>')" title="Copiar Token">📋</button>
                                    <?php else: ?>
                                        <span style="color:#ccc">-</span>
                                    <?php endif; ?>
                                </td>

                                <td style="text-align: center; white-space: nowrap;">
                                    <form method="post" style="display:inline-block;">
                                        <input type="hidden" name="reset_id" value="<?php echo $reg['cpf']; ?>">
                                        <?php if($isSoliciting): ?>
                                            <button class="btn-reset" type="submit" name="gerar_token" style="background:#2980b9">🔑 Gerar Token</button>
                                        <?php endif; ?>
                                    </form>
                                    <form method="post" style="display:inline-block;" onsubmit="return confirm('Excluir permanentemente?');">
                                        <input type="hidden" name="delete_id" value="<?php echo $reg['cpf']; ?>">
                                        <button class="btn-delete" type="submit" name="excluir_usuario">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </details>

        <!-- CONSOLE SQL -->
        <details class="card" style="border-top-color: #333;">
            <summary><h2 style="color: #333;">🛠️ Console SQL</h2><div class="toggle-icon"></div></summary>
            <div class="card-content">
                <form method="post">
                    <div class="sql-box"><textarea class="input-sql" name="query" id="sqlEditor" placeholder="SELECT..."></textarea></div>
                    <div style="overflow: hidden;"><button type="submit" class="btn-execute">Executar</button></div>
                </form>
            </div>
        </details>
    </div>

    <script>
        // FUNÇÃO PARA COPIAR TOKEN
        function copyToken(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert("Token " + text + " copiado para a área de transferência!");
            }, function(err) {
                console.error('Erro ao copiar: ', err);
            });
        }

        // LÓGICA DE AUTO-REFRESH INTELIGENTE
        const sqlEditor = document.getElementById('sqlEditor');
        let refreshTimer = setTimeout(autoRefresh, 30000); // 30 segundos

        function autoRefresh() {
            // Só atualiza se o ADM não estiver digitando no SQL
            if (document.activeElement !== sqlEditor && sqlEditor.value === "") {
                window.location.reload();
            } else {
                // Se ele estiver ocupado, tenta de novo em 30 segundos
                console.log("Refresh pausado: ADM está usando o console SQL.");
                refreshTimer = setTimeout(autoRefresh, 30000);
            }
        }

        // Resetar o timer se houver interação na página (evita atualizar na cara do ADM)
        window.addEventListener('mousemove', resetTimer);
        window.addEventListener('keypress', resetTimer);

        function resetTimer() {
            clearTimeout(refreshTimer);
            refreshTimer = setTimeout(autoRefresh, 30000);
        }
    </script>
</body>
</html>