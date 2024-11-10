<?php

    require_once dirname(__DIR__, 1 ). '/service/ventaPorCobrarBusiness.php';
    require_once dirname(__DIR__, 1) . '/service/ventaBusiness.php';
    require_once dirname(__DIR__, 1) . '/domain/VentaPorCobrar.php';
    require_once dirname(__DIR__, 1) . '/domain/VentaDetalle.php';
    require_once dirname(__DIR__, 1) . '/domain/Venta.php';
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';

    $response = [];
    $method = $_SERVER["REQUEST_METHOD"];                   //<- Método de la solicitud
    $ventaCobrarBusiness = new VentaPorCobrarBusiness();

    if ($method == "POST") {
        // Acción a realizar (insertar, actualizar, eliminar, abonar)
        $accion = $_POST['accion'] ?? "";
        if (empty($accion)) {
            // Enviar respuesta de error al cliente
            Utils::enviarRespuesta(400, false, "No se ha especificado una acción.");
        }

        // Verificar si se quiere abonar una venta
        // if ($accion === 'abonar') {
        //     // Datos de la venta y el abono recibidos en la solicitud
        //     $ventaPorCobrar = isset($_POST['venta']) ? json_decode($_POST['venta'], true) : null;
        //     $response = $ventaCobrarBusiness->abonarVentaCobrar($ventaPorCobrar['ID'], $abono);
        //     Utils::enviarRespuesta($response['success'] ? 200 : 400, $response['success'], $response['message']);
        // }

        // Extraer los datos de la venta y los detalles de la solicitud
        $ventaData = isset($_POST['detalles']) ? json_decode($_POST['detalles'], true) : null;
        if (empty($ventaData) || !isset($ventaData['Detalles'])) {
            // Enviar respuesta de error al cliente
            Utils::enviarRespuesta(400, false, "No se han recibido los datos de la venta.");
        }

        // Verificar si vienen los datos de venta por cobrar en la solicitud
        if (!isset($ventaData['VentaPorCobrar']) || empty($ventaData['VentaPorCobrar'])) {
            // Enviar respuesta de error al cliente
            Utils::enviarRespuesta(400, false, "No se han recibido los datos de la venta por cobrar.");
        }
        
        // Creamos los objetos necesarios
        $venta = Utils::convertToObject($ventaData, Venta::class); //<- Crear objeto Venta

        // Obtenemos la lista de ventaDetalle
        $detalles = []; //<- Obtener los detalles de la venta
        foreach ($ventaData['Detalles'] as $detalle) {
            // Crear un objeto VentaDetalle con los datos recibidos
            $ventaDetalle = Utils::convertToObject($detalle, VentaDetalle::class);
            $ventaDetalle->setVentaDetalleVenta($venta);
            array_push($detalles, $ventaDetalle);
        }

        // Creamos el objeto VentaPorCobrar y asignamos la Venta
        $ventaPorCobrar = Utils::convertToObject($ventaData['VentaPorCobrar'], VentaPorCobrar::class);
        $ventaPorCobrar->setVentaPorCobrarVenta($venta);
        
        $result = $ventaCobrarBusiness->validarCamposFecha($ventaPorCobrar, ($accion === 'actualizar') ? true : false, true);
        if ($result["is_valid"]) {
            switch($accion){
                case 'insertar':
                    $response =$ventaCobrarBusiness->insertarListaVentaPorCobrar($ventaPorCobrar, $detalles);
                    break;
                case 'eliminar':
                    $response = $ventaCobrarBusiness->deleteVentaCobrar($id);
                    break;
                case 'actualizar':
                    $response = $ventaCobrarBusiness->updateVentaCobrar($ventaobj);
                    break;
                case 'abonar':
                    $abono = isset($_POST['abono']) ? floatval($_POST['abono']) : 0;
                    $response = $ventaCobrarBusiness->abonarVentaCobrar($id, $abono);
                    break;
                default:
                    Utils::enviarRespuesta(400, false, "Acción no válida.");
                    break;
            }
        } else {
            Utils::enviarRespuesta(400, false, $result['message']);
        }

        http_response_code($response['success'] ? 200 : 400);
        header("Content-Type: application/json");
        echo json_encode($response);
        exit();
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