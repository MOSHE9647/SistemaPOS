<?php

    class Producto {

        private $productoID;
        private $productoNombre;
        private $productoPrecioCompra;
        private $productoPorcentajeGanancia;
        private $productoDescripcion;
        private $productoCodigoBarrasID;
        private $productoImagen;
        private $productoEstado;

        public function __construct($productoNombre, $productoPrecioCompra, $productoCodigoBarrasID, $productoImagen = null, 
                $productoPorcentajeGanancia = null, $productoID = 0, $productoDescripcion = "", $productoEstado = true){
           
            $this->productoID = $productoID;
            $this->productoNombre = strtoupper($productoNombre);
            $this->productoPrecioCompra = $productoPrecioCompra;
            $this->productoDescripcion = $productoDescripcion;
            $this->productoCodigoBarrasID = $productoCodigoBarrasID;
            $this->productoEstado = $productoEstado;
            $this->productoImagen = $productoImagen;
            $this->productoPorcentajeGanancia = $productoPorcentajeGanancia;
        }

        // Getters
        public function getProductoID() { return $this->productoID; }
        public function getProductoNombre() { return $this->productoNombre; }
        public function getProductoPrecioCompra() { return $this->productoPrecioCompra; }
        public function getProductoDescripcion() { return $this->productoDescripcion; }
        public function getProductoCodigoBarrasID() { return $this->productoCodigoBarrasID; }
        public function getProductoEstado() { return $this->productoEstado; }
        public function getProductoImagen(){ return $this->productoImagen; } 
        public function getPorcentajeGanancia(){ return $this->productoPorcentajeGanancia; }

        // Setters
        public function setProductoID($productoID) { $this->productoID = $productoID; }
        public function setProductoNombre($productoNombre) { $this->productoNombre = $productoNombre; }
        public function setProductoPrecioCompra($productoPrecioCompra) { $this->productoPrecioCompra = $productoPrecioCompra; }
        public function setProductoDescripcion($productoDescripcion) { $this->productoDescripcion = $productoDescripcion; }
        public function setProductoCodigoBarrasID($productoCodigoBarrasID) { $this->productoCodigoBarrasID = $productoCodigoBarrasID; }
        public function setProductoEstado($productoEstado) { $this->productoEstado = $productoEstado; }
        public function setProductoImagen($productoImagen){  $this->productoImagen = $productoImagen; }
        public function setPorcentajeGanancia($productoPorcentajeGanancia){ $this->productoPorcentajeGanancia = $productoPorcentajeGanancia; }

        public function __toString() {
            return 
                "Producto ID:". $this->productoID."\n" .
                "Nombre:". $this->productoNombre."\n" .
                "Precio de Compra:". $this->productoPrecioCompra."\n" .
                "Descripción:". $this->productoDescripcion."\n" .
                "Estado: " . ($this->productoEstado ? "Activo" : "Inactivo") . "\n";
        }

    }

?>
