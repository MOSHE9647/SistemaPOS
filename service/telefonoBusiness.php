<?php

    require_once __DIR__ . '/../data/telefonoData.php';

    class TelefonoBusiness {

        private $className;
        private $telefonoData;

        public function __construct() {
            $this->className = get_class($this);
            $this->telefonoData = new TelefonoData();
        }

        public function validarTelefonoID($telefonoID) {
            if ($telefonoID === null || !is_numeric($telefonoID) || $telefonoID < 0) {
                Utils::writeLog("El ID [$telefonoID] del teléfono no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                return ["is_valid" => false, "message" => "El ID del teléfono está vacío o no es válido. Revise que este sea un número y que sea mayor a 0"];
            }

            return ["is_valid" => true];
        }

        public function validarTelefono($telefono, $validarCamposAdicionales = true, $insert = false) {
            try {
                // Obtener los valores de las propiedades del objeto
                $telefonoID = $telefono->getTelefonoID();
                $tipo = $telefono->getTelefonoTipo();
                $codigoPais = $telefono->getTelefonoCodigoPais();
                $numero = $telefono->getTelefonoNumero();
                $errors = [];

                // Verifica que el ID del Telefono sea válido
                if (!$insert) {
                    $checkID = $this->validarTelefonoID($telefonoID);
                    if (!$checkID['is_valid']) { $errors[] = $checkID['message']; }
                }

                // Si la validación de campos adicionales está activada, valida los otros campos
                if ($validarCamposAdicionales) {
                    if ($tipo === null || empty($tipo) || is_numeric($tipo)) {
                        $errors[] = "El campo 'Tipo' está vacío o no es válido.";
                        Utils::writeLog("El campo 'Tipo [$tipo]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    }
                    if ($codigoPais === null || empty($codigoPais)) {
                        $errors[] = "El campo 'Código de País' está vacío o no es válido.";
                        Utils::writeLog("El campo 'Código de País [$codigoPais]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    }
                    if ($numero === null || empty($numero) || is_numeric($numero)) {
                        $errors[] = "El campo 'Número de Teléfono' está vacío o no es válido.";
                        Utils::writeLog("El campo 'Número de Teléfono [$numero]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
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

        public function insertTBTelefono($telefono) {
            // Verifica que los datos del telefono sean validos
            $check = $this->validarTelefono($telefono, true, true);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->telefonoData->insertTelefono($telefono);
        }

        public function updateTBTelefono($telefono) {
            // Verifica que los datos del telefono sean validos
            $check = $this->validarTelefono($telefono);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->telefonoData->updateTelefono($telefono);
        }

        public function deleteTBTelefono($telefonoID) {
            // Verifica que los datos del telefono sean validos
            $checkID = $this->validarTelefonoID($telefonoID);
            if (!$checkID["is_valid"]) {
                return ["success" => $checkID["is_valid"], "message" => $checkID["message"]];
            }

            return $this->telefonoData->deleteTelefono($telefonoID);
        }

        public function existeTelefono($telefono = null, $update = false, $insert = false) {
            // Verifica que los datos del telefono sean validos
            $check = $this->validarTelefono($telefono, $update, $insert);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            // Obtener los valores de las propiedades del objeto
            $ID = $telefono->getTelefonoID();
            $codigoPais = $telefono->getTelefonoCodigoPais();
            $numero = $telefono->getTelefonoNumero();

            // Verifica si el telefono ya existe
            return $this->telefonoData->existeTelefono($ID, $codigoPais, $numero, $update, $insert);
        }

        public function getAllTBTelefono($onlyActive = false, $deleted = false) {
            return $this->telefonoData->getAllTBTelefono($onlyActive, $deleted);
        }

        public function getPaginatedTelefonos($page, $size, $sort = null, $onlyActive = true, $deleted = false) {
            return $this->telefonoData->getPaginatedTelefonos($page, $size, $sort, $onlyActive, $deleted);
        }

        public function getTelefonoByID($telefonoID, $onlyActive = true, $deleted = false) {
            // Verifica que el ID del telefono sea valido
            $checkID = $this->validarTelefonoID($telefonoID);
            if (!$checkID["is_valid"]) {
                return ["success" => $checkID["is_valid"], "message" => $checkID["message"]];
            }

            return $this->telefonoData->getTelefonoByID($telefonoID, $onlyActive, $deleted);
        }

    }

?>