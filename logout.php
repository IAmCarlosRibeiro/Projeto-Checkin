<!--
"""
    Ponto Online
    Copyright (c) 2026 Carlos Eduardo Santos Ribeiro.
    All Rights Reserved.

    This software is PROPRIETARY. Use is subject to the terms in the LICENSE file.
    Unauthorized distribution, modification, or commercial use is strictly prohibited.
"""
-->

<?php
session_start();

// Destrói todas as variáveis de sessão
$_SESSION = array();

// Se for preciso, mata o cookie da sessão também
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destrói a sessão
session_destroy();

// Redireciona para o index (Tela de Ponto)
header("Location: index.php");
exit;
?>