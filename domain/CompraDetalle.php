<?php
class CompraDetalle {
    private $compraDetalleID;
    private $compraID;
    //private $compraDetalleCompraID;
    private $loteID;
    //private $compraDetalleLoteID;
    private $productoID;
   // private $compraDetalleProductoID;
    private $compraDetallePrecioProducto;
    private $compraDetalleCantidad;
    private $compraDetalleFechaCreacion;
    private $compraDetalleFechaModificacion;
    private $compraDetalleEstado;

    public function __construct(
        $compraDetalleID,
        $compraID,
       // $compraDetalleCompraID,
        $loteID,
        // $compraDetalleLoteID,
        $productoID,
       // $compraDetalleProductoID,
        $compraDetallePrecioProducto,
        $compraDetalleCantidad,
        $compraDetalleFechaCreacion,
        $compraDetalleFechaModificacion,
        $compraDetalleEstado
    ) {
        $this->compraDetalleID = $compraDetalleID;
        $this->compraID = $compraID;
       // $this->compraDetalleCompraID = $compraDetalleCompraID;
        $this->loteID = $loteID;
       // $this->compraDetalleLoteID = $compraDetalleLoteID;
        $this->productoID = $productoID;
       // $this->compraDetalleProductoID = $compraDetalleProductoID;
        $this->compraDetallePrecioProducto = $compraDetallePrecioProducto;
        $this->compraDetalleCantidad = $compraDetalleCantidad;
        $this->compraDetalleFechaCreacion = $compraDetalleFechaCreacion;
        $this->compraDetalleFechaModificacion = $compraDetalleFechaModificacion;
        $this->compraDetalleEstado = $compraDetalleEstado;
    }

    // Getters
    public function getCompraDetalleID() { return $this->compraDetalleID; }
    public function getCompraID() { return $this->compraID; }
   // public function getCompraDetalleCompraID() { return $this->compraDetalleCompraID; }
    public function getLoteID() { return $this->loteID; }
    //public function getCompraDetalleLoteID() { return $this->compraDetalleLoteID; }
    public function getProductoID() { return $this->productoID; }
  //  public function getCompraDetalleProductoID() { return $this->compraDetalleProductoID; }
    public function getCompraDetallePrecioProducto() { return $this->compraDetallePrecioProducto; }
    public function getCompraDetalleCantidad() { return $this->compraDetalleCantidad; }
    public function getCompraDetalleFechaCreacion() { return $this->compraDetalleFechaCreacion; }
    public function getCompraDetalleFechaModificacion() { return $this->compraDetalleFechaModificacion; }
    public function getCompraDetalleEstado() { return $this->compraDetalleEstado; }

    // Setters
    public function setCompraDetalleID($compraDetalleID) { $this->compraDetalleID = $compraDetalleID; }
    public function setCompraID($compraID) { $this->compraID = $compraID; }
  //  public function setCompraDetalleCompraID($compraDetalleCompraID) { $this->compraDetalleCompraID = $compraDetalleCompraID; }
    public function setLoteID($loteID) { $this->loteID = $loteID; }
    //public function setCompraDetalleLoteID($compraDetalleLoteID) { $this->compraDetalleLoteID = $compraDetalleLoteID; }
    public function setProductoID($productoID) { $this->productoID = $productoID; }
   // public function setCompraDetalleProductoID($compraDetalleProductoID) { $this->compraDetalleProductoID = $compraDetalleProductoID; }
    public function setCompraDetallePrecioProducto($compraDetallePrecioProducto) { $this->compraDetallePrecioProducto = $compraDetallePrecioProducto; }
    public function setCompraDetalleCantidad($compraDetalleCantidad) { $this->compraDetalleCantidad = $compraDetalleCantidad; }
    public function setCompraDetalleFechaCreacion($compraDetalleFechaCreacion) { $this->compraDetalleFechaCreacion = $compraDetalleFechaCreacion; }
    public function setCompraDetalleFechaModificacion($compraDetalleFechaModificacion) { $this->compraDetalleFechaModificacion = $compraDetalleFechaModificacion; }
    public function setCompraDetalleEstado($compraDetalleEstado) { $this->compraDetalleEstado = $compraDetalleEstado; }
}
?>
