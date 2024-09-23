<?php
    require_once __DIR__ . '/../service/ProductoBusiness.php';

    $response = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Acción que se va a realizar
        $accion = $_POST['accion'];

        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        $codigobarrasid = isset($_POST['codigobarrasnumero']) ? $_POST['codigobarrasnumero'] : 0;
        $productonombre = isset($_POST['productonombre']) ? $_POST['productonombre'] : "";
        $productopreciocompra = isset($_POST['productopreciocompra']) ? $_POST['productopreciocompra'] : 0.00;
        $productoporcentajeganancia = isset($_POST['productoporcentajeganancia']) ? $_POST['productoporcentajeganancia'] : 0.00;
        $productodescripcion = isset($_POST['productodescripcion']) ? $_POST['productodescripcion'] : "";
        $categoriaid = isset($_POST['categorianombre']) ? $_POST['categorianombre'] : 0;
        $subcategoriaid = isset($_POST['subcategorianombre']) ? $_POST['subcategorianombre'] : 0;
        $marcaid = isset($_POST['marcanombre']) ? $_POST['marcanombre'] : 0;
        $presentacionid = isset($_POST['presentacionnombre']) ? $_POST['presentacionnombre'] : 0;
        $productoimagen = isset($_POST['productoimagen']) ? $_POST['productoimagen'] : '';
        $productoestado = isset($_POST['productoestado']) ? $_POST['productoestado'] : 1;

        // Se crea el Service para las operaciones
        $productoBusiness = new ProductoBusiness();

        // Crea y verifica que los datos del producto sean correctos
        $producto = new Producto(
            $id, $codigobarrasid, $productonombre, $productopreciocompra, $productoporcentajeGanancia, $productodescripcion,
            $categoriaid, $subcategoriaid, $marcaid, $presentacionid, $productoimagen, $productoestado
        );
        $check = $productoBusiness->validarProducto($producto, $accion != 'eliminar'); //<- Indica si se validan (o no) los campos además del ID
        if ($check['is_valid']) {
            switch ($accion) {
                case 'insertar':
                    // Inserta el producto en la base de datos
                    $response = $productoBusiness->insertTBProducto($producto, $productoimagen);
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

        if (isset($_GET['accion']) && $_GET['accion'] === 'listarCompraDetalleProductos') {
            $productoBusiness = new ProductoBusiness();
            $result = $productoBusiness->getAllTBCompraDetalleProducto();
            header('Content-Type: application/json');
            echo json_encode($result);
            exit();
        }
        
        // Obtener parámetros de la solicitud GET
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
    
        // Validar los parámetros
        if ($page < 1) $page = 1;
        if ($size < 1) $size = 5;

        $productoBusiness = new ProductoBusiness();
        $result = $productoBusiness->getPaginatedProductos($page, $size);
        
        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    }
?>
