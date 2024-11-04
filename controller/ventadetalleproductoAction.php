<?php

// Importar las dependencias necesarias
require_once __DIR__ . '/../service/ventadetalleproductoBusiness.php';
require_once dirname(__DIR__, 1) . '/utils/Utils.php';
require_once dirname(__DIR__, 1) . '/service/productoBusiness.php'; // Asegúrate de que este archivo existe
require_once dirname(__DIR__, 1) . '/service/ventaDetalleBusiness.php'; // Asegúrate de que este archivo existe


$response = [];
$method = $_SERVER["REQUEST_METHOD"]; // Método de la solicitud
$ventadetalleproductoBusiness = new VentadetalleProductoBusiness(); // Lógica de negocio de Compra

if ($method == "POST") {
    // Acción a realizar en el controlador
    $accion = $_POST['accion'] ?? ""; // Obtener la acción del POST

    // Comprobar si no se especificó ninguna acción
    if (empty($accion)) {
        Utils::enviarRespuesta(400, false, "No se ha especificado una acción.");
    }

    // Manejar la acción de obtener proveedor por ID
    if ($accion === 'getProducto') {
        $productoid = isset($_POST['productoid']) ? intval($_POST['productoid']) : 0; // ID del proveedor

        // Crear una instancia de ProveedorBusiness
        $productoBusiness = new ProductoBusiness();
        $producto = $productoBusiness->getProductoByID($productoid); // Cambiar a getCompraProveedorByID

        // Enviar respuesta al cliente
        http_response_code($producto['success'] ? 200 : 400);
        header("Content-Type: application/json");
        echo json_encode($producto);
        exit();
    }

     // Manejar la acción de obtener proveedor por ID
     if ($accion === 'getVentaDetalle') {
        $ventadetalleid = isset($_POST['ventadetalleid']) ? intval($_POST['ventadetalleid']) : 0; // ID del proveedor

        // Crear una instancia de ProveedorBusiness
        $ventadetalleBusiness = new VentadetalleBusiness();
        $ventadetalle = $ventadetalleBusiness->getVentaDetalleByID($ventadetalleid); // Cambiar a getCompraProveedorByID

        // Enviar respuesta al cliente
        http_response_code($ventadetalle['success'] ? 200 : 400);
        header("Content-Type: application/json");
        echo json_encode($ventadetalle);
        exit();
    }

    // Datos recibidos en la solicitud para acciones de compra
    $id                     = isset($_POST['id'])                     ? intval($_POST['id'])               : 0; // ID de la compra
    $ventadetalleid           = isset($_POST['ventadetalleid'])               ? intval($_POST['ventadetalleid']) : 0; // ID del proveedor
    $productoid           = isset($_POST['productoid'])           ? intval($_POST['productoid']) : 0; // ID del proveedor

    // Verifica que no estemos creando una compra si estamos eliminando
    if ($accion !== 'eliminar') {
        // Crea la instancia de Proveedor a partir del ID
        $productoBusiness = new ProductoBusiness();
        $producto = $productoBusiness->getProductoByID($productoid); // Cambiado aquí también

        $ventadetalleBusiness = new VentaDetalleBusiness();
        $ventadetalle = $ventadetalleBusiness->getVentaDetalleByID($ventadetalleid); // Cambiado aquí también

        if ($producto === null) {
            Utils::enviarRespuesta(400, false, "producto no encontrado.");
        }
        if ($ventadetalle === null) {
            Utils::enviarRespuesta(400, false, "venta no encontrado.");
        }
        // Crea la instancia de Compra solo si no es una acción de eliminación
        $compra = new Compra($id, $cliente, $proveedor, $compranumerofactura, $compramoneda, $compramontobruto, $compramontoneto,
        $compramontoimpuesto, $compracondicioncompra, $compratipopago,  $comprafechacreacion, $comprafechamodificacion);
        
        // Verifica que los datos de la compra sean válidos
        $check = $compraBusiness->validarCompra($compra, true, $accion == 'insertar'); // Validar campos
    } else {
        // Para la acción de eliminar, solo necesitamos el ID
        $check = ['is_valid' => true]; // Establecer como válido porque solo eliminamos
    }

    // Si los datos son válidos, se realiza la acción correspondiente
    if ($check['is_valid']) {
        switch ($accion) {
            case 'insertar':
                $response = $ventadetalleproductoBusiness->insertTBVentaDetalleProducto($venta);
                break;
            case 'actualizar':
                $response = $ventadetalleproductoBusiness->updateTBVentaDetalleProducto($compra);
                break;
            case 'eliminar':
                $response = $ventadetalleproductoBusiness->deleteTBVentaDetalleProducto($id); // Solo pasar el ID para eliminar
                break;
            default:
                Utils::enviarRespuesta(400, false, "Acción no válida.");
        }
    } else {
        // En caso de que los datos no sean válidos, se envía la respuesta correspondiente
        Utils::enviarRespuesta(400, false, $check['message']);
    }

    // Enviar respuesta al cliente
    http_response_code($response['success'] ? 200 : 400);
    header("Content-Type: application/json");
    echo json_encode($response);
    exit();
} else if ($method == "GET") {
    // Parámetros de la solicitud
    $accion     = isset($_GET['accion']) ? $_GET['accion'] : ""; // Obtener la acción del GET
    $deleted    = isset($_GET['deleted']) ? boolval($_GET['deleted']) : false; // Parámetro para mostrar eliminados
    $onlyActive = isset($_GET['filter']) ? boolval($_GET['filter']) : true; // Filtrar solo activos

    // Realizar la acción solicitada
    switch ($accion) {
        case 'all':
            // Obtener todos los productos
            $response = $ventadetalleproductoBusiness->getAllTBVentaDetalleProducto($onlyActive, $deleted);
            break;
        case 'id':
            // Obtener un producto por su ID
            $ventaproductoID = intval($_GET['id'] ?? -1); // Asegurarse de que sea un entero
            $response = $ventadetalleproductoBusiness->getVentaDetalleProductoByID($ventaproductoID, $onlyActive, $deleted);
            break;
        default:
            // Parámetros de paginación
            $search = isset($_GET['search']) ? $_GET['search'] : null; // Parámetro de búsqueda
            $page   = isset($_GET['page']) ? intval($_GET['page']) : 1; // Página actual
            $size   = isset($_GET['size']) ? intval($_GET['size']) : 5; // Tamaño de la página
            $sort   = isset($_GET['sort']) ? $_GET['sort'] : null; // Parámetro de ordenación

            // Validar los parámetros
            if ($page < 1) $page = 1; // Asegurarse de que la página sea válida
            if ($size < 1) $size = 5; // Asegurarse de que el tamaño sea válido

            // Obtener los productos paginados
            $response = $ventadetalleproductoBusiness->getPaginatedVentaDetalleProducto($search, $page, $size, $sort, $onlyActive, $deleted);
            break;
    }

    // Enviar respuesta al cliente
    http_response_code($response['success'] ? 200 : 400);
    header("Content-Type: application/json");
    echo json_encode($response);
    exit();
} else {
    // Enviar respuesta de método no permitido
    Utils::enviarRespuesta(405, false, "Método no permitido ($method).");
}
