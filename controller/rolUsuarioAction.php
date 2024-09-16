<?php

    require_once __DIR__ . '/../service/rolUsuarioBusiness.php';

    $response = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Acción que se va a realizar
        $accion = isset($_POST['accion']) ? $_POST['accion'] : "";

        // Datos recibidos en la solicitud (Form)
        $id = isset($_POST['id']) ? $_POST['id'] : -1;
        $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : "";
        $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : "";

        // Se crea el Service para las operaciones
        $rolBusiness = new RolBusiness();

        // Crea y verifica que los datos del rol sean correctos
        $rol = new RolUsuario($id, $nombre, $descripcion);
        $check = $rolBusiness->validarRol($rol, $accion != 'eliminar', $accion == 'insertar'); //<- Indica si se validan (o no) los campos además del ID

        // Si los datos son válidos se realiza acción correspondiente
        if ($check['is_valid']) {
            switch ($accion) {
                case 'insertar':
                    // Inserta el rol en la base de datos
                    $response = $rolBusiness->insertTBRolUsuario($rol);
                    break;
                case 'actualizar':
                    // Actualiza la info del rol en la base de datos
                    $response = $rolBusiness->updateTBRolUsuario($rol);
                    break;
                case 'eliminar':
                    // Elimina el rol de la base de datos
                    $response = $rolBusiness->deleteTBRolUsuario($id);
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

        $rolBusiness = new RolBusiness();
        switch ($accion) {
            case 'todos':
                $response = $rolBusiness->getAllTBRolUsuario($onlyActiveOrInactive, $deleted);
                break;
            case 'id':
                $rolID = isset($_GET['id']) ? intval($_GET['id']) : -1;
                $response = $rolBusiness->getRolUsuarioByID($rolID);
                break;
            default:
                $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
                $sort = isset($_GET['sort']) ? $_GET['sort'] : null;

                // Validar los parámetros
                if ($page < 1) $page = 1;
                if ($size < 1) $size = 5;

                $response = $rolBusiness->getPaginatedRoles($page, $size, $sort, $onlyActiveOrInactive, $deleted);
                break;
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

?>