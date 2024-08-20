<?php

class ProductoCategoria {
    private $productocategoriaid;
    private $productoid;
    private $categoriaid;

    public function __construct($productocategoriaid, $productoid, $categoriaid) {
        $this->productocategoriaid = $productocategoriaid;
        $this->productoid = $productoid;
        $this->categoriaid = $categoriaid;
    }

    // Getters
    public function getProductoCategoriaId() {
        return $this->productocategoriaid;
    }

    public function getProductoId() {
        return $this->productoid;
    }

    public function getCategoriaId() {
        return $this->categoriaid;
    }

    // Setters
    public function setProductoCategoriaId($productocategoriaid) {
        $this->productocategoriaid = $productocategoriaid;
    }

    public function setProductoId($productoid) {
        $this->productoid = $productoid;
    }

    public function setCategoriaId($categoriaid) {
        $this->categoriaid = $categoriaid;
    }
}

?>
