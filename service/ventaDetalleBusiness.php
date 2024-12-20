<?php

require_once dirname(__DIR__, 1) . "/service/codigoBarrasBusiness.php";
require_once dirname(__DIR__, 1) . "/data/VentaDetalleData.php";
require_once dirname(__DIR__, 1) . "/utils/Utils.php";

class VentaDetalleBusiness {

    private $className;      // Variable para almacenar el nombre de la clase
    private $ventaDetalleData; // Variable para almacenar la clase VentaDetalleData

    /**
     * Constructor de la clase VentaDetalleBusiness.
     */
    public function __construct() {
        $this->ventaDetalleData = new VentaDetalleData(); // Se crea un objeto de la clase VentaDetalleData
        $this->className = get_class($this); // Se obtiene el nombre de la clase
    }

    /**
     * Valida el ID de un detalle de venta.
     *
     * @param mixed $ventaDetalleID El ID del detalle de venta a validar.
     * @return array Un arreglo asociativo indicando si el ID es válido y un mensaje en caso de error.
     */
    public function validarVentaDetalleID($ventaDetalleID) {
        if ($ventaDetalleID === null || !is_numeric($ventaDetalleID) || $ventaDetalleID < 0) {
            Utils::writeLog("El ID [$ventaDetalleID] del detalle de venta no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
            return ["is_valid" => false, "message" => "El ID del detalle de venta no es válido. Debe ser un número mayor a 0"];
        }
        return ["is_valid" => true];
    }

    /**
     * Valida los datos de un detalle de venta.
     *
     * @param VentaDetalle $ventaDetalle El detalle de venta a validar.
     * @return array Un arreglo asociativo indicando si los datos son válidos y un mensaje en caso de error.
     */
    public function validarVentaDetalle($ventaDetalle) {
        $productoID = $ventaDetalle->getVentaDetalleProducto()->getProductoID();
        $cantidad = $ventaDetalle->getVentaDetalleCantidad();
        $precio = $ventaDetalle->getVentaDetallePrecio();
        $errors = [];

        // Verificación de campos (ajustar según los campos de VentaDetalle)
        if ($productoID === null || !is_numeric($productoID) || $productoID <= 0) {
            $errors[] = "El ID del producto no es válido.";
        }
        if ($cantidad === null || !is_numeric($cantidad) || $cantidad <= 0) {
            $errors[] = "La cantidad debe ser un número mayor a 0.";
        }
        if ($precio === null || !is_numeric($precio) || $precio <= 0) {
            $errors[] = "El precio unitario no es válido.";
        }

        // Otros campos adicionales pueden verificarse aquí

        if (!empty($errors)) {
            return ["is_valid" => false, "message" => implode('<br>', $errors)];
        }
        return ["is_valid" => true];
    }

    /**
     * Inserta un nuevo detalle de venta en la base de datos.
     *
     * @param VentaDetalle $ventaDetalle Los datos del detalle de venta a insertar.
     * @return array Resultado de la operación de inserción.
     */
    public function insertVentaDetalle($ventaData) {
        //! NO ESTA VALIDANDO NI VENTA NI LOS PRODUCTOS DE VENTA DETALLE
        //? SOLO ESTÁ VALIDANDO LOS DATOS DE VENTA DETALLE
        foreach ($ventaData[1] as $ventaDetalle) {
            $check = $this->validarVentaDetalle($ventaDetalle);
            if (!$check["is_valid"]) {
                return ["success" => false, "message" => $check["message"]];
            }
        }
        return $this->ventaDetalleData->insertarListaVentaDetalle($ventaData);
    }

    /**
     * Inserta una venta con sus detalles en la base de datos.
     *
     * @param array $ventaDetalles Lista de detalles de venta a insertar.
     * @return array Resultado de la operación de inserción.
     */
    public function insertVentaConDetalles($ventaDetalles) {
        if (empty($ventaDetalles)) {
            return ["success" => false, "message" => "La lista de detalles de venta está vacía."];
        }

        // Validar cada detalle de venta
        foreach ($ventaDetalles as $ventaDetalle) {
            $check = $this->validarVentaDetalle($ventaDetalle);
            if (!$check["is_valid"]) {
                return ["success" => false, "message" => $check["message"]];
            }
        }

        // Obtener la venta del primer detalle de venta
        $venta = $ventaDetalles[0]->getVenta();

        // Iniciar transacción
        $this->ventaDetalleData->beginTransaction();

        // Insertar la venta
        $insertVentaResult = $this->ventaDetalleData->insertVenta($venta);
        if (!$insertVentaResult["success"]) {
            $this->ventaDetalleData->rollback();
            return ["success" => false, "message" => "Error al insertar la venta."];
        }

        // Insertar cada detalle de venta
        foreach ($ventaDetalles as $ventaDetalle) {
            $ventaDetalle->setVentaID($insertVentaResult["venta_id"]);
            $insertDetalleResult = $this->ventaDetalleData->insertVentaDetalle($ventaDetalle);
            if (!$insertDetalleResult["success"]) {
                $this->ventaDetalleData->rollback();
                return ["success" => false, "message" => "Error al insertar un detalle de venta."];
            }
        }

        // Confirmar transacción
        $this->ventaDetalleData->commit();
        return ["success" => true, "message" => "Venta y detalles insertados correctamente."];
    }

    /**
     * Actualiza la información de un detalle de venta.
     *
     * @param VentaDetalle $ventaDetalle Los datos del detalle de venta a actualizar.
     * @return array Resultado de la operación de actualización.
     */
    public function updateVentaDetalle($ventaDetalle) {
        $check = $this->validarVentaDetalle($ventaDetalle);
        if (!$check["is_valid"]) {
            return ["success" => false, "message" => $check["message"]];
        }
        return $this->ventaDetalleData->updateVentaDetalle($ventaDetalle);
    }

    /**
     * Elimina un detalle de venta de la base de datos.
     *
     * @param int $ventaDetalleID El ID del detalle de venta a eliminar.
     * @return array Resultado de la operación de eliminación.
     */
    public function deleteVentaDetalle($ventaDetalleID) {
        $checkID = $this->validarVentaDetalleID($ventaDetalleID);
        if (!$checkID["is_valid"]) {
            return ["success" => false, "message" => $checkID["message"]];
        }
        return $this->ventaDetalleData->deleteVentaDetalle($ventaDetalleID);
    }

    /**
     * Obtiene todos los detalles de venta de la base de datos.
     *
     * @return array Un arreglo de detalles de venta.
     */
    public function getAllVentaDetalles() {
        return $this->ventaDetalleData->getAllVentaDetalles();
    }

    /**
     * Obtiene un detalle de venta por su ID.
     *
     * @param int $ventaDetalleID El ID del detalle de venta.
     * @return array El detalle de venta solicitado.
     */
    public function getVentaDetalleByID($ventaDetalleID) {
        $checkID = $this->validarVentaDetalleID($ventaDetalleID);
        if (!$checkID["is_valid"]) {
            return ["success" => false, "message" => $checkID["message"]];
        }
        return $this->ventaDetalleData->getVentaDetalleByID($ventaDetalleID);
    }
}
?>
