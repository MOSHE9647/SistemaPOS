<?php
Class Compra{
    private $compraID;
    private $compraNumeroFactura;
    private $compraMontoBruto;
    private $compraMontoNeto;
    private $compraTipoPago;
    //private $compraProveedorId;
    private $proveedorID;
    private $compraFechaCreacion;
    private $compraFechaModificacion;
    private $compraEstado;
//$compraProveedorId=0 
    function __construct($compraID=0, $compraNumeroFactura = "", $compraMontoBruto=0.0, $compraMontoNeto=0.0, $compraTipoPago="",
     $proveedorID, $compraFechaCreacion="", $compraFechaModificacion="", $compraEstado=true)
    {
        $this->compraID = $compraID;
        $this->compraNumeroFactura = $compraNumeroFactura;
        $this->compraMontoBruto = $compraMontoBruto;
        $this->compraMontoNeto = $compraMontoNeto;
        $this->compraTipoPago = $compraTipoPago;
        //$this->compraProveedorId = $compraProveedorId;
        $this->proveedorID = $proveedorID;
        $this->compraFechaCreacion = $compraFechaCreacion;
        $this->compraFechaModificacion = $compraFechaModificacion;
        $this->compraEstado = $compraEstado;
    }
      // Getters y Setters para los atributos
    function getCompraID() {return $this->compraID;}
    function getCompraNumeroFactura() {return $this->compraNumeroFactura;}
    function getCompraMontoBruto() {return $this->compraMontoBruto;}
    function getCompraMontoNeto() {return $this->compraMontoNeto;}
     function getCompraTipoPago() {return $this->compraTipoPago;}
     //function getCompraProveedorId() {  return $this->compraProveedorId;}
     function getProveedorID() { return $this->proveedorID; }
    function getCompraFechaCreacion() {   return $this->compraFechaCreacion;}
    function getCompraFechaModificacion() { return $this->compraFechaModificacion; }
    function getCompraEstado() {return $this->compraEstado;}


    function setCompraID($compraID) {$this->compraID = $compraID;}
    function setCompraNumeroFactura($compraNumeroFactura) {$this->compraNumeroFactura = $compraNumeroFactura;}
    function setCompraMontoBruto($compraMontoBruto) {$this->compraMontoBruto = $compraMontoBruto;} 
    function setCompraMontoNeto($compraMontoNeto) {     $this->compraMontoNeto = $compraMontoNeto;}
    function setCompraTipoPago($compraTipoPago) {    $this->compraTipoPago = $compraTipoPago;}
    //function setCompraProveedorId($compraProveedorId) {  $this->compraProveedorId = $compraProveedorId;}
    function setProveedorID($proveedorID) { $this->proveedorID = $proveedorID; }
    function setCompraFechaCreacion($compraFechaCreacion) {   $this->compraFechaCreacion = $compraFechaCreacion; }
    function setCompraFechaModificacion($compraFechaModificacion) {$this->compraFechaModificacion = $compraFechaModificacion;}
    function setCompraEstado($compraEstado) {$this->compraEstado = $compraEstado;}

    // Implementación del método __toString
public function __toString() {
    return "ID: " . $this->compraID . "\n" .
           "Número de Factura: " . $this->compraNumeroFactura . "\n" .
           "Monto Bruto: " . $this->compraMontoBruto . "\n" .
           "Monto Neto: " . $this->compraMontoNeto . "\n" .
           "Tipo de Pago: " . $this->compraTipoPago . "\n" .
          // "Proveedor ID: " . $this->compraProveedorId . "\n" .
          "Proveedor ID: " . $this->proveedorID . "\n" .
           "Fecha de Creación: " . $this->compraFechaCreacion . "\n" .
           "Fecha de Modificación: " . $this->compraFechaModificacion . "\n" .
           "Estado: " . ($this->compraEstado ? "Activo" : "Inactivo") . "\n";
}

}
?>