<?php
include __DIR__ . '/../service/proveedorTelefonoBusiness.php';
require_once __DIR__ . '/../utils/Utils.php';

// Función para validar los datos de ProveedorTelefono
function validarDatosProveedorTelefono($proveedorId, $telefono) {
    $errors = [];

    if (empty($proveedorId) || !is_numeric($proveedorId)) {
        $errors[] = "El campo 'Proveedor ID' no es válido.";
    }

    if (empty($telefono) || !is_numeric($telefono)) {
        $errors[] = "El campo 'Teléfono' no puede estar vacío o ser no numérico.";
    }

    return $errors;
}

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion = $_POST['accion'];
    $proveedorTelefonoId = isset($_POST['proveedortelefonoid']) ? $_POST['proveedortelefonoid'] : null;
    $proveedorId = $_POST['proveedorid'];
    $telefono = $_POST['telefono'];
    $activo = isset($_POST['activo']) ? $_POST['activo'] : 1;

    $proveedorTelefonoBusiness = new ProveedorTelefonoBusiness();

    if ($accion == 'eliminar') {
        if (empty($proveedorTelefonoId) || !is_numeric($proveedorTelefonoId)) {
            $response['success'] = false;
            $response['message'] = "El ID de ProveedorTelefono no puede estar vacío.";
        } else {
            $result = $proveedorTelefonoBusiness->eliminarProveedorTelefono($proveedorTelefonoId);
            $response['success'] = $result["success"];
            $response['message'] = $result["message"];
        }
    } else {
        $validationErrors = validarDatosProveedorTelefono($proveedorId, $telefono);

        if (empty($validationErrors)) {
            $proveedorTelefono = new ProveedorTelefono($proveedorTelefonoId, $proveedorId, $telefono, $activo);

            if ($accion == 'insertar') {
                $result = $proveedorTelefonoBusiness->insertarProveedorTelefono($proveedorTelefono);
                $response['success'] = $result["success"];
                $response['message'] = $result["message"];
            } elseif ($accion == 'actualizar') {
                $result = $proveedorTelefonoBusiness->actualizarProveedorTelefono($proveedorTelefono);
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
    $proveedorTelefonoBusiness = new ProveedorTelefonoBusiness();
    if (isset($_GET['id'])) {
        $proveedorTelefonoId = $_GET['id'];
        $result = $proveedorTelefonoBusiness->obtenerProveedorTelefonoPorId($proveedorTelefonoId);
        echo json_encode($result);
    } else {
        $result = $proveedorTelefonoBusiness->obtenerProveedoresTelefonosActivos();
        echo json_encode($result);
    }
    exit();
}
?>
