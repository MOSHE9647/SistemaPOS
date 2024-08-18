<?php
    include __DIR__ . '/../service/loteBusiness.php';
    require_once __DIR__ . '/../utils/Utils.php';

    // Función para validar los datos del lote
    function validarDatos($codigo, $productoID, $cantidad, $precio, $proveedorID, $fechaIngreso, $fechaVencimiento) {
        $errors = [];

        if (empty($codigo)) {
            $errors[] = "El campo 'Código' no puede estar vacío.";
        }
        if (is_numeric($productoID) || $productoID <= '0') {
            $errors[] = "El campo 'ID del Producto'tiene que ser mayor que  0";
        }
        if (empty($cantidad) || !is_numeric($cantidad) || $cantidad <= 0) {
            $errors[] = "El campo 'Cantidad' debe ser mayor a 0.";
        }
        if (empty($precio) || !is_numeric($precio) || $precio <= 0) {
            $errors[] = "El campo 'Precio' debe ser mayor a 0.";
        }

        if (is_numeric($proveedorID) || $proveedorID <= '0') {
            $errors[] = "El campo 'ID del Proveedor'tiene que ser mayor que  0";
        }
        if (empty($fechaIngreso) || !Utils::validar_fecha($fechaIngreso)) {
            $errors[] = "El campo 'Fecha de Ingreso' no es válido.";
        }
        if (empty($fechaVencimiento) || !Utils::validar_fecha($fechaVencimiento)) {
            $errors[] = "El campo 'Fecha de Vencimiento' no es válido.";
        }

        return $errors;
    }

    $response = [];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $accion = $_POST['accion'];
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $codigo = isset($_POST['codigo']) ? $_POST['codigo'] : "";
        $productoID = isset($_POST['producto_id']) ? $_POST['producto_id'] : "";
        $cantidad = isset($_POST['cantidad']) ? $_POST['cantidad'] : "";
        $precio = isset($_POST['precio']) ? $_POST['precio'] : "";
        $proveedorID = isset($_POST['proveedor_id']) ? $_POST['proveedor_id'] : "";
        $fechaIngreso = isset($_POST['fecha_ingreso']) ? $_POST['fecha_ingreso'] : "";
        $fechaVencimiento = isset($_POST['fecha_vencimiento']) ? $_POST['fecha_vencimiento'] : "";

        $loteBusiness = new LoteBusiness();

        if ($accion == 'eliminar') {
            if (empty($id) || !is_numeric($id)) {
                $response['success'] = false;
                $response['message'] = "El ID no puede estar vacío.";
            } else {
                $result = $loteBusiness->deleteLote($id);
                $response['success'] = $result["success"];
                $response['message'] = $result["message"];
            }
        } else {
            $validationErrors = validarDatos($codigo, $productoID, $cantidad, $precio, $proveedorID, $fechaIngreso, $fechaVencimiento);

            if (empty($validationErrors)) {
                if ($accion == 'insertar') {
                    $lote = new Lote($codigo, $productoID, $cantidad, $precio, $proveedorID, $fechaIngreso, $fechaVencimiento, $id);
                    $result = $loteBusiness->insertLote($lote);
                    $response['success'] = $result["success"];
                    $response['message'] = $result["message"];
                } elseif ($accion == 'actualizar') {
                    $lote = new Lote($codigo, $productoID, $cantidad, $precio, $proveedorID, $fechaIngreso, $fechaVencimiento, $id);
                    $result = $loteBusiness->updateLote($lote);
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

        $loteBusiness = new LoteBusiness();
        $result = $loteBusiness->getPaginatedLotes($page, $size, $sort);
        
        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    }
?>
