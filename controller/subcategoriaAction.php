<?php
    require_once __DIR__ . '/../service/subcategoriaBusiness.php';
    require_once __DIR__ . '/../utils/Utils.php';

    $response = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $accion = $_POST['accion'];
        $id_subcategoria = isset($_POST['id']) ? $_POST['id'] : -1;
        $nombre_subcategoria = isset($_POST['nombre']) ? $_POST['nombre']: "";
        $descripcion = isset($_POST['descripcion'])?$_POST['descripcion']:"";

        $subcategoriaBusiness = new SubcategoriaBusiness();

        $subcategoria = new Subcategoria($nombre_subcategoria, $descripcion,$id_subcategoria);
        Utils::writeLog(" Datos : [$nombre_subcategoria]  [$id_subcategoria]  [$descripcion]" );

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

        if (isset($_GET['accion']) && $_GET['accion'] === 'listarSubcategorias') {
            Utils::writeLog("Ingreso ",UTILS_LOG_FILE);
            $subcategoriaBusiness = new SubcategoriaBusiness();
            $result = $subcategoriaBusiness->getAllSubcategorias();
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
        
        $subcategoriaService = new SubcategoriaBusiness();
        $result = $subcategoriaService->getPaginatedSubcategorias($page, $size, $sort);
        
        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    }

?>