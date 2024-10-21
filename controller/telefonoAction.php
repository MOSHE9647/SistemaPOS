<?php

    require_once dirname(__DIR__, 1) . '/service/telefonoBusiness.php';
    require_once dirname(__DIR__, 1) . '/domain/Telefono.php';
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';

    $response = [];                                 //<- Respuesta a enviar al cliente
    $method = $_SERVER["REQUEST_METHOD"];           //<- Método de la solicitud
    $telefonoBusiness = new TelefonoBusiness();     //<- Lógica de negocio de Teléfono

    if ($method == "POST") {
        // Acción a realizar en el controlador
        $accion = $_POST['accion'] ?? "";
        if (empty($accion)) {
            Utils::enviarRespuesta(400, false, "No se ha especificado una acción.");
        }

        // Datos recibidos en la solicitud (Form)
        $id         = isset($_POST['id'])           ? intval($_POST['id'])  : -1;
        $tipo       = isset($_POST['tipo'])         ? $_POST['tipo']        : "";
        $codigo     = isset($_POST['codigo'])       ? $_POST['codigo']      : "";
        $numero     = isset($_POST['numero'])       ? $_POST['numero']      : "";
        $extension  = isset($_POST['extension'])    ? $_POST['extension']   : "";

        // Crea y verifica que los datos del telefono sean correctos
        $telefono = new Telefono($id, $tipo, $codigo, $numero, $extension);
        $check = $telefonoBusiness->validarTelefono($telefono, $accion != 'eliminar', $accion == 'insertar');

        // Si los datos son válidos se realiza acción correspondiente
        if ($check['is_valid']) {
            switch ($accion) {
                case 'insertar':
                    // Inserta el telefono en la base de datos
                    $response = $telefonoBusiness->insertTBTelefono($telefono);
                    break;
                case 'actualizar':
                    // Actualiza la info del telefono en la base de datos
                    $response = $telefonoBusiness->updateTBTelefono($telefono);
                    break;
                case 'eliminar':
                    // Elimina al telefono de la base de datos
                    $response = $telefonoBusiness->deleteTBTelefono($id);
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

    else if ($method == "GET") {
        // Parámetros de la solicitud
        $accion     = isset($_GET['accion'])    ? $_GET['accion']           : "";
        $deleted    = isset($_GET['deleted'])   ? boolval($_GET['deleted']) : false;
        $onlyActive = isset($_GET['filter'])    ? boolval($_GET['filter'])  : true;

        // Realizar la acción solicitada
        switch ($accion) {
            case 'exists':
                // Obtener parámetros de la solicitud GET
                $telefono    = isset($_GET['telefono'])   ? json_decode($_GET['telefono'], true) : null;
                $insert      = isset($_GET['insert'])     ? boolval($_GET['insert'])             : false;
                $update      = isset($_GET['update'])     ? boolval($_GET['update'])             : false;

                // Generar el teléfono a partir de los datos recibidos
                $telefono = new Telefono($telefono['ID'], $telefono['Tipo'], $telefono['CodigoPais'], $telefono['Numero'], $telefono['Extension']);
                $response = $telefonoBusiness->existeTelefono($telefono, $update, $insert);
                break;
            case 'all':
                $response = $telefonoBusiness->getAllTBTelefono($onlyActive, $deleted);
                break;
            case 'id':
                $telefonoID = intval($_GET['id'] ?? -1);
                $response = $telefonoBusiness->getTelefonoByID($telefonoID, $onlyActive, $deleted);
                break;
            default:
                // Obtener parámetros de la solicitud GET
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
                $sort = isset($_GET['sort']) ? $_GET['sort']         : null;

                // Validar los parámetros
                if ($page < 1) $page = 1;
                if ($size < 1) $size = 5;

                // Obtiene la lista (paginada) de telefonos
                $response = $telefonoBusiness->getPaginatedTelefonos($page, $size, $sort, $onlyActive, $deleted);
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