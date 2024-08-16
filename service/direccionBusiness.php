<?php

    include __DIR__ . "/../data/direccionData.php";

    class DireccionBusiness {

        private $direccionData;

        public function __construct() {
            $this->direccionData = new DireccionData();
        }

        public function validarDireccion($direccion, $validarCamposAdicionales = true) {
            try {
                // Obtener los valores de las propiedades del objeto
                $direccionID = $direccion->getDireccionID();
                $provincia = $direccion->getDireccionProvincia();
                $canton = $direccion->getDireccionCanton();
                $distrito = $direccion->getDireccionDistrito();
                $distancia = $direccion->getDireccionDistancia();
                $errors = [];

                // Verifica que el ID de la dirección sea válido
                if ($direccionID === null || !is_numeric($direccionID) || $direccionID < 0) {
                    $errors[] = "El ID de la dirección está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                    Utils::writeLog("El ID [$direccionID] de la dirección no es válido.", BUSINESS_LOG_FILE);
                }
        
                // Si la validación de campos adicionales está activada, valida los otros campos
                if ($validarCamposAdicionales) {
                    if ($provincia === null || empty($provincia) || is_numeric($provincia)) {
                        $errors[] = "El campo 'Provincia' está vacío o no es válido.";
                        Utils::writeLog("El campo 'Provincia [$provincia]' no es válido.", BUSINESS_LOG_FILE);
                    }
                    if ($canton === null || empty($canton) || is_numeric($canton)) {
                        $errors[] = "El campo 'Cantón' está vacío o no es válido.";
                        Utils::writeLog("El campo 'Cantón [$canton]' no es válido.", BUSINESS_LOG_FILE);
                    }
                    if ($distrito === null || empty($distrito) || is_numeric($distrito)) {
                        $errors[] = "El campo 'Distrito' está vacío o no es válido.";
                        Utils::writeLog("El campo 'Distrito [$distrito]' no es válido.", BUSINESS_LOG_FILE);
                    }
                    if ($distancia === null || empty($distancia) || !is_numeric($distancia)) {
                        $errors[] = "El campo 'Distancia' está vacío o no es válido.";
                        Utils::writeLog("El campo 'Distancia [$distancia]' no es válido.", BUSINESS_LOG_FILE);
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
            $check = $this->validarDireccion($direccion);
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
            return $this->direccionData->deleteDireccion($direccionID);
        }

        public function getAllTBDireccion() {
            return $this->direccionData->getAllTBDireccion();
        }

        public function getPaginatedDirecciones($page, $size, $sort = null) {
            return $this->direccionData->getPaginatedDirecciones($page, $size, $sort);
        }

    }

?>