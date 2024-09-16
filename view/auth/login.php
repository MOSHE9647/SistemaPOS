<?php
    
    require_once __DIR__ . '/../../auth/config.php';
    require_once __DIR__ . '/../../utils/Variables.php';
    
    // Verifica si el usuario está autenticado
    if (!isset($_SESSION[SESSION_ACCESS_DENIED]) && (isset($_SESSION[SESSION_AUTHENTICATED]) || $_SESSION[SESSION_AUTHENTICATED] === true)) {
        // Si el usuario está autenticado, redirige al index
        $INDEX_PAGE = "/../../index.php";
        header("Location: $INDEX_PAGE");
        exit();
    }

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión | POSFusion</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <div class="login-container">
        <h2>Iniciar Sesión</h2>
        <form id="loginForm">
            <label for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" required>
            
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit">Iniciar Sesión</button>
        </form>
    </div>
    
    <!-- Scripts del Archivo -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="./view/js/utils.js"></script>
    <script defer src="../js/login.js"></script>
</body>
</html>
