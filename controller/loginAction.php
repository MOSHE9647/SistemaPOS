<?php
    session_start();
    require_once __DIR__ . '/../service/usuarioBusiness.php';

    // Verifica si el formulario ha sido enviado
    $response = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Crea una instancia de UsuarioBusiness
        $usuarioBusiness = new UsuarioBusiness();

        // Autentica al usuario
        $result = $usuarioBusiness->autenticarUsuario($email, $password);
        
        // Si el usuario no existe, muestra un mensaje de error (código 401 para credenciales incorrectas)
        if (!$result['success']) {
            http_response_code(401); // Código 401: No autorizado
            $response['success'] = false;
            $response['message'] = $result['message'];
            
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        
        // Si el usuario existe, verifica si está activo
        $usuario = $result['usuario'];
        $isActive = $usuario->getUsuarioEstado();
        if (!$isActive) {
            http_response_code(403); // Código 403: Prohibido (usuario inactivo)
            $response['success'] = false;
            $response['message'] = 'El usuario no está activo. Contacte al administrador.';
            
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }

        // Si las credenciales son válidas, obtiene los datos del usuario e inicia sesión
        session_regenerate_id(true); // Regenera el ID de sesión para prevenir ataques de fijación de sesión
        $_SESSION[SESSION_AUTHENTICATED_USER] = $usuario;
        $_SESSION[SESSION_AUTHENTICATED] = true;

        // Crea la respuesta de éxito
        http_response_code(200); // Código 200: OK (sesión iniciada correctamente)
        $response['success'] = true;
        $response['message'] = 'Sesión iniciada correctamente. Redirigiendo...';

        // Redirige a la URL de origen si existe
        if (isset($_SESSION[SESSION_ORIGIN_URL])) {
            // Obtiene la URL de origen y la elimina de la sesión
            $url_origen = $_SESSION[SESSION_ORIGIN_URL];
            unset($_SESSION[SESSION_ORIGIN_URL]); // Elimina la URL de origen para evitar redirecciones futuras no deseadas
            
            // Agrega la URL de origen a la respuesta
            $response['redirect'] = $url_origen;
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            // Redirige a index.php si no hay URL de origen
            $response['redirect'] = '../../index.php';
            header('Content-Type: application/json');
            echo json_encode($response);
        }
        exit;
    } else {
        // Si no se envía una solicitud POST válida, retorna código 400: Solicitud incorrecta
        http_response_code(400); // Código 400: Solicitud incorrecta
        $response['success'] = false;
        $response['message'] = 'Método de solicitud no permitido.';

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
?>
