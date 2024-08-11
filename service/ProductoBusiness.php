<?php
   include __DIR__ . "/../data/ProductoData.php";


   class ProductoBusiness{
        private $ProductoData;

    function __construct(){
        $this->$ProductoData = new ProductoData();
    }
    function insertTBProducto($producto){
        return $this->$ProductoData->insertProducto($producto);
    }
    function deleteTBProducto($producto){
        return $this->$ProductoData->deleteProducto($producto);
    }
    function getAllTBProducto(){
        return $this->$ProductoData->getAllProductos();
    }
    function updateTBProducto($producto){
        return $this->$ProductoData->updateProducto($producto);
    }
    function getProductoByID(){
        return $this->$ProductoData->getProductoByID();
    }

   }
?>