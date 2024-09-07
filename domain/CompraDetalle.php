<?php
class CompraDetalle {
    private $compradetalleid;
    private $compradetallecompraid;
    private $compradetalleloteid;
    private $compradetalleproductoid;
    private $compradetalleprecioproducto;
    private $compradetallecantidad;
    private $compradetallefechacreacion;
    private $compradetallefechamodificacion;
    private $compradetalleestado;

    public function __construct(
        $compradetalleid,
        $compradetallecompraid,
        $compradetalleloteid,
        $compradetalleproductoid,
        $compradetalleprecioproducto,
        $compradetallecantidad,
        $compradetallefechacreacion,
        $compradetallefechamodificacion,
        $compradetalleestado
    ) {
        $this->compradetalleid = $compradetalleid;
        $this->compradetallecompraid = $compradetallecompraid;
        $this->compradetalleloteid = $compradetalleloteid;
        $this->compradetalleproductoid = $compradetalleproductoid;
        $this->compradetalleprecioproducto = $compradetalleprecioproducto;
        $this->compradetallecantidad = $compradetallecantidad;
        $this->compradetallefechacreacion = $compradetallefechacreacion;
        $this->compradetallefechamodificacion = $compradetallefechamodificacion;
        $this->compradetalleestado = $compradetalleestado;
    }

    // Getters
    public function getCompradetalleid() { return $this->compradetalleid; }
    public function getCompradetallecompraid() { return $this->compradetallecompraid; }
    public function getCompradetalleloteid() { return $this->compradetalleloteid; }
    public function getCompradetalleproductoid() { return $this->compradetalleproductoid; }
    public function getCompradetalleprecioproducto() { return $this->compradetalleprecioproducto; }
    public function getCompradetallecantidad() { return $this->compradetallecantidad; }
    public function getCompradetallefechacreacion() { return $this->compradetallefechacreacion; }
    public function getCompradetallefechamodificacion() { return $this->compradetallefechamodificacion; }
    public function getCompradetalleestado() { return $this->compradetalleestado; }

    // Setters
    public function setCompradetalleid($compradetalleid) { $this->compradetalleid = $compradetalleid; }
    public function setCompradetallecompraid($compradetallecompraid) { $this->compradetallecompraid = $compradetallecompraid; }
    public function setCompradetalleloteid($compradetalleloteid) { $this->compradetalleloteid = $compradetalleloteid; }
    public function setCompradetalleproductoid($compradetalleproductoid) { $this->compradetalleproductoid = $compradetalleproductoid; }
    public function setCompradetalleprecioproducto($compradetalleprecioproducto) { $this->compradetalleprecioproducto = $compradetalleprecioproducto; }
    public function setCompradetallecantidad($compradetallecantidad) { $this->compradetallecantidad = $compradetallecantidad; }
    public function setCompradetallefechacreacion($compradetallefechacreacion) { $this->compradetallefechacreacion = $compradetallefechacreacion; }
    public function setCompradetallefechamodificacion($compradetallefechamodificacion) { $this->compradetallefechamodificacion = $compradetallefechamodificacion; }
    public function setCompradetalleestado($compradetalleestado) { $this->compradetalleestado = $compradetalleestado; }
}
?>
