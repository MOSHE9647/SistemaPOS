<?php

    require_once __DIR__ . '/../service/proveedorTelefonoBusiness.php';

    $response = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Acción que se va a realizar
        $accion = isset($_POST['accion']) ? $_POST['accion'] : "";

        // Datos recibidos en la solicitud (Form)
        $proveedorID = isset($_POST['proveedor']) ? intval($_POST['proveedor']) : null;
        $telefonoID = isset($_POST['telefono']) ? intval($_POST['telefono']) : null;

        // Se crea el Service para las operaciones
        $proveedorTelefonoBusiness = new ProveedorTelefonoBusiness();

        // Crea y verifica que los ID del Proveedor y del Telefono sean correctos
        $check = $proveedorTelefonoBusiness->validarProveedorTelefono($proveedorID, $telefonoID);

        // Si los datos son válidos se realiza acción correspondiente
        if ($check['is_valid']) {
            switch ($accion) {
                case 'agregar':
                    // Agrega un teléfono a un proveedor en la base de datos.
                    $response = $proveedorTelefonoBusiness->addTelefonoToProveedor($proveedorID, $telefonoID);
                    break;
                case 'eliminar':
                    // Elimina un teléfono de un proveedor en la base de datos.
                    $response = $proveedorTelefonoBusiness->removeTelefonoFromProveedor($proveedorID, $telefonoID);
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
        $proveedorTelefonoBusiness = new ProveedorTelefonoBusiness();

        // Crea y verifica que el ID del Proveedor sea correcto
        $check = $proveedorTelefonoBusiness->validarProveedorTelefono($proveedorID, null, false);

        if ($check['is_valid']) {
            switch ($accion) {
                case 'todo':
                    // Obtiene los teléfonos de un proveedor en la base de datos.
                    $response = $proveedorTelefonoBusiness->getTelefonosByProveedor($proveedorID, true);
                    break;
                default:
                    // Obtener parámetros de la solicitud GET
                    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                    $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
                    $sort = isset($_GET['sort']) ? $_GET['sort'] : null;
    
                    // Validar los parámetros
                    if ($page < 1) $page = 1;
                    if ($size < 1) $size = 5;
    
                    // Obtiene los teléfonos de un proveedor en la base de datos.
                    $response = $proveedorTelefonoBusiness->getPaginatedTelefonosByProveedor($proveedorID, $page, $size, $sort, $onlyActiveOrInactive, $deleted);
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