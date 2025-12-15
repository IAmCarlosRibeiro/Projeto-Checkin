<?php
session_start();

// Verificar se o usuário está autenticado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Função para executar consultas SQL e retornar o resultado como um array associativo
function executarConsulta($db, $sql)
{
    $resultado = $db->query($sql);

    if (!$resultado) {
        return false;
    }

    $dados = array();

    while ($row = $resultado->fetchArray(SQLITE3_ASSOC)) {
        $dados[] = $row;
    }

    return $dados;
}

// Dados de conexão com o banco de dados SQLite3
$dbPath = "./DB/db_pontos.db";

// Conectar ao banco de dados SQLite3
$db = new SQLite3($dbPath);

// Verificar se a conexão foi estabelecida com sucesso
if (!$db) {
    die("Erro ao conectar ao banco de dados.");
}

// Consulta SQL para obter todos os registros da tabela "usuarios"
$sqlUsuarios = "SELECT * FROM usuarios";
$registrosUsuarios = executarConsulta($db, $sqlUsuarios);

// Consulta SQL para obter todos os registros da tabela "temp"
$sqlTemp = "SELECT * FROM temp";
$registrosTemp = executarConsulta($db, $sqlTemp);

// Verificar se houve um envio de formulário para fazer alterações no banco de dados
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $query = $_POST["query"];

    // Executar a consulta SQL para fazer alterações no banco de dados
    $resultadoQuery = $db->exec($query);

    if ($resultadoQuery) {
        echo "<script>alert('Consulta executada com sucesso!');</script>";
    } else {
        echo "<script>alert('Erro ao executar a consulta.');</script>";
    }

    // Verificar se é uma solicitação de exclusão de usuário
    if (isset($_POST['excluir_usuario'])) {
        $cpf = $_POST['delete_id'];

        // Preparar e executar a consulta SQL para excluir o usuário
        $sqlExcluirUsuario = "DELETE FROM usuarios WHERE cpf = '$cpf'";
        $resultadoExclusao = $db->exec($sqlExcluirUsuario);

        if ($resultadoExclusao) {
            echo "<script>alert('Usuário excluído com sucesso!');</script>";
            // Recarregar a página após a exclusão
            echo "<script>window.location = 'adm.php';</script>";
        } else {
            echo "<script>alert('Erro ao excluir usuário.');</script>";
        }
    }

    // Verificar se é uma solicitação de exclusão de registro temporário
    if (isset($_POST['excluir_registro_temp'])) {
        $id_temp = $_POST['delete_id_temp'];

        // Preparar e executar a consulta SQL para excluir o registro temporário
        $sqlExcluirRegistroTemp = "DELETE FROM temp WHERE cpf = '$id_temp'";
        $resultadoExclusaoTemp = $db->exec($sqlExcluirRegistroTemp);

        if ($resultadoExclusaoTemp) {
            echo "<script>alert('Registro temporário excluído com sucesso!');</script>";
            // Recarregar a página após a exclusão
            echo "<script>window.location = 'adm.php';</script>";
        } else {
            echo "<script>alert('Erro ao excluir registro temporário.');</script>";
        }
    }
}

// Fechar a conexão com o banco de dados
$db->close();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Página de Administração</title>
    <link rel="stylesheet" href="./styles/stylerelatorio.css">
    <link rel="shortcut icon" href="./styles/raposa.ico" type="image/x-icon">
    <style>
        /* Estilo para o botão de excluir */
        .btn-excluir {
            background-color: #f44336; /* Vermelho */
            color: white;
            padding: 8px 16px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }

        /* Estilo para o botão de excluir quando passa o mouse */
        .btn-excluir:hover {
            background-color: #d32f2f; /* Vermelho mais escuro */
        }
    </style>
</head>

<body>
    <h1>Página de Administração</h1>
    <div class="bodyform">
        <h2>Realizar alterações no banco de dados:</h2>
        <form method="post" action="">
            <div class="input-box">
                <textarea class="input-cpf" name="query" rows="5" cols="100" placeholder="Digite a consulta SQL aqui"></textarea>
            </div>
            <br>
            <button type="submit">Executar Consulta</button>
        </form>
        <h2>Registros da tabela usuários:</h2>
        <table>
            <tr>
                <th>Nome</th>
                <th>CPF</th>
                <th>Senha</th>
                <th>Ações</th> <!-- Adicionando uma coluna para ações -->
            </tr>
            <?php foreach ($registrosUsuarios as $registro) { ?>
                <tr>
                    <td><?php echo $registro['nome']; ?></td>
                    <td><?php echo $registro['cpf']; ?></td>
                    <td><?php echo $registro['senha']; ?></td>
                    <td>
                        <!-- Botão de excluir -->
                        <form method="post" action="">
                            <input type="hidden" name="delete_id" value="<?php echo $registro['cpf']; ?>">
                            <button class="btn-excluir" type="submit" name="excluir_usuario">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>

        <h2>Registros da tabela temporária (Pontos abertos):</h2>
        <table>
            <tr>
                <th>CPF</th>
                <th>Entrada</th>
                <th>Ações</th> <!-- Adicionando uma coluna para ações -->
            </tr>
            <?php foreach ($registrosTemp as $registro) { ?>
                <tr>
                    <td><?php echo $registro['cpf']; ?></td>
                    <td><?php echo $registro['entrada']; ?></td>
                    <td>
                        <!-- Botão de excluir para registro temporário -->
                        <form method="post" action="">
                            <input type="hidden" name="delete_id_temp" value="<?php echo $registro['cpf']; ?>">
                            <button class="btn-excluir" type="submit" name="excluir_registro_temp">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>

    </div>
</body>

</html>