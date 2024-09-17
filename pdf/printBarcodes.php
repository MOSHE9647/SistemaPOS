<?php
    require_once(__DIR__ . '/../utils/Utils.php');

    $count = isset($_GET['count']) ? intval($_GET['count']) : 1;
    $productoID = isset($_GET['producto']) ? intval($_GET['producto']) : 1;
    $scale = 2; // <- Escala de la Imagen

    header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Imprimir Códigos de Barras</title>
        <style>
            /* Estilos para la impresión */
            @media print {
                .no-print {
                    display: none;
                }
                @page {
                    size: A4;
                    margin: 0;
                }
                body {
                    font-size: 12pt;
                }
                .barcode {
                    margin: 10mm;
                    display: inline-block;
                }
            }
            /* Estilos generales */
            body {
                font-family: CascadiaMono, sans-serif;
                margin: 0;
                text-align: center;
            }
            .barcodes-container {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                align-items: flex-start;
                gap: 10px;
                padding: 10px;
            }
            .barcode {
                width: 220px;
                border: 3px solid black;
                padding: 20px;
                border-radius: 5px;
            }
        </style>
        <script>
            async function fetchBarcode(productoID, scale = 1, text = true, transparent = false) {
                try {
                    const response = await fetch(`../controller/codigoBarrasAction.php?producto=${productoID}&scale=${scale}&text=${text}&trans=${transparent}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        return data.image; // Devuelve la imagen en base64
                    } else {
                        console.error(data.message);
                        return null;
                    }
                } catch (error) {
                    console.error('Error fetching barcode:', error);
                    return null;
                }
            }

            async function displayBarcodes(count) {
                const barcodesContainer = document.querySelector('.barcodes-container');
                barcodesContainer.innerHTML = ''; // Limpiar el contenedor
                const barcodeImage = await fetchBarcode(<?= $productoID ?>, <?= $scale ?>);  // Llamar al controlador para generar el código de barras

                for (let i = 0; i < count; i++) {
                    const barcodeDiv = document.createElement('div');
                    barcodeDiv.classList.add('barcode');
                    if(barcodeImage) barcodeDiv.innerHTML = `<img src="${barcodeImage}" alt="Barcode">`;
                    else barcodeDiv.innerHTML = '<p>Error al cargar el código de barras</p>';
                    barcodesContainer.appendChild(barcodeDiv);
                }

                window.print();  // Imprimir la página
            }

            window.onload = function() {
                displayBarcodes(<?= $count ?>);  // Ya se pasa directamente la variable PHP count
            }
        </script>
    </head>
    <body>
        <h1 class="no-print">Imprimir Códigos de Barras</h1>
        <div class="barcodes-container"></div>
        <p class="no-print">Generado por Isaac Herrera</p>
    </body>
</html>
