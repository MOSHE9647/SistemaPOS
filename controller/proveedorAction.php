<?php

    require_once dirname(__DIR__, 1) . '/service/proveedorBusiness.php';
    require_once dirname(__DIR__, 1) . '/domain/Direccion.php';
    require_once dirname(__DIR__, 1) . '/domain/Categoria.php';
    require_once dirname(__DIR__, 1) . '/domain/Producto.php';
    require_once dirname(__DIR__, 1) . '/domain/Telefono.php';

    $response = [];                                 //<- Respuesta a enviar al cliente
    $method = $_SERVER["REQUEST_METHOD"];           //<- Método de la solicitud
    $proveedorBusiness = new ProveedorBusiness();   //<- Lógica de negocio de Proveedor

    if ($method == "POST") {
        // Acción a realizar en el controlador
        $accion = $_POST['accion'] ?? "";
        if (empty($accion)) {
            Utils::enviarRespuesta(400, false, "No se ha especificado una acción.");
        }

        // Datos del Proveedor recibidos en la solicitud
        $id          = isset($_POST['id'])          ? $_POST['id']                              : -1;
        $nombre      = isset($_POST['nombre'])      ? $_POST['nombre']                          : "";
        $email       = isset($_POST['email'])       ? $_POST['email']                           : "";
        $categoria   = isset($_POST['categoria'])   ? $_POST['categoria']                       : -1;
        $direcciones = isset($_POST['direcciones']) ? json_decode($_POST['direcciones'], true)  : [];
        $productos   = isset($_POST['productos'])   ? json_decode($_POST['productos'], true)    : [];
        $telefonos   = isset($_POST['telefonos'])   ? json_decode($_POST['telefonos'], true)    : [];
        
        // Crea y verifica que los datos del proveedor sean correctos
        $proveedor = new Proveedor($id, $nombre, $email, $direcciones, new Categoria($categoria), $productos, $telefonos);
        $check = $proveedorBusiness->validarProveedor($proveedor, $accion != 'eliminar', $accion == 'insertar');

        // Si los datos son válidos se realiza acción correspondiente
        if ($check['is_valid']) {
            switch ($accion) {
                case 'insertar':
                    // Inserta el proveedor en la base de datos
                    $response = $proveedorBusiness->insertTBProveedor($proveedor);
                    break;
                case 'actualizar':
                    // Actualiza la info del proveedor en la base de datos
                    $response = $proveedorBusiness->updateTBProveedor($proveedor);
                    break;
                case 'eliminar':
                    // Elimina al proveedor de la base de datos
                    $response = $proveedorBusiness->deleteTBProveedor($id);
                    break;
                default:
                    // Error en caso de que la accion no sea válida
                    Utils::enviarRespuesta(400, false, "Acción no válida.");
            }
        } else {
            // Si los datos no son validos, se devuelve un mensaje de error
            Utils::enviarRespuesta(400, false, $check['message']);
        }

        // Enviar respuesta al cliente
        http_response_code($response['success'] ? 200 : 400);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    else if ($method == "GET") {
        // Parámetros de la solicitud
        $accion     = isset($_GET['accion'])    ? $_GET['accion']           : "";
        $deleted    = isset($_GET['deleted'])   ? boolval($_GET['deleted']) : false;
        $onlyActive = isset($_GET['filter'])    ? boolval($_GET['filter'])  : true;

        // Realizar la acción solicitada
        switch ($accion) {
            case 'all':
                $response = $proveedorBusiness->getAllTBProveedor($onlyActive, $deleted);
                break;
            case 'id':
                $proveedorID = isset($_GET['id']) ? intval($_GET['id']) : -1;
                $response = $proveedorBusiness->getProveedorByID($proveedorID, $onlyActive, $deleted);
                break;
            default:
                // Obtener parámetros de la solicitud GET
                $search = isset($_GET['search']) ? $_GET['search'] : null;
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
                $sort = isset($_GET['sort']) ? $_GET['sort'] : null;

                // Validar los parámetros
                if ($page < 1) $page = 1;
                if ($size < 1) $size = 5;

                $response = $proveedorBusiness->getPaginatedProveedores($search, $page, $size, $sort, $onlyActive, $deleted);
                break;
        }
        
        // Enviar respuesta al cliente
        http_response_code($response['success'] ? 200 : 400);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
    
    else {
        // Enviar respuesta de método no permitido
        Utils::enviarRespuesta(405, false, "Método no permitido ($method).");
    }

?>
