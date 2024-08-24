<?php

  class ProveedorProducto {
    private $provedorproductoid;
    private $proveedorid;
    private $productoid;

    // Constructor
    public function __construct($provedorproductoid, $proveedorid, $productoid) {
        $this->provedorproductoid = $provedorproductoid;
        $this->proveedorid = $proveedorid;
        $this->productoid = $productoid;
    }

    // Getters y Setters
    public function getProvedorProductoId() {
        return $this->provedorproductoid;
    }

    public function setProvedorProductoId($provedorproductoid) {
        $this->provedorproductoid = $provedorproductoid;
    }

    public function getProveedorId() {
        return $this->proveedorid;
    }

    public function setProveedorId($proveedorid) {
        $this->proveedorid = $proveedorid;
    }

    public function getProductoId() {
        return $this->productoid;
    }

    public function setProductoId($productoid) {
        $this->productoid = $productoid;
    }
  }
?>

