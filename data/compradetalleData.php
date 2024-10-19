<?php

require_once dirname(__DIR__, 1) . '/data/data.php';
require_once __DIR__ . '/../domain/CompraDetalle.php';
require_once __DIR__ . '/../utils/Variables.php';

class CompraDetalleData extends Data {

    // Constructor
    public function __construct() {
        parent::__construct();
    }

    public function insertCompraDetalle($compraDetalle) {
        try {
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];
    
            $queryGetLastId = "SELECT MAX(" . COMPRA_DETALLE_ID . ") FROM " . TB_COMPRA_DETALLE;
            $idCont = mysqli_query($conn, $queryGetLastId);
            $nextId = 1;
            if ($row = mysqli_fetch_row($idCont)) {
                $nextId = (int) trim($row[0]) + 1;
            }
    
            // Obtener valores del objeto CompraDetalle
            $compraDetalleID = $compraDetalle->getCompraDetalleID();
            $compraDetalleCompra = $compraDetalle->getCompraDetalleCompra();
            $compraDetalleProducto = $compraDetalle->getCompraDetalleProducto();
            $compraDetalleFechaCreacion = $compraDetalle->getCompraDetalleFechaCreacion();
            $compraDetalleFechaModificacion = $compraDetalle->getCompraDetalleFechaModificacion();
            $compraDetalleEstado = $compraDetalle->getCompraDetalleEstado();
    
            // Query de inserción
            $queryInsert = "INSERT INTO " . TB_COMPRA_DETALLE . " ("
                . COMPRA_DETALLE_ID . ", "
                . COMPRA_DETALLE_COMPRA_ID . ", "
                . COMPRA_DETALLE_PRODUCTO_ID . ", "
                . COMPRA_DETALLE_FECHA_CREACION . ", "
                . COMPRA_DETALLE_FECHA_MODIFICACION . ", "
                . COMPRA_DETALLE_ESTADO
                . ") VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($conn, $queryInsert);
    
            // Bind de los valores
            mysqli_stmt_bind_param(
                $stmt,
                'iisssi',
                $nextId,
                $compraDetalleCompra,
                $compraDetalleProducto,
                $compraDetalleFechaCreacion,
                $compraDetalleFechaModificacion,
                $compraDetalleEstado
            );
    
            $result = mysqli_stmt_execute($stmt);
            return ["success" => true, "message" => "Detalle de compra insertado exitosamente"];
        } catch (Exception $e) {
            $userMessage = $this->handleMysqlError($e->getCode(), $e->getMessage(), 'Error al insertar el detalle de compra');
            return ["success" => false, "message" => $userMessage];
        } finally {
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }
    
    public function updateCompraDetalle($compraDetalle) {
        try {
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];
    
            $queryUpdate = "UPDATE " . TB_COMPRA_DETALLE .
                " SET " . 
                COMPRA_DETALLE_COMPRA_ID . " = ?, " .
                COMPRA_DETALLE_PRODUCTO_ID . " = ?, " .
                COMPRA_DETALLE_FECHA_CREACION . " = ?, " .
                COMPRA_DETALLE_FECHA_MODIFICACION . " = ?, " .
                COMPRA_DETALLE_ESTADO . " = ? " .
                "WHERE " . COMPRA_DETALLE_ID . " = ?";
    
            $stmt = mysqli_prepare($conn, $queryUpdate);
    
            // Obtener valores del objeto CompraDetalle
            $compraDetalleID = $compraDetalle->getCompraDetalleID();
            $compraDetalleCompra = $compraDetalle->getCompraDetalleCompra();
            $compraDetalleProducto = $compraDetalle->getCompraDetalleProducto();
            $compraDetalleFechaCreacion = $compraDetalle->getCompraDetalleFechaCreacion();
            $compraDetalleFechaModificacion = $compraDetalle->getCompraDetalleFechaModificacion();
            $compraDetalleEstado = $compraDetalle->getCompraDetalleEstado();
    
            // Bind de los valores
            mysqli_stmt_bind_param(
                $stmt,
                'iissii',
                $compraDetalleCompra,
                $compraDetalleProducto,
                $compraDetalleFechaCreacion,
                $compraDetalleFechaModificacion,
                $compraDetalleEstado,
                $compraDetalleID
            );
    
            $result = mysqli_stmt_execute($stmt);
            return ["success" => true, "message" => "Detalle de compra actualizado exitosamente"];
        } catch (Exception $e) {
            $userMessage = $this->handleMysqlError($e->getCode(), $e->getMessage(), 'Error al actualizar el detalle de compra');
            return ["success" => false, "message" => $userMessage];
        } finally {
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }
    

