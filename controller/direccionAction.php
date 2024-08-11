<?php

    include __DIR__ . '/../service/direccionBusiness.php';

    function validarDatos($provincia, $canton, $distrito, $distancia) {
        $errors = [];

        if ($provincia === null || empty($provincia) || is_numeric($provincia)) {
            $errors[] = "El campo 'Provincia' está vacío o no es válido.";
        }
        if ($canton === null || empty($canton) || is_numeric($canton)) {
            $errors[] = "El campo 'Cantón' está vacío o no es válido.";
        }
        if ($distrito === null || empty($distrito) || is_numeric($distrito)) {
            $errors[] = "El campo 'Distrito' está vacío o no es válido.";
        }
        if ($distancia === null || empty($distancia) || !is_numeric($distancia)) {
            $errors[] = "El campo 'Distancia' está vacío o no es válido.";
        }

        return $errors;
    }

    $response = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Acción que se va a realizar
        $accion = $_POST['accion'];

        // Datos del Form
        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        $provincia = $_POST['provincia'];
        $canton = $_POST['canton'];
        $distrito = $_POST['distrito'];
        $barrio = isset($_POST['barrio']) ? $_POST['barrio'] : "";
        $sennas = isset($_POST['sennas']) ? $_POST['sennas'] : "";
        $distancia = $_POST['distancia'];

        $direccionBusiness = new DireccionBusiness();

        if ($accion == 'eliminar') {
            if (empty($id) || !is_numeric($id)) {
                $response['success'] = false;
                $response['message'] = "El ID no puede estar vacío.";
            } else {
                $result = $direccionBusiness->deleteTBDireccion($id);
                $response['success'] = $result["success"];
                $response['message'] = $result["message"];
            }
        } else {
            $validationErrors = validarDatos($provincia, $canton, $distrito, $distancia);

            if (empty($validationErrors)) {
                if ($accion == 'insertar') {
                    $direccion = new Direccion($provincia, $canton, $distrito, $barrio, $id, $sennas, $distancia);
                    $result = $direccionBusiness->insertTBDireccion($direccion);
                    $response['success'] = $result["success"];
                    $response['message'] = $result["message"];
                } elseif ($accion == 'actualizar') {
                    $direccion = new Direccion($provincia, $canton, $distrito, $barrio, $id, $sennas, $distancia);
                    $result = $direccionBusiness->updateTBDireccion($direccion);
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

?>