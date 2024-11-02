import { mostrarMensaje } from "./gui/notification.js";
import { verificarRespuestaJSON } from "./utils.js";

export async function fetchBarcode(barcode = "", text = true, scale = 2, transparent = false) {
    try {
        const query = `accion=barcode&barcode=${barcode}&scale=${scale}&text=${text ? 1 : 0}&trans=${transparent}`;
        const response = await fetch(`${window.baseURL}/controller/codigoBarrasAction.php?${query}`);
        if (!response.ok) throw new Error(`Error ${response.status} (${response.statusText})`);

        const data = await verificarRespuestaJSON(response);
        if (data.success) {
            return data; // Devuelve la imagen en base64
        } else {
            console.error(data.message);
            mostrarMensaje(data.message, 'error', 'Error al obtener el código de barras');
            return null;
        }
    } catch (error) {
        console.error('Error fetching barcode:', error);
        mostrarMensaje('Ocurrió un error al obtener el código de barras.', 'error', 'Error interno');
        return null;
    }
}

export async function generateBarcode(barcodeInput, imageContainer) {
    // Obtener el código de barras del input
    const codigoBarras = barcodeInput.value;

    // Validar el código de barras
    const codigoVacio = codigoBarras === '';
    const codigoEntre12y13 = codigoBarras.length >= 12 && codigoBarras.length <= 13;
    const codigoNumerico = /^\d+$/.test(codigoBarras);

    // Si el código de barras es válido, obtener la imagen
    if (codigoVacio || (codigoEntre12y13 && codigoNumerico)) {
        fetchBarcode(codigoBarras).then((data) => {
            if (data) {
                const barcodeImage = document.querySelector(imageContainer);
                barcodeImage.src = data.image;
                barcodeInput.value = data.code;
            }
        });
    } else {
        mostrarMensaje(
            'El código de barras debe ser númerico y tener entre 12 y 13 caracteres', 
            'error', 
            'Código de Barras inválido'
        );
    }
}