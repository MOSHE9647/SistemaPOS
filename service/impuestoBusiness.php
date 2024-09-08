<?php

    require_once __DIR__ . "/../data/impuestoData.php";
    require_once __DIR__ . '/../utils/Utils.php';

    class ImpuestoBusiness {

        private $impuestoData;
        private $className;

        public function __construct() {
            $this->impuestoData = new ImpuestoData();
            $this->className = get_class($this);
        }

        public function validarImpuestoID($impuestoID) {
            if ($impuestoID === null || !is_numeric($impuestoID) || $impuestoID < 0) {
                Utils::writeLog("El ID [$impuestoID] del impuesto no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ["is_valid" => false, "message" => "El ID del impuesto está vacío o no es válido. Revise que este sea un número y que sea mayor a 0"];
            }

            return ["is_valid" => true];
        }

        public function validarImpuesto($impuesto, $validarCamposAdicionales = true, $insert = false) {
            try {
                // Obtener los valores de las propiedades del objeto
                $impuestoID = $impuesto->getImpuestoID();
                $nombre = $impuesto->getImpuestoNombre();
                $valor = $impuesto->getImpuestoValor();
                $fechaVigencia = $impuesto->getImpuestoFechaVigencia();
                $errors = [];

                // Verifica que el ID del impuesto sea válido
                if (!$insert) {
                    $checkID = $this->validarImpuestoID($impuestoID);
                    if (!$checkID['is_valid']) { $errors[] = $checkID['message']; }
                }

                // Si la validación de campos adicionales está activada, valida los otros campos
                if ($validarCamposAdicionales) {
                    if ($nombre === null || empty($nombre) || is_numeric($nombre)) {
                        $errors[] = "El campo 'Nombre' está vacío o no es válido.";
                        Utils::writeLog("El campo 'Nombre [$nombre]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                    }
                    if ($valor === null || empty($valor) || !is_numeric($valor) || $valor <= 0 || $valor > 100) {
                        $errors[] = "El campo 'Valor' está vacío o no es válido. Revise que este sea un número y que sea mayor a 0 y menor o igual a 100";
                        Utils::writeLog("El campo 'Valor [$valor]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                    }
                    if (empty($fechaVigencia) || !Utils::validarFecha($fechaVigencia)) {
                        $errors[] = "El campo 'Fecha Vigencia' está vacío o no es válido.";
                        Utils::writeLog("El campo 'Fecha Vigencia [$fechaVigencia]' está vacío o no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                    }
                    if (!Utils::fechaMenorOIgualAHoy($fechaVigencia)) {
                        $errors[] = "El campo 'Fecha Vigencia' no puede ser una fecha mayor a la de hoy. Revise que la fecha sea menor o igual a la de hoy.";
                        Utils::writeLog("El campo 'Fecha Vigencia [$fechaVigencia]' es mayor a la de hoy.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
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

        public function insertTBImpuesto($impuesto) {
            // Verifica que los datos del impuesto sean validos
            $check = $this->validarImpuesto($impuesto, true, true);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->impuestoData->insertImpuesto($impuesto);
        }

        public function updateTBImpuesto($impuesto) {
            // Verifica que los datos del impuesto sean validos
            $check = $this->validarImpuesto($impuesto);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->impuestoData->updateImpuesto($impuesto);
        }

        public function deleteTBImpuesto($impuestoID) {
            // Verifica que el ID del impuesto sea valido
            $checkID = $this->validarImpuestoID($impuestoID);
            if (!$checkID["is_valid"]) {
                return ["success" => $checkID["is_valid"], "message" => $checkID["message"]];
            }

            return $this->impuestoData->deleteImpuesto($impuestoID);
        }

        public function getAllTBImpuesto($onlyActiveOrInactive = false, $deleted = false) {
            return $this->impuestoData->getAllTBImpuesto($onlyActiveOrInactive, $deleted);
        }

        public function getPaginatedImpuestos($page, $size, $sort = null, $onlyActiveOrInactive = true, $deleted = false) {
            return $this->impuestoData->getPaginatedImpuestos($page, $size, $sort, $onlyActiveOrInactive, $deleted);
        }

        public function getImpuestoByID($impuestoID, $json = true) {
            // Verifica que el ID del impuesto sea valido
            $checkID = $this->validarImpuestoID($impuestoID);
            if (!$checkID["is_valid"]) {
                return ["success" => $checkID["is_valid"], "message" => $checkID["message"]];
            }

            return $this->impuestoData->getImpuestoByID($impuestoID, $json);
        }

    }

?>