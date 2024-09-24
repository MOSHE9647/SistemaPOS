<?php

    require_once dirname(__DIR__, 1) . "/service/clienteBusiness.php";

    $response = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Acción que se va a realizar
        $accion = isset($_POST['accion']) ? $_POST['accion'] : "";
        if (!$accion) {
            $response['success'] = false;
            $response['message'] = "No se ha especificado una acción.";

            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }

        // Datos recibidos en la solicitud (Form)
        $id = isset($_POST['id']) ? intval($_POST['id']) : -1;
        $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : "";
        $telefonoID = isset($_POST['telefono']) ? intval($_POST['telefono']) : -1;

        // Se crea el Service para las operaciones
        $clienteBusiness = new ClienteBusiness();

        // Crea y verifica que los datos del cliente sean correctos
        $cliente = new Cliente($id, $nombre, $telefonoID);
        $check = $clienteBusiness->validarCliente($cliente, $accion != 'eliminar', $accion == 'insertar'); //<- Indica si se validan (o no) los campos además del ID

        // Si los datos son válidos se realiza acción correspondiente
        if ($check['is_valid']) {
            switch ($accion) {
                case 'insertar':
                    // Inserta el cliente en la base de datos
                    $response = $clienteBusiness->insertTBCliente($cliente);
                    break;
                case 'actualizar':
                    // Actualiza la info del cliente en la base de datos
                    $response = $clienteBusiness->updateTBCliente($cliente);
                    break;
                case 'eliminar':
                    // Elimina el cliente de la base de datos
                    $response = $clienteBusiness->deleteTBCliente($id);
                    break;
                default:
                    // Error en caso de que la accion no sea válida
                    http_response_code(400);
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
    
    else if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $accion = isset($_GET['accion']) ? $_GET['accion'] : "";
        $deleted = isset($_GET['deleted']) ? boolval($_GET['deleted']) : false;
        $onlyActiveOrInactive = isset($_GET['filter']) ? boolval($_GET['filter']) : true;

        $clienteBusiness = new ClienteBusiness();
        switch ($accion) {
            case 'all':
                $response = $clienteBusiness->getAllTBCliente($onlyActiveOrInactive, $deleted);
                break;
            case 'id':
                $id = isset($_GET['id']) ? intval($_GET['id']) : -1;
                $response = $clienteBusiness->getClienteByID($id);
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

                $response = $clienteBusiness->getPaginatedClientes($search, $page, $size, $sort, $onlyActiveOrInactive, $deleted);
                break;
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
    
    else {
        $response['success'] = false;
        $response['message'] = "Método no permitido (" . $_SERVER["REQUEST_METHOD"] . ").";

        http_response_code(405);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

?>