<?php

    require_once dirname(__DIR__, 1) . '/service/impuestoBusiness.php';
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';

    $response = [];                                //<- Respuesta a enviar al cliente
    $method = $_SERVER["REQUEST_METHOD"];          //<- Método de la solicitud
    $impuestoBusiness = new ImpuestoBusiness();    //<- Lógica de negocio de Impuesto

    if ($method === "POST") {
        // Acción a realizar en el controlador
        $accion = $_POST['accion'] ?? "";
        if (empty($accion)) {
            Utils::enviarRespuesta(400, false, "No se ha especificado una acción.");
        }

        // Datos recibidos en la solicitud (Form)
        $id =           isset($_POST['id'])             ? intval($_POST['id'])          : -1;
        $nombre =       isset($_POST['nombre'])         ? $_POST['nombre']              : "";
        $valor =        isset($_POST['valor'])          ? floatval($_POST['valor'])     : 0.0;
        $descripcion =  isset($_POST['descripcion'])    ? $_POST['descripcion']         : "";
        $fechaInicio =  isset($_POST['fechaInicio'])    ? $_POST['fechaInicio']         : "";
        $fechaFin =     isset($_POST['fechaFin'])       ? $_POST['fechaFin']            : "";

        // Crea y verifica que los datos del impuesto sean correctos
        $impuesto = new Impuesto($id, $nombre, $valor, $descripcion, $fechaInicio, $fechaFin);
        $check = $impuestoBusiness->validarImpuesto($impuesto, $accion !== 'eliminar', $accion === 'insertar');

        // Si los datos son válidos se realiza acción correspondiente
        if ($check['is_valid']) {
            switch ($accion) {
                case 'insertar':
                    // Inserta el impuesto en la base de datos
                    $response = $impuestoBusiness->insertTBImpuesto($impuesto);
                    break;
                case 'actualizar':
                    // Actualiza la info del impuesto en la base de datos
                    $response = $impuestoBusiness->updateTBImpuesto($impuesto);
                    break;
                case 'eliminar':
                    // Elimina el impuesto de la base de datos
                    $response = $impuestoBusiness->deleteTBImpuesto($id);
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

        // Realizar acción solicitada
        switch ($accion) {
            case 'all':
                $response = $impuestoBusiness->getAllTBImpuesto($onlyActive, $deleted);
                break;
            case 'id':
                $impuestoID = intval($_GET['id'] ?? -1);
                $response = $impuestoBusiness->getImpuestoByID($impuestoID, $onlyActive, $deleted);
                break;
            default:
                // Obtener parámetros de la solicitud GET
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
                $sort = isset($_GET['sort']) ? $_GET['sort']         : null;

                // Validar los parámetros
                if ($page < 1) $page = 1;
                if ($size < 1) $size = 5;

                $response = $impuestoBusiness->getPaginatedImpuestos($page, $size, $sort, $onlyActive, $deleted);
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