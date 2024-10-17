<?php

    require_once dirname(__DIR__, 1) . "/data/subcategoriaData.php";
    require_once dirname(__DIR__, 1) . "/utils/Utils.php";

    class SubcategoriaBusiness {

        private $subcategoriaData;
        private $className;

        public function __construct() {
            $this->subcategoriaData = new SubcategoriaData();
            $this->className = get_class($this);
        }

        public function validarSubcategoriaID($subcategoriaID) {
            if ($subcategoriaID === null || !is_numeric($subcategoriaID) || $subcategoriaID < 0) {
                Utils::writeLog("El 'ID [$subcategoriaID]' de la subcategoria no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ["is_valid" => false, "message" => "El ID de la subcategoria está vacío o no es válido. Revise que este sea un número y que sea mayor a 0"];
            }

            return ["is_valid" => true];
        }

        public function validarCategoriaID($categoriaID) {
            if ($categoriaID === null || !is_numeric($categoriaID) || $categoriaID < 0) {
                Utils::writeLog("El 'ID [$categoriaID]' de la categoria no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ["is_valid" => false, "message" => "El ID de la categoria está vacío o no es válido. Revise que este sea un número y que sea mayor a 0"];
            }

            return ["is_valid" => true];
        }

        public function validarSubcategoria($subcategoria, $validarCamposAdicionales = true, $insert = false){
            try {
                // Obtener los valores de las propiedades del objeto
                $categoriaID = $subcategoria->getSubcategoriaCategoria()->getCategoriaID();
                $subcategoriaID = $subcategoria->getSubcategoriaID();
                $nombre = $subcategoria->getSubcategoriaNombre();
                $errors = [];

                if (!$insert) {
                    $checkID = $this->validarSubcategoriaID($subcategoriaID);
                    if (!$checkID['is_valid']) { $errors[] = $checkID['message']; }
                }

                // Si la validación de campos adicionales está activada, valida los otros campos
                if ($validarCamposAdicionales) {
                    // Verifica que el nombre de la subcategoria sea válido
                    if($nombre === null || empty($nombre) || is_numeric($nombre)){
                        $errors[] = "El campo 'Nombre' está vacío o no es válido.";
                        Utils::writeLog("El campo Nombre '[$nombre]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                    }

                    // Verifica que el ID de la categoria sea válido
                    $checkCategoriaID = $this->validarCategoriaID($categoriaID);
                    if (!$checkCategoriaID['is_valid']) { $errors[] = $checkCategoriaID['message']; }
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

        public function insertSubcategoria($subcategoria) {
            // Verificar que los datos de la subcategoria sean válidos
            $check = $this->validarSubcategoria($subcategoria, true, true);
            if (!$check['is_valid']) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            // Insertar la subcategoria en la base de datos
            return $this->subcategoriaData->insertSubcategoria($subcategoria);
        }

        public function updateSubcategoria($subcategoria) {
            // Verificar que los datos de la subcategoria sean válidos
            $check = $this->validarSubcategoria($subcategoria, true, false);
            if (!$check['is_valid']) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            // Actualizar la subcategoria en la base de datos
            return $this->subcategoriaData->updateSubcategoria($subcategoria);
        }

        public function deleteSubcategoria($subcategoriaID) {
            // Verificar que el ID de la subcategoria sea válido
            $check = $this->validarSubcategoriaID($subcategoriaID);
            if (!$check['is_valid']) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            // Eliminar la subcategoria de la base de datos
            return $this->subcategoriaData->deleteSubcategoria($subcategoriaID);
        }

        public function getSubcategoriasByCategoria($categoriaID) {
            // Verificar que el ID de la categoria sea válido
            $check = $this->validarCategoriaID($categoriaID);
            if (!$check['is_valid']) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            // Obtener las subcategorias de la categoria
            return $this->subcategoriaData->getSubcategoriasByCategoria($categoriaID);
        }

        public function getSubcategoriaByID($subcategoriaID, $onlyActive = true, $deleted = false) {
            // Verificar que el ID de la subcategoria sea válido
            $check = $this->validarSubcategoriaID($subcategoriaID);
            if (!$check['is_valid']) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            // Obtener la subcategoria de la base de datos
            return $this->subcategoriaData->getSubcategoriaByID($subcategoriaID, $onlyActive, $deleted);
        }

        public function getAllTBSubcategorias($onlyActive = false, $deleted = false){
            return $this->subcategoriaData->getAllTBSubcategorias($onlyActive, $deleted);
        }

        public function getPaginatedSubcategorias($page, $size, $sort = null, $onlyActive = false, $deleted = false){
            return $this->subcategoriaData->getPaginatedSubcategorias($page, $size, $sort, $onlyActive, $deleted);
        }

    }
?>