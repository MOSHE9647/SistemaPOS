<?php
    include __DIR__ . '/../service/ProductoBusiness.php';
    require_once __DIR__ . '/../utils/Utils.php';

    // Función para validar los datos del impuesto
    function validarDatos($nombre, $preciounitarioproducto, $fecha) {
        $errors = [];
        if(empty($nombre) || is_numeric($nombre) || $nombre == null){
            $errors[] = "El campo 'Nombre' no puede estar vacío o ser numérico.";
        }    
        if(empty($preciounitarioproducto) || $preciounitarioproducto == null){
            $errors[] = "El precio unitario no puede estar vacio o tener caracteres no numericos";
        }else{
            if($preciounitarioproducto < 0){
                $errors[] = "El precio unitaro debe ser un valor positivo";
            }
        }
        if (empty($fecha) || !Utils::validar_fecha($fecha)) {
            $errors[] = "El campo 'Fecha de adquisicion' no es válido.";
        }
        return $errors;
    }


    $response = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $accion = $_POST['accion'];

        $idproducto = isset($_POST['id']) ? $_POST['id'] : null;
        $nombreproducto = isset($_POST['nombre']) ? $_POST['nombre'] : "";
        $preciounitarioproducto = isset($_POST['precio']) ? $_POST['precio'] : "";
        $cantidadproducto = isset($_POST['cantidad']) ? $_POST['cantidad'] : "";
        $fechaadquisicionproducto = isset($_POST['fecha']) ? $_POST['fecha'] : "";
        $descripcionproducto = isset($_POST['descripcion']) ? $_POST['descripcion'] : "";

        $ProductoBusiness = new ProductoBusiness();

        if ($accion == 'eliminar') {
            if (empty($idproducto) || !is_numeric($idproducto)) {
                $response['success'] = false;
                $response['message'] = "El ID no puede estar vacío.";
            } else {
                $result = $ProductoBusiness->deleteTBProducto($idproducto);
                $response['success'] = $result["success"];
                $response['message'] = $result["message"];
            }
        } else {
            $validationErrors = validarDatos($nombreproducto, $preciounitarioproducto, $fechaadquisicionproducto);
            if (empty($validationErrors)) {
                $producto = new Producto($nombreproducto,$preciounitarioproducto,$cantidadproducto,$fechaadquisicionproducto,$idproducto,$descripcionproducto);
                if ($accion == 'insertar') {
                    $result = $ProductoBusiness->insertTBProducto($producto);
                    $response['success'] = $result["success"];
                    $response['message'] = $result["message"];
                } elseif ($accion == 'actualizar') {
                    $result = $ProductoBusiness->updateTBProducto($producto);
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

        $ProductoBusiness = new ProductoBusiness();
        $result = $ProductoBusiness->getPaginatedProductos($page, $size, $sort);
        
        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    }

?>