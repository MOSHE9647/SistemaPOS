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

        if (isset($_GET['accion']) && $_GET['accion'] === 'listarSubcategorias') {
            Utils::writeLog("Ingreso a listarSubcategorias",UTILS_LOG_FILE);

            $subcategoriaBusiness = new SubcategoriaBusiness();
            $categoriaBusiness = new CategoriaBusiness();  // Crear instancia del Business de categorías

            // Obtener todas las categorías
            $categorias = $categoriaBusiness->getAllTBCategoria();

            // Obtener todas las subcategorías
            $subcategorias = $subcategoriaBusiness->getAllSubcategorias();

            // Asociar las subcategorías con sus categorías
            foreach ($categorias as &$categoria) {
                $categoria['subcategorias'] = array_filter($subcategorias, function($subcategoria) use ($categoria) {
                    return $subcategoria['categoriaId'] === $categoria['id'];  // Asociar por categoría ID
                });
            }

            // Enviar las categorías con subcategorías asociadas en la respuesta JSON
            header('Content-Type: application/json');
            echo json_encode(array('categorias' => $categorias));
            exit();
        }

        // Obtener parámetros de la solicitud GET para paginación
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
