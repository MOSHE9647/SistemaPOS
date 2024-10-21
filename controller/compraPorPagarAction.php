<?php

require_once __DIR__ . '/../service/compraPorPagarBusiness.php';
require_once dirname(__DIR__, 1) . '/utils/Utils.php';
require_once __DIR__ . '/../domain/CompraPorPagar.php';

$response = [];
$method = $_SERVER["REQUEST_METHOD"];                   //<- Método de la solicitud
$compraPagarBusiness = new CompraPorPagarBussines();

if ($method == "POST") {
    $accion = $_POST['accion'] ?? "";
    if (empty($accion)) {
        Utils::enviarRespuesta(400, false, "No se ha especificado una acción.");
    }

    $id             = isset($_POST['id'])           ?intval($_POST['id'])               :-1;
    $detalleid      = isset($_POST['detalleid'])    ?intval($_POST['detalleid'])        :-1;
    //objeto detalle
    $DetalleCompra  = isset($_POST['compradetallecompraid']) ? intval($_POST['compradetallecompraid']) : 0;
    //Objeto Compra
    $DetalleProducto= isset($_POST['compradetalleproductoid']) ? intval($_POST['compradetalleproductoid']) : 0;
    //objetoProducto


    //resto del objeto CompraPorPagar
    $fechaVence     = isset($_POST['fechaVence'])   ?$_POST['fechaVence']               :"";
    $montoTotal     = isset($_POST['montoTotal'])   ?floatval($_POST['montoTotal'])     :0;
    $montoPagado    = isset($_POST['montoPagado'])  ?floatval($_POST['montoPagado'])    :0;
    $fechaPago      = isset($_POST['fechaPago'])    ?$_POST['fechaPago']                :"";
    $estadoCuenta   = isset($_POST['estadoCuenta']) ?$_POST['estadoCuenta']             :"";
    $notas          = isset($_POST['notas'])        ?$_POST['notas']                    :"";


    $ObjetoCompra = new CompraPorPagar($id, 
                    new CompraDetalle($detalleid),
                    $fechaVence,
                    $montoTotal,
                    $montoPagado,
                    $fechaPago,
                    $estadoCuenta,
                    $notas
                );
    
    $result = $compraPagarBusiness->validarCamposFecha($ObjetoCompra,($accion === 'actualizar')? true:false, true);
    if($result["is_valid"]){
            switch($accion){
                case 'insertar':
                    $response =$compraPagarBusiness->insertCompraPorPagar($ObjetoCompra);
                    break;
                case 'eliminar':
                    $response = $compraPagarBusiness->deleteCompraPorPagar($id);
                    break;
                case 'actualizar':
                    $response = $compraPagarBusiness->updateCompraPorPagar($ObjetoCompra);
                    break;
                default:
                    Utils::enviarRespuesta(400, false, "Acción no válida.");
                    break;
            }
    }else{
        Utils::enviarRespuesta(400, false, $check['message']);
    }

    http_response_code($response['success'] ? 200 : 400);
    header("Content-Type: application/json");
    echo json_encode($response);
    exit();



}else if ($method == "GET"){
    // Parámetros de la solicitud
    $accion     = isset($_GET['accion'])    ? $_GET['accion']           : "";
    $deleted    = isset($_GET['deleted'])   ? boolval($_GET['deleted']) : false;
    $onlyActive = isset($_GET['filter'])    ? boolval($_GET['filter'])  : true;

    switch($accion){
        case 'all':
            $response = $compraPagarBusiness->getAllCompraPorPagar($onlyActive,$deleted);
            break;
        case 'id':
            $id = isset($_GET['id'])? intval($_GET['id']):-1;
            $response = $compraPagarBusiness->getCompraPorPagarID($id,$onlyActive,$deleted);
            break;
        default:
                $search = isset($_GET['search']) ? $_GET['search']          : null;
                $page   = isset($_GET['page'])   ? intval($_GET['page'])    : 1;
                $size   = isset($_GET['size'])   ? intval($_GET['size'])    : 5;
                $sort   = isset($_GET['sort'])   ? $_GET['sort']            : null;

                // Validar los parámetros
                if ($page < 1) $page = 1;
                if ($size < 1) $size = 5;

                $response = $compraPagarBusiness->paginaCompraPorPagar($search,$page,$size,$sort,$onlyActive,$deleted);
                break;
    }
    // Enviar respuesta al cliente
    http_response_code($response['success'] ? 200 : 400);
    header("Content-Type: application/json");
    echo json_encode($response);
    exit();
}else{
    Utils::enviarRespuesta(405, false, "Método no permitido ($method).");
}
?>