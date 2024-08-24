<?php

    class Telefono {

        private $telefonoID;
        private $telefonoProveedorID;
        private $telefonoFechaCreacion;
        private $telefonoTipo;
        private $telefonoExtension;
        private $telefonoCodigoPais;
        private $telefonoNumero;
        private $telefonoEstado;

        public function __construct($telefonoCodigoPais, $telefonoNumero, $telefonoTipo, $telefonoProveedorID, 
                $telefonoID = 0, $telefonoExtension = null, $telefonoFechaCreacion = null, $telefonoEstado = true) {
            $this->telefonoID = $telefonoID;
            $this->telefonoProveedorID = $telefonoProveedorID;
            $this->telefonoFechaCreacion = $telefonoFechaCreacion;
            $this->telefonoTipo = $telefonoTipo;
            $this->telefonoExtension = $telefonoExtension;
            $this->telefonoCodigoPais = $telefonoCodigoPais;
            $this->telefonoNumero = $telefonoNumero;
            $this->telefonoEstado = $telefonoEstado;
        }

        function getTelefonoID() { return $this->telefonoID; }
        function getTelefonoProveedorID() { return $this->telefonoProveedorID; }
        function getTelefonoFechaCreacion() { return $this->telefonoFechaCreacion; }
        function getTelefonoTipo() { return $this->telefonoTipo; }
        function getTelefonoExtension() { return $this->telefonoExtension; }
        function getTelefonoCodigoPais() { return $this->telefonoCodigoPais; }
        function getTelefonoNumero() { return $this->telefonoNumero; }
        function getTelefonoEstado() { return $this->telefonoEstado; }

        function setTelefonoID($telefonoID) { $this->telefonoID = $telefonoID; }
        function setTelefonoProveedorID($telefonoProveedorID) { $this->telefonoProveedorID = $telefonoProveedorID; }
        function setTelefonoFechaCreacion($telefonoFechaCreacion) { $this->telefonoFechaCreacion = $telefonoFechaCreacion; }
        function setTelefonoTipo($telefonoTipo) { $this->telefonoTipo = $telefonoTipo; }
        function setTelefonoExtension($telefonoExtension) { $this->telefonoExtension = $telefonoExtension; }
        function setTelefonoCodigoPais($telefonoCodigoPais) { $this->telefonoCodigoPais = $telefonoCodigoPais; }
        function setTelefonoNumero($telefonoNumero) { $this->telefonoNumero = $telefonoNumero; }
        function setTelefonoEstado($telefonoEstado) { $this->telefonoEstado = $telefonoEstado; }

        public function __toString() {
            return sprintf(
                "ID: %d\nProveedor ID: %d\nFecha de Creación: %s\nTipo: %s\nExtensión: %s\nCódigo País: %s\nNúmero: %s\nEstado: %s",
                $this->telefonoID,
                $this->telefonoProveedorID,
                $this->telefonoFechaCreacion ? $this->telefonoFechaCreacion->format('Y-m-d') : 'N/A',
                $this->telefonoTipo,
                $this->telefonoExtension ?? 'N/A',
                $this->telefonoCodigoPais,
                $this->telefonoNumero,
                $this->telefonoEstado ? 'Activo' : 'Inactivo'
            );
        }

    }

?>