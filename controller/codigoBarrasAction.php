<?php
    require_once (__DIR__ . '/../libs/barcode-1d/BCGColor.php');
    require_once (__DIR__ . '/../libs/barcode-1d/BCGDrawing.php');
    require_once (__DIR__ . '/../libs/barcode-1d/BCGFontFile.php');
    require_once (__DIR__ . '/../libs/barcode-1d/1D/BCGean13.php');
    require_once (__DIR__ . '/../service/codigoBarrasBusiness.php');
    require_once (__DIR__ . '/../utils/Utils.php');

    $response = [];
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Obtener y sanitizar parámetros
        $accion = isset($_POST['accion']) ? $_POST['accion'] : "";
        if (empty($accion)) {
            $response = [
                'success' => false,
                'message' => "No se ha especificado una acción."
            ];
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }

        // Datos del Código de Barras recibidos en la solicitud
        $id =            isset($_POST['id'])          ? intval($_POST['id'])       : -1;
        $codigo =        isset($_POST['codigo'])      ? $_POST['codigo']           : "";

        // Se crea el Service para las operaciones
        $codigoBarrasBusiness = new CodigoBarrasBusiness();

        // Crea y verifica que los datos del código de barras sean correctos
        $codigoBarras = new CodigoBarras($id, $codigo);
        $check = $codigoBarrasBusiness->validarCodigoBarras($codigoBarras, $accion != 'eliminar', $accion == 'insertar');

        // Si los datos son válidos se realiza acción correspondiente
        if ($check['is_valid']) {
            switch ($accion) {
                case 'insertar':
                    // Inserta el código de barras en la base de datos
                    $response = $codigoBarrasBusiness->insertTBCodigoBarras($codigoBarras);
                    break;
                case 'actualizar':
                    // Actualiza la info del código de barras en la base de datos
                    $response = $codigoBarrasBusiness->updateTBCodigoBarras($codigoBarras);
                    break;
                case 'eliminar':
                    // Elimina el código de barras de la base de datos
                    $response = $codigoBarrasBusiness->deleteTBCodigoBarras($id);
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

    else if ($_SERVER["REQUEST_METHOD"] === "GET") {
        $accion               = isset($_GET['accion'])      ? $_GET['accion']           : "";
        $deleted              = isset($_GET['deleted'])     ? boolval($_GET['deleted']) : false;
        $onlyActive = isset($_GET['filter'])      ? boolval($_GET['filter'])  : true;

        $codigoBarrasBusiness = new CodigoBarrasBusiness();
        switch ($accion) {
            case 'all':
                $response = $codigoBarrasBusiness->getAllTBCodigoBarras($onlyActive, $deleted);
                break;
            case 'id':
                $id = isset($_GET['id']) ? intval($_GET['id']) : -1;
                $response = $codigoBarrasBusiness->getCodigoBarrasByID($id, $onlyActive, $deleted);
                break;
            case 'barcode':
                // Obtener y sanitizar parámetros
                $scale =         isset($_GET['scale'])    ? intval($_GET['scale'])    :  1;
                $text =          isset($_GET['text'])     ? boolval($_GET['text'])    : true;
                $transparent =   isset($_GET['trans'])    ? boolval($_GET['trans'])   : false;
                $barcode =       isset($_GET['barcode'])  ? $_GET['barcode']          : null;

                // Verificar existencia de archivo de fuente
                $fontPath = __DIR__ . '/../libs/barcode-1d/font/CascadiaMono.ttf';
                if (!file_exists($fontPath)) {
                    $response = [
                        'success' => false,
                        'message' => 'No se encontró el archivo de fuente para el código de barras'
                    ];
                }

                try {
                    // Ajusta el tamaño de la fuente según la escala
                    $adjustedFontSize = 9 * $scale;

                    $font = new BCGFontFile($fontPath, $adjustedFontSize);
                    $colorBlack = new BCGColor(0, 0, 0);
                    $colorWhite = new BCGColor(255, 255, 255);
                    $colorWhite->setTransparent($transparent);

                    if (!$barcode) {
                        // Genera el código de barras (13 dígitos): 12 dígitos + 1 dígito de verificación
                        $barcode = $codigoBarrasBusiness->generarCodigoDeBarras();
                        if (!$barcode['success']) { throw new Exception($barcode['message']); }
                        $ean13Code = $barcode['code'];
                    } else {
                        if (!is_string($barcode) || strlen($barcode) < 12 || strlen($barcode) > 13 || !ctype_digit($barcode)) {
                            throw new Exception('El código de barras debe tener entre 12 y 13 dígitos numéricos');
                        } else {
                            $ean13Code = $barcode;
                        }
                    }

                    // Configuración del código de barras
                    $code = new BCGean13();
                    $code->setScale($scale);
                    $code->setThickness(30);
                    $code->setForegroundColor($colorBlack);
                    $code->setBackgroundColor($colorWhite);
                    $code->setFont($font);
                    $code->parse($ean13Code);
                    if (!$text) {
                        $code->clearLabels();
                    }

                    // Cambia el último digito de $ean13Code por el dígito de verificación
                    $ean13Code = substr($ean13Code, 0, 12) . $code->getChecksum();

                    // Creación de la imagen en un buffer de memoria
                    $drawing = new BCGDrawing($code, $colorWhite);

                    ob_start();  // Iniciar el buffer de salida
                    $drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
                    $imageData = ob_get_contents();  // Obtener la imagen desde el buffer
                    ob_end_clean();  // Limpiar el buffer

                    // Convertir la imagen a Base64
                    $base64Image = base64_encode($imageData);

                    // Devolver la respuesta en formato JSON
                    $response = [
                        'success' => true,
                        'message' => 'Código de Barras generado exitosamente',
                        'code' => $ean13Code,  // Código de Barras generado
                        'image' => 'data:image/png;base64,' . $base64Image  // Imagen en Base64
                    ];
                } catch (Exception $e) {
                    $response = [
                        'success' => false,
                        'message' => 'Ocurrió un error al intentar generar el código de barras: ' . $e->getMessage()
                    ];
                }
                break;
            default:
                // Obtener parámetros de la solicitud GET
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
                $sort = isset($_GET['sort']) ? $_GET['sort'] : null;

                // Validar los parámetros
                if ($page < 1) $page = 1;
                if ($size < 1) $size = 5;

                $response = $codigoBarrasBusiness->getPaginatedCodigosBarras($page, $size, $sort, $onlyActive, $deleted);
                break;
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    else {
        $response['success'] = false;
        $response['message'] = "Método no permitido (" . $_SERVER["REQUEST_METHOD"] . ").";

        http_response_code(405);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

?>