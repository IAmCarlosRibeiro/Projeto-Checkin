<?php
session_start();

// Verificar autenticação
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$dbPath = "./DB/db_pontos.db";
$db = new SQLite3($dbPath);
if (!$db) die("Erro ao conectar ao banco de dados.");

// --- LÓGICA DE DADOS ---
date_default_timezone_set('America/Sao_Paulo');
$countUsers = $db->querySingle("SELECT COUNT(*) FROM usuarios");
$countWorking = $db->querySingle("SELECT COUNT(*) FROM temp");
$today = date('Y-m-d');
$countPontosHoje = $db->querySingle("SELECT COUNT(*) FROM pontos WHERE date(entrada) = '$today'");

// --- PROCESSAMENTO DE AÇÕES (POST) ---
// Se houve um POST, processamos e depois REDIRECIONAMOS
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $redirect = true; // Flag para controlar redirecionamento

    // 1. Executar SQL
    if (isset($_POST["query"]) && !empty($_POST["query"])) {
        $query = $_POST["query"];
        if (@$db->exec($query)) {
            $_SESSION['flash_msg'] = "Consulta executada com sucesso!";
            $_SESSION['flash_type'] = "success";
        } else {
            $_SESSION['flash_msg'] = "Erro SQL: " . $db->lastErrorMsg();
            $_SESSION['flash_type'] = "error";
        }
    }
    // 2. Excluir Usuário
    elseif (isset($_POST['excluir_usuario'])) {
        $cpf = $_POST['delete_id'];
        if ($db->exec("DELETE FROM usuarios WHERE cpf = '$cpf'")) {
            $_SESSION['flash_msg'] = "Usuário excluído.";
            $_SESSION['flash_type'] = "success";
        } else {
            $_SESSION['flash_msg'] = "Erro ao excluir usuário.";
            $_SESSION['flash_type'] = "error";
        }
    }
    // 3. Reset Senha
    elseif (isset($_POST['resetar_senha'])) {
        $cpf = $_POST['reset_id'];
        if ($db->exec("UPDATE usuarios SET senha = '1234' WHERE cpf = '$cpf'")) {
            $_SESSION['flash_msg'] = "Senha resetada para '1234'.";
            $_SESSION['flash_type'] = "success";
        } else {
            $_SESSION['flash_msg'] = "Erro ao resetar senha.";
            $_SESSION['flash_type'] = "error";
        }
    }
    // 4. Mover para Lixeira (Cancelamento de Ponto)
    elseif (isset($_POST['excluir_registro_temp'])) {
        $id_temp = $_POST['delete_id_temp'];
        $entradaOriginal = $db->querySingle("SELECT entrada FROM temp WHERE cpf = '$id_temp'");
        
        if ($entradaOriginal) {
            $saidaAdmin = date("Y-m-d H:i:s");
            $sqlLixeira = "INSERT INTO lixeira (cpf, entrada, saida) VALUES ('$id_temp', '$entradaOriginal', '$saidaAdmin')";
            
            if ($db->exec($sqlLixeira)) {
                if ($db->exec("DELETE FROM temp WHERE cpf = '$id_temp'")) {
                    $_SESSION['flash_msg'] = "Ponto movido para a lixeira.";
                    $_SESSION['flash_type'] = "success";
                } else {
                    $_SESSION['flash_msg'] = "Erro ao limpar temp.";
                    $_SESSION['flash_type'] = "error";
                }
            } else {
                $_SESSION['flash_msg'] = "Erro ao mover para lixeira.";
                $_SESSION['flash_type'] = "error";
            }
        } else {
            $_SESSION['flash_msg'] = "Registro não encontrado.";
            $_SESSION['flash_type'] = "error";
        }
    } else {
        $redirect = false;
    }

    // Se processou algo, redireciona para limpar o POST
    if ($redirect) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Recarregar listas
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
        summary {
            list-style: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            outline: none;
        }
        summary::-webkit-details-marker { display: none; }
        .card h2 {
            border: none;
            margin-bottom: 0;
            padding-bottom: 0;
            width: 100%;
        }
        details[open] .card-content {
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
            margin-top: 15px;
        }
        .toggle-icon::after {
            content: '▼';
            font-size: 0.8rem;
            color: #999;
        }
        details[open] .toggle-icon::after { content: '▲'; }
        .btn-trash {
            text-decoration: none;
            color: #555;
            background-color: #fff;
            border: 1px solid #ccc;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .btn-trash:hover {
            background-color: #f0f0f0;
            border-color: #999;
            color: #333;
        }
        .header-actions {
            display: flex;
            gap: 10px;
        }
    </style>
</head>

