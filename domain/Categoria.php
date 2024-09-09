<?php

    class Categoria {

        private $categoriaID;
        private $categoriaNombre;
        private $categoriaDescripcion;
        private $categoriaEstado;

        function __construct($categoriaID = 0, $categoriaNombre = "", $categoriaDescripcion = "", $categoriaEstado = true) {
            $this->categoriaID = $categoriaID;
            $this->categoriaNombre = strtoupper($categoriaNombre);
            $this->categoriaDescripcion = $categoriaDescripcion;
            $this->categoriaEstado = $categoriaEstado;
        }

        function getCategoriaID() { return $this->categoriaID; }
        function getCategoriaNombre() { return $this->categoriaNombre; }
        function getCategoriaDescripcion() { return $this->categoriaDescripcion; }
        function getCategoriaEstado() { return $this->categoriaEstado; }

        function setCategoriaID($categoriaID) { $this->categoriaID = $categoriaID; }
        function setCategoriaNombre($categoriaNombre) { $this->categoriaNombre = strtoupper($categoriaNombre); }
        function setCategoriaDescripcion($categoriaDescripcion) { $this->categoriaDescripcion = $categoriaDescripcion; }
        function setCategoriaEstado($categoriaEstado) { $this->categoriaEstado = $categoriaEstado; }
        
        // Implementación del método __toString
        public function __toString() {
            return "ID: " . $this->categoriaID . "\n" .
                "Nombre: " . $this->categoriaNombre . "\n" .
                "Descripcion: " . $this->categoriaDescripcion . "\n" .
                "Estado: " . ($this->categoriaEstado ? "Activo" : "Inactivo") . "\n";
        }

    }
    
?>
