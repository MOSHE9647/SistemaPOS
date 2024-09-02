<?php

include __DIR__ . "/../data/tipoCompraData.php";
require_once __DIR__ . '/../utils/Utils.php';

class TipoCompraBusiness {

    private $tipoCompraData;

    public function __construct() {
        $this->tipoCompraData = new TipoCompraData();
    }

    public function validarTipoCompra($tipoCompra, $validarCamposAdicionales = true) {
        try {
            // Obtener los valores de las propiedades del objeto
            $tipoCompraID = $tipoCompra->getTipoCompraID();
            $nombre = $tipoCompra->getTipoCompraNombre();
            $tasaInteres = $tipoCompra->getTipoCompraTasaInteres();
            $plazos = $tipoCompra->getTipoCompraPlazos();
            $meses = $tipoCompra->getTipoCompraMeses();
            $compraProductoID = $tipoCompra->getTipoCompraCompraProductoID();
            $errors = [];

            // Verifica que el ID del TipoCompra sea válido
            if ($tipoCompraID === null || !is_numeric($tipoCompraID) || $tipoCompraID < 0) {
                $errors[] = "El ID del TipoCompra está vacío o no es válido. Revise que este sea un número y que sea mayor a 0.";
                Utils::writeLog("El ID [$tipoCompraID] del TipoCompra no es válido.", BUSINESS_LOG_FILE);
            }

            // Si la validación de campos adicionales está activada, valida los otros campos
            if ($validarCamposAdicionales) {
                if ($nombre === null || empty($nombre) || is_numeric($nombre)) {
                    $errors[] = "El campo 'Nombre' está vacío o no es válido.";
                    Utils::writeLog("[TipoCompra] El campo 'Nombre [$nombre]' no es válido.", BUSINESS_LOG_FILE);
                }
                if ($tasaInteres === null || !is_numeric($tasaInteres) || $tasaInteres < 0) {
                    $errors[] = "El campo 'Tasa de Interés' está vacío o no es válido. Revise que este sea un número y que sea mayor o igual a 0.";
                    Utils::writeLog("[TipoCompra] El campo 'Tasa de Interés [$tasaInteres]' no es válido.", BUSINESS_LOG_FILE);
                }
                if ($plazos === null || !is_numeric($plazos) || $plazos <= 0) {
                    $errors[] = "El campo 'Plazos' está vacío o no es válido. Revise que este sea un número y que sea mayor a 0.";
                    Utils::writeLog("[TipoCompra] El campo 'Plazos [$plazos]' no es válido.", BUSINESS_LOG_FILE);
                }
                if ($meses === null || !is_numeric($meses) || $meses <= 0) {
                    $errors[] = "El campo 'Meses' está vacío o no es válido. Revise que este sea un número y que sea mayor a 0.";
                    Utils::writeLog("[TipoCompra] El campo 'Meses [$meses]' no es válido.", BUSINESS_LOG_FILE);
                }
                if ($compraProductoID === null || !is_numeric($compraProductoID) || $compraProductoID < 0) {
                    $errors[] = "El ID de CompraProducto no es válido.";
                    Utils::writeLog("[TipoCompra] El ID de CompraProducto [$compraProductoID] no es válido.", BUSINESS_LOG_FILE);
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

    public function insertTBTipoCompra($tipoCompra) {
        // Verifica que los datos de TipoCompra sean válidos
        $check = $this->validarTipoCompra($tipoCompra);
        if (!$check["is_valid"]) {
            return ["success" => $check["is_valid"], "message" => $check["message"]];
        }

        return $this->tipoCompraData->insertTipoCompra($tipoCompra);
    }

    public function updateTBTipoCompra($tipoCompra) {
        // Verifica que los datos de TipoCompra sean válidos
        $check = $this->validarTipoCompra($tipoCompra);
        if (!$check["is_valid"]) {
            return ["success" => $check["is_valid"], "message" => $check["message"]];
        }

        return $this->tipoCompraData->updateTipoCompra($tipoCompra);
    }

    public function deleteTBTipoCompra($tipoCompra) {
        // Verifica que los datos de TipoCompra sean válidos
        $check = $this->validarTipoCompra($tipoCompra, false);
        if (!$check["is_valid"]) {
            return ["success" => $check["is_valid"], "message" => $check["message"]];
        }

        $tipoCompraID = $tipoCompra->getTipoCompraID(); // Obtenemos el ID verificado del TipoCompra
        unset($tipoCompra); // Eliminamos el objeto para no ocupar espacio en memoria (en caso de ser necesario)
        return $this->tipoCompraData->deleteTipoCompra($tipoCompraID);
    }

    public function getAllTBTipoCompra() {
        return $this->tipoCompraData->getAllTBTipoCompra();
    }

    public function getPaginatedTipoCompra($page, $size, $sort = null) {
        return $this->tipoCompraData->getPaginatedTipoCompra($page, $size, $sort);
    }

}

?>