    public function getAllCompraDetalles() {
        try {
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];
    
            $querySelect = "
            SELECT 
                c." . COMPRA_DETALLE_ID . ", 
                c." . COMPRA_DETALLE_COMPRA_ID . ", 
                p.productonombre AS productoNombre, 
                c." . COMPRA_DETALLE_FECHA_CREACION . ", 
                c." . COMPRA_DETALLE_FECHA_MODIFICACION . ", 
                c." . COMPRA_DETALLE_ESTADO . "
            FROM " . TB_COMPRA_DETALLE . " c    
            JOIN tbproducto p ON c." . COMPRA_DETALLE_PRODUCTO_ID . " = p.productoid
            WHERE c." . COMPRA_DETALLE_ESTADO . " != false";
    
            $result = mysqli_query($conn, $querySelect);
    
            $listaCompraDetalles = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $currentCompraDetalle = new CompraDetalle(
                    $row[COMPRA_DETALLE_ID],
                    $row[COMPRA_DETALLE_COMPRA_ID],
                    $row["productoNombre"],
                    $row[COMPRA_DETALLE_FECHA_CREACION],
                    $row[COMPRA_DETALLE_FECHA_MODIFICACION],
                    $row[COMPRA_DETALLE_ESTADO]
                );
                array_push($listaCompraDetalles, $currentCompraDetalle);
            }
    
