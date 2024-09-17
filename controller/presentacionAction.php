<?php
require_once __DIR__ . '/../service/presentacionBusiness.php';
require_once __DIR__ . '/../utils/Utils.php';

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion = $_POST['accion'];
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : "";
    $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : "";

    $presentacionBusiness = new PresentacionBusiness();
    $presentacion = new Presentacion($id, $nombre, $descripcion, 1); // El estado por defecto es 1 (activo)

    switch($accion){
        case 'eliminar':
            $response = $presentacionBusiness->deleteTBPresentacion($presentacion);
            break;
        case 'insertar':
            $response = $presentacionBusiness->insertTBPresentacion($presentacion);
            break;
        case 'actualizar':
            $response = $presentacionBusiness->updateTBPresentacion($presentacion);
            break;
        default:
            $response['success'] = false;
            $response['message'] = "Acción no válida.";
            break;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {

    if (isset($_GET['accion']) && $_GET['accion'] === 'listarPresentaciones') {
        Utils::writeLog("Ingreso a listar presentaciones", UTILS_LOG_FILE);
        $presentacionBusiness = new PresentacionBusiness();
        $result = $presentacionBusiness->getAllTBPresentaciones();
        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    }

    // Obtener parámetros de la solicitud GET
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
    $sort = isset($_GET['sort']) ? $_GET['sort'] : null;

    // Validar los parámetros
    if ($page < 1) $page = 1;
    if ($size < 1) $size = 5;

    $presentacionBusiness = new PresentacionBusiness();
    $result = $presentacionBusiness->getPaginatedPresentaciones($page, $size, $sort);

    header('Content-Type: application/json');
    echo json_encode($result);
    exit();
}
?>
