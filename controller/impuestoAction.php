<?php
    require_once dirname(__DIR__, 1) . '/service/impuestoBusiness.php';

    $response = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Acción que se va a realizar
        $accion = isset($_POST['accion']) ? $_POST['accion'] : "";
        if (empty($accion)) {
            $response['success'] = false;
            $response['message'] = "No se ha especificado una acción.";
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }

        // Datos recibidos en la solicitud (Form)
        $id =           isset($_POST['id'])             ? $_POST['id']          : -1;
        $nombre =       isset($_POST['nombre'])         ? $_POST['nombre']      : "";
        $valor =        isset($_POST['valor'])          ? $_POST['valor']       : 0.0;
        $descripcion =  isset($_POST['descripcion'])    ? $_POST['descripcion'] : "";
        $fechaInicio =  isset($_POST['fechaInicio'])    ? $_POST['fechaInicio'] : "";
        $fechaFin =     isset($_POST['fechaFin'])       ? $_POST['fechaFin']    : "";

        // Se crea el Service para las operaciones
        $impuestoBusiness = new ImpuestoBusiness();

        // Crea y verifica que los datos del impuesto sean correctos
        $impuesto = new Impuesto($id, $nombre, $valor, $descripcion, $fechaInicio, $fechaFin);
        $check = $impuestoBusiness->validarImpuesto($impuesto, $accion != 'eliminar', $accion == 'insertar'); //<- Indica si se validan (o no) los campos además del ID

        // Si los datos son válidos se realiza acción correspondiente
        if ($check['is_valid']) {
            switch ($accion) {
                case 'insertar':
                    // Inserta el impuesto en la base de datos
                    $response = $impuestoBusiness->insertTBImpuesto($impuesto);
                    break;
                case 'actualizar':
                    // Actualiza la info del impuesto en la base de datos
                    $response = $impuestoBusiness->updateTBImpuesto($impuesto);
                    break;
                case 'eliminar':
                    // Elimina el impuesto de la base de datos
                    $response = $impuestoBusiness->deleteTBImpuesto($id);
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
        $onlyActive = isset($_GET['filter']) ? boolval($_GET['filter']) : true;

        $impuestoBusiness = new ImpuestoBusiness();
        switch ($accion) {
            case 'all':
                $response = $impuestoBusiness->getAllTBImpuesto($onlyActive, $deleted);
                break;
            case 'id':
                $impuestoID = isset($_GET['id']) ? intval($_GET['id']) : -1;
                $response = $impuestoBusiness->getImpuestoByID($impuestoID, $onlyActive, $deleted);
                break;
            default:
                // Obtener parámetros de la solicitud GET
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
                $sort = isset($_GET['sort']) ? $_GET['sort'] : null;

                // Validar los parámetros
                if ($page < 1) $page = 1;
                if ($size < 1) $size = 5;

                $response = $impuestoBusiness->getPaginatedImpuestos($page, $size, $sort, $onlyActive, $deleted);
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