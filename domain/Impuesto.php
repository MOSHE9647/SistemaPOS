<?php

class Impuesto {
    
    private $impuestoID;
    private $impuestoNombre;
    private $impuestoValor;
    private $impuestoEstado;
    private $impuestoDescripcion;
    private $impuestoFechaVigencia;

    function __construct($impuestoNombre = "", $impuestoValor = 0.0, $impuestoFechaVigencia = null, 
            $impuestoID = 0, $impuestoDescripcion = "", $impuestoEstado = true) {
        $this->impuestoID = $impuestoID;
        $this->impuestoNombre = strtoupper($impuestoNombre);
        $this->impuestoValor = $impuestoValor;
        $this->impuestoEstado = $impuestoEstado;
        $this->impuestoDescripcion = $impuestoDescripcion;
        $this->impuestoFechaVigencia = $impuestoFechaVigencia;
    }

    function getImpuestoID() { return $this->impuestoID; }
    function getImpuestoNombre() { return $this->impuestoNombre; }
    function getImpuestoValor() { return $this->impuestoValor; }
    function getImpuestoEstado() { return $this->impuestoEstado; }
    function getImpuestoDescripcion() { return $this->impuestoDescripcion; }
    function getImpuestoFechaVigencia() { return $this->impuestoFechaVigencia; }

    function setImpuestoID($impuestoID) { $this->impuestoID = $impuestoID; }
    function setImpuestoNombre($impuestoNombre) { $this->impuestoNombre = $impuestoNombre; }
    function setImpuestoValor($impuestoValor) { $this->impuestoValor = $impuestoValor; }
    function setImpuestoEstado($impuestoEstado) { $this->impuestoEstado = $impuestoEstado; }
    function setImpuestoDescripcion($impuestoDescripcion) { $this->impuestoDescripcion = $impuestoDescripcion; }
    function setImpuestoFechaVigencia($impuestoFechaVigencia) { $this->impuestoFechaVigencia = $impuestoFechaVigencia; }
    
    // Implementación del método __toString
    public function __toString() {
        return 
            "ID: " . $this->impuestoID . "\n" .
            "Nombre: " . $this->impuestoNombre . "\n" .
            "Valor: " . $this->impuestoValor . "\n" .
            "Estado: " . ($this->impuestoEstado ? "Activo" : "Inactivo") . "\n" .
            "Descripción: " . $this->impuestoDescripcion . "\n" .
            "Fecha de Vigencia: " . $this->impuestoFechaVigencia . "\n"
        ;
    }

}