<?php

    require_once dirname(__DIR__, 1) . "/utils/Utils.php";

    class Telefono implements JsonSerializable {

        private $telefonoID;
        private $telefonoTipo;
        private $telefonoCodigoPais;
        private $telefonoNumero;
        private $telefonoExtension;
        private $telefonoFechaCreacion;
        private $telefonoFechaModificacion;
        private $telefonoEstado;

        public function __construct($telefonoID = -1, $telefonoTipo = "", $telefonoCodigoPais = "", $telefonoNumero = "",
                $telefonoExtension = "", $telefonoFechaCreacion = "", $telefonoFechaModificacion = "", $telefonoEstado = true) {
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

        public function jsonSerialize() {
            return [
                'ID' => $this->telefonoID,
                'Tipo' => $this->telefonoTipo,
                'CodigoPais' => $this->telefonoCodigoPais,
                'Numero' => $this->telefonoNumero,
                'Extension' => $this->telefonoExtension,
                'Creacion' => $this->telefonoFechaCreacion ? Utils::formatearFecha($this->telefonoFechaCreacion) : '',
                'Modificacion' => $this->telefonoFechaModificacion ? Utils::formatearFecha($this->telefonoFechaModificacion) : '',
                'CreacionISO' => $this->telefonoFechaCreacion ? Utils::formatearFecha($this->telefonoFechaCreacion, 'Y-MM-dd') : '',
                'ModificacionISO' => $this->telefonoFechaModificacion ? Utils::formatearFecha($this->telefonoFechaModificacion, 'Y-MM-dd') : '',
                'Estado' => $this->telefonoEstado
            ];
        }

    }

?>