<?php
    require_once __DIR__ . '/../service/productoBusiness.php';

    $response = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Acción que se va a realizar
        $accion = isset($_POST['accion']) ? $_POST['accion'] : "";

        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : "";
        $precioCompra = isset($_POST['precioCompra']) ? $_POST['precioCompra'] : 0;
        $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : "";
        $codigoBarrasID = isset($_POST['codigoBarrasID']) ? $_POST['codigoBarrasID'] : "";
        $foto = isset($_POST['foto']) ? $_POST['foto'] : '';
        $ganancia = isset($_POST['ganancia']) ? $_POST['ganancia'] : '';

        // Se crea el Service para las operaciones
        $productoBusiness = new ProductoBusiness();

        // Crea y verifica que los datos del producto sean correctos
        $producto = new Producto($nombre, $precioCompra, $codigoBarrasID, $foto, $ganancia, $id, $descripcion);
        $check = $productoBusiness->validarProducto($producto, $accion != 'eliminar'); //<- Indica si se validan (o no) los campos además del ID
        if ($check['is_valid']) {
            switch ($accion) {
                case 'insertar':
                    // Inserta el producto en la base de datos
                    $response = $productoBusiness->insertTBProducto($producto);
                    break;
                case 'actualizar':
                    // Actualiza la info del producto en la base de datos
                    $response = $productoBusiness->updateTBProducto($producto);
                    break;
                case 'eliminar':
                    $response = $productoBusiness->deleteTBProducto($producto);
                    // Elimina el producto de la base de datos
                    break;
                default:
                    // Error en caso de que la acción no sea válida
                    $response['success'] = false;
                    $response['message'] = "Acción no válida.";
                    break;
            }
        } else {
            // Si los datos no son válidos, se devuelve un mensaje de error
            $response['success'] = $check['is_valid'];
            $response['message'] = $check['message'];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        if (isset($_GET['accion']) && $_GET['accion'] === 'listarProductos') {
            $productoBusiness = new ProductoBusiness();
            $result = $productoBusiness->getAllTBProducto();
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

        $productoBusiness = new ProductoBusiness();
        $result = $productoBusiness->getPaginatedProductos($page, $size, $sort);
        
        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    }
?>
