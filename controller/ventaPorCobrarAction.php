<?php

    require_once dirname(__DIR__, 1 ). '/service/ventaPorCobrarBusiness.php';
    require_once dirname(__DIR__, 1) . '/service/ventaBusiness.php';
    require_once dirname(__DIR__, 1) . '/domain/VentaPorCobrar.php';
    require_once dirname(__DIR__, 1) . '/domain/VentaDetalle.php';
    require_once dirname(__DIR__, 1) . '/domain/Venta.php';
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';

    $response = [];
    $method = $_SERVER["REQUEST_METHOD"]; // Método de la solicitud
    $ventaCobrarBusiness = new VentaPorCobrarBusiness();

    if ($method == "POST") {
        $accion = $_POST['accion'] ?? "";
        if (empty($accion)) {
            Utils::enviarRespuesta(400, false, "No se ha especificado una acción.");
        }

        $ventaData = isset($_POST['detalles']) ? json_decode($_POST['detalles'], true) : null;
        if (empty($ventaData)) {
            Utils::enviarRespuesta(400, false, "No se han recibido los datos de la venta.");
        }

        switch ($accion) {
            case 'abonar':
                $response = $ventaCobrarBusiness->abonarVentaCobrar($ventaData['Venta']['ID'], $ventaData['Abono']);
                break;
            case 'eliminar':
                $response = $ventaCobrarBusiness->deleteVentaCobrar($ventaData['ID']);
                break;
            default:
                // Validar los datos de la venta
                if (!isset($ventaData['VentaPorCobrar']) || empty($ventaData['VentaPorCobrar'])) {
                    Utils::enviarRespuesta(400, false, "No se han recibido los datos de la venta por cobrar.");
                }

                // Convertir los datos de la venta a objetos
                $venta = Utils::convertToObject($ventaData, Venta::class);

                // Convertir los detalles de la venta a objetos
                $detalles = array_map(function($detalle) use ($venta) {
                    $ventaDetalle = Utils::convertToObject($detalle, VentaDetalle::class);
                    $ventaDetalle->setVentaDetalleVenta($venta);
                    return $ventaDetalle;
                }, $ventaData['Detalles'] ?? []);

                // Convertir los datos de la venta por cobrar a objetos
                $ventaPorCobrar = Utils::convertToObject($ventaData['VentaPorCobrar'], VentaPorCobrar::class);
                $ventaPorCobrar->setVentaPorCobrarVenta($venta);

                $result = $ventaCobrarBusiness->validarCamposFecha($ventaPorCobrar, $accion === 'actualizar', true);
                if ($result["is_valid"]) {
                    switch ($accion) {
                        case 'insertar':
                            $response = $ventaCobrarBusiness->insertarListaVentaPorCobrar($ventaPorCobrar, $detalles);
                            break;
                        case 'actualizar':
                            $response = $ventaCobrarBusiness->updateVentaCobrar($ventaPorCobrar);
                            break;
                        default:
                            Utils::enviarRespuesta(400, false, "Acción no válida.");
                    }
                } else {
                    Utils::enviarRespuesta(400, false, $result['message']);
                }
                break;
        }

        Utils::enviarRespuesta($response['success'] ? 200 : 400, $response['success'], $response['message']);
    }

    else if ($method == "GET"){
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