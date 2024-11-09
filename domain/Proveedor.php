<?php

    require_once dirname(__DIR__, 1) . '/utils/Utils.php';
    require_once dirname(__DIR__, 1) . '/domain/Direccion.php';
    require_once dirname(__DIR__, 1) . '/domain/Categoria.php';
    require_once dirname(__DIR__, 1) . '/domain/Producto.php';
    require_once dirname(__DIR__, 1) . '/domain/Telefono.php';

    class Proveedor implements JsonSerializable {
        
        private $proveedorID;
        private $proveedorCategoria;
        private $proveedorNombre;
        private $proveedorEmail;
        private $proveedorDirecciones;
        private $proveedorProductos;
        private $proveedorTelefonos;
        private $proveedorFechaCreacion;
        private $proveedorFechaModificacion;
        private $proveedorEstado;

        function __construct(int $proveedorID = -1, string $proveedorNombre = "", string $proveedorEmail = "", 
            array $proveedorDirecciones = [], Categoria $proveedorCategoria = null, array $proveedorProductos = [],
            array $proveedorTelefonos = [], $proveedorFechaCreacion = "", $proveedorFechaModificacion = "", bool $proveedorEstado = true) 
        {
            $this->proveedorID = $proveedorID;
            $this->proveedorCategoria = $proveedorCategoria;
            $this->proveedorNombre = strtoupper($proveedorNombre);
            $this->proveedorEmail = strtolower($proveedorEmail);
            $this->proveedorDirecciones = $proveedorDirecciones;
            $this->proveedorProductos = $proveedorProductos;
            $this->proveedorTelefonos = $proveedorTelefonos;
            $this->proveedorFechaCreacion = $proveedorFechaCreacion;
            $this->proveedorFechaModificacion = $proveedorFechaModificacion;
            $this->proveedorEstado = $proveedorEstado;
        }

        public function getProveedorID(): int { return $this->proveedorID; }
        public function getProveedorCategoria(): ?Categoria { return $this->proveedorCategoria; }
        public function getProveedorNombre(): string { return $this->proveedorNombre; }
        public function getProveedorEmail(): string { return $this->proveedorEmail; }
        public function getProveedorDirecciones(): ?array { return $this->proveedorDirecciones; }
        public function getProveedorProductos(): ?array { return $this->proveedorProductos; }
        public function getProveedorTelefonos(): ?array { return $this->proveedorTelefonos; }
        public function getProveedorFechaCreacion() { return $this->proveedorFechaCreacion; }
        public function getProveedorFechaModificacion() { return $this->proveedorFechaModificacion; }
        public function getProveedorEstado(): bool { return $this->proveedorEstado; }

        public function setProveedorID(int $proveedorID) { $this->proveedorID = $proveedorID; }
        public function setProveedorCategoria(Categoria $proveedorCategoria) { $this->proveedorCategoria = $proveedorCategoria; }
        public function setProveedorNombre(string $proveedorNombre) { $this->proveedorNombre = strtoupper($proveedorNombre); }
        public function setProveedorEmail(string $proveedorEmail) { $this->proveedorEmail = strtolower($proveedorEmail); }
        public function setProveedorDirecciones(array $proveedorDirecciones) { $this->proveedorDirecciones = $proveedorDirecciones; }
        public function setProveedorProductos(array $proveedorProductos) { $this->proveedorProductos = $proveedorProductos; }
        public function setProveedorTelefonos(array $proveedorTelefonos) { $this->proveedorTelefonos = $proveedorTelefonos; }
        public function setProveedorFechaCreacion($proveedorFechaCreacion) { $this->proveedorFechaCreacion = $proveedorFechaCreacion; }
        public function setProveedorFechaModificacion($proveedorFechaModificacion) { $this->proveedorFechaModificacion = $proveedorFechaModificacion; }
        public function setProveedorEstado(bool $proveedorEstado) { $this->proveedorEstado = $proveedorEstado; }

        public static function fromArray(array $proveedor): Proveedor {
            $listaDirecciones = array_map(function($direccion) {
                return $direccion !== null ? Utils::convertToObject($direccion, Direccion::class) : null;
            }, $proveedor['Direcciones'] ?? []);

            $listaProductos = array_map(function($producto) {
                return $producto !== null ? Utils::convertToObject($producto, Producto::class) : null;
            }, $proveedor['Productos'] ?? []);

            $listaTelefonos = array_map(function($telefono) {
                return $telefono !== null ? Utils::convertToObject($telefono, Telefono::class) : null;
            }, $proveedor['Telefonos'] ?? []);

            return new Proveedor(
                intval($proveedor['ID'] ?? -1),
                $proveedor['Nombre'] ?? "",
                $proveedor['Email'] ?? "",
                $listaDirecciones = array_filter($listaDirecciones),
                Utils::convertToObject($proveedor['Categoria'] ?? null, Categoria::class),
                $listaProductos = array_filter($listaProductos),
                $listaTelefonos = array_filter($listaTelefonos),
                $proveedor['Creacion'] ?? "",
                $proveedor['Modificacion'] ?? "",
                $proveedor['Estado'] ?? true
            );
        }

        public function jsonSerialize() {
            return [
                'ID' => $this->proveedorID,
                'Nombre' => $this->proveedorNombre,
                'Email' => $this->proveedorEmail,
                'Direcciones' => $this->proveedorDirecciones,
                'Categoria' => $this->proveedorCategoria,
                'Productos' => $this->proveedorProductos,
                'Telefonos' => $this->proveedorTelefonos,
                'Creacion' => $this->proveedorFechaCreacion ? Utils::formatearFecha($this->proveedorFechaCreacion) : '',
                'Modificacion' => $this->proveedorFechaModificacion ? Utils::formatearFecha($this->proveedorFechaModificacion) : '',
                'CreacionISO' => $this->proveedorFechaCreacion ? Utils::formatearFecha($this->proveedorFechaCreacion, 'Y-MM-dd') : '',
                'ModificacionISO' => $this->proveedorFechaModificacion ? Utils::formatearFecha($this->proveedorFechaModificacion, 'Y-MM-dd') : '',
                'Estado' => $this->proveedorEstado
            ];
        }

    }
    
?>