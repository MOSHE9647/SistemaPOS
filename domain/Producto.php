<?php

class Producto{
    private $idproducto;
    private $nombreproducto;
    private $preciounitarioproducto;
    private $cantidadproducto;
    private $fechaadquisicionproducto;
    private $descripcionproducto;
    private $estadoproducto;

    function __construct($nombreproducto,$preciounitarioproducto,$cantidadproducto,$fechaadquisicionproducto,$idproducto=0,$descripcionproducto="",$estadoproducto = true){
        $this->idproducto = $idproducto;
        $this->nombreproducto = $nombreproducto;
        $this->preciounitarioproducto = $preciounitarioproducto;
        $this->cantidadproducto = $cantidadproducto;
        $this->fechaadquisicionproducto = $fechaadquisicionproducto;
        $this->descripcionproducto = $descripcionproducto;
        $this->estadoproducto = $estadoproducto;
    }
    //getters
    function getIdProducto(){return $this->idproducto;}
    function getNombreProducto(){return $this->nombreproducto;}
    function getDescripcionProducto(){return $this->descripcionproducto;}
    function getEstadoProducto(){return $this->estadoproducto;}
    function getPrecioUnitarioProducto(){ return $this->preciounitarioproducto;}
    function getCantidadProducto(){return $this->cantidadproducto;}
    function getFechaAdquisicion(){return $this->fechaadquisicionproducto;}
    //setters
    function setIdProducto($idproducto){$this->idproducto = $idproducto;}
    function setNombreProducto($nombreproducto){$this->nombreproducto =$nombreproducto;}
    function setDescripcionProducto( $descripcionproducto){$this->descripcionproducto = $descripcionproducto;}
    function setEstadoProducto($estadoproducto){$this->estadoproducto = $estadoproducto;}
    function setPrecioUnitarioProducto($preciounitarioproducto){$this->preciounitarioproducto = $preciounitarioproducto;}
    function setCantidadProducto($cantidadproducto){$this->cantidadproducto = $cantidadproducto;}
    function setFechaAdquisicion($fechaadquisicionproducto){$this->fechaadquisicionproducto = $fechaadquisicionproducto;}

    //
    function __toString() {
        return 
            "Producto ID:". $this->idproducto."\n" .
            "Nombre:". $this->nombreproducto."\n" .
            "Precio Unitario:". $this->preciounitarioproducto."\n" .
            "Cantidad:". $this->cantidadproducto."\n" .
            "Fecha de Adquisición:". $this->fechaadquisicionproducto."\n" .
            "Descripción:". $this->descripcionproducto."\n" .
            "Estado: " . ($this->estadoproducto ? "Activo" : "Inactivo") . "\n";
    }
}
?>