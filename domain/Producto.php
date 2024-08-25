<?php

    class Producto {

        private $productoID;
        private $productoFoto;
        private $productoNombre;
        private $productoPrecio;
        private $productoDescripcion;
        private $productoCodigoBarras;
        private $productoEstado;
        private $productoPorcentajeGanancia;

        private $subcategoria; //objeto Subcategoria;
        private $categoria; // objeto categoria


        public function __construct($productoNombre, $productoPrecio, $productoCodigoBarras,$productoFoto = null, $productoPorcentajeGanancia = null,
                $productoID = 0, $productoDescripcion = "", $productoEstado = true) {
            $this->productoID = $productoID;
            $this->productoNombre = strtoupper($productoNombre);
            $this->productoPrecio = $productoPrecio;
            $this->productoDescripcion = $productoDescripcion;
            $this->productoCodigoBarras = $productoCodigoBarras;
            $this->productoEstado = $productoEstado;
            $this->productoFoto = $productoFoto;
            $this->productoPorcentajeGanancia = $productoPorcentajeGanancia;
        }

        public function getProductoID() { return $this->productoID; }
        public function getProductoNombre() { return $this->productoNombre; }
        public function getProductoPrecio() {  return $this->productoPrecio; }
        public function getProductoDescripcion() { return $this->productoDescripcion; }
        public function getProductoCodigoBarras() { return $this->productoCodigoBarras; }
        public function getProductoEstado() { return $this->productoEstado; }
        public function getProductoFoto(){ return $this->productoFoto; } 
        public function getCategoria(){ return $this->categoria; }
        public function getSubcategoria(){ return $this->subcategoria; }
        public function getPorcentajeGanancia(){ return $this->productoPorcentajeGanancia; }

        public function setProductoID($productoID) { $this->productoID = $productoID; }
        public function setProductoNombre($productoNombre) { $this->productoNombre =$productoNombre; }
        public function setProductoPrecio($productoPrecio) { $this->productoPrecio = $productoPrecio; }
        public function setProductoDescripcion($productoDescripcion) { $this->productoDescripcion = $productoDescripcion; }
        public function setProductoCodigoBarras($productoCodigoBarras) { $this->productoCodigoBarras = $productoCodigoBarras; }
        public function setProductoEstado($productoEstado) { $this->productoEstado = $productoEstado; }
        public function setProductoFoto($productoFoto){  $this->productoFoto = $productoFoto; }
        public function setCategoria($categoria){ $this->categoria = $categoria; }
        public function setSubcategoria($subcategoria){ $this->subcategoria = $subcategoria; }
        public function setPorcentajeGanancia($productoPorcentajeGanancia){ return $this->productoPorcentajeGanancia = $productoPorcentajeGanancia; }

        public function __toString() {
            return 
                "Producto ID:". $this->productoID."\n" .
                "Nombre:". $this->productoNombre."\n" .
                "Precio Unitario:". $this->productoPrecio."\n" .
                "Descripción:". $this->productoDescripcion."\n" .
                "Estado: " . ($this->productoEstado ? "Activo" : "Inactivo") . "\n";
        }

    }

?>