<?php

require_once dirname(__DIR__, 1) . "/data/CompraDetalleData.php";
require_once dirname(__DIR__, 1) . '/utils/Utils.php';

class CompraDetalleBusiness {

    private $className;
    private $compraDetalleData;

    public function __construct() {
        $this->compraDetalleData = new CompraDetalleData();
        $this->className = get_class($this);
    }

    public function validarCompraDetalleID($compraDetalleID) {
        if ($compraDetalleID === null || !is_numeric($compraDetalleID) || $compraDetalleID < 0) {
            Utils::writeLog("El ID [$compraDetalleID] del detalle de compra no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
            return ["is_valid" => false, "message" => "El ID del detalle de compra está vacío o no es válido. Revise que este sea un número y que sea mayor a 0"];
        }
        return ["is_valid" => true];
    }

    public function validarCompraDetalle($compraDetalle, $insert = false) {
        try {
            $compraDetalleID = $compraDetalle->getCompraDetalleID();
            $compraID = $compraDetalle->getCompraDetalleCompra();
            $productoID = $compraDetalle->getCompraDetalleProducto();
            $cantidad = $compraDetalle->getCompraDetalleCantidad();
            $estado = $compraDetalle->getCompraDetalleEstado();
            $errors = [];

            if (!$insert) {
                $checkID = $this->validarCompraDetalleID($compraDetalleID);
                if (!$checkID['is_valid']) { $errors[] = $checkID['message']; }
            }

            if ($compraID === null || !is_numeric($compraID) || $compraID <= 0) {
                $errors[] = "El campo 'Compra ID' no es válido. Debe ser un número mayor a 0.";
            }
            if ($productoID === null || !is_numeric($productoID) || $productoID <= 0) {
                $errors[] = "El campo 'Producto ID' no es válido. Debe ser un número mayor a 0.";
            }
            if ($cantidad === null || !is_numeric($cantidad) || $cantidad <= 0) {
                $errors[] = "El campo 'Cantidad' no es válido. Debe ser un número mayor a 0.";
            }
            if ($estado === null || !is_numeric($estado)) {
                $errors[] = "El campo 'Estado' no es válido.";
            }

            if (!empty($errors)) {
                throw new Exception(implode('<br>', $errors));
            }

            return ["is_valid" => true];
        } catch (Exception $e) {
            return ["is_valid" => false, "message" => $e->getMessage()];
        }
    }

    public function insertCompraDetalle($compraDetalle) {
        $check = $this->validarCompraDetalle($compraDetalle, true);
        if (!$check["is_valid"]) {
            return ["success" => false, "message" => $check["message"]];
        }
        return $this->compraDetalleData->insertCompraDetalle($compraDetalle);
    }

    public function updateCompraDetalle($compraDetalle) {
        $check = $this->validarCompraDetalle($compraDetalle);
        if (!$check["is_valid"]) {
            return ["success" => false, "message" => $check["message"]];
        }
        return $this->compraDetalleData->updateCompraDetalle($compraDetalle);
    }

    public function deleteCompraDetalle($compraDetalleID) {
        $checkID = $this->validarCompraDetalleID($compraDetalleID);
        if (!$checkID["is_valid"]) {
            return ["success" => false, "message" => $checkID["message"]];
        }
        return $this->compraDetalleData->deleteCompraDetalle($compraDetalleID);
    }

    public function getAllCompraDetalles() {
        return $this->compraDetalleData->getAllCompraDetalles();
    }

    public function getCompraDetalleByID($compraDetalleID) {
        $checkID = $this->validarCompraDetalleID($compraDetalleID);
        if (!$checkID["is_valid"]) {
            return ["success" => false, "message" => $checkID["message"]];
        }
        return $this->compraDetalleData->getCompraDetalleByID($compraDetalleID);
    }

}

?>