<body>
    <div class="main-container">
        
        <div class="header-section">
            <div>
                <h1>Dashboard</h1>
                <span style="color: #666; font-size: 0.9rem;">Visão geral do sistema</span>
            </div>
            
            <div class="header-actions">
                <a href="lixeira.php" class="btn-trash" target="_blank">🗑️ Acessar Lixeira</a>
                <a href="login.php" class="btn-logout">Sair</a>
            </div>
        </div>

        <!-- EXIBIÇÃO DA MENSAGEM DE SESSÃO -->
        <?php if (isset($_SESSION['flash_msg'])): ?>
            <div style="padding: 15px; margin-bottom: 20px; border-radius: 5px; color: #fff; font-weight: bold; background-color: <?php echo ($_SESSION['flash_type'] == 'success') ? '#27ae60' : '#e74c3c'; ?>;">
                <?php 
                    echo $_SESSION['flash_msg']; 
                    // Limpa a mensagem para não aparecer de novo no próximo F5
                    unset($_SESSION['flash_msg']);
                    unset($_SESSION['flash_type']);
                ?>
            </div>
        <?php endif; ?>

        <!-- MÉTRICAS -->
        <div class="dashboard-grid">
            <div class="stat-card blue">
                <div class="stat-info"><h3><?php echo $countUsers; ?></h3><p>Funcionários</p></div>
                <div class="stat-icon">👥</div>
            </div>
            <div class="stat-card orange">
                <div class="stat-info"><h3><?php echo $countWorking; ?></h3><p>Trabalhando Agora</p></div>
                <div class="stat-icon">⏳</div>
            </div>
            <div class="stat-card green">
                <div class="stat-info"><h3><?php echo $countPontosHoje; ?></h3><p>Pontos Hoje</p></div>
                <div class="stat-icon">✅</div>
            </div>
        </div>

        <!-- SEÇÃO: PONTOS EM ABERTO -->
        <details class="card" style="border-top-color: #e67e22;" <?php if(count($registrosTemp) > 0) echo 'open'; ?>>
            <summary>
                <h2 style="color: #e67e22;">⏳ Funcionários em Turno (<?php echo count($registrosTemp); ?>)</h2>
                <div class="toggle-icon"></div>
            </summary>
            <div class="card-content">
                <div class="table-responsive">
                    <?php if (count($registrosTemp) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>CPF</th>
                                <th>Entrada</th>
                                <th>Status</th>
                                <th style="text-align: center;">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registrosTemp as $reg) { ?>
                            <tr>
                                <td><?php echo $reg['cpf']; ?></td>
                                <td><?php echo $reg['entrada']; ?></td>
                                <td><span class="badge badge-working">Em andamento</span></td>
                                <td style="text-align: center;">
                                    <form method="post" onsubmit="return confirm('Mover este ponto para a LIXEIRA? Isso encerrará o ponto com o horário atual.');">
                                        <input type="hidden" name="delete_id_temp" value="<?php echo $reg['cpf']; ?>">
                                        <button class="btn-delete" type="submit" name="excluir_registro_temp">Cancelar Ponto</button>
                                    </form>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <?php else: ?><p>Nenhum ponto em aberto no momento.</p><?php endif; ?>
                </div>
            </div>
        </details>

        <!-- SEÇÃO: GERENCIAR USUÁRIOS -->
        <details class="card">
            <summary>
                <h2>👥 Gerenciar Usuários (<?php echo $countUsers; ?>)</h2>
                <div class="toggle-icon"></div>
            </summary>
            <div class="card-content">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr><th>Nome</th><th>CPF</th><th style="text-align: center;">Ações</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registrosUsuarios as $reg) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($reg['nome']); ?></td>
                                <td><?php echo $reg['cpf']; ?></td>
                                <td style="text-align: center; white-space: nowrap;">
                                    <form method="post" style="display:inline-block;" onsubmit="return confirm('Resetar senha para 1234?');">
                                        <input type="hidden" name="reset_id" value="<?php echo $reg['cpf']; ?>">
                                        <button class="btn-reset" type="submit" name="resetar_senha">Reset Senha</button>
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

        <!-- SEÇÃO: CONSOLE SQL -->
        <details class="card" style="border-top-color: #333;">
            <summary>
                <h2 style="color: #333;">🛠️ Console SQL (Avançado)</h2>
                <div class="toggle-icon"></div>
            </summary>
            <div class="card-content">
                <form method="post" action="">
                    <div class="sql-box">
                        <textarea class="input-sql" name="query" placeholder="Digite seu SQL aqui..."></textarea>
                    </div>
                    <div style="overflow: hidden;">
                        <button type="submit" class="btn-execute">Executar Comando</button>
                    </div>
                </form>
            </div>
        </details>

    </div>
</body>
</html>