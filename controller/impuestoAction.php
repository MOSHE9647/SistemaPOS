<?php
    session_start();
    include __DIR__ . '/../service/impuestoBusiness.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $accion = $_POST['accion'];
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $nombre = $_POST['nombre'];
        $valor = $_POST['valor'];
        $descripcion = $_POST['descripcion'];
        $fecha = $_POST['fecha'];

        function validar_fecha($fecha) {
            $formato = 'Y-m-d';
            $date = DateTime::createFromFormat($formato, $fecha);
            return $date && $date->format($formato) === $fecha;
        }

        if ($accion == 'insertar') {
            $redirect_url = '../view/impuesto/insertarImpuesto.php';
        } elseif ($accion == 'actualizar') {
            $redirect_url = '../view/impuesto/actualizarImpuesto.php';
        } elseif ($accion == 'eliminar') {
            $redirect_url = '../view/impuesto/eliminarImpuesto.php';
        }

        if ($accion != 'eliminar') {
            if (empty($nombre) || is_numeric($nombre)) {
                $_SESSION['error'] = "El campo 'Nombre' no puede estar vacío o ser numérico.";
                header("Location: $redirect_url");
                exit();
            }
            if (!is_numeric($valor) || $valor == '0') {
                $_SESSION['error'] = "El campo 'Valor' no puede ser 0.";
                header("Location: $redirect_url");
                exit();
            }
            if (empty($descripcion) || is_numeric($descripcion)) {
                $_SESSION['error'] = "El campo 'Descripción' no puede estar vacío o ser numérico.";
                header("Location: $redirect_url");
                exit();
            }
            if (empty($fecha) || !validar_fecha($fecha)) {
                $_SESSION['error'] = "El campo 'Fecha' no es válido.";
                header("Location: $redirect_url");
                exit();
            }
        }

        $impuestoBusiness = new ImpuestoBusiness();

        if ($accion == 'insertar') {
            $impuesto = new Impuesto(0, $nombre, $valor, $descripcion, $fecha);
            $result = $impuestoBusiness->insertTBImpuesto($impuesto);
            if (!$result["success"]) {
                $_SESSION['error'] = $result["message"];
                header("Location: $redirect_url");
            } else {
                $_SESSION['success'] = "Impuesto insertado exitosamente.";
                header("Location: $redirect_url");
            }
        } elseif ($accion == 'actualizar') {
            $impuesto = new Impuesto($id, $nombre, $valor, $descripcion, $fecha);
            $result = $impuestoBusiness->updateTBImpuesto($impuesto);
            if (!$result["success"]) {
                $_SESSION['error'] = $result["message"];
                header("Location: $redirect_url");
            } else {
                $_SESSION['success'] = "Impuesto actualizado exitosamente.";
                header("Location: $redirect_url");
            }
        } elseif ($accion == 'eliminar') {
            $result = $impuestoBusiness->deleteTBImpuesto($id);
            if (!$result["success"]) {
                $_SESSION['error'] = $result["message"];
                header("Location: $redirect_url");
            } else {
                $_SESSION['success'] = "Impuesto eliminado exitosamente.";
                header("Location: $redirect_url");
            }
        }
        exit();
    }
?>