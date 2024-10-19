<?php

require_once __DIR__ . '/../service/compraDetalleBusiness.php';

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Acción que se va a realizar
    $accion = $_POST['accion'];

    // Datos recibidos en la solicitud (Form)
    $compraDetalleID = isset($_POST['compradetalleid']) ? intval($_POST['compradetalleid']) : 0;
    $compraDetalleCompra = isset($_POST['compradetallecompraid']) ? intval($_POST['compradetallecompraid']) : 0;
    $compraDetalleProducto = isset($_POST['compradetalleproductoid']) ? intval($_POST['compradetalleproductoid']) : 0;

    // Para los campos de fechas y estado
    $compraDetalleFechaCreacion = isset($_POST['compradetallefechacreacion']) ? $_POST['compradetallefechacreacion'] : '';
    $compraDetalleFechaModificacion = isset($_POST['compradetallefechamodificacion']) ? $_POST['compradetallefechamodificacion'] : '';
    $compraDetalleEstado = isset($_POST['compradetalleestado']) ? intval($_POST['compradetalleestado']) : 1;

    // Se crea el Service para las operaciones
    $compraDetalleBusiness = new CompraDetalleBusiness();

    // Crea y verifica que los datos del detalle de compra sean correctos
    $compraDetalle = new CompraDetalle(
        $compraDetalleID, 
        $compraDetalleCompraID, 
        $compraDetalleProductoID, 
        $compraDetalleFechaCreacion, 
        $compraDetalleFechaModificacion, 
        $compraDetalleEstado
    );

    $check = $compraDetalleBusiness->validarCompraDetalle($compraDetalle, $accion != 'eliminar'); // Indica si se validan (o no) los campos además del ID

    // Si los datos son válidos se realiza la acción correspondiente
    if ($check['is_valid']) {
        switch ($accion) {
            case 'insertar':
                // Inserta el detalle de compra en la base de datos
                $response = $compraDetalleBusiness->insertCompraDetalle($compraDetalle);
                break;
            case 'actualizar':
                // Actualiza la info del detalle de compra en la base de datos
                $response = $compraDetalleBusiness->updateCompraDetalle($compraDetalle);
                break;
            case 'eliminar':
                // Elimina el detalle de compra de la base de datos (ID se verifica en validarCompraDetalle)
                $response = $compraDetalleBusiness->deleteCompraDetalle($compraDetalleID);
                break;
            default:
                // Error en caso de que la acción no sea válida
                $response['success'] = false;
                $response['message'] = "Acción no válida.";
                break;
        }
    } else {
        // Si los datos no son válidos, se devuelve un mensaje de error
        $response['success'] = $check['is_valid'];
        $response['message'] = $check['message'];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Obtener parámetros de la solicitud GET
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $size = isset($_GET['size']) ? intval($_GET['size']) : 5;

    // Validar los parámetros
    if ($page < 1) $page = 1;
    if ($size < 1) $size = 5;

    // Crea el Service y obtiene la lista (paginada) de detalles de compra
    $compraDetalleBusiness = new CompraDetalleBusiness();
    $response = $compraDetalleBusiness->getPaginatedCompraDetalles($page, $size);

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

?>
