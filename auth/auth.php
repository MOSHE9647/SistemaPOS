<?php
    
    require_once 'config.php';
    require_once dirname(__DIR__, 1) . '/utils/Variables.php';
    
    // Verifica si el usuario está autenticado
    if (!isset($_SESSION[SESSION_AUTHENTICATED]) || $_SESSION[SESSION_AUTHENTICATED] !== true) {
        // Guarda la URL a la que intentaba acceder el usuario
        $_SESSION[SESSION_ORIGIN_URL] = $_SERVER['REQUEST_URI'];

        // Si el usuario no está autenticado, redirige a la página de login
        $LOGIN_URL = './view/auth/login.php';
        header("Location: $LOGIN_URL");
        exit();
    }

    // Verifica si la sesión ha caducado
    function verificarSesionCaducada() {
        $tiempoInactividadMaximo = 1800; // 30 minutos
        if (isset($_SESSION[SESSION_LAST_ACCESS])) {
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
        $LOGIN_URL = './view/auth/login.php';
        header("Location: $LOGIN_URL");
        exit();
    }

    // Función para verificar si el usuario tiene el rol adecuado
    function verificarRol($rolesPermitidos) {
        if (in_array($_SESSION[SESSION_USER_ROLE], $rolesPermitidos)) {
            return true;
        } else {
            return false;
        }
    }

?>