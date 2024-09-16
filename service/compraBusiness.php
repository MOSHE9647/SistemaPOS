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
            $proveedorid = $compra->getProveedorID();
            $errors = [];

            if ($compraID === null || !is_numeric($compraID) || $compraID < 0) {
                $errors[] = "El ID de la compra está vacío o no es válido. Debe ser un número mayor a 0.";
                Utils::writeLog("El ID [$compraID] de la compra no es válido.", BUSINESS_LOG_FILE);
            }

            // Si la validación de campos adicionales está activada, valida los otros campos
            if ($validarCamposAdicionales) {
                if (empty($compraNumeroFactura)) {
                    $errors[] = "El campo 'Número de factura' está vacío.";
                    Utils::writeLog("El campo 'Número de factura [$compraNumeroFactura]' está vacío.", BUSINESS_LOG_FILE);
                }

                if ($compraMontoBruto === null || !is_numeric($compraMontoBruto) || $compraMontoBruto < 0) {
                    $errors[] = "El campo 'Monto bruto' está vacío o no es válido.";
                    Utils::writeLog("El campo 'Monto bruto [$compraMontoBruto]' no es válido.", BUSINESS_LOG_FILE);
                }

                if ($compraMontoNeto === null || !is_numeric($compraMontoNeto) || $compraMontoNeto < 0) {
                    $errors[] = "El campo 'Monto neto' está vacío o no es válido.";
                    Utils::writeLog("El campo 'Monto neto [$compraMontoNeto]' no es válido.", BUSINESS_LOG_FILE);
                }

                if (empty($compraTipoPago)) {
                    $errors[] = "El campo 'Tipo de pago' está vacío.";
                    Utils::writeLog("El campo 'Tipo de pago [$compraTipoPago]' está vacío.", BUSINESS_LOG_FILE);
                }

                if ($proveedorid === null || !is_numeric($proveedorid) || $proveedorid < 0) {
                    $errors[] = "El campo 'Proveedor ID' está vacío o no es válido.";
                    Utils::writeLog("El campo 'Proveedor ID [$proveedorid]' no es válido.", BUSINESS_LOG_FILE);
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
        // Verifica que los datos de la compra sean válidos
        $check = $this->validarCompra($compra);
        if (!$check["is_valid"]) {
            return ["success" => false, "message" => $check["message"]];
        }

        return $this->compraData->insertCompra($compra);
    }

    public function updateTBCompra($compra) {
        // Verifica que los datos de la compra sean válidos
        $check = $this->validarCompra($compra);
        if (!$check["is_valid"]) {
            return ["success" => false, "message" => $check["message"]];
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
