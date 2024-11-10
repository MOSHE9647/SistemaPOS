<?php
    // Archivos requeridos
    require_once dirname(__DIR__, 2) . '/auth/config.php';
    require_once dirname(__DIR__, 2) . '/utils/Variables.php';

    // Verifica si el usuario está autenticado
    if (!empty($_SESSION[SESSION_AUTHENTICATED]) && $_SESSION[SESSION_AUTHENTICATED] === true) {
        // Si el usuario está autenticado, redirige al index
        header("Location: ../../index.php");
        exit();
    }

    // Función para generar un mensaje basado en los parámetros de sesión
    function getSessionMessage() {
        if (!empty($_SESSION[SESSION_ACCESS_DENIED])) {
            unset($_SESSION[SESSION_ACCESS_DENIED]);
            return ['message' => 'No tiene permiso para acceder a esta página', 'type' => 'error', 'title' => 'Acceso denegado'];
        }
        if (!empty($_GET[SESSION_LOGGED_OUT])) {
            return ['message' => 'La sesión se ha cerrado correctamente', 'type' => 'info', 'title' => 'Sesión cerrada'];
        }
        if (empty($_SESSION[SESSION_AUTHENTICATED])) {
            return ['message' => 'Por favor inicie sesión para continuar', 'type' => 'info', 'title' => 'Inicio de sesión'];
        }
        return null;
    }

    // Variables de la página
    $sessionMessage = getSessionMessage();

    // Rutas de la página
    $loginURL = '../../controller/loginAction.php';
    $loginScript = '../static/js/auth/login.js';
    $loginStylesheet = '../static/css/auth/login.css';

    // Parámetros que deben ser eliminados de la URL después de ser utilizados
    $urlParamsToRemove = [SESSION_LOGGED_OUT];
?>

<!DOCTYPE html>
<html lang="es-cr">
    <!-- @author Isaac Herrera -->
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="author" content="Isaac Herrera">
        <meta name="description" content="Login">
        <title>Inicio de Sesi&oacute;n | POSFusion</title>
        <link rel="stylesheet" href="<?= $loginStylesheet ?>">
    </head>
    <body>
        <div class="main-container">
            <h1>Inicio de Sesi&oacute;n</h1>
            <p>Inicie sesi&oacute;n con su correo y contrase&ntilde;a</p><br>
            <form id="loginForm" class="form" action="<?= $loginURL ?>" method="post">
                <label for="email">Correo</label>
                <input type="email" name="email" id="email" required>
                <label for="password">Contrase&ntilde;a</label>
                <input type="password" name="password" id="password" required>
                <a href="#" onclick="
                    mostrarMensaje(
                        'Si no recuerda su contraseña, por favor comuníquese con el administrador del sistema para obtener ayuda.',
                        'warning',
                        'Contraseña olvidada'
                    );
                ">Olvid&oacute; su contrase&ntilde;a?</a>
                <button type="submit">INICIAR</button>
            </form>
            <a href="https://github.com/MOSHE9647/SistemaPOS" target="_blank" class="github-icon">
                <i class="lab la-github"></i>
            </a>
        </div>

        <!-- Loader -->
        <div class="loader-container">
            <div class="lds-ring loader" id="loader"><div></div><div></div><div></div><div></div></div>
        </div>

        <!-- Scripts de la pagina -->
        <script type="module" src="<?= $loginScript ?>"></script>
        <script>
            // Ejecutar si hay un mensaje de sesión
            document.addEventListener("DOMContentLoaded", function() {
                <?php if ($sessionMessage): ?>
                    mostrarMensaje(
                        '<?= $sessionMessage['message'] ?>', 
                        '<?= $sessionMessage['type'] ?>', 
                        '<?= $sessionMessage['title'] ?>'
                    );

                    // Elimina los parámetros de sesión una vez usados
                    removeUrlParams(<?= json_encode($urlParamsToRemove) ?>);
                <?php endif; ?>
            });
        </script>
    </body>
</html>