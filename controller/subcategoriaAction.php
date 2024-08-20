<?php
    include __DIR__ . '/../service/subcategoriaBusiness.php';
    require_once __DIR__ . '/../utils/Utils.php';

    // Función para validar los datos del impuesto
    function validarDatos($nombre) {
        $errors = [];
        if(empty($nombre) || is_numeric($nombre) || $nombre == null){
            $errors[] = "¡El campo 'Nombre' no puede estar vacio o ser numerico!";
        }    
        return $errors;
    }
    
    $response = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $accion = $_POST['accion'];

        $id_subcategoria = isset($_POST['id']) ? $_POST['id'] : null;
        $nombre_subcategoria = $_POST['nombre'];

        $subcategoriaBusiness = new SubcategoriaBusiness();

        if ($accion == 'eliminar') {
            if (empty($id_subcategoria) || !is_numeric($id_subcategoria)) {
                $response['success'] = false;
                $response['message'] = "El ID no puede estar vacío.";
            } else {
                $result = $subcategoriaBusiness->deleteSubcategoria($id_subcategoria);
                $response['success'] = $result["success"];
                $response['message'] = $result["message"];
            }
        } else {
            $validationErrors = validarDatos($nombre_subcategoria);
            if (empty($validationErrors)) {
                $subcategoria = new Subcategoria($nombre_subcategoria,$id_subcategoria);
                if ($accion == 'insertar') {
                    // Utils::writeLog("Action insert: ".$subcategoria->getSubcategoriaNombre()."  ".$subcategoria->getSubcategoriaId());
                    $result = $subcategoriaBusiness->insertSubcategoria($subcategoria);
                    $response['success'] = $result["success"];
                    $response['message'] = $result["message"];
                } elseif ($accion == 'actualizar') {
                    // Utils::writeLog("Action update: ".$subcategoria->getSubcategoriaNombre()."  ".$subcategoria->getSubcategoriaId());
                    $result = $subcategoriaBusiness->updateSubcategoria($subcategoria);
                    $response['success'] = $result["success"];
                    $response['message'] = $result["message"];
                } else {
                    $response['success'] = false;
                    $response['message'] = "Acción no válida.";
                }
            } else {
                $response['success'] = false;
                $response['message'] = implode(' ', $validationErrors);
            }
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
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