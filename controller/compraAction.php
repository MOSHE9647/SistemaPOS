<?php

require_once __DIR__ . '/../service/compraBusiness.php';
require_once dirname(__DIR__, 1) . '/utils/Utils.php';

$response = [];
$method = $_SERVER["REQUEST_METHOD"];                   //<- Método de la solicitud
$compraBusiness = new CompraBusiness();  // Lógica de negocio de Compra

if ($method == "POST") {
    // Acción a realizar en el controlador
    $accion = $_POST['accion'] ?? "";
    if (empty($accion)) {
        Utils::enviarRespuesta(400, false, "No se ha especificado una acción.");
    }

    // Datos recibidos en la solicitud (Form)
    $id                     = isset($_POST['id'])                    ? intval($_POST['id'])               : 0;
    $compranumerofactura     = isset($_POST['numerofactura'])         ? $_POST['numerofactura']            : "";
    $compramontobruto        = isset($_POST['montobruto'])            ? floatval($_POST['montobruto'])     : 0;
    $compramontoneto         = isset($_POST['montoneto'])             ? floatval($_POST['montoneto'])      : 0;
    $compratipopago          = isset($_POST['tipopago'])              ? $_POST['tipopago']                 : "";
    $proveedorid             = isset($_POST['proveedornombre'])       ? $_POST['proveedornombre']           : 0;
    $comprafechacreacion     = isset($_POST['fechacreacion'])         ? $_POST['fechacreacion']            : '';
    $comprafechamodificacion = isset($_POST['fechamodificacion'])     ? $_POST['fechamodificacion']        : '';

    // Crea y verifica que los datos de la compra sean correctos
    $compra = new Compra($id, $compranumerofactura, $compramontobruto, $compramontoneto, $compratipopago, $proveedorid, $comprafechacreacion, $comprafechamodificacion);


    $check = $compraBusiness->validarCompra($compra, $accion != 'eliminar' , $accion == 'insertar'); // Indica si se validan (o no) los campos además del ID
    // Si los datos son válidos se realiza la acción correspondiente
    if ($check['is_valid']) {
        switch ($accion) {
            case 'insertar':
                $response = $compraBusiness->insertTBCompra($compra);
                break;
            case 'actualizar':
                $response = $compraBusiness->updateTBCompra($compra);
                break;
            case 'eliminar':
                $response = $compraBusiness->deleteTBCompra($id);
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
        exit();;
}

else if ($method == "GET") {
    // Parámetros de la solicitud
    $accion     = isset($_GET['accion'])    ? $_GET['accion']           : "";
    $deleted    = isset($_GET['deleted'])   ? boolval($_GET['deleted']) : false;
    $onlyActive = isset($_GET['filter'])    ? boolval($_GET['filter'])  : true;

    // Realizar la acción solicitada
    switch ($accion) {
        case 'all':
            // Obtener todos los productos
            $response = $compraBusiness->getAllTBCompra($onlyActive, $deleted);
            break;
        case 'id':
            // Obtener un producto por su ID
            $compraID = intval($_GET['id'] ?? -1);
            $response = $compraBusiness->getCompraByID($compraID, $onlyActive, $deleted);
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

            // Obtener los productos paginados
            $response = $compraBusiness->getPaginatedCompras($search, $page, $size, $sort, $onlyActive, $deleted);
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
