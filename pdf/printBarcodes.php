<?php
    require_once(__DIR__ . '/../libs/barcode-1d/BCGColor.php');
    require_once(__DIR__ . '/../libs/barcode-1d/BCGDrawing.php');
    require_once(__DIR__ . '/../libs/barcode-1d/BCGFontFile.php');
    require_once(__DIR__ . '/../libs/barcode-1d/1D/BCGean13.php');
    require_once __DIR__ . '/../utils/Utils.php';

    if (isset($_GET['count'])) {
        $count = intval($_GET['count']);
    } else {
        $count = 1;
    }

    $fontsize = 9; // <- Tamaño de Fuente para el Código
    $scale = 2;    // <- Escala de la Imagen

    header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Imprimir Códigos de Barras. Producto: 200642129501</title>
        <style>
            /* Estilos para la impresión */
            @media print {
                /* Ocultar el título y el footer */
                .no-print {
                    display: none;
                }
                /* Ajustar el tamaño de la página */
                @page {
                    size: A4;
                    /* margin-top: 10mm; */
                    margin: 0;
                }
                /* Ajustar el tamaño de la fuente */
                body {
                    font-size: 12pt;
                }
                /* Ajustar el espaciado entre los códigos de barras */
                .barcode {
                    margin: 10mm;
                    display: inline-block;
                }
            }
            /* Estilos generales */
            body {
                font-family: CascadiaMono, sans-serif;
                margin: 0; /* Elimina el margen por defecto */
                text-align: center; /* Centra el texto dentro del body */
            }
            .barcodes-container {
                display: flex;
                flex-wrap: wrap; /* Permite que los elementos se envuelvan en múltiples líneas */
                justify-content: center; /* Centra horizontalmente */
                align-items: flex-start; /* Alinea los elementos al inicio verticalmente */
                gap: 10px; /* Espaciado entre los elementos */
                padding: 10px; /* Espaciado alrededor del contenedor */
            }
            .barcode {
                width: 220px;
                border: 3px solid black;
                padding: 20px;
                border-radius: 5px;
            }
        </style>
        <script>
            window.onload = function() {
                window.print();
            }
        </script>
    </head>
    <body>
        <!-- Título que se ocultará en la impresión -->
        <h1 class="no-print">Imprimir Códigos de Barras</h1>
        <div class="barcodes-container">
            <?php for ($i = 0; $i < $count; $i++): ?>
                <div class="barcode">
                    <?php
                        $ean13Code = Utils::generateEAN13Barcode('000100010001');
                        $fontPath = __DIR__ . '/../libs/barcode-1d/font/CascadiaMono.ttf';
                        $font = new BCGFontFile($fontPath, $fontsize * $scale);
                        $colorBlack = new BCGColor(0, 0, 0);
                        $colorWhite = new BCGColor(255, 255, 255);
                        $colorWhite->setTransparent(false);

                        $code = new BCGean13();
                        $code->setScale($scale);
                        $code->setThickness(30);
                        $code->setForegroundColor($colorBlack);
                        $code->setBackgroundColor($colorWhite);
                        $code->setFont($font);
                        $code->parse($ean13Code);

                        $filePath = __DIR__ . "/../view/img/{$ean13Code}.png";
                        $drawing = new BCGDrawing($code, $colorWhite);
                        $drawing->finish(BCGDrawing::IMG_FORMAT_PNG, $filePath);
                    ?>
                    <img src="data:image/png;base64,<?= base64_encode(file_get_contents(__DIR__ . "/../view/img/{$ean13Code}.png")) ?>" alt="Barcode">
                </div>
            <?php endfor; ?>
        </div>
        <!-- Footer que se ocultará en la impresión -->
        <p class="no-print">Generado por Isaac Herrera</p>
    </body>
</html>

