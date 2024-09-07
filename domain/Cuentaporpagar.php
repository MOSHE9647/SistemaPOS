<?php
class CuentaPorPagar {
    private $cuentaporpagarid;
    private $cuentaporpagarcompradetalleid;
    private $cuentaporpagarfechavencimiento;
    private $cuentaporpagarmontototal;
    private $cuentaporpagarmontopagado;
    private $cuentaporpagarfechapago;
    private $cuentaporpagarnotas;
    private $cuentaporpagarestadocuenta;
    private $cuentaporpagarestado;

    public function __construct($cuentaporpagarid, $cuentaporpagarcompradetalleid, $cuentaporpagarfechavencimiento, $cuentaporpagarmontototal, $cuentaporpagarmontopagado, $cuentaporpagarfechapago, $cuentaporpagarnotas, $cuentaporpagarestadocuenta, $cuentaporpagarestado) {
        $this->cuentaporpagarid = $cuentaporpagarid;
        $this->cuentaporpagarcompradetalleid = $cuentaporpagarcompradetalleid;
        $this->cuentaporpagarfechavencimiento = $cuentaporpagarfechavencimiento;
        $this->cuentaporpagarmontototal = $cuentaporpagarmontototal;
        $this->cuentaporpagarmontopagado = $cuentaporpagarmontopagado;
        $this->cuentaporpagarfechapago = $cuentaporpagarfechapago;
        $this->cuentaporpagarnotas = $cuentaporpagarnotas;
        $this->cuentaporpagarestadocuenta = $cuentaporpagarestadocuenta;
        $this->cuentaporpagarestado = $cuentaporpagarestado;
    }

    // Getters
    public function getCuentaporpagarid() {
        return $this->cuentaporpagarid;
    }

    public function getCuentaporpagarcompradetalleid() {
        return $this->cuentaporpagarcompradetalleid;
    }

    public function getCuentaporpagarfechavencimiento() {
        return $this->cuentaporpagarfechavencimiento;
    }

    public function getCuentaporpagarmontototal() {
        return $this->cuentaporpagarmontototal;
    }

    public function getCuentaporpagarmontopagado() {
        return $this->cuentaporpagarmontopagado;
    }

    public function getCuentaporpagarfechapago() {
        return $this->cuentaporpagarfechapago;
    }

    public function getCuentaporpagarnotas() {
        return $this->cuentaporpagarnotas;
    }

    public function getCuentaporpagarestadocuenta() {
        return $this->cuentaporpagarestadocuenta;
    }

    public function getCuentaporpagarestado() {
        return $this->cuentaporpagarestado;
    }

    // Setters
    public function setCuentaporpagarid($cuentaporpagarid) {
        $this->cuentaporpagarid = $cuentaporpagarid;
    }

    public function setCuentaporpagarcompradetalleid($cuentaporpagarcompradetalleid) {
        $this->cuentaporpagarcompradetalleid = $cuentaporpagarcompradetalleid;
    }

    public function setCuentaporpagarfechavencimiento($cuentaporpagarfechavencimiento) {
        $this->cuentaporpagarfechavencimiento = $cuentaporpagarfechavencimiento;
    }

    public function setCuentaporpagarmontototal($cuentaporpagarmontototal) {
        $this->cuentaporpagarmontototal = $cuentaporpagarmontototal;
    }

    public function setCuentaporpagarmontopagado($cuentaporpagarmontopagado) {
        $this->cuentaporpagarmontopagado = $cuentaporpagarmontopagado;
    }

    public function setCuentaporpagarfechapago($cuentaporpagarfechapago) {
        $this->cuentaporpagarfechapago = $cuentaporpagarfechapago;
    }

    public function setCuentaporpagarnotas($cuentaporpagarnotas) {
        $this->cuentaporpagarnotas = $cuentaporpagarnotas;
    }

    public function setCuentaporpagarestadocuenta($cuentaporpagarestadocuenta) {
        $this->cuentaporpagarestadocuenta = $cuentaporpagarestadocuenta;
    }

    public function setCuentaporpagarestado($cuentaporpagarestado) {
        $this->cuentaporpagarestado = $cuentaporpagarestado;
    }
}
?>
