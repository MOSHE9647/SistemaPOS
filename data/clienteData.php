<?php

    require_once 'data.php';
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';
    require_once dirname(__DIR__, 1) . '/utils/Variables.php';
    require_once dirname(__DIR__, 1) . '/domain/Cliente.php';
    require_once dirname(__DIR__, 1) . '/domain/Telefono.php';

    class ClienteData extends Data {

        private $className;

        public function __construct() {
            $this->className = get_class($this);
            parent::__construct();
        }

        public function existeCliente($clienteID = null, $clienteTelefonoID = null, $update = false, $insert = false) {
            $conn = null; $stmt = null;

            try {
                // Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) { throw new Exception($result["message"]); }
				$conn = $result["connection"];

                // Inicializa la consulta base
                $queryCheck = "SELECT " . CLIENTE_ID . ", " . CLIENTE_TELEFONO_ID . ", " . CLIENTE_ESTADO . " FROM " . TB_CLIENTE . " WHERE ";
				$params = [];
				$types = "";

                // Consulta para verificar si existe un cliente con el ID ingresado
                if ($clienteID && (!$update && !$insert)) {
                    $queryCheck .= CLIENTE_ID . " = ? ";
                    $params[] = $clienteID;
                    $types .= "i";
                }

                // Consulta para verificar si existe un cliente con el teléfono ingresado
                else if ($insert && $clienteTelefonoID) {
                    $queryCheck .= CLIENTE_TELEFONO_ID . " = ? ";
                    $params[] = $clienteTelefonoID;
                    $types .= "i";
                }

                // Consulta en caso de actualizar para verificar si existe un cliente con el mismo número de teléfono
                else if ($update && ($clienteID && $clienteTelefonoID)) {
                    $queryCheck .= CLIENTE_TELEFONO_ID . " = ? AND " . CLIENTE_ID . " != ? ";
                    $params = [$clienteTelefonoID, $clienteID];
                    $types .= "ii";
                }

                // En caso de no cumplirse ninguna condición
                else {
                    // Registrar parámetros faltantes y lanzar excepción
                    $missingParamsLog = "Faltan parámetros para verificar la existencia del cliente:";
                    if (!$clienteID) $missingParamsLog .= " clienteID [" . ($clienteID ?? 'null') . "]";
                    if (!$clienteTelefonoID) $missingParamsLog .= " clienteTelefonoID [" . ($clienteTelefonoID ?? 'null') . "]";
                    Utils::writeLog($missingParamsLog, DATA_LOG_FILE, WARN_MESSAGE, $this->className);
                    throw new Exception("Faltan parámetros para verificar la existencia del cliente.");
                }

                // Asignar los parámetros y ejecutar la consulta
                $stmt = mysqli_prepare($conn, $queryCheck);
				mysqli_stmt_bind_param($stmt, $types, ...$params);
				mysqli_stmt_execute($stmt);
				$result = mysqli_stmt_get_result($stmt);

                // Verificar si el cliente existe
                if ($row = mysqli_fetch_assoc($result)) {
                    // Verificar si está inactivo (bit de estado en 0)
                    $isInactive = $row[CLIENTE_ESTADO] == 0;
                    return [
                        "success" => true, "exists" => true, "telefonoID" => $row[CLIENTE_TELEFONO_ID],
                        "inactive" => $isInactive, "clienteID" => $row[CLIENTE_ID]
                    ];
                }

                // Retorna false si no se encontraron resultados
                $messageParams = [];
                if ($clienteID) { $messageParams[] = "'ID [$clienteID]'"; }
                if ($clienteTelefonoID)  { $messageParams[] = "'TelefonoID [$clienteTelefonoID]'"; }
                $params = implode(', ', $messageParams);

                $message = "No se encontró ningún cliente ($params) en la base de datos.";
                return ["success" => true, "exists" => false, "message" => $message];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al verificar la existencia del cliente en la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y libera los recursos
                if ($stmt) { mysqli_stmt_close($stmt); }
                if ($conn) { mysqli_close($conn); }
            } 
        }

        public function insertCliente($cliente) {
            $conn = null; $stmt = null;

            try {
                $clienteTelefonoID = $cliente->getClienteTelefonoID();

                // Verificar si el usuario ya existe en la base de datos
                $result = $this->existeCliente(null, $clienteTelefonoID, false, true);
                if (!$result["success"]) { return $result; }

                // En caso de que el cliente existe pero esté inactivo
                if ($result["exists"] && $result["inactive"]) {
                    $message = "Ya existe un cliente con el mismo teléfono ($clienteTelefonoID) en la base de datos, pero está inactivo. ¿Desea reactivarlo?";
                    return ["success" => true, "message" => $message, "inactive" => $result["inactive"], "id" => $result["clienteID"]];
                }

                // En caso de que el cliente ya exista y esté activo
                if ($result["exists"]) {
                    $message = "El cliente con TelefonoID [$clienteTelefonoID] ya existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => true, "message" => "Ya existe un cliente con el mismo teléfono en la base de datos."];
                }

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Obtenemos el último ID de la tabla tbusuario
                $queryGetLastId = "SELECT MAX(" . CLIENTE_ID . ") FROM " . TB_CLIENTE;
                $idCont = mysqli_query($conn, $queryGetLastId);
                $nextId = 1;

                // Calcula el siguiente ID para la nueva entrada
				if ($row = mysqli_fetch_row($idCont)) {
					$nextId = (int) trim($row[0]) + 1;
				}

                // Crea una consulta y un statement SQL para insertar el nuevo registro
                $queryInsert = 
                    "INSERT INTO " . TB_CLIENTE . " ("
                        . CLIENTE_ID . ", "
                        . CLIENTE_NOMBRE . ", "
                        . CLIENTE_TELEFONO_ID . 
                    ") VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($conn, $queryInsert);

                // Obtener los valores del objeto Cliente
                $clienteNombre = $cliente->getClienteNombre();

                // Asignar los parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, "iss", $nextId, $clienteNombre, $clienteTelefonoID);
                mysqli_stmt_execute($stmt);

                return ["success" => true, "message" => "Cliente insertado correctamente.", "id" => $nextId];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al insertar el cliente en la base de datos',
                    $this->className
                );

                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cerrar la conexión y liberar recursos
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function updateCliente($cliente) {
            $conn = null; $stmt = null;

            try {
                $clienteID = $cliente->getClienteID();
                $clienteTelefonoID = $cliente->getClienteTelefonoID();

                // Verificar si el cliente existe en la base de datos
                $result = $this->existeCliente($clienteID);
                if (!$result["success"]) { return $result; }

                // En caso de que el cliente no exista
                if (!$result["exists"]) {
                    $message = "El cliente con 'ID [$clienteID]' no existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => true, "message" => "El cliente seleccionado no existe en la base de datos."];
                }

                // Verifica que no exista otro cliente con la misma información
                $result = $this->existeCliente($clienteID, $clienteTelefonoID, true);
                if (!$result["success"]) { return $result; }

                // En caso de que el cliente exista
                if ($result["exists"]) {
                    $message = "El cliente con 'TelefonoID [$clienteTelefonoID]' ya existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => true, "message" => "Ya existe un cliente con el mismo teléfono en la base de datos."];
                }

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Crea una consulta y un statement SQL para actualizar el registro
                $queryUpdate = 
                    "UPDATE " . TB_CLIENTE . " SET "
                        . CLIENTE_NOMBRE . " = ?, "
                        . CLIENTE_TELEFONO_ID . " = ?, "
                        . CLIENTE_ESTADO . " = TRUE "
                    . "WHERE " . CLIENTE_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryUpdate);

                // Obtener los valores del objeto Cliente
                $clienteNombre = $cliente->getClienteNombre();

                // Asignar los parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, "sii", $clienteNombre, $clienteTelefonoID, $clienteID);
                mysqli_stmt_execute($stmt);

                // Devuelve un mensaje de éxito
                return ["success" => true, "message" => "Cliente actualizado exitosamente"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al actualizar el cliente en la base de datos',
                    $this->className
                );

                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cerrar la conexión y liberar recursos
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function deleteCliente($clienteID) {
            $conn = null; $stmt = null;

            try {
                // Verificar si el cliente existe en la base de datos
                $result = $this->existeCliente($clienteID);
                if (!$result["success"]) { return $result; }

                // En caso de que el cliente no exista
                if (!$result["exists"]) {
                    $message = "El cliente con 'ID [$clienteID]' no existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => true, "message" => "El cliente seleccionado no existe en la base de datos."];
                }
                $clienteTelefonoID = $result["telefonoID"];

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Iniciar una transacción
                mysqli_begin_transaction($conn);

                // Crea una consulta y un statement SQL para eliminar el registro del cliente
                $queryDeleteCliente = "UPDATE " . TB_CLIENTE . " SET " . CLIENTE_ESTADO . " = FALSE WHERE " . CLIENTE_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryDeleteCliente);

                // Asignar los parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, "i", $clienteID);
                mysqli_stmt_execute($stmt);

                // Crea una consulta y un statement SQL para eliminar el registro del teléfono
                $queryDeleteTelefono = "UPDATE " . TB_TELEFONO . " SET " . TELEFONO_ESTADO . " = FALSE WHERE " . TELEFONO_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryDeleteTelefono);

                // Asignar los parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, "i", $clienteTelefonoID);
                mysqli_stmt_execute($stmt);

                // Confirmar la transacción
                mysqli_commit($conn);

                return ["success" => true, "message" => "Cliente eliminado correctamente."];
            } catch (Exception $e) {
                // Revertir la transacción en caso de error
                if ($conn) { mysqli_rollback($conn); }

                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al eliminar el cliente en la base de datos',
                    $this->className
                );

                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cerrar la conexión y liberar recursos
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function getAllTBCliente($onlyActiveOrInactive = false, $deleted = false) {
            $conn = null;

            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Inicializa la consulta base
                $querySelect = "SELECT * FROM " . TB_CLIENTE;
                if ($onlyActiveOrInactive) { 
                    $querySelect .= " WHERE " . CLIENTE_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE"); 
                }
                $result = mysqli_query($conn, $querySelect);

                // Crear un array para almacenar los clientes
                $clientes = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $cliente = new Cliente(
                        $row[CLIENTE_ID], 
                        $row[CLIENTE_NOMBRE], 
                        $row[CLIENTE_TELEFONO_ID],
                        $row[CLIENTE_FECHA_CREACION],
                        $row[CLIENTE_FECHA_MODIFICACION],
                        $row[CLIENTE_ESTADO]
                    );
                    $clientes[] = $cliente;
                }

                // Devuelve un mensaje de éxito
                return ["success" => true, "data" => $clientes];
            } catch (Excpetion $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al obtener los clientes de la base de datos',
                    $this->className
                );

                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cerrar la conexión y liberar recursos
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function getPaginatedClientes($search, $page, $size, $sort, $onlyActiveOrInactive = false, $deleted = false) {
            $conn = null; $stmt = null;

            try {
                // Calcular el offset y la página actual
                $offset = ($page - 1) * $size;

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Consultar el total de registros
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_CLIENTE;
                if ($onlyActiveOrInactive) { 
                    $queryTotalCount .= " WHERE " . CLIENTE_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE"); 
                }

                // Ejecutar la consulta y obtener el total de registros
                $totalResult = mysqli_query($conn, $queryTotalCount);
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = (int) $totalRow['total'];
                $totalPages = ceil($totalRecords / $size);

                // Construir la consulta SQL para paginación
                $querySelect = "
                    SELECT 
                        c." . CLIENTE_ID . ",
                        c." . CLIENTE_NOMBRE . ",
                        c." . CLIENTE_TELEFONO_ID . ", 
                        c." . CLIENTE_FECHA_CREACION . ", 
                        c." . CLIENTE_FECHA_MODIFICACION . ",
                        c." . CLIENTE_ESTADO . ",
                        t." . TELEFONO_TIPO . ",
                        t." . TELEFONO_CODIGO_PAIS . ",
                        t." . TELEFONO_NUMERO . ",
                        t." . TELEFONO_EXTENSION . ",
                        CONCAT(t." . TELEFONO_CODIGO_PAIS . ", ' ', t." . TELEFONO_NUMERO . ") AS telefono
                    FROM " . 
                        TB_CLIENTE . " c 
                    INNER JOIN " . TB_TELEFONO . " t 
                        ON c." . CLIENTE_TELEFONO_ID . " = t." . TELEFONO_ID
                ;

                // Agregar filtro de búsqueda a la consulta
                $params = [];
                $types = "";
                if ($search) {
                    $querySelect .= " WHERE (" . CLIENTE_NOMBRE . " LIKE ?";
                    $querySelect .= " OR CONCAT(t." . TELEFONO_CODIGO_PAIS . ", ' ', t." . TELEFONO_NUMERO . ") LIKE ?)";
                    $searchParam = "%" . $search . "%";
                    $params = [$searchParam, $searchParam];
                    $types .= "ss";
                }

                // Agregar filtro de estado a la consulta
                if ($onlyActiveOrInactive) { 
                    $querySelect .= $search ? " AND " : " WHERE ";
                    $querySelect .= CLIENTE_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE"); 
                }

                // Agregar ordenamiento a la consulta
                if ($sort) { 
                    if ($sort === 'telefono') {
                        $querySelect .= " ORDER BY t." . TELEFONO_CODIGO_PAIS . ", t." . TELEFONO_NUMERO . " ";
                    } else {
                        $querySelect .= " ORDER BY c.cliente" . $sort . " ";
                    }
                } else { 
                    $querySelect .= " ORDER BY " . CLIENTE_ID . " DESC"; 
                }

                // Agregar límites a la consulta
                $querySelect .= " LIMIT ? OFFSET ?";
                $params = array_merge($params, [$size, $offset]);
                $types .= "ii";

                // Preparar la consulta SQL y asignar los parámetros
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);

                // Ejecutar la consulta y obtener los resultados
                $result = mysqli_stmt_get_result($stmt);

                // Crear un array para almacenar los clientes
                $clientes = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $clientes[] = new Cliente(
                    $row[CLIENTE_ID], 
                    $row[CLIENTE_NOMBRE], 
                    $row[CLIENTE_TELEFONO_ID],
                    $row[CLIENTE_FECHA_CREACION],
                    $row[CLIENTE_FECHA_MODIFICACION],
                    $row[CLIENTE_ESTADO],
                    new Telefono(
                        $row[CLIENTE_TELEFONO_ID],
                        $row[TELEFONO_TIPO],
                        $row[TELEFONO_CODIGO_PAIS],
                        $row[TELEFONO_NUMERO],
                        $row[TELEFONO_EXTENSION]
                    )
                    );
                }

                // Devuelve un mensaje de éxito
                return [
                    "success" => true,
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "clientes" => $clientes
                ];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al obtener los clientes de la base de datos',
                    $this->className
                );

                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cerrar la conexión y liberar recursos
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function getClienteByID($clienteID) {
            $conn = null; $stmt = null;

            try {
                // Verificar si el cliente existe en la base de datos
                $result = $this->existeCliente($clienteID);
                if (!$result["success"]) { return $result; }

                // En caso de que el cliente no exista
                if (!$result["exists"]) {
                    $message = "El cliente con 'ID [$clienteID]' no existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => true, "message" => "El cliente seleccionado no existe en la base de datos."];
                }

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Crea una consulta y un statement SQL para obtener el registro
                $querySelect = "SELECT * FROM " . TB_CLIENTE . " WHERE " . CLIENTE_ID . " = ? AND " . CLIENTE_ESTADO . " != FALSE";
                $stmt = mysqli_prepare($conn, $querySelect);

                // Asignar los parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, "i", $clienteID);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                // Verificar si se encontró el cliente
                if ($row = mysqli_fetch_assoc($result)) {
                    $cliente = new Cliente(
                        $row[CLIENTE_ID], 
                        $row[CLIENTE_NOMBRE], 
                        $row[CLIENTE_TELEFONO_ID],
                        $row[CLIENTE_FECHA_CREACION],
                        $row[CLIENTE_FECHA_MODIFICACION],
                        $row[CLIENTE_ESTADO]
                    );
                    return ["success" => true, "cliente" => $cliente];
                }

                // En caso de que no se haya encontrado el cliente
                $message = "No se encontró el cliente con 'ID [$clienteID]' en la base de datos.";
                Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ["success" => true, "message" => "No se encontró el cliente seleccionado en la base de datos."];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al obtener el cliente de la base de datos',
                    $this->className
                );

                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cerrar la conexión y liberar recursos
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function getClienteByTelefonoID($clienteTelefonoID) {
            $conn = null; $stmt = null;

            try {
                // Verificar si el cliente existe en la base de datos
                $result = $this->existeCliente(null, $clienteTelefonoID);
                if (!$result["success"]) { return $result; }

                // En caso de que el cliente no exista
                if (!$result["exists"]) {
                    $message = "El cliente con 'TelefonoID [$clienteTelefonoID]' no existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => true, "message" => "El cliente seleccionado no existe en la base de datos."];
                }

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Crea una consulta y un statement SQL para obtener el registro
                $querySelect = "SELECT * FROM " . TB_CLIENTE . " WHERE " . CLIENTE_TELEFONO_ID . " = ? AND " . CLIENTE_ESTADO . " != FALSE";
                $stmt = mysqli_prepare($conn, $querySelect);

                // Asignar los parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, "i", $clienteTelefonoID);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                // Verificar si se encontró el cliente
                if ($row = mysqli_fetch_assoc($result)) {
                    $cliente = new Cliente(
                        $row[CLIENTE_ID], 
                        $row[CLIENTE_NOMBRE], 
                        $row[CLIENTE_TELEFONO_ID],
                        $row[CLIENTE_FECHA_CREACION],
                        $row[CLIENTE_FECHA_MODIFICACION],
                        $row[CLIENTE_ESTADO]
                    );
                    return ["success" => true, "cliente" => $cliente];
                }

                // En caso de que no se haya encontrado el cliente
                $message = "No se encontró el cliente con 'TelefonoID [$clienteTelefonoID]' en la base de datos.";
                Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ["success" => true, "message" => "No se encontró el cliente seleccionado en la base de datos."];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al obtener el cliente de la base de datos',
                    $this->className
                );

                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cerrar la conexión y liberar recursos
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }
        
    }

?>