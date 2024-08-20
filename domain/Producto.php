<?php

    class Producto {

        private $productoID;
        private $productoNombre;
        private $productoPrecio;
        private $productoCantidad;
        private $productoFecha;
        private $productoDescripcion;
        private $productoCodigoBarras;
        private $productoEstado;

        public function __construct($productoNombre, $productoPrecio, $productoCantidad, $productoFecha, $productoCodigoBarras,
                $productoID = 0, $productoDescripcion = "", $productoEstado = true) {
            $this->productoID = $productoID;
            $this->productoNombre = strtoupper($productoNombre);
            $this->productoPrecio = $productoPrecio;
            $this->productoCantidad = $productoCantidad;
            $this->productoFecha = $productoFecha;
            $this->productoDescripcion = $productoDescripcion;
            $this->productoCodigoBarras = $productoCodigoBarras;
            $this->productoEstado = $productoEstado;
        }

        public function getProductoID() { return $this->productoID; }
        public function getProductoNombre() { return $this->productoNombre; }
        public function getProductoPrecio() {  return $this->productoPrecio; }
        public function getProductoCantidad() { return $this->productoCantidad; }
        public function getProductoFechaAdquisicion() { return $this->productoFecha; }
        public function getProductoDescripcion() { return $this->productoDescripcion; }
        public function getProductoCodigoBarras() { return $this->productoCodigoBarras; }
        public function getProductoEstado() { return $this->productoEstado; }

        public function setProductoID($productoID) { $this->productoID = $productoID; }
        public function setProductoNombre($productoNombre) { $this->productoNombre =$productoNombre; }
        public function setProductoPrecio($productoPrecio) { $this->productoPrecio = $productoPrecio; }
        public function setProductoCantidad($productoCantidad) { $this->productoCantidad = $productoCantidad; }
        public function setProductoFechaAdquisicion($productoFecha) { $this->productoFecha = $productoFecha; }
        public function setProductoDescripcion($productoDescripcion) { $this->productoDescripcion = $productoDescripcion; }
        public function setProductoCodigoBarras($productoCodigoBarras) { $this->productoCodigoBarras = $productoCodigoBarras; }
        public function setProductoEstado($productoEstado) { $this->productoEstado = $productoEstado; }

        public function __toString() {
            return 
                "Producto ID:". $this->productoID."\n" .
                "Nombre:". $this->productoNombre."\n" .
                "Precio Unitario:". $this->productoPrecio."\n" .
                "Cantidad:". $this->productoCantidad."\n" .
                "Fecha de Adquisición:". $this->productoFecha."\n" .
                "Descripción:". $this->productoDescripcion."\n" .
                "Estado: " . ($this->productoEstado ? "Activo" : "Inactivo") . "\n";
        }

    }

?>