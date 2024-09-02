<?php

include __DIR__ . "/../data/loteData.php";

class LoteBusiness {

    private $loteData;

    public function __construct() {
        $this->loteData = new LoteData();
    }

    public function validarLote($lote, $validarCamposAdicionales = true) {
        try {
            // Obtener los valores de las propiedades del objeto
            $loteID = $lote->getLoteID();
            $loteCodigo = $lote->getLoteCodigo();
            $loteFechaVencimiento = $lote->getLoteFechaVencimiento();
            $loteEstado = $lote->getLoteEstado();
            $errors = [];

            // Verifica que el ID del lote sea válido
            if ($loteID === null || !is_numeric($loteID) || $loteID < 0) {
                $errors[] = "El ID del lote está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                Utils::writeLog("El ID [$loteID] del lote no es válido.", BUSINESS_LOG_FILE);
            }

            // Si la validación de campos adicionales está activada, valida los otros campos
            if ($validarCamposAdicionales) {
                
                if ($loteCodigo === null || empty($loteCodigo)) {
                    $errors[] = "El campo 'Código del lote' está vacío o no es válido.";
                    Utils::writeLog("El campo 'Código del lote [$loteCodigo]' no es válido.", BUSINESS_LOG_FILE);
                }

                if ($loteFechaVencimiento === null || empty($loteFechaVencimiento)) {
                    $errors[] = "El campo 'Fecha de vencimiento del lote' está vacío o no es válido.";
                    Utils::writeLog("El campo 'Fecha de vencimiento del lote [$loteFechaVencimiento]' no es válido.", BUSINESS_LOG_FILE);
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

    public function insertTBLote($lote) {
        // Verifica que los datos del lote sean válidos
        $check = $this->validarLote($lote);
        if (!$check["is_valid"]) {
            return ["success" => $check["is_valid"], "message" => $check["message"]];
        }

        return $this->loteData->insertLote($lote);
    }

    public function updateTBLote($lote) {
        // Verifica que los datos del lote sean válidos
        $check = $this->validarLote($lote);
        if (!$check["is_valid"]) {
            return ["success" => $check["is_valid"], "message" => $check["message"]];
        }
        
        return $this->loteData->updateLote($lote);
    }

    public function deleteTBLote($loteID) {
        return $this->loteData->deleteLote($loteID);
    }

    public function getAllTBLote() {
        return $this->loteData->getAllTBLote();
    }

    public function getPaginatedLotes($page, $size) {
        return $this->loteData->getPaginatedLotes($page, $size);
    }

    public function getLoteByID($loteID) {
        return $this->loteData->getLoteByID($loteID);
    }
}

?>
