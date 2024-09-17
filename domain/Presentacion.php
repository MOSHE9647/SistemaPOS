<?php

class Presentacion {
    private $presentacionId;
    private $presentacionNombre;
    private $presentacionDescripcion;
    private $presentacionEstado;

    public function __construct($presentacionId, $presentacionNombre, $presentacionDescripcion, $presentacionEstado) {
        $this->presentacionId = $presentacionId;
        $this->presentacionNombre = $presentacionNombre;
        $this->presentacionDescripcion = $presentacionDescripcion;
        $this->presentacionEstado = $presentacionEstado;
    }

    // Getters y Setters
    public function getPresentacionId() {
        return $this->presentacionId;
    }

    public function setPresentacionId($presentacionId) {
        $this->presentacionId = $presentacionId;
    }

    public function getPresentacionNombre() {
        return $this->presentacionNombre;
    }

    public function setPresentacionNombre($presentacionNombre) {
        $this->presentacionNombre = $presentacionNombre;
    }

    public function getPresentacionDescripcion() {
        return $this->presentacionDescripcion;
    }

    public function setPresentacionDescripcion($presentacionDescripcion) {
        $this->presentacionDescripcion = $presentacionDescripcion;
    }

    public function getPresentacionEstado() {
        return $this->presentacionEstado;
    }

    public function setPresentacionEstado($presentacionEstado) {
        $this->presentacionEstado = $presentacionEstado;
    }
}
?>
