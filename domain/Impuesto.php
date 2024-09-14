<?php

class Impuesto {
    
    private $impuestoID;
    private $impuestoNombre;
    private $impuestoValor;
    private $impuestoDescripcion;
    private $impuestoFechaInicioVigencia;
    private $impuestoFechaFinVigencia;
    private $impuestoEstado;

    function __construct($impuestoID = -1, $impuestoNombre = "", $impuestoValor = 0, $impuestoDescripcion = "", 
            $impuestoFechaInicioVigencia = null, $impuestoFechaFinVigencia = null,  $impuestoEstado = true) {
        $this->impuestoID = $impuestoID;
        $this->impuestoNombre = strtoupper($impuestoNombre);
        $this->impuestoValor = $impuestoValor;
        $this->impuestoDescripcion = $impuestoDescripcion;
        $this->impuestoFechaInicioVigencia = $impuestoFechaInicioVigencia;
        $this->impuestoFechaFinVigencia = $impuestoFechaFinVigencia;
        $this->impuestoEstado = $impuestoEstado;
    }

    function getImpuestoID() { return $this->impuestoID; }
    function getImpuestoNombre() { return $this->impuestoNombre; }
    function getImpuestoValor() { return $this->impuestoValor; }
    function getImpuestoDescripcion() { return $this->impuestoDescripcion; }
    function getImpuestoFechaInicioVigencia() { return $this->impuestoFechaInicioVigencia; }
    function getImpuestoFechaFinVigencia() { return $this->impuestoFechaFinVigencia; }
    function getImpuestoEstado() { return $this->impuestoEstado; }

    function setImpuestoID($impuestoID) { $this->impuestoID = $impuestoID; }
    function setImpuestoNombre($impuestoNombre) { $this->impuestoNombre = $impuestoNombre; }
    function setImpuestoValor($impuestoValor) { $this->impuestoValor = $impuestoValor; }
    function setImpuestoEstado($impuestoEstado) { $this->impuestoEstado = $impuestoEstado; }
    function setImpuestoDescripcion($impuestoDescripcion) { $this->impuestoDescripcion = $impuestoDescripcion; }
    function setImpuestoFechaInicioVigencia($impuestoFechaInicioVigencia) { $this->impuestoFechaInicioVigencia = $impuestoFechaInicioVigencia; }
    function setImpuestoFechaFinVigencia($impuestoFechaFinVigencia) { $this->impuestoFechaFinVigencia = $impuestoFechaFinVigencia; }
    
    // Implementación del método __toString
    public function __toString() {
        return 
            "ID: " . $this->impuestoID . "\n" .
            "Nombre: " . $this->impuestoNombre . "\n" .
            "Valor: " . $this->impuestoValor . "\n" .
            "Descripción: " . $this->impuestoDescripcion . "\n" .
            "Fecha Inicio de Vigencia: " . $this->impuestoFechaInicioVigencia . "\n" .
            "Fecha Fin de Vigencia: " . $this->impuestoFechaFinVigencia . "\n" .
            "Estado: " . ($this->impuestoEstado ? "Activo" : "Inactivo") . "\n"
        ;
    }

}