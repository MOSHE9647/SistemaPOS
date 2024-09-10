<?php
require_once __DIR__ . '/../service/proveedorCategoriaBusiness.php';
require_once __DIR__ . '/../utils/Variables.php';


$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion = isset($_POST['accion'])?$_POST['accion']:"";
    $proveedorProductoId = isset($_POST['proveedorproductoid']) ? $_POST['proveedorproductoid'] : null;
    $proveedorId = isset($_POST['proveedorid'])?$_POST['proveedorid']:0 ;
    $categoriaId = isset($_POST['categoriaid'])?$_POST['categoriaid']:0;

   $proveedorCategoriaBusiness = new ProveedorCategoriaBusiness();
   Utils::writeLog("Id proveedor ...".$proveedorId,UTILS_LOG_FILE);
    switch($accion){
        case 'eliminar':
            $response =$proveedorCategoriaBusiness->deleteCategoriaToProveedor($proveedorId,$categoriaId);
            break;
        case 'insertar':
            $response =$proveedorCategoriaBusiness->addCategoriaProveedor($proveedorId, $categoriaId);
            break;
        case 'actualizar':
            break;
        default:
            $response['success'] = false;
            $response['message'] = "Accion no valida.";
            break;
    }
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
   $proveedorCategoriaBusiness = new ProveedorCategoriaBusiness();
   $response = [];
   Utils::writeLog("Id proveedor ... Listado",UTILS_LOG_FILE);
    if (isset($_GET['proveedor'])) {
        $proveedorProductoId = isset($_GET['proveedor']) ?$_GET['proveedor']:0;
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : null;
        // Validar los parámetros
        if ($page < 1) $page = 1;
        if ($size < 1) $size = 5;
        // Obtiene las categoria de un proveedor en la base de datos.
        $response = $proveedorCategoriaBusiness->getPaginateCategoriaProveedor($proveedorProductoId, $page, $size, $sort);

        echo json_encode( $response);
    } else {
        $response['success'] = false;
        $response['message'] = 'Selecciona un proveedor';
        echo json_encode($response);
    }
    exit();
}
?>
