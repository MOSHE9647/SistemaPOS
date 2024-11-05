<?php

// Importar las dependencias necesarias
require_once __DIR__ . '/../service/ventaBusiness.php';
require_once dirname(__DIR__, 1) . '/utils/Utils.php';
require_once dirname(__DIR__, 1) . '/service/clienteBusiness.php'; // Asegúrate de que este archivo existe

$response = [];
$method = $_SERVER["REQUEST_METHOD"]; // Método de la solicitud
$ventaBusiness = new VentaBusiness(); // Lógica de negocio de Compra

if ($method == "POST") {
    // Acción a realizar en el controlador
    $accion = $_POST['accion'] ?? ""; // Obtener la acción del POST

    // Comprobar si no se especificó ninguna acción
    if (empty($accion)) {
        Utils::enviarRespuesta(400, false, "No se ha especificado una acción.");
    }

    // Manejar la acción de obtener proveedor por ID
    if ($accion === 'getCliente') {
        $clienteid = isset($_POST['clienteid']) ? intval($_POST['clienteid']) : 0; // ID del proveedor

        // Crear una instancia de ProveedorBusiness
        $clienteBusiness = new ClienteBusiness();
        $cliente = $clienteBusiness->getVentaClienteByID($clienteid); // Cambiar a getCompraProveedorByID

        // Enviar respuesta al cliente
        http_response_code($cliente['success'] ? 200 : 400);
        header("Content-Type: application/json");
        echo json_encode($cliente);
        exit();
    }



    // Datos recibidos en la solicitud para acciones de compra
    $id                     = isset($_POST['id'])                     ? intval($_POST['id'])               : 0; // ID de la compra
    $clienteid              = isset($_POST['clienteid'])              ? intval($_POST['clienteid']) : 0; // ID del cliente
    $ventanumerofactura     = isset($_POST['numerofactura'])         ? $_POST['numerofactura']    : ""; // Número de factura
    $ventamoneda            = isset($_POST['moneda'])                ? $_POST['moneda']          : ""; // Moneda
    $ventamontobruto        = isset($_POST['montobruto'])            ? floatval($_POST['montobruto']) : 0.0; // Monto bruto
    $ventamontoneto         = isset($_POST['montoneto'])             ? floatval($_POST['montoneto']) : 0.0; // Monto neto
    $ventamontoimpuesto     = isset($_POST['montoimpuesto'])         ? floatval($_POST['montoimpuesto']) : 0.0; // Monto impuesto
    $ventacondicionventa    = isset($_POST['condicionventa'])        ? $_POST['condicionventa']   : ""; // Condición de venta
    $ventatipopago          = isset($_POST['tipopago'])              ? $_POST['tipopago']         : ""; // Tipo de pago
    $ventamontopago        = isset($_POST['montopago'])             ? floatval($_POST['montopago']) : 0.0; // Monto neto
    $ventamontovuelto     = isset($_POST['montovuelto'])         ? floatval($_POST['montovuelto']) : 0.0; // Monto impuesto
    $ventareferenciatarjeta     = isset($_POST['referenciatarjeta'])         ? $_POST['referenciatarjeta']    : ''; // Fecha de creación
    $ventacomprobantesinpe = isset($_POST['comprobantesinpe'])     ? $_POST['comprobantesinpe'] : ''; // Fecha de modificación
    $ventafechacreacion     = isset($_POST['fechacreacion'])         ? $_POST['fechacreacion']    : ''; // Fecha de creación
    $ventafechamodificacion = isset($_POST['fechamodificacion'])     ? $_POST['fechamodificacion'] : ''; // Fecha de modificación

    // Verifica que no estemos creando una compra si estamos eliminando
    if ($accion !== 'eliminar') {
        // Crea la instancia de Proveedor a partir del ID
        $clienteBusiness = new ClienteBusiness();
        $cliente = $clienteBusiness->getVentaClienteByID($clienteid); // Cambiado aquí también

        if ($cliente === null) {
            Utils::enviarRespuesta(400, false, "Cliente no encontrado.");
        }

        // Crea la instancia de Venta solo si no es una acción de eliminación
        $venta = new Venta($id, $cliente, $ventanumerofactura, $ventamoneda, $ventamontobruto, $ventamontoneto, $ventamontoimpuesto, 
        $ventacondicionventa, $ventatipopago, $ventamontopago, $ventamontovuelto, $ventareferenciatarjeta,
        $ventacomprobantesinpe, $ventafechacreacion, $ventafechamodificacion);
        
        
        // Verifica que los datos de la compra sean válidos
        $check = $ventaBusiness->validarVenta($venta, true, $accion == 'insertar'); // Validar campos
    } else {
        // Para la acción de eliminar, solo necesitamos el ID
        $check = ['is_valid' => true]; // Establecer como válido porque solo eliminamos
    }

    // Si los datos son válidos, se realiza la acción correspondiente
    if ($check['is_valid']) {
        switch ($accion) {
            case 'insertar':
                $response = $ventaBusiness->insertTBVenta($venta);
                break;
            case 'actualizar':
                $response = $ventaBusiness->updateTBVenta($venta);
                break;
            case 'eliminar':
                $response = $ventaBusiness->deleteTBVenta($id); // Solo pasar el ID para eliminar
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
            $response = $ventaBusiness->getAllTBVenta($onlyActive, $deleted);
            break;
        case 'id':
            // Obtener un producto por su ID
            $ventaID = intval($_GET['id'] ?? -1); // Asegurarse de que sea un entero
            $response = $ventaBusiness->getVentaByID($ventaID, $onlyActive, $deleted);
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
            $response = $ventaBusiness->getPaginatedVentas($search, $page, $size, $sort, $onlyActive, $deleted);
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
