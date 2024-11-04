<?php

require_once dirname(__DIR__, 1) . "/service/codigoBarrasBusiness.php";
require_once dirname(__DIR__, 1) . "/data/VentaDetalleProductoData.php";
require_once dirname(__DIR__, 1) . "/utils/Utils.php";

class VentaDetalleProductoBusiness {

    private $className;      // Variable para almacenar el nombre de la clase
    private $ventaDetalleProductoData; // Variable para almacenar la clase VentaDetalleProductoData

    /**
     * Constructor de la clase VentaDetalleProductoBusiness.
     */
    public function __construct() {
        $this->ventaDetalleProductoData = new VentaDetalleProductoData(); // Se crea un objeto de la clase VentaDetalleProductoData
        $this->className = get_class($this); // Se obtiene el nombre de la clase
    }

    /**
     * Valida el ID de un detalle de venta producto.
     *
     * @param mixed $ventaDetalleProductoID El ID del detalle de venta producto a validar.
     * @return array Un arreglo asociativo indicando si el ID es válido y un mensaje en caso de error.
     */
    public function validarVentaDetalleProductoID($ventaDetalleProductoID) {
        if ($ventaDetalleProductoID === null || !is_numeric($ventaDetalleProductoID) || $ventaDetalleProductoID < 0) {
            Utils::writeLog("El ID [$ventaDetalleProductoID] del detalle de venta producto no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
            return ["is_valid" => false, "message" => "El ID del detalle de venta producto no es válido. Debe ser un número mayor a 0"];
        }
        return ["is_valid" => true];
    }

    /**
     * Valida los datos de un detalle de venta producto.
     *
     * @param VentaDetalleProducto $ventaDetalleProducto El detalle de venta producto a validar.
     * @return array Un arreglo asociativo indicando si los datos son válidos y un mensaje en caso de error.
     */
    public function validarVentaDetalleProducto($ventaDetalleProducto) {
        $errors = [];

        // Verificación de campos (ajustar según los campos de VentaDetalleProducto)
        if ($ventaDetalleProducto->getVentaProductoID() === null || !is_numeric($ventaDetalleProducto->getVentaProductoID()) || $ventaDetalleProducto->getVentaProductoID() <= 0) {
            $errors[] = "El ID del producto de venta no es válido.";
        }
        if ($ventaDetalleProducto->getVentaDetalleID() === null || !is_numeric($ventaDetalleProducto->getVentaDetalleID()) || $ventaDetalleProducto->getVentaDetalleID() <= 0) {
            $errors[] = "El ID del detalle de venta no es válido.";
        }
        if ($ventaDetalleProducto->getProductoID() === null || !is_numeric($ventaDetalleProducto->getProductoID()) || $ventaDetalleProducto->getProductoID() <= 0) {
            $errors[] = "El ID del producto no es válido.";
        }
        if ($ventaDetalleProducto->getVentaProductoEstado() === null || !is_numeric($ventaDetalleProducto->getVentaProductoEstado())) {
            $errors[] = "El estado del producto de venta no es válido.";
        }

        // Otros campos adicionales pueden verificarse aquí

        if (!empty($errors)) {
            return ["is_valid" => false, "message" => implode('<br>', $errors)];
        }
        return ["is_valid" => true];
    }

    /**
     * Inserta un nuevo detalle de venta producto en la base de datos.
     *
     * @param VentaDetalleProducto $ventaDetalleProducto Los datos del detalle de venta producto a insertar.
     * @return array Resultado de la operación de inserción.
     */
    public function insertVentaDetalleProducto($ventaDetalleProducto) {
        $check = $this->validarVentaDetalleProducto($ventaDetalleProducto);
        if (!$check["is_valid"]) {
            return ["success" => false, "message" => $check["message"]];
        }
        return $this->ventaDetalleProductoData->insertVentaDetalleProducto($ventaDetalleProducto);
    }

    /**
     * Actualiza la información de un detalle de venta producto.
     *
     * @param VentaDetalleProducto $ventaDetalleProducto Los datos del detalle de venta producto a actualizar.
     * @return array Resultado de la operación de actualización.
     */
    public function updateVentaDetalleProducto($ventaDetalleProducto) {
        $check = $this->validarVentaDetalleProducto($ventaDetalleProducto);
        if (!$check["is_valid"]) {
            return ["success" => false, "message" => $check["message"]];
        }
        return $this->ventaDetalleProductoData->updateVentaDetalleProducto($ventaDetalleProducto);
    }

    /**
     * Elimina un detalle de venta producto de la base de datos.
     *
     * @param int $ventaDetalleProductoID El ID del detalle de venta producto a eliminar.
     * @return array Resultado de la operación de eliminación.
     */
    public function deleteVentaDetalleProducto($ventaDetalleProductoID) {
        $checkID = $this->validarVentaDetalleProductoID($ventaDetalleProductoID);
        if (!$checkID["is_valid"]) {
            return ["success" => false, "message" => $checkID["message"]];
        }
        return $this->ventaDetalleProductoData->deleteVentaDetalleProducto($ventaDetalleProductoID);
    }

    /**
     * Obtiene todos los detalles de venta producto de la base de datos.
     *
     * @return array Un arreglo de detalles de venta producto.
     */
    public function getAllVentaDetalleProductos() {
        return $this->ventaDetalleProductoData->getAllVentaDetalleProductos();
    }

    /**
     * Obtiene un detalle de venta producto por su ID.
     *
     * @param int $ventaDetalleProductoID El ID del detalle de venta producto.
     * @return array El detalle de venta producto solicitado.
     */
    public function getVentaDetalleProductoByID($ventaDetalleProductoID) {
        $checkID = $this->validarVentaDetalleProductoID($ventaDetalleProductoID);
        if (!$checkID["is_valid"]) {
            return ["success" => false, "message" => $checkID["message"]];
        }
        return $this->ventaDetalleProductoData->getVentaDetalleProductoByID($ventaDetalleProductoID);
    }
}
?>
