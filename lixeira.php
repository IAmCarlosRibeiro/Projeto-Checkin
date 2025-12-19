<?php
session_start();

// --- VERIFICAÇÃO DE PERMISSÃO ---
// Aceita se for ADMIN (logged_in) OU MODERADOR (logged_in2)
$isAdmin = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$isMod = isset($_SESSION['logged_in2']) && $_SESSION['logged_in2'] === true;

if (!$isAdmin && !$isMod) {
    header("Location: login.php");
    exit;
}

$dbPath = "./DB/db_pontos.db";
$db = new SQLite3($dbPath);
date_default_timezone_set('America/Sao_Paulo');

// Variáveis
$calendarEvents = [];
$summaryData = [];
$showResults = false;

// Formatação
function formatarDuracao($duracao)
{
    $horas = floor($duracao / 3600);
    $minutos = floor(($duracao / 60) % 60);
    $segundos = $duracao % 60;
    return sprintf("%02d:%02d:%02d", $horas, $minutos, $segundos);
}

// --- PROCESSAMENTO DO FORMULÁRIO ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST["start_date"]) && !empty($_POST["end_date"])) {
        $startDate = $_POST["start_date"];
        $endDate = $_POST["end_date"];
        $cpf = preg_replace('/\D/', '', $_POST["cpf"]);
        $showResults = true;

        // 1. DADOS PARA O CALENDÁRIO
        $sql = "SELECT u.nome, l.cpf, l.entrada, l.saida, 
                strftime('%s', l.saida) - strftime('%s', l.entrada) AS duracao 
                FROM lixeira AS l 
                INNER JOIN usuarios AS u ON l.cpf = u.cpf 
                WHERE (date(l.entrada) >= :start_date AND date(l.entrada) <= :end_date)";

        if (!empty($cpf)) $sql .= " AND l.cpf = :cpf";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':start_date', $startDate);
        $stmt->bindValue(':end_date', $endDate);
        if (!empty($cpf)) $stmt->bindValue(':cpf', $cpf);

        $result = $stmt->execute();
        while ($row = $result->fetchArray()) {
            $titulo = $row['nome'] . " (" . formatarDuracao($row['duracao']) . ")";
            $calendarEvents[] = [
                'title' => $titulo,
                'start' => $row['entrada'],
                'end'   => $row['saida'],
                'color' => '#c0392b', // Vermelho Lixeira
                'extendedProps' => ['cpf' => $row['cpf']]
            ];
        }

        // 2. DADOS PARA A TABELA (RESUMO)
        $sqlHours = "SELECT u.nome, l.cpf, SUM(strftime('%s', l.saida) - strftime('%s', l.entrada)) AS duracao_total 
                     FROM lixeira AS l 
                     INNER JOIN usuarios AS u ON l.cpf = u.cpf 
                     WHERE (date(l.entrada) >= :start_date AND date(l.entrada) <= :end_date)";

        if (empty($cpf)) $sqlHours .= " GROUP BY l.cpf";
        else $sqlHours .= " AND l.cpf = :cpf";

        $stmtHours = $db->prepare($sqlHours);
        $stmtHours->bindValue(':start_date', $startDate);
        $stmtHours->bindValue(':end_date', $endDate);
        if (!empty($cpf)) $stmtHours->bindValue(':cpf', $cpf);

        $resultHours = $stmtHours->execute();
        while ($rowH = $resultHours->fetchArray()) {
            $summaryData[] = $rowH;
        }
    } else {
        echo "<script>alert('Selecione o período.');</script>";
    }
}
$db->close();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lixeira</title>

    <!-- CSS da Dashboard -->
    <link rel="stylesheet" href="./styles/stylerelatorio.css">
    <link rel="shortcut icon" href="./styles/clock.ico" type="image/x-icon">

    <!-- FullCalendar -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>

    <style>
        /* Botão de busca vermelho */
        .btn-filter-trash {
            background: #c0392b;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            height: 42px;
            width: 100%;
            transition: background 0.3s;
        }

        .btn-filter-trash:hover {
            background: #a93226;
        }
    </style>
</head>

<body>
    <div class="main-container">

        <!-- HEADER -->
        <div class="header-section">
            <div>
                <h1 style="color: #c0392b;">🗑️ Lixeira</h1>
                <span style="color: #666; font-size: 0.9rem;">Histórico de pontos excluídos</span>
            </div>

            <div class="header-actions">
                <?php if ($isAdmin): ?>
                    <!-- Botão Voltar (Apenas para Admin) -->
                    <a href="admin.php" class="btn-back">← Dashboard</a>
                <?php endif; ?>

                <!-- Botão Sair (Para todos) -->
                <a href="logout.php" class="btn-logout">Sair</a>
            </div>
        </div>

        <!-- CARD DE FILTRO -->
        <div class="card">
            <div class="card-content" style="border:none; padding-top:20px;">
                <form method="POST" action="" class="filter-form">
                    <div class="input-group">
                        <label>Data Início:</label>
                        <input type="date" name="start_date" required value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : ''; ?>">
                    </div>

                    <div class="input-group">
                        <label>Data Término:</label>
                        <input type="date" name="end_date" required value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : ''; ?>">
                    </div>

                    <div class="input-group">
                        <label>Filtrar CPF (Opcional):</label>
                        <input id="cpf" type="text" name="cpf" placeholder="000.000.000-00" maxlength="14" value="<?php echo isset($_POST['cpf']) ? $_POST['cpf'] : ''; ?>">
                    </div>

                    <div class="input-group">
                        <button type="submit" class="btn-filter-trash">🔍 Buscar Registros</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($showResults): ?>

            <!-- CALENDÁRIO (RETRÁTIL) -->
            <details class="card" style="border-top-color: #c0392b;" open id="detailsCalendar">
                <summary>
                    <div class="summary-content">
                        <h2>📅 Visão Mensal (Excluídos)</h2>
                    </div>
                    <div class="toggle-icon"></div>
                </summary>
                <div class="card-content">
                    <div id='calendar'></div>
                </div>
            </details>

            <!-- TABELA DE RESUMO (RETRÁTIL) -->
            <?php if (count($summaryData) > 0): ?>
                <details class="card" style="border-top-color: #c0392b;" open>
                    <summary>
                        <div class="summary-content">
                            <h2>⏱️ Resumo de Horas Excluídas</h2>
                        </div>
                        <div class="toggle-icon"></div>
                    </summary>
                    <div class="card-content">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr style="background:#c0392b; color:white;">
                                        <th style="background:#c0392b; color:white;">Nome</th>
                                        <th style="background:#c0392b; color:white;">CPF</th>
                                        <th style="background:#c0392b; color:white;">Total de Horas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($summaryData as $data): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($data['nome']); ?></td>
                                            <td><?php echo htmlspecialchars($data['cpf']); ?></td>
                                            <td><strong><?php echo formatarDuracao($data['duracao_total']); ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </details>
            <?php else: ?>
                <div class="card">
                    <div class="card-content" style="padding:20px; text-align:center; color:#666;">
                        Nenhum registro encontrado na lixeira para este período.
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </div>

    <script>
        // Máscara CPF
        var numberInput = document.getElementById("cpf");
        if (numberInput) {
            numberInput.addEventListener("input", function() {
                var v = this.value.replace(/\D/g, "");
                v = v.replace(/(\d{3})(\d)/, "$1.$2");
                v = v.replace(/(\d{3})(\d)/, "$1.$2");
                v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
                this.value = v;
            });
        }

        // Calendário
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var detailsEl = document.getElementById('detailsCalendar');

            if (calendarEl) {
                var eventsData = <?php echo json_encode($calendarEvents); ?>;
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    locale: 'pt-br',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,listWeek'
                    },
                    buttonText: {
                        today: 'Hoje',
                        month: 'Mês',
                        week: 'Semana',
                        list: 'Lista'
                    },
                    events: eventsData,
                    height: 'auto',
                    eventClick: function(info) {
                        info.jsEvent.preventDefault();
                        var nome = info.event.title.split(' (')[0];
                        var entrada = info.event.start.toLocaleString();
                        var saida = info.event.end ? info.event.end.toLocaleString() : '?';
                        alert('REGISTRO EXCLUÍDO\n\nFuncionário: ' + nome + '\nEntrada: ' + entrada + '\nSaída: ' + saida);
                    }
                });

                if (eventsData.length > 0) calendar.gotoDate(eventsData[0].start);

                calendar.render();

                // FIX: Redesenhar calendário ao abrir a aba
                if (detailsEl) {
                    detailsEl.addEventListener("toggle", function() {
                        if (this.open) {
                            setTimeout(function() {
                                calendar.updateSize();
                            }, 50);
                        }
                    });
                }
            }
        });
    </script>
</body>

</html>