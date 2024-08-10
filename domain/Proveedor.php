<?php

class Proveedor {
    
    private $proveedorID;
    private $proveedorNombre;
    private $proveedorEmail; 
    private $proveedorEstado;
    private $proveedorTipo;
    private $proveedorFechaRegistro;
   

    function __construct($proveedorNombre,$proveedorEmail,$proveedorFechaRegistro, $proveedorID = 0, $proveedorTipo= "",   $proveedorEstado = true) {
        $this->proveedorID = $proveedorID;
        $this->proveedorNombre = strtoupper($proveedorNombre);
        $this->proveedorEmail = strtolower($proveedorEmail); // Convertir a minúsculas para consistencia
        $this->proveedorEstado = $proveedorEstado; 
        $this->proveedorTipo = $proveedorTipo;
        $this->proveedorFechaRegistro = $proveedorFechaRegistro;
    }

    // Getters
    function getProveedorID() { return $this->proveedorID; }
    function getProveedorNombre() { return $this->proveedorNombre; }
    function getProveedorEmail() { return $this->proveedorEmail; }
    function getProveedorEstado() { return $this->proveedorEstado; }
    function getProveedorTipo() { return $this->proveedorTipo; }
    function getProveedorFechaRegistro() { return $this->proveedorFechaRegistro; }
    

    // Setters
    function setProveedorID($proveedorID) { $this->proveedorID = $proveedorID; }
    function setProveedorNombre($proveedorNombre) { $this->proveedorNombre = $proveedorNombre; }
    function setProveedorEmail($proveedorEmail) { $this->proveedorEmail = $proveedorEmail; }
    function setProveedorEstado($proveedorEstado) { $this->proveedorEstado = $proveedorEstado; }
    function setProveedorTipo($proveedorTipo) { $this->proveedorTipo = $proveedorTipo; }
    function setProveedorFechaRegistro($proveedorFechaRegistro) { $this->proveedorFechaRegistro = $proveedorFechaRegistro; }
}
?>
