<?php
require_once __DIR__ . "/../data/ProveedorProductoData.php";

class ProveedorProductoBusiness {

    private $proveedorProductoData;

    public function __construct() {
        $this->proveedorProductoData = new ProveedorProducto();
    }
    public function verificacionDeIDs($idproveedor, $idproducto = 0,$verificarProducto = false, $idproveedorProducto = null){
        try{
            $errors = [];
            if(empty($idproveedor) || !is_numeric($idproveedor) || $idproveedor <= 0){
                $errors[] = "El ID del proveedor está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                Utils::writeLog("El ID '[$idproveedor]' del proveedor no es válido.", BUSINESS_LOG_FILE);   
            }
            if($verificarProducto &&  (empty($idproducto) || !is_numeric($idproducto) || $idproducto <= 0)){
                $errors[] = "El ID del producto está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                Utils::writeLog("El ID '[$idproducto]' del producto no es válido.", BUSINESS_LOG_FILE);  
            }
            if($idproveedorProducto !== null){
                if($verificarProducto &&  (empty($idproveedorProducto) || !is_numeric($idproveedorProducto) || $idproveedorProducto <= 0)){
                    $errors[] = "El ID del proveedorproducto está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                    Utils::writeLog("El ID '[$idproducto]' del producto no es válido.", BUSINESS_LOG_FILE);  
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

    public function getAllProductosProveedor($idproveedor){
        $check = $this->verificacionDeIDs($idproveedor);
        if(!$check['is_valid']){return $check; }
        return $this->proveedorProductoData->getProductoByProveedor($idproveedor, true);
    }

    public function getPaginateProductoProveedor($idproveedor,$page,$size, $sort= null, $onlyActive = true, $deleted = false){
        $check = $this->verificacionDeIDs($idproveedor);
        if(!$check['is_valid']){return $check; }
        return $this->proveedorProductoData-> getPaginateProductoProveedor($idproveedor,$page,$size, $sort, $onlyActive, $deleted);
    }

    public function addProductoProveedor($idproveedor,$idproducto){
        $check = $this->verificacionDeIDs($idproveedor, $idproducto, true);
        if(!$check['is_valid']){return $check; }

        return $this->proveedorProductoData-> addProductoToProveedor($idproveedor, $idproducto);
    }

    public function deleteProductoToProveedor($idproveedor, $idproducto){
        $check = $this->verificacionDeIDs($idproveedor, $idproducto, true);
        if(!$check['is_valid']){return $check; }
        return $this->proveedorProductoData->removeProductoToProveedor($idproveedor, $idproducto);
    }
    public function updateProveedorProducto($idproveedorProducto, $idproveedor, $idproducto){
        $check = $this->verificacionDeIDs($idproveedor, $idproducto, true, $idproveedorProducto);
        if(!$check['is_valid']){return $check; }
        return $this->proveedorProductoData->updateProductoProveedor($idproveedorProducto, $idproducto, $idproveedor);
    }



    
    public function insertarProveedorProducto($proveedorId, $productoId) {
        try {
            return $this->proveedorProductoData->insertarProveedorProducto($proveedorId, $productoId);
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    public function obtenerTodosProveedorProducto() {
        try {
            return $this->proveedorProductoData->obtenerTodosProveedorProducto();
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    public function obtenerProveedorProductoPorId($proveedorProductoId) {
        try {
            return $this->proveedorProductoData->obtenerProveedorProductoPorId($proveedorProductoId);
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }
    
    public function actualizarProveedorProducto($proveedorProductoId, $nuevoProveedorId, $nuevoProductoId) {
        try {
            return $this->proveedorProductoData->actualizarProveedorProducto($proveedorProductoId, $nuevoProveedorId, $nuevoProductoId);
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    public function eliminarProveedorProducto($proveedorProductoId) {
        try {
            return $this->proveedorProductoData->eliminarProveedorProducto($proveedorProductoId);
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }
    
    
}
?>
