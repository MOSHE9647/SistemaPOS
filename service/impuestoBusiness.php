<?php

    include __DIR__ . "/../data/impuestoData.php";
    require_once __DIR__ . '/../utils/Utils.php';

    class ImpuestoBusiness {

        private $impuestoData;

        public function __construct() {
            $this->impuestoData = new ImpuestoData();
        }

        public function validarImpuesto($impuesto, $validarCamposAdicionales = true) {
            try {
                // Obtener los valores de las propiedades del objeto
                $impuestoID = $impuesto->getImpuestoID();
                $nombre = $impuesto->getImpuestoNombre();
                $valor = $impuesto->getImpuestoValor();
                $fechaVigencia = $impuesto->getImpuestoFechaVigencia();
                $errors = [];

                // Verifica que el ID del impuesto sea válido
                if ($impuestoID === null || !is_numeric($impuestoID) || $impuestoID < 0) {
                    $errors[] = "El ID del impuesto está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                    Utils::writeLog("El ID [$impuestoID] del impuesto no es válido.", BUSINESS_LOG_FILE);
                }

                // Si la validación de campos adicionales está activada, valida los otros campos
                if ($validarCamposAdicionales) {
                    if ($nombre === null || empty($nombre) || is_numeric($nombre)) {
                        $errors[] = "El campo 'Nombre' está vacío o no es válido.";
                        Utils::writeLog("[Impuesto] El campo 'Nombre [$nombre]' no es válido.", BUSINESS_LOG_FILE);
                    }
                    if ($valor === null || empty($valor) || !is_numeric($valor) || $valor <= 0) {
                        $errors[] = "El campo 'Valor' está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                        Utils::writeLog("[Impuesto] El campo 'Valor [$valor]' no es válido.", BUSINESS_LOG_FILE);
                    }
                    if (empty($fechaVigencia) || !Utils::validarFecha($fechaVigencia)) {
                        $errors[] = "El campo 'Fecha Vigencia' está vacío o no es válido.";
                        Utils::writeLog("[Impuesto] El campo 'Fecha Vigencia [$fechaVigencia]' está vacío o no es válido.", BUSINESS_LOG_FILE);
                    }
                    // Verificar si la fecha de vigencia es menor o igual a la de hoy
                    if (!Utils::fechaMenorOIgualAHoy($fechaVigencia)) {
                        $errors[] = "El campo 'Fecha Vigencia' no puede ser mayor a la de hoy. Revise que la fecha sea menor o igual a la de hoy.";
                        Utils::writeLog("[Impuesto] El campo 'Fecha Vigencia [$fechaVigencia]' es mayor a la de hoy.", BUSINESS_LOG_FILE);
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
            // Verifica que los datos de la direcion sean validos
            $check = $this->validarImpuesto($impuesto);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->impuestoData->insertImpuesto($impuesto);
        }

        public function updateTBImpuesto($impuesto) {
            // Verifica que los datos de la direcion sean validos
            $check = $this->validarImpuesto($impuesto);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->impuestoData->updateImpuesto($impuesto);
        }

        public function deleteTBImpuesto($impuesto) {
            // Verifica que los datos de la direcion sean validos
            $check = $this->validarImpuesto($impuesto, false);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            $impuestoID = $impuesto->getImpuestoID(); //<- Obtenemos el ID verificado del Impuesto
            unset($impuesto); //<- Eliminamos el objeto para no ocupar espacio en memoria (en caso de ser necesario)
            return $this->impuestoData->deleteImpuesto($impuestoID);
        }

        public function getAllTBImpuesto() {
            return $this->impuestoData->getAllTBImpuesto();
        }

        public function getPaginatedImpuestos($page, $size, $sort = null) {
            return $this->impuestoData->getPaginatedImpuestos($page, $size, $sort);
        }

    }

?>