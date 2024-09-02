
<?php

class Lote {
    
    private $loteID;
    private $loteCodigo;
    private $loteFechaVencimiento;
    private $loteEstado;

    function __construct($loteID = 0, $loteCodigo = "", $loteFechaVencimiento = "", $loteEstado = true) {
        $this->loteID = $loteID;
        $this->loteCodigo = $loteCodigo;
        $this->loteFechaVencimiento = $loteFechaVencimiento;
        $this->loteEstado = $loteEstado;
    }

    function getLoteID() { return $this->loteID; }
    function getLoteCodigo() { return $this->loteCodigo; }
    function getLoteFechaVencimiento() { return $this->loteFechaVencimiento; }
    function getLoteEstado() { return $this->loteEstado; }

    function setLoteID($loteID) { $this->loteID = $loteID; }
    function setLoteCodigo($loteCodigo) { $this->loteCodigo = $loteCodigo; }
    function setLoteFechaVencimiento($loteFechaVencimiento) { $this->loteFechaVencimiento = $loteFechaVencimiento; }
    function setLoteEstado($loteEstado) { $this->loteEstado = $loteEstado; }

    // Implementación del método __toString
    public function __toString() {
        return "ID: " . $this->loteID . "\n" .
               "Código: " . $this->loteCodigo . "\n" .
               "Fecha de Vencimiento: " . $this->loteFechaVencimiento . "\n" .
               "Estado: " . ($this->loteEstado ? "Activo" : "Inactivo") . "\n";
    }
}

?>
