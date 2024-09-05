<?php

    include __DIR__ . '/../service/telefonoBusiness.php';
    include_once __DIR__ . '/../utils/Utils.php';

    $response = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Acción que se va a realizar
        $accion = isset($_POST['accion']) ? $_POST['accion'] : "";

        // Datos recibidos en la solicitud (Form)
        $id = isset($_POST['id']) ? $_POST['id'] : -1;
        $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : "";
        $codigo = isset($_POST['codigo']) ? $_POST['codigo'] : "";
        $numero = isset($_POST['numero']) ? $_POST['numero'] : "";
        $extension = isset($_POST['extension']) ? $_POST['extension'] : "";

        // Se crea el Service para las operaciones
        $telefonoBusiness = new TelefonoBusiness();

        // Crea y verifica que los datos de la direccion sean correctos
        $telefono = new Telefono($id, $tipo, $codigo, $numero, $extension);
        $check = $telefonoBusiness->validarTelefono($telefono, $accion != 'eliminar', $accion == 'insertar'); //<- Indica si se validan (o no) los campos además del ID

        // Si los datos son válidos se realiza acción correspondiente
        if ($check['is_valid']) {
            switch ($accion) {
                case 'insertar':
                    // Inserta el telefono en la base de datos
                    $response = $telefonoBusiness->insertTBTelefono($telefono);
                    break;
                case 'actualizar':
                    // Actualiza la info del telefono en la base de datos
                    $response = $telefonoBusiness->updateTBTelefono($telefono);
                    break;
                case 'eliminar':
                    // Elimina al telefono de la base de datos
                    $response = $telefonoBusiness->deleteTBTelefono($id);
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

        $telefonoBusiness = new TelefonoBusiness();
        switch ($accion) {
            case 'todos':
                $response = $telefonoBusiness->getAllTBTelefono($onlyActiveOrInactive, $deleted);
                break;
            case 'id':
                $telefonoID = $telefonoID = isset($_GET['id']) ? $_GET['id'] : -1;
                $response = $telefonoBusiness->getTelefonoByID($telefonoID);
                break;
            default:
                // Obtener parámetros de la solicitud GET
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
                $sort = isset($_GET['sort']) ? $_GET['sort'] : null;

                // Validar los parámetros
                if ($page < 1) $page = 1;
                if ($size < 1) $size = 5;

                $response = $telefonoBusiness->getPaginatedTelefonos($page, $size, $sort, $onlyActiveOrInactive, $deleted);
                break;
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

?>