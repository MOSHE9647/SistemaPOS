<?php

require_once dirname(__DIR__, 1) . '/data/data.php';
require_once __DIR__ . '/../domain/CuentaPorPagar.php';
require_once __DIR__ . '/../utils/Variables.php';

class CuentaPorPagarData extends Data {

    // Constructor
    public function __construct() {
        parent::__construct();
    }
    public function cuentaPorPagarCompraDetalleIdExiste($cuentaPorPagarCompraDetalleID) {
        try {
            // Establece una conexión con la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];
    
            // Crea una consulta SQL para verificar si existe el `cuentaPorPagarCompraDetalleID`
            $queryCheck = "SELECT COUNT(*) FROM " . TB_CUENTA_POR_PAGAR . " WHERE " . CUENTA_POR_PAGAR_COMPRA_DETALLE_ID . " = ? AND " . CUENTA_POR_PAGAR_ESTADO . " != false";
            $stmt = mysqli_prepare($conn, $queryCheck);
    
            // Asigna el parámetro y ejecuta la consulta
            mysqli_stmt_bind_param($stmt, "i", $cuentaPorPagarCompraDetalleID);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
    
            // Verificar si hay resultados
            $count = mysqli_fetch_row($result)[0];
            return $count > 0;
    
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        } finally {
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }
    
    public function insertCuentaPorPagar($cuentaPorPagar) {
        try {
            // Establece una conexión con la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];
    
             // Verificar si el `cuentaPorPagarCompraDetalleID` ya existe
        if ($this->cuentaPorPagarCompraDetalleIdExiste($cuentaPorPagar->getCuentaPorPagarCompraDetalleID())) {
            return ["success" => false, "message" => "Ya existe una cuenta por pagar con el mismo Compra Detalle ID."];
        }
            
     // Obtiene el último ID de la tabla tblote
     $queryGetLastId = "SELECT MAX(" . CUENTA_POR_PAGAR_ID . ") FROM " . TB_CUENTA_POR_PAGAR;
     $idCont = mysqli_query($conn, $queryGetLastId);
     $nextId = 1;

     // Calcula el siguiente ID para la nueva entrada
     if ($row = mysqli_fetch_row($idCont)) {
         $nextId = (int) trim($row[0]) + 1;
     }
            // Crea una consulta y un statement SQL para insertar el registro
            $queryInsert = "INSERT INTO " . TB_CUENTA_POR_PAGAR . " (" 
                . CUENTA_POR_PAGAR_ID . ", " 
                . CUENTA_POR_PAGAR_COMPRA_DETALLE_ID . ", "                  
                . CUENTA_POR_PAGAR_FECHA_VENCIMIENTO . ", " 
                . CUENTA_POR_PAGAR_MONTO_TOTAL . ", " 
                . CUENTA_POR_PAGAR_MONTO_PAGADO . ", " 
                . CUENTA_POR_PAGAR_FECHA_PAGO . ", " 
                . CUENTA_POR_PAGAR_NOTAS . ", " 
                . CUENTA_POR_PAGAR_ESTADO_CUENTA . ", " 
                . CUENTA_POR_PAGAR_ESTADO 
                . ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $queryInsert);
    
            // Obtener los valores de las propiedades del objeto $cuentaPorPagar
            $cuentaPorPagarID = $cuentaPorPagar->getCuentaPorPagarID();
            $cuentaPorPagarCompraDetalleID = $cuentaPorPagar->getCuentaPorPagarCompraDetalleID();
            $cuentaPorPagarFechaVencimiento = $cuentaPorPagar->getCuentaPorPagarFechaVencimiento();
            $cuentaPorPagarMontoTotal = $cuentaPorPagar->getCuentaPorPagarMontoTotal();
            $cuentaPorPagarMontoPagado = $cuentaPorPagar->getCuentaPorPagarMontoPagado();
            $cuentaPorPagarFechaPago = $cuentaPorPagar->getCuentaPorPagarFechaPago();
            $cuentaPorPagarNotas = $cuentaPorPagar->getCuentaPorPagarNotas();
            $cuentaPorPagarEstadoCuenta = $cuentaPorPagar->getCuentaPorPagarEstadoCuenta();
            $cuentaPorPagarEstado = $cuentaPorPagar->getCuentaPorPagarEstado();
    // Asegurarse de que estos valores no sean nulos
    $cuentaPorPagarEstadoCuenta = !empty($cuentaPorPagar->getCuentaPorPagarEstadoCuenta()) ? $cuentaPorPagar->getCuentaPorPagarEstadoCuenta() : 'Pendiente';
    $cuentaPorPagarNotas = !empty($cuentaPorPagar->getCuentaPorPagarNotas()) ? $cuentaPorPagar->getCuentaPorPagarNotas() : 'Sin Notas';
    $cuentaPorPagarEstado = $cuentaPorPagar->getCuentaPorPagarEstado();

            // Asigna los valores a cada '?' de la consulta
            mysqli_stmt_bind_param(
                $stmt,
                'iisddssss', // i: Entero, d: Doble, s: Cadena
                $nextId,
                $cuentaPorPagarCompraDetalleID,
                $cuentaPorPagarFechaVencimiento,
                $cuentaPorPagarMontoTotal,
                $cuentaPorPagarMontoPagado,
                $cuentaPorPagarFechaPago,
                $cuentaPorPagarNotas,
                $cuentaPorPagarEstadoCuenta,
                $cuentaPorPagarEstado
            );
    
            // Ejecuta la consulta de inserción
            $result = mysqli_stmt_execute($stmt);
            return ["success" => true, "message" => "Cuenta por pagar insertada exitosamente"];
        } catch (Exception $e) {
            // Manejo del error dentro del bloque catch
            return ["success" => false, "message" => $e->getMessage()];
        } finally {
            // Cierra el statement y la conexión si están definidos
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }
    
    public function updateCuentaPorPagar($cuentaPorPagar) {
        try {
            // Establece una conexión con la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];
    
            // Crea una consulta y un statement SQL para actualizar el registro
            $queryUpdate = 
                "UPDATE " . TB_CUENTA_POR_PAGAR . 
                " SET " . 
                    CUENTA_POR_PAGAR_COMPRA_DETALLE_ID . " = ?, " .
                    CUENTA_POR_PAGAR_FECHA_VENCIMIENTO . " = ?, " .
                    CUENTA_POR_PAGAR_MONTO_TOTAL . " = ?, " .
                    CUENTA_POR_PAGAR_MONTO_PAGADO . " = ?, " .
                    CUENTA_POR_PAGAR_FECHA_PAGO . " = ?, " .
                    CUENTA_POR_PAGAR_NOTAS . " = ?, " .
                    CUENTA_POR_PAGAR_ESTADO_CUENTA . " = ?, " .
                    CUENTA_POR_PAGAR_ESTADO . " = ? " .
                "WHERE " . CUENTA_POR_PAGAR_ID . " = ?";
            $stmt = mysqli_prepare($conn, $queryUpdate);
    
            // Obtener los valores de las propiedades del objeto $cuentaPorPagar
            $cuentaPorPagarID = $cuentaPorPagar->getCuentaPorPagarID();
            $cuentaPorPagarCompraDetalleID = $cuentaPorPagar->getCuentaPorPagarCompraDetalleID();
            $cuentaPorPagarFechaVencimiento = $cuentaPorPagar->getCuentaPorPagarFechaVencimiento();
            $cuentaPorPagarMontoTotal = $cuentaPorPagar->getCuentaPorPagarMontoTotal();
            $cuentaPorPagarMontoPagado = $cuentaPorPagar->getCuentaPorPagarMontoPagado();
            $cuentaPorPagarFechaPago = $cuentaPorPagar->getCuentaPorPagarFechaPago();
            $cuentaPorPagarNotas = $cuentaPorPagar->getCuentaPorPagarNotas();
            $cuentaPorPagarEstadoCuenta = $cuentaPorPagar->getCuentaPorPagarEstadoCuenta();
            $cuentaPorPagarEstado = $cuentaPorPagar->getCuentaPorPagarEstado();
    
            // Asigna los valores a cada '?' de la consulta
            mysqli_stmt_bind_param(
                $stmt,
                'isddssssi', // i: Entero, s: Cadena, d: Decimal
                $cuentaPorPagarCompraDetalleID,
                $cuentaPorPagarFechaVencimiento,
                $cuentaPorPagarMontoTotal,
                $cuentaPorPagarMontoPagado,
                $cuentaPorPagarFechaPago,
                $cuentaPorPagarNotas,
                $cuentaPorPagarEstadoCuenta,
                $cuentaPorPagarEstado,
                $cuentaPorPagarID
            );
    
            // Ejecuta la consulta de actualización
            $result = mysqli_stmt_execute($stmt);
    
            // Devuelve el resultado de la consulta
            return ["success" => true, "message" => "Cuenta por pagar actualizada exitosamente"];
        } catch (Exception $e) {
            // Manejo del error dentro del bloque catch
            $userMessage = $this->handleMysqlError(
                $e->getCode(), 
                $e->getMessage(),
                'Error al actualizar la cuenta por pagar en la base de datos'
            );
    
            // Devolver mensaje amigable para el usuario
            return ["success" => false, "message" => $userMessage];
        } finally {
            // Cierra el statement y la conexión si están definidos
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }
    
    public function getAllCuentaPorPagar() {
        try {
            // Establece una conexión con la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];
    
            // Construir la consulta SQL para obtener todos los registros
            $querySelect = "
            SELECT 
                " . CUENTA_POR_PAGAR_ID . ", 
                " . CUENTA_POR_PAGAR_COMPRA_DETALLE_ID . ", 
                " . CUENTA_POR_PAGAR_FECHA_VENCIMIENTO . ", 
                " . CUENTA_POR_PAGAR_MONTO_TOTAL . ", 
                " . CUENTA_POR_PAGAR_MONTO_PAGADO . ", 
                " . CUENTA_POR_PAGAR_FECHA_PAGO . ", 
                " . CUENTA_POR_PAGAR_NOTAS . ", 
                " . CUENTA_POR_PAGAR_ESTADO_CUENTA . ", 
                " . CUENTA_POR_PAGAR_ESTADO . "
            FROM " . TB_CUENTA_POR_PAGAR . "
            WHERE " . CUENTA_POR_PAGAR_ESTADO . " != false
            ";
    
            $result = mysqli_query($conn, $querySelect);
    
            // Crear la lista con los datos obtenidos
            $listaCuentaPorPagar = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $currentCuentaPorPagar = new CuentaPorPagar(
                    $row[CUENTA_POR_PAGAR_ID],
                    $row[CUENTA_POR_PAGAR_COMPRA_DETALLE_ID],
                    $row[CUENTA_POR_PAGAR_FECHA_VENCIMIENTO],
                    $row[CUENTA_POR_PAGAR_MONTO_TOTAL],
                    $row[CUENTA_POR_PAGAR_MONTO_PAGADO],
                    $row[CUENTA_POR_PAGAR_FECHA_PAGO],
                    $row[CUENTA_POR_PAGAR_NOTAS],
                    $row[CUENTA_POR_PAGAR_ESTADO_CUENTA],
                    $row[CUENTA_POR_PAGAR_ESTADO]
                );
                array_push($listaCuentaPorPagar, $currentCuentaPorPagar);
            }
    
            return ["success" => true, "listaCuentaPorPagar" => $listaCuentaPorPagar];
        } catch (Exception $e) {
            // Manejo del error dentro del bloque catch
            $userMessage = $this->handleMysqlError(
                $e->getCode(), 
                $e->getMessage(),
                'Error al obtener la lista de cuentas por pagar desde la base de datos'
            );
    
            // Devolver mensaje amigable para el usuario
            return ["success" => false, "message" => $userMessage];
        } finally {
            // Cierra la conexión
            if (isset($conn)) { mysqli_close($conn); }
        }
    }
    
    public function getPaginatedCuentaPorPagar($page, $size, $sort = null) {
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
            $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_CUENTA_POR_PAGAR . " WHERE " . CUENTA_POR_PAGAR_ESTADO . " != false";
            $totalResult = mysqli_query($conn, $queryTotalCount);
            $totalRow = mysqli_fetch_assoc($totalResult);
            $totalRecords = (int) $totalRow['total'];
            $totalPages = ceil($totalRecords / $size);
    
            // Construir la consulta SQL para paginación
            $querySelect = "
            SELECT 
                " . CUENTA_POR_PAGAR_ID . ", 
                " . CUENTA_POR_PAGAR_COMPRA_DETALLE_ID . ", 
                " . CUENTA_POR_PAGAR_FECHA_VENCIMIENTO . ", 
                " . CUENTA_POR_PAGAR_MONTO_TOTAL . ", 
                " . CUENTA_POR_PAGAR_MONTO_PAGADO . ", 
                " . CUENTA_POR_PAGAR_FECHA_PAGO . ", 
                " . CUENTA_POR_PAGAR_NOTAS . ", 
                " . CUENTA_POR_PAGAR_ESTADO_CUENTA . ", 
                " . CUENTA_POR_PAGAR_ESTADO . "
            FROM " . TB_CUENTA_POR_PAGAR . "
            WHERE " . CUENTA_POR_PAGAR_ESTADO . " != false
            LIMIT ? OFFSET ?
            ";
    
            // Preparar la consulta y vincular los parámetros
            $stmt = mysqli_prepare($conn, $querySelect);
            mysqli_stmt_bind_param($stmt, "ii", $size, $offset);
    
            // Ejecutar la consulta
            $result = mysqli_stmt_execute($stmt);
    
            // Obtener el resultado
            $result = mysqli_stmt_get_result($stmt);
    
            // Crear la lista de cuentas por pagar
            $listaCuentaPorPagar = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $listaCuentaPorPagar [] = [
                    'ID' => $row[CUENTA_POR_PAGAR_ID],
                    'CompraDetalleID' => $row[CUENTA_POR_PAGAR_COMPRA_DETALLE_ID],
                    'FechaVencimiento' => $row[CUENTA_POR_PAGAR_FECHA_VENCIMIENTO],
                    'MontoTotal' => $row[CUENTA_POR_PAGAR_MONTO_TOTAL],
                    'MontoPagado' => $row[CUENTA_POR_PAGAR_MONTO_PAGADO],
                    'FechaPago' => $row[CUENTA_POR_PAGAR_FECHA_PAGO],
                    'Notas' => $row[CUENTA_POR_PAGAR_NOTAS],
                    'EstadoCuenta' => $row[CUENTA_POR_PAGAR_ESTADO_CUENTA],
                    'Estado' => $row[CUENTA_POR_PAGAR_ESTADO]
                ];
            }
    
            // Devolver el resultado con la lista de cuentas por pagar y metadatos de paginación
            return [
                "success" => true,
                "page" => $page,
                "size" => $size,
                "totalPages" => $totalPages,
                "totalRecords" => $totalRecords,
                "listaCuentaPorPagar" => $listaCuentaPorPagar
            ];
        } catch (Exception $e) {
            // Manejo del error dentro del bloque catch
            $userMessage = $this->handleMysqlError(
                $e->getCode(), 
                $e->getMessage(),
                'Error al obtener la lista paginada de cuentas por pagar desde la base de datos'
            );
    
            // Devolver mensaje amigable para el usuario
            return ["success" => false, "message" => $userMessage];
        } finally {
            // Cerrar la conexión y el statement
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }

