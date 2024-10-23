<?php

    require_once dirname(__DIR__, 1) . "/utils/Utils.php";

    class Telefono implements JsonSerializable {

        private $telefonoID;
        private $telefonoTipo;
        private $telefonoCodigoPais;
        private $telefonoNumero;
        private $telefonoExtension;
        private $telefonoEstado;

        public function __construct(int $telefonoID = -1, string $telefonoTipo = "", string $telefonoCodigoPais = "", 
            string $telefonoNumero = "", string $telefonoExtension = "", bool $telefonoEstado = true) 
        {
            $this->telefonoID = $telefonoID;
            $this->telefonoTipo = $telefonoTipo;
            $this->telefonoCodigoPais = $telefonoCodigoPais;
            $this->telefonoNumero = $telefonoNumero;
            $this->telefonoExtension = $telefonoExtension;
            $this->telefonoEstado = $telefonoEstado;
        }

        function getTelefonoID() { return $this->telefonoID; }
        function getTelefonoTipo() { return $this->telefonoTipo; }
        function getTelefonoCodigoPais() { return $this->telefonoCodigoPais; }
        function getTelefonoNumero() { return $this->telefonoNumero; }
        function getTelefonoExtension() { return $this->telefonoExtension; }
        function getTelefonoEstado() { return $this->telefonoEstado; }

        function setTelefonoID($telefonoID) { $this->telefonoID = $telefonoID; }
        function setTelefonoTipo($telefonoTipo) { $this->telefonoTipo = $telefonoTipo; }
        function setTelefonoCodigoPais($telefonoCodigoPais) { $this->telefonoCodigoPais = $telefonoCodigoPais; }
        function setTelefonoNumero($telefonoNumero) { $this->telefonoNumero = $telefonoNumero; }
        function setTelefonoExtension($telefonoExtension) { $this->telefonoExtension = $telefonoExtension; }
        function setTelefonoEstado($telefonoEstado) { $this->telefonoEstado = $telefonoEstado; }

        function obtenerNumeroCompleto() {
            return $this->telefonoCodigoPais . " " . $this->telefonoNumero;
        }

        public function jsonSerialize() {
            return [
                'ID' => $this->telefonoID,
                'Tipo' => $this->telefonoTipo,
                'CodigoPais' => $this->telefonoCodigoPais,
                'Numero' => $this->telefonoNumero,
                'Extension' => $this->telefonoExtension,
                'Estado' => $this->telefonoEstado
            ];
        }

    }

?>