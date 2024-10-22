<?php

    require_once dirname(__DIR__, 1) . '/data/data.php';
    require_once __DIR__ . '/../domain/CompraDetalle.php';
    require_once __DIR__ . '/../utils/Variables.php';

    class CompraDetalleData extends Data {
  
        // Nombre de la clase
        private $className;

        /**
         * Inicializa una nueva instancia de la clase ProductoData.
         */
		public function __construct() {
			parent::__construct();
            $this->className = get_class($this);
        }

        public function insertCompraDetalle($compraDetalle, $conn = null) {
            $createdConnection = false;
            $stmt = null;
        
            try {
                // Establece una conexión con la base de datos
                if ($conn === null) {
                    $result = $this->getConnection();
                    if (!$result["success"]) {
                        throw new Exception($result["message"]);
                    }
                    $conn = $result["connection"];
                    $createdConnection = true;
        
                    // Inicia una transacción
                    mysqli_begin_transaction($conn);
                }
        
                // Obtener el último ID de la tabla tbcompradetalle
                $queryGetLastId = "SELECT MAX(" . COMPRA_DETALLE_ID . ") FROM " . TB_COMPRA_DETALLE;
                $idCont = mysqli_query($conn, $queryGetLastId);
                $nextId = 1;
        
                // Calcula el siguiente ID para la nueva entrada
                if ($row = mysqli_fetch_row($idCont)) {
                    $nextId = (int) trim($row[0]) + 1;
                }
        
                // Obtener valores del objeto CompraDetalle
                $compraDetalleCompraID = $compraDetalle->getCompraDetalleCompra();
                $compraDetalleProductoID = $compraDetalle->getCompraDetalleProducto();
                $compraDetalleFechaCreacion = $compraDetalle->getCompraDetalleFechaCreacion();
                $compraDetalleFechaModificacion = $compraDetalle->getCompraDetalleFechaModificacion();
                $compraDetalleEstado = $compraDetalle->getCompraDetalleEstado();
        
                // Crear consulta de inserción
                $queryInsert = "INSERT INTO " . TB_COMPRA_DETALLE . " ("
                    . COMPRA_DETALLE_ID . ", "
                    . COMPRA_DETALLE_COMPRA_ID . ", "
                    . COMPRA_DETALLE_PRODUCTO_ID . ", "
                    . COMPRA_DETALLE_FECHA_CREACION . ", "
                    . COMPRA_DETALLE_FECHA_MODIFICACION . ", "
                    . COMPRA_DETALLE_ESTADO
                    . ") VALUES (?, ?, ?, ?, ?, ?)";
                
                $stmt = mysqli_prepare($conn, $queryInsert);
        
                // Asigna los valores a cada '?' y ejecuta la consulta
                mysqli_stmt_bind_param(
                    $stmt,
                    'iisssi',
                    $nextId,
                    $compraDetalleCompraID,
                    $compraDetalleProductoID,
                    $compraDetalleFechaCreacion,
                    $compraDetalleFechaModificacion,
                    $compraDetalleEstado
                );
        
                // Ejecuta la consulta
                $result = mysqli_stmt_execute($stmt);
                
                // Confirmar la transacción
                if ($createdConnection) {
                    mysqli_commit($conn);
                }
        
                return ["success" => true, "message" => "Detalle de compra insertado exitosamente", "id" => $nextId];
            } catch (Exception $e) {
                if (isset($conn) && $createdConnection) {
                    mysqli_rollback($conn);
                }
        
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al insertar el detalle de compra',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) {
                    mysqli_stmt_close($stmt);
                }
                if (isset($conn) && $createdConnection) {
                    mysqli_close($conn);
                }
            }
        }
        
    public function updateCompraDetalle($compraDetalle) {
        try {
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];
    
            // Crear la consulta de actualización
            $queryUpdate = "UPDATE " . TB_COMPRA_DETALLE . " 
                            SET " . 
                            COMPRA_DETALLE_COMPRA_ID . " = ?, " .
                            COMPRA_DETALLE_PRODUCTO_ID . " = ?, " .
                            COMPRA_DETALLE_FECHA_CREACION . " = ?, " .
                            COMPRA_DETALLE_FECHA_MODIFICACION . " = ?, " .
                            COMPRA_DETALLE_ESTADO . " = ? 
                            WHERE " . COMPRA_DETALLE_ID . " = ?";
    
            // Preparar la declaración
            $stmt = mysqli_prepare($conn, $queryUpdate);
    
            // Obtener valores del objeto CompraDetalle
            $compraDetalleID = $compraDetalle->getCompraDetalleID();
            $compraDetalleCompra = $compraDetalle->getCompraDetalleCompra();
            $compraDetalleProducto = $compraDetalle->getCompraDetalleProducto();
            $compraDetalleFechaCreacion = $compraDetalle->getCompraDetalleFechaCreacion();
            $compraDetalleFechaModificacion = $compraDetalle->getCompraDetalleFechaModificacion();
            $compraDetalleEstado = $compraDetalle->getCompraDetalleEstado();
    
            // Vincular los parámetros
            mysqli_stmt_bind_param(
                $stmt,
                'iissii', // Tipos de datos: i = entero, s = cadena
                $compraDetalleCompra,
                $compraDetalleProducto,
                $compraDetalleFechaCreacion,
                $compraDetalleFechaModificacion,
                $compraDetalleEstado,
                $compraDetalleID
            );
    
            // Ejecutar la consulta
            $result = mysqli_stmt_execute($stmt);
    
            if (!$result) {
                throw new Exception("Error al ejecutar la actualización: " . mysqli_error($conn));
            }
    
            return ["success" => true, "message" => "Detalle de compra actualizado exitosamente"];
        } catch (Exception $e) {
            // Manejo del error
            $userMessage = $this->handleMysqlError($e->getCode(), $e->getMessage(), 'Error al actualizar el detalle de compra');
            return ["success" => false, "message" => $userMessage];
        } finally {
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }

    public function getAllCompraDetalle($onlyActive = false) {
        $conn = null;
    
        try {
            // Establece una conexión con la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) { throw new Exception($result["message"]); }
            $conn = $result["connection"];
    
            // Construir la consulta SQL para obtener todos los detalles de compra
            $querySelect = "
                SELECT 
                    c." . COMPRA_DETALLE_ID . ", 
                    c." . COMPRA_DETALLE_COMPRA_ID . ", 
                    c." . COMPRA_DETALLE_PRODUCTO_ID . ", 
                    c." . COMPRA_DETALLE_FECHA_CREACION . ", 
                    c." . COMPRA_DETALLE_FECHA_MODIFICACION . ", 
                    c." . COMPRA_DETALLE_ESTADO . " 
                FROM " . TB_COMPRA_DETALLE . " c";
            
            if ($onlyActive) {
                $querySelect .= " WHERE c." . COMPRA_DETALLE_ESTADO . " != false";
            }
    
            $result = mysqli_query($conn, $querySelect);
    
            // Crear la lista con los datos obtenidos
            $listaCompraDetalles = [];
            while ($row = mysqli_fetch_assoc($result)) {
                // Obtiene el nombre del producto asociado al detalle de compra
                $productoData = new ProductoData();
                $producto = $productoData->getProductoByID($row[COMPRA_DETALLE_PRODUCTO_ID]);
                if (!$producto["success"]) { throw new Exception($producto["message"]); }
    
                $currentCompraDetalle = new CompraDetalle(
                    $row[COMPRA_DETALLE_ID],
                    $row[COMPRA_DETALLE_COMPRA_ID],
                    $producto["productoNombre"], // Cambia a la propiedad que desees obtener
                    $row[COMPRA_DETALLE_FECHA_CREACION],
                    $row[COMPRA_DETALLE_FECHA_MODIFICACION],
                    $row[COMPRA_DETALLE_ESTADO]
                );
    
                array_push($listaCompraDetalles, $currentCompraDetalle);
            }
    
            // Devolver la lista de detalles de compra
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
    
    public function getPaginatedCompraDetalles($search, $page, $size, $sort = null) {
        $conn = null; 
        $stmt = null;
    
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
            $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_COMPRA_DETALLE . " c 
                                WHERE c." . COMPRA_DETALLE_ESTADO . " != false";
    
            // Agregar filtro de búsqueda si se proporciona
            if ($search) {
                $queryTotalCount .= " AND (c." . COMPRA_DETALLE_COMPRA_ID . " LIKE ? OR c." . COMPRA_DETALLE_PRODUCTO_ID . " LIKE ?)";
            }
    
            // Obtener el total de registros
            $totalParams = [];
            $totalTypes = "";
            if ($search) {
                $searchParam = "%" . $search . "%";
                $totalParams = [$searchParam, $searchParam];
                $totalTypes .= "ss";
            }
            $totalResult = mysqli_prepare($conn, $queryTotalCount);
            if ($search) {
                mysqli_stmt_bind_param($totalResult, $totalTypes, ...$totalParams);
            }
            mysqli_stmt_execute($totalResult);
            $totalRow = mysqli_stmt_get_result($totalResult);
            $totalRecords = (int) mysqli_fetch_assoc($totalRow)['total'];
            $totalPages = ceil($totalRecords / $size);
    
            // Construir la consulta SQL para paginación
            $querySelect = "
            SELECT 
                c." . COMPRA_DETALLE_ID . ", 
                c." . COMPRA_DETALLE_COMPRA_ID . " AS compraDetalleCompra,
                c." . COMPRA_DETALLE_PRODUCTO_ID . " AS compraDetalleProducto,
                c." . COMPRA_DETALLE_FECHA_CREACION . ", 
                c." . COMPRA_DETALLE_FECHA_MODIFICACION . ", 
                c." . COMPRA_DETALLE_ESTADO . "
            FROM " . TB_COMPRA_DETALLE . " c
            WHERE c." . COMPRA_DETALLE_ESTADO . " != false";
    
            // Agregar filtro de búsqueda a la consulta
            if ($search) {
                $querySelect .= " AND (c." . COMPRA_DETALLE_COMPRA_ID . " LIKE ? OR c." . COMPRA_DETALLE_PRODUCTO_ID . " LIKE ?)";
            }
    
            // Agregar ordenamiento si se proporciona
            if ($sort) {
                $querySelect .= " ORDER BY " . $sort . " ";
            }
    
            $querySelect .= " LIMIT ? OFFSET ?";
    
            // Preparar la consulta y vincular los parámetros
            $stmt = mysqli_prepare($conn, $querySelect);
            $params = [];
            if ($search) {
                $params = array_merge($params, [$searchParam, $searchParam]);
            }
            $params = array_merge($params, [$size, $offset]);
            $types = $search ? "ssii" : "iii"; // 'ssii' if search is provided, 'iii' otherwise
            mysqli_stmt_bind_param($stmt, $types, ...$params);
    
            // Ejecutar la consulta
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
    
            // Crear la lista de detalles de compra
            $listaCompraDetalles = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $listaCompraDetalles[] = [
                    'ID' => $row[COMPRA_DETALLE_ID],
                    'CompraDetalleCompra' => $row["compraDetalleCompra"],
                    'CompraDetalleProducto' => $row["compraDetalleProducto"],
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
    
    private function compraDetalleExiste($compraDetalleID = null) {
        try {
            // Establecer conexión a la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];
    
            // Inicializa la consulta base
            $queryCheck = "SELECT " . COMPRA_DETALLE_ID . ", " . COMPRA_DETALLE_ESTADO . " FROM " . TB_COMPRA_DETALLE . " WHERE ";
            $params = [];
            $types = "";
    
            // Verifica si existe un detalle de compra con el ID ingresado
            if ($compraDetalleID) {
                // Consultar: Verificar existencia por ID
                $queryCheck .= COMPRA_DETALLE_ID . " = ?";
                $params = [$compraDetalleID];
                $types .= 'i';
            } else {
                // Registro no encontrado, lanzar excepción
                Utils::writeLog("Faltan parámetros para verificar la existencia del detalle de compra.", DATA_LOG_FILE, WARN_MESSAGE, get_class($this));
                throw new Exception("Faltan parámetros para verificar la existencia del detalle de compra en la base de datos.");
            }
    
            // Asignar los parámetros y ejecutar la consulta
            $stmt = mysqli_prepare($conn, $queryCheck);
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
    
            // Verificar si existe un detalle de compra con el ID ingresado
            if ($row = mysqli_fetch_assoc($result)) {
                // Verificar si está inactivo
                $isInactive = $row[COMPRA_DETALLE_ESTADO] == 0;
                return ["success" => true, "exists" => true, "inactive" => $isInactive, "compraDetalleID" => $row[COMPRA_DETALLE_ID]];
            }
    
            // Retorna false si no se encontraron resultados
            $message = "No se encontró ningún detalle de compra con ID [$compraDetalleID] en la base de datos.";
            return ["success" => true, "exists" => false, "message" => $message];
        } catch (Exception $e) {
            // Manejo del error dentro del bloque catch
            $userMessage = $this->handleMysqlError(
                $e->getCode(), $e->getMessage(),
                'Ocurrió un error al verificar la existencia del detalle de compra en la base de datos'
            );
    
            // Devolver mensaje amigable para el usuario
            return ["success" => false, "message" => $userMessage];
        } finally {
            // Cerrar la conexión y el statement
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }
    
    
    public function deleteCompraDetalle($compraDetalleID, $conn = null) {
        $createdConnection = false;
        $stmt = null;
    
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
            if ($conn === null) {
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
                $createdConnection = true;
    
                // Inicia una transacción
                mysqli_begin_transaction($conn);
            }
    
            // Consulta para eliminar el registro (borrado lógico)
            $queryDelete = "UPDATE " . TB_COMPRA_DETALLE . " SET " . COMPRA_DETALLE_ESTADO . " = FALSE WHERE " . COMPRA_DETALLE_ID . " = ?";
            $stmt = mysqli_prepare($conn, $queryDelete);
            mysqli_stmt_bind_param($stmt, 'i', $compraDetalleID);
    
            // Ejecuta la consulta de eliminación
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error al eliminar el detalle de compra.");
            }
    
            // Confirmar la transacción
            if ($createdConnection) {
                mysqli_commit($conn);
            }
    
            return ["success" => true, "message" => "Detalle de compra eliminado exitosamente"];
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            if (isset($conn) && $createdConnection) {
                mysqli_rollback($conn);
            }
    
            // Manejo del error dentro del bloque catch
            $userMessage = $this->handleMysqlError(
                $e->getCode(), $e->getMessage(),
                'Error al eliminar el detalle de compra de la base de datos',
                get_class($this)
            );
    
            // Devolver mensaje amigable para el usuario
            return ["success" => false, "message" => $userMessage];
        } finally {
            // Cerrar la conexión y el statement
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn) && $createdConnection) { mysqli_close($conn); }
        }
    }
    
    
    public function getCompraDetalleByID($compraDetalleID, $onlyActive = true) {
        $conn = null; 
        $stmt = null;
    
        try {
            // Verificar existencia del detalle de compra
            $check = $this->compraDetalleExiste($compraDetalleID);
            if (!$check['success']) {
                return $check; // Error al verificar la existencia
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
                JOIN tbproducto p ON c." . COMPRA_DETALLE_PRODUCTO_ID . " = p.productoid 
                WHERE c." . COMPRA_DETALLE_ID . " = ?" . ($onlyActive ? " AND c." . COMPRA_DETALLE_ESTADO . " != FALSE" : "");
    
            $stmt = mysqli_prepare($conn, $querySelect);
            mysqli_stmt_bind_param($stmt, 'i', $compraDetalleID);
            mysqli_stmt_execute($stmt);
            $resultSet = mysqli_stmt_get_result($stmt);
    
            // Obtener el resultado
            if ($detalle = mysqli_fetch_assoc($resultSet)) {
                return [
                    "success" => true,
                    "data" => $detalle
                ];
            } else {
                // En caso de que no se haya encontrado el detalle
                $message = "No se encontró un detalle de compra con el ID [" . $compraDetalleID . "]";
                Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ["success" => false, "message" => $message];
            }
        } catch (Exception $e) {
            // Manejo del error dentro del bloque catch
            $userMessage = $this->handleMysqlError(
                $e->getCode(), $e->getMessage(),
                'Error al obtener el detalle de compra de la base de datos',
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
                    
}
?>