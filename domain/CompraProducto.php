<?php
class CompraProducto {
    private $compraProductoId;
    private $compraProductoCantidad;
    private $compraProductoProveedorId;
    private $compraProductoFechaCreacion;
    private $compraProductoEstado;

    public function __construct($compraProductoId, $compraProductoCantidad, $compraProductoProveedorId, $compraProductoFechaCreacion, $compraProductoEstado) {
        $this->compraProductoId = $compraProductoId;
        $this->compraProductoCantidad = $compraProductoCantidad;
        $this->compraProductoProveedorId = $compraProductoProveedorId;
        $this->compraProductoFechaCreacion = $compraProductoFechaCreacion;
        $this->compraProductoEstado = $compraProductoEstado;
    }

    // Getters
    public function getCompraProductoId() {
        return $this->compraProductoId;
    }

    public function getCompraProductoCantidad() {
        return $this->compraProductoCantidad;
    }

    public function getCompraProductoProveedorId() {
        return $this->compraProductoProveedorId;
    }

    public function getCompraProductoFechaCreacion() {
        return $this->compraProductoFechaCreacion;
    }

    public function getCompraProductoEstado() {
        return $this->compraProductoEstado;
    }

    // Setters
    public function setCompraProductoId($compraProductoId) {
        $this->compraProductoId = $compraProductoId;
    }

    public function setCompraProductoCantidad($compraProductoCantidad) {
        $this->compraProductoCantidad = $compraProductoCantidad;
    }

    public function setCompraProductoProveedorId($compraProductoProveedorId) {
        $this->compraProductoProveedorId = $compraProductoProveedorId;
    }

    public function setCompraProductoFechaCreacion($compraProductoFechaCreacion) {
        $this->compraProductoFechaCreacion = $compraProductoFechaCreacion;
    }

    public function setCompraProductoEstado($compraProductoEstado) {
        $this->compraProductoEstado = $compraProductoEstado;
    }
}
?>
