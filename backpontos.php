<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');

$dbPath = "./DB/db_pontos.db";
$db = new SQLite3($dbPath);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $cpf = preg_replace('/\D/', '', $_POST["cpf"]); 
    $password = $_POST["password"];

    $stmt = $db->prepare("SELECT * FROM usuarios WHERE cpf = :cpf");
    $stmt->bindValue(':cpf', $cpf);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);

    // Verifica usuário e senha
    if ($user && password_verify($password, $user['senha'])) {

        $checkTemp = $db->querySingle("SELECT COUNT(*) FROM temp WHERE cpf = '$cpf'");

        if ($checkTemp > 0) {
            // SAÍDA
            $rowTemp = $db->querySingle("SELECT entrada FROM temp WHERE cpf = '$cpf'", true);
            $entrada = $rowTemp['entrada'];
            $saida = date("Y-m-d H:i:s");

            $stmtInsert = $db->prepare("INSERT INTO pontos (cpf, entrada, saida) VALUES (:cpf, :entrada, :saida)");
            $stmtInsert->bindValue(':cpf', $cpf);
            $stmtInsert->bindValue(':entrada', $entrada);
            $stmtInsert->bindValue(':saida', $saida);

            if ($stmtInsert->execute()) {
                $db->exec("DELETE FROM temp WHERE cpf = '$cpf'");
                // NOME DA VARIÁVEL: msg_index
                $_SESSION['msg_index'] = "Saída registrada: " . date("d/m/y H:i:s");
                $_SESSION['type_index'] = "success"; 
            } else {
                $_SESSION['msg_index'] = "Erro ao salvar saída.";
                $_SESSION['type_index'] = "error";
            }

        } else {
            // ENTRADA
            $entrada = date("Y-m-d H:i:s");
            $stmtTemp = $db->prepare("INSERT INTO temp (cpf, entrada) VALUES (:cpf, :entrada)");
            $stmtTemp->bindValue(':cpf', $cpf);
            $stmtTemp->bindValue(':entrada', $entrada);

            if ($stmtTemp->execute()) {
                // NOME DA VARIÁVEL: msg_index
                $_SESSION['msg_index'] = "Entrada registrada: " . date("d/m/y H:i:s");
                $_SESSION['type_index'] = "success";
            } else {
                $_SESSION['msg_index'] = "Erro ao salvar entrada.";
                $_SESSION['type_index'] = "error";
            }
        }

    } else {
        // NOME DA VARIÁVEL: msg_index
        $_SESSION['msg_index'] = "CPF ou Senha incorretos.";
        $_SESSION['type_index'] = "error";
    }

    header("Location: index.php");
    exit;
}
$db->close();
?>