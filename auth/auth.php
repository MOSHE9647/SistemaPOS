<?php

    require_once 'config.php';
    require_once dirname(__DIR__) . '/utils/Variables.php';

    // Verifica si el usuario está autenticado
    if (empty($_SESSION[SESSION_AUTHENTICATED]) || $_SESSION[SESSION_AUTHENTICATED] !== true) {
        // Guarda la URL a la que intentaba acceder el usuario
        $_SESSION[SESSION_ORIGIN_URL] = $_SERVER['REQUEST_URI'];

        // Redirige a la página de login
        header("Location: ./view/auth/login.php");
        exit();
    }

    // Verifica si la sesión ha caducado
    function verificarSesionCaducada() {
        $tiempoInactividadMaximo = 1800; // 30 minutos
        if (!empty($_SESSION[SESSION_LAST_ACCESS])) {
            $tiempoInactividad = time() - $_SESSION[SESSION_LAST_ACCESS];
            if ($tiempoInactividad > $tiempoInactividadMaximo) {
                session_unset();
                session_destroy();
                return true;
            }
        }
        $_SESSION[SESSION_LAST_ACCESS] = time();
        return false;
    }

    if (verificarSesionCaducada()) {
        header("Location: ./view/auth/login.php");
        exit();
    }

    // Verifica si el usuario tiene el rol adecuado
    function verificarRol($rolesPermitidos) {
        return in_array($_SESSION[SESSION_USER_ROLE], $rolesPermitidos);
    }

?>