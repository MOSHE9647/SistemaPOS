<?php
    require_once __DIR__ . '/../service/subcategoriaBusiness.php';
    require_once __DIR__ . '/../service/categoriaBusiness.php'; // Agregar el Business de categorías
    require_once __DIR__ . '/../utils/Utils.php';

    $response = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $accion = $_POST['accion'];
        $id_subcategoria = isset($_POST['id']) ? $_POST['id'] : -1;
        $nombre_subcategoria = isset($_POST['nombre']) ? $_POST['nombre']: "";
        $descripcion = isset($_POST['descripcion'])?$_POST['descripcion']:"";
        $categoriaid = isset($_POST['categoria'])?$_POST['categoria']:0;
        
        $subcategoriaBusiness = new SubcategoriaBusiness();
        $subcategoria = new Subcategoria($nombre_subcategoria, $categoriaid , $descripcion, $id_subcategoria);

        Utils::writeLog("$nombre_subcategoria, $categoriaid, $descripcion, $id_subcategoria", UTILS_LOG_FILE);

        switch($accion){
            case 'eliminar':
                $response = $subcategoriaBusiness->deleteSubcategoria($subcategoria);
                break;
            case 'insertar':
                $response = $subcategoriaBusiness->insertSubcategoria($subcategoria);
                break;
            case 'actualizar':
                $response = $subcategoriaBusiness->updateSubcategoria($subcategoria);
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
        $accion = isset($_GET['accion']) ? $_GET['accion'] : "";

        $subcategoriaService = new SubcategoriaBusiness();
        switch ($accion) {
            case 'subcategoria-categoria':
                $categoriaID = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;
                $response = $subcategoriaService->getAllSubcategoriasByCategoriaID($categoriaID);
                break;
            case 'listarSubcategorias':
                $response = $subcategoriaService->getAllSubcategorias();
                break;
            default:
                // Obtener parámetros de la solicitud GET para paginación
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
                $sort = isset($_GET['sort']) ? $_GET['sort'] : null;

                // Validar los parámetros
                if ($page < 1) $page = 1;
                if ($size < 1) $size = 5;

                $response = $subcategoriaService->getPaginatedSubcategorias($page, $size, $sort);
                break;
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
?>
