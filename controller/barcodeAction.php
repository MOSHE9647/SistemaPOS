<?php
    require_once(__DIR__ . '/../libs/barcode-1d/BCGColor.php');
    require_once(__DIR__ . '/../libs/barcode-1d/BCGDrawing.php');
    require_once(__DIR__ . '/../libs/barcode-1d/BCGFontFile.php');
    require_once(__DIR__ . '/../libs/barcode-1d/1D/BCGean13.php');
    include_once __DIR__ . '/../utils/Utils.php';

    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        // Obtener y sanitizar parámetros
        $productoID =   isset($_GET['productoID'])  ? intval($_GET['productoID'])   : 0;
        $scale =        isset($_GET['scale'])       ? intval($_GET['scale'])        : 1;
        $transparent =  !empty($_GET['trans']);
        $save =         !empty($_GET['save']);
        $text =         empty($_GET['text']);

        // Verificar existencia de archivo de fuente
        $fontPath = __DIR__ . '/../libs/barcode-1d/font/CascadiaMono.ttf';
        if (!file_exists($fontPath)) {
            die('Fuente CascadiaMono.ttf no encontrada.');
        }

        try {
            // Ajusta el tamaño de la fuente según la escala
            $adjustedFontSize = 9 * $scale;

            $font = new BCGFontFile($fontPath, $adjustedFontSize);
            $colorBlack = new BCGColor(0, 0, 0);
            $colorWhite = new BCGColor(255, 255, 255);
            $colorWhite->setTransparent($transparent);
            $ean13Code = Utils::generateEAN13Barcode('200642129502');

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

            // Creación y salida de la imagen
            $drawing = new BCGDrawing($code, $colorWhite);

            if ($save) {
                $filePath = __DIR__ . "/../generated/{$ean13Code}.png";
                $drawing->finish(BCGDrawing::IMG_FORMAT_PNG, $filePath);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Código de Barras creado exitosamente',
                    'code' => $ean13Code
                ]);
            } else {
                header('Content-Type: image/png');
                $drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
            }

            exit();
        } catch (Exception $e) {
            die('Error al generar el código de barras: ' . htmlspecialchars($e->getMessage()));
        }
    }
?>