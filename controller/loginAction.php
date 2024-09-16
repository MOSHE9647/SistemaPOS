<?php
    session_start();
    require_once __DIR__ . '/../service/usuarioBusiness.php'; // Incluye la clase UsuarioBusiness

    // Verifica si el formulario ha sido enviado
    $response = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Crea una instancia de UsuarioBusiness
        $usuarioBusiness = new UsuarioBusiness();

        // Autentica al usuario
        $usuario = $usuarioBusiness->autenticarUsuario($email, $password);
        
        // Si el usuario no existe, muestra un mensaje de error
        if ($usuario === null) {
            $response['success'] = false;
            $response['message'] = 'Correo o contraseña incorrectos.';
            
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        
        // Si el usuario existe, verifica si está activo
        $isActive = $usuario->getUsuarioEstado();
        if (!$isActive) {
            $response['success'] = false;
            $response['message'] = 'El usuario no está activo. Contacte al administrador.';
            
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }

        // Si las credenciales son válidas, obtiene los datos del usuario e inicia sesión
        $_SESSION[SESSION_USER_ID] = $usuario->getUsuarioID();
        $_SESSION[SESSION_USER_NAME] = $usuario->getUsuarioNombre();
        $_SESSION[SESSION_USER_LAST_NAME_1] = $usuario->getUsuarioApellido1();
        $_SESSION[SESSION_USER_LAST_NAME_2] = $usuario->getUsuarioApellido2();
        $_SESSION[SESSION_USER_EMAIL] = $usuario->getUsuarioEmail();
        $_SESSION[SESSION_USER_ROLE] = $usuario->getUsuarioRolID();
        $_SESSION[SESSION_USER_REGISTRATION_DATE] = $usuario->getUsuarioFechaCreacion();
        $_SESSION[SESSION_AUTHENTICATED] = true;

        // Redirige a la URL de origen si existe
        if (isset($_SESSION[SESSION_ORIGIN_URL])) {
            $url_origen = $_SESSION[SESSION_ORIGIN_URL];
            unset($_SESSION[SESSION_ORIGIN_URL]); // Elimina la URL de origen para evitar redirecciones futuras no deseadas
            header("Location: $url_origen");
        } else {
            // Redirige a index.php si no hay URL de origen
            // header('Location: index.php');
            $response['success'] = true;
            $response['message'] = 'Inicio de sesión exitoso.';
            $response['usuario'] = [
                'id' => $_SESSION[SESSION_USER_ID],
                'nombre' => $_SESSION[SESSION_USER_NAME],
                'apellido1' => $_SESSION[SESSION_USER_LAST_NAME_1],
                'apellido2' => $_SESSION[SESSION_USER_LAST_NAME_2],
                'correo' => $_SESSION[SESSION_USER_EMAIL],
                'rol' => $_SESSION[SESSION_USER_ROLE],
                'fecha_creacion' => $_SESSION[SESSION_USER_REGISTRATION_DATE]
            ];

            header('Content-Type: application/json');
            echo json_encode($response);
        }
        exit;
    }
?>