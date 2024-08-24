<?php

class ProductoSubcategoria {
    private $id_ProductoSubcategoria;
    
    private $id_producto;
    private $nombre_producto;

    private $id_subcategoria;
    private $nombre_subcategoria;

    private $productoSubcategoriaEstado;

    // Getters y Setters
    public function __construct( $id_producto, $id_subcategoria, $id_ProductoSubcategoria = null, $productoSubcategoriaEstado = true, $nombre_producto=null, $nombre_subcategoria = null){
        $this->id_ProductoSubcategoria = $id_ProductoSubcategoria;
    
        $this->id_producto = $id_producto;
        $this->nombre_producto = $nombre_producto;
    
        $this->id_subcategoria = $id_subcategoria;
        $this->nombre_subcategoria = $nombre_subcategoria;
    
        $this->productoSubcategoriaEstado = $productoSubcategoriaEstado;
    }
    
    public function getIdProductoSubcategoria() {
        return $this->id_ProductoSubcategoria;
    }

    public function setIdProductoSubcategoria($id_ProductoSubcategoria) {
        $this->id_ProductoSubcategoria = $id_ProductoSubcategoria;
    }

    public function getIdProducto() {
        return $this->id_producto;
    }

    public function setIdProducto($id_producto) {
        $this->id_producto = $id_producto;
    }

    public function getNombreProducto() {
        return $this->nombre_producto;
    }

    public function setNombreProducto($nombre_producto) {
        $this->nombre_producto = $nombre_producto;
    }

    public function getIdSubcategoria() {
        return $this->id_subcategoria;
    }

    public function setIdSubcategoria($id_subcategoria) {
        $this->id_subcategoria = $id_subcategoria;
    }

    public function getNombreSubcategoria() {
        return $this->nombre_subcategoria;
    }

    public function setNombreSubcategoria($nombre_subcategoria) {
        $this->nombre_subcategoria = $nombre_subcategoria;
    }

    public function getProductoSubcategoriaEstado() {
        return $this->productoSubcategoriaEstado;
    }

    public function setProductoSubcategoriaEstado($productoSubcategoriaEstado) {
        $this->productoSubcategoriaEstado = $productoSubcategoriaEstado;
    }
}

?>