<?php

class Lote {
    
    private $loteID;
    private $loteCodigo;
    private $productoID;
    private $loteFechaVencimiento;
    private $loteEstado;

    function __construct($loteID = 0, $loteCodigo = "", $productoID, $loteFechaVencimiento, $loteEstado = true) {
        $this->loteID = $loteID;
        $this->loteCodigo = $loteCodigo;
        $this->productoID = $productoID;
        $this->loteFechaVencimiento = $loteFechaVencimiento;
        $this->loteEstado = $loteEstado;
    }

    function getLoteID() { return $this->loteID; }
    function getLoteCodigo() { return $this->loteCodigo; }
    function getProductoID() { return $this->productoID; }
    function getLoteFechaVencimiento() { return $this->loteFechaVencimiento; }
    function getLoteEstado() { return $this->loteEstado; }

    function setLoteID($loteID) { $this->loteID = $loteID; }
    function setLoteCodigo($loteCodigo) { $this->loteCodigo = $loteCodigo; }
    function setProductoID($productoID) { $this->productoID = $productoID; }
    function setLoteFechaVencimiento($loteFechaVencimiento) { $this->loteFechaVencimiento = $loteFechaVencimiento; }
    function setLoteEstado($loteEstado) { $this->loteEstado = $loteEstado; }

    // Implementación del método __toString
    public function __toString() {
        return "ID: " . $this->loteID . "\n" .
               "Código: " . $this->loteCodigo . "\n" .
               "Producto ID: " . $this->productoID . "\n" .
               "Fecha de Vencimiento: " . $this->loteFechaVencimiento . "\n" .
               "Estado: " . ($this->loteEstado ? "Activo" : "Inactivo") . "\n";
    }
}

?>
