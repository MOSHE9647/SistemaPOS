<?php

    // Configuración de las sesiones para mayor seguridad
    ini_set('session.cookie_lifetime', 0); // La cookie expira cuando se cierra el navegador
    ini_set('session.cookie_httponly', true); // Solo accesible por HTTP, no por scripts
    ini_set('session.use_strict_mode', 1); // Evitar que PHP acepte identificadores de sesión no válidos

    // Inicia la sesión
    session_start();

?>
