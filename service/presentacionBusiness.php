<?php

    require_once dirname(__DIR__, 1) . "/data/presentacionData.php";
    require_once dirname(__DIR__, 1) . "/utils/Utils.php";

    class PresentacionBusiness {

        private $presentacionData;
        private $className;

        public function __construct() {
            $this->presentacionData = new PresentacionData();
            $this->className = get_class($this);
        }
        
        public function validarPresentacionID($presentacionID) {
            if ($presentacionID === null || !is_numeric($presentacionID) || $presentacionID < 0) {
                Utils::writeLog("El 'ID [$presentacionID]' de la presentacion no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ["is_valid" => false, "message" => "El ID de la presentacion está vacío o no es válido. Revise que este sea un número y que sea mayor a 0"];
            }

            return ["is_valid" => true];
        }

        public function validarPresentacion($presentacion, $validarCamposAdicionales = true, $insert = false){
            try {
                // Obtener los valores de las propiedades del objeto
                $presentacionID = $presentacion->getPresentacionID();
                $nombre = $presentacion->getPresentacionNombre();
                $errors = [];

                if (!$insert) {
                    $checkID = $this->validarPresentacionID($presentacionID);
                    if (!$checkID['is_valid']) { $errors[] = $checkID['message']; }
                }

                // Si la validación de campos adicionales está activada, valida los otros campos
                if ($validarCamposAdicionales) {
                    if($nombre === null || empty($nombre)){
                        $errors[] = "El campo 'Nombre' está vacío o no es válido.";
                        Utils::writeLog("El campo Nombre '[$nombre]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
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

        public function insertTBPresentacion($presentacion) {
            // Verificar que los datos de la presentacion sean válidos
            $checkPresentacion = $this->validarPresentacion($presentacion, true, true);
            if (!$checkPresentacion['is_valid']) {
                return ["success" => $checkPresentacion["is_valid"], "message" => $checkPresentacion['message']];
            }
            
            // Insertar la presentacion en la base de datos
            return $this->presentacionData->insertPresentacion($presentacion);
        }

        public function updateTBPresentacion($presentacion) {
            // Verificar que los datos de la presentacion sean válidos
            $checkPresentacion = $this->validarPresentacion($presentacion);
            if (!$checkPresentacion['is_valid']) {
                return ["success" => $checkPresentacion["is_valid"], "message" => $checkPresentacion['message']];
            }

            // Actualizar la presentacion en la base de datos
            return $this->presentacionData->updatePresentacion($presentacion);
        }

        public function deleteTBPresentacion($presentacionID) {
            // Verificar que el ID de la presentacion sea válido
            $checkID = $this->validarPresentacionID($presentacionID);
            if (!$checkID['is_valid']) {
                return ["success" => $checkID["is_valid"], "message" => $checkID['message']];
            }

            // Eliminar la presentacion de la base de datos
            return $this->presentacionData->deletePresentacion($presentacionID);
        }

        public function getAllTBPresentacion($onlyActive = true, $deleted = false) {
            return $this->presentacionData-> getAllTBPresentaciones($onlyActive, $deleted);
        }

        public function getPaginatedPresentaciones($page, $size, $sort = null, $onlyActive = true, $deleted = false) {
            return $this->presentacionData->getPaginatedPresentaciones($page, $size, $sort, $onlyActive, $deleted);
        }

        public function getPresentacionByID($presentacionID, $onlyActive = true, $deleted = false) {
            // Verificar que el ID de la presentacion sea válido
            $checkID = $this->validarPresentacionID($presentacionID);
            if (!$checkID['is_valid']) {
                return ["success" => $checkID["is_valid"], "message" => $checkID['message']];
            }

            // Obtener la presentacion de la base de datos
            return $this->presentacionData->getPresentacionByID($presentacionID, $onlyActive, $deleted);
        }

    }

?>