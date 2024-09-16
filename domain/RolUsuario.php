<?php

    class RolUsuario {
        
        public $id;
        public $nombre;
        public $descripcion;
        public $estado;

        public function __construct($id = -1, $nombre = "", $descripcion = "", $estado = true) {
            $this->id = $id;
            $this->nombre = strtoupper($nombre);
            $this->descripcion = $descripcion;
            $this->estado = $estado;
        }

        public function setRolID($id) { $this->id = $id; }
        public function setRolNombre($nombre) { $this->nombre = strtoupper($nombre); }
        public function setRolDescripcion($descripcion) { $this->descripcion = $descripcion; }
        public function setRolEstado($estado) { $this->estado = $estado; }

        public function getRolID() { return $this->id; }
        public function getRolNombre() { return $this->nombre; }
        public function getRolDescripcion() { return $this->descripcion; }
        public function getRolEstado() { return $this->estado; }

        public function __toString() {
            return $this->nombre;
        }

    }

?>