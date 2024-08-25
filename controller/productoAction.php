<?php
    include __DIR__ . '/../service/productoBusiness.php';

    $response = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Acción que se va a realizar
        $accion = isset($_POST['accion']) ? $_POST['accion'] : "";

        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : "";
        $precio = isset($_POST['precio']) ? $_POST['precio'] : 0;
        $cantidad = isset($_POST['cantidad']) ? $_POST['cantidad'] : 0;
        $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : "";
        $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : "";
        $codigo = isset($_POST['codigo']) ? $_POST['codigo'] : "";

        // Se crea el Service para las operaciones
        $productoBusiness = new ProductoBusiness();

        // Crea y verifica que los datos del producto sean correctos
        $producto = new Producto($nombre, $precio, $cantidad, $fecha, $codigo, $id, $descripcion);
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

        if (isset($_GET['accion']) && $_GET['accion'] === 'listarProductos') {
            $productoBusiness = new ProductoBusiness();
            $result = $productoBusiness->getAllProductos();
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

        $ProductoBusiness = new ProductoBusiness();
        $result = $ProductoBusiness->getPaginatedProductos($page, $size, $sort);
        
        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    }

?>