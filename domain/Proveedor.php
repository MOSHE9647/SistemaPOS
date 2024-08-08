<?php

    // CLASE BASE, HAY QUE MODIFICAR

    class Proveedor {

        private $proveedorID;
        private $proveedorEstado;

        public function __construct($proveedorID = 0, $proveedorEstado = true) {
            $this->proveedorID = $proveedorID;
            $this->proveedorEstado = $proveedorEstado;
        }

        function getProveedorID() { return $this->proveedorID; }
        function getProveedorEstado() { return $this->proveedorEstado; }

        function setProveedorID($proveedorID) { $this->proveedorID = $proveedorID; }
        function setProveedorEstado($proveedorEstado) { $this->proveedorEstado = $proveedorEstado; }

    }

?>