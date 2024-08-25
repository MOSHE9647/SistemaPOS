<?php

include __DIR__ . "/../data/categoriaData.php";

class CategoriaBusiness {

    private $categoriaData;

    public function __construct() {
        $this->categoriaData = new CategoriaData();
    }

    public function insertTBCategoria($categoria) {
        return $this->categoriaData->insertCategoria($categoria);
    }

    public function getAllTBCategoria() {
        return $this->categoriaData->getAllTBCategoria();
    }

    public function getPaginatedCategorias($page, $size, $sort = null) {
        return $this->categoriaData->getPaginatedCategorias($page, $size, $sort);
    }

    public function updateTBCategoria($categoria) {
        return $this->categoriaData->updateCategoria($categoria);
    }

    public function deleteTBCategoria($categoriaID) {
        return $this->categoriaData->deleteCategoria($categoriaID);
    }

}
?>
