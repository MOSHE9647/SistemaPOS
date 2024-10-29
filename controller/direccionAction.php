<?php

    require_once dirname(__DIR__, 1) . '/service/direccionBusiness.php';
    require_once dirname(__DIR__, 1) . '/domain/Direccion.php';
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';
    
    $response = [];                                 //<- Respuesta a enviar al cliente
    $method = $_SERVER["REQUEST_METHOD"];           //<- Método de la solicitud
    $direccionBusiness = new DireccionBusiness();   //<- Lógica de negocio de Dirección

    if ($method == "POST") {
        // Acción a realizar en el controlador
        $accion = $_POST['accion'] ?? "";
        if (empty($accion)) {
            Utils::enviarRespuesta(400, false, "No se ha especificado una acción.");
        }

        // Datos recibidos en la solicitud
        $id         = isset($_POST['id'])           ? intval($_POST['id'])          :  -1;
        $provincia  = isset($_POST['provincia'])    ? $_POST['provincia']           :  "";
        $canton     = isset($_POST['canton'])       ? $_POST['canton']              :  "";
        $distrito   = isset($_POST['distrito'])     ? $_POST['distrito']            :  "";
        $barrio     = isset($_POST['barrio'])       ? $_POST['barrio']              :  "";
        $sennas     = isset($_POST['sennas'])       ? $_POST['sennas']              :  "";
        $distancia  = isset($_POST['distancia'])    ? floatval($_POST['distancia']) : 0.0;

        // Crea y verifica que los datos de la direccion sean correctos
        $direccion = new Direccion($id, $provincia, $canton, $distrito, $barrio, $sennas, $distancia);
        $check = $direccionBusiness->validarDireccion($direccion, $accion != 'eliminar', $accion == 'insertar');

        // Si los datos son válidos se realiza acción correspondiente
        if ($check['is_valid']) {
            switch ($accion) {
                case 'insertar':
                    // Inserta la direccion en la base de datos
                    $response = $direccionBusiness->insertTBDireccion($direccion);
                    break;
                case 'actualizar':
                    // Actualiza la info de la direccion en la base de datos
                    $response = $direccionBusiness->updateTBDireccion($direccion);
                    break;
                case 'eliminar':
                    // Elimina la direccion de la base de datos
                    $response = $direccionBusiness->deleteTBDireccion($id);
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
                $direccion    = isset($_GET['direccion'])   ? json_decode($_GET['direccion'], true) : null;
                $insert       = isset($_GET['insert'])      ? boolval($_GET['insert'])              : false;
                $update       = isset($_GET['update'])      ? boolval($_GET['update'])              : false;

                // Generar la dirección con los datos recibidos en el arreglo
                $direccion = new Direccion(
                    $direccion['ID'] ?? -1,
                    $direccion['Provincia'] ?? "",
                    $direccion['Canton'] ?? "",
                    $direccion['Distrito'] ?? "",
                    $direccion['Barrio'] ?? "",
                    $direccion['Sennas'] ?? "",
                    $direccion['Distancia'] ?? 0.0
                );

                $response = $direccionBusiness->existeDireccion($direccion, $update, $insert);
                break;
            case 'all':
                $response = $direccionBusiness->getAllTBDireccion($onlyActive, $deleted);
                break;
            case 'id':
                $direccionID = intval($_GET['id'] ?? -1);
                $response = $direccionBusiness->getDireccionByID($direccionID, $onlyActive, $deleted);
                break;
            default:
                // Obtener parámetros de la solicitud GET
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
                $sort = isset($_GET['sort']) ? $_GET['sort']         : null;

                // Validar los parámetros
                if ($page < 1) $page = 1;
                if ($size < 1) $size = 5;

                // Obtiene la lista (paginada) de direcciones
                $response = $direccionBusiness->getPaginatedDirecciones($page, $size, $sort, $onlyActive, $deleted);
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