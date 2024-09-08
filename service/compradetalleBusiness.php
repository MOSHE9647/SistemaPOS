<?php

require_once __DIR__ . "/../data/CompraDetalleData.php";

class CompraDetalleBusiness {

    private $compraDetalleData;

    public function __construct() {
        $this->compraDetalleData = new CompraDetalleData();
    }

    public function validarCompraDetalle($compraDetalle, $validarCamposAdicionales = true) {
        try {
            // Obtener los valores de las propiedades del objeto
            $compraDetalleID = $compraDetalle->getCompradetalleid();
            $compraDetalleCompraID = $compraDetalle->getCompradetallecompraid();
            $compraDetalleLoteID = $compraDetalle->getCompradetalleloteid();
            $compraDetalleProductoID = $compraDetalle->getCompradetalleproductoid();
            $compraDetallePrecioProducto = $compraDetalle->getCompradetalleprecioproducto();
            $compraDetalleCantidad = $compraDetalle->getCompradetallecantidad();
            $errors = [];

            // Verifica que el ID del detalle de compra sea válido
            if ($compraDetalleID === null || !is_numeric($compraDetalleID) || $compraDetalleID <= 0) {
                $errors[] = "El ID del detalle de compra está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                Utils::writeLog("El ID [$compraDetalleID] del detalle de compra no es válido.", BUSINESS_LOG_FILE);
            }

            // Si la validación de campos adicionales está activada, valida los otros campos
            if ($validarCamposAdicionales) {

                if ($compraDetalleCompraID === null || !is_numeric($compraDetalleCompraID) || $compraDetalleCompraID <= 0) {
                    $errors[] = "El campo 'ID de compra' está vacío o no es válido.";
                    Utils::writeLog("El campo 'ID de compra [$compraDetalleCompraID]' no es válido.", BUSINESS_LOG_FILE);
                }

                if ($compraDetalleLoteID === null || !is_numeric($compraDetalleLoteID) || $compraDetalleLoteID <= 0) {
                    $errors[] = "El campo 'ID de lote' está vacío o no es válido.";
                    Utils::writeLog("El campo 'ID de lote [$compraDetalleLoteID]' no es válido.", BUSINESS_LOG_FILE);
                }

                if ($compraDetalleProductoID === null || !is_numeric($compraDetalleProductoID) || $compraDetalleProductoID <= 0) {
                    $errors[] = "El campo 'ID de producto' está vacío o no es válido.";
                    Utils::writeLog("El campo 'ID de producto [$compraDetalleProductoID]' no es válido.", BUSINESS_LOG_FILE);
                }

                if ($compraDetallePrecioProducto === null || !is_numeric($compraDetallePrecioProducto) || $compraDetallePrecioProducto < 0) {
                    $errors[] = "El campo 'Precio del producto' está vacío o no es válido.";
                    Utils::writeLog("El campo 'Precio del producto [$compraDetallePrecioProducto]' no es válido.", BUSINESS_LOG_FILE);
                }

                if ($compraDetalleCantidad === null || !is_numeric($compraDetalleCantidad) || $compraDetalleCantidad < 0) {
                    $errors[] = "El campo 'Cantidad' está vacío o no es válida.";
                    Utils::writeLog("El campo 'Cantidad [$compraDetalleCantidad]' no es válido.", BUSINESS_LOG_FILE);
                }
            }

            // Lanza una excepción si hay errores
            if (!empty($errors)) {
                throw new Exception(implode('<br>', $errors));
            }

            return ["is_valid" => true];
        } catch (Exception $e) {
            return ["is_valid" => false, "message" => $e->getMessage()];
        }
    }

    public function insertCompraDetalle($compraDetalle) {
        // Verifica que los datos del detalle de compra sean válidos
        $check = $this->validarCompraDetalle($compraDetalle);
        if (!$check["is_valid"]) {
            return ["success" => $check["is_valid"], "message" => $check["message"]];
        }

        return $this->compraDetalleData->insertCompraDetalle($compraDetalle);
    }

    public function updateCompraDetalle($compraDetalle) {
        // Verifica que los datos del detalle de compra sean válidos
        $check = $this->validarCompraDetalle($compraDetalle);
        if (!$check["is_valid"]) {
            return ["success" => $check["is_valid"], "message" => $check["message"]];
        }
        
        return $this->compraDetalleData->updateCompraDetalle($compraDetalle);
    }

    public function deleteCompraDetalle($compraDetalleID) {
        return $this->compraDetalleData->deleteCompraDetalle($compraDetalleID);
    }

    public function getAllCompraDetalle() {
        return $this->compraDetalleData->getAllCompraDetalle();
    }

    public function getPaginatedCompraDetalles($page, $size) {
        return $this->compraDetalleData->getPaginatedCompraDetalles($page, $size);
    }

    public function getCompraDetalleByID($compraDetalleID) {
        return $this->compraDetalleData->getCompraDetalleByID($compraDetalleID);
    }
}
?>
