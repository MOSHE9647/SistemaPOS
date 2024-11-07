<?php

    require_once dirname(__DIR__, 1) . '/service/ventaDetalleBusiness.php';
    require_once dirname(__DIR__, 1) . '/domain/VentaDetalle.php';
    require_once dirname(__DIR__, 1) . '/domain/CodigoBarras.php';
    require_once dirname(__DIR__, 1) . '/domain/Subcategoria.php';
    require_once dirname(__DIR__, 1) . '/domain/Presentacion.php';
    require_once dirname(__DIR__, 1) . '/domain/Categoria.php';
    require_once dirname(__DIR__, 1) . '/domain/Producto.php';
    require_once dirname(__DIR__, 1) . '/domain/Marca.php';
    require_once dirname(__DIR__, 1) . '/domain/Venta.php';
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
        $listaDetalles = isset($_POST['detalles']) ? json_decode($_POST['detalles']) : null;
        if (empty($listaDetalles)) {
            Utils::enviarRespuesta(400, false, "No se han recibido los datos de la venta.");
        }

        // Crear un arreglo de detalles de venta
        $detalles = [];
        foreach ($listaDetalles as $detalle) {
            // Crear un objeto VentaDetalle con los datos recibidos
            $ventaDetalle = Utils::convertToObject($detalle, VentaDetalle::class);

            // $check = $ventaDetalleBusiness->validarVentaDetalle($ventaDetalle, $accion != 'eliminar', $accion == 'insertar');
            // if (!$check['is_valid']) Utils::enviarRespuesta(400, false, $check['message']);

            array_push($detalles, $ventaDetalle);
        }

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
