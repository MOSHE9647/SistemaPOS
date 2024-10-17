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
            // Establece una conexión con la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];

            // Obtiene el último ID de la tabla tbcompradetalle
            $queryGetLastId = "SELECT MAX(" . COMPRA_DETALLE_ID . ") FROM " . TB_COMPRA_DETALLE;
            $idCont = mysqli_query($conn, $queryGetLastId);
            $nextId = 1;

            // Calcula el siguiente ID para la nueva entrada
            if ($row = mysqli_fetch_row($idCont)) {
                $nextId = (int) trim($row[0]) + 1;
            }
            $compraDetalleiID = $compraDetalle->getCompraDetalleID();
            $compraID = $compraDetalle->getCompraID();
            $loteID = $compraDetalle->getLoteID();
            $productoID = $compraDetalle->getProductoID();
            $compraDetallePrecioProducto = $compraDetalle->getCompraDetallePrecioProducto();
            $compraDetalleCantidad = $compraDetalle->getCompraDetalleCantidad(); 
            $compraDetalleFechaCreacion = $compraDetalle->getCompraDetalleFechaCreacion();
            $compraDetalleFechaModificacion = $compraDetalle->getCompraDetalleFechaModificacion();
            $compraDetalleEstado = $compraDetalle->getCompraDetalleEstado(); // Falta agregar este valor
            // Crea una consulta y un statement SQL para insertar el registro
            $queryInsert = "INSERT INTO " . TB_COMPRA_DETALLE . " ("
                . COMPRA_DETALLE_ID . ", "
                . COMPRA_DETALLE_COMPRA_ID . ", "                  
                . COMPRA_DETALLE_LOTE_ID . ", "
                . COMPRA_DETALLE_PRODUCTO_ID . ", "
                . COMPRA_DETALLE_PRECIO_PRODUCTO . ", "
                . COMPRA_DETALLE_CANTIDAD . ", "
                . COMPRA_DETALLE_FECHA_CREACION . ", "
                . COMPRA_DETALLE_FECHA_MODIFICACION . ", "
                . COMPRA_DETALLE_ESTADO
                . ") VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, true)";
            $stmt = mysqli_prepare($conn, $queryInsert);

            // Obtener los valores de las propiedades del objeto $compraDetalle
          //  $compraID = $compraDetalle->getCompraID();
            //$loteID = $compraDetalle->getLoteID();
            //$productoID = $compraDetalle->getProductoID();
            //$precioProducto = $compraDetalle->getPrecioProducto();
            //$cantidad = $compraDetalle->getCantidad();

            // Asigna los valores a cada '?' de la consulta
            mysqli_stmt_bind_param(
                $stmt,
                'iiiidiss', // i: Entero, d: Doble, s: Cadena
                $nextId,
                $compraID,
                $loteID ,
                $productoID,
                $compraDetallePrecioProducto,
                $compraDetalleCantidad,
                $compraDetalleFechaCreacion,
                $compraDetalleFechaModificacion 
            );

            // Ejecuta la consulta de inserción
            $result = mysqli_stmt_execute($stmt);
            return ["success" => true, "message" => "Detalle de compra insertado exitosamente"];
        } catch (Exception $e) {
            // Manejo del error dentro del bloque catch
            $userMessage = $this->handleMysqlError(
                $e->getCode(), 
                $e->getMessage(),
                'Error al insertar el detalle de compra en la base de datos'
            );

            // Devolver mensaje amigable para el usuario
            return ["success" => false, "message" => $userMessage];
        } finally {
            // Cierra el statement y la conexión si están definidos
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }
    public function updateCompraDetalle($compraDetalle) {
        try {
            // Establece una conexión con la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];
    
            // Crea una consulta y un statement SQL para actualizar el registro
            $queryUpdate = 
                "UPDATE " . TB_COMPRA_DETALLE . 
                " SET " . 
                    COMPRA_DETALLE_COMPRA_ID . " = ?, " .
                    COMPRA_DETALLE_LOTE_ID . " = ?, " .
                    COMPRA_DETALLE_PRODUCTO_ID . " = ?, " .
                    COMPRA_DETALLE_PRECIO_PRODUCTO . " = ?, " .
                    COMPRA_DETALLE_CANTIDAD . " = ?, " .
                    COMPRA_DETALLE_FECHA_CREACION . " = ?, " .
                    COMPRA_DETALLE_FECHA_MODIFICACION . " = ?, " .
                    COMPRA_DETALLE_ESTADO . " = ? " .
                "WHERE " . COMPRA_DETALLE_ID . " = ?";
            $stmt = mysqli_prepare($conn, $queryUpdate);
    
            // Obtener los valores de las propiedades del objeto $compraDetalle
            $compraDetalleiID = $compraDetalle->getCompraDetalleID();
            $compraID = $compraDetalle->getCompraID();
            $loteID = $compraDetalle->getLoteID();
            $productoID = $compraDetalle->getProductoID();
            $compraDetallePrecioProducto = $compraDetalle->getCompraDetallePrecioProducto();
            $compraDetalleCantidad = $compraDetalle->getCompraDetalleCantidad(); 
            $compraDetalleFechaCreacion = $compraDetalle->getCompraDetalleFechaCreacion();
            $compraDetalleFechaModificacion = $compraDetalle->getCompraDetalleFechaModificacion();
            $compraDetalleEstado = $compraDetalle->getCompraDetalleEstado(); // Falta agregar este valor
    
            // Asigna los valores a cada '?' de la consulta
            mysqli_stmt_bind_param(
                $stmt,
                'iiidissii', // i: Entero, d: Doble, s: Cadena
                $compraID,
                $loteID,
                $productoID,
                $compraDetallePrecioProducto,
                $compraDetalleCantidad,
                $compraDetalleFechaCreacion,
                $compraDetalleFechaModificacion,
                $compraDetalleEstado,
                $compraDetalleiID
            );
    
            // Ejecuta la consulta de actualización
            $result = mysqli_stmt_execute($stmt);
    
            // Devuelve el resultado de la consulta
            return ["success" => true, "message" => "Detalle de compra actualizado exitosamente"];
        } catch (Exception $e) {
            // Manejo del error dentro del bloque catch
            $userMessage = $this->handleMysqlError(
                $e->getCode(), 
                $e->getMessage(),
                'Error al actualizar el detalle de compra en la base de datos'
            );
    
            // Devolver mensaje amigable para el usuario
            return ["success" => false, "message" => $userMessage];
        } finally {
            // Cierra el statement y la conexión si están definidos
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }

    public function getAllCompraDetalles() {
        try {
            // Establece una conexión con la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];
    
            // Construir la consulta SQL para obtener todos los detalles de compra activos
            $querySelect = "
            SELECT 
                c." . COMPRA_DETALLE_ID . ", 
                cp.compranumerofactura AS compranumeroFactura,
                l.lotecodigo AS loteCodigo, 
                p.productonombre AS productoNombre,   
                c." . COMPRA_DETALLE_PRODUCTO_ID . ", 
                c." . COMPRA_DETALLE_PRECIO_PRODUCTO . ", 
                c." . COMPRA_DETALLE_CANTIDAD . ", 
                c." . COMPRA_DETALLE_FECHA_CREACION . ", 
                c." . COMPRA_DETALLE_FECHA_MODIFICACION . ", 
                c." . COMPRA_DETALLE_ESTADO . "
            FROM " . TB_COMPRA_DETALLE . " c    
            JOIN tbcompra cp ON c." . COMPRA_DETALLE_COMPRA_ID . " = cp.compraid
            JOIN tblote l ON c." . COMPRA_DETALLE_LOTE_ID . " = l.loteid
            JOIN tbproducto p ON c." . COMPRA_DETALLE_PRODUCTO_ID . " = p.productoid
            WHERE c." . COMPRA_DETALLE_ESTADO . " != false
            ";
    
            $result = mysqli_query($conn, $querySelect);
    
            // Crear la lista con los datos obtenidos
            $listaCompraDetalles = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $currentCompraDetalle = new CompraDetalle(
                    $row[COMPRA_DETALLE_ID],
                    $row["compranumeroFactura"],
                    $row["loteCodigo"],
                    $row["productoNombre"],
                    $row[COMPRA_DETALLE_PRECIO_PRODUCTO],
                    $row[COMPRA_DETALLE_CANTIDAD],
                    $row[COMPRA_DETALLE_FECHA_CREACION],
                    $row[COMPRA_DETALLE_FECHA_MODIFICACION],
                    $row[COMPRA_DETALLE_ESTADO]
                );
                array_push($listaCompraDetalles, $currentCompraDetalle);
            }
    
            return ["success" => true, "listaCompraDetalles" => $listaCompraDetalles];
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
            // Cerramos la conexión
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
    
            // Establece una conexión con la base de datos
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
                l.lotecodigo AS loteCodigo, 
                p.productonombre AS productoNombre,
                c." . COMPRA_DETALLE_PRECIO_PRODUCTO . ", 
                c." . COMPRA_DETALLE_CANTIDAD . ", 
                c." . COMPRA_DETALLE_FECHA_CREACION . ", 
                c." . COMPRA_DETALLE_FECHA_MODIFICACION . ", 
                c." . COMPRA_DETALLE_ESTADO . "
            FROM " . TB_COMPRA_DETALLE . " c
            JOIN tbcompra cp ON c." . COMPRA_DETALLE_COMPRA_ID . " = cp.compraid
            JOIN tblote l ON c." . COMPRA_DETALLE_LOTE_ID . " = l.loteid
            JOIN tbproducto p ON c." . COMPRA_DETALLE_PRODUCTO_ID . " = p.productoid
            WHERE c." . COMPRA_DETALLE_ESTADO . " != false 
            ";
            $querySelect.= "LIMIT ? OFFSET ?";
            // Preparar la consulta y vincular los parámetros
            $stmt = mysqli_prepare($conn, $querySelect);
            mysqli_stmt_bind_param($stmt, "ii", $size, $offset);
    
            // Ejecutar la consulta
            mysqli_stmt_execute($stmt);
    
            // Obtener el resultado
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
            // Establece una conexión con la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];
    
            // Crea una consulta y un statement SQL para buscar el registro
            $queryCheck = "SELECT * FROM " . TB_COMPRA_DETALLE . " WHERE " . COMPRA_DETALLE_ID . " = ? AND " . COMPRA_DETALLE_ESTADO . " != false";
            $stmt = mysqli_prepare($conn, $queryCheck);
    
            // Asignar los parámetros y ejecutar la consulta
            mysqli_stmt_bind_param($stmt, "i", $compraDetalleID);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
    
            // Verifica si existe algún registro con los criterios dados
            if (mysqli_num_rows($result) > 0) {
                return ["success" => true, "exists" => true];
            }
    
            return ["success" => true, "exists" => false];
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
            // Cierra la conexión y el statement
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }
    public function deleteCompraDetalle($compraDetalleID) {
        try {
            // Verifica que el ID del detalle de compra no esté vacío y sea numérico
            if (empty($compraDetalleID) || !is_numeric($compraDetalleID) || $compraDetalleID <= 0) {
                throw new Exception("El ID no puede estar vacío o ser menor a 0.");
            }
    
            // Verificar si existe el ID y que el Estado no sea false
            $check = $this->compraDetalleExiste($compraDetalleID);
            if (!$check["success"]) {
                return $check; // Error al verificar la existencia
            }
            if (!$check["exists"]) {
                throw new Exception("No se encontró un detalle de compra con el ID [" . $compraDetalleID . "]");
            }
    
            // Establece una conexión con la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];
    
            // Crea una consulta y un statement SQL para eliminar el registro
            $queryDelete = "UPDATE " . TB_COMPRA_DETALLE . " SET " . COMPRA_DETALLE_ESTADO . " = false WHERE " . COMPRA_DETALLE_ID . " = ?";
            $stmt = mysqli_prepare($conn, $queryDelete);
            mysqli_stmt_bind_param($stmt, 'i', $compraDetalleID);
    
            // Ejecuta la consulta de eliminación
            $result = mysqli_stmt_execute($stmt);
    
            // Devuelve el resultado de la consulta
            return ["success" => true, "message" => "Detalle de compra eliminado exitosamente"];
        } catch (Exception $e) {
            // Devolver mensaje amigable para el usuario
            return ["success" => false, "message" => $e->getMessage()];
        } finally {
            // Cierra la conexión y el statement si están definidos
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }
    public function getCompraDetalleByID($compraDetalleID) {
        try {
            $check = $this->compraDetalleExiste($compraDetalleID);
            if (!$check['success']) {
                return $check;
            }
            if (!$check['exists']) {
                Utils::writeLog("El detalle de compra con 'ID [$compraDetalleID]' no existe en la base de datos.", DATA_LOG_FILE);
                throw new Exception("No existe ningún detalle de compra en la base de datos que coincida con la información proporcionada.");
            }
    
            // Establece una conexión con la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];
    
            // Obtenemos la información del detalle de compra
            $querySelect = "SELECT * FROM " . TB_COMPRA_DETALLE . " WHERE " . COMPRA_DETALLE_ID . " = ? AND " . COMPRA_DETALLE_ESTADO . " != false";
            $stmt = mysqli_prepare($conn, $querySelect);
    
            // Asignar los parámetros y ejecutar la consulta
            mysqli_stmt_bind_param($stmt, 'i', $compraDetalleID);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
    
            // Verifica si existe algún registro con los criterios dados
            $compraDetalle = null;
            if ($row = mysqli_fetch_assoc($result)) {
                $compraDetalle = new CompraDetalle(
                    $row[COMPRA_DETALLE_ID],
                    $row[COMPRA_DETALLE_COMPRA_ID],
                    $row[COMPRA_DETALLE_LOTE_ID],
                    $row[COMPRA_DETALLE_PRODUCTO_ID],
                    $row[COMPRA_DETALLE_PRECIO_PRODUCTO],
                    $row[COMPRA_DETALLE_CANTIDAD],
                    $row[COMPRA_DETALLE_FECHA_CREACION],
                    $row[COMPRA_DETALLE_FECHA_MODIFICACION],
                    $row[COMPRA_DETALLE_ESTADO]
                );
            }
            
            return ["success" => true, "compraDetalle" => $compraDetalle];
        } catch (Exception $e) {
            // Manejo del error dentro del bloque catch
            $userMessage = $this->handleMysqlError(
                $e->getCode(), 
                $e->getMessage(),
                'Error al obtener el detalle de compra desde la base de datos'
            );
            
            // Devolver mensaje amigable para el usuario
            return ["success" => false, "message" => $userMessage];
        } finally {
            // Cerrar la conexión y el statement
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }
                    
}
?>
