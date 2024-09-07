<?php
    include __DIR__ . '/../service/proveedorBusiness.php';

    $response = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Acción que se va a realizar
        $accion = isset($_POST['accion']) ? $_POST['accion'] : "";
        // Datos recibidos en la solicitud (Form)
        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : "";
        $email = isset($_POST['email']) ? $_POST['email'] : "";
        $telefono = isset($_POST['telefono']) ? $_POST['telefono'] : "";

        // Se crea el Service para las operaciones
        $proveedorBusiness = new ProveedorBusiness();

        // Crea y verifica que los datos del proveedor sean correctos
        $proveedor = new Proveedor($nombre, $email, $fecha, $id, $telefono);
        $check = $proveedorBusiness->validarProveedor($proveedor, $accion != 'eliminar'); //<- Indica si se validan (o no) los campos además del ID

        // Si los datos son válidos se realiza acción correspondiente
        if ($check['is_valid']) {
            switch ($accion) {
                case 'insertar':
                    // Inserta el proveedor en la base de datos
                    $response = $proveedorBusiness->insertTBProveedor($proveedor);
                    break;
                case 'actualizar':
                    // Actualiza la info del proveedor en la base de datos
                    $response = $proveedorBusiness->updateTBProveedor($proveedor);
                    break;
                case 'eliminar':
                    // Elimina al proveedor de la base de datos
                    $response = $proveedorBusiness->deleteTBProveedor($proveedor);
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
        if (isset($_GET['accion']) && $_GET['accion'] === 'listarProveedores') {
            $proveedorBusiness = new ProveedorBusiness();
            $result = $proveedorBusiness->getAllTBProveedor();
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

        $proveedorBusiness = new ProveedorBusiness();
        $result = $proveedorBusiness->getPaginatedProveedores($page, $size, $sort);
        
        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    }

?>
