<?php

// Importar las dependencias necesarias
require_once __DIR__ . '/../service/ventadetalleproductoBusiness.php';
require_once dirname(__DIR__, 1) . '/utils/Utils.php';
require_once dirname(__DIR__, 1) . '/service/productoBusiness.php'; // Asegúrate de que este archivo existe
require_once dirname(__DIR__, 1) . '/service/ventaDetalleBusiness.php'; // Asegúrate de que este archivo existe


$response = [];
$method = $_SERVER["REQUEST_METHOD"]; // Método de la solicitud
$ventadetalleBusiness = new VentadetalleBusiness(); // Lógica de negocio de Compra

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