<?php

    require_once __DIR__ . '/../../auth/config.php';
    require_once __DIR__ . '/../../utils/Variables.php';

    // Elimina las variables de sesión
    $_SESSION = [];

    // Si se usa una cookie de sesión, se destruye
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Destruye la sesión
    session_destroy();

    // Redirigir a login o página de inicio
    $INDEX_URL = '/../index.php?' . SESSION_LOGGED_OUT . '=true';
    header("Location: $INDEX_URL");
    exit();

?>