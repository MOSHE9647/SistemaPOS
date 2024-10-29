<?php

    require_once dirname(__DIR__, 1) . '/service/rolUsuarioBusiness.php';
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';

    $response = [];                         //<- Respuesta a enviar al cliente
    $method = $_SERVER["REQUEST_METHOD"];   //<- Método de la solicitud
    $rolBusiness = new RolBusiness();       //<- Lógica de negocio de Rol
    
    if ($method === "POST") {
        // Acción a realizar en el controlador
        $accion = $_POST['accion'] ?? "";
        if (empty($accion)) {
            Utils::enviarRespuesta(400, false, "No se ha especificado una acción.");
        }

        // Datos recibidos en la solicitud (Form)
        $id =           isset($_POST['id'])             ? intval($_POST['id'])          : -1;
        $nombre =       isset($_POST['nombre'])         ? $_POST['nombre']              : "";
        $descripcion =  isset($_POST['descripcion'])    ? $_POST['descripcion']         : "";

        // Crea y verifica que los datos del rol sean correctos
        $rol = new RolUsuario($id, $nombre, $descripcion);
        $check = $rolBusiness->validarRol($rol, $accion !== 'eliminar', $accion === 'insertar');

        // Si los datos son válidos se realiza acción correspondiente
        if ($check['is_valid']) {
            switch ($accion) {
                case 'insertar':
                    // Inserta el rol en la base de datos
                    $response = $rolBusiness->insertTBRolUsuario($rol);
                    break;
                case 'actualizar':
                    // Actualiza la info del rol en la base de datos
                    $response = $rolBusiness->updateTBRolUsuario($rol);
                    break;
                case 'eliminar':
                    // Elimina el rol de la base de datos
                    $response = $rolBusiness->deleteTBRolUsuario($id);
                    break;
                default:
                    // Error en caso de que la accion no sea válida
                    Utils::enviarRespuesta(400, false, "Acción no válida.");
                    break;
            }
        } else {
            // Si los datos no son validos, se devuelve un mensaje de error
            Utils::enviarRespuesta(400, false, $check['message']);
        }
        
        // Enviar respuesta al cliente
        http_response_code($response['success'] ? 200 : 400);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    else if ($method === "GET") {
        // Parámetros de la solicitud
        $accion     = isset($_GET['accion'])    ? $_GET['accion']           : "";
        $deleted    = isset($_GET['deleted'])   ? boolval($_GET['deleted']) : false;
        $onlyActive = isset($_GET['filter'])    ? boolval($_GET['filter'])  : true;

        // Realizar acción correspondiente
        switch ($accion) {
            case 'all':
                $response = $rolBusiness->getAllTBRolUsuario($onlyActive, $deleted);
                break;
            case 'id':
                $rolID = intval($_GET['id'] ?? -1);
                $response = $rolBusiness->getRolUsuarioByID($rolID, $onlyActive, $deleted);
                break;
            default:
                // Obtener parámetros de la solicitud GET
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
                $sort = isset($_GET['sort']) ? $_GET['sort']         : null;

                // Validar los parámetros
                if ($page < 1) $page = 1;
                if ($size < 1) $size = 5;

                $response = $rolBusiness->getPaginatedRoles($page, $size, $sort, $onlyActive, $deleted);
                break;
        }

        // Enviar respuesta al cliente
        http_response_code($response['success'] ? 200 : 400);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    else {
        // Enviar respuesta de método no permitido
        Utils::enviarRespuesta(405, false, "Método no permitido ($method).");
    }

?>