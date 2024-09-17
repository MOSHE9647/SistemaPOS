<?php

require_once __DIR__ . "/../data/cuentaPorPagarData.php";

class CuentaPorPagarBusiness {

    private $cuentaPorPagarData;

    public function __construct() {
        $this->cuentaPorPagarData = new CuentaPorPagarData();
    }

    public function validarCuentaPorPagar($cuentaPorPagar, $validarCamposAdicionales = true) {
        try {
            // Obtener los valores de las propiedades del objeto
            $cuentaporpagarid = $cuentaPorPagar->getCuentaporpagarid();
            $cuentaporpagarcompradetalleid = $cuentaPorPagar->getCuentaporpagarcompradetalleid();
            $cuentaporpagarfechavencimiento = $cuentaPorPagar->getCuentaporpagarfechavencimiento();
            $cuentaporpagarmontototal = $cuentaPorPagar->getCuentaporpagarmontototal();
            $cuentaporpagarmontopagado = $cuentaPorPagar->getCuentaporpagarmontopagado();
            $cuentaporpagarfechapago = $cuentaPorPagar->getCuentaporpagarfechapago();
            $cuentaporpagarnotas = $cuentaPorPagar->getCuentaporpagarnotas();
            $cuentaporpagarestadocuenta = $cuentaPorPagar->getCuentaporpagarestadocuenta();
            $cuentaporpagarestado = $cuentaPorPagar->getCuentaporpagarestado();
            $errors = [];

            // Verifica que el ID de cuenta por pagar sea válido
            if ($cuentaporpagarid === null || !is_numeric($cuentaporpagarid) || $cuentaporpagarid < 0) {
                $errors[] = "El ID de cuenta por pagar está vacío o no es válido. Revise que sea un número y que sea mayor a 0";
                Utils::writeLog("El ID [$cuentaporpagarid] de cuenta por pagar no es válido.", BUSINESS_LOG_FILE);
            }
           
            
            // Si la validación de campos adicionales está activada, valida los otros campos
            if ($validarCamposAdicionales) {
               if ($cuentaporpagarcompradetalleid === null || !is_numeric($cuentaporpagarcompradetalleid) || intval($cuentaporpagarcompradetalleid) <= 0) {
                    $errors[] = "El campo Compra Detalle solo puede contener números.";
                    Utils::writeLog("El campo 'ID de detalle de compra [$cuentaporpagarcompradetalleid]' no es válido.", BUSINESS_LOG_FILE);
                }
                

                if ($cuentaporpagarfechavencimiento === null || empty($cuentaporpagarfechavencimiento)) {
                    $errors[] = "El campo 'Fecha de vencimiento' está vacío o no es válido.";
                    Utils::writeLog("El campo 'Fecha de vencimiento [$cuentaporpagarfechavencimiento]' no es válido.", BUSINESS_LOG_FILE);
                }

                if ($cuentaporpagarmontototal === null || !is_numeric($cuentaporpagarmontototal) || $cuentaporpagarmontototal < 0) {
                    $errors[] = "El campo 'Monto total' está vacío o no es válido.";
                    Utils::writeLog("El campo 'Monto total [$cuentaporpagarmontototal]' no es válido.", BUSINESS_LOG_FILE);
                }

                if ($cuentaporpagarmontopagado === null || !is_numeric($cuentaporpagarmontopagado) || $cuentaporpagarmontopagado < 0) {
                    $errors[] = "El campo 'Monto pagado' está vacío o no es válido.";
                    Utils::writeLog("El campo 'Monto pagado [$cuentaporpagarmontopagado]' no es válido.", BUSINESS_LOG_FILE);
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

    public function insertCuentaPorPagar($cuentaPorPagar) {
        // Verifica que los datos de la cuenta por pagar sean válidos
        $check = $this->validarCuentaPorPagar($cuentaPorPagar);
        if (!$check["is_valid"]) {
            return ["success" => $check["is_valid"], "message" => $check["message"]];
        }

        return $this->cuentaPorPagarData->insertCuentaPorPagar($cuentaPorPagar);
    }

    public function updateCuentaPorPagar($cuentaPorPagar) {
        // Verifica que los datos de la cuenta por pagar sean válidos
        $check = $this->validarCuentaPorPagar($cuentaPorPagar);
        if (!$check["is_valid"]) {
            return ["success" => $check["is_valid"], "message" => $check["message"]];
        }
        
        return $this->cuentaPorPagarData->updateCuentaPorPagar($cuentaPorPagar);
    }

    public function deleteCuentaPorPagar($cuentaporpagarid) {
        return $this->cuentaPorPagarData->deleteCuentaPorPagar($cuentaporpagarid);
    }

    public function getAllCuentaPorPagar() {
        return $this->cuentaPorPagarData->getAllCuentaPorPagar();
    }

    public function getPaginatedCuentaPorPagar($page, $size) {
        return $this->cuentaPorPagarData->getPaginatedCuentaPorPagar($page, $size);
    }

    public function getCuentaPorPagarByID($cuentaporpagarid) {
        return $this->cuentaPorPagarData->getCuentaPorPagarByID($cuentaporpagarid);
    }
}
?>
