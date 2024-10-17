<?php
    require_once __DIR__ . '/../service/categoriaBusiness.php';

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
        $id = isset($_POST['id']) ? $_POST['id'] : -1;
        $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : "";
        $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : "";

        // Se crea el Service para las operaciones
        $categoriaBusiness = new CategoriaBusiness();

        // Crea y verifica que los datos de la categoria sean correctos
        $categoria = new Categoria($id, $nombre, $descripcion);
        $check = $categoriaBusiness->validarCategoria($categoria, $accion != 'eliminar', $accion == 'insertar'); //<- Indica si se validan (o no) los campos además del ID

        if ($check['is_valid']) {
            switch ($accion) {
                case 'insertar':
                    $response = $categoriaBusiness->insertTBCategoria($categoria);
                    break;
                case 'actualizar':
                    $response = $categoriaBusiness->updateTBCategoria($categoria);
                    break;
                case 'eliminar':
                    $response = $categoriaBusiness->deleteTBCategoria($id);
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

        $categoriaBusiness = new CategoriaBusiness();
        switch ($accion) {
            case 'all':
                $response = $categoriaBusiness->getAllTBCategoria($onlyActive, $deleted);
                break;
            case 'id':
                $categoriaID = isset($_GET['id']) ? intval($_GET['id']) : -1;
                $response = $categoriaBusiness->getCategoriaByID($categoriaID, $onlyActive, $deleted);
                break;
            default:
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
                $sort = isset($_GET['sort']) ? $_GET['sort'] : null;

                // Validar los parámetros
                if ($page < 1) $page = 1;
                if ($size < 1) $size = 5;

                $response = $categoriaBusiness->getPaginatedCategorias($page, $size, $sort, $onlyActive, $deleted);
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