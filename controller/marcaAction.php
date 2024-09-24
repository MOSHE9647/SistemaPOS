<?php
require_once __DIR__ . '/../service/marcaBusiness.php';
require_once __DIR__ . '/../utils/Utils.php';

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion = $_POST['accion'];
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : "";
    $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : "";

    $marcaBusiness = new MarcaBusiness();
    $marca = new Marca($id, $nombre, $descripcion);

    switch ($accion) {
        case 'eliminar':
            $response = $marcaBusiness->deleteTBMarca($marca);
            break;
        case 'insertar':
            $response = $marcaBusiness->insertTBMarca($marca);
            break;
        case 'actualizar':
            $response = $marcaBusiness->updateTBMarca($marca);
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

    if (isset($_GET['accion']) && $_GET['accion'] === 'listarMarcas') {
        Utils::writeLog("Ingreso a listar marcas", UTILS_LOG_FILE);
        $marcaBusiness = new MarcaBusiness();
        $result = $marcaBusiness->getAllTBMarcas();
        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'listarProductoMarcas') {
        // Este es el bloque que quieres insertar
        $marcaBusiness = new MarcaBusiness();
        $result = $marcaBusiness->getAllTBProductoMarca();
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

    $marcaBusiness = new MarcaBusiness();
    $result = $marcaBusiness->getPaginatedMarcas($page, $size, $sort);

    header('Content-Type: application/json');
    echo json_encode($result);
    exit();
}
?>

