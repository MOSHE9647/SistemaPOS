<?php

    require_once dirname(__DIR__, 1) . "/utils/Utils.php";
    require_once dirname(__DIR__, 1) . "/domain/Telefono.php";
    require_once dirname(__DIR__, 1) . "/domain/Usuario.php";

    class Cliente implements JsonSerializable {

        private $clienteID;
        private $clienteNombre;
        private $clienteAlias;
        private $clienteUsuario;
        private $clienteTelefono;
        private $clienteFechaCreacion;
        private $clienteFechaModificacion;
        private $clienteEstado;

        public function __construct(int $clienteID = -1, string $clienteNombre = "No Definido", $clienteAlias = "No Definido", 
            Telefono $clienteTelefono = null, Usuario $clienteUsuario = null, $clienteFechaCreacion = "", $clienteFechaModificacion = "", 
            bool $clienteEstado = true) 
        {
            $this->clienteID = $clienteID;
            $this->clienteNombre = $clienteNombre;
            $this->clienteAlias = $clienteAlias;
            $this->clienteUsuario = $clienteUsuario;
            $this->clienteTelefono = $clienteTelefono;
            $this->clienteFechaCreacion = $clienteFechaCreacion;
            $this->clienteFechaModificacion = $clienteFechaModificacion;
            $this->clienteEstado = $clienteEstado;
        }

        public function getClienteID(): int { return $this->clienteID; }
        public function getClienteNombre(): string { return $this->clienteNombre; }
        public function getClienteAlias(): string { return $this->clienteAlias; }
        public function getClienteUsuario(): Usuario { return $this->clienteUsuario; }
        public function getClienteTelefono(): Telefono { return $this->clienteTelefono; }
        public function getClienteFechaCreacion() { return $this->clienteFechaCreacion; }
        public function getClienteFechaModificacion() { return $this->clienteFechaModificacion; }
        public function getClienteEstado(): bool { return $this->clienteEstado; }

        public function setClienteID(int $clienteID) { $this->clienteID = $clienteID; }
        public function setClienteNombre(string $clienteNombre) { $this->clienteNombre = $clienteNombre; }
        public function setClienteAlias(string $clienteAlias) { $this->clienteAlias = $clienteAlias; }
        public function setClienteUsuario(Usuario $clienteUsuario) { $this->clienteUsuario = $clienteUsuario; }
        public function setClienteTelefono(Telefono $clienteTelefono) { $this->clienteTelefono = $clienteTelefono; }
        public function setClienteFechaCreacion($clienteFechaCreacion) { $this->clienteFechaCreacion = $clienteFechaCreacion; }
        public function setClienteFechaModificacion($clienteFechaModificacion) { $this->clienteFechaModificacion = $clienteFechaModificacion; }
        public function setClienteEstado(bool $clienteEstado) { $this->clienteEstado = $clienteEstado; }

        public function jsonSerialize() {
            return [
                'ID' => $this->clienteID,
                'Nombre' => $this->clienteNombre,
                'Alias' => $this->clienteAlias,
                'Usuario' => $this->clienteUsuario,
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