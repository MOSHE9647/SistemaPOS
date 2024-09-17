<?php

    require_once __DIR__ . '/../service/usuarioTelefonoBusiness.php';

    $response = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Acción que se va a realizar
        $accion = isset($_POST['accion']) ? $_POST['accion'] : "";

        // Datos recibidos en la solicitud (Form)
        $usuarioID = isset($_POST['usuario']) ? intval($_POST['usuario']) : null;
        $telefonoID = isset($_POST['telefono']) ? intval($_POST['telefono']) : null;

        // Se crea el Service para las operaciones
        $usuarioTelefonoBusiness = new UsuarioTelefonoBusiness();

        // Crea y verifica que los ID del Usuario y del Telefono sean correctos
        $check = $usuarioTelefonoBusiness->validarUsuarioTelefono($usuarioID, $telefonoID);

        // Si los datos son válidos se realiza acción correspondiente
        if ($check['is_valid']) {
            switch ($accion) {
                case 'agregar':
                    // Agrega un teléfono a un usuario en la base de datos.
                    $response = $usuarioTelefonoBusiness->addTelefonoToUsuario($usuarioID, $telefonoID);
                    break;
                case 'eliminar':
                    // Elimina un teléfono de un usuario en la base de datos.
                    $response = $usuarioTelefonoBusiness->removeTelefonoFromUsuario($usuarioID, $telefonoID);
                    break;
                default:
                    // Error en caso de que la accion no sea válida
                    $response['success'] = false;
                    $response['message'] = "Acción no válida.";
                    break;
            }
        } else {
            // Si los datos no son validos, se devuelve un mensaje de error
            $response['success'] = $check['is_valid'];
            $response['message'] = $check['message'];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $accion = isset($_GET['accion']) ? $_GET['accion'] : "";
        $deleted = isset($_GET['deleted']) ? boolval($_GET['deleted']) : false;
        $onlyActiveOrInactive = isset($_GET['filter']) ? boolval($_GET['filter']) : true;
        $usuarioID = isset($_GET['usuario']) ? intval($_GET['usuario']) : null;

        // Se crea el Service para las operaciones
        $usuarioTelefonoBusiness = new UsuarioTelefonoBusiness();

        // Crea y verifica que el ID del Usuario sea correcto
        $check = $usuarioTelefonoBusiness->validarUsuarioTelefono($usuarioID, null, false);

        if ($check['is_valid']) {
            switch ($accion) {
                case 'todo':
                    // Obtiene los teléfonos de un usuario en la base de datos.
                    $response = $usuarioTelefonoBusiness->getTelefonosByUsuario($usuarioID, true);
                    break;
                default:
                    // Obtener parámetros de la solicitud GET
                    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                    $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
                    $sort = isset($_GET['sort']) ? $_GET['sort'] : null;
    
                    // Validar los parámetros
                    if ($page < 1) $page = 1;
                    if ($size < 1) $size = 5;
    
                    // Obtiene los teléfonos de un usuario en la base de datos.
                    $response = $usuarioTelefonoBusiness->getPaginatedTelefonosByUsuario($usuarioID, $page, $size, $sort, $onlyActiveOrInactive, $deleted);
                    break;
            }
        } else {
            // Si los datos no son validos, se devuelve un mensaje de error
            $response['success'] = $check['is_valid'];
            $response['message'] = $check['message'];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

?>