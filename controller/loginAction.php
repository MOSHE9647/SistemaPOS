<?php
    session_start();
    require_once dirname(__DIR__, 1) . '/service/usuarioBusiness.php';
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';

    // Verifica si el formulario ha sido enviado
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Obtiene los datos del formulario
        $email    = isset($_POST['email'])      ? $_POST['email']       : null;
        $password = isset($_POST['password'])   ? $_POST['password']    : null;

        // Verifica si el email y la contraseña no están vacíos
        if (!$email || !$password) {
            Utils::enviarRespuesta(400, false, 'Email o contraseña no proporcionados.');
        }

        // Crea una instancia de UsuarioBusiness y autentica al usuario
        $usuarioBusiness = new UsuarioBusiness();
        $result = $usuarioBusiness->autenticarUsuario($email, $password);

        // Si el usuario no existe, muestra un mensaje de error (código 401 para credenciales incorrectas)
        if (!$result['success']) {
            Utils::enviarRespuesta(401, false, $result['message']);
        }

        // Si el usuario existe, verifica si está activo
        $usuario = $result['usuario'];
        if (!$usuario->getUsuarioEstado()) {
            Utils::enviarRespuesta(403, false, 'El usuario no está activo. Contacte al administrador.');
        }

        // Si las credenciales son válidas, obtiene los datos del usuario e inicia sesión
        session_regenerate_id(true); // Regenera el ID de sesión para prevenir ataques de fijación de sesión
        $_SESSION[SESSION_AUTHENTICATED_USER] = $usuario; // Almacena el usuario autenticado en la sesión
        $_SESSION[SESSION_AUTHENTICATED] = true; // Almacena el estado de autenticación en la sesión

        // Crea la respuesta de éxito
        $response = ['success' => true];

        // Redirige a la URL de origen si existe
        if (isset($_SESSION[SESSION_ORIGIN_URL])) {
            $response['redirect'] = $_SESSION[SESSION_ORIGIN_URL]; // Redirige a la URL de origen
            unset($_SESSION[SESSION_ORIGIN_URL]); // Elimina la URL de origen para evitar redirecciones futuras no deseadas
        } else {
            // Si no existe una URL de origen, redirige al index
            $response['redirect'] = '../../index.php';
        }

        http_response_code(200); // Código 200: OK (sesión iniciada correctamente)
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    } else {
        // Si no se envía una solicitud POST válida, retorna código 405: Solicitud incorrecta
        Utils::enviarRespuesta(405, false, "Método no permitido (" . $_SERVER["REQUEST_METHOD"] . ").");
    }
?>