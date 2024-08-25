<?php
include __DIR__ . "/../data/ProveedorProductoData.php";

class ProveedorProductoBusiness {

    private $proveedorProductoData;

    public function __construct() {
        $this->proveedorProductoData = new ProveedorProductoData();
    }

    public function insertarProveedorProducto($proveedorId, $productoId) {
        try {
            return $this->proveedorProductoData->insertarProveedorProducto($proveedorId, $productoId);
        } catch (Exception $e) {
            
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    public function obtenerTodosProveedorProducto() {
        try {
            return $this->proveedorProductoData->obtenerTodosProveedorProducto();
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    public function obtenerProveedorProductoPorId($proveedorProductoId) {
        try {
            return $this->proveedorProductoData->obtenerProveedorProductoPorId($proveedorProductoId);
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }
    
    public function actualizarProveedorProducto($proveedorProductoId, $nuevoProveedorId, $nuevoProductoId) {
        try {
            return $this->proveedorProductoData->actualizarProveedorProducto($proveedorProductoId, $nuevoProveedorId, $nuevoProductoId);
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    public function eliminarProveedorProducto($proveedorProductoId) {
        try {
            return $this->proveedorProductoData->eliminarProveedorProducto($proveedorProductoId);
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }
    
    
}
?>
