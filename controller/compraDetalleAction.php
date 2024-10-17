<?php

require_once __DIR__ . '/../service/compraDetalleBusiness.php';

$response = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Acción que se va a realizar
    $accion = $_POST['accion'];

    // Datos recibidos en la solicitud (Form)
    $id = isset($_POST['id']) ? $_POST['id'] : 0;
    $compraid = isset($_POST['compranumerofactura']) ? $_POST['compranumerofactura'] : 0;
    $loteid = isset($_POST['lotecodigo']) ? $_POST['lotecodigo'] : 0;
    //$loteid = isset($_POST['loteid']) ? $_POST['loteid'] : 0;
    $productoid = isset($_POST['productonombre']) ? $_POST['productonombre'] : 0;
    $precioproducto = isset($_POST['precioproducto']) ? $_POST['precioproducto'] : 0.00;
    $cantidad = isset($_POST['cantidad']) ? $_POST['cantidad'] : 0;
    
    // Para los campos de fechas y estado
    $fechacreacion = isset($_POST['fechacreacion']) ? $_POST['fechacreacion'] : '';
    $fechamodificacion = isset($_POST['fechamodificacion']) ? $_POST['fechamodificacion'] : '';
    $estado = isset($_POST['estado']) ? $_POST['estado'] : 1;

    // Se crea el Service para las operaciones
    $compraDetalleBusiness = new CompraDetalleBusiness();

    // Crea y verifica que los datos del detalle de compra sean correctos
    $compraDetalle = new CompraDetalle($id, $compraid, $loteid, $productoid, $precioproducto, $cantidad, $fechacreacion, $fechamodificacion, $estado);
    $check = $compraDetalleBusiness->validarCompraDetalle($compraDetalle, $accion != 'eliminar'); //<- Indica si se validan (o no) los campos además del ID

    // Si los datos son válidos se realiza acción correspondiente
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
                $response = $compraDetalleBusiness->deleteCompraDetalle($id);
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
