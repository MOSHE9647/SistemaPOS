<?php

class Lote {
    
    private $loteID;
    private $loteCodigo;
    private $compraID;
    private $productoID;
    private $proveedorID;
    private $loteFechaVencimiento;
    private $loteEstado;

    function __construct($loteID = 0, $loteCodigo = "", $compraID, $productoID, $proveedorID, $loteFechaVencimiento, $loteEstado = true) {
        $this->loteID = $loteID;
        $this->loteCodigo = $loteCodigo;
        $this->compraID = $compraID;
        $this->productoID = $productoID;
        $this->proveedorID = $proveedorID;
        $this->loteFechaVencimiento = $loteFechaVencimiento;
        $this->loteEstado = $loteEstado;
    }

    function getLoteID() { return $this->loteID; }
    function getLoteCodigo() { return $this->loteCodigo; }
    function getCompraID() { return $this->compraID; }
    function getProductoID() { return $this->productoID; }
    function getProveedorID() { return $this->proveedorID; }
    function getLoteFechaVencimiento() { return $this->loteFechaVencimiento; }
    function getLoteEstado() { return $this->loteEstado; }

    function setLoteID($loteID) { $this->loteID = $loteID; }
    function setLoteCodigo($loteCodigo) { $this->loteCodigo = $loteCodigo; }
    function setCompraID($compraID) { $this->compraID = $compraID; }
    function setProductoID($productoID) { $this->productoID = $productoID; }
    function setProveedorID($proveedorID) { $this->proveedorID = $proveedorID; }
    function setLoteFechaVencimiento($loteFechaVencimiento) { $this->loteFechaVencimiento = $loteFechaVencimiento; }
    function setLoteEstado($loteEstado) { $this->loteEstado = $loteEstado; }

    // Implementación del método __toString
    public function __toString() {
        return "ID: " . $this->loteID . "\n" .
               "Código: " . $this->loteCodigo . "\n" .
               "Compra ID: " . $this->compraID . "\n" .
               "Producto ID: " . $this->productoID . "\n" .
               "Proveedor ID: " . $this->proveedorID . "\n" .
               "Fecha de Vencimiento: " . $this->loteFechaVencimiento . "\n" .
               "Estado: " . ($this->loteEstado ? "Activo" : "Inactivo") . "\n";
    }
}

?>
