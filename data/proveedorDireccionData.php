<?php

    require_once dirname(__DIR__, 1) . '/data/data.php';
	require_once dirname(__DIR__, 1) . '/domain/Direccion.php';
	require_once dirname(__DIR__, 1) . '/utils/Variables.php';

    class ProveedorDireccionData extends Data {

        private $className;

        public function __construct() {
            $this->className = get_class($this);
            parent::__construct();
        }

        public function existeProveedorDireccion($proveedorID = null, $direccionID = null, $tbProveedor = false, $tbDireccion = false) {
            $conn = null; $stmt = null;

            try {
                $missingParamsLog="";
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Determina la tabla y construye la consulta base
                $tableName = $tbProveedor ? TB_PROVEEDOR : ($tbDireccion ? TB_DIRECCION : TB_PROVEEDOR_DIRECCION);
                $queryCheck = "SELECT 1 FROM $tableName WHERE ";
                $params = [];
                $types = "";

                if ($proveedorID && $direccionID) {
                    // Consulta para verificar si existe una asignación entre el proveedor y la dirección
                    $queryCheck .= PROVEEDOR_ID . " = ? AND " . DIRECCION_ID . " = ? AND " . PROVEEDOR_DIRECCION_ESTADO . " != FALSE";
                    $params = [$proveedorID, $direccionID];
                    $types = "ii";
                } else if ($proveedorID) {
                    // Consulta para verificar si existe un proveedor con el ID ingresado
                    $estadoCampo = $tbProveedor ? PROVEEDOR_ESTADO : PROVEEDOR_DIRECCION_ESTADO;
                    $queryCheck .= PROVEEDOR_ID . " = ? AND $estadoCampo != FALSE";
                    $params = [$proveedorID];
                    $types = "i";
                } else if ($direccionID) {
                    // Consulta para verificar si existe una dirección con el ID ingresado
                    $estadoCampo = $tbDireccion ? DIRECCION_ESTADO : PROVEEDOR_DIRECCION_ESTADO;
                    $queryCheck .= DIRECCION_ID . " = ? AND $estadoCampo != FALSE";
                    $params = [$direccionID];
                    $types = "i";
                } else {
                    // Registrar parámetros faltantes y lanzar excepción
                    $missingParamsLog = "Faltan parámetros para verificar la existencia del proveedor y/o direccion:";
                    if (!$proveedorID) $missingParamsLog .= " proveedorID [" . ($proveedorID ?? 'null') . "]";
                    if (!$direccionID) $missingParamsLog .= " direccionID [" . ($direccionID ?? 'null') . "]";
                    Utils::writeLog($missingParamsLog, DATA_LOG_FILE, WARN_MESSAGE, $this->className);
                    return ["success" => false, "message" => "No se proporcionaron los parámetros necesarios para realizar la verificación."];
                }

                // Prepara la consulta y ejecuta la verificación
                $stmt = mysqli_prepare($conn, $queryCheck);
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                // Verifica si existe algún registro con los criterios dados
                if (mysqli_num_rows($result) > 0) {
                    return ["success" => true, "exists" => true];
                }

                // Retorna false si no se encontraron resultados
                return ["success" => true, "exists" => false, "message" => $missingParamsLog];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al verificar la existencia de la dirección y/o del proveedor en la base de datos',
                    $this->className
                );
        
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if (isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
            }
        }

        public function verificarExistenciaProveedorDireccion($proveedorID, $direccionID) {
            // Verificar que el proveedor exista en la base de datos
            $checkProveedor = $this->existeProveedorDireccion($proveedorID, null, true);
            if (!$checkProveedor["success"]) { return $checkProveedor; }
            if (!$checkProveedor["exists"]) {
                $message = "El proveedor con ID [$proveedorID] no existe en la base de datos.";
                Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ['success' => false, 'message' => "El proveedor seleccionado no existe en la base de datos."];
            }

            // Verificar que la dirección exista en la base de datos
            $checkDireccion = $this->existeProveedorDireccion(null, $direccionID, false, true);
            if (!$checkDireccion["success"]) { return $checkDireccion; }
            if (!$checkDireccion["exists"]) {
                $message = "La dirección con ID [$direccionID] no existe en la base de datos.";
                Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ['success' => false, 'message' => "La dirección seleccionada no existe en la base de datos."];
            }

            return ['success' => true];
        }

        public function addDireccionToProveedor($proveedorID, $direccionID, $conn = null) {
            $createdConnection = false; //<- Indica si la conexión se creó aquí o viene por parámetro
            $stmt = null;
            
            try {
                // Verificar la existencia del proveedor y de la dirección en la base de datos
                $check = $this->verificarExistenciaProveedorDireccion($proveedorID, $direccionID);
                if (!$check['success']) { return $check; }

                // Verificar si la dirección ya está asignada a algún proveedor
                $check = $this->existeProveedorDireccion(null, $direccionID);
                if (!$check['success']) { return $check; }
                if ($check['exists']) {
                    $message = "La dirección con ID [$direccionID] ya está asignada a otro proveedor.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ['success' => false, 'message' => $message];
                }

                // Si no se proporcionó una conexión, crea una nueva
                if (is_null($conn)) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConnection = true;
                }

                // Obtenemos el último ID de la tabla tbproveedordireccion
				$queryGetLastId = "SELECT MAX(" . PROVEEDOR_DIRECCION_ID . ") FROM " . TB_PROVEEDOR_DIRECCION;
				$idCont = mysqli_query($conn, $queryGetLastId);
				$nextId = 1;

                // Calcula el siguiente ID para la nueva entrada
				if ($row = mysqli_fetch_row($idCont)) {
					$nextId = (int) trim($row[0]) + 1;
				}

                // Crea una consulta y un statement SQL para insertar el registro
                $queryInsert = "INSERT INTO " . TB_PROVEEDOR_DIRECCION . " ("
                    . PROVEEDOR_DIRECCION_ID . ", "
                    . PROVEEDOR_ID . ", "
                    . DIRECCION_ID
                    . ") VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($conn, $queryInsert);

                // Prepara y ejecuta la consulta de inserción
                mysqli_stmt_bind_param($stmt, 'iii', $nextId, $proveedorID, $direccionID);
                $result = mysqli_stmt_execute($stmt);

                return ["success" => true, "message" => "Dirección asignada exitosamente al proveedor."];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
				$userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al intentar asignarle la dirección al proveedor en la base de datos',
                    $this->className
                );
        
                return ["success" => false, "message" => $userMessage];
			} finally {
				// Cierra el statement y la conexión solo si fueron creados en esta función
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if ($createdConnection && isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
			}
        }

        public function removeDireccionFromProveedor($proveedorID, $direccionID, $conn = null) {
            $createdConnection = false; //<- Indica si la conexión se creó aquí o viene por parámetro
            $stmt = null;

            try {
                // Verificar la existencia del proveedor y de la dirección en la base de datos
                $check = $this->verificarExistenciaProveedorDireccion($proveedorID, $direccionID);
                if (!$check['success']) { return $check; }

                // Verificar si existe la asignación entre la dirección y el proveedor en la base de datos
                $check = $this->existeProveedorDireccion($proveedorID, $direccionID);
                if (!$check['success']) { return $check; }
                if (!$check['exists']) {
                    $message = "La dirección con ID [$direccionID] no está asignada al proveedor con ID [$proveedorID].";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ['success' => false, 'message' => "La dirección seleccionada no está asignada al proveedor."];
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

                // Crea una consulta y un statement SQL para eliminar el registro (borrado logico)
				$queryUpdate = 
                    "UPDATE " . TB_PROVEEDOR_DIRECCION . 
                    " SET " 
                        . PROVEEDOR_DIRECCION_ESTADO . " = FALSE " .
					"WHERE " 
                        . PROVEEDOR_ID . " = ? AND " 
                        . DIRECCION_ID . " = ?";
				$stmt = mysqli_prepare($conn, $queryUpdate);
                mysqli_stmt_bind_param($stmt, 'ii', $proveedorID, $direccionID);
				mysqli_stmt_execute($stmt);

                // Eliminar la dirección de la tabla tbDireccion
                $queryUpdateDireccion = 
                    "UPDATE " . TB_DIRECCION . 
                    " SET " 
                        . DIRECCION_ESTADO . " = FALSE " .
                    "WHERE " 
                        . DIRECCION_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryUpdateDireccion);
                mysqli_stmt_bind_param($stmt, 'i', $direccionID);
                mysqli_stmt_execute($stmt);

                // Confirmar la transacción si la conexión fue creada aquí
                if ($createdConnection) {
                    mysqli_commit($conn);
                }
		
				// Devuelve el resultado de la operación
				return ["success" => true, "message" => "Dirección eliminada exitosamente."];
            } catch (Exception $e) {
                // Revertir la transacción en caso de error si la conexión fue creada aquí
                if ($createdConnection && isset($conn)) { mysqli_rollback($conn); }
        
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al intentar eliminar la dirección del proveedor en la base de datos',
                    $this->className
                );
        
                return ["success" => false, "message" => $userMessage];
			} finally {
				// Cierra el statement y la conexión solo si fueron creados en esta función
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if ($createdConnection && isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
			}
        }

        public function getDireccionesByProveedorID($proveedorID, $onlyActive = true, $deleted = false) {
            $conn = null; $stmt = null;

            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Consulta para obtener las direcciones de un proveedor
				$querySelect = "
                    SELECT
                        D.*
                    FROM " . 
                        TB_DIRECCION . " D
                    INNER JOIN
                        " . TB_PROVEEDOR_DIRECCION . " PD 
                        ON D." . DIRECCION_ID . " = PD." . DIRECCION_ID . "
                    WHERE
                        PD." . PROVEEDOR_ID . " = ?" . ($onlyActive ? " AND 
                        PD." . PROVEEDOR_DIRECCION_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE") : "")
                ;

                // Preparar la consulta, vincular los parámetros y ejecutar la consulta
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, 'i', $proveedorID);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                // Creamos la lista con los datos obtenidos
                $direcciones = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $direccion = new Direccion(
                        $row[DIRECCION_ID],
                        $row[DIRECCION_PROVINCIA],
                        $row[DIRECCION_CANTON],
                        $row[DIRECCION_DISTRITO],
                        $row[DIRECCION_BARRIO],
                        $row[DIRECCION_SENNAS],
                        $row[DIRECCION_DISTANCIA],
                        $row[DIRECCION_ESTADO]
                    );
                    $direcciones[] = $direccion;
                }

                return ["success" => true, "direcciones" => $direcciones];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al obtener la lista de direcciones del proveedor desde la base de datos',
                    $this->className
                );

                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if (isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
            }
        }

        public function updateDireccionesProveedor($proveedor, $conn = null) {
            $createdConnection = false; //<- Indica si la conexión se creó aquí o viene por parámetro
            $stmt = null;

            try {
                // Obtener el ID del Proveedor
                $proveedorID = $proveedor->getProveedorID();
                
                // Verificar que el proveedor tenga direcciones registradas
                $check = $this->existeProveedorDireccion($proveedorID);
                if (!$check["success"]) { throw new Exception($check["message"]); }
                if (!$check["exists"]) {
                    $message = "El proveedor con ID [" . $proveedorID . "] no tiene direcciones registradas.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ['success' => false, 'message' => "El proveedor seleccionado no tiene direcciones registradas."];
                }

                // Obtener la lista actual de direcciones del proveedor desde la base de datos
                $result = $this->getDireccionesByProveedorID($proveedorID);
                if (!$result["success"]) { throw new Exception($result["message"]); }

                // Obtener los ID's de las direcciones actuales
                $direccionesActuales = array_map(function($direccion) {
                    return $direccion->getDireccionID();
                }, $result["direcciones"]);

                // Obtener los ID's de las direcciones nuevas
                $direccionesNuevas = array_map(function($direccion) {
                    return $direccion['ID'];
                }, $proveedor->getProveedorDirecciones());

                // Establece una conexión con la base de datos
                if (is_null($conn)) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConnection = true;
        
                    // Desactivar el autocommit para manejar transacciones si la conexión fue creada aquí
                    mysqli_autocommit($conn, false);
                }

                // Añadir nuevas direcciones (las que no están en la BD)
                foreach ($direccionesNuevas as $nuevaDireccionID) {
                    if (!in_array($nuevaDireccionID, $direccionesActuales)) {
                        $addResult = $this->addDireccionToProveedor($proveedorID, $nuevaDireccionID, $conn);
                        if (!$addResult["success"]) { throw new Exception($addResult["message"]); }
                    }
                }

                // Eliminar direcciones que ya no están en la nueva lista
                foreach ($direccionesActuales as $direccionActualID) {
                    if (!in_array($direccionActualID, $direccionesNuevas)) {
                        $removeResult = $this->removeDireccionFromProveedor($proveedorID, $direccionActualID, $conn);
                        if (!$removeResult["success"]) { throw new Exception($removeResult["message"]); }
                    }
                }

                // Confirmar la transacción
                if ($createdConnection) {
                    mysqli_commit($conn);
                }

                return ["success" => true, "message" => "Las direcciones del proveedor se han actualizado correctamente."];
            } catch (Exception $e) {
                if ($createdConnection && isset($conn)) { mysqli_rollback($conn); }
        
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al intentar actualizar las direcciones del proveedor en la base de datos',
                    $this->className
                );
        
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if ($createdConnection && isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
            }
        }

        public function getPaginatedDireccionesByProveedor($proveedorID, $page, $size, $sort = null, $onlyActive = true, $deleted = false) {
            $conn = null; $stmt = null;

            try {
                // Calcular el offset y el total de páginas
                $offset = ($page - 1) * $size;

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Consultar el total de registros
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_PROVEEDOR_DIRECCION . " WHERE " . PROVEEDOR_ID . " = ? ";
                if ($onlyActive) { $queryTotalCount .= " AND " . PROVEEDOR_DIRECCION_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE") . " "; }

                // Preparar la consulta y ejecutarla
                $stmt = mysqli_prepare($conn, $queryTotalCount);
                mysqli_stmt_bind_param($stmt, 'i', $proveedorID);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $totalRecords = (int) mysqli_fetch_assoc($result)["total"];
                $totalPages = ceil($totalRecords / $size);

                // Construir la consulta SQL para paginación
                $querySelect = "
                    SELECT
                        D.*
                    FROM " . 
                        TB_DIRECCION . " D
                    INNER JOIN " 
                        . TB_PROVEEDOR_DIRECCION . " PD 
                        ON D." . DIRECCION_ID . " = PD." . DIRECCION_ID . "
                    WHERE
                        PD." . PROVEEDOR_ID . " = ? ";
                if ($onlyActive) { $querySelect .= " AND " . PROVEEDOR_DIRECCION_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE") . " "; }

                // Añadir la cláusula de ordenamiento si se proporciona
                if ($sort) { $querySelect .= "ORDER BY direccion" . $sort . " "; }

				// Añadir la cláusula de limitación y offset
                $querySelect .= " LIMIT ? OFFSET ?";

                // Preparar la consulta, vincular los parámetros y ejecutar la consulta
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, "iii", $proveedorID, $size, $offset);
                mysqli_stmt_execute($stmt);

                // Obtener los resultados de la consulta
                $result = mysqli_stmt_get_result($stmt);

                // Creamos la lista con los datos obtenidos
                $direcciones = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $direccion = new Direccion(
                        $row[DIRECCION_ID],
                        $row[DIRECCION_PROVINCIA],
                        $row[DIRECCION_CANTON],
                        $row[DIRECCION_DISTRITO],
                        $row[DIRECCION_BARRIO],
                        $row[DIRECCION_SENNAS],
                        $row[DIRECCION_DISTANCIA],
                        $row[DIRECCION_ESTADO]
                    );
                    $direcciones[] = $direccion;
                }

                return [
                    "success" => true,
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "proveedor" => $proveedorID,
                    "direcciones" => $direcciones
                ];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al obtener la lista de direcciones del proveedor desde la base de datos',
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