<?php

    include __DIR__ . '/../service/direccionBusiness.php';

    $response = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Acción que se va a realizar
        $accion = $_POST['accion'];

        // Datos recibidos en la solicitud (Form)
        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        $provincia = isset($_POST['provincia']) ? $_POST['provincia'] : "";
        $canton = isset($_POST['canton']) ? $_POST['canton'] : "";
        $distrito = isset($_POST['distrito']) ? $_POST['distrito'] : "";
        $barrio = isset($_POST['barrio']) ? $_POST['barrio'] : "";
        $sennas = isset($_POST['sennas']) ? $_POST['sennas'] : "";
        $distancia = isset($_POST['distancia']) ? $_POST['distancia'] : "";

        // Se crea el Service para las operaciones
        $direccionBusiness = new DireccionBusiness();

        // Crea y verifica que los datos de la direccion sean correctos
        $direccion = new Direccion($provincia, $canton, $distrito, $barrio, $id, $sennas, $distancia);
        $check = $direccionBusiness->validarDireccion($direccion, $accion != 'eliminar'); //<- Indica si se validan (o no) los campos además del ID

        // Si los datos son válidos se realiza acción correspondiente
        if ($check['is_valid']) {
            switch ($accion) {
                case 'insertar':
                    // Inserta la direccion en la base de datos
                    $response = $direccionBusiness->insertTBDireccion($direccion);
                    break;
                case 'actualizar':
                    // Actualiza la info de la direccion en la base de datos
                    $response = $direccionBusiness->updateTBDireccion($direccion);
                    break;
                case 'eliminar':
                    // Elimina la direccion de la base de datos (ID se verifica en validarDireccion)
                    $response = $direccionBusiness->deleteTBDireccion($id);
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
        // Obtener parámetros de la solicitud GET
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : null;

        // Validar los parámetros
        if ($page < 1) $page = 1;
        if ($size < 1) $size = 5;

        // Crea el Service y obtiene la lista (paginada) de direcciones
        $direccionBusiness = new DireccionBusiness();
        $response = $direccionBusiness->getPaginatedDirecciones($page, $size, $sort);
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

?>