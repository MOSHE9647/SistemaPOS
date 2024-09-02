<?php

class TipoCompra {

    private $tipoCompraID;
    private $tipoCompraFechaCreacion;
    private $tipoCompraFechaModificacion;
    private $tipoCompraCompraProductoID;
    private $tipoCompraDescripcion;
    private $tipoCompraNombre;
    private $tipoCompraTasaInteres;
    private $tipoCompraPlazos;
    private $tipoCompraMeses;
    private $tipoCompraEstado;

    function __construct(
        $tipoCompraNombre, 
        $tipoCompraTasaInteres, 
        $tipoCompraPlazos, 
        $tipoCompraMeses, 
        $tipoCompraCompraProductoID, 
        $tipoCompraID = 0, 
        $tipoCompraDescripcion = "", 
        $tipoCompraEstado = 1, 
        $tipoCompraFechaCreacion = null, 
        $tipoCompraFechaModificacion = null
    ) {
        $this->tipoCompraID = $tipoCompraID;
        $this->tipoCompraFechaCreacion = $tipoCompraFechaCreacion ?? date('Y-m-d H:i:s');
        $this->tipoCompraFechaModificacion = $tipoCompraFechaModificacion ?? date('Y-m-d H:i:s');
        $this->tipoCompraCompraProductoID = $tipoCompraCompraProductoID;
        $this->tipoCompraDescripcion = $tipoCompraDescripcion;
        $this->tipoCompraNombre = strtoupper($tipoCompraNombre);
        $this->tipoCompraTasaInteres = $tipoCompraTasaInteres;
        $this->tipoCompraPlazos = $tipoCompraPlazos;
        $this->tipoCompraMeses = $tipoCompraMeses;
        $this->tipoCompraEstado = $tipoCompraEstado;
    }

    function getTipoCompraID() { return $this->tipoCompraID; }
    function getTipoCompraFechaCreacion() { return $this->tipoCompraFechaCreacion; }
    function getTipoCompraFechaModificacion() { return $this->tipoCompraFechaModificacion; }
    function getTipoCompraCompraProductoID() { return $this->tipoCompraCompraProductoID; }
    function getTipoCompraDescripcion() { return $this->tipoCompraDescripcion; }
    function getTipoCompraNombre() { return $this->tipoCompraNombre; }
    function getTipoCompraTasaInteres() { return $this->tipoCompraTasaInteres; }
    function getTipoCompraPlazos() { return $this->tipoCompraPlazos; }
    function getTipoCompraMeses() { return $this->tipoCompraMeses; }
    function getTipoCompraEstado() { return $this->tipoCompraEstado; }

    function setTipoCompraID($tipoCompraID) { $this->tipoCompraID = $tipoCompraID; }
    function setTipoCompraFechaCreacion($tipoCompraFechaCreacion) { $this->tipoCompraFechaCreacion = $tipoCompraFechaCreacion; }
    function setTipoCompraFechaModificacion($tipoCompraFechaModificacion) { $this->tipoCompraFechaModificacion = $tipoCompraFechaModificacion; }
    function setTipoCompraCompraProductoID($tipoCompraCompraProductoID) { $this->tipoCompraCompraProductoID = $tipoCompraCompraProductoID; }
    function setTipoCompraDescripcion($tipoCompraDescripcion) { $this->tipoCompraDescripcion = $tipoCompraDescripcion; }
    function setTipoCompraNombre($tipoCompraNombre) { $this->tipoCompraNombre = $tipoCompraNombre; }
    function setTipoCompraTasaInteres($tipoCompraTasaInteres) { $this->tipoCompraTasaInteres = $tipoCompraTasaInteres; }
    function setTipoCompraPlazos($tipoCompraPlazos) { $this->tipoCompraPlazos = $tipoCompraPlazos; }
    function setTipoCompraMeses($tipoCompraMeses) { $this->tipoCompraMeses = $tipoCompraMeses; }
    function setTipoCompraEstado($tipoCompraEstado) { $this->tipoCompraEstado = $tipoCompraEstado; }

    // Implementación del método __toString
    public function __toString() {
        return "ID: " . $this->tipoCompraID . "\n" .
               "Fecha de Creación: " . $this->tipoCompraFechaCreacion . "\n" .
               "Fecha de Modificación: " . $this->tipoCompraFechaModificacion . "\n" .
               "ID de CompraProducto: " . $this->tipoCompraCompraProductoID . "\n" .
               "Descripción: " . $this->tipoCompraDescripcion . "\n" .
               "Nombre: " . $this->tipoCompraNombre . "\n" .
               "Tasa de Interés: " . $this->tipoCompraTasaInteres . "\n" .
               "Plazos: " . $this->tipoCompraPlazos . "\n" .
               "Meses: " . $this->tipoCompraMeses . "\n" .
               "Estado: " . ($this->tipoCompraEstado ? "Activo" : "Inactivo") . "\n";
    }
}
?>
