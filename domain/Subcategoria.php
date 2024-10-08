<?php

class Subcategoria{
    private $subcategoria_id;
    private $subcategoria_nombre;
    private $subcategoria_descripcion;
    private $sucategoria_categoria_id;
    private $subcategoria_estado;

    // Constructor
    public function __construct($subcategoria_nombre,$sucategoria_categoria_id ,$subcategoria_descripcion = "", $subcategoria_id = 0, $subcategoria_estado = true ) {
        $this->subcategoria_id = $subcategoria_id;
        $this->subcategoria_nombre = $subcategoria_nombre;
        $this->subcategoria_estado = $subcategoria_estado;
        $this->subcategoria_descripcion = $subcategoria_descripcion;
        $this->sucategoria_categoria_id = $sucategoria_categoria_id;
    }

    // Getters
    public function getSubcategoriaId() { return $this->subcategoria_id; }
    public function getSubcategoriaNombre() { return $this->subcategoria_nombre; }
    public function getSubcategoriaEstado() { return $this->subcategoria_estado; }
    public function getSubcategoriaDescripcion() { return $this->subcategoria_descripcion; }
    public function getSubcategoriaCategoriaId() {return $this->sucategoria_categoria_id; }

    // Setters
    public function setSubcategoriaId($subcategoria_id) { $this->subcategoria_id = $subcategoria_id; }
    public function setSubcategoriaNombre($subcategoria_nombre) { $this->subcategoria_nombre = $subcategoria_nombre; }
    public function setSubcategoriaEstado($subcategoria_estado) { $this->subcategoria_estado = $subcategoria_estado; }
    public function setSubcategoriaDescripcion($subcategoria_descripcion) { $this->subcategoria_descripcion = $subcategoria_descripcion; }
    public function setSubcategoriaCategoriaId( $sucategoria_categoria_id){$this->sucategoria_categoria_id = $sucategoria_categoria_id;}

    public function __toString() {
        return "Subcategoria: [ID: " . $this->subcategoria_id . 
               ", Nombre: " . $this->subcategoria_nombre . 
               ", Descripción: " . $this->subcategoria_descripcion . 
               ", Categoría ID: " . $this->sucategoria_categoria_id . 
               ", Estado: " . ($this->subcategoria_estado ? 'Activo' : 'Inactivo') . "]";
    }
}


?>