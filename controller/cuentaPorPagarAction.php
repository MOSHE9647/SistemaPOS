<?php

require_once __DIR__ . '/../service/cuentaPorPagarBusiness.php';

$response = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Acción que se va a realizar
    $accion = $_POST['accion'];

    // Datos recibidos en la solicitud (Form)
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $compradetalleid = isset($_POST['compradetalleid']) ? intval($_POST['compradetalleid']) : 0;
    $fechavencimiento = isset($_POST['fechavencimiento']) ? $_POST['fechavencimiento'] : '';
    $montototal = isset($_POST['montototal']) ? floatval($_POST['montototal']) : 0;
    $montopagado = isset($_POST['montopagado']) ? floatval($_POST['montopagado']) : 0;
    $fechapago = isset($_POST['fechapago']) ? $_POST['fechapago'] : '';
    $notas = isset($_POST['notas']) ? $_POST['notas'] : '';
    $estadocuenta = isset($_POST['estadocuenta']) ? $_POST['estadocuenta'] : 'Pendiente';
    $estado = isset($_POST['estado']) ? intval($_POST['estado']) : 1;

    // Se crea el Service para las operaciones
    $cuentaPorPagarBusiness = new CuentaPorPagarBusiness();

    // Crea el objeto CuentaPorPagar
    $cuentaPorPagar = new CuentaPorPagar($id, $compradetalleid, $fechavencimiento, $montototal, $montopagado, $fechapago, $notas, $estadocuenta, $estado);

    // Valida los datos de la cuenta por pagar
    $check = $cuentaPorPagarBusiness->validarCuentaPorPagar($cuentaPorPagar, $accion != 'eliminar'); // Indica si se validan (o no) los campos además del ID

    // Si los datos son válidos se realiza la acción correspondiente
    if ($check['is_valid']) {
        switch ($accion) {
            case 'insertar':
                // Inserta la cuenta por pagar en la base de datos
                $response = $cuentaPorPagarBusiness->insertCuentaPorPagar($cuentaPorPagar);
                  // Obtener el último ID insertado si la inserción fue exitosa
    
                break;
            case 'actualizar':
                // Actualiza la información de la cuenta por pagar en la base de datos
                $response = $cuentaPorPagarBusiness->updateCuentaPorPagar($cuentaPorPagar);
                break;
            case 'eliminar':
                // Elimina la cuenta por pagar de la base de datos (ID se verifica en validarCuentaPorPagar)
                $response = $cuentaPorPagarBusiness->deleteCuentaPorPagar($id);
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

    // Validar los parámetros
    if ($page < 1) $page = 1;
    if ($size < 1) $size = 5;

    // Crea el Service y obtiene la lista (paginada) de cuentas por pagar
    $cuentaPorPagarBusiness = new CuentaPorPagarBusiness();
    $response = $cuentaPorPagarBusiness->getPaginatedCuentaPorPagar($page, $size);

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion = $_POST['accion'];
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    // Agregar depuración para ver si el ID es correcto
    error_log("El ID recibido es: " . $id);

    if ($accion === 'eliminar') {
        if ($id <= 0) {
            echo json_encode([
                "success" => false,
                "message" => "El ID no puede estar vacío o ser menor a 0."
            ]);
            exit();
        }

        $cuentaPorPagarBusiness = new CuentaPorPagarBusiness();
        $response = $cuentaPorPagarBusiness->deleteCuentaPorPagar($id);
        echo json_encode($response);
        exit();
    }
}


?>
