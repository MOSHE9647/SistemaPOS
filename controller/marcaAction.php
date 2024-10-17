<?php
    require_once __DIR__ . '/../service/marcaBusiness.php';

    $response = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Acción que se va a realizar
        $accion = isset($_POST['accion']) ? $_POST['accion'] : "";
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

        // Datos recibidos en la solicitud (Form)
        $id = isset($_POST['id']) ? $_POST['id'] : -1;
        $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : "";
        $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : "";

        // Se crea el Service para las operaciones
        $marcaBusiness = new MarcaBusiness();

        // Crea y verifica que los datos de la marca sean correctos
        $marca = new Marca($id, $nombre, $descripcion);
        $check = $marcaBusiness->validarMarca($marca, $accion != 'eliminar', $accion == 'insertar'); //<- Indica si se validan (o no) los campos además del ID

        if ($check['is_valid']) {
            switch ($accion) {
                case 'insertar':
                    $response = $marcaBusiness->insertTBMarca($marca);
                    break;
                case 'actualizar':
                    $response = $marcaBusiness->updateTBMarca($marca);
                    break;
                case 'eliminar':
                    $response = $marcaBusiness->deleteTBMarca($id);
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
                'message' => $check['message']
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

        $marcaBusiness = new MarcaBusiness();
        switch ($accion) {
            case 'all':
                $response = $marcaBusiness->getAllTBMarca($onlyActive, $deleted);
                break;
            case 'id':
                $marcaID = isset($_GET['id']) ? intval($_GET['id']) : -1;
                $response = $marcaBusiness->getMarcaByID($marcaID, $onlyActive, $deleted);
                break;
            default:
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
                $sort = isset($_GET['sort']) ? $_GET['sort'] : null;

                // Validar los parámetros
                if ($page < 1) $page = 1;
                if ($size < 1) $size = 5;

                $response = $marcaBusiness->getPaginatedMarcas($page, $size, $sort, $onlyActive, $deleted);
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