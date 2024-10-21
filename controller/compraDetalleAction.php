<?php

require_once dirname(__DIR__, 1) . '/service/compraDetalleBusiness.php';
require_once dirname(__DIR__, 1) . '/utils/Utils.php';

$response = [];                                         // <- Respuesta a enviar al cliente
$method = $_SERVER["REQUEST_METHOD"];                   // <- Método de la solicitud
$compraDetalleBusiness = new CompraDetalleBusiness();   // <- Lógica de negocio de CompraDetalle

if ($method == "POST") {
    // Acción a realizar en el controlador
    $accion = $_POST['accion'] ?? "";
    if (empty($accion)) {
        Utils::enviarRespuesta(400, false, "No se ha especificado una acción.");
    }

    // Datos de CompraDetalle recibidos en la solicitud
    $compraDetalleID           = isset($_POST['id'])                  ? intval($_POST['id'])                : -1;
    $compraDetalleCompra       = isset($_POST['compra'])              ? intval($_POST['compra'])            : -1;
    $compraDetalleProducto     = isset($_POST['producto'])            ? intval($_POST['producto'])          : -1;
    $compraDetalleFechaCreacion = isset($_POST['fechaCreacion'])      ? $_POST['fechaCreacion']            : "";
    $compraDetalleFechaModificacion = isset($_POST['fechaModificacion']) ? $_POST['fechaModificacion']    : "";
    $compraDetalleEstado       = isset($_POST['estado'])              ? intval($_POST['estado'])            : 1;

    // Crear un objeto CompraDetalle con los datos recibidos
    $compraDetalle = new CompraDetalle(
        $compraDetalleID, $compraDetalleCompraID, $compraDetalleProductoID,
        $compraDetalleFechaCreacion, $compraDetalleFechaModificacion, $compraDetalleEstado
    );

    // Realizar la acción solicitada si los datos son válidos
    $check = $compraDetalleBusiness->validarCompraDetalle($compraDetalle, $accion != 'eliminar', $accion == 'insertar');
    if ($check['is_valid']) {
        switch ($accion) {
            case 'insertar':
                $response = $compraDetalleBusiness->insertCompraDetalle($compraDetalle);
                break;
            case 'actualizar':
                $response = $compraDetalleBusiness->updateCompraDetalle($compraDetalle);
                break;
            case 'eliminar':
                $response = $compraDetalleBusiness->deleteCompraDetalle($compraDetalleID);
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
            // Obtener todos los detalles de compra
            $response = $compraDetalleBusiness->getAllCompraDetalle($onlyActive, $deleted);
            break;
        case 'id':
            // Obtener un detalle de compra por su ID
            $compraDetalleID = intval($_GET['id'] ?? -1);
            $response = $compraDetalleBusiness->getCompraDetalleByID($compraDetalleID, $onlyActive, $deleted);
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

            // Obtener los detalles de compra paginados
            $response = $compraDetalleBusiness->getPaginatedCompraDetalles($search, $page, $size, $sort, $onlyActive, $deleted);
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
