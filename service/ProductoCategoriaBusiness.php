<?php
include __DIR__ . "/../data/ProductoCategoriaData.php";

class ProductoCategoriaBusiness {

    private $productoCategoriaData;

    public function __construct() {
        $this->productoCategoriaData = new ProductoCategoriaData();
    }

    public function insertarProductoCategoria($productoCategoria) {
        return $this->productoCategoriaData->insertarProductoCategoria($productoCategoria);
    }

    public function actualizarProductoCategoria($productoCategoria) {
        return $this->productoCategoriaData->actualizarProductoCategoria($productoCategoria);
    }

    public function eliminarProductoCategoria($productocategoriaid) {
        return $this->productoCategoriaData->eliminarProductoCategoria($productocategoriaid);
    }

    public function obtenerProductoCategoriaPorId($productocategoriaid) {
        return $this->productoCategoriaData->obtenerProductoCategoriaPorId($productocategoriaid);
    }

    public function obtenerProductosPorCategoria($categoriaid) {
        return $this->productoCategoriaData->obtenerProductosPorCategoria($categoriaid);
    }

    public function obtenerCategoriasPorProducto($productoid) {
        return $this->productoCategoriaData->obtenerCategoriasPorProducto($productoid);
    }
}
?>
