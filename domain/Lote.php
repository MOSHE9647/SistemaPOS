<?php

class Lote {
    
    private $loteID;
    private $loteCodigo;
    private $productoID;
    private $loteCantidad;
    private $lotePrecio;
    private $proveedorID;
    private $loteFechaIngreso;
    private $loteFechaVencimiento;
    private $loteEstado;

    function __construct($loteID = 0, $loteCodigo = "", $productoID, $loteCantidad, $lotePrecio, $proveedorID,
     $loteFechaIngreso, $loteFechaVencimiento, $loteEstado = true) {
        $this->loteID = $loteID;
        $this->loteCodigo = $loteCodigo;
        $this->productoID = $productoID;
        $this->loteCantidad = $loteCantidad;
        $this->lotePrecio = $lotePrecio;
        $this->proveedorID = $proveedorID;
        $this->loteFechaIngreso = $loteFechaIngreso;
        $this->loteFechaVencimiento = $loteFechaVencimiento;
        $this->loteEstado = $loteEstado;
    }

    function getLoteID() { return $this->loteID; }
    function getLoteCodigo() { return $this->loteCodigo; }
    function getProductoID() { return $this->productoID; }
    function getLoteCantidad() { return $this->loteCantidad; }
    function getLotePrecio() { return $this->lotePrecio; }
    function getProveedorID() { return $this->proveedorID; }
    function getLoteFechaIngreso() { return $this->loteFechaIngreso; }
    function getLoteFechaVencimiento() { return $this->loteFechaVencimiento; }
    function getLoteEstado() { return $this->loteEstado; }

    function setLoteID($loteID) { $this->loteID = $loteID; }
    function setLoteCodigo($loteCodigo) { $this->loteCodigo = $loteCodigo; }
    function setProductoID($productoID) { $this->productoID = $productoID; }
    function setLoteCantidad($loteCantidad) { $this->loteCantidad = $loteCantidad; }
    function setLotePrecio($lotePrecio) { $this->lotePrecio = $lotePrecio; }
    function setProveedorID($proveedorID) { $this->proveedorID = $proveedorID; }
    function setLoteFechaIngreso($loteFechaIngreso) { $this->loteFechaIngreso = $loteFechaIngreso; }
    function setLoteFechaVencimiento($loteFechaVencimiento) { $this->loteFechaVencimiento = $loteFechaVencimiento; }
    function setLoteEstado($loteEstado) { $this->loteEstado = $loteEstado; }

    // Implementación del método __toString
    public function __toString() {
        return "ID: " . $this->loteID . "\n" .
               "Código: " . $this->loteCodigo . "\n" .
               "Producto ID: " . $this->productoID . "\n" .
               "Cantidad: " . $this->loteCantidad . "\n" .
               "Precio: " . $this->lotePrecio . "\n" .
               "Proveedor ID: " . $this->proveedorID . "\n" .
               "Fecha de Ingreso: " . $this->loteFechaIngreso . "\n" .
               "Fecha de Vencimiento: " . $this->loteFechaVencimiento . "\n" .
               "Estado: " . ($this->loteEstado ? "Activo" : "Inactivo") . "\n";
    }
}

?>
