<?php

    include __DIR__ . '/../service/proveedorDireccionBusiness.php';

    function validarDatos($proveedorID, $direccionID) {
        $errors = [];

        if ($proveedorID === null || !is_numeric($proveedorID) || $proveedorID <= 0) {
            $errors[] = "La información del proveedor no es válida.";
        }

        if ($direccionID === null || !is_numeric($direccionID) || $direccionID <= 0) {
            $errors[] = "La información de la dirección no es válida.";
        }

        return $errors;
    }

    $response = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Acción que se va a realizar
        $accion = $_POST['accion'];

        // Datos del Form
        $proveedorID = isset($_POST['proveedorID']) ? $_POST['proveedorID'] : 0;
        $direccionID = isset($_POST['direccionID']) ? $_POST['direccionID'] : 0;

        // Se valida que los ID no estén vacíos
        $validationErrors = validarDatos($proveedorID, $direccionID);
        if (empty($validationErrors)) {
            $proveedorDireccionBusiness = new ProveedorDireccionBusiness();

            if ($accion == 'insertar') {
                $result = $proveedorDireccionBusiness->addDireccionToProveedor($proveedorID, $direccionID);
                $response['success'] = $result["success"];
                $response['message'] = $result["message"];
            } elseif ($accion == 'actualizar') {
                $result = $proveedorDireccionBusiness->updateDireccionOfProveedor($proveedorID, $direccionID);
                $response['success'] = $result["success"];
                $response['message'] = $result["message"];
            } elseif ($accion == 'eliminar') {
                $result = $proveedorDireccionBusiness->removeDireccionFromProveedor($proveedorID, $direccionID);
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