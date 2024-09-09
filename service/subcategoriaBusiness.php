<?php
    require_once __DIR__ . "/../data/subcategoriaData.php";

    class SubcategoriaBusiness{
        private $subcategoriaData;


        public function verificacionDeDatos($subcategoria, $verificarcampos = true, $verificarid = false){
           try{
                $id = $subcategoria->getSubcategoriaId();
                $nombre = $subcategoria->getSubcategoriaNombre();
                $errors = [];
                if($verificarid && (empty($id) || $id <= 0 || !is_numeric($id))){
                    $errors[] = "El ID de la subcategoria está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                    Utils::writeLog("El ID '[$id]' de la subcategoria no es válido.", BUSINESS_LOG_FILE);   
                }
                if($verificarcampos){
                    if(empty($nombre)){
                        $errors[] = "El Nombre de la subcategoria esta vacia. Revisa que esta ingresando correctamente el nombre.";
                        Utils::writeLog("El Nombre '>>[$nombre]' de la subcategoria no es válido.", BUSINESS_LOG_FILE);
                    }
                }

                if (!empty($errors)) {
                    throw new Exception(implode('<br>', $errors));
                }
                return ["is_valid" => true];
            } catch (Exception $e) {
                return ["is_valid" => false, "message" => $e->getMessage()];
            }
        }

        public function __construct(){
            $this->subcategoriaData = new SubcategoriaData();
        }

        function insertSubcategoria($subcategoria){
            $check = $this->verificacionDeDatos($subcategoria,true);
            if(!$check['is_valid']){ return $check; }
            return $this->subcategoriaData->insertSubcategoria($subcategoria);
        }

        function updateSubcategoria($subcategoria){
            $check = $this->verificacionDeDatos($subcategoria,true,true);
            if(!$check['is_valid']){ return $check; }
            return $this->subcategoriaData->updateSubcategorias($subcategoria);
        }

        function deleteSubcategoria($subcategoria){
            $check = $this->verificacionDeDatos($subcategoria,false,true);
            if(!$check['is_valid']){ return $check; }
            return $this->subcategoriaData->deleteSubcategoria($subcategoria->getSubcategoriaId());
        }

        function getAllSubcategorias(){
            return $this->subcategoriaData->getAllSubcategorias();
        }
        function getPaginatedSubcategorias($page, $size, $sort = null){
            return $this->subcategoriaData->getPaginatedSubcategorias($page, $size, $sort);
        }
    }
?>