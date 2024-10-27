<?php

    require_once dirname(__DIR__, 1) . '/service/ventaDetalleBusiness.php';
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';

    $response = [];                                         // Respuesta a enviar al cliente
    $method = $_SERVER["REQUEST_METHOD"];                   // Método de la solicitud
    $ventaDetalleBusiness = new VentaDetalleBusiness();     // Lógica de negocio de VentaDetalle

    if ($method == "POST") {
        // Acción a realizar en el controlador
        $accion = $_POST['accion'] ?? "";
        if (empty($accion)) {
            Utils::enviarRespuesta(400, false, "No se ha especificado una acción.");
        }

        // Datos de VentaDetalle recibidos en la solicitud
        $ventaDetalleID   = isset($_POST['id'])              ? intval($_POST['id'])              : -1;
        $ventaID          = isset($_POST['venta_id'])        ? intval($_POST['venta_id'])        : -1;
        $precio           = isset($_POST['precio'])          ? floatval($_POST['precio'])        : 0.0;
        $cantidad         = isset($_POST['cantidad'])        ? intval($_POST['cantidad'])        : 0;
        $estado           = isset($_POST['estado'])          ? boolval($_POST['estado'])         : true;

        // Crear un objeto VentaDetalle con los datos recibidos
        $ventaDetalle = new VentaDetalle($ventaDetalleID, $precio, $cantidad, new Venta($ventaID), $estado);

        // Validación y acciones
        $check = $ventaDetalleBusiness->validarVentaDetalle($ventaDetalle, $accion != 'eliminar', $accion == 'insertar');
        if ($check['is_valid']) {
            switch ($accion) {
                case 'insertar':
                    $response = $ventaDetalleBusiness->insertVentaDetalle($ventaDetalle);
                    break;
                case 'actualizar':
                    $response = $ventaDetalleBusiness->updateVentaDetalle($ventaDetalle);
                    break;
                case 'eliminar':
                    $response = $ventaDetalleBusiness->deleteVentaDetalle($ventaDetalleID);
                    break;
                default:
                    Utils::enviarRespuesta(400, false, "Acción no válida.");
            }
        } else {
            Utils::enviarRespuesta(400, false, $check['message']);
        }

        // Enviar respuesta al cliente
        http_response_code($response['success'] ? 200 : 400);
        header("Content-Type: application/json");
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
            case 'all':
                // Obtener todos los detalles de venta
                $response = $ventaDetalleBusiness->getAllVentaDetalles($onlyActive, $deleted);
                break;
            case 'id':
                // Obtener un detalle de venta por su ID
                $ventaDetalleID = intval($_GET['id'] ?? -1);
                $response = $ventaDetalleBusiness->getVentaDetalleByID($ventaDetalleID, $onlyActive, $deleted);
                break;
            default:
                // Parámetros de paginación
                $search = isset($_GET['search']) ? $_GET['search']          : null;
                $page   = isset($_GET['page'])   ? intval($_GET['page'])    : 1;
                $size   = isset($_GET['size'])   ? intval($_GET['size'])    : 5;
                $sort   = isset($_GET['sort'])   ? $_GET['sort']            : null;

                // Validar los parámetros
                if ($page < 1) $page = 1;
                if ($size < 1) $size = 5;

                // Obtener los detalles de venta paginados
                $response = $ventaDetalleBusiness->getPaginatedVentaDetalles($search, $page, $size, $sort, $onlyActive, $deleted);
                break;
        }

        // Enviar respuesta al cliente
        http_response_code($response['success'] ? 200 : 400);
        header("Content-Type: application/json");
        echo json_encode($response);
        exit();
    } 

    else {
        // Enviar respuesta de método no permitido
        Utils::enviarRespuesta(405, false, "Método no permitido ($method).");
    }

?>
