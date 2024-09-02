<?php

require_once __DIR__ . '/../data/CompraProductoData.php';
require_once __DIR__ . '/../domain/CompraProducto.php';
require_once __DIR__ . '/../utils/Utils.php';

class CompraProductoBusiness {

    private $compraProductoData;

    public function __construct() {
        $this->compraProductoData = new CompraProductoData();
    }

    /**
     * Inserta un nuevo registro de Compra Producto.
     * @param CompraProducto $compraProducto
     * @return array
     */
    public function insertarCompraProducto($compraProducto) {
        try {
            // Validar objeto CompraProducto
            $validationResult = $this->validarCompraProducto($compraProducto);
            if (!$validationResult['success']) {
                return $validationResult; // Retorna errores de validación
            }

            // Llamar al método de inserción en la capa de datos
            $result = $this->compraProductoData->insertarCompraProducto($compraProducto);
            return $result;

        } catch (Exception $e) {
            return ["success" => false, "message" => "Error al insertar compra de producto: " . $e->getMessage()];
        }
    }

    /**
     * Actualiza un registro de CompraProducto existente.
     * @param CompraProducto $compraProducto
     * @return array
     */
    public function actualizarCompraProducto($compraProducto) {
        try {
            // Validar objeto Compra Producto
            $validationResult = $this->validarCompraProducto($compraProducto);
            if (!$validationResult['success']) {
                return $validationResult; // Retorna errores de validación
            }

            
            $result = $this->compraProductoData->actualizarCompraProducto($compraProducto);
            return $result;

        } catch (Exception $e) {
            return ["success" => false, "message" => "Error al actualizar compra de producto: " . $e->getMessage()];
        }
    }

    /**
     * Elimina lógicamente un registro de Compra Producto.
     * @param int $compraProductoId
     * @return array
     */
    public function eliminarCompraProducto($compraProductoId) {
        try {
            // Validar ID
            if (empty($compraProductoId) || !is_numeric($compraProductoId)) {
                return ["success" => false, "message" => "ID de la compra de producto no es válido."];
            }

            // Llamar al método de eliminación en la capa de datos
            $result = $this->compraProductoData->eliminarCompraProducto($compraProductoId);
            return $result;

        } catch (Exception $e) {
            return ["success" => false, "message" => "Error al eliminar compra de producto: " . $e->getMessage()];
        }
    }

    /**
     * Obtiene la lista de todos los registros activos de Compra Producto.
     * @return array
     */
    public function obtenerListaCompraProducto() {
        try {
            $result = $this->compraProductoData->obtenerListaCompraProducto();
            return $result;

        } catch (Exception $e) {
            return ["success" => false, "message" => "Error al obtener lista de compras de productos: " . $e->getMessage()];
        }
    }

    /**
     * Validar los datos de CompraProducto antes de la inserción o actualización.
     * @param CompraProducto $compraProducto
     * @return array
     */
    private function validarCompraProducto($compraProducto) {
        $errors = [];

        if (empty($compraProducto->getCantidad()) || !is_numeric($compraProducto->getCantidad()) || $compraProducto->getCantidad() <= 0) {
            $errors[] = "La cantidad debe ser un número positivo.";
        }

        if (empty($compraProducto->getProveedorId()) || !is_numeric($compraProducto->getProveedorId())) {
            $errors[] = "ID del proveedor no es válido.";
        }

        // Verificar fecha de creación
        if (empty($compraProducto->getFechaCreacion()) || !Utils::validarFecha($compraProducto->getFechaCreacion())) {
            $errors[] = "La fecha de creación no es válida.";
        }

        if (!empty($errors)) {
            return ["success" => false, "message" => implode(' ', $errors)];
        }

        return ["success" => true];
    }

    /**
     * Obtiene la lista de proveedores (nombre e ID)
     * @return array
     */
    public function obtenerListaProveedores() {
        try {
            // Llama al método en la capa de datos para obtener la lista de proveedores
            $result = $this->compraProductoData->obtenerListaProveedores();
            return $result;

        } catch (Exception $e) {
            return ["success" => false, "message" => "Error al obtener lista de proveedores: " . $e->getMessage()];
        }
    }
}

?>
