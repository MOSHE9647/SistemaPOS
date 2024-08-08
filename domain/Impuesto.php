<?php

class Impuesto {
    
    private $impuestoID;
    private $impuestoNombre;
    private $impuestoValor;
    private $impuestoDescripcion;
    private $impuestoFechaVigencia;

    function __construct($impuestoID, $impuestoNombre, $impuestoValor, $impuestoDescripcion, $impuestoFechaVigencia) {
        $this->impuestoID = $impuestoID;
        $this->impuestoNombre = $impuestoNombre;
        $this->impuestoValor = $impuestoValor;
        $this->impuestoDescripcion = $impuestoDescripcion;
        $this->impuestoFechaVigencia = $impuestoFechaVigencia;
    }

    function getImpuestoID() { return $this->impuestoID; }
    function getImpuestoNombre() { return $this->impuestoNombre; }
    function getImpuestoValor() { return $this->impuestoValor; }
    function getImpuestoDescripcion() { return $this->impuestoDescripcion; }
    function getImpuestoFechaVigencia() { return $this->impuestoFechaVigencia; }

    function setImpuestoID($impuestoID) { $this->impuestoID = $impuestoID; }
    function setImpuestoNombre($impuestoNombre) { $this->impuestoNombre = $impuestoNombre; }
    function setImpuestoValor($impuestoValor) { $this->impuestoValor = $impuestoValor; }
    function setImpuestoDescripcion($impuestoDescripcion) { $this->impuestoDescripcion = $impuestoDescripcion; }
    function setImpuestoFechaVigencia($impuestoFechaVigencia) { $this->impuestoFechaVigencia = $impuestoFechaVigencia; }
    
}