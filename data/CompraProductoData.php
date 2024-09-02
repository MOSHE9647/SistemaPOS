<?php

require_once __DIR__ . '/../utils/Variables.php';
require_once __DIR__ . '/../utils/Utils.php';
require_once 'Data.php';
require_once __DIR__ . '/../domain/CompraProducto.php';
require_once __DIR__ . '/../domain/Proveedor.php';

class CompraProductoData extends Data {

    public function __construct() {
        parent::__construct();
    }

    // Método para obtener un nuevo ID
    private function obtenerNuevoId() {
        try {
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];

            $query = "SELECT MAX(" . COMPRA_PRODUCTO_ID . ") AS max_id FROM " . TB_COMPRA_PRODUCTO;
            $stmt = mysqli_prepare($conn, $query);
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
            }

            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if (!$result) {
                throw new Exception("Error al obtener el resultado: " . mysqli_error($conn));
            }

            $row = mysqli_fetch_assoc($result);
            if ($row['max_id'] === null) {
                return 1;
            }
            return $row['max_id'] + 1;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        } finally {
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }

    // Método para insertar una nueva compra de producto
    public function insertarCompraProducto($compraProducto) {
        try {
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];

            $nuevoId = $this->obtenerNuevoId();
            if (!$nuevoId) {
                throw new Exception("No se pudo generar un nuevo ID.");
            }

            $query = "INSERT INTO " . TB_COMPRA_PRODUCTO . " (" . COMPRA_PRODUCTO_ID . ", " . COMPRA_PRODUCTO_CANTIDAD .
             ", " . COMPRA_PRODUCTO_PROVEEDOR_ID . ", " . COMPRA_PRODUCTO_FECHA_CREACION . 
             ", " . COMPRA_PRODUCTO_ESTADO . ") VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
            }

            $id = $compraProducto->getCompraProductoId();
            $cantidad = $compraProducto->getCompraProductoCantidad();
            $proveedorId = $compraProducto->getCompraProductoProveedorId();
            $fechaCreacion = $compraProducto->getCompraProductoFechaCreacion();
            $estado = $compraProducto->getCompraProductoEstado();

            mysqli_stmt_bind_param($stmt, 'iiisi', $nuevoId, $cantidad, $proveedorId, $fechaCreacion, $estado);
            $success = mysqli_stmt_execute($stmt);
            if (!$success) {
                throw new Exception("Error al ejecutar la consulta: " . mysqli_error($conn));
            }

            return ["success" => true, "message" => "Compra de producto guardada correctamente."];
        } catch (Exception $e) {
            error_log($e->getMessage());
            return ["success" => false, "message" => $e->getMessage()];
        } finally {
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }

    public function actualizarCompraProducto($compraProducto) {
        try {
            // Obtener conexión a la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];
    
            // Verificar que el ID del producto de compra no sea nulo o inválido
            $compraProductoId = $compraProducto->getCompraProductoId();
            if (empty($compraProductoId) || !is_numeric($compraProductoId)) {
                throw new Exception("ID de la compra de producto no válido.");
            }
    
            // Verificar que el proveedor exista
            $proveedorId = $compraProducto->getCompraProductoProveedorId();
            $queryCheckProveedor = "SELECT COUNT(*) AS count FROM " . TB_PROVEEDOR . " WHERE " . PROVEEDOR_ID . " = ? AND " . PROVEEDOR_ESTADO . " = 1";
            $stmtCheckProveedor = mysqli_prepare($conn, $queryCheckProveedor);
            if (!$stmtCheckProveedor) {
                throw new Exception("Error al preparar la consulta para verificar proveedor: " . mysqli_error($conn));
            }
    
            mysqli_stmt_bind_param($stmtCheckProveedor, 'i', $proveedorId);
            mysqli_stmt_execute($stmtCheckProveedor);
            $resultCheckProveedor = mysqli_stmt_get_result($stmtCheckProveedor);
            $rowCheckProveedor = mysqli_fetch_assoc($resultCheckProveedor);
    
            if ($rowCheckProveedor['count'] == 0) {
                throw new Exception("El proveedor con ID $proveedorId no existe o está inactivo.");
            }
    
            // Preparar consulta para actualizar la compra de producto
            $query = "UPDATE " . TB_COMPRA_PRODUCTO . " SET " . 
                     COMPRA_PRODUCTO_CANTIDAD . " = ?, " . 
                     COMPRA_PRODUCTO_PROVEEDOR_ID . " = ?, " . 
                     COMPRA_PRODUCTO_FECHA_CREACION . " = ?, " . 
                     COMPRA_PRODUCTO_ESTADO . " = ? " .
                     "WHERE " . COMPRA_PRODUCTO_ID . " = ?";
            
            $stmt = mysqli_prepare($conn, $query);
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
            }
    
            // Asignar los valores a actualizar
            $cantidad = $compraProducto->getCompraProductoCantidad();
            $fechaCreacion = $compraProducto->getCompraProductoFechaCreacion();
            $estado = $compraProducto->getCompraProductoEstado();
    
            mysqli_stmt_bind_param($stmt, 'iisi', $cantidad, $proveedorId, $fechaCreacion, $estado, $compraProductoId);
            $success = mysqli_stmt_execute($stmt);
            if (!$success) {
                throw new Exception("Error al ejecutar la consulta: " . mysqli_error($conn));
            }
    
            return ["success" => true, "message" => "Compra de producto actualizada correctamente."];
        } catch (Exception $e) {
            error_log($e->getMessage());
            return ["success" => false, "message" => $e->getMessage()];
        } finally {
            // Cerrar conexiones
            if (isset($stmtCheckProveedor)) { mysqli_stmt_close($stmtCheckProveedor); }
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }

    public function eliminarCompraProducto($compraProductoId) {
        try {
            // Obtener conexión a la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];
    
            // Validar el ID de CompraProducto
            if (empty($compraProductoId) || !is_numeric($compraProductoId)) {
                throw new Exception("ID de la compra de producto no válido.");
            }
    
            // Preparar consulta para el borrado lógico
            $query = "UPDATE " . TB_COMPRA_PRODUCTO . " SET " . COMPRA_PRODUCTO_ESTADO . " = 0 WHERE " . COMPRA_PRODUCTO_ID . " = ?";
            $stmt = mysqli_prepare($conn, $query);
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
            }
    
            
            mysqli_stmt_bind_param($stmt, 'i', $compraProductoId);
            $success = mysqli_stmt_execute($stmt);
            if (!$success) {
                throw new Exception("Error al ejecutar la consulta: " . mysqli_error($conn));
            }
    
            return ["success" => true, "message" => "Compra de producto eliminada lógicamente con éxito."];
        } catch (Exception $e) {
            error_log($e->getMessage());
            return ["success" => false, "message" => $e->getMessage()];
        } finally {
            // Cierre de conexiones
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }

    public function obtenerListaCompraProducto() {
        try {
            // Obtener conexión a la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];
    
            // Preparar consulta con INNER JOIN para obtener el nombre del proveedor
            $query = "
                SELECT cp." . COMPRA_PRODUCTO_ID . ", 
                       cp." . COMPRA_PRODUCTO_CANTIDAD . ",
                       cp." . COMPRA_PRODUCTO_PROVEEDOR_ID . ",
                       cp." . COMPRA_PRODUCTO_FECHA_CREACION . ",
                       cp." . COMPRA_PRODUCTO_ESTADO . ",
                       p." . PROVEEDOR_NOMBRE . " AS proveedor_nombre
                FROM " . TB_COMPRA_PRODUCTO . " cp
                INNER JOIN " . TB_PROVEEDOR . " p ON cp." . COMPRA_PRODUCTO_PROVEEDOR_ID . " = p." . PROVEEDOR_ID . "
                WHERE cp." . COMPRA_PRODUCTO_ESTADO . " = 1
            ";
    
            $stmt = mysqli_prepare($conn, $query);
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
            }
    
            // Ejecutar la consulta
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if (!$result) {
                throw new Exception("Error al obtener el resultado: " . mysqli_error($conn));
            }
    
            // Crear lista de CompraProducto
            $listaCompraProducto = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $compraProducto = [
                    "id" => $row[COMPRA_PRODUCTO_ID],
                    "cantidad" => $row[COMPRA_PRODUCTO_CANTIDAD],
                    "proveedor_id" => $row[COMPRA_PRODUCTO_PROVEEDOR_ID],
                    "proveedor_nombre" => $row["proveedor_nombre"],  // Obtener nombre del proveedor
                    "fecha_creacion" => $row[COMPRA_PRODUCTO_FECHA_CREACION],
                    "estado" => $row[COMPRA_PRODUCTO_ESTADO]
                ];
                array_push($listaCompraProducto, $compraProducto);
            }
    
            return ["success" => true, "data" => $listaCompraProducto];
        } catch (Exception $e) {
            error_log($e->getMessage());
            return ["success" => false, "message" => $e->getMessage()];
        } finally {
            // Cerrar conexiones
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }

    public function obtenerListaProveedores() {
        try {
            // Establecer conexión a la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];
    
            // Preparar la consulta SQL para obtener nombres e IDs de proveedores activos
            $query = "SELECT " . PROVEEDOR_ID . ", " . PROVEEDOR_NOMBRE . "
                      FROM " . TB_PROVEEDOR . "
                      WHERE " . PROVEEDOR_ESTADO . " = 1";  // Solo proveedores activos
    
            $stmt = mysqli_prepare($conn, $query);
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
            }
    
            // Ejecutar la consulta
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if (!$result) {
                throw new Exception("Error al obtener el resultado: " . mysqli_error($conn));
            }
    
            // Crear lista de proveedores con los datos obtenidos
            $listaProveedores = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $proveedor = [
                    "id" => $row[PROVEEDOR_ID],
                    "nombre" => $row[PROVEEDOR_NOMBRE]
                ];
                array_push($listaProveedores, $proveedor);
            }
    
            return ["success" => true, "data" => $listaProveedores];
        } catch (Exception $e) {
            error_log($e->getMessage());
            return ["success" => false, "message" => $e->getMessage()];
        } finally {
            // Cerrar conexiones
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }
    
    
    

    


}

?>
