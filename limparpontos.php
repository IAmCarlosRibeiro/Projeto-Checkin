<?php
if ($_SERVER["HTTP_X_CRON_AUTH"] != "111313fa7a549cea71469bfab796a058") {
    die("Acesso não Autorizado");
}

// Definir o fuso horário para Brasília
date_default_timezone_set('America/Sao_Paulo');

// Dados de conexão com o banco de dados SQLite3
$dbPath = "./DB/db_pontos.db";

// Conectar ao banco de dados SQLite3
$db = new SQLite3($dbPath);

// Verificar se a conexão foi estabelecida com sucesso
if (!$db) {
    die("Erro ao conectar ao banco de dados.");
}

// Criar a tabela "lixeira" se ela não existir
$sqlCriarLixeira = "
    CREATE TABLE IF NOT EXISTS lixeira (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        cpf INTEGER NOT NULL,
        entrada DATETIME,
        saida DATETIME,
        FOREIGN KEY(cpf) REFERENCES usuarios (cpf)
    )
";

if ($db->exec($sqlCriarLixeira)) {
    echo "Tabela 'lixeira' verificada/criada com sucesso.<br>";
} else {
    echo "Erro ao criar tabela 'lixeira'.<br>";
}

// Consultar registros da tabela "temp"
$sqlSelecionarTemp = "SELECT cpf, entrada FROM temp";
$result = $db->query($sqlSelecionarTemp);

if ($result) {
    $saida = date("Y-m-d H:i:s"); // Obtém a data e hora atual para a saída

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $cpf = $row['cpf'];
        $entrada = $row['entrada'];

        // Inserir registro na tabela "lixeira"
        $sqlInserirLixeira = "
            INSERT INTO lixeira (cpf, entrada, saida)
            VALUES ('$cpf', '$entrada', '$saida')
        ";

        if ($db->exec($sqlInserirLixeira)) {
            echo "Registro movido para a tabela 'lixeira' com sucesso.<br>";
        } else {
            echo "Erro ao mover registro para a tabela 'lixeira'.<br>";
        }
    }

    // Deletar todos os registros da tabela "temp"
    $sqlDeletarTemp = "DELETE FROM temp";

    if ($db->exec($sqlDeletarTemp)) {
        echo "Registros da tabela 'temp' deletados com sucesso!<br>";
    } else {
        echo "Erro ao deletar registros da tabela 'temp'.<br>";
    }
} else {
    echo "Erro ao selecionar registros da tabela 'temp'.<br>";
}

// Inserir dados específicos na tabela "pontos"
$entrada = date("Y-m-d H:i:s"); // Obtém a data e hora atual
$saida = date("Y-m-d H:i:s"); // Obtém a data e hora atual

$sqlInserirPontos = "INSERT INTO pontos (cpf, entrada, saida) VALUES ('555555', '$entrada', '$saida')";

if ($db->exec($sqlInserirPontos)) {
    echo "Registro inserido na tabela 'pontos' com sucesso!<br>";
} else {
    echo "Erro ao inserir registro na tabela 'pontos'.<br>";
}

// Fechar a conexão com o banco de dados
$db->close();
?>