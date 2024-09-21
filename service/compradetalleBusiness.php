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
            $compraDetalleID = $compraDetalle->getCompraDetalleID();
           // $compraDetalleCompraID = $compraDetalle->getCompraDetalleCompraID();
           $compraID = $compraDetalle->getCompraID();
            $loteID = $compraDetalle->getLoteID();
            //$compraDetalleLoteID = $compraDetalle->getCompraDetalleLoteID();
            $productoID = $compraDetalle->getProductoID();
            $compraDetallePrecioProducto = $compraDetalle->getCompraDetallePrecioProducto();
            $compraDetalleCantidad = $compraDetalle->getCompraDetalleCantidad();
            $errors = [];

            // Verifica que el ID del detalle de compra sea válido
            //if ($compraDetalleID === null || !is_numeric($compraDetalleID) || $compraDetalleID <= 0) {
                //$errors[] = "El ID del detalle de compra está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
              //  Utils::writeLog("El ID [$compraDetalleID] del detalle de compra no es válido.", BUSINESS_LOG_FILE);
            //}

            // Si la validación de campos adicionales está activada, valida los otros campos
            if ($validarCamposAdicionales) {

                if ($compraID === null ||  $compraID <= 0) {
                    $errors[] = "No ha seleccionado ninguna opcion del campo 'Compra'.";
                    Utils::writeLog("El campo 'ID de compra [$compraID]' no es válido.", BUSINESS_LOG_FILE);
                }

                if ($loteID === null ||  $loteID <= 0) {
                    $errors[] = "No ha seleccionado ninguna opcion del campo 'Lote'.";
                    Utils::writeLog("El campo 'ID de lote [$loteID]' no es válido.", BUSINESS_LOG_FILE);
                }

                if ($productoID== null || $productoID<=0 ) {
                    $errors[] = "No ha seleccionado ninguna opcion del campo 'Producto'.";
                    Utils::writeLog("El campo 'ID de producto [$productoID]' no es válido.", BUSINESS_LOG_FILE);
                }

                if (empty($compraDetallePrecioProducto)) {
                    $errors[] = "El campo 'Precio producto' está vacío.";
                    Utils::writeLog("El campo 'Precio del producto [$compraDetallePrecioProducto]' no es válido.", BUSINESS_LOG_FILE);
                } elseif ($compraDetallePrecioProducto < 0) {
                    $errors[] = "El campo 'Precio producto' tiene que ser positivo.";
                    Utils::writeLog("El campo 'Precio del producto [$compraDetallePrecioProducto]' no es válido.", BUSINESS_LOG_FILE);
                }
              

                if (empty($compraDetalleCantidad)) {
                    $errors[] = "El campo 'Cantidad' está vacío.";
                    Utils::writeLog("El campo 'Cantidad [$compraDetalleCantidad]' no es válido.", BUSINESS_LOG_FILE);
                }elseif ($compraDetalleCantidad < 0) {
                    $errors[] = "El campo 'Cantidad'tiene que ser positivo.";
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
        return $this->compraDetalleData->getAllCompraDetalles();
    }

    public function getPaginatedCompraDetalles($page, $size) {
        return $this->compraDetalleData->getPaginatedCompraDetalles($page, $size);
    }

    public function getCompraDetalleByID($compraDetalleID) {
        return $this->compraDetalleData->getCompraDetalleByID($compraDetalleID);
    }
}
?>
