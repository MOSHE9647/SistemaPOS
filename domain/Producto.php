<?php

    require_once dirname(__DIR__, 1) . "/domain/CodigoBarras.php";
    require_once dirname(__DIR__, 1) . "/domain/Subcategoria.php";
    require_once dirname(__DIR__, 1) . "/domain/Presentacion.php";
    require_once dirname(__DIR__, 1) . "/domain/Categoria.php";
    require_once dirname(__DIR__, 1) . "/domain/Marca.php";
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';

    class Producto implements JsonSerializable {

        private $productoID;
        private $productoCodigoBarras;
        private $productoCategoria;
        private $productoSubCategoria;
        private $productoMarca;
        private $productoPresentacion;
        private $productoNombre;
        private $productoCantidad;
        private $productoPrecioCompra;
        private $productoPorcentajeGanancia;
        private $productoDescripcion;
        private $productoImagen;
        private $productoFechaVencimiento;
        private $productoEstado;

        public function __construct(int $productoID = -1, CodigoBarras $productoCodigoBarras = null, string $productoNombre = "", 
            int $productoCantidad = 0, float $productoPrecioCompra = 0.0, float $productoPorcentajeGanancia = 0.0, 
            string $productoDescripcion = "", Categoria $productoCategoria = null, Subcategoria $productoSubCategoria = null, 
            Marca $productoMarca = null, Presentacion $productoPresentacion = null, $productoImagen = null, $productoFechaVencimiento = "",
            bool $productoEstado = true) 
        {
            $this->productoID = $productoID;
            $this->productoCodigoBarras = $productoCodigoBarras;
            $this->productoNombre = strtoupper($productoNombre);
            $this->productoCantidad = $productoCantidad;
            $this->productoPrecioCompra = Utils::formatearDecimal($productoPrecioCompra);
            $this->productoPorcentajeGanancia = Utils::formatearDecimal($productoPorcentajeGanancia);
            $this->productoDescripcion = ucfirst($productoDescripcion);
            $this->productoCategoria = $productoCategoria;
            $this->productoSubCategoria = $productoSubCategoria;
            $this->productoMarca = $productoMarca;
            $this->productoPresentacion = $productoPresentacion;
            $this->productoImagen = $productoImagen;
            $this->productoFechaVencimiento = $productoFechaVencimiento;
            $this->productoEstado = $productoEstado;
        }

        public function getProductoID(): int { return $this->productoID; }
        public function getProductoCodigoBarras(): ?CodigoBarras { return $this->productoCodigoBarras; }
        public function getProductoNombre(): string { return $this->productoNombre; }
        public function getProductoCantidad(): int { return $this->productoCantidad; }
        public function getProductoPrecioCompra(): float { return $this->productoPrecioCompra; }
        public function getProductoPorcentajeGanancia(): float { return $this->productoPorcentajeGanancia; }
        public function getProductoDescripcion(): string { return $this->productoDescripcion; }
        public function getProductoCategoria(): ?Categoria { return $this->productoCategoria; }
        public function getProductoSubCategoria(): ?Subcategoria { return $this->productoSubCategoria; }
        public function getProductoMarca(): ?Marca { return $this->productoMarca; }
        public function getProductoPresentacion(): ?Presentacion { return $this->productoPresentacion; }
        public function getProductoImagen() { return $this->productoImagen; }
        public function getProductoFechaVencimiento() { return $this->productoFechaVencimiento; }
        public function getProductoEstado(): bool { return $this->productoEstado; }

        public function setProductoID(int $productoID) { $this->productoID = $productoID; }
        public function setProductoCodigoBarras(CodigoBarras $productoCodigoBarras) { $this->productoCodigoBarras = $productoCodigoBarras; }
        public function setProductoNombre(string $productoNombre) { $this->productoNombre = strtoupper($productoNombre); }
        public function setProductoCantidad(int $productoCantidad) { $this->productoCantidad = $productoCantidad; }
        public function setProductoPrecioCompra(float $productoPrecioCompra) { $this->productoPrecioCompra = Utils::formatearDecimal($productoPrecioCompra); }
        public function setProductoPorcentajeGanancia(float $productoPorcentajeGanancia) { $this->productoPorcentajeGanancia = Utils::formatearDecimal($productoPorcentajeGanancia); }
        public function setProductoDescripcion(string $productoDescripcion) { $this->productoDescripcion = ucfirst($productoDescripcion); }
        public function setProductoCategoria(Categoria $productoCategoria) { $this->productoCategoria = $productoCategoria; }
        public function setProductoSubCategoria(Subcategoria $productoSubCategoria) { $this->productoSubCategoria = $productoSubCategoria; }
        public function setProductoMarca(Marca $productoMarca) { $this->productoMarca = $productoMarca; }
        public function setProductoPresentacion(Presentacion $productoPresentacion) { $this->productoPresentacion = $productoPresentacion; }
        public function setProductoImagen($productoImagen) { $this->productoImagen = $productoImagen; }
        public function setProductoFechaVencimiento($productoFechaVencimiento) { $this->productoFechaVencimiento = $productoFechaVencimiento; }
        public function setProductoEstado(bool $productoEstado) { $this->productoEstado = $productoEstado; }

        public function jsonSerialize() {
            return [
                'ID' => $this->productoID,
                'CodigoBarras' => $this->productoCodigoBarras ? $this->productoCodigoBarras : null,
                'Nombre' => $this->productoNombre,
                'Cantidad' => $this->productoCantidad,
                'PrecioCompra' => $this->productoPrecioCompra,
                'PorcentajeGanancia' => $this->productoPorcentajeGanancia,
                'Categoria' => $this->productoCategoria ? $this->productoCategoria : null,
                'Subcategoria' => $this->productoSubCategoria ? $this->productoSubCategoria : null,
                'Marca' => $this->productoMarca ? $this->productoMarca : null,
                'Presentacion' => $this->productoPresentacion ? $this->productoPresentacion : null,
                'Descripcion' => $this->productoDescripcion,
                'Imagen' => $this->productoImagen,
                'Vencimiento' => $this->productoFechaVencimiento ? Utils::formatearFecha($this->productoFechaVencimiento) : '',
                'VencimientoISO' => $this->productoFechaVencimiento ? Utils::formatearFecha($this->productoFechaVencimiento, 'Y-MM-dd') : '',
                'Estado' => $this->productoEstado
            ];
        }

    }

?>
