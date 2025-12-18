<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$dbPath = "./DB/db_pontos.db";
$db = new SQLite3($dbPath);
date_default_timezone_set('America/Sao_Paulo');

// --- MÉTRICAS GERAIS ---
$countUsers = $db->querySingle("SELECT COUNT(*) FROM usuarios");
$countWorking = $db->querySingle("SELECT COUNT(*) FROM temp");
$today = date('Y-m-d');
$countPontosHoje = $db->querySingle("SELECT COUNT(*) FROM pontos WHERE date(entrada) = '$today'");

// --- PROCESSAMENTO POST ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $redirect = true;

    // 1. SALVAR ALTERAÇÕES (Vem do Modal)
    if (isset($_POST['save_changes'])) {
        $cpf = $_POST['modal_cpf'];
        $newStatus = $_POST['modal_status'];
        $newPerm = $_POST['modal_perm'];
        
        $stmt = $db->prepare("UPDATE usuarios SET status = :st, admin = :adm WHERE cpf = :cpf");
        $stmt->bindValue(':st', $newStatus);
        $stmt->bindValue(':adm', $newPerm);
        $stmt->bindValue(':cpf', $cpf);
        $stmt->execute();
        $_SESSION['adm_msg'] = "Dados atualizados com sucesso!"; $_SESSION['adm_type'] = "success";
    }

    // 2. EXCLUIR USUÁRIO (Vem do Modal)
    elseif (isset($_POST['delete_user'])) {
        $cpf = $_POST['modal_cpf']; 
        $db->exec("DELETE FROM usuarios WHERE cpf = '$cpf'");
        $_SESSION['adm_msg'] = "Usuário excluído."; $_SESSION['adm_type'] = "success";
    }

    // 3. GERAR TOKEN
    elseif (isset($_POST['gerar_token'])) {
        $cpf = $_POST['reset_id'];
        $token = rand(100000, 999999);
        $stmt = $db->prepare("UPDATE usuarios SET token_recuperacao = :token, token_expiracao = datetime('now', '+60 minutes') WHERE cpf = :cpf");
        $stmt->bindValue(':token', $token);
        $stmt->bindValue(':cpf', $cpf);
        if ($stmt->execute()) {
            $_SESSION['adm_msg'] = "Token $token gerado!"; $_SESSION['adm_type'] = "success";
        }
    }
    
    // 4. MOVER PARA LIXEIRA
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
    
    // 5. SQL
    elseif (isset($_POST['query']) && !empty($_POST['query'])) {
        if (@$db->exec($_POST['query'])) {
            $_SESSION['adm_msg'] = "SQL executado."; $_SESSION['adm_type'] = "success";
        } else {
            $_SESSION['adm_msg'] = "Erro SQL: " . $db->lastErrorMsg(); $_SESSION['adm_type'] = "error";
        }
    }
    else { $redirect = false; }

    if ($redirect) {
        $qs = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
        header("Location: adm.php" . $qs);
        exit;
    }
}

// --- CONSULTAS COM FILTROS DE PESQUISA (GET) ---

// Filtro Usuários
$search_users = isset($_GET['q_users']) ? trim($_GET['q_users']) : '';
$sqlUsers = "SELECT * FROM usuarios";
if ($search_users) {
    $sqlUsers .= " WHERE nome LIKE '%$search_users%' OR cpf LIKE '%$search_users%' OR email LIKE '%$search_users%'";
}
$sqlUsers .= " ORDER BY nome ASC";

$registrosUsuarios = [];
$res = $db->query($sqlUsers);
while ($row = $res->fetchArray(SQLITE3_ASSOC)) $registrosUsuarios[] = $row;

// Filtro Turno
$search_temp = isset($_GET['q_temp']) ? trim($_GET['q_temp']) : '';
$sqlTemp = "SELECT * FROM temp";
if ($search_temp) {
    $sqlTemp .= " WHERE cpf LIKE '%$search_temp%'";
}

$registrosTemp = [];
$res = $db->query($sqlTemp);
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
    <script>
        function stopProp(e) { e.stopPropagation(); }
        function copyToken(text) { navigator.clipboard.writeText(text).then(() => alert("Copiado!")); }
        
        // FUNÇÕES DO MODAL
        function openModal(nome, cpf, email, status, perm) {
            document.getElementById('modalTitle').innerText = "Gerenciar: " + nome;
            document.getElementById('modal_cpf').value = cpf;
            document.getElementById('display_cpf').value = cpf; 
            document.getElementById('modal_status').value = status || 'ativo';
            document.getElementById('modal_perm').value = perm;
            
            document.getElementById('manageModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('manageModal').style.display = 'none';
        }
    </script>
</head>

<body>
    <div class="main-container">
        
        <!-- HEADER -->
        <div class="header-section">
            <div class="header-title">
                <h1>Dashboard</h1>
                <div class="live-indicator"><div class="pulse"></div> Atualização Automática Ativa</div>
            </div>
            <div class="header-actions">
                <a href="lixeira.php" class="btn-trash" target="_blank">🗑️ Acessar Lixeira</a>
                <a href="login.php" class="btn-logout">Sair</a>
            </div>
        </div>

        <?php if (isset($_SESSION['adm_msg'])): ?>
            <div style="padding: 15px; margin-bottom: 20px; border-radius: 5px; color: #fff; font-weight: bold; background-color: <?php echo ($_SESSION['adm_type'] == 'success') ? '#27ae60' : '#e74c3c'; ?>;">
                <?php echo $_SESSION['adm_msg']; unset($_SESSION['adm_msg']); unset($_SESSION['adm_type']); ?>
            </div>
        <?php endif; ?>

        <!-- METRICAS -->
        <div class="dashboard-grid">
            <div class="stat-card blue"><div class="stat-info"><h3><?php echo $countUsers; ?></h3><p>Funcionários</p></div><div class="stat-icon">👥</div></div>
            <div class="stat-card orange"><div class="stat-info"><h3><?php echo $countWorking; ?></h3><p>Trabalhando Agora</p></div><div class="stat-icon">⏳</div></div>
            <div class="stat-card green"><div class="stat-info"><h3><?php echo $countPontosHoje; ?></h3><p>Pontos Hoje</p></div><div class="stat-icon">✅</div></div>
        </div>

        <!-- ABA 1: FUNCIONÁRIOS EM TURNO (Primeira Posição) -->
        <details class="card" style="border-top-color: #e67e22;" <?php if(count($registrosTemp) > 0 || $search_temp) echo 'open'; ?>>
            <summary>
                <div class="summary-content">
                    <h2 style="color: #e67e22;">⏳ Funcionários em Turno (<?php echo count($registrosTemp); ?>)</h2>
                    
                    <!-- Barra de Pesquisa -->
                    <form method="GET" class="search-form" onclick="stopProp(event)">
                        <input type="text" name="q_temp" class="search-input" placeholder="Pesquisar CPF..." value="<?php echo htmlspecialchars($search_temp); ?>">
                        <!-- Mantém a busca da outra aba -->
                        <?php if($search_users): ?><input type="hidden" name="q_users" value="<?php echo $search_users; ?>"><?php endif; ?>
                    </form>
                </div>
                <div class="toggle-icon"></div>
            </summary>
            
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
                                <td><span class="badge badge-solicita">Em andamento</span></td>
                                <td style="text-align: center;">
                                    <form method="post" onsubmit="return confirm('Mover para lixeira?');">
                                        <input type="hidden" name="delete_id_temp" value="<?php echo $reg['cpf']; ?>">
                                        <button class="btn-manage" style="background:#c0392b" type="submit" name="excluir_registro_temp">Cancelar</button>
                                    </form>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <?php else: ?><p>Nenhum registro encontrado.</p><?php endif; ?>
                </div>
            </div>
        </details>

        <!-- ABA 2: GERENCIAR USUÁRIOS (Segunda Posição) -->
        <details class="card" <?php if(!$search_temp) echo 'open'; ?>>
            <summary>
                <div class="summary-content">
                    <h2>👥 Gerenciar Usuários</h2>
                    
                    <!-- Barra de Pesquisa -->
                    <form method="GET" class="search-form" onclick="stopProp(event)">
                        <input type="text" name="q_users" class="search-input" placeholder="Nome, CPF ou Email..." value="<?php echo htmlspecialchars($search_users); ?>">
                        <!-- Mantém a busca da outra aba -->
                        <?php if($search_temp): ?><input type="hidden" name="q_temp" value="<?php echo $search_temp; ?>"><?php endif; ?>
                    </form>
                </div>
                <div class="toggle-icon"></div>
            </summary>

            <div class="card-content">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>CPF</th>
                                <th>E-mail</th>
                                <th>Status</th>
                                <th>Permissão</th>
                                <th>Token</th>
                                <th style="text-align: center;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registrosUsuarios as $reg) { 
                                $isSoliciting = ($reg['status'] == 'solicitando');
                                $token = $reg['token_recuperacao'];
                                $rowClass = $isSoliciting ? 'row-solicitando' : '';
                                $st = $reg['status'] ?: 'ativo';
                                
                                // Badges Visuais
                                $badgeClass = 'badge-ok';
                                $statusLabel = 'Ativo';
                                if($st == 'solicitando') { $badgeClass = 'badge-solicita'; $statusLabel = 'Solicitando'; }
                                if($st == 'ferias') { $badgeClass = 'badge-ferias'; $statusLabel = 'Férias'; }
                                if($st == 'desligado') { $badgeClass = 'badge-desligado'; $statusLabel = 'Desligado'; }
                                
                                // Permissão Visual
                                $permLabel = 'Usuário';
                                if($reg['admin'] == 1) $permLabel = '<b style="color:red">Admin</b>';
                                if($reg['admin'] == 2) $permLabel = '<b>Moderador</b>';
                            ?>
                            <tr class="<?php echo $rowClass; ?>">
                                <td><?php echo htmlspecialchars($reg['nome']); ?></td>
                                <td><?php echo $reg['cpf']; ?></td>
                                <td><?php echo htmlspecialchars($reg['email']); ?></td>
                                
                                <!-- Status -->
                                <td><span class="badge <?php echo $badgeClass; ?>"><?php echo $statusLabel; ?></span></td>
                                
                                <!-- Permissão -->
                                <td><?php echo $permLabel; ?></td>
                                
                                <!-- Token -->
                                <td style="text-align: center;">
                                    <?php if($token): ?>
                                        <span class="token-display"><?php echo $token; ?></span>
                                        <button class="btn-copy" onclick="copyToken('<?php echo $token; ?>')">📋</button>
                                    <?php else: ?>-<?php endif; ?>
                                </td>

                                <!-- Ações -->
                                <td style="text-align: center; white-space: nowrap;">
                                    <?php if($isSoliciting): ?>
                                        <form method="post" style="display:inline-block;" onsubmit="return confirm('Gerar Token?');">
                                            <input type="hidden" name="reset_id" value="<?php echo $reg['cpf']; ?>">
                                            <button class="btn-manage" style="background:#d35400" type="submit" name="gerar_token">🔑 Gerar Token</button>
                                        </form>
                                    <?php endif; ?>

                                    <button class="btn-manage" onclick="openModal(
                                        '<?php echo addslashes($reg['nome']); ?>', 
                                        '<?php echo $reg['cpf']; ?>', 
                                        '<?php echo $reg['email']; ?>',
                                        '<?php echo $st; ?>',
                                        '<?php echo $reg['admin']; ?>'
                                    )">⚙️ Gerenciar</button>
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
            <summary><div class="summary-content"><h2 style="color: #333;">🛠️ Console SQL</h2></div><div class="toggle-icon"></div></summary>
            <div class="card-content">
                <form method="post">
                    <div class="sql-box"><textarea class="input-sql" name="query" id="sqlEditor" placeholder="SELECT..."></textarea></div>
                    <div style="overflow: hidden;"><button type="submit" class="btn-execute">Executar</button></div>
                </form>
            </div>
        </details>

    </div> <!-- Fim Main -->

    <!-- === MODAL === -->
    <div id="manageModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h2 id="modalTitle">Gerenciar</h2>
                <button class="btn-close" onclick="closeModal()">×</button>
            </div>
            
            <form method="POST">
                <input type="hidden" id="modal_cpf" name="modal_cpf">
                
                <div class="modal-group">
                    <label>CPF (Apenas leitura)</label>
                    <input type="text" id="display_cpf" disabled style="background:#eee;">
                </div>

                <div class="modal-group">
                    <label>Status</label>
                    <select name="modal_status" id="modal_status">
                        <option value="ativo">✅ Ativo</option>
                        <option value="ferias">🏖️ Férias</option>
                        <option value="desligado">🚫 Desligado</option>
                    </select>
                </div>

                <div class="modal-group">
                    <label>Nível de Permissão</label>
                    <select name="modal_perm" id="modal_perm">
                        <option value="0">Usuário Comum</option>
                        <option value="2">Moderador (Lixeira)</option>
                        <option value="1">Administrador Geral</option>
                    </select>
                </div>

                <div class="modal-actions">
                    <button type="submit" name="save_changes" class="btn-save">💾 Salvar Alterações</button>
                    <hr style="width:100%; border:0; border-top:1px solid #eee; margin:10px 0;">
                    <button type="submit" name="delete_user" class="btn-danger" onclick="return confirm('TEM CERTEZA? Isso apagará todo o histórico deste usuário.')">🗑️ Excluir Usuário</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function copyToken(text) { navigator.clipboard.writeText(text).then(() => alert("Copiado!")); }
        
        const sqlEditor = document.getElementById('sqlEditor');
        setInterval(function() {
            if (document.activeElement !== sqlEditor && sqlEditor.value === "") {
                if(!document.querySelector('.search-input:focus')) {
                    // window.location.reload(); 
                }
            }
        }, 30000);
    </script>
</body>
</html>