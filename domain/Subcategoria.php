<?php

class Subcategoria{
    private $subcategoria_id;
    private $subcategoria_nombre;
    private $subcategoria_estado;

    // Constructor
    public function __construct($subcategoria_nombre,$subcategoria_id = 0, $subcategoria_estado = true ) {
        $this->subcategoria_id = $subcategoria_id;
        $this->subcategoria_nombre = $subcategoria_nombre;
        $this->subcategoria_estado = $subcategoria_estado;
    }

    // Getters
    public function getSubcategoriaId() {
        return $this->subcategoria_id;
    }

    public function getSubcategoriaNombre() {
        return $this->subcategoria_nombre;
    }

    public function getSubcategoriaEstado() {
        return $this->subcategoria_estado;
    }

    // Setters
    public function setSubcategoriaId($subcategoria_id) {
        $this->subcategoria_id = $subcategoria_id;
    }

    public function setSubcategoriaNombre($subcategoria_nombre) {
        $this->subcategoria_nombre = $subcategoria_nombre;
    }

    public function setSubcategoriaEstado($subcategoria_estado) {
        $this->subcategoria_estado = $subcategoria_estado;
    }
}


?>