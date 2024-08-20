<?php
   include __DIR__ . "/../data/productoSubcategoriaData.php";
class ProductoSubcategoriaBusines{
    private $productoSubcategoria;

    function __construct(){
        $this->productoSubcategoria = new ProductoSubcategoriaData();
    }
    function insertProductoSubcategoria($productoSubcategoria){
        return $this->productoSubcategoria->insertProductoSubcategoria($productoSubcategoria);
    }
    function getAllProductoSubcategoria($page, $size, $sort = null){
        return $this->productoSubcategoria->getALLProductoSubcategoria($page, $size, $sort);
    }
    function updateProductoSubcategoria($ProductoSubcategoria){
        return $this->productoSubcategoria->updateProductoSubcategoria($ProductoSubcategoria);
    }
    function deleteProductoSubcategoria($id){
        return $this->productoSubcategoria->deleteProductoSubcategoria($id);
    }
}

?>