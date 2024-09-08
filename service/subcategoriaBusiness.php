<?php
    require_once __DIR__ . "/../data/subcategoriaData.php";

    class SubcategoriaBusiness{
        private $subcategoriaData;

        public function __construct(){
            $this->subcategoriaData = new SubcategoriaData();
        }
        function getAllSubcategorias(){
            return $this->subcategoriaData->getAllSubcategorias();
        }
        function insertSubcategoria($subcategoria){
            return $this->subcategoriaData->insertSubcategoria($subcategoria);
        }
        function updateSubcategoria($subcategoria){
           return $this->subcategoriaData->updateSubcategorias($subcategoria);
        }
        function deleteSubcategoria($id){
            return $this->subcategoriaData->deleteSubcategoria($id);
        }
        function getPaginatedSubcategorias($page, $size, $sort = null){
            return $this->subcategoriaData->getPaginatedSubcategorias($page, $size, $sort);
        }
    }
?>