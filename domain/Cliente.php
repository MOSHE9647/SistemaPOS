<?php

    require_once dirname(__DIR__, 1) . "/utils/Utils.php";

    class Cliente implements JsonSerializable {

        private $clienteID;
        private $clienteNombre;
        private $clienteTelefono;
        private $clienteTelefonoID;
        private $clienteFechaCreacion;
        private $clienteFechaModificacion;
        private $clienteEstado;

        public function __construct($clienteID = -1, $clienteNombre = "", $clienteTelefonoID = -1, 
                $clienteFechaCreacion = "", $clienteFechaModificacion = "", $clienteEstado = true, $clienteTelefono = null) {
            $this->clienteID = $clienteID;
            $this->clienteNombre = $clienteNombre;
            $this->clienteTelefono = $clienteTelefono;
            $this->clienteTelefonoID = $clienteTelefonoID;
            $this->clienteFechaCreacion = $clienteFechaCreacion;
            $this->clienteFechaModificacion = $clienteFechaModificacion;
            $this->clienteEstado = $clienteEstado;
        }

        public function getClienteID() { return $this->clienteID; }
        public function getClienteNombre() { return $this->clienteNombre; }
        public function getClienteTelefono() { return $this->clienteTelefono; }
        public function getClienteTelefonoID() { return $this->clienteTelefonoID; }
        public function getClienteFechaCreacion() { return $this->clienteFechaCreacion; }
        public function getClienteFechaModificacion() { return $this->clienteFechaModificacion; }
        public function getClienteEstado() { return $this->clienteEstado; }

        public function setClienteID($clienteID) { $this->clienteID = $clienteID; }
        public function setClienteNombre($clienteNombre) { $this->clienteNombre = $clienteNombre; }
        public function setClienteTelefono($clienteTelefono) { $this->clienteTelefono = $clienteTelefono; }
        public function setClienteTelefonoID($clienteTelefonoID) { $this->clienteTelefonoID = $clienteTelefonoID; }
        public function setClienteFechaCreacion($clienteFechaCreacion) { $this->clienteFechaCreacion = $clienteFechaCreacion; }
        public function setClienteFechaModificacion($clienteFechaModificacion) { $this->clienteFechaModificacion = $clienteFechaModificacion; }
        public function setClienteEstado($clienteEstado) { $this->clienteEstado = $clienteEstado; }

        public function jsonSerialize() {
            return [
                'ID' => $this->clienteID,
                'Nombre' => $this->clienteNombre,
                'TelefonoID' => $this->clienteTelefonoID,
                'Telefono' => $this->clienteTelefono,
                'Creacion' => $this->clienteFechaCreacion ? Utils::formatearFecha($this->clienteFechaCreacion) : '',
                'Modificacion' => $this->clienteFechaModificacion ? Utils::formatearFecha($this->clienteFechaModificacion) : '',
                'CreacionISO' => $this->clienteFechaCreacion ? Utils::formatearFecha($this->clienteFechaCreacion, 'Y-MM-dd') : '',
                'ModificacionISO' => $this->clienteFechaModificacion ? Utils::formatearFecha($this->clienteFechaModificacion, 'Y-MM-dd') : '',
                'Estado' => $this->clienteEstado
            ];
        }

    }

?>