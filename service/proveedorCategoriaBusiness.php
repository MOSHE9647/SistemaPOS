<?php
require_once __DIR__ . "/../data/proveedorCategoriasData.php";

class ProveedorCategoriaBusiness {

    private $proveedorCategoriaData;

    public function __construct() {
        $this->proveedorCategoriaData = new ProveedorCategoriaData();
    }

    public function verificacionDeIDs($idproveedor, $idcategoria = 0,$verificarCategoria = false){
        try{
            $errors = [];
            if(empty($idproveedor) || !is_numeric($idproveedor) || $idproveedor <= 0){
                $errors[] = "El ID del proveedor está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                Utils::writeLog("El ID '[$idproveedor]' del proveedor no es válido.", BUSINESS_LOG_FILE);   
            }
            if($verificarCategoria &&  (empty($idcategoria) || !is_numeric($idcategoria) || $idcategoria <= 0)){
                $errors[] = "El ID del producto está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                Utils::writeLog("El ID '[$idcategoria]' del categoria no es válido.", BUSINESS_LOG_FILE);  
            }
            if (!empty($errors)) {
                throw new Exception(implode('<br>', $errors));
            }
            return ["is_valid" => true];
        } catch (Exception $e) {
            return ["is_valid" => false, "message" => $e->getMessage()];
        }
    }

    public function getAllCategoriasProveedor($idproveedor){
        $check = $this->verificacionDeIDs($idproveedor);
        if(!$check['is_valid']){return $check; }
        return $this->proveedorCategoriaData-> getCategoriaByProveedor($idproveedor, true);
    }

    public function getPaginateCategoriaProveedor($idproveedor,$page,$size, $sort= null, $onlyActive = true, $deleted = false){
        $check = $this->verificacionDeIDs($idproveedor);
        if(!$check['is_valid']){return $check; }
        return $this->proveedorCategoriaData->getPaginateCategoriaProveedor($idproveedor,$page,$size, $sort, $onlyActive, $deleted);
    }

    public function addCategoriaProveedor($idproveedor,$idcategoria){
        $check = $this->verificacionDeIDs($idproveedor, $idcategoria, true);
        if(!$check['is_valid']){return $check; }

        return $this->proveedorCategoriaData-> addCategoriaToProveedor($idproveedor, $idcategoria);
    }

    public function deleteCategoriaToProveedor($idproveedor, $idcategoria){
        $check = $this->verificacionDeIDs($idproveedor, $idcategoria, true);
        if(!$check['is_valid']){return $check; }
        return $this->proveedorCategoriaData->removeCategoriaToProveedor($idproveedor, $idcategoria);
    }

}
?>
