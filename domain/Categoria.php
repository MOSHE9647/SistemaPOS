<?php

class Categoria {

    private $categoriaID;
    private $categoriaNombre;
    private $categoriaEstado;

    function __construct($categoriaNombre, $categoriaID = 0, $categoriaEstado = true) {
        $this->categoriaID = $categoriaID;
        $this->categoriaNombre = strtoupper($categoriaNombre);
        $this->categoriaEstado = $categoriaEstado;
    }

    function getCategoriaID() { return $this->categoriaID; }
    function getCategoriaNombre() { return $this->categoriaNombre; }
    function getCategoriaEstado() { return $this->categoriaEstado; }

    function setCategoriaID($categoriaID) { $this->categoriaID = $categoriaID; }
    function setCategoriaNombre($categoriaNombre) { $this->categoriaNombre = strtoupper($categoriaNombre); }
    function setCategoriaEstado($categoriaEstado) { $this->categoriaEstado = $categoriaEstado; }
    
    // Implementación del método __toString
    public function __toString() {
        return "ID: " . $this->categoriaID . "\n" .
               "Nombre: " . $this->categoriaNombre . "\n" .
               "Estado: " . ($this->categoriaEstado ? "Activo" : "Inactivo") . "\n";
    }

}
?>
