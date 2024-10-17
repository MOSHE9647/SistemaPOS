<?php

    require_once dirname(__DIR__, 1) . '/data/data.php';
    require_once dirname(__DIR__, 1) . '/domain/Telefono.php';
    require_once dirname(__DIR__, 1) . '/utils/Variables.php';
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';

    class ProveedorTelefonoData extends Data {

        private $className;

        public function __construct() {
            $this->className = get_class($this);
            parent::__construct();
        }

        /**
         * Verifica si existe un proveedor o teléfono en la base de datos.
         * 
         * @param int $proveedorID ID del proveedor (opcional)
         * @param int $telefonoID ID del teléfono (opcional)
         * @param bool $tbProveedor Verificar en la tabla de proveedores (opcional)
         * @param bool $tbTelefono Verificar en la tabla de teléfonos (opcional)
         * @return array Resultado de la verificación (success, exists, message)
         */
        public function existeProveedorTelefono($proveedorID = null, $telefonoID = null, $tbProveedor = false, $tbTelefono = false) {
            $conn = null; $stmt = null;

            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
        
                // Determina la tabla y construye la consulta base
                $tableName = $tbProveedor ? TB_PROVEEDOR : ($tbTelefono ? TB_TELEFONO : TB_PROVEEDOR_TELEFONO);
                $queryCheck = "SELECT 1 FROM $tableName WHERE ";
                $params = [];
                $types = "";
                
                if ($proveedorID && $telefonoID) {
                    // Consulta para verificar si existe una asignación entre el proveedor y el teléfono
                    $queryCheck = "SELECT " . PROVEEDOR_TELEFONO_ID . ", " . PROVEEDOR_TELEFONO_ESTADO . " FROM " . TB_PROVEEDOR_TELEFONO . " WHERE ";
                    $queryCheck .= PROVEEDOR_ID . " = ? AND " . TELEFONO_ID . " = ?";
                    $params = [$proveedorID, $telefonoID];
                    $types = "ii";
                } elseif ($proveedorID) {
                    // Consulta para verificar si existe un proveedor con el ID ingresado
                    $estadoCampo = $tbProveedor ? PROVEEDOR_ESTADO : PROVEEDOR_TELEFONO_ESTADO;
                    $queryCheck .= PROVEEDOR_ID . " = ? AND $estadoCampo != FALSE";
                    $params = [$proveedorID];
                    $types = "i";
                } elseif ($telefonoID) {
                    // Consulta para verificar si existe un teléfono con el ID ingresado
                    $estadoCampo = $tbTelefono ? TELEFONO_ESTADO : PROVEEDOR_TELEFONO_ESTADO;
                    $queryCheck .= TELEFONO_ID . " = ? AND $estadoCampo != FALSE";
                    $params = [$telefonoID];
                    $types = "i";
                } else {
                    // Registrar parámetros faltantes y lanzar excepción
                    $missingParamsLog = "Faltan parámetros para verificar la existencia del proveedor y/o telefono:";
                    if (!$proveedorID) $missingParamsLog .= " proveedorID [" . ($proveedorID ?? 'null') . "]";
                    if (!$telefonoID) $missingParamsLog .= " telefonoID [" . ($telefonoID ?? 'null') . "]";
                    Utils::writeLog($missingParamsLog, DATA_LOG_FILE, WARN_MESSAGE, $this->className);
                    return ["success" => false, "message" => "No se proporcionaron los parámetros necesarios para realizar la verificación."];
                }
        
                // Preparar y ejecutar la consulta
                $stmt = mysqli_prepare($conn, $queryCheck);
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
        
                // Verifica si existe algún registro con los criterios dados
                if ($row = mysqli_fetch_assoc($result)) {
                    if ($proveedorID && $telefonoID) {
                        $isInactive = $row[PROVEEDOR_TELEFONO_ESTADO] == 0;
                        return ["success" => true, "exists" => true, "inactive" => $isInactive, 'id' => $row[PROVEEDOR_TELEFONO_ID]];
                    }
                    return ["success" => true, "exists" => true];
                }
        
                // Retorna false si no se encontraron resultados
                return ["success" => true, "exists" => false];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al verificar la existencia del teléfono y/o del proveedor en la base de datos',
                    $this->className
                );
        
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if (isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
            }
        }

        /**
         * Verifica que el proveedor y el teléfono existan en la base de datos.
         * 
         * @param int $proveedorID ID del proveedor
         * @param int $telefonoID ID del teléfono
         * @return array Resultado de la verificación (success, message)
         */
        private function verificarExistenciaProveedorTelefono($proveedorID, $telefonoID) {
            // Verificar que el proveedor exista en la base de datos
            $checkProveedorID = $this->existeProveedorTelefono($proveedorID, null, true);
            if (!$checkProveedorID["success"]) { return $checkProveedorID; }
            if (!$checkProveedorID["exists"]) {
                $message = "El proveedor con 'ID [$proveedorID]' no existe en la base de datos.";
                Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ['success' => false, 'message' => "El proveedor seleccionado no existe en la base de datos."];
            }

            // Verificar que el teléfono exista en la base de datos
            $checkTelefonoID = $this->existeProveedorTelefono(null, $telefonoID, false, true);
            if (!$checkTelefonoID["success"]) { return $checkTelefonoID; }
            if (!$checkTelefonoID["exists"]) {
                $message = "El teléfono con ID [$telefonoID] no existe en la base de datos.";
                Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ['success' => false, 'message' => "El telefono seleccionado no existe en la base de datos."];
            }

            return ['success' => true];
        }

        /**
         * Agrega un teléfono a un proveedor en la base de datos.
         * 
         * @param int $proveedorID ID del proveedor
         * @param int $telefonoID ID del teléfono
         * @param mysqli $conn Conexión a la base de datos (opcional)
         * @return array Resultado de la operación (success, message)
         */
        public function addTelefonoToProveedor($proveedorID, $telefonoID, $conn = null) {
            $createdConnection = false; //<- Indica si la conexión se creó aquí o viene por parámetro
            $stmt = null;
        
            try {
                // Verificar la existencia del proveedor y del teléfono en la base de datos
                $check = $this->verificarExistenciaProveedorTelefono($proveedorID, $telefonoID);
                if (!$check["success"]) { return $check; }
        
                // Verificar si el teléfono ya está asignado a algún proveedor
                $checkID = $this->existeProveedorTelefono(null, $telefonoID);
                if (!$checkID["success"]) { return $checkID; }
               
                // Si el teléfono ya está asignado a un proveedor, no se puede asignar
                if ($checkID["exists"]) {
                    $message = "El teléfono con ID [$telefonoID] ya está asignado a otro proveedor.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ['success' => false, 'message' => "El teléfono seleccionado ya está asignado a un proveedor."];
                }

                // Verificar si existe la asignacion entre el proveedor y el telefono
                $check = $this->existeProveedorTelefono($proveedorID, $telefonoID);
                if (!$check["success"]) { return $check; }

                // Si no se proporcionó una conexión, crea una nueva
                if (is_null($conn)) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConnection = true;
                }

                // Si la asignación ya existe, pero está inactiva, se reactiva
                if ($check["exists"] && $check["inactive"]) {
                    $queryUpdate = 
                        "UPDATE " . TB_PROVEEDOR_TELEFONO . 
                        " SET " 
                            . PROVEEDOR_TELEFONO_ESTADO . " = TRUE " .
                        "WHERE " 
                            . PROVEEDOR_ID . " = ? AND " 
                            . TELEFONO_ID . " = ?";
                    $stmt = mysqli_prepare($conn, $queryUpdate);
                    mysqli_stmt_bind_param($stmt, "ii", $proveedorID, $telefonoID);
                    mysqli_stmt_execute($stmt);
        
                    return ["success" => true, "message" => "Teléfono asignado exitosamente al proveedor."];
                }
        
                // Obtenemos el último ID de la tabla tbproveedortelefono
                $queryGetLastId = "SELECT MAX(" . PROVEEDOR_TELEFONO_ID . ") FROM " . TB_PROVEEDOR_TELEFONO;
                $idCont = mysqli_query($conn, $queryGetLastId);
                $nextId = 1;
        
                // Calcula el siguiente ID para la nueva entrada
                if ($row = mysqli_fetch_row($idCont)) {
                    $nextId = (int) trim($row[0]) + 1;
                }
        
                // Crea una consulta y un statement SQL para insertar el nuevo registro
                $queryInsert = "INSERT INTO " . TB_PROVEEDOR_TELEFONO . " ("
                    . PROVEEDOR_TELEFONO_ID . ", "
                    . PROVEEDOR_ID . ", "
                    . TELEFONO_ID
                    . ") VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($conn, $queryInsert);
        
                // Vincula los parámetros y ejecuta la consulta
                mysqli_stmt_bind_param($stmt, "iii", $nextId, $proveedorID, $telefonoID);
                mysqli_stmt_execute($stmt);
        
                return ["success" => true, "message" => "Teléfono asignado exitosamente al proveedor."];
            } catch (Exception $e) {
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al intentar asignarle el teléfono al proveedor en la base de datos',
                    $this->className
                );
        
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra el statement y la conexión solo si fueron creados en esta función
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if ($createdConnection && isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
            }
        }        

        /**
         * Elimina un teléfono de un proveedor en la base de datos.
         * 
         * @param int $proveedorID ID del proveedor
         * @param int $telefonoID ID del teléfono
         * @param mysqli $conn Conexión a la base de datos (opcional)
         * @return array Resultado de la operación (success, message)
         */
        public function removeTelefonoFromProveedor($proveedorID, $telefonoID, $conn = null) {
            $createdConnection = false; //<- Indica si la conexión se creó aquí o viene por parámetro
            $stmt = null;
        
            try {
                // Verificar la existencia del proveedor y el teléfono en la base de datos
                $checkIDs = $this->verificarExistenciaProveedorTelefono($proveedorID, $telefonoID);
                if (!$checkIDs["success"]) { return $checkIDs; }
        
                // Verificar si existe la asignación entre el teléfono y el proveedor en la base de datos
                $check = $this->existeProveedorTelefono($proveedorID, $telefonoID);
                if (!$check["success"]) { return $check; }
                if (!$check["exists"]) {
                    $message = "El teléfono con ID [$telefonoID] no está asignado al proveedor con ID [$proveedorID].";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ['success' => false, 'message' => "El teléfono seleccionado no está asignado al proveedor."];
                }
        
                // Si no se proporcionó una conexión, crea una nueva
                if (is_null($conn)) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConnection = true;
        
                    // Desactivar el autocommit para manejar transacciones si la conexión fue creada aquí
                    mysqli_autocommit($conn, false);
                }
        
                // Eliminar la asignación entre el proveedor y el teléfono
                $queryUpdate = 
                    "UPDATE " . TB_PROVEEDOR_TELEFONO . 
                    " SET " 
                        . PROVEEDOR_TELEFONO_ESTADO . " = FALSE " .
                    "WHERE " 
                        . PROVEEDOR_ID . " = ? AND " 
                        . TELEFONO_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryUpdate);
                mysqli_stmt_bind_param($stmt, "ii", $proveedorID, $telefonoID);
                mysqli_stmt_execute($stmt);
        
                // Eliminar teléfono de la tabla tbTelefono
                $queryUpdateTelefono = 
                    "UPDATE " . TB_TELEFONO . 
                    " SET " 
                        . TELEFONO_ESTADO . " = FALSE " .
                    "WHERE " 
                        . TELEFONO_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryUpdateTelefono);
                mysqli_stmt_bind_param($stmt, "i", $telefonoID);
                mysqli_stmt_execute($stmt);
        
                // Confirmar la transacción si la conexión fue creada aquí
                if ($createdConnection) {
                    mysqli_commit($conn);
                }
        
                return ["success" => true, "message" => "Teléfono eliminado correctamente."];
            } catch (Exception $e) {
                // Revertir la transacción en caso de error si la conexión fue creada aquí
                if ($createdConnection && isset($conn)) { mysqli_rollback($conn); }
        
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al intentar eliminar el teléfono del proveedor en la base de datos',
                    $this->className
                );
        
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra el statement y la conexión solo si fueron creados en esta función
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if ($createdConnection && isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
            }
        }

        /**
         * Obtiene la lista de teléfonos de un proveedor desde la base de datos.
         * 
         * @param int $proveedorID ID del proveedor
         * @param bool $json Devuelve la lista en formato JSON (opcional)
         * @return array Resultado de la consulta (success, telefonos)
         */
        public function getTelefonosByProveedorID($proveedorID, $onlyActive = true, $deleted = false) {
            $conn = null; $stmt = null;
            
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Consulta para obtener los teléfonos de un proveedor
                $querySelect = "
                    SELECT
                        T.*
                    FROM " . 
                        TB_TELEFONO . " T
                    INNER JOIN " . 
                        TB_PROVEEDOR_TELEFONO . " PT 
                        ON T." . TELEFONO_ID . " = PT." . TELEFONO_ID . "
                    WHERE 
                        PT." . PROVEEDOR_ID . " = ?" . ($onlyActive ? " AND 
                        PT." . PROVEEDOR_TELEFONO_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE") : "");

                // Preparar la consulta, vincular los parámetros y ejecutar la consulta
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, "i", $proveedorID);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                $telefonos = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $telefono = new Telefono(
                        $row[TELEFONO_ID],
                        $row[TELEFONO_TIPO],
                        $row[TELEFONO_CODIGO_PAIS],
                        $row[TELEFONO_NUMERO],
                        $row[TELEFONO_EXTENSION],
                        $row[TELEFONO_FECHA_CREACION],
                        $row[TELEFONO_FECHA_MODIFICACION],
                        $row[TELEFONO_ESTADO]
                    );
                    $telefonos[] = $telefono;
                }

                return ["success" => true, "telefonos" => $telefonos];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al obtener la lista de teléfonos del proveedor desde la base de datos',
                    $this->className
                );

                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if (isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
            }
        }

        /**
         * Actualiza la lista de teléfonos de un proveedor en la base de datos.
         * 
         * @param Proveedor $proveedor Objeto Proveedor con la lista de teléfonos actualizada
         * @return array Resultado de la operación (success, message)
         */
        public function updateTelefonosProveedor($proveedor, $conn = null) {
            $createdConnection = false; //<- Indica si la conexión se creó aquí o viene por parámetro
            $stmt = null;
        
            try {
                // Obtener el ID del Proveedor
                $proveedorID = $proveedor->getProveedorID();

                // Verificar que el proveedor tenga teléfonos registrados
                $checkID = $this->existeProveedorTelefono($proveedorID);
                if (!$checkID["success"]) { throw new Exception($checkID["message"]); }
                if (!$checkID["exists"]) {
                    $message = "El proveedor con ID [$proveedorID] no tiene teléfonos registrados.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => false, "message" => "El proveedor seleccionado no tiene teléfonos registrados."];
                }

                // Obtener la lista actual de teléfonos del proveedor desde la base de datos
                $result = $this->getTelefonosByProveedorID($proveedorID);
                if (!$result["success"]) { throw new Exception($result["message"]); }
                
                // Obtener los ID's de los teléfonos actuales
                $telefonosActuales = array_map(function($telefono) {
                    return $telefono->getTelefonoID();
                }, $result["telefonos"]);

                // Obtener los ID's de los nuevos teléfonos
                $nuevosTelefonos = array_map(function($telefono) {
                    return $telefono['ID'];
                }, $proveedor->getProveedorTelefonos());
        
                // Establece una conexión con la base de datos
                if (is_null($conn)) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConnection = true;
        
                    // Desactivar el autocommit para manejar transacciones si la conexión fue creada aquí
                    mysqli_autocommit($conn, false);
                }
        
                // Añadir nuevos teléfonos (los que no están en la BD)
                foreach ($nuevosTelefonos as $nuevoTelefonoID) {
                    if (!in_array($nuevoTelefonoID, $telefonosActuales)) {
                        $addResult = $this->addTelefonoToProveedor($proveedorID, $nuevoTelefonoID, $conn);
                        if (!$addResult["success"]) { throw new Exception($addResult["message"]); }
                    }
                }
        
                // Eliminar teléfonos que ya no están en la nueva lista
                foreach ($telefonosActuales as $telefonoActualID) {
                    if (!in_array($telefonoActualID, $nuevosTelefonos)) {
                        $removeResult = $this->removeTelefonoFromProveedor($proveedorID, $telefonoActualID, $conn);
                        if (!$removeResult["success"]) { throw new Exception($removeResult["message"]); }
                    }
                }
        
                // Confirmar la transacción
                if ($createdConnection) {
                    mysqli_commit($conn);
                }
        
                return ["success" => true, "message" => "Los teléfonos del proveedor se han actualizado correctamente."];
        
            } catch (Exception $e) {
                if ($createdConnection && isset($conn)) { mysqli_rollback($conn); }
        
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al intentar actualizar los teléfonos del proveedor en la base de datos',
                    $this->className
                );
        
                return ["success" => false, "message" => $userMessage];
            } finally {
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if ($createdConnection && isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
            }
        }

        public function getPaginatedTelefonosByProveedor($proveedorID, $page, $size, $sort = null, $onlyActive = true, $deleted = false) {
            $conn = null; $stmt = null;

            try {
                // Validar los parámetros de paginación
                if (!is_numeric($page) || $page < 1) {
                    throw new Exception("El número de página debe ser un entero positivo.");
                }
                if (!is_numeric($size) || $size < 1) {
                    throw new Exception("El tamaño de la página debe ser un entero positivo.");
                }
                $offset = ($page - 1) * $size;

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Consultar el total de registros
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_PROVEEDOR_TELEFONO . " WHERE " . PROVEEDOR_ID . " = ? ";
                if ($onlyActive) { $queryTotalCount .= " AND " . PROVEEDOR_TELEFONO_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE") . " "; }

                $stmt = mysqli_prepare($conn, $queryTotalCount);
                mysqli_stmt_bind_param($stmt, "i", $proveedorID);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $totalRecords = (int) mysqli_fetch_assoc($result)["total"];
                $totalPages = ceil($totalRecords / $size);

                // Construir la consulta SQL para paginación
                $querySelect = "
                    SELECT
                        T.*
                    FROM " . 
                        TB_TELEFONO . " T
                    INNER JOIN " . 
                        TB_PROVEEDOR_TELEFONO . " PT 
                        ON T." . TELEFONO_ID . " = PT." . TELEFONO_ID . "
                    WHERE 
                        PT." . PROVEEDOR_ID . " = ?";
                if ($onlyActive) { $querySelect .= " AND PT." . PROVEEDOR_TELEFONO_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE") . " "; }

                // Añadir la cláusula de ordenamiento si se proporciona
                if ($sort) { $querySelect .= "ORDER BY telefono" . $sort . " "; }

				// Añadir la cláusula de limitación y offset
                $querySelect .= " LIMIT ? OFFSET ?";

                // Preparar la consulta, vincular los parámetros y ejecutar la consulta
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, "iii", $proveedorID, $size, $offset);
                mysqli_stmt_execute($stmt);

                // Obtener los resultados de la consulta
                $result = mysqli_stmt_get_result($stmt);

                $telefonos = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $telefonos[] = [
                        'ID' => $row[TELEFONO_ID],
						'Tipo' => $row[TELEFONO_TIPO],
						'CodigoPais' => $row[TELEFONO_CODIGO_PAIS],
						'Numero' => $row[TELEFONO_NUMERO],
						'Extension' => $row[TELEFONO_EXTENSION],
						'CreacionISO' => Utils::formatearFecha($row[TELEFONO_FECHA_CREACION], 'Y-MM-dd'),
						'Creacion' => Utils::formatearFecha($row[TELEFONO_FECHA_CREACION]),
                        'ModificacionISO' => Utils::formatearFecha($row[TELEFONO_FECHA_MODIFICACION], 'Y-MM-dd'),
                        'Modificacion' => Utils::formatearFecha($row[TELEFONO_FECHA_MODIFICACION]),
						'Estado' => $row[TELEFONO_ESTADO]
                    ];
                }

                return [
                    "success" => true,
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "proveedor" => $proveedorID,
                    "telefonos" => $telefonos
                ];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al obtener la lista de teléfonos del proveedor desde la base de datos',
                    $this->className
                );
        
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if (isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
            }
        }

    }

?>