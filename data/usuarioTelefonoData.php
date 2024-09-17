<?php

    require_once 'data.php';
    require_once __DIR__ . '/../domain/Telefono.php';
    require_once __DIR__ . '/../utils/Variables.php';
    require_once __DIR__ . '/../utils/Utils.php';

    class UsuarioTelefonoData extends Data {

        private $className;

        public function __construct() {
            $this->className = get_class($this);
            parent::__construct();
        }

        /**
         * Verifica si existe un usuario o teléfono en la base de datos.
         * 
         * @param int $usuarioID ID del usuario (opcional)
         * @param int $telefonoID ID del teléfono (opcional)
         * @param bool $tbUsuario Verificar en la tabla de usuarioes (opcional)
         * @param bool $tbTelefono Verificar en la tabla de teléfonos (opcional)
         * @return array Resultado de la verificación (success, exists, message)
         */
        public function existeUsuarioTelefono($usuarioID = null, $telefonoID = null, $tbUsuario = false, $tbTelefono = false) {
            $conn = null; $stmt = null;

            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
        
                // Determina la tabla y construye la consulta base
                $tableName = $tbUsuario ? TB_USUARIO : ($tbTelefono ? TB_TELEFONO : TB_USUARIO_TELEFONO);
                $queryCheck = "SELECT 1 FROM $tableName WHERE ";
                $params = [];
                $types = "";
                
                if ($usuarioID && $telefonoID) {
                    // Consulta para verificar si existe una asignación entre el usuario y el teléfono
                    $queryCheck = "SELECT " . USUARIO_TELEFONO_ID . ", " . USUARIO_TELEFONO_ESTADO . " FROM " . TB_USUARIO_TELEFONO . " WHERE ";
                    $queryCheck .= USUARIO_ID . " = ? AND " . TELEFONO_ID . " = ?";
                    $params = [$usuarioID, $telefonoID];
                    $types = "ii";
                } elseif ($usuarioID) {
                    // Consulta para verificar si existe un usuario con el ID ingresado
                    $estadoCampo = $tbUsuario ? USUARIO_ESTADO : USUARIO_TELEFONO_ESTADO;
                    $queryCheck .= USUARIO_ID . " = ? AND $estadoCampo != FALSE";
                    $params = [$usuarioID];
                    $types = "i";
                } elseif ($telefonoID) {
                    // Consulta para verificar si existe un teléfono con el ID ingresado
                    $estadoCampo = $tbTelefono ? TELEFONO_ESTADO : USUARIO_TELEFONO_ESTADO;
                    $queryCheck .= TELEFONO_ID . " = ? AND $estadoCampo != FALSE";
                    $params = [$telefonoID];
                    $types = "i";
                } else {
                    // Registrar parámetros faltantes y lanzar excepción
                    $missingParamsLog = "Faltan parámetros para verificar la existencia del usuario y/o telefono:";
                    if (!$usuarioID) $missingParamsLog .= " usuarioID [" . ($usuarioID ?? 'null') . "]";
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
                    if ($usuarioID && $telefonoID) {
                        $isInactive = $row[USUARIO_TELEFONO_ESTADO] == 0;
                        return ["success" => true, "exists" => true, "inactive" => $isInactive, 'id' => $row[USUARIO_TELEFONO_ID]];
                    }
                    return ["success" => true, "exists" => true];
                }
        
                // Retorna false si no se encontraron resultados
                return ["success" => true, "exists" => false];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al verificar la existencia del teléfono y/o del usuario en la base de datos',
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
         * Verifica que el usuario y el teléfono existan en la base de datos.
         * 
         * @param int $usuarioID ID del usuario
         * @param int $telefonoID ID del teléfono
         * @return array Resultado de la verificación (success, message)
         */
        private function verificarExistenciaUsuarioTelefono($usuarioID, $telefonoID) {
            // Verificar que el usuario exista en la base de datos
            $checkUsuarioID = $this->existeUsuarioTelefono($usuarioID, null, true);
            if (!$checkUsuarioID["success"]) { return $checkUsuarioID; }
            if (!$checkUsuarioID["exists"]) {
                $message = "El usuario con 'ID [$usuarioID]' no existe en la base de datos.";
                Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ['success' => false, 'message' => "El usuario seleccionado no existe en la base de datos."];
            }

            // Verificar que el teléfono exista en la base de datos
            $checkTelefonoID = $this->existeUsuarioTelefono(null, $telefonoID, false, true);
            if (!$checkTelefonoID["success"]) { return $checkTelefonoID; }
            if (!$checkTelefonoID["exists"]) {
                $message = "El teléfono con ID [$telefonoID] no existe en la base de datos.";
                Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ['success' => false, 'message' => "El telefono seleccionado no existe en la base de datos."];
            }

            return ['success' => true];
        }

        /**
         * Agrega un teléfono a un usuario en la base de datos.
         * 
         * @param int $usuarioID ID del usuario
         * @param int $telefonoID ID del teléfono
         * @param mysqli $conn Conexión a la base de datos (opcional)
         * @return array Resultado de la operación (success, message)
         */
        public function addTelefonoToUsuario($usuarioID, $telefonoID, $conn = null) {
            $createdConnection = false; //<- Indica si la conexión se creó aquí o viene por parámetro
            $stmt = null;
        
            try {
                // Verificar la existencia del usuario y del teléfono en la base de datos
                $check = $this->verificarExistenciaUsuarioTelefono($usuarioID, $telefonoID);
                if (!$check["success"]) { return $check; }
        
                // Verificar si el teléfono ya está asignado a algún usuario
                $checkID = $this->existeUsuarioTelefono(null, $telefonoID);
                if (!$checkID["success"]) { return $checkID; }
               
                // Si el teléfono ya está asignado a un usuario, no se puede asignar
                if ($checkID["exists"]) {
                    $message = "El teléfono con ID [$telefonoID] ya está asignado a otro usuario.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ['success' => false, 'message' => "El teléfono seleccionado ya está asignado a un usuario."];
                }

                // Verificar si existe la asignacion entre el usuario y el telefono
                $check = $this->existeUsuarioTelefono($usuarioID, $telefonoID);
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
                        "UPDATE " . TB_USUARIO_TELEFONO . 
                        " SET " 
                            . USUARIO_TELEFONO_ESTADO . " = TRUE " .
                        "WHERE " 
                            . USUARIO_ID . " = ? AND " 
                            . TELEFONO_ID . " = ?";
                    $stmt = mysqli_prepare($conn, $queryUpdate);
                    mysqli_stmt_bind_param($stmt, "ii", $usuarioID, $telefonoID);
                    mysqli_stmt_execute($stmt);
        
                    return ["success" => true, "message" => "Teléfono asignado exitosamente al usuario."];
                }
        
                // Obtenemos el último ID de la tabla tbusuariotelefono
                $queryGetLastId = "SELECT MAX(" . USUARIO_TELEFONO_ID . ") FROM " . TB_USUARIO_TELEFONO;
                $idCont = mysqli_query($conn, $queryGetLastId);
                $nextId = 1;
        
                // Calcula el siguiente ID para la nueva entrada
                if ($row = mysqli_fetch_row($idCont)) {
                    $nextId = (int) trim($row[0]) + 1;
                }
        
                // Crea una consulta y un statement SQL para insertar el nuevo registro
                $queryInsert = "INSERT INTO " . TB_USUARIO_TELEFONO . " ("
                    . USUARIO_TELEFONO_ID . ", "
                    . USUARIO_ID . ", "
                    . TELEFONO_ID
                    . ") VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($conn, $queryInsert);
        
                // Vincula los parámetros y ejecuta la consulta
                mysqli_stmt_bind_param($stmt, "iii", $nextId, $usuarioID, $telefonoID);
                mysqli_stmt_execute($stmt);
        
                return ["success" => true, "message" => "Teléfono asignado exitosamente al usuario."];
            } catch (Exception $e) {
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al intentar asignarle el teléfono al usuario en la base de datos',
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
         * Elimina un teléfono de un usuario en la base de datos.
         * 
         * @param int $usuarioID ID del usuario
         * @param int $telefonoID ID del teléfono
         * @param mysqli $conn Conexión a la base de datos (opcional)
         * @return array Resultado de la operación (success, message)
         */
        public function removeTelefonoFromUsuario($usuarioID, $telefonoID, $conn = null) {
            $createdConnection = false; //<- Indica si la conexión se creó aquí o viene por parámetro
            $stmt = null;
        
            try {
                // Verificar la existencia del usuario y el teléfono en la base de datos
                $checkIDs = $this->verificarExistenciaUsuarioTelefono($usuarioID, $telefonoID);
                if (!$checkIDs["success"]) { return $checkIDs; }
        
                // Verificar si existe la asignación entre el teléfono y el usuario en la base de datos
                $check = $this->existeUsuarioTelefono($usuarioID, $telefonoID);
                if (!$check["success"]) { return $check; }
                if (!$check["exists"]) {
                    $message = "El teléfono con ID [$telefonoID] no está asignado al usuario con ID [$usuarioID].";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ['success' => false, 'message' => "El teléfono seleccionado no está asignado al usuario."];
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
        
                // Eliminar la asignación entre el usuario y el teléfono
                $queryUpdate = 
                    "UPDATE " . TB_USUARIO_TELEFONO . 
                    " SET " 
                        . USUARIO_TELEFONO_ESTADO . " = FALSE " .
                    "WHERE " 
                        . USUARIO_ID . " = ? AND " 
                        . TELEFONO_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryUpdate);
                mysqli_stmt_bind_param($stmt, "ii", $usuarioID, $telefonoID);
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
                    'Ocurrió un error al intentar eliminar el teléfono del usuario en la base de datos',
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
         * Obtiene la lista de teléfonos de un usuario desde la base de datos.
         * 
         * @param int $usuarioID ID del usuario
         * @param bool $json Devuelve la lista en formato JSON (opcional)
         * @return array Resultado de la consulta (success, telefonos)
         */
        public function getTelefonosByUsuario($usuarioID, $json = false) {
            $conn = null; $stmt = null;
            
            try {
                // Verificar que el usuario tenga teléfonos registrados
                $checkID = $this->existeUsuarioTelefono($usuarioID);
                if (!$checkID["success"]) { throw new Exception($checkID["message"]); }
                if (!$checkID["exists"]) {
                    $message = "El usuario con ID [$usuarioID] no tiene teléfonos registrados.";
                    Utils::writeLog($message, DATA_LOG_FILE, WARN_MESSAGE, $this->className);
                    return ["success" => true, "message" => "El usuario seleccionado no tiene teléfonos registrados."];
                }

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Consulta para obtener los teléfonos de un usuario
                $querySelect = "
                    SELECT
                        T.*
                    FROM " . 
                        TB_TELEFONO . " T
                    INNER JOIN " . 
                        TB_USUARIO_TELEFONO . " UT 
                        ON T." . TELEFONO_ID . " = UT." . TELEFONO_ID . "
                    WHERE 
                        UT." . USUARIO_ID . " = ? AND 
                        UT." . USUARIO_TELEFONO_ESTADO . " != FALSE
                ";

                // Preparar la consulta, vincular los parámetros y ejecutar la consulta
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, "i", $usuarioID);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                $telefonos = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    if ($json) {
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
                    } else {
                        $telefonos[] = new Telefono(
                            $row[TELEFONO_ID],
                            $row[TELEFONO_TIPO],
                            $row[TELEFONO_CODIGO_PAIS],
                            $row[TELEFONO_NUMERO],
                            $row[TELEFONO_EXTENSION],
                            $row[TELEFONO_FECHA_CREACION],
                            $row[TELEFONO_FECHA_MODIFICACION],
                            $row[TELEFONO_ESTADO]
                        );
                    }
                }

                return ["success" => true, "telefonos" => $telefonos];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al obtener la lista de teléfonos del usuario desde la base de datos',
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
         * Actualiza la lista de teléfonos de un usuario en la base de datos.
         * 
         * @param Usuario $usuario Objeto Usuario con la lista de teléfonos actualizada
         * @return array Resultado de la operación (success, message)
         */
        public function updateTelefonosUsuario($usuario) {
            $conn = null; $stmt = null;
        
            try {
                // Obtener el ID del Usuario
                $usuarioID = $usuario->getUsuarioID();

                // Verificar que el usuario tenga teléfonos registrados
                $checkID = $this->existeUsuarioTelefono($usuarioID);
                if (!$checkID["success"]) { throw new Exception($checkID["message"]); }
                if (!$checkID["exists"]) {
                    $message = "El usuario con ID [$usuarioID] no tiene teléfonos registrados.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => false, "message" => "El usuario seleccionado no tiene teléfonos registrados."];
                }

                // Obtener la lista actual de teléfonos del usuario desde la base de datos
                $result = $this->getTelefonosByUsuario($usuarioID);
                if (!$result["success"]) { throw new Exception($result["message"]); }
                
                // Obtener los ID's de los teléfonos actuales
                $telefonosActuales = array_map(function($telefono) {
                    return $telefono->getTelefonoID();
                }, $result["telefonos"]);

                // Obtener los ID's de los nuevos teléfonos
                $nuevosTelefonos = array_map(function($telefono) {
                    return $telefono->getTelefonoID();
                }, $usuario->getUsuarioTelefonos());
        
                // Establece una conexión con la base de datos
                $connResult = $this->getConnection();
                if (!$connResult["success"]) { throw new Exception($connResult["message"]); }
                $conn = $connResult["connection"];
                
                // Iniciar una transacción
                mysqli_begin_transaction($conn);
        
                // Añadir nuevos teléfonos (los que no están en la BD)
                foreach ($nuevosTelefonos as $nuevoTelefonoID) {
                    if (!in_array($nuevoTelefonoID, $telefonosActuales)) {
                        $addResult = $this->addTelefonoToUsuario($usuarioID, $nuevoTelefonoID, $conn);
                        if (!$addResult["success"]) { throw new Exception($addResult["message"]); }
                    }
                }
        
                // Eliminar teléfonos que ya no están en la nueva lista
                foreach ($telefonosActuales as $telefonoActualID) {
                    if (!in_array($telefonoActualID, $nuevosTelefonos)) {
                        $removeResult = $this->removeTelefonoFromUsuario($usuarioID, $telefonoActualID, $conn);
                        if (!$removeResult["success"]) { throw new Exception($removeResult["message"]); }
                    }
                }
        
                // Confirmar la transacción
                mysqli_commit($conn);
        
                return ["success" => true, "message" => "Los teléfonos del usuario se han actualizado correctamente."];
        
            } catch (Exception $e) {
                if (isset($conn)) { mysqli_rollback($conn); }
        
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al intentar actualizar los teléfonos del usuario en la base de datos',
                    $this->className
                );
        
                return ["success" => false, "message" => $userMessage];
            } finally {
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if (isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
            }
        }

        public function getPaginatedTelefonosByUsuario($usuarioID, $page, $size, $sort = null, $onlyActiveOrInactive = true, $deleted = false) {
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
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_USUARIO_TELEFONO . " WHERE " . USUARIO_ID . " = ? ";
                if ($onlyActiveOrInactive) { $queryTotalCount .= " AND " . USUARIO_TELEFONO_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE") . " "; }

                $stmt = mysqli_prepare($conn, $queryTotalCount);
                mysqli_stmt_bind_param($stmt, "i", $usuarioID);
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
                        TB_USUARIO_TELEFONO . " UT 
                        ON T." . TELEFONO_ID . " = UT." . TELEFONO_ID . "
                    WHERE 
                        UT." . USUARIO_ID . " = ?";
                if ($onlyActiveOrInactive) { $querySelect .= " AND UT." . USUARIO_TELEFONO_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE") . " "; }

                // Añadir la cláusula de ordenamiento si se proporciona
                if ($sort) { $querySelect .= "ORDER BY telefono" . $sort . " "; }

				// Añadir la cláusula de limitación y offset
                $querySelect .= " LIMIT ? OFFSET ?";

                // Preparar la consulta, vincular los parámetros y ejecutar la consulta
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, "iii", $usuarioID, $size, $offset);
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
                    "usuario" => $usuarioID,
                    "telefonos" => $telefonos
                ];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al obtener la lista de teléfonos del usuario desde la base de datos',
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