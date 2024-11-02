<?php

    require_once dirname(__DIR__, 1) . '/data/data.php';
    require_once dirname(__DIR__, 1) . '/domain/VentaDetalle.php';
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';
    require_once dirname(__DIR__, 1) . '/utils/Variables.php';

    class VentaDetalleData extends Data {
        
        private $className;

        public function __construct() {
            parent::__construct();
            $this->className = get_class($this);
        }

        /**
         * Verifica la existencia de un detalle de venta en la base de datos.
         *
         * @param int|null $ventaDetalleID El ID del detalle de venta (opcional).
         * @param int|null $ventaID El ID de la venta (opcional).
         * @param int|null $productoID El ID del producto (opcional).
         * @param bool $update Indica si se está realizando una operación de actualización (opcional).
         * @param bool $insert Indica si se está realizando una operación de inserción (opcional).
         * @return array Un arreglo asociativo con el resultado de la verificación:
         *               - "success" (bool): Indica si la operación fue exitosa.
         *               - "exists" (bool): Indica si el detalle de venta existe en la base de datos.
         *               - "inactive" (bool, opcional): Indica si el detalle de venta está inactivo (solo si existe).
         *               - "ventaDetalleID" (int, opcional): El ID del detalle de venta (solo si existe).
         *               - "message" (string, opcional): Mensaje de error o información adicional.
         * @throws Exception Si ocurre un error durante la verificación.
         */
        private function ventaDetalleExiste($ventaDetalleID = null, $ventaID = null, $productoID = null, $update = false, $insert = false) {
            try {
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
        
                $queryCheck = "SELECT " . VENTA_DETALLE_ID . ", " . VENTA_DETALLE_ESTADO . " FROM " . TB_VENTA_DETALLE . " WHERE (";
                $params = [];
                $types = "";

                if ($ventaDetalleID && (!$update && !$insert)) {
                    $queryCheck .= VENTA_DETALLE_ID . " = ?";
                    $params = [$ventaDetalleID];
                    $types .= 'i';
                } 
                else if ($insert && ($ventaID && $productoID)) {
                    $queryCheck .= "(" . VENTA_ID . " = ? AND " . PRODUCTO_ID . " = ?)";
                    $params = [$ventaID, $productoID];
                    $types .= 'ii';
                } 
                else if ($update && ($ventaID && $productoID && $ventaDetalleID)) {
                    $queryCheck .= "(" . VENTA_ID . " = ? AND " . PRODUCTO_ID . " = ? AND " . VENTA_DETALLE_ID . " != ?)";
                    $params = [$ventaID, $productoID, $ventaDetalleID];
                    $types .= 'iii';
                } 
                else {
                    $missingParamsLog = "Faltan parámetros para verificar la existencia del detalle de venta:";
                    if (!$ventaDetalleID) $missingParamsLog .= " ventaDetalleID [" . ($ventaDetalleID ?? 'null') . "]";
                    if (!$ventaID) $missingParamsLog .= " ventaID [" . ($ventaID ?? 'null') . "]";
                    if (!$productoID) $missingParamsLog .= " productoID [" . ($productoID ?? 'null') . "]";
                    Utils::writeLog($missingParamsLog, DATA_LOG_FILE, WARN_MESSAGE, $this->className, __LINE__);
                    throw new Exception("Faltan parámetros para verificar la existencia del detalle de venta en la base de datos.");
                }

                $queryCheck .= ")";
                
                $stmt = mysqli_prepare($conn, $queryCheck);
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if ($row = mysqli_fetch_assoc($result)) {
                    $isInactive = $row[VENTA_DETALLE_ESTADO] == 0;
                    return ["success" => true, "exists" => true, "inactive" => $isInactive, "ventaDetalleID" => $row[VENTA_DETALLE_ID]];
                }

                $messageParams = [];
                if ($ventaDetalleID) { $messageParams[] = "'ID [$ventaDetalleID]'"; }
                if ($ventaID) { $messageParams[] = "'Venta ID [$ventaID]'"; }
                if ($productoID) { $messageParams[] = "'Producto ID [$productoID]'"; }
                $params = implode(', ', $messageParams);

                $message = "No se encontró ningún detalle de venta ($params) en la base de datos.";
                return ["success" => true, "exists" => false, "message" => $message];
            } catch (Exception $e) {
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al verificar la existencia del detalle de venta en la base de datos',
                    $this->className
                );
        
                return ["success" => false, "message" => $userMessage];
            } finally {
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function insertVentaDetalle($ventaDetalle, $conn = null) {
            $createdConnection = false;
            $stmt = null;
        
            try {
                // Establece una conexión con la base de datos
                if ($conn === null) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConnection = true;
        
                    // Inicia una transacción
                    mysqli_begin_transaction($conn);
                }
        
                // Obtener los valores de las propiedades del objeto VentaDetalle
                $ventaDetalleVenta = $ventaDetalle->getVentaDetalleVenta();
                $ventaDetallePrecio = $ventaDetalle->getVentaDetallePrecio();
                $ventaDetalleCantidad = $ventaDetalle->getVentaDetalleCantidad();
                $ventaDetalleEstado = $ventaDetalle->getVentaDetalleEstado();
        
                // Verifica si el detalle de venta ya existe
                $check = $this->ventaDetalleExiste($ventaDetalleVenta, $ventaDetallePrecio);
                if (!$check["success"]) { throw new Exception($check["message"]); }
        
                if ($check["exists"]) {
                    $message = "El detalle de venta para 'Venta ID [$ventaDetalleVenta]' y 'Precio [$ventaDetallePrecio]' ya existe.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    return ["success" => false, "message" => $message];
                }
        
                // Obtenemos el último ID de la tabla tbventadetalle
                $queryGetLastId = "SELECT MAX(" . VENTA_DETALLE_ID . ") FROM " . TB_VENTA_DETALLE;
                $idCont = mysqli_query($conn, $queryGetLastId);
                $nextId = 1;
        
                // Calcula el siguiente ID para la nueva entrada
                if ($row = mysqli_fetch_row($idCont)) {
                    $nextId = (int) trim($row[0]) + 1;
                }
        
                // Crea una consulta y un statement SQL para insertar el nuevo registro
                $queryInsert = 
                    "INSERT INTO " . TB_VENTA_DETALLE . " ("
                        . VENTA_DETALLE_ID . ", "
                        . VENTA_DETALLE_VENTA . ", "
                        . VENTA_DETALLE_PRECIO . ", "
                        . VENTA_DETALLE_CANTIDAD . ", "
                        . VENTA_DETALLE_ESTADO 
                    . ") VALUES (?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $queryInsert);
        
                // Asigna los valores a cada '?' y ejecuta la consulta
                mysqli_stmt_bind_param(
                    $stmt,
                    'iiddi', // i: entero, d: decimal
                    $nextId,
                    $ventaDetalleVenta,
                    $ventaDetallePrecio,
                    $ventaDetalleCantidad,
                    $ventaDetalleEstado
                );
                $result = mysqli_stmt_execute($stmt);
        
                // Confirmar la transacción
                if ($createdConnection) { mysqli_commit($conn); }
        
                return ["success" => true, "message" => "Detalle de venta insertado exitosamente", "id" => $nextId];
            } catch (Exception $e) {
                if (isset($conn) && $createdConnection) { mysqli_rollback($conn); }
        
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al insertar el detalle de venta en la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn) && $createdConnection) { mysqli_close($conn); }
            }
        }
        public function updateVentaDetalle($ventaDetalle, $conn = null) {
            $createdConnection = false;
            $stmt = null;
        
            try {
                // Obtener los valores de las propiedades del objeto
                $ventaDetalleID = $ventaDetalle->getVentaDetalleID();
                $ventaDetalleVenta = $ventaDetalle->getVentaDetalleVenta();
                $ventaDetallePrecio = $ventaDetalle->getVentaDetallePrecio();
                $ventaDetalleCantidad = $ventaDetalle->getVentaDetalleCantidad();
                $ventaDetalleEstado = $ventaDetalle->getVentaDetalleEstado();
        
                // Verifica si el detalle de venta ya existe en la base de datos
                $check = $this->ventaDetalleExiste($ventaDetalleID);
                if (!$check["success"]) { throw new Exception($check["message"]); }
        
                // En caso de no existir
                if (!$check["exists"]) {
                    $message = "El detalle de venta con 'ID [$ventaDetalleID]' no existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    return ["success" => false, "message" => "El detalle de venta seleccionado no existe en la base de datos."];
                }
        
                // Establece una conexión con la base de datos
                if ($conn === null) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConnection = true;
        
                    // Inicia una transacción
                    mysqli_begin_transaction($conn);
                }
        
                // Crea una consulta y un statement SQL para actualizar el registro
                $queryUpdate = 
                    "UPDATE " . TB_VENTA_DETALLE . 
                    " SET " . 
                        VENTA_DETALLE_VENTA . " = ?, " .
                        VENTA_DETALLE_PRECIO . " = ?, " .
                        VENTA_DETALLE_CANTIDAD . " = ?, " .
                        VENTA_DETALLE_ESTADO . " = ? " .
                    "WHERE " . VENTA_DETALLE_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryUpdate);
        
                // Asigna los valores a cada '?' de la consulta
                mysqli_stmt_bind_param(
                    $stmt,
                    'idiii', // i: entero, d: decimal
                    $ventaDetalleVenta,
                    $ventaDetallePrecio,
                    $ventaDetalleCantidad,
                    $ventaDetalleEstado,
                    $ventaDetalleID
                );
        
                // Ejecuta la consulta de actualización
                $result = mysqli_stmt_execute($stmt);
        
                // Confirmar la transacción
                if ($createdConnection) { mysqli_commit($conn); }
        
                return ["success" => true, "message" => "Detalle de venta actualizado exitosamente"];
            } catch (Exception $e) {
                if (isset($conn) && $createdConnection) { mysqli_rollback($conn); }
        
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(),
                    $e->getMessage(),
                    'Error al actualizar el detalle de venta en la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn) && $createdConnection) { mysqli_close($conn); }
            }
        }

        public function deleteVentaDetalle($ventaDetalleID, $conn = null) {
            $createdConnection = false;
            $stmt = null;
        
            try {
                // Verifica si el detalle de la venta existe en la base de datos
                $check = $this->ventaDetalleExiste($ventaDetalleID);
                if (!$check["success"]) { throw new Exception($check["message"]); }
        
                // En caso de no existir
                if (!$check["exists"]) {
                    $message = "El detalle de venta con 'ID [$ventaDetalleID]' no existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    return ["success" => true, "message" => "El detalle de venta seleccionado no existe en la base de datos."];
                }
        
                // Obtiene la información del detalle de venta
                $ventaDetalle = $this->getVentaDetalleByID($ventaDetalleID);
                if (!$ventaDetalle["success"]) { throw new Exception($ventaDetalle["message"]); }
                $ventaDetalle = $ventaDetalle["ventaDetalle"];
        
                // Establece una conexión con la base de datos
                if ($conn === null) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConnection = true;
        
                    // Inicia una transacción
                    mysqli_begin_transaction($conn);
                }
        
                // Crea una consulta y un statement SQL para eliminar el registro (borrado lógico)
                $queryDelete = "UPDATE " . TB_VENTA_DETALLE . " SET " . VENTA_DETALLE_ESTADO . " = FALSE WHERE " . VENTA_DETALLE_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryDelete);
                mysqli_stmt_bind_param($stmt, 'i', $ventaDetalleID);
        
                // Ejecuta la consulta de eliminación
                $result = mysqli_stmt_execute($stmt);
        
                // Confirmar la transacción
                if ($createdConnection) { mysqli_commit($conn); }
        
                // Devuelve el resultado de la operación
                return ["success" => true, "message" => "Detalle de venta eliminado exitosamente."];
            } catch (Exception $e) {
                // Revertir la transacción en caso de error
                if (isset($conn) && $createdConnection) { mysqli_rollback($conn); }
        
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al eliminar el detalle de venta de la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn) && $createdConnection) { mysqli_close($conn); }
            }
        }
        public function getAllTBVentaDetalle($onlyActive = false, $deleted = false) {
            $conn = null;
            
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
        
                // Construir la consulta SQL para obtener todos los detalles de venta activos
                $querySelect = "SELECT * FROM " . TB_VENTA_DETALLE;
                if ($onlyActive) { 
                    $querySelect .= " WHERE " . VENTA_DETALLE_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE'); 
                }
                $result = mysqli_query($conn, $querySelect);
        
                // Crear la lista con los datos obtenidos
                $ventaDetalles = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    // Obtiene la información de la venta asociada
                    $ventaData = new VentaData();
                    $venta = $ventaData->getVentaByID($row[VENTA_ID], false);
                    if (!$venta["success"]) { throw new Exception($venta["message"]); }
        
                    // Obtiene el producto asociado al detalle de venta
                    $productoData = new ProductoData();
                    $producto = $productoData->getProductoByID($row[PRODUCTO_ID], false);
                    if (!$producto["success"]) { throw new Exception($producto["message"]); }
        
                    // Crea una instancia de VentaDetalle
                    $ventaDetalle = new VentaDetalle(
                        $row[VENTA_DETALLE_ID],
                        $venta["venta"],  // Aquí se asume que el objeto `venta` tiene un método adecuado
                        $producto["producto"],  // Aquí se asume que el objeto `producto` tiene un método adecuado
                        $row[VENTA_DETALLE_CANTIDAD],
                        $row[VENTA_DETALLE_PRECIO],
                        $row[VENTA_DETALLE_ESTADO]
                    );
                    $ventaDetalles[] = $ventaDetalle;
                }
        
                // Devolver la lista de detalles de venta
                return ["success" => true, "ventaDetalles" => $ventaDetalles];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener la lista de detalles de venta desde la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cerramos la conexión
                if (isset($conn)) { mysqli_close($conn); }
            }
        }
        public function getPaginatedVentaDetalles($search, $page, $size, $sort = null, $onlyActive = false, $deleted = false) {
            $conn = null; 
            $stmt = null;
            
            try {
                // Calcular el offset y el total de páginas
                $offset = ($page - 1) * $size;
        
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
        
                // Consultar el total de registros
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_VENTA_DETALLE;
                if ($onlyActive) { $queryTotalCount .= " WHERE " . VENTA_DETALLE_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE'); }
        
                // Obtener el total de registros y calcular el total de páginas
                $totalResult = mysqli_query($conn, $queryTotalCount);
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = (int) $totalRow['total'];
                $totalPages = ceil($totalRecords / $size);
        
                // Construir la consulta SQL para paginación
                $querySelect = "
                    SELECT 
                        VD.*, P." . PRODUCTO_NOMBRE . ", C." . CODIGO_BARRAS_NUMERO . "
                    FROM " . 
                        TB_VENTA_DETALLE . " VD 
                    INNER JOIN " . 
                        TB_PRODUCTO . " P ON VD." . VENTA_DETALLE_PRODUCTO_ID . " = P." . PRODUCTO_ID . "
                    INNER JOIN " . 
                        TB_CODIGO_BARRAS . " C ON P." . CODIGO_BARRAS_ID . " = C." . CODIGO_BARRAS_ID
                ;
        
                // Agregar filtro de búsqueda a la consulta
                $params = [];
                $types = "";
                if ($search) {
                    $querySelect .= " WHERE (P." . PRODUCTO_NOMBRE . " LIKE ? OR C." . CODIGO_BARRAS_NUMERO . " LIKE ?)";
                    $searchParam = "%" . $search . "%";
                    $params = [$searchParam, $searchParam];
                    $types .= "ss";
                }
        
                // Agregar filtro de estado a la consulta
                if ($onlyActive) { 
                    $querySelect .= $search ? " AND " : " WHERE ";
                    $querySelect .= VD."." . VENTA_DETALLE_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE"); 
                }
        
                // Agregar ordenamiento a la consulta
                if ($sort) { 
                    $querySelect .= " ORDER BY VD." . $sort . " ";
                } else { 
                    $querySelect .= " ORDER BY VD." . VENTA_DETALLE_ID . " DESC"; 
                }
        
                // Agregar límites a la consulta
                $querySelect .= " LIMIT ? OFFSET ?";
                $params = array_merge($params, [$size, $offset]);
                $types .= "ii";
        
                // Preparar la consulta y vincular los parámetros
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);
        
                // Ejecutar la consulta y obtener los resultados
                $result = mysqli_stmt_get_result($stmt);
        
                $ventaDetalles = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    // Crear objeto VentaDetalle con los datos obtenidos
                    $ventaDetalle = new VentaDetalle(
                        $row[VENTA_DETALLE_ID],
                        $row[VENTA_DETALLE_PRODUCTO_ID],
                        $row[VENTA_DETALLE_CANTIDAD],
                        $row[VENTA_DETALLE_PRECIO],
                        $row[VENTA_DETALLE_TOTAL],
                        $row[VENTA_DETALLE_FECHA],
                        $row[VENTA_DETALLE_ESTADO],
                        $row[PRODUCTO_NOMBRE],
                        $row[CODIGO_BARRAS_NUMERO]
                    );
                    $ventaDetalles[] = $ventaDetalle;
                }
        
                return [
                    "success" => true,
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "ventaDetalles" => $ventaDetalles
                ];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener la lista de detalles de venta desde la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cerrar la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }
        public function getVentaDetalleByID($ventaDetalleID, $onlyActive = true, $deleted = false) {
            $conn = null; 
            $stmt = null;
        
            try {
                // Verificar si el detalle de venta existe en la base de datos
                $check = $this->ventaDetalleExiste($ventaDetalleID);
                if (!$check["success"]) { throw new Exception($check["message"]); }
        
                // En caso de no existir
                if (!$check["exists"]) {
                    $message = "El detalle de venta con 'ID [$ventaDetalleID]' no existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    return ["success" => true, "message" => "El detalle de venta seleccionado no existe en la base de datos."];
                }
        
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
        
                // Obtenemos la información del detalle de venta
                $querySelect = "
                    SELECT 
                        VD.*
                    FROM " . 
                        TB_VENTA_DETALLE . " VD 
                    WHERE 
                        VD." . VENTA_DETALLE_ID . " = ?" . ($onlyActive ? " AND 
                        VD." . VENTA_DETALLE_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE') : '');
        
                $stmt = mysqli_prepare($conn, $querySelect);
        
                // Asigna los parámetros y ejecuta la consulta
                mysqli_stmt_bind_param($stmt, 'i', $ventaDetalleID);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
        
                // Verifica si existe algún registro con los criterios dados
                if ($row = mysqli_fetch_assoc($result)) {
                    // Crear un objeto de detalle de venta
                    $ventaDetalle = new VentaDetalle(
                        $row[VENTA_DETALLE_ID],
                        $row[VENTA_DETALLE_VENTA], // ID de la venta a la que pertenece este detalle
                        $row[VENTA_DETALLE_PRECIO], // Precio del producto
                        $row[VENTA_DETALLE_CANTIDAD], // Cantidad vendida
                        $row[VENTA_DETALLE_ESTADO] // Estado del detalle de venta
                    );
        
                    return ["success" => true, "ventaDetalle" => $ventaDetalle];
                }
        
                // En caso de que no se haya encontrado el detalle de venta
                $message = "No se encontró el detalle de venta con 'ID [$ventaDetalleID]' en la base de datos.";
                Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                return ["success" => false, "message" => "No se encontró el detalle de venta seleccionado en la base de datos."];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener el detalle de venta de la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }
                                
    }

?>
