<?php

require_once __DIR__ . "/../data/categoriaData.php";

class CategoriaBusiness {

    private $categoriaData;

    public function __construct() {
        $this->categoriaData = new CategoriaData();
    }
    
    public function verificacionDeDatos($categoria, $verificarcampos = false,$verificarid = false){
        try{
            $id = $categoria->getCategoriaID();
            $nombre = $categoria->getCategoriaNombre();
            $errors = [];
            if($verificarid && (empty($id) || $id <= 0 || !is_numeric($id))){
                $errors[] = "El ID de la categoria está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                Utils::writeLog("El ID '[$id]' de la categoria no es válido.", BUSINESS_LOG_FILE);   
            }
            if($verificarcampos){
                if(empty($nombre)){
                    $errors[] = "El Nombre de la categoria esta vacia. Revisa que esta ingresando correctamente el nombre.";
                    Utils::writeLog("El Nombre '>>[$nombre]' de la categoria no es válido.", BUSINESS_LOG_FILE);
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

    public function insertTBCategoria($categoria) {
        $check = $this->verificacionDeDatos($categoria,true);
        if(!$check['is_valid']){ return $check; }
        return $this->categoriaData->insertCategoria($categoria);
    }

    public function updateTBCategoria($categoria) {
        $check = $this->verificacionDeDatos($categoria,true,true);
        if(!$check['is_valid']){ return $check; }
        return $this->categoriaData->updateCategoria($categoria);
    }

    public function deleteTBCategoria($categoria) {
        $check = $this->verificacionDeDatos($categoria,false,true);
        if(!$check['is_valid']){ return $check; }
        return $this->categoriaData->deleteCategoria($categoria->getCategoriaID());
    }

    public function getAllTBCategoria() {
        return $this->categoriaData-> getAllCategorias();
    }

    public function getPaginatedCategorias($page, $size, $sort = null) {
        return $this->categoriaData->getPaginatedCategorias($page, $size, $sort);
    }
}
?>
