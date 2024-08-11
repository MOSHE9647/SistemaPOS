<?php
    include_once 'data.php';
    include __DIR__ . '/../domain/Producto.php';
    require_once __DIR__ . '/../utils/Utils.php';
    require_once __DIR__ . '/../utils/Variables.php';



	class ProductoData extends Data {
        // Constructor
		public function __construct() {
			parent::__construct();
		}


        function getAllProductos(){

        }
        function updateProducto($producto){

        }
        function insertProducto($producto){

        }
        function deleteProducto($producto){

        }
        function getProductoById(){

        }
    }

?>