<?php

    require_once dirname(__DIR__, 1) . "/service/clienteBusiness.php";

    $response = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Acción que se va a realizar
        $accion = $_POST['accion'] ?? "";
        if (empty($accion)) {
            $response = [
                'success' => false,
                'message' => "No se ha especificado una acción."
            ];
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }

        // Datos del Cliente recibidos en la solicitud
        $clienteID = intval($_POST['id'] ?? -1);
        $nombre = $_POST['nombre'] ?? "No Definido";
        $alias = $_POST['alias'] ?? "No Definido";

        // Datos del Telefono recibidos en la solicitud
        $telefonoID = intval($_POST['telefono'] ?? -1);
        $tipo = $_POST['tipo'] ?? "";
        $codigo = $_POST['codigo'] ?? "";
        $numero = $_POST['numero'] ?? "";

        // Crea el objeto Telefono con los datos recibidos
        $telefono = new Telefono($telefonoID, $tipo, $codigo, $numero);

        // Crea el service del cliente y verifica que los datos sean correctos
        $clienteBusiness = new ClienteBusiness();
        $cliente = new Cliente($clienteID, $nombre, $alias, $telefono);
        $checkCliente = $clienteBusiness->validarCliente($cliente, $accion !== 'eliminar', $accion === 'insertar');

        // Si los datos son válidos se realiza acción correspondiente
        if ($checkCliente['is_valid']) {
            switch ($accion) {
                case 'insertar':
                    $response = $clienteBusiness->insertTBCliente($cliente);
                    break;
                case 'actualizar':
                    $response = $clienteBusiness->updateTBCliente($cliente);
                    break;
                case 'eliminar':
                    $response = $clienteBusiness->deleteTBCliente($clienteID);
                    break;
                default:
                    http_response_code(400);
                    $response = [
                        'success' => false,
                        'message' => "Acción no válida."
                    ];
                    break;
            }
        } else {
            $response = [
                'success' => false,
                'message' => $checkCliente['message']
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
    
    else if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $accion = isset($_GET['accion']) ? $_GET['accion'] : "";
        $deleted = isset($_GET['deleted']) ? boolval($_GET['deleted']) : false;
        $onlyActive = isset($_GET['filter']) ? boolval($_GET['filter']) : true;

        $clienteBusiness = new ClienteBusiness();
        switch ($accion) {
            case 'all':
                $response = $clienteBusiness->getAllTBCliente($onlyActive, $deleted);
                break;
            case 'id':
                $id = isset($_GET['id']) ? intval($_GET['id']) : -1;
                $response = $clienteBusiness->getClienteByID($id, $onlyActive, $deleted);
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

                $response = $clienteBusiness->getPaginatedClientes($search, $page, $size, $sort, $onlyActive, $deleted);
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