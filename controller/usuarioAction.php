<?php

    require_once dirname(__DIR__, 1) . "/service/usuarioBusiness.php";
    require_once dirname(__DIR__, 1) . "/utils/Utils.php";

    $response = [];                                 //<- Respuesta a enviar al cliente
    $method = $_SERVER["REQUEST_METHOD"];           //<- Método de la solicitud
    $usuarioBusiness = new UsuarioBusiness();       //<- Lógica de negocio de Usuario

    if ($method === "POST") {
        // Acción a realizar en el controlador
        $accion = $_POST['accion'] ?? "";
        if (empty($accion)) {
            Utils::enviarRespuesta(400, false, "No se ha especificado una acción.");
        }

        // Datos recibidos en la solicitud (Form)
        $id         = isset($_POST['id'])           ? intval($_POST['id'])  : -1;
        $rolID      = isset($_POST['rol'])          ? intval($_POST['rol']) : -1;
        $nombre     = isset($_POST['nombre'])       ? $_POST['nombre']      : "";
        $apellido1  = isset($_POST['apellido1'])    ? $_POST['apellido1']   : "";
        $apellido2  = isset($_POST['apellido2'])    ? $_POST['apellido2']   : "";
        $correo     = isset($_POST['correo'])       ? $_POST['correo']      : "";
        $password   = isset($_POST['password'])     ? $_POST['password']    : "";

        // Crea el objeto Usuario con los datos recibidos
        $usuario = new Usuario($id, $nombre, $apellido1, $apellido2, $correo, $password, new RolUsuario($rolID));
        
        // Si los datos son válidos se realiza acción correspondiente
        $check = $usuarioBusiness->validarUsuario($usuario, $accion !== 'eliminar', $accion === 'insertar');
        if ($check['is_valid']) {
            switch ($accion) {
                case 'insertar':
                    $response = $usuarioBusiness->insertTBUsuario($usuario);
                    break;
                case 'actualizar':
                    if (empty($password)) {
                        $result = $usuarioBusiness->getUsuarioByID($id, false);
                        if (!$result['success']) {
                            $response = $result;
                            break;
                        }
                        $usuario->setUsuarioPassword($result['usuario']->getUsuarioPassword());
                    }
                    $response = $usuarioBusiness->updateTBUsuario($usuario);
                    break;
                case 'eliminar':
                    $response = $usuarioBusiness->deleteTBUsuario($id);
                    break;
                default:
                    Utils::enviarRespuesta(400, false, "Acción no válida.");
                    break;
            }
        } else {
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

        // Realizar la acción solicitada
        switch ($accion) {
            case 'all':
                $response = $usuarioBusiness->getAllTBUsuario($onlyActive, $deleted);
                break;
            case 'id':
                $id = intval($_GET['id'] ?? -1);
                $response = $usuarioBusiness->getUsuarioByID($id, $onlyActive, $deleted);
                break;
            default:
                // Obtener parámetros de la solicitud GET
                $search = isset($_GET['search']) ? $_GET['search']       : null;
                $page   = isset($_GET['page'])   ? intval($_GET['page']) : 1;
                $size   = isset($_GET['size'])   ? intval($_GET['size']) : 5;
                $sort   = isset($_GET['sort'])   ? $_GET['sort']         : null;

                // Validar los parámetros
                if ($page < 1) $page = 1;
                if ($size < 1) $size = 5;

                $response = $usuarioBusiness->getPaginatedUsuarios($search, $page, $size, $sort, $onlyActive, $deleted);
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