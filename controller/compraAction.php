<?php

require_once __DIR__ . '/../service/compraBusiness.php';

$response = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Acción que se va a realizar
    $accion = $_POST['accion'];

    // Datos recibidos en la solicitud (Form)
    $id = isset($_POST['id']) ? $_POST['id'] : 0;
    $numeroFactura = isset($_POST['numeroFactura']) ? $_POST['numeroFactura'] : "";
    $montoBruto = isset($_POST['montoBruto']) ? $_POST['montoBruto'] : 0;
    $montoNeto = isset($_POST['montoNeto']) ? $_POST['montoNeto'] : 0;
    $tipoPago = isset($_POST['tipoPago']) ? $_POST['tipoPago'] : "";
    $proveedorId = isset($_POST['proveedorId']) ? $_POST['proveedorId'] : 0;
    $fechaCreacion = isset($_POST['fechaCreacion']) ? $_POST['fechaCreacion'] : '';
    $fechaModificacion = isset($_POST['fechaModificacion']) ? $_POST['fechaModificacion'] : '';
    
    // Se crea el Service para las operaciones
    $compraBusiness = new CompraBusiness();

    // Crea y verifica que los datos de la compra sean correctos
    $compra = new Compra($id, $numeroFactura, $montoBruto, $montoNeto, $tipoPago, $proveedorId, $fechaCreacion, $fechaModificacion);
    $check = $compraBusiness->validarCompra($compra, $accion != 'eliminar'); // Indica si se validan (o no) los campos además del ID

    // Si los datos son válidos se realiza la acción correspondiente
    if ($check['is_valid']) {
        switch ($accion) {
            case 'insertar':
                // Inserta la compra en la base de datos
                $response = $compraBusiness->insertTBCompra($compra);
                break;
            case 'actualizar':
                // Actualiza la información de la compra en la base de datos
                $response = $compraBusiness->updateTBCompra($compra);
                break;
            case 'eliminar':
                // Elimina la compra de la base de datos (ID se verifica en validarCompra)
                $response = $compraBusiness->deleteTBCompra($id);
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

    // Crea el Service y obtiene la lista (paginada) de compras
    $compraBusiness = new CompraBusiness();
    $response = $compraBusiness->getPaginatedCompras($page, $size);

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

?>
