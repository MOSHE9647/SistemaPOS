<?php
    require_once __DIR__ . '/../service/categoriaBusiness.php';
    require_once __DIR__ . '/../utils/Utils.php';

    $response = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $accion = $_POST['accion'];
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : "";
        $descripcion = isset($_POST['descripcion'])? $_POST['descripcion']:"";

        $categoriaBusiness = new CategoriaBusiness();
        $categoria = new Categoria($id,$nombre,$descripcion);

        switch($accion){
            case 'eliminar':
                $response = $categoriaBusiness->deleteTBCategoria($categoria);
                break;
            case 'insertar':
                $response = $categoriaBusiness->insertTBCategoria($categoria);
                break;
            case 'actualizar':
                $response = $categoriaBusiness->updateTBCategoria($categoria);
                break;
            default:
                $response['success'] = false;
                $response['message'] = "Acción no válida.";
                break;
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        
        if (isset($_GET['accion']) && $_GET['accion'] === 'listarCategorias') {
            Utils::writeLog("Ingreso ",UTILS_LOG_FILE);
            $categoriaBusiness = new CategoriaBusiness();
            $result = $categoriaBusiness->getAllTBCategoria();
            header('Content-Type: application/json');
            echo json_encode($result);
            exit();
        }
        // Obtener parámetros de la solicitud GET
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : null;

        // Validar los parámetros
        if ($page < 1) $page = 1;
        if ($size < 1) $size = 5;

        $categoriaBusiness = new CategoriaBusiness();
        $result = $categoriaBusiness->getPaginatedCategorias($page, $size, $sort);
        
        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    }
?>
