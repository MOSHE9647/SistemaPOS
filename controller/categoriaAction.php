<?php
    include __DIR__ . '/../service/categoriaBusiness.php';
    require_once __DIR__ . '/../utils/Utils.php';

    // Función para validar los datos de la categoría
    function validarDatosCategoria($nombre, $estado) {
        $errors = [];

        if (empty($nombre) || is_numeric($nombre)) {
            $errors[] = "El campo 'Nombre' no puede estar vacío o ser numérico.";
        }
        if (!isset($estado) || ($estado != '1' && $estado != '0')) {
            $errors[] = "El campo 'Estado' debe ser 1 (activo) o 0 (inactivo).";
        }

        return $errors;
    }

    $response = [];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $accion = $_POST['accion'];
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : "";
        $estado = isset($_POST['estado']) ? $_POST['estado'] : "";

        $categoriaBusiness = new CategoriaBusiness();

        if ($accion == 'eliminar') {
            if (empty($id) || !is_numeric($id)) {
                $response['success'] = false;
                $response['message'] = "El ID no puede estar vacío.";
            } else {
                $result = $categoriaBusiness->deleteCategoria($id);
                $response['success'] = $result["success"];
                $response['message'] = $result["message"];
            }
        } else {
            $validationErrors = validarDatosCategoria($nombre, $estado);

            if (empty($validationErrors)) {
                if ($accion == 'insertar') {
                    $categoria = new Categoria(null, $nombre, $estado);
                    $result = $categoriaBusiness->insertCategoria($categoria);
                    $response['success'] = $result["success"];
                    $response['message'] = $result["message"];
                } elseif ($accion == 'actualizar') {
                    $categoria = new Categoria($id, $nombre, $estado);
                    $result = $categoriaBusiness->updateCategoria($categoria);
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

        $categoriaBusiness = new CategoriaBusiness();
        $result = $categoriaBusiness->getPaginatedCategorias($page, $size, $sort);
        
        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    }
?>
