<?php
    require_once __DIR__ . '/../service/tipoCompraBusiness.php';

    $response = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Acción que se va a realizar
        $accion = isset($_POST['accion']) ? $_POST['accion'] : "";

        // Datos recibidos en la solicitud (Form)
        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : "";
        $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : "";

        // Se crea el Service para las operaciones
        $tipoCompraBusiness = new TipoCompraBusiness();

        // Crea y verifica que los datos del tipo de compra sean correctos
        $tipoCompra = new TipoCompra($nombre, $descripcion, $id);
        $check = $tipoCompraBusiness->validarTipoCompra($tipoCompra, $accion != 'eliminar'); //<- Indica si se validan (o no) los campos además del ID

        // Si los datos son válidos se realiza acción correspondiente
        if ($check['is_valid']) {
            switch ($accion) {
                case 'insertar':
                    // Inserta el tipo de compra en la base de datos
                    $response = $tipoCompraBusiness->insertTipoCompra($tipoCompra);
                    break;
                case 'actualizar':
                    // Actualiza la info del tipo de compra en la base de datos
                    $response = $tipoCompraBusiness->updateTipoCompra($tipoCompra);
                    break;
                case 'eliminar':
                    // Elimina el tipo de compra de la base de datos
                    $response = $tipoCompraBusiness->deleteTipoCompra($tipoCompra->getTipoCompraID());
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
        // Obtener parámetros de la solicitud GET
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : null;

        // Validar los parámetros
        if ($page < 1) $page = 1;
        if ($size < 1) $size = 5;

        $tipoCompraBusiness = new TipoCompraBusiness();
        $result = $tipoCompraBusiness->getPaginatedTipoCompras($page, $size, $sort);
        
        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    }
?>
