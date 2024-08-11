<?php

class Producto{
    private $idproducto;
    private $nombreproducto;
    private $preciounitarioproducto;
    private $descripcionproducto;
    private $estadoproducto;

    function __construct($idproducto, $nombreproducto,$preciounitarioproducto, $descripcionproducto="",$estadoproducto=true){
        $this->$idproducto = $idproducto;
        $this->$nombreproducto =$nombreproducto;
        $this->$preciounitarioproducto = $preciounitarioproducto;
        $this->$descripcionproducto = $descripcionproducto;
        $this->$estadoproducto = $estadoproducto;
    }
    //getters
    function getIdProducto(){return $this->$idproducto;}
    function getNombreProducto(){return $this->$nombreproducto;}
    function getDescripcionProducto(){return $this->$descripcionproducto;}
    function getEstadoProducto(){return $this->$estadoproducto;}
    function getPrecioUnitarioProducto(){ return $this->preciounitarioproducto;}

    //setters
    function setIdProducto($idproducto){$this->$idproducto = $idproducto;}
    function setNombreProducto($nombreproducto){$this->$nombreproducto =$nombreproducto;}
    function setDescripcionProducto( $descripcionproducto){$this->$descripcionproducto = $descripcionproducto;}
    function setEstadoProducto($estadoproducto){$this->$estadoproducto = $estadoproducto;}
    function setPrecioUnitarioProducto($preciounitarioproducto){$this->$preciounitarioproducto = $preciounitarioproducto;}

}
?>