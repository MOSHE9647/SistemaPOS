<?php

    require_once __DIR__ . '/../service/direccionBusiness.php';

    $response = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Acción que se va a realizar
        $accion = isset($_POST['accion']) ? $_POST['accion'] : "";

        // Datos recibidos en la solicitud (Form)
        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        $provincia = isset($_POST['provincia']) ? $_POST['provincia'] : "";
        $canton = isset($_POST['canton']) ? $_POST['canton'] : "";
        $distrito = isset($_POST['distrito']) ? $_POST['distrito'] : "";
        $barrio = isset($_POST['barrio']) ? $_POST['barrio'] : "";
        $sennas = isset($_POST['sennas']) ? $_POST['sennas'] : "";
        $distancia = isset($_POST['distancia']) ? $_POST['distancia'] : 0;

        // Se crea el Service para las operaciones
        $direccionBusiness = new DireccionBusiness();

        // Crea y verifica que los datos de la direccion sean correctos
        $direccion = new Direccion($id, $provincia, $canton, $distrito, $barrio, $sennas, $distancia);
        $check = $direccionBusiness->validarDireccion($direccion, $accion != 'eliminar', $accion == 'insertar'); //<- Indica si se validan (o no) los campos además del ID

        // Si los datos son válidos se realiza acción correspondiente
        if ($check['is_valid']) {
            switch ($accion) {
                case 'insertar':
                    // Inserta la direccion en la base de datos
                    $response = $direccionBusiness->insertTBDireccion($direccion);
                    break;
                case 'actualizar':
                    // Actualiza la info de la direccion en la base de datos
                    $response = $direccionBusiness->updateTBDireccion($direccion);
                    break;
                case 'eliminar':
                    // Elimina la direccion de la base de datos
                    $response = $direccionBusiness->deleteTBDireccion($id);
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

        $direccionBusiness = new DireccionBusiness();
        switch ($accion) {
            case 'todos':
                $response = $direccionBusiness->getAllDirecciones($deleted, $onlyActiveOrInactive);
                break;
            case 'id':
                $direccionID = $direccionID = isset($_GET['id']) ? $_GET['id'] : -1;
                $response = $direccionBusiness->getDireccionByID($direccionID);
                break;
            default:
                // Obtener parámetros de la solicitud GET
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
                $sort = isset($_GET['sort']) ? $_GET['sort'] : null;

                // Validar los parámetros
                if ($page < 1) $page = 1;
                if ($size < 1) $size = 5;

                // Obtiene la lista (paginada) de direcciones
                $response = $direccionBusiness->getPaginatedDirecciones($page, $size, $sort, $onlyActiveOrInactive, $deleted);
                break;
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

?>