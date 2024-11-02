<?php

    require_once dirname(__DIR__, 1) . "/data/categoriaData.php";
    require_once dirname(__DIR__, 1) . "/utils/Utils.php";

    class CategoriaBusiness {

        private $categoriaData;
        private $className;

        public function __construct() {
            $this->categoriaData = new CategoriaData();
            $this->className = get_class($this);
        }
        
        public function validarCategoriaID($categoriaID) {
            if ($categoriaID === null || !is_numeric($categoriaID) || $categoriaID < 0) {
                Utils::writeLog("El 'ID [$categoriaID]' de la categoria no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                return ["is_valid" => false, "message" => "El ID de la categoria está vacío o no es válido. Revise que este sea un número y que sea mayor a 0"];
            }

            return ["is_valid" => true];
        }

        public function validarCategoria($categoria, $validarCamposAdicionales = true, $insert = false){
            try {
                // Obtener los valores de las propiedades del objeto
                $categoriaID = $categoria->getCategoriaID();
                $nombre = $categoria->getCategoriaNombre();
                $errors = [];

                if (!$insert) {
                    $checkID = $this->validarCategoriaID($categoriaID);
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

        public function insertTBCategoria($categoria) {
            // Verificar que los datos de la categoria sean válidos
            $checkCategoria = $this->validarCategoria($categoria, true, true);
            if (!$checkCategoria['is_valid']) {
                return ["success" => $checkCategoria["is_valid"], "message" => $checkCategoria['message']];
            }
            
            // Insertar la categoria en la base de datos
            return $this->categoriaData->insertCategoria($categoria);
        }

        public function updateTBCategoria($categoria) {
            // Verificar que los datos de la categoria sean válidos
            $checkCategoria = $this->validarCategoria($categoria);
            if (!$checkCategoria['is_valid']) {
                return ["success" => $checkCategoria["is_valid"], "message" => $checkCategoria['message']];
            }

            // Actualizar la categoria en la base de datos
            return $this->categoriaData->updateCategoria($categoria);
        }

        public function deleteTBCategoria($categoriaID) {
            // Verificar que el ID de la categoria sea válido
            $checkID = $this->validarCategoriaID($categoriaID);
            if (!$checkID['is_valid']) {
                return ["success" => $checkID["is_valid"], "message" => $checkID['message']];
            }

            // Eliminar la categoria de la base de datos
            return $this->categoriaData->deleteCategoria($categoriaID);
        }

        public function getAllTBCategoria($onlyActive = false, $deleted = false) {
            return $this->categoriaData-> getAllTBCategorias($onlyActive, $deleted);
        }

        public function getPaginatedCategorias($page, $size, $sort = null, $onlyActive = true, $deleted = false) {
            return $this->categoriaData->getPaginatedCategorias($page, $size, $sort, $onlyActive, $deleted);
        }

        public function getCategoriaByID($categoriaID, $onlyActive = true, $deleted = false) {
            // Verificar que el ID de la categoria sea válido
            $checkID = $this->validarCategoriaID($categoriaID);
            if (!$checkID['is_valid']) {
                return ["success" => $checkID["is_valid"], "message" => $checkID['message']];
            }

            // Obtener la categoria de la base de datos
            return $this->categoriaData->getCategoriaByID($categoriaID, $onlyActive, $deleted);
        }

    }

?>