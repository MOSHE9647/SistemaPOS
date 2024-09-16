<?php

    require_once __DIR__ . "/../service/usuarioBusiness.php";

    $response = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Acción que se va a realizar
        $accion = isset($_POST['accion']) ? $_POST['accion'] : "";

        // Datos recibidos en la solicitud (Form)
        $id = isset($_POST['id']) ? intval($_POST['id']) : -1;
        $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : "";
        $apellido1 = isset($_POST['apellido1']) ? $_POST['apellido1'] : "";
        $apellido2 = isset($_POST['apellido2']) ? $_POST['apellido2'] : "";
        $correo = isset($_POST['correo']) ? $_POST['correo'] : "";
        $password = isset($_POST['password']) ? $_POST['password'] : "";
        $rolID = isset($_POST['rol']) ? intval($_POST['rol']) : -1;

        // Se crea el Service para las operaciones
        $usuarioBusiness = new UsuarioBusiness();

        // Crea y verifica que los datos del usuario sean correctos
        $usuario = new Usuario($id, $nombre, $apellido1, $apellido2, $correo, $password, $rolID);
        $check = $usuarioBusiness->validarUsuario($usuario, $accion != 'eliminar', $accion == 'insertar'); //<- Indica si se validan (o no) los campos además del ID

        // Si los datos son válidos se realiza acción correspondiente
        if ($check['is_valid']) {
            switch ($accion) {
                case 'insertar':
                    // Inserta el usuario en la base de datos
                    $response = $usuarioBusiness->insertTBUsuario($usuario);
                    break;
                case 'actualizar':
                    // Actualiza la info del usuario en la base de datos
                    if ($password === null || empty($password)) {
                        $result = $usuarioBusiness->getUsuarioByID($id, false);
                        if (!$result['success']) {
                            $response = $result;
                            break;
                        }
                        
                        $usuarioEnBD = $result['usuario'];
                        $usuario->setUsuarioPassword($usuarioEnBD->getUsuarioPassword());
                    }
                    $response = $usuarioBusiness->updateTBUsuario($usuario);
                    break;
                case 'eliminar':
                    // Elimina el usuario de la base de datos
                    $response = $usuarioBusiness->deleteTBUsuario($id);
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

        $usuarioBusiness = new UsuarioBusiness();
        switch ($accion) {
            case 'todos':
                $response = $usuarioBusiness->getAllTBUsuario($deleted, $onlyActiveOrInactive);
                break;
            case 'id':
                $usuarioID = isset($_GET['id']) ? intval($_GET['id']) : -1;
                $response = $usuarioBusiness->getUsuarioByID($usuarioID);
                break;
            default:
                // Obtener parámetros de la solicitud GET
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
                $sort = isset($_GET['sort']) ? $_GET['sort'] : null;

                // Validar los parámetros
                if ($page < 1) $page = 1;
                if ($size < 1) $size = 5;

                $response = $usuarioBusiness->getPaginatedUsuarios($page, $size, $sort, $onlyActiveOrInactive, $deleted);
                break;
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

?>