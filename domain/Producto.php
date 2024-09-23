<?php

    class Producto {

        private $productoID; 
        private $codigoBarrasID;
        private $productoNombre;
        private $productoPrecioCompra;
        private $productoPorcentajeGanancia;
        private $productoDescripcion;
        private $categoriaID;
        private $subCategoriaID;
        private $marcaID;
        private $presentacionID; 
        private $productoImagen;
        private $productoEstado;

        public function __construct(
            $productoID,
            $codigoBarrasID,
            $productoNombre,
            $productoPrecioCompra, 
            $productoPorcentajeGanancia,
            $productoDescripcion,
            $categoriaID,
            $subCategoriaID, 
            $marcaID, 
            $presentacionID,
            $productoImagen,
            $productoEstado){
           
            $this->productoID = $productoID;
            $this->codigoBarrasID = $codigoBarrasID;
            $this->productoNombre = $productoNombre;
            $this->productoPrecioCompra = $productoPrecioCompra;
            $this->productoPorcentajeGanancia = $productoPorcentajeGanancia;
            $this->productoDescripcion = $productoDescripcion;
            $this->categoriaID = $categoriaID;
            $this->subCategoriaID = $subCategoriaID;
            $this->marcaID = $marcaID;
            $this->presentacionID = $presentacionID;
            $this->productoEstado = $productoEstado;
            $this->productoImagen = $productoImagen;
            
        }

        // Getters
        public function getProductoID() { return $this->productoID; }
        public function getCodigoBarrasID() { return $this->codigoBarrasID; }
        public function getProductoNombre() { return $this->productoNombre; }
        public function getProductoPrecioCompra() { return $this->productoPrecioCompra; }
        public function getProductoPorcentajeGanancia() { return $this->productoPorcentajeGanancia; }
        public function getProductoDescripcion() { return $this->productoDescripcion; }
        public function getCategoriaID() { return $this->categoriaID; }
        public function getSubCategoriaID() { return $this->subCategoriaID; }
        public function getMarcaID() { return $this->marcaID; }
        public function getPresentacionID() { return $this->presentacionID; }
        public function getProductoImagen() { return $this->productoImagen; }
        public function getProductoEstado() { return $this->productoEstado; }
    

        // Setters
        public function setProductoID($productoID) { $this->productoID = $productoID; }
        public function setCodigoBarrasID($codigoBarrasID) { $this->codigoBarrasID = $codigoBarrasID; }
        public function setProductoNombre($productoNombre) { $this->productoNombre = $productoNombre; }
        public function setProductoPrecioCompra($productoPrecioCompra) { $this->productoPrecioCompra = $productoPrecioCompra; }
        public function setProductoPorcentajeGanancia($productoPorcentajeGanancia) { $this->productoPorcentajeGanancia = $productoPorcentajeGanancia; }
        public function setProductoDescripcion($productoDescripcion) { $this->productoDescripcion = $productoDescripcion; }
        public function setCategoriaID($categoriaID) { $this->categoriaID = $categoriaID; }
        public function setSubCategoriaID($subCategoriaID) { $this->subCategoriaID = $subCategoriaID; }
        public function setMarcaID($marcaID) { $this->marcaID = $marcaID; }
        public function setPresentacionID($presentacionID) { $this->presentacionID = $presentacionID; }
        public function setProductoImagen($productoImagen) { $this->productoImagen = $productoImagen; }
        public function setProductoEstado($productoEstado) { $this->productoEstado = $productoEstado; }

    }

?>
