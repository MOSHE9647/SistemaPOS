<?php

    require_once dirname(__DIR__, 1) . "/utils/Utils.php";

    class RolUsuario implements JsonSerializable {
        
        public $rolID;
        public $rolNombre;
        public $rolDescripcion;
        public $rolEstado;

        public function __construct(int $rolID = -1, string $rolNombre = "", string $rolDescripcion = "", bool $rolEstado = true) {
            $this->rolID = $rolID;
            $this->rolNombre = ucfirst(strtolower($rolNombre));
            $this->rolDescripcion = $rolDescripcion;
            $this->rolEstado = $rolEstado;
        }

        public function setRolID(int $rolID) { $this->rolID = $rolID; }
        public function setRolNombre(string $rolNombre) { $this->rolNombre = ucfirst(strtolower($rolNombre)); }
        public function setRolDescripcion(string $rolDescripcion) { $this->rolDescripcion = $rolDescripcion; }
        public function setRolEstado(bool $rolEstado) { $this->rolEstado = $rolEstado; }

        public function getRolID(): int { return $this->rolID; }
        public function getRolNombre(): string { return $this->rolNombre; }
        public function getRolDescripcion(): string { return $this->rolDescripcion; }
        public function getRolEstado(): bool { return $this->rolEstado; }

        public function jsonSerialize() {
            return [
                'ID' => $this->rolID,
                'Nombre' => $this->rolNombre,
                'Descripcion' => $this->rolDescripcion,
                'Estado' => $this->rolEstado
            ];
        }

    }

?>