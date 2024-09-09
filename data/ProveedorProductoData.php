<?php
    require_once 'data.php';
    require_once __DIR__ . '/../domain/ProveedorProducto.php';
    require_once __DIR__ . '/../domain/Producto.php';
    require_once __DIR__ . '/../utils/Variables.php';

    class ProveedorProducto extends Data{

        public function __construct(){
            parent::__construct();
        }
        
        private function obtenerNuevoId() {
            try {
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];

                $query = "SELECT MAX(" . PROVEEDOR_PRODUCTO_ID . ") AS max_id FROM " . TB_PROVEEDOR_PRODUCTO;
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

        function verificarExisteProducto($producto_id = null, $producto_nombre = null, $producto_Fecha = null){
            try {
                
                 //Conexion a la base de datos
                 
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
                
                //Generando sentencia SQL dinámica
               
                $queryCheck = "SELECT * FROM " . TB_PRODUCTO . " WHERE ";
                $conditions = [];
                $params = [];
                $types = "";
        
                if ($producto_id !== null) {
                    // Verificar existencia por ID
                    $conditions[] = PRODUCTO_ID . " = ?";
                    $params[] = $producto_id;
                    $types .= 'i';
                }
                if ($producto_nombre !== null) {
                    // Verificar existencia por nombre
                    $conditions[] = PRODUCTO_NOMBRE . " = ?";
                    $params[] = $producto_nombre;
                    $types .= 's';
                }
                if ($producto_Fecha !== null) {
                    // Verificar existencia por fecha de adquisición
                    $conditions[] = PRODUCTO_FECHA_ADQ . " = ?";
                    $params[] = $producto_Fecha;
                    $types .= 's';
                }
        
                // Asegurar que el producto esté activo
                $conditions[] = PRODUCTO_ESTADO . " != false";
        
                // Unir todas las condiciones
                $queryCheck .= implode(' AND ', $conditions);
                
                
                //Preparar y ejecutar la consulta

                $stmt = mysqli_prepare($conn, $queryCheck);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }
        
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
        
                // Verifica si existe algún registro con los criterios dados
                if (mysqli_num_rows($result) > 0) {
                    return ["success" => true, "exists" => true];
                }
                return ["success" => true, "exists" => false];
            } catch (Exception $e) {
                // Devuelve el mensaje de error
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        
        
        }

        private function verificarProveedorExiste($proveedorId) {
            try {
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                $query = "SELECT COUNT(*) AS count FROM " . TB_PROVEEDOR . " WHERE " . PROVEEDOR_ID . " = ? AND " . PROVEEDOR_ESTADO . " != false";
                $stmt = mysqli_prepare($conn, $query);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }
        
                mysqli_stmt_bind_param($stmt, 'i', $proveedorId);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if (!$result) {
                    throw new Exception("Error al obtener el resultado: " . mysqli_error($conn));
                }
        
                $row = mysqli_fetch_assoc($result);
                return $row['count'] > 0;
            } catch (Exception $e) {
                error_log($e->getMessage());
                return false;
            } finally {
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }
        

        public function insertarProveedorProducto($proveedorId, $productoId) {
            try {
                // Verifica si el producto y el proveedor existen
                if (!$this->verificarExisteProducto($productoId)) {
                    throw new Exception("El producto con ID $productoId no existe.");
                }
                if (!$this->verificarProveedorExiste($proveedorId)) {
                    throw new Exception("El proveedor con ID $proveedorId no existe.");
                }
        
                // Genera un nuevo ID para la relación
                $nuevoId = $this->obtenerNuevoId();
                if (!$nuevoId) {
                    throw new Exception("No se pudo generar un nuevo ID.");
                }
        
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                $query = "INSERT INTO " . TB_PROVEEDOR_PRODUCTO . " (" . PROVEEDOR_PRODUCTO_ID . ", " . PROVEEDOR_ID . ", " . PRODUCTO_ID . ") VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($conn, $query);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }
        
                mysqli_stmt_bind_param($stmt, 'iii', $nuevoId, $proveedorId, $productoId);
                $success = mysqli_stmt_execute($stmt);
                if (!$success) {
                    throw new Exception("Error al ejecutar la consulta: " . mysqli_error($conn));
                }
        
                return ["success" => true, "message" => "Guardado correctamente."];
            } catch (Exception $e) {
                error_log($e->getMessage());
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        //Obtener nombres de Proveedores-Producto junto con sus ID
        public function obtenerTodosProveedorProducto() {
            try {
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                //Inner Join
                $query = "SELECT pp." . PROVEEDOR_PRODUCTO_ID . ", 
                                 p." . PRODUCTO_ID . " AS producto_id, 
                                 p." . PRODUCTO_NOMBRE . " AS producto_nombre, 
                                 pr." . PROVEEDOR_ID . " AS proveedor_id, 
                                 pr." . PROVEEDOR_NOMBRE . " AS proveedor_nombre 
                          FROM " . TB_PROVEEDOR_PRODUCTO . " pp
                          INNER JOIN " . TB_PRODUCTO . " p ON pp." . PRODUCTO_ID . " = p." . PRODUCTO_ID . "
                          INNER JOIN " . TB_PROVEEDOR . " pr ON pp." . PROVEEDOR_ID . " = pr." . PROVEEDOR_ID;
        
                $stmt = mysqli_prepare($conn, $query);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }
        
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if (!$result) {
                    throw new Exception("Error al obtener el resultado: " . mysqli_error($conn));
                }
        
                $proveedorProductos = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $proveedorProductos[] = $row;
                }
        
                return ["success" => true, "data" => $proveedorProductos];
            } catch (Exception $e) {
                error_log($e->getMessage());
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }
        

        public function actualizarProveedorProducto($proveedorProductoId, $nuevoProveedorId, $nuevoProductoId) {
            try {
                // Verifica si el nuevo producto y proveedor existen
                if (!$this->verificarExisteProducto($nuevoProductoId)) {
                    throw new Exception("El producto con ID $nuevoProductoId no existe.");
                }
                if (!$this->verificarProveedorExiste($nuevoProveedorId)) {
                    throw new Exception("El proveedor con ID $nuevoProveedorId no existe.");
                }
        
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                $query = "UPDATE " . TB_PROVEEDOR_PRODUCTO . " 
                          SET " . PROVEEDOR_ID . " = ?, " . PRODUCTO_ID . " = ? 
                          WHERE " . PROVEEDOR_PRODUCTO_ID . " = ?";
                $stmt = mysqli_prepare($conn, $query);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }
        
                mysqli_stmt_bind_param($stmt, 'iii', $nuevoProveedorId, $nuevoProductoId, $proveedorProductoId);
                $success = mysqli_stmt_execute($stmt);
                if (!$success) {
                    throw new Exception("Error al ejecutar la consulta: " . mysqli_error($conn));
                }
        
                return ["success" => true, "message" => "Actualizado correctamente."];
            } catch (Exception $e) {
                error_log($e->getMessage());
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function eliminarProveedorProducto($proveedorProductoId) {
            try {
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                $query = "DELETE FROM " . TB_PROVEEDOR_PRODUCTO . " WHERE " . PROVEEDOR_PRODUCTO_ID . " = ?";
                $stmt = mysqli_prepare($conn, $query);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }
        
                mysqli_stmt_bind_param($stmt, 'i', $proveedorProductoId);
                $success = mysqli_stmt_execute($stmt);
                if (!$success) {
                    throw new Exception("Error al ejecutar la consulta: " . mysqli_error($conn));
                }
        
                return ["success" => true, "message" => "Eliminado correctamente."];
            } catch (Exception $e) {
                error_log($e->getMessage());
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function obtenerProveedorProductoPorId($proveedorProductoId) {
            try {
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                $query = "SELECT pp." . PROVEEDOR_PRODUCTO_ID . ", 
                                 p." . PRODUCTO_ID . " AS producto_id, 
                                 p." . PRODUCTO_NOMBRE . " AS producto_nombre, 
                                 pr." . PROVEEDOR_ID . " AS proveedor_id, 
                                 pr." . PROVEEDOR_NOMBRE . " AS proveedor_nombre 
                          FROM " . TB_PROVEEDOR_PRODUCTO . " pp
                          INNER JOIN " . TB_PRODUCTO . " p ON pp." . PRODUCTO_ID . " = p." . PRODUCTO_ID . "
                          INNER JOIN " . TB_PROVEEDOR . " pr ON pp." . PROVEEDOR_ID . " = pr." . PROVEEDOR_ID . "
                          WHERE pp." . PROVEEDOR_PRODUCTO_ID . " = ?";
        
                $stmt = mysqli_prepare($conn, $query);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }
        
                mysqli_stmt_bind_param($stmt, 'i', $proveedorProductoId);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if (!$result) {
                    throw new Exception("Error al obtener el resultado: " . mysqli_error($conn));
                }
        
                $proveedorProducto = mysqli_fetch_assoc($result);
                return ["success" => true, "data" => $proveedorProducto];
            } catch (Exception $e) {
                error_log($e->getMessage());
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }        
    }

?>