<?php

    class RolUsuario {
        
        public $rolID;
        public $rolNombre;
        public $rolDescripcion;
        public $rolEstado;

        public function __construct($rolID = -1, $rolNombre = "", $rolDescripcion = "", $rolEstado = true) {
            $this->rolID = $rolID;
            $this->rolNombre = strtoupper($rolNombre);
            $this->rolDescripcion = $rolDescripcion;
            $this->rolEstado = $rolEstado;
        }

        public function setRolID($rolID) { $this->rolID = $rolID; }
        public function setRolNombre($rolNombre) { $this->rolNombre = strtoupper($rolNombre); }
        public function setRolDescripcion($rolDescripcion) { $this->rolDescripcion = $rolDescripcion; }
        public function setRolEstado($rolEstado) { $this->rolEstado = $rolEstado; }

        public function getRolID() { return $this->rolID; }
        public function getRolNombre() { return $this->rolNombre; }
        public function getRolDescripcion() { return $this->rolDescripcion; }
        public function getRolEstado() { return $this->rolEstado; }

        public function __toString() {
            return $this->nombre;
        }

    }

?>