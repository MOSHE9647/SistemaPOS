<?php

include __DIR__ . '/../service/loteBusiness.php';

$response = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Acción que se va a realizar
    $accion = $_POST['accion'];

    // Datos recibidos en la solicitud (Form)
    $id = isset($_POST['id']) ? $_POST['id'] : 0;
    $lotecodigo = isset($_POST['lotecodigo']) ? $_POST['lotecodigo'] : "";
    $compraid = isset($_POST['compraid']) ? $_POST['compraid'] : 0;
    $productoid = isset($_POST['productonombre']) ? $_POST['productonombre'] : 0;
    $proveedorid = isset($_POST['proveedornombre']) ? $_POST['proveedornombre'] : 0;
    $lotefechavencimiento = isset($_POST['lotefechavencimiento']) ? $_POST['lotefechavencimiento'] : '';

    // Se crea el Service para las operaciones
    $loteBusiness = new LoteBusiness();

    // Crea y verifica que los datos del lote sean correctos
    $lote = new Lote($id, $lotecodigo, $compraid, $productoid, $proveedorid, $lotefechavencimiento);
    $check = $loteBusiness->validarLote($lote, $accion != 'eliminar'); //<- Indica si se validan (o no) los campos además del ID

    // Si los datos son válidos se realiza acción correspondiente
    if ($check['is_valid']) {
        switch ($accion) {
            case 'insertar':
                // Inserta el lote en la base de datos
                $response = $loteBusiness->insertTBLote($lote);
                break;
            case 'actualizar':
                // Actualiza la info del lote en la base de datos
                $response = $loteBusiness->updateTBLote($lote);
                break;
            case 'eliminar':
                // Elimina el lote de la base de datos (ID se verifica en validarLote)
                $response = $loteBusiness->deleteTBLote($id);
                break;
            default:
                // Error en caso de que la accion no sea válida
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

    // Crea el Service y obtiene la lista (paginada) de lotes
    $loteBusiness = new LoteBusiness();
    $response = $loteBusiness->getPaginatedLotes($page, $size);

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

?>