    private function cuentaPorPagarExiste($cuentaPorPagarID) {
        try {
            // Establece una conexión con la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];
        
            // Crea una consulta SQL que verifica solo las cuentas activas
            $queryCheck = "SELECT * FROM " . TB_CUENTA_POR_PAGAR . " WHERE " . CUENTA_POR_PAGAR_ID . " = ? AND " . CUENTA_POR_PAGAR_ESTADO . " != false";
            $stmt = mysqli_prepare($conn, $queryCheck);
        
            // Asigna el parámetro y ejecuta la consulta
            mysqli_stmt_bind_param($stmt, "i", $cuentaPorPagarID);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
        
            // Verifica si existe algún registro con los criterios dados
            if (mysqli_num_rows($result) > 0) {
                $existingRow = mysqli_fetch_assoc($result);
                return ["success" => true, "exists" => true, "data" => $existingRow]; // Devuelve los datos si existe
            }
        
            return ["success" => true, "exists" => false];
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        } finally {
            // Cierra la conexión y el statement si están definidos
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }
    
    

    public function deleteCuentaPorPagar($cuentaPorPagarID) {
        try {
            // Verifica que el ID de la cuenta por pagar no esté vacío y sea numérico
            if (empty($cuentaPorPagarID) || !is_numeric($cuentaPorPagarID) || $cuentaPorPagarID <= 0) {
                throw new Exception("El ID no puede estar vacío o ser menor a 0.");
            }
            
            // Verificar si existe el ID y que el Estado no sea false
            $check = $this->cuentaPorPagarExiste($cuentaPorPagarID);
            if (!$check["success"]) {
                return $check; // Error al verificar la existencia
            }
            if (!$check["exists"]) {
                throw new Exception("No se encontró una cuenta por pagar con el ID [" . $cuentaPorPagarID . "]");
            }
            
            // Establece una conexión con la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];
            
            // Crea una consulta y un statement SQL para eliminar el registro
            $queryDelete = "UPDATE " . TB_CUENTA_POR_PAGAR . " SET " . CUENTA_POR_PAGAR_ESTADO . " = false WHERE " . CUENTA_POR_PAGAR_ID . " = ?";
            $stmt = mysqli_prepare($conn, $queryDelete);
            mysqli_stmt_bind_param($stmt, 'i', $cuentaPorPagarID);
            
            // Ejecuta la consulta de eliminación
            $result = mysqli_stmt_execute($stmt);
            
            // Devuelve el resultado de la consulta
            return ["success" => true, "message" => "Cuenta por pagar eliminada exitosamente"];
        } catch (Exception $e) {
            // Devolver mensaje amigable para el usuario
            return ["success" => false, "message" => $e->getMessage()];
        } finally {
            // Cierra la conexión y el statement si están definidos
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }

    public function getCuentaPorPagarByID($cuentaPorPagarID) {
        try {
            $check = $this->cuentaPorPagarExiste($cuentaPorPagarID);
            if (!$check['success']) {
                return $check;
            }
            if (!$check['exists']) {
                Utils::writeLog("La cuenta por pagar con 'ID [$cuentaPorPagarID]' no existe en la base de datos.", DATA_LOG_FILE);
                throw new Exception("No existe ninguna cuenta por pagar en la base de datos que coincida con la información proporcionada.");
            }
    
            // Establece una conexión con la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];
    
            // Obtenemos la información de la cuenta por pagar
            $querySelect = "SELECT * FROM " . TB_CUENTA_POR_PAGAR . " WHERE " . CUENTA_POR_PAGAR_ID . " = ? AND " . CUENTA_POR_PAGAR_ESTADO . " != false";
            $stmt = mysqli_prepare($conn, $querySelect);
    
            // Asignar los parámetros y ejecutar la consulta
            mysqli_stmt_bind_param($stmt, 'i', $cuentaPorPagarID);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
    
            // Verifica si existe algún registro con los criterios dados
            $cuentaPorPagar = null;
            if ($row = mysqli_fetch_assoc($result)) {
                $cuentaPorPagar = new CuentaPorPagar(
                    $row[CUENTA_POR_PAGAR_ID],
                    $row[CUENTA_POR_PAGAR_COMPRA_DETALLE_ID],
                    $row[CUENTA_POR_PAGAR_FECHA_VENCIMIENTO],
                    $row[CUENTA_POR_PAGAR_MONTO_TOTAL],
                    $row[CUENTA_POR_PAGAR_MONTO_PAGADO],
                    $row[CUENTA_POR_PAGAR_FECHA_PAGO],
                    $row[CUENTA_POR_PAGAR_NOTAS],
                    $row[CUENTA_POR_PAGAR_ESTADO_CUENTA],
                    $row[CUENTA_POR_PAGAR_ESTADO]
                );
            }
    
            return ["success" => true, "cuentaPorPagar" => $cuentaPorPagar];
        } catch (Exception $e) {
            // Manejo del error dentro del bloque catch
            $userMessage = $this->handleMysqlError(
                $e->getCode(), 
                $e->getMessage(),
                'Error al obtener la cuenta por pagar desde la base de datos'
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
