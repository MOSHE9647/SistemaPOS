<?php

    include __DIR__ . '/../service/productoCategoriaBusiness.php';

    function validarDatos($productoID, $categoriaID) {
        $errors = [];

        if ($productoID === null || !is_numeric($productoID) || $productoID <= 0) {
            $errors[] = "La información del producto no es válida.";
        }

        if ($categoriaID === null || !is_numeric($categoriaID) || $categoriaID <= 0) {
            $errors[] = "La información de la categoría no es válida.";
        }

        return $errors;
    }

    $response = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Acción que se va a realizar
        $accion = $_POST['accion'];

        // Datos del Form
        $productoID = isset($_POST['productoID']) ? $_POST['productoID'] : 0;
        $categoriaID = isset($_POST['categoriaID']) ? $_POST['categoriaID'] : 0;

        // Se valida que los ID no estén vacíos
        $validationErrors = validarDatos($productoID, $categoriaID);
        if (empty($validationErrors)) {
            $productoCategoriaBusiness = new ProductoCategoriaBusiness();

            if ($accion == 'insertar') {
                $result = $productoCategoriaBusiness->addCategoriaToProducto($productoID, $categoriaID);
                $response['success'] = $result["success"];
                $response['message'] = $result["message"];
            } elseif ($accion == 'actualizar') {
                $result = $productoCategoriaBusiness->updateCategoriaOfProducto($productoID, $categoriaID);
                $response['success'] = $result["success"];
                $response['message'] = $result["message"];
            } elseif ($accion == 'eliminar') {
                $result = $productoCategoriaBusiness->removeCategoriaFromProducto($productoID, $categoriaID);
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

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

?>
