<?php

    class Telefono {

        private $telefonoID;
        private $telefonoTipo;
        private $telefonoCodigoPais;
        private $telefonoNumero;
        private $telefonoExtension;
        private $telefonoFechaCreacion;
        private $telefonoFechaModificacion;
        private $telefonoEstado;

        public function __construct($telefonoID = -1, $telefonoTipo = "", $telefonoCodigoPais = "", $telefonoNumero = "",
                $telefonoExtension = "", $telefonoFechaCreacion = null, $telefonoFechaModificacion = null, $telefonoEstado = true) {
            $this->telefonoID = $telefonoID;
            $this->telefonoTipo = $telefonoTipo;
            $this->telefonoCodigoPais = $telefonoCodigoPais;
            $this->telefonoNumero = $telefonoNumero;
            $this->telefonoExtension = $telefonoExtension;
            $this->telefonoFechaCreacion = $telefonoFechaCreacion;
            $this->telefonoFechaModificacion = $telefonoFechaModificacion;
            $this->telefonoEstado = $telefonoEstado;
        }

        function getTelefonoID() { return $this->telefonoID; }
        function getTelefonoTipo() { return $this->telefonoTipo; }
        function getTelefonoCodigoPais() { return $this->telefonoCodigoPais; }
        function getTelefonoNumero() { return $this->telefonoNumero; }
        function getTelefonoExtension() { return $this->telefonoExtension; }
        function getTelefonoFechaCreacion() { return $this->telefonoFechaCreacion; }
        function getTelefonoFechaModificacion() { return $this->telefonoFechaModificacion; }
        function getTelefonoEstado() { return $this->telefonoEstado; }

        function setTelefonoID($telefonoID) { $this->telefonoID = $telefonoID; }
        function setTelefonoTipo($telefonoTipo) { $this->telefonoTipo = $telefonoTipo; }
        function setTelefonoCodigoPais($telefonoCodigoPais) { $this->telefonoCodigoPais = $telefonoCodigoPais; }
        function setTelefonoNumero($telefonoNumero) { $this->telefonoNumero = $telefonoNumero; }
        function setTelefonoExtension($telefonoExtension) { $this->telefonoExtension = $telefonoExtension; }
        function setTelefonoFechaCreacion($telefonoFechaCreacion) { $this->telefonoFechaCreacion = $telefonoFechaCreacion; }
        function setTelefonoFechaModificacion($telefonoFechaModificacion) { $this->telefonoFechaModificacion = $telefonoFechaModificacion; }
        function setTelefonoEstado($telefonoEstado) { $this->telefonoEstado = $telefonoEstado; }

        public function __toString() {
            return sprintf(
                "ID: %d\nFecha de Creación: %s\nFecha de Modificación: %s\nTipo: %s\nExtensión: %s\nCódigo País: %s\nNúmero: %s\nEstado: %s",
                $this->telefonoID,
                $this->telefonoProveedorID,
                $this->telefonoFechaCreacion ? $this->telefonoFechaCreacion->format('Y-m-d') : 'N/A',
                $this->telefonoFechaModificacion ? $this->telefonoFechaModificacion->format('Y-m-d') : 'N/A',
                $this->telefonoTipo,
                $this->telefonoExtension ?? 'N/A',
                $this->telefonoCodigoPais,
                $this->telefonoNumero,
                $this->telefonoEstado ? 'Activo' : 'Inactivo'
            );
        }

    }

?>