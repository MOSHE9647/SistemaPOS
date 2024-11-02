<?php

    require_once dirname(__DIR__, 1) . "/data/marcaData.php";
    require_once dirname(__DIR__, 1) . "/utils/Utils.php";

    class MarcaBusiness {

        private $marcaData;
        private $className;

        public function __construct() {
            $this->marcaData = new MarcaData();
            $this->className = get_class($this);
        }
        
        public function validarMarcaID($marcaID) {
            if ($marcaID === null || !is_numeric($marcaID) || $marcaID < 0) {
                Utils::writeLog("El 'ID [$marcaID]' de la marca no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                return ["is_valid" => false, "message" => "El ID de la marca está vacío o no es válido. Revise que este sea un número y que sea mayor a 0"];
            }

            return ["is_valid" => true];
        }

        public function validarMarca($marca, $validarCamposAdicionales = true, $insert = false){
            try {
                // Obtener los valores de las propiedades del objeto
                $marcaID = $marca->getMarcaID();
                $nombre = $marca->getMarcaNombre();
                $errors = [];

                if (!$insert) {
                    $checkID = $this->validarMarcaID($marcaID);
                    if (!$checkID['is_valid']) { $errors[] = $checkID['message']; }
                }

                // Si la validación de campos adicionales está activada, valida los otros campos
                if ($validarCamposAdicionales) {
                    if($nombre === null || empty($nombre) || is_numeric($nombre)){
                        $errors[] = "El campo 'Nombre' está vacío o no es válido.";
                        Utils::writeLog("El campo Nombre '[$nombre]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    }
                }

                // Si hay errores, los retorna
                if (!empty($errors)) {
                    throw new Exception(implode('<br>', $errors));
                }

                return ["is_valid" => true];
            } catch (Exception $e) {
                return ["is_valid" => false, "message" => $e->getMessage()];
            }
        }

        public function insertTBMarca($marca) {
            // Verificar que los datos de la marca sean válidos
            $checkMarca = $this->validarMarca($marca, true, true);
            if (!$checkMarca['is_valid']) {
                return ["success" => $checkMarca["is_valid"], "message" => $checkMarca['message']];
            }
            
            // Insertar la marca en la base de datos
            return $this->marcaData->insertMarca($marca);
        }

        public function updateTBMarca($marca) {
            // Verificar que los datos de la marca sean válidos
            $checkMarca = $this->validarMarca($marca);
            if (!$checkMarca['is_valid']) {
                return ["success" => $checkMarca["is_valid"], "message" => $checkMarca['message']];
            }

            // Actualizar la marca en la base de datos
            return $this->marcaData->updateMarca($marca);
        }

        public function deleteTBMarca($marcaID) {
            // Verificar que el ID de la marca sea válido
            $checkID = $this->validarMarcaID($marcaID);
            if (!$checkID['is_valid']) {
                return ["success" => $checkID["is_valid"], "message" => $checkID['message']];
            }

            // Eliminar la marca de la base de datos
            return $this->marcaData->deleteMarca($marcaID);
        }

        public function getAllTBMarca($onlyActive = false, $deleted = false) {
            return $this->marcaData->getAllTBMarcas($onlyActive, $deleted);
        }

        public function getPaginatedMarcas($page, $size, $sort = null, $onlyActive = true, $deleted = false) {
            return $this->marcaData->getPaginatedMarcas($page, $size, $sort, $onlyActive, $deleted);
        }

        public function getMarcaByID($marcaID, $onlyActive = true, $deleted = false) {
            // Verificar que el ID de la marca sea válido
            $checkID = $this->validarMarcaID($marcaID);
            if (!$checkID['is_valid']) {
                return ["success" => $checkID["is_valid"], "message" => $checkID['message']];
            }

            // Obtener la marca de la base de datos
            return $this->marcaData->getMarcaByID($marcaID, $onlyActive, $deleted);
        }

    }

?>