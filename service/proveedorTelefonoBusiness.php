<?php
include __DIR__ . "/../data/ProveedorTelefonoData.php";

class ProveedorTelefonoBusiness {

    private $proveedorTelefonoData;

    public function __construct() {
        $this->proveedorTelefonoData = new ProveedorTelefonoData();
    }

    public function insertarProveedorTelefono($proveedorTelefono) {
        return $this->proveedorTelefonoData->insertarProveedorTelefono($proveedorTelefono);
    }

    public function actualizarProveedorTelefono($proveedorTelefono) {
        return $this->proveedorTelefonoData->actualizarProveedorTelefono($proveedorTelefono);
    }

    public function eliminarProveedorTelefono($proveedortelefonoid) {
        return $this->proveedorTelefonoData->eliminarProveedorTelefono($proveedortelefonoid);
    }

    public function getPaginationProveedorTelefono($page, $size, $sort = null) {
        return $this->proveedorTelefonoData->getPaginationProveedorTelefono($page, $size, $sort);
    }

    public function obtenerProveedorTelefonoPorId($proveedortelefonoid) {
        return $this->proveedorTelefonoData->obtenerProveedorTelefonoPorId($proveedortelefonoid);
    }

    public function obtenerProveedoresTelefonosActivos() {
        return $this->proveedorTelefonoData->obtenerProveedoresTelefonosActivos();
    }
}
?>
