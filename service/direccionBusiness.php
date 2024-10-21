<?php

    require_once dirname(__DIR__, 1) . "/data/direccionData.php";
    require_once dirname(__DIR__, 1) . "/utils/Utils.php";

    class DireccionBusiness {

        private $direccionData;
        private $className;

        public function __construct() {
            $this->direccionData = new DireccionData();
            $this->className = get_class($this);
        }

        public function validarDireccionID($direccionID) {
            if ($direccionID === null || !is_numeric($direccionID) || $direccionID < 0) {
                Utils::writeLog("El ID [$direccionID] de la dirección no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ["is_valid" => false, "message" => "El ID de la dirección está vacío o no es válido. Revise que este sea un número y que sea mayor a 0"];
            }

            return ["is_valid" => true];
        }

        public function validarDireccion($direccion, $validarCamposAdicionales = true, $insert = false) {
            try {
                // Obtener los valores de las propiedades del objeto
                $direccionID = $direccion->getDireccionID();
                $provincia = $direccion->getDireccionProvincia();
                $canton = $direccion->getDireccionCanton();
                $distrito = $direccion->getDireccionDistrito();
                $distancia = $direccion->getDireccionDistancia();
                $errors = [];

                // Verifica que el ID de la dirección sea válido
                if (!$insert) {
                    $checkID = $this->validarDireccionID($direccionID);
                    if (!$checkID['is_valid']) { $errors[] = $checkID['message']; }
                }
        
                // Si la validación de campos adicionales está activada, valida los otros campos
                if ($validarCamposAdicionales) {
                    if ($provincia === null || empty($provincia) || is_numeric($provincia)) {
                        $errors[] = "El campo 'Provincia' está vacío o no es válido.";
                        Utils::writeLog("El campo 'Provincia [$provincia]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                    }
                    if ($canton === null || empty($canton) || is_numeric($canton)) {
                        $errors[] = "El campo 'Cantón' está vacío o no es válido.";
                        Utils::writeLog("El campo 'Cantón [$canton]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                    }
                    if ($distrito === null || empty($distrito) || is_numeric($distrito)) {
                        $errors[] = "El campo 'Distrito' está vacío o no es válido.";
                        Utils::writeLog("El campo 'Distrito [$distrito]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                    }
                    if ($distancia === null || empty($distancia) || !is_numeric($distancia) || $distancia <= 0) {
                        $errors[] = "El campo 'Distancia' está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                        Utils::writeLog("El campo 'Distancia [$distancia]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
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

        public function insertTBDireccion($direccion) {
            // Verifica que los datos de la direcion sean validos
            $check = $this->validarDireccion($direccion, true, true);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->direccionData->insertDireccion($direccion);
        }

        public function updateTBDireccion($direccion) {
            // Verifica que los datos de la direcion sean validos
            $check = $this->validarDireccion($direccion);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }
            
            return $this->direccionData->updateDireccion($direccion);
        }

        public function deleteTBDireccion($direccionID) {
            // Verifica que los datos de la direccion sean validos
            $check = $this->validarDireccionID($direccionID);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->direccionData->deleteDireccion($direccionID);
        }

        public function existeDireccion($direccion = null, $update = false, $insert = false) {
            // Verifica que los datos de la direcion sean validos
            $check = $this->validarDireccion($direccion, $update, $insert);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->direccionData->existeDireccion($direccion, $update, $insert);
        }

        public function getAllTBDireccion($onlyActive = false, $deleted = false) {
            return $this->direccionData->getAllTBDireccion($onlyActive, $deleted);
        }

        public function getPaginatedDirecciones($page, $size, $sort = null, $onlyActive = true, $deleted = false) {
            return $this->direccionData->getPaginatedDirecciones($page, $size, $sort, $onlyActive, $deleted);
        }

        public function getDireccionByID($direccionID, $onlyActive = true, $deleted = false) {
            // Verifica que el ID de la direccion sea valido
            $checkID = $this->validarDireccionID($direccionID);
            if (!$checkID["is_valid"]) {
                return ["success" => $checkID["is_valid"], "message" => $checkID["message"]];
            }

            return $this->direccionData->getDireccionByID($direccionID, $onlyActive, $deleted);
        }

    }

?>