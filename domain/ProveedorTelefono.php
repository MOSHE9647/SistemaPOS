<?php

class ProveedorTelefono {
    private $proveedortelefonoid;
    private $proveedorid;
    private $telefono;
    private $activo;

    public function __construct($proveedortelefonoid, $proveedorid, $telefono, $activo) {
        $this->proveedortelefonoid = $proveedortelefonoid;
        $this->proveedorid = $proveedorid;
        $this->telefono = $telefono;
        $this->activo = $activo;
    }

    // Getters
    public function getProveedorTelefonoId() {
        return $this->proveedortelefonoid;
    }

    public function getProveedorId() {
        return $this->proveedorid;
    }

    public function getTelefono() {
        return $this->telefono;
    }

    public function getActivo() {
        return $this->activo;
    }

    // Setters
    public function setProveedorTelefonoId($proveedortelefonoid) {
        $this->proveedortelefonoid = $proveedortelefonoid;
    }

    public function setProveedorId($proveedorid) {
        $this->proveedorid = $proveedorid;
    }

    public function setTelefono($telefono) {
        $this->telefono = $telefono;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }
}

?>
