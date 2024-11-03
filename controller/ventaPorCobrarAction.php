<?php

require_once dirname(__DIR__, 1 ). '/service/ventaPorCobrarBusiness.php';
require_once dirname(__DIR__, 1) . '/utils/Utils.php';
require_once dirname(__DIR__, 1) . '/domain/VentaPorCobrar.php';
require_once dirname(__DIR__, 1) . '/domain/Venta.php';

$response = [];
$method = $_SERVER["REQUEST_METHOD"];                   //<- Método de la solicitud
$ventaCobrarBusiness = new VentaPorCobrarBusiness();

if ($method == "POST") {
    $accion = $_POST['accion'] ?? "";
    if (empty($accion)) {
        Utils::enviarRespuesta(400, false, "No se ha especificado una acción.");
    }

    $id                 = isset($_POST['id'])               ?intval($_POST['id'])               :-1;
    $ventaid            = isset($_POST['ventaid'])          ?intval($_POST['ventaid'])          : 0;
    $fechaVence         = isset($_POST['fechaVence'])       ?$_POST['fechaVence']               :"";
    $estadoCancelado    = isset($_POST['estadoCancelado'])  ?$_POST['estadoCancelado']          :false;
    $notas              = isset($_POST['notas'])            ?$_POST['notas']                    :"";

    $ventaobj = new VentaPorCobrar($id,
                    new Venta($ventaid),
                    $fechaVence,
                    $estadoCancelado,
                    $notas
                );
    
    $result = $ventaCobrarBusiness->validarCamposFecha($ventaobj,($accion === 'actualizar')? true:false, true);
    if($result["is_valid"]){
            switch($accion){
                case 'insertar':
                    $response =$ventaCobrarBusiness->insertVentaCobrar($ventaobj);
                    break;
                case 'eliminar':
                    $response = $ventaCobrarBusiness->deleteVentaCobrar($id);
                    break;
                case 'actualizar':
                    $response = $ventaCobrarBusiness->updateVentaCobrar($ventaobj);
                    break;
                default:
                    Utils::enviarRespuesta(400, false, "Acción no válida.");
                    break;
            }
    }else{
        Utils::enviarRespuesta(400, false, $result['message']);
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
            $response = $ventaCobrarBusiness->getAllVentaCobrar($onlyActive,$deleted);
            break;
        case 'id':
            $id = isset($_GET['id'])? intval($_GET['id']):-1;
            $response = $ventaCobrarBusiness->getVentaCobrarID($id,$onlyActive,$deleted);
            break;
        case 'cliente':
            $idCliente = isset($_GET['clienteid'])?$_GET['clienteid']:-1;
            $response = $ventaCobrarBusiness->ventaPorCobrarClienteExiste($idCliente);
            break;
        default:
            $search = isset($_GET['search']) ? $_GET['search']          : null;
            $page   = isset($_GET['page'])   ? intval($_GET['page'])    : 1;
            $size   = isset($_GET['size'])   ? intval($_GET['size'])    : 5;
            $sort   = isset($_GET['sort'])   ? $_GET['sort']            : null;

            // Validar los parámetros
            if ($page < 1) $page = 1;
            if ($size < 1) $size = 5;

            $response = $ventaCobrarBusiness->paginaVentaCobrar($search,$page,$size,$sort,$onlyActive,$deleted);
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