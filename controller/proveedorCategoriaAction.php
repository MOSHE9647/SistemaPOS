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
    if (isset($_GET['id'])) {
        $proveedorProductoId = isset($_GET['id']) ?$_GET['id']:0;
        $result =$proveedorCategoriaBusiness-> getAllCategoriasProveedor($proveedorProductoId);

        // $result =$proveedorCategoriaBusiness->obtenerProveedorProductoPorId($proveedorProductoId);
        echo json_encode($result);
    } else {
        $result = $proveedorCategoriaBusiness->getAllCategoriasProveedor(0);
        echo json_encode($result);
    }
    exit();
}
?>
