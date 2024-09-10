<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Generar Código de Barras</title>

        <link rel="stylesheet" href="./css/styles.css">
        <style>
            .productos-container {
                margin-bottom: 20px;
            }

            .productos-container label {
                margin-right: 10px;
            }

            #producto-select {
                width: 320px;
                height: 30px;
                margin: 5px 10px;
                background-color: #ffffd6;
                border: 1px solid #545454;
                border-radius: 5px;
                padding: 5px;
            }

            #barcode-container {
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .barcode {
                display: flex;
                align-items: center;
                text-align: center;
                margin-bottom: 1rem;
            }

            .barcode label {
                margin-right: 10px;
            }

            .barcode input {
                width: 265px;
                margin-right: 0.5rem;
            }

            #barcodeImage {
                border: 2px solid #000;
                border-radius: 5px;
                padding: 1.2rem;
                margin-top: 1rem;
            }
        </style>
    </head>
    <body>
        <h2>Generador de Códigos de Barras</h2>
        
        <div id="message"></div>

        <div class="productos-container">
            <label for="producto-select">Seleccione un Producto:</label>
            <select id="producto-select">
                <option value="1">Producto Prueba 1</option>
                <option value="2">Producto Prueba 2</option>
                <option value="3">Producto Prueba 3</option>
                <option value="4">Producto Prueba 4</option>
                <option value="5">Producto Prueba 5</option>
            </select>
        </div>

        <div id="barcode-container">
            <div class="barcode">
                <label for="barcodeText">Código de Barras: </label>
                <input 
                    id="barcodeText" type="text" maxlength="12" 
                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 12);"
                    title="Ingrese hasta 12 dígitos numéricos"
                />
                <button onclick="generateBarcode()">Generar</button>
            </div>

            <img id="barcodeImage" alt="Código de Barras" style="display: none;" />
        </div>

        <script src="../view/js/utils.js"></script>
        <script>
            // Función para obtener el código de barras desde el servidor
            async function fetchBarcode(productoID, barcode) {
                try {
                    // Reemplaza `productoID` con el ID del producto que deseas consultar
                    const response = await fetch(`/../controller/codigoBarrasAction.php?producto=${productoID}&barcode=${barcode}&scale=2`);
                    const data = await response.json();

                    console.log(data);
                    if (data.success) {
                        // Inserta la imagen en el elemento img
                        document.getElementById('barcodeImage').src = data.image;
                        document.getElementById('barcodeImage').style.display = 'block';

                        // Inserta el código de barras en el input
                        document.getElementById('barcodeText').value = data.code;
                    } else {
                        // Muestra un mensaje de error
                        showMessage(data.message, 'error');
                        console.error('Error:', data.message);
                    }
                } catch (error) {
                    showMessage(error.message, 'error');
                    console.error('Error:', error.message);
                }
            }

            // Función para generar el código de barras
            function generateBarcode() {
                const barcodeInput = document.getElementById('barcodeText').value = '';
                const productoID = parseInt(document.getElementById('producto-select').value);
                fetchBarcode(productoID, '');
            }
        </script>
    </body>
</html>
