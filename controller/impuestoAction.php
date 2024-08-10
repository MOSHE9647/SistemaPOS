<?php
    include __DIR__ . '/../service/impuestoBusiness.php';
    require_once __DIR__ . '/../utils/Utils.php';

    // Función para validar los datos del impuesto
    function validarDatos($nombre, $valor, $fecha) {
        $errors = [];

        if (empty($nombre) || is_numeric($nombre)) {
            $errors[] = "El campo 'Nombre' no puede estar vacío o ser numérico.";
        }
        if (!is_numeric($valor) || $valor <= '0') {
            $errors[] = "El campo 'Valor' tiene que ser mayor a 0.";
        }
        if (empty($fecha) || !Utils::validar_fecha($fecha)) {
            $errors[] = "El campo 'Fecha Vigencia' no es válido.";
        }

        return $errors;
    }

    $response = [];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $accion = $_POST['accion'];
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : "";
        $valor = isset($_POST['valor']) ? $_POST['valor'] : "";
        $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : "";
        $fecha = isset($_POST['fecha_vigencia']) ? $_POST['fecha_vigencia'] : "";

        $impuestoBusiness = new ImpuestoBusiness();

        if ($accion == 'eliminar') {
            if (empty($id) || !is_numeric($id)) {
                $response['success'] = false;
                $response['message'] = "El ID no puede estar vacío.";
            } else {
                $result = $impuestoBusiness->deleteTBImpuesto($id);
                $response['success'] = $result["success"];
                $response['message'] = $result["message"];
            }
        } else {
            $validationErrors = validarDatos($nombre, $valor, $fecha);

            if (empty($validationErrors)) {
                if ($accion == 'insertar') {
                    $impuesto = new Impuesto($nombre, $valor, $fecha, $id, $descripcion);

                    Utils::writeLog(
                        $impuesto->getImpuestoID() . ", " .
                        $impuesto->getImpuestoNombre() . ", " .
                        $impuesto->getImpuestoValor() . ", " .
                        $impuesto->getImpuestoEstado() . ", " .
                        $impuesto->getImpuestoDescripcion() . ", " .
                        $impuesto->getImpuestoFechaVigencia()
                    );

                    $result = $impuestoBusiness->insertTBImpuesto($impuesto);
                    $response['success'] = $result["success"];
                    $response['message'] = $result["message"];
                } elseif ($accion == 'actualizar') {
                    $impuesto = new Impuesto($nombre, $valor, $fecha, $id, $descripcion);
                    $result = $impuestoBusiness->updateTBImpuesto($impuesto);
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