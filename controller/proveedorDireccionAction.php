<?php

    require_once __DIR__ . '/../service/proveedorDireccionBusiness.php';

    $response = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Acción que se va a realizar
        $accion = isset($_POST['accion']) ? $_POST['accion'] : "";

        // Datos del Form
        $proveedorID = isset($_POST['proveedor']) ? intval($_POST['proveedor']) : null;
        $direccionID = isset($_POST['direccion']) ? intval($_POST['direccion']) : null;

        // Se crea el Service para las operaciones
        $proveedorDireccionBusiness = new ProveedorDireccionBusiness();

        // Crea y verifica que los ID del Proveedor y de la Dirección sean correctos
        $check = $proveedorDireccionBusiness->validarProveedorDireccion($proveedorID, $direccionID);

        // Si los datos son válidos se realiza acción correspondiente
        if ($check['is_valid']) {
            switch ($accion) {
                case 'agregar':
                    // Agrega una dirección a un proveedor en la base de datos.
                    $response = $proveedorDireccionBusiness->addDireccionToProveedor($proveedorID, $direccionID);
                    break;
                case 'eliminar':
                    // Elimina una dirección de un proveedor en la base de datos.
                    $response = $proveedorDireccionBusiness->removeDireccionFromProveedor($proveedorID, $direccionID);
                    break;
                default:
                    // Error en caso de que la accion no sea válida
                    $response['success'] = false;
                    $response['message'] = "Acción no válida.";
                    break;
            }
        } else {
            // Si los datos no son validos, se devuelve un mensaje de error
            $response['success'] = $check['is_valid'];
            $response['message'] = $check['message'];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $accion = isset($_GET['accion']) ? $_GET['accion'] : "";
        $deleted = isset($_GET['deleted']) ? boolval($_GET['deleted']) : false;
        $onlyActiveOrInactive = isset($_GET['filter']) ? boolval($_GET['filter']) : true;
        $proveedorID = isset($_GET['proveedor']) ? intval($_GET['proveedor']) : null;

        // Se crea el Service para las operaciones
        $proveedorDireccionBusiness = new ProveedorDireccionBusiness();

        // Crea y verifica que el ID del Proveedor sea correcto
        $check = $proveedorDireccionBusiness->validarProveedorDireccion($proveedorID, null, false);

        if ($check['is_valid']) {
            switch ($accion) {
                case 'id':
                    // Obtiene las direcciones de un proveedor en la base de datos.
                    $response = $proveedorDireccionBusiness->getDireccionesByProveedor($proveedorID, true);
                    break;
                default:
                    // Obtener parámetros de la solicitud GET
                    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                    $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
                    $sort = isset($_GET['sort']) ? $_GET['sort'] : null;
    
                    // Validar los parámetros
                    if ($page < 1) $page = 1;
                    if ($size < 1) $size = 5;

                    // Obtiene las direcciones de un proveedor en la base de datos.
                    $response = $proveedorDireccionBusiness->getPaginatedDireccionesByProveedor($proveedorID, $page, $size, $sort, $onlyActiveOrInactive, $deleted);
                    break;
            }
        } else {
            // Si los datos no son validos, se devuelve un mensaje de error
            $response['success'] = $check['is_valid'];
            $response['message'] = $check['message'];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

?>