            return ["success" => true, "listaCompraDetalles" => $listaCompraDetalles];
        } catch (Exception $e) {
            $userMessage = $this->handleMysqlError($e->getCode(), $e->getMessage(), 'Error al obtener la lista de detalles de compra');
            return ["success" => false, "message" => $userMessage];
        } finally {
            if (isset($conn)) { mysqli_close($conn); }
        }
    }
    
    public function getPaginatedCompraDetalles($page, $size, $sort = null) {
        try {
            // Verificar que la página y el tamaño sean números enteros positivos
            if (!is_numeric($page) || $page < 1) {
                throw new Exception("El número de página debe ser un entero positivo.");
            }
            if (!is_numeric($size) || $size < 1) {
                throw new Exception("El tamaño de la página debe ser un entero positivo.");
            }
    
            // Calcular el offset para la paginación
            $offset = ($page - 1) * $size;
    
            // Establecer conexión a la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];
    
            // Consultar el total de registros en la tabla
            $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_COMPRA_DETALLE . " WHERE " . COMPRA_DETALLE_ESTADO . " != false";
            $totalResult = mysqli_query($conn, $queryTotalCount);
            $totalRow = mysqli_fetch_assoc($totalResult);
            $totalRecords = (int) $totalRow['total'];
            $totalPages = ceil($totalRecords / $size);
    
            // Construir la consulta SQL para paginación
            $querySelect = "
            SELECT 
                c." . COMPRA_DETALLE_ID . ", 
                cp.compranumerofactura AS compranumeroFactura,
           
                p.productonombre AS productoNombre,
                c." . COMPRA_DETALLE_PRECIO_PRODUCTO . ", 
                c." . COMPRA_DETALLE_CANTIDAD . ", 
                c." . COMPRA_DETALLE_FECHA_CREACION . ", 
                c." . COMPRA_DETALLE_FECHA_MODIFICACION . ", 
                c." . COMPRA_DETALLE_ESTADO . "
            FROM " . TB_COMPRA_DETALLE . " c
            JOIN tbcompra cp ON c." . COMPRA_DETALLE_COMPRA_ID . " = cp.compraid
            
            JOIN tbproducto p ON c." . COMPRA_DETALLE_PRODUCTO_ID . " = p.productoid
            WHERE c." . COMPRA_DETALLE_ESTADO . " != false 
            ";
    
            // Agregar ordenamiento si se proporciona
            if ($sort) {
                $querySelect .= "ORDER BY " . $sort . " ";
            }
    
            $querySelect .= "LIMIT ? OFFSET ?";
    
            // Preparar la consulta y vincular los parámetros
            $stmt = mysqli_prepare($conn, $querySelect);
            mysqli_stmt_bind_param($stmt, "ii", $size, $offset);
    
            // Ejecutar la consulta
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
    
            // Crear la lista de detalles de compra
            $listaCompraDetalles = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $listaCompraDetalles[] = [
                    'ID' => $row[COMPRA_DETALLE_ID],
                    'CompraNumeroFactura' => $row["compranumeroFactura"],
                    'LoteCodigo' => $row["loteCodigo"],
                    'ProductoNombre' => $row["productoNombre"],
                    'PrecioProducto' => $row[COMPRA_DETALLE_PRECIO_PRODUCTO],
                    'Cantidad' => $row[COMPRA_DETALLE_CANTIDAD],
                    'FechaCreacion' => $row[COMPRA_DETALLE_FECHA_CREACION],
                    'FechaModificacion' => $row[COMPRA_DETALLE_FECHA_MODIFICACION],
                    'Estado' => $row[COMPRA_DETALLE_ESTADO]
                ];
            }
    
            // Devolver el resultado con la lista de detalles de compra y metadatos de paginación
            return [
                "success" => true,
                "page" => $page,
                "size" => $size,
                "totalPages" => $totalPages,
                "totalRecords" => $totalRecords,
                "listaCompraDetalles" => $listaCompraDetalles
            ];
        } catch (Exception $e) {
            // Manejo del error dentro del bloque catch
            $userMessage = $this->handleMysqlError(
                $e->getCode(), 
                $e->getMessage(),
                'Error al obtener la lista de detalles de compra desde la base de datos'
            );
    
            // Devolver mensaje amigable para el usuario
            return ["success" => false, "message" => $userMessage];
        } finally {
            // Cerrar la conexión y el statement
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }
    
    private function compraDetalleExiste($compraDetalleID) {
        try {
            // Establecer conexión a la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];
    
            // Consulta para verificar la existencia del registro
            $queryCheck = "SELECT COUNT(*) FROM " . TB_COMPRA_DETALLE . " WHERE " . COMPRA_DETALLE_ID . " = ? AND " . COMPRA_DETALLE_ESTADO . " != false";
            $stmt = mysqli_prepare($conn, $queryCheck);
            
            // Asignar los parámetros y ejecutar la consulta
            mysqli_stmt_bind_param($stmt, "i", $compraDetalleID);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $count);
            mysqli_stmt_fetch($stmt);
    
            return ["success" => true, "exists" => $count > 0];
        } catch (Exception $e) {
            // Manejo del error dentro del bloque catch
            $userMessage = $this->handleMysqlError(
                $e->getCode(), 
                $e->getMessage(),
                'Error al verificar la existencia del detalle de compra en la base de datos'
            );
    
            // Devolver mensaje amigable para el usuario
            return ["success" => false, "message" => $userMessage];
        } finally {
            // Cerrar la conexión y el statement
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }
    
    public function deleteCompraDetalle($compraDetalleID) {
        try {
            // Verifica que el ID del detalle de compra sea válido
            if (empty($compraDetalleID) || !is_numeric($compraDetalleID) || $compraDetalleID <= 0) {
                throw new Exception("El ID no puede estar vacío o ser menor a 0.");
            }
    
            // Verificar si existe el ID
            $check = $this->compraDetalleExiste($compraDetalleID);
            if (!$check["success"]) {
                return $check; // Error al verificar la existencia
            }
            if (!$check["exists"]) {
                throw new Exception("No se encontró un detalle de compra con el ID [" . $compraDetalleID . "]");
            }
    
            // Establecer conexión a la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];
    
            // Consulta para eliminar el registro
            $queryDelete = "UPDATE " . TB_COMPRA_DETALLE . " SET " . COMPRA_DETALLE_ESTADO . " = false WHERE " . COMPRA_DETALLE_ID . " = ?";
            $stmt = mysqli_prepare($conn, $queryDelete);
            mysqli_stmt_bind_param($stmt, 'i', $compraDetalleID);
    
            // Ejecuta la consulta de eliminación
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error al eliminar el detalle de compra.");
            }
    
            return ["success" => true, "message" => "Detalle de compra eliminado exitosamente"];
        } catch (Exception $e) {
            // Manejo del error
            return ["success" => false, "message" => $e->getMessage()];
        } finally {
            // Cerrar la conexión y el statement
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }
    
    public function getCompraDetalleByID($compraDetalleID) {
        try {
            // Verificar existencia del detalle de compra
            $check = $this->compraDetalleExiste($compraDetalleID);
            if (!$check['success']) {
                return $check;
            }
            if (!$check['exists']) {
                throw new Exception("No se encontró un detalle de compra con el ID [" . $compraDetalleID . "]");
            }
    
            // Establecer conexión a la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];
    
            // Consulta para obtener el detalle de compra por ID
            $querySelect = "SELECT 
                c." . COMPRA_DETALLE_ID . ", 
                cp.compranumerofactura AS compranumeroFactura,
                l.lotecodigo AS loteCodigo, 
                p.productonombre AS productoNombre,
                c." . COMPRA_DETALLE_PRECIO_PRODUCTO . ", 
                c." . COMPRA_DETALLE_CANTIDAD . ", 
                c." . COMPRA_DETALLE_FECHA_CREACION . ", 
                c." . COMPRA_DETALLE_FECHA_MODIFICACION . ", 
                c." . COMPRA_DETALLE_ESTADO . " 
            FROM " . TB_COMPRA_DETALLE . " c 
            JOIN tbcompra cp ON c." . COMPRA_DETALLE_COMPRA_ID . " = cp.compraid 
          
            JOIN tbproducto p ON c." . COMPRA_DETALLE_PRODUCTO_ID . " = p.productoid 
            WHERE c." . COMPRA_DETALLE_ID . " = ? AND c." . COMPRA_DETALLE_ESTADO . " != false";
    
            $stmt = mysqli_prepare($conn, $querySelect);
            mysqli_stmt_bind_param($stmt, 'i', $compraDetalleID);
            mysqli_stmt_execute($stmt);
            $resultSet = mysqli_stmt_get_result($stmt);
    
            // Obtener el resultado
            $detalle = mysqli_fetch_assoc($resultSet);
            if (!$detalle) {
                throw new Exception("No se encontró un detalle de compra con el ID [" . $compraDetalleID . "]");
            }
    
            return [
                "success" => true,
                "data" => $detalle
            ];
        } catch (Exception $e) {
            // Manejo del error
            return ["success" => false, "message" => $e->getMessage()];
        } finally {
            // Cerrar la conexión y el statement
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }
    
                    
}
?>
