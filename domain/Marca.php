<?php

class Marca {
    // Atributos privados
    private $marcaId;
    private $marcaNombre;
    private $marcaDescripcion;
    private $marcaEstado;

    // Constructor
    public function __construct($marcaId = null, $marcaNombre = null, $marcaDescripcion = null, $marcaEstado = null) {
        $this->marcaId = $marcaId;
        $this->marcaNombre = $marcaNombre;
        $this->marcaDescripcion = $marcaDescripcion;
        $this->marcaEstado = $marcaEstado;
    }

    // Getters y Setters

    
    public function getMarcaId() {
        return $this->marcaId;
    }

   
    public function setMarcaId($marcaId) {
        $this->marcaId = $marcaId;
    }

    
    public function getMarcaNombre() {
        return $this->marcaNombre;
    }

    
    public function setMarcaNombre($marcaNombre) {
        $this->marcaNombre = $marcaNombre;
    }

    
    public function getMarcaDescripcion() {
        return $this->marcaDescripcion;
    }

    
    public function setMarcaDescripcion($marcaDescripcion) {
        $this->marcaDescripcion = $marcaDescripcion;
    }

    
    public function getMarcaEstado() {
        return $this->marcaEstado;
    }

    
    public function setMarcaEstado($marcaEstado) {
        $this->marcaEstado = $marcaEstado;
    }
}
?>
