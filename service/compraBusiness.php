<?php

require_once __DIR__ . "/../data/compraData.php";

class CompraBusiness {

    private $compraData;

    public function __construct() {
        $this->compraData = new CompraData();
    }

    public function validarCompra($compra, $validarCamposAdicionales = true) {
        try {
            // Obtener los valores de las propiedades del objeto
            $compraID = $compra->getCompraID();
            $compraNumeroFactura = $compra->getCompraNumeroFactura();
            $compraMontoBruto = $compra->getCompraMontoBruto();
            $compraMontoNeto = $compra->getCompraMontoNeto();
            $compraTipoPago = $compra->getCompraTipoPago();
            $compraProveedorId = $compra->getCompraProveedorId();
            $compraFechaCreacion = $compra->getCompraFechaCreacion();
            $compraFechaModificacion = $compra->getCompraFechaModificacion();
            $compraEstado = $compra->getCompraEstado();
            $errors = [];

            if ($compraID === null || !is_numeric($compraID) || $compraID < 0) {
                $errors[] = "El ID de la compra está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                Utils::writeLog("El ID [$compraID] de la compra no es válido.", BUSINESS_LOG_FILE);
            }

            // Si la validación de campos adicionales está activada, valida los otros campos
            if ($validarCamposAdicionales) {
                if ($compraNumeroFactura === null || empty($compraNumeroFactura)) {
                    $errors[] = "El campo 'Número de factura' está vacío o no es válido.";
                    Utils::writeLog("El campo 'Número de factura [$compraNumeroFactura]' no es válido.", BUSINESS_LOG_FILE);
                }

                if ($compraMontoBruto === null || !is_numeric($compraMontoBruto) || $compraMontoBruto < 0) {
                    $errors[] = "El campo 'Monto bruto' está vacío o no es válido.";
                    Utils::writeLog("El campo 'Monto bruto [$compraMontoBruto]' no es válido.", BUSINESS_LOG_FILE);
                }

                if ($compraMontoNeto === null || !is_numeric($compraMontoNeto) || $compraMontoNeto < 0) {
                    $errors[] = "El campo 'Monto neto' está vacío o no es válido.";
                    Utils::writeLog("El campo 'Monto neto [$compraMontoNeto]' no es válido.", BUSINESS_LOG_FILE);
                }

                if ($compraTipoPago === null || empty($compraTipoPago)) {
                    $errors[] = "El campo 'Tipo de pago' está vacío o no es válido.";
                    Utils::writeLog("El campo 'Tipo de pago [$compraTipoPago]' no es válido.", BUSINESS_LOG_FILE);
                }

                if ($compraProveedorId === null || !is_numeric($compraProveedorId) || $compraProveedorId < 0) {
                    $errors[] = "El campo 'ID del proveedor' está vacío o no es válido.";
                    Utils::writeLog("El campo 'ID del proveedor [$compraProveedorId]' no es válido.", BUSINESS_LOG_FILE);
                }

                if ($compraFechaModificacion === null || empty($compraFechaModificacion)) {
                    $errors[] = "El campo 'Fecha de modificacion' está vacío o no es válido.";
                    Utils::writeLog("El campo 'Fecha de modificacion [$compraFechaModificacion]' no es válido.", BUSINESS_LOG_FILE);
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

    public function insertTBCompra($compra) {
        // Verifica que los datos del lote sean válidos
        $check = $this->validarCompra($compra);
        if (!$check["is_valid"]) {
            return ["success" => $check["is_valid"], "message" => $check["message"]];
        }

        return $this->compraData->insertCompra($compra);
    }

    public function updateTBCompra($compra) {
        // Verifica que los datos del lote sean válidos
        $check = $this->validarCompra($compra);
        if (!$check["is_valid"]) {
            return ["success" => $check["is_valid"], "message" => $check["message"]];
        }
        
        return $this->compraData->updateCompra($compra);
    }

    public function deleteTBCompra($compraID) {
        return $this->compraData->deleteCompra($compraID);
    }

    public function getAllTBCompra() {
        return $this->compraData->getAllTBCompra();
    }

    public function getPaginatedCompras($page, $size) {
        return $this->compraData->getPaginatedCompras($page, $size);
    }

    public function getCompraByID($compraID) {
        return $this->compraData->getCompraByID($compraID);
    }
}
?>