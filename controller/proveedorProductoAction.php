<?php
require_once __DIR__ . '/../service/proveedorProductoBusiness.php';
require_once __DIR__ . '/../utils/Variables.php';


$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion = isset($_POST['accion'])?$_POST['accion']:"";
    $proveedorProductoId = isset($_POST['proveedorproductoid']) ? $_POST['proveedorproductoid'] : null;
    $proveedorId = isset($_POST['proveedorid'])?$_POST['proveedorid']:0 ;
    $productoId = isset($_POST['productoid'])?$_POST['productoid']:0;

    $proveedorProductoBusiness = new ProveedorProductoBusiness();
    switch($accion){
        case 'eliminar':
            $response = $proveedorProductoBusiness->deleteProductoToProveedor($proveedorId,$productoId);
            break;
        case 'insertar':
            $response = $proveedorProductoBusiness->addProductoProveedor($proveedorId, $productoId);
            break;
        case 'actualizar':
            $response = $proveedorProductoBusiness->updateProveedorProducto($proveedorProductoId,$proveedorId,$productoId);
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
    
    $proveedorProductoBusiness = new ProveedorProductoBusiness();



    if (isset($_GET['id'])) {
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : null; 
        $proveedorProductoId = isset($_GET['id']) ?$_GET['id']:0;
        $result = $proveedorProductoBusiness->getPaginateProductoProveedor($proveedorProductoId,$page, $size, $sort);
        // $result = $proveedorProductoBusiness->obtenerProveedorProductoPorId($proveedorProductoId);
        echo json_encode($result);
    } else {
        $result = $proveedorProductoBusiness->obtenerTodosProveedorProducto();
        echo json_encode($result);
    }
    exit();
}
?>
