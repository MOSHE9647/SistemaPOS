<?php
   include __DIR__ . "/../data/ProductoData.php";


   class ProductoBusiness{
        private $ProductoData;

        public function __construct(){
            $this->ProductoData = new ProductoData();
        }
        function insertTBProducto($producto){
            return $this->ProductoData->insertProducto($producto);
        }
        function deleteTBProducto($id){
            return $this->ProductoData->deleteProducto($id);
        }
        function getAllTBProducto(){
            return $this->ProductoData->getAllProductos();
        }
        function updateTBProducto($producto){
            return $this->ProductoData->updateProducto($producto);
        }
        function getProductoByID(){
            return $this->ProductoData->getProductoByID();
        }

   }
?>