<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lixeira</title>
    
    <!-- Certifique-se de copiar o CSS novo para este arquivo ou usar o stylerelatorio.css -->
    <link rel="stylesheet" href="./styles/stylelixeira.css">
    <link rel="shortcut icon" href="./styles/clock.ico" type="image/x-icon">

    <!-- FullCalendar CSS e JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
</head>

<body>
    <div class="main-container">
        
        <div class="header-section">
            <h1>Lixeira</h1>
        </div>

        <!-- Card do Formulário -->
        <div class="card">
            <div class="bodyform">
                <form method="POST" action="">
                    <label for="start_date">Data de início:</label>
                    <input type="date" id="start_date" name="start_date" required value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : ''; ?>">

                    <label for="end_date">Data de término:</label>
                    <input type="date" id="end_date" name="end_date" required value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : ''; ?>">

                    <div class="input-box">
                        <label for="cpf" class="input-cpf-label">CPF:</label>
                        <input id="cpf" class="input-cpf" type="text" name="cpf" placeholder="CPF" value="<?php echo isset($_POST['cpf']) ? $_POST['cpf'] : ''; ?>">
                    </div>

                    <button type="submit">Gerar Lista</button>
                </form>
            </div>
        </div>

        <?php
        session_start();

        // Verificar se o usuário está autenticado
        if (!isset($_SESSION['logged_in2']) || $_SESSION['logged_in2'] !== true) {
            header("Location: loginl.php");
            exit;
        }

        // Variáveis do sistema
        $calendarEvents = [];
        $summaryData = [];
        $showResults = false;

        // Conexão DB
        $dbPath = "./DB/db_pontos.db";
        $db = new SQLite3($dbPath);

        if (!$db) {
            echo "<script>alert('Erro ao conectar ao banco de dados.');</script>";
        }

        // Função de formatação
        function formatarDuracao($duracao) {
            $horas = floor($duracao / 3600);
            $minutos = floor(($duracao / 60) % 60);
            $segundos = $duracao % 60;
            return sprintf("%02d:%02d:%02d", $horas, $minutos, $segundos);
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!empty($_POST["start_date"]) && !empty($_POST["end_date"])) {
                $startDate = $_POST["start_date"];
                $endDate = $_POST["end_date"];
                $cpf = $_POST["cpf"];
                $showResults = true;

                // 1. CALENDÁRIO: Busca dados da tabela Lixeira
                // Nota: Alterado de 'pontos' para 'lixeira'
                $sql = "SELECT u.nome, l.cpf, l.entrada, l.saida, 
                        strftime('%s', l.saida) - strftime('%s', l.entrada) AS duracao 
                        FROM lixeira AS l 
                        INNER JOIN usuarios AS u ON l.cpf = u.cpf 
                        WHERE (date(l.entrada) >= :start_date AND date(l.entrada) <= :end_date)";
                
                if (!empty($cpf)) {
                    $sql .= " AND l.cpf = :cpf";
                }

                $stmt = $db->prepare($sql);
                if ($stmt) {
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
                            'color' => '#d9534f', // Cor vermelha para indicar lixeira/excluídos
                            'extendedProps' => [
                                'cpf' => $row['cpf']
                            ]
                        ];
                    }
                }

                // 2. TABELA: Busca resumo da tabela Lixeira
                // Nota: Alterado de 'pontos' para 'lixeira'
                $sqlHours = "SELECT u.nome, l.cpf, SUM(strftime('%s', l.saida) - strftime('%s', l.entrada)) AS duracao_total 
                             FROM lixeira AS l 
                             INNER JOIN usuarios AS u ON l.cpf = u.cpf 
                             WHERE (date(l.entrada) >= :start_date AND date(l.entrada) <= :end_date)";

                if (empty($cpf)) {
                    $sqlHours .= " GROUP BY l.cpf";
                } else {
                    $sqlHours .= " AND l.cpf = :cpf";
                }

                $stmtHours = $db->prepare($sqlHours);
                if ($stmtHours) {
                    $stmtHours->bindValue(':start_date', $startDate);
                    $stmtHours->bindValue(':end_date', $endDate);
                    if (!empty($cpf)) $stmtHours->bindValue(':cpf', $cpf);

                    $resultHours = $stmtHours->execute();
                    while ($rowH = $resultHours->fetchArray()) {
                        $summaryData[] = $rowH;
                    }
                }
            } else {
                echo "<script>alert('Por favor, selecione um intervalo de tempo.');</script>";
            }
        }
        $db->close();
        ?>

        <?php if ($showResults): ?>
            
            <!-- Calendário -->
            <div class="card">
                <h2 style="margin-bottom: 20px;">📅 Visão Mensal (Itens Excluídos)</h2>
                <div id='calendar'></div>
            </div>

            <!-- Tabela de Resumo -->
            <?php if (count($summaryData) > 0): ?>
            <div class="card">
                <h2 style="margin-bottom: 20px;">⏱️ Resumo de Horas na Lixeira</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>CPF</th>
                                <th>Total de Horas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($summaryData as $data): ?>
                                <tr>
                                    <td><?php echo $data['nome']; ?></td>
                                    <td><?php echo $data['cpf']; ?></td>
                                    <td><strong><?php echo formatarDuracao($data['duracao_total']); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php else: ?>
                <script>alert('Nenhum registro encontrado na lixeira para este período.');</script>
            <?php endif; ?>

        <?php endif; ?>

    </div>

    <script>
        // Formatar campo CPF apenas números
        var numberInput = document.getElementById("cpf");
        if(numberInput){
            numberInput.addEventListener("input", function () {
                this.value = this.value.replace(/\D/g, "");
            });
        }

        // Inicializar Calendário
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            
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
                        today:    'Hoje',
                        month:    'Mês',
                        week:     'Semana',
                        day:      'Dia',
                        list:     'Lista'
                    },
                    events: eventsData,
                    height: 'auto',

                    // Evento de clique detalhado
                    eventClick: function(info) {
                        info.jsEvent.preventDefault();

                        var cpfFuncionario = info.event.extendedProps.cpf ? info.event.extendedProps.cpf : 'Não informado';
                        var nomeFuncionario = info.event.title.split(' (')[0];

                        var options = { 
                            day: '2-digit', month: '2-digit', year: 'numeric', 
                            hour: '2-digit', minute: '2-digit', second: '2-digit' 
                        };

                        var entrada = info.event.start ? info.event.start.toLocaleString('pt-BR', options) : 'Erro';
                        var saida = info.event.end ? info.event.end.toLocaleString('pt-BR', options) : 'Em andamento';

                        alert(
                            'Registro de Lixeira\n\n' +
                            'Funcionário: ' + nomeFuncionario + 
                            '\nCPF: ' + cpfFuncionario + 
                            '\nEntrada: ' + entrada + 
                            '\nSaída: ' + saida
                        );
                    }
                });
                
                if (eventsData.length > 0) {
                    calendar.gotoDate(eventsData[0].start);
                }

                calendar.render();
            }
        });
    </script>
</body>
</html>