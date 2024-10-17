<?php
    session_start();
    require_once dirname(__DIR__, 1) . '/service/usuarioBusiness.php';

    // Función para manejar respuestas de error
    function enviarRespuesta($codigo, $mensaje, $exito = false) {
        http_response_code($codigo);
        $response = [
            'success' => $exito,
            'message' => $mensaje
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Verifica si el formulario ha sido enviado
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Obtiene los datos del formulario
        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;

        if (!$email || !$password) {
            enviarRespuesta(400, 'Email o contraseña no proporcionados.');
        }

        // Crea una instancia de UsuarioBusiness
        $usuarioBusiness = new UsuarioBusiness();

        // Autentica al usuario
        $result = $usuarioBusiness->autenticarUsuario($email, $password);

        // Si el usuario no existe, muestra un mensaje de error (código 401 para credenciales incorrectas)
        if (!$result['success']) {
            enviarRespuesta(401, $result['message']);
        }

        // Si el usuario existe, verifica si está activo
        $usuario = $result['usuario'];
        if (!$usuario->getUsuarioEstado()) {
            enviarRespuesta(403, 'El usuario no está activo. Contacte al administrador.');
        }

        // Si las credenciales son válidas, obtiene los datos del usuario e inicia sesión
        session_regenerate_id(true); // Regenera el ID de sesión para prevenir ataques de fijación de sesión
        $_SESSION[SESSION_AUTHENTICATED_USER] = $usuario;
        $_SESSION[SESSION_AUTHENTICATED] = true;

        // Crea la respuesta de éxito
        $response = ['success' => true];

        // Redirige a la URL de origen si existe
        if (isset($_SESSION[SESSION_ORIGIN_URL])) {
            $response['redirect'] = $_SESSION[SESSION_ORIGIN_URL];
            unset($_SESSION[SESSION_ORIGIN_URL]); // Elimina la URL de origen para evitar redirecciones futuras no deseadas
        } else {
            $response['redirect'] = '../../index.php';
        }

        http_response_code(200); // Código 200: OK (sesión iniciada correctamente)
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } 
    
    else {
        // Si no se envía una solicitud POST válida, retorna código 405: Solicitud incorrecta
        enviarRespuesta(405, "Método no permitido (" . $_SERVER["REQUEST_METHOD"] . ").");
    }
?>