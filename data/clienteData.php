<?php

    require_once dirname(__DIR__, 1) . '/data/data.php';
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';
    require_once dirname(__DIR__, 1) . '/utils/Variables.php';
    require_once dirname(__DIR__, 1) . '/domain/Cliente.php';
    require_once dirname(__DIR__, 1) . '/domain/Usuario.php';
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
                $queryCheck = "SELECT " . CLIENTE_ID . ", " . TELEFONO_ID . ", " . CLIENTE_ESTADO . " FROM " . TB_CLIENTE . " WHERE ";
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
                    $queryCheck .= TELEFONO_ID . " = ? ";
                    $params[] = $clienteTelefonoID;
                    $types .= "i";
                }

                // Consulta en caso de actualizar para verificar si existe un cliente con el mismo número de teléfono
                else if ($update && ($clienteID && $clienteTelefonoID)) {
                    $queryCheck .= TELEFONO_ID . " = ? AND " . CLIENTE_ID . " != ? ";
                    $params = [$clienteTelefonoID, $clienteID];
                    $types .= "ii";
                }

                // En caso de no cumplirse ninguna condición
                else {
                    // Registrar parámetros faltantes y lanzar excepción
                    $missingParamsLog = "Faltan parámetros para verificar la existencia del cliente:";
                    if (!$clienteID) $missingParamsLog .= " clienteID [" . ($clienteID ?? 'null') . "]";
                    if (!$clienteTelefonoID) $missingParamsLog .= " clienteTelefonoID [" . ($clienteTelefonoID ?? 'null') . "]";
                    Utils::writeLog($missingParamsLog, DATA_LOG_FILE, WARN_MESSAGE, $this->className, __LINE__);
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
                        "success" => true, "exists" => true, "telefonoID" => $row[TELEFONO_ID],
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

        public function insertCliente($cliente, $conn = null) {
            $createdConn = false;
            $stmt = null;

            try {
                if (!$conn) {
                    // Establece una conexión con la base de datos
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConn = true;

                    // Iniciar una transacción
                    mysqli_begin_transaction($conn);
                }
                
                // Insertar el Telefono en la Base de Datos
                $telefonoData = new TelefonoData();
                $clienteTelefono = $cliente->getClienteTelefono();
                $result = $telefonoData->insertTelefono($clienteTelefono, $conn);
                if (!$result["success"]) { throw new Exception($result["message"]); }

                // Verifica si el teléfono está inactivo
                $clienteTelefono->setTelefonoID($result["id"]);
                if ($result["inactive"]) {
                    // Actualiza el estado del teléfono a activo
                    $result = $telefonoData->updateTelefono($clienteTelefono, $conn);
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                }

                // Obtiene el ID del teléfono insertado
                $clienteTelefonoID = $clienteTelefono->getTelefonoID();

                // Verificar si el cliente ya existe en la base de datos
                $result = $this->existeCliente(null, $clienteTelefonoID, false, true);
                if (!$result["success"]) { return $result; }
                
                // En caso de que el cliente exista pero esté inactivo
                if ($result["exists"] && $result["inactive"]) {
                    $numeroTelefono = $clienteTelefono->obtenerNumeroCompleto();
                    $message = "Ya existe un cliente con el mismo teléfono ($numeroTelefono) en la base de datos, pero está inactivo. ¿Desea reactivarlo?";
                    return ["success" => true, "message" => $message, "inactive" => $result["inactive"], "id" => $result["clienteID"]];
                }

                // En caso de que el cliente ya exista y esté activo
                if ($result["exists"]) {
                    $message = "El cliente con 'TelefonoID [$clienteTelefonoID]' ya existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    return ["success" => true, "message" => "Ya existe un cliente con el mismo teléfono en la base de datos."];
                }

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
                        . TELEFONO_ID . ", "
                        . CLIENTE_NOMBRE . ", "
                        . CLIENTE_ALIAS .
                    ") VALUES (?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $queryInsert);

                // Obtener los valores del objeto Cliente
                $clienteNombre = $cliente->getClienteNombre();
                $clienteAlias = $cliente->getClienteAlias();
                $clienteTelefonoID = $clienteTelefono->getTelefonoID();

                // Asignar los parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, "iiss", $nextId, $clienteTelefonoID, $clienteNombre, $clienteAlias);
                mysqli_stmt_execute($stmt);

                // Confirmar la transacción
                if ($createdConn) { mysqli_commit($conn); }

                return ["success" => true, "message" => "Cliente insertado correctamente.", "id" => $nextId];
            } catch (Exception $e) {
                if ($createdConn) { mysqli_rollback($conn); }

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
                if ($createdConn && isset($conn)) { mysqli_close($conn); }
            }
        }

        public function updateCliente($cliente, $conn = null) {
            $createdConn = false;
            $stmt = null;

            try {
                // Obtener los IDs del cliente y el teléfono
                $clienteID = $cliente->getClienteID();
                $clienteTelefono = $cliente->getClienteTelefono();
                $clienteTelefonoID = $clienteTelefono->getTelefonoID();

                // Verificar si el cliente existe en la base de datos
                $result = $this->existeCliente($clienteID);
                if (!$result["success"]) { return $result; }

                // En caso de que el cliente no exista
                if (!$result["exists"]) {
                    $message = "El cliente con 'ID [$clienteID]' no existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    return ["success" => true, "message" => "El cliente seleccionado no existe en la base de datos."];
                }

                // Verifica que no exista otro cliente con la misma información
                $result = $this->existeCliente($clienteID, $clienteTelefonoID, true);
                if (!$result["success"]) { return $result; }

                // En caso de que el cliente exista
                if ($result["exists"]) {
                    $message = "El cliente con 'TelefonoID [$clienteTelefonoID]' ya existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    return ["success" => true, "message" => "Ya existe un cliente con el mismo teléfono en la base de datos."];
                }

                // Establece una conexión con la base de datos
                if (!$conn) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConn = true;

                    // Iniciar una transacción
                    mysqli_begin_transaction($conn);
                }

                // Actualizar el Telefono en la Base de Datos
                $telefonoData = new TelefonoData();
                $result = $telefonoData->updateTelefono($clienteTelefono, $conn);
                if (!$result["success"]) { throw new Exception($result["message"]); }

                // Crea una consulta y un statement SQL para actualizar el registro
                $queryUpdate = 
                    "UPDATE " . TB_CLIENTE . " SET "
                        . TELEFONO_ID . " = ?, "
                        . CLIENTE_NOMBRE . " = ?, "
                        . CLIENTE_ALIAS . " = ?, "
                        . CLIENTE_ESTADO . " = TRUE "
                    . "WHERE " . CLIENTE_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryUpdate);

                // Obtener los valores del objeto Cliente
                $clienteNombre = $cliente->getClienteNombre();
                $clienteAlias = $cliente->getClienteAlias();

                // Asignar los parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, "issi", $clienteTelefonoID, $clienteNombre, $clienteAlias, $clienteID);
                mysqli_stmt_execute($stmt);

                // Confirmar la transacción
                if ($createdConn) { mysqli_commit($conn); }

                // Devuelve un mensaje de éxito
                return ["success" => true, "message" => "Cliente actualizado exitosamente"];
            } catch (Exception $e) {
                if ($createdConn) { mysqli_rollback($conn); }

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
                if ($createdConn && isset($conn)) { mysqli_close($conn); }
            }
        }

        public function deleteCliente($clienteID, $conn = null) {
            $createdConn = false;
            $stmt = null;

            try {
                // Verificar si el cliente existe en la base de datos
                $result = $this->existeCliente($clienteID);
                if (!$result["success"]) { return $result; }

                // En caso de que el cliente no exista
                if (!$result["exists"]) {
                    $message = "El cliente con 'ID [$clienteID]' no existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    return ["success" => true, "message" => "El cliente seleccionado no existe en la base de datos."];
                }
                $clienteTelefonoID = $result["telefonoID"];

                // Establece una conexión con la base de datos
                if (!$conn) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConn = true;

                    // Iniciar una transacción
                    mysqli_begin_transaction($conn);
                }

                // Crea una consulta y un statement SQL para eliminar el registro del cliente
                $queryDelete = "UPDATE " . TB_CLIENTE . " SET " . CLIENTE_ESTADO . " = FALSE WHERE " . CLIENTE_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryDelete);

                // Asignar los parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, "i", $clienteID);
                mysqli_stmt_execute($stmt);

                // Eliminar el teléfono asociado al cliente
                $telefonoData = new TelefonoData();
                $result = $telefonoData->deleteTelefono($clienteTelefonoID, $conn);
                if (!$result["success"]) { throw new Exception($result["message"]); }

                // Confirmar la transacción
                if ($createdConn) { mysqli_commit($conn); }

                return ["success" => true, "message" => "Cliente eliminado correctamente."];
            } catch (Exception $e) {
                // Revertir la transacción en caso de error
                if ($createdConn) { mysqli_rollback($conn); }

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
                if ($createdConn && isset($conn)) { mysqli_close($conn); }
            }
        }

        public function getAllTBCliente($onlyActive = false, $deleted = false) {
            $conn = null;

            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Inicializa la consulta base
                $querySelect = "SELECT * FROM " . TB_CLIENTE;
                if ($onlyActive) { 
                    $querySelect .= " WHERE " . CLIENTE_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE"); 
                }
                $result = mysqli_query($conn, $querySelect);

                // Crear un array para almacenar los clientes
                $clientes = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    // Obtiene el telefono del cliente
                    $telefonoData = new TelefonoData();
                    $telefono = $telefonoData->getTelefonoByID($row[TELEFONO_ID]);
                    if (!$telefono["success"]) { throw new Exception($telefono["message"]); }

                    // Crea un objeto Cliente con los datos obtenidos
                    $cliente = new Cliente(
                        $row[CLIENTE_ID], 
                        $row[CLIENTE_NOMBRE],
                        $row[CLIENTE_ALIAS],
                        $telefono["telefono"],
                        $row[CLIENTE_CREACION],
                        $row[CLIENTE_MODIFICACION],
                        $row[CLIENTE_ESTADO]
                    );
                    $clientes[] = $cliente;
                }

                // Devuelve un mensaje de éxito
                return ["success" => true, "clientes" => $clientes];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al obtener los clientes de la base de datos',
                    $this->className
                );

                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => "Error al obtener la lista de clientes: $userMessage"];
            } finally {
                // Cerrar la conexión y liberar recursos
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function getPaginatedClientes($search, $page, $size, $sort = null, $onlyActive = false, $deleted = false) {
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
                if ($onlyActive) { 
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
                        c.*,
                        t.*,
                        CONCAT(t." . TELEFONO_CODIGO_PAIS . ", ' ', t." . TELEFONO_NUMERO . ") AS telefono
                    FROM " . 
                        TB_CLIENTE . " c 
                    INNER JOIN " . 
                        TB_TELEFONO . " t ON c." . TELEFONO_ID . " = t." . TELEFONO_ID
                ;

                // Agregar filtro de búsqueda a la consulta
                $params = [];
                $types = "";
                if ($search) {
                    $querySelect .= " WHERE (" . CLIENTE_NOMBRE . " LIKE ?";
                    $querySelect .= " OR telefono LIKE ?)";
                    $searchParam = "%" . $search . "%";
                    $params = [$searchParam, $searchParam];
                    $types .= "ss";
                }

                // Agregar filtro de estado a la consulta
                if ($onlyActive) { 
                    $querySelect .= $search ? " AND " : " WHERE ";
                    $querySelect .= CLIENTE_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE"); 
                }

                // Agregar ordenamiento a la consulta
                if ($sort) {
                    $querySelect .= " ORDER BY " . ($sort === 'telefono' ? $sort : "c.cliente$sort");
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
                        $row[CLIENTE_ALIAS],
                        new Telefono(
                            $row[TELEFONO_ID],
                            $row[TELEFONO_TIPO],
                            $row[TELEFONO_CODIGO_PAIS],
                            $row[TELEFONO_NUMERO],
                            $row[TELEFONO_EXTENSION]
                        ),
                        $row[CLIENTE_CREACION],
                        $row[CLIENTE_MODIFICACION],
                        $row[CLIENTE_ESTADO]
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

        public function getClienteByID($clienteID, $onlyActive = false, $deleted = false) {
            $conn = null; $stmt = null;

            try {
                // Verificar si el cliente existe en la base de datos
                $result = $this->existeCliente($clienteID);
                if (!$result["success"]) { return $result; }

                // En caso de que el cliente no exista
                if (!$result["exists"]) {
                    $message = "El cliente con 'ID [$clienteID]' no existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    return ["success" => true, "message" => "El cliente seleccionado no existe en la base de datos."];
                }

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Crea una consulta y un statement SQL para obtener el registro
                $querySelect = "
                    SELECT 
                        * 
                    FROM " . 
                        TB_CLIENTE . " 
                    WHERE " . 
                        CLIENTE_ID . " = ?" . ($onlyActive ? " AND " . 
                        CLIENTE_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE") : "")
                    ;
                $stmt = mysqli_prepare($conn, $querySelect);

                // Asignar los parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, "i", $clienteID);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                // Verificar si se encontró el cliente
                if ($row = mysqli_fetch_assoc($result)) {
                    // Obtiene el telefono del cliente
                    $telefonoData = new TelefonoData();
                    $telefono = $telefonoData->getTelefonoByID($row[TELEFONO_ID], false);
                    if (!$telefono["success"]) { throw new Exception($telefono["message"]); }

                    // Crea un objeto Cliente con los datos obtenidos
                    $cliente = new Cliente(
                        $row[CLIENTE_ID], 
                        $row[CLIENTE_NOMBRE],
                        $row[CLIENTE_ALIAS],
                        $telefono["telefono"],
                        $row[CLIENTE_CREACION],
                        $row[CLIENTE_MODIFICACION],
                        $row[CLIENTE_ESTADO]
                    );
                    return ["success" => true, "cliente" => $cliente];
                }

                // En caso de que no se haya encontrado el cliente
                $message = "No se encontró el cliente con 'ID [$clienteID]' en la base de datos.";
                Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
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
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    return ["success" => true, "message" => "El cliente seleccionado no existe en la base de datos."];
                }

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Crea una consulta y un statement SQL para obtener el registro
                $querySelect = "SELECT * FROM " . TB_CLIENTE . " WHERE " . TELEFONO_ID . " = ? AND " . CLIENTE_ESTADO . " != FALSE";
                $stmt = mysqli_prepare($conn, $querySelect);

                // Asignar los parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, "i", $clienteTelefonoID);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                // Verificar si se encontró el cliente
                if ($row = mysqli_fetch_assoc($result)) {
                    // Obtiene el telefono del cliente
                    $telefonoData = new TelefonoData();
                    $telefono = $telefonoData->getTelefonoByID($row[TELEFONO_ID]);
                    if (!$telefono["success"]) { throw new Exception($telefono["message"]); }

                    // Crea un objeto Cliente con los datos obtenidos
                    $cliente = new Cliente(
                        $row[CLIENTE_ID], 
                        $row[CLIENTE_NOMBRE],
                        $row[CLIENTE_ALIAS],
                        $telefono["telefono"],
                        $row[CLIENTE_CREACION],
                        $row[CLIENTE_MODIFICACION],
                        $row[CLIENTE_ESTADO]
                    );
                    return ["success" => true, "cliente" => $cliente];
                }

                // En caso de que no se haya encontrado el cliente
                $message = "No se encontró el cliente con 'TelefonoID [$clienteTelefonoID]' en la base de datos.";
                Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
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


        public function getVentaClienteByID($clienteID) {
            $conn = null;
            $stmt = null;
        
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                // Consulta SQL para obtener solo el ID y el nombre del proveedor
                $querySelect = "
                    SELECT 
                        " . CLIENTE_ID . ", 
                        " . CLIENTE_NOMBRE . " 
                    FROM " . TB_CLIENTE . " 
                    WHERE " . CLIENTE_ID . " = ?";
                $stmt = mysqli_prepare($conn, $querySelect);
        
                // Vincula los parámetros de la consulta
                mysqli_stmt_bind_param($stmt, 'i', $clienteID);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
        
              // Obtener el resultado de la consulta
        if ($row = mysqli_fetch_assoc($result)) {
            // Retorna una instancia de Proveedor
            return new Cliente($row[CLIENTE_ID], $row[CLIENTE_NOMBRE]);
        }
        
                // Retorna false si no se encontraron resultados
                return [
                    "success" => false,
                    "message" => "No se encontró ningún cliente con el ID proporcionado."
                ];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener el cliente desde la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return [
                    "success" => false,
                    "message" => $userMessage
                ];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) {
                    mysqli_stmt_close($stmt);
                }
                if (isset($conn)) {
                    mysqli_close($conn);
                }
            }
        }

        public function getCompraClienteByID($clienteID) {
            $conn = null;
            $stmt = null;
        
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                // Consulta SQL para obtener solo el ID y el nombre del proveedor
                $querySelect = "
                    SELECT 
                        " . CLIENTE_ID . ", 
                        " . CLIENTE_NOMBRE . " 
                    FROM " . TB_CLIENTE . " 
                    WHERE " . CLIENTE_ID . " = ?";
                $stmt = mysqli_prepare($conn, $querySelect);
        
                // Vincula los parámetros de la consulta
                mysqli_stmt_bind_param($stmt, 'i', $clienteID);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
        
              // Obtener el resultado de la consulta
        if ($row = mysqli_fetch_assoc($result)) {
            // Retorna una instancia de Proveedor
            return new Cliente($row[CLIENTE_ID], $row[CLIENTE_NOMBRE]);
        }
        
                // Retorna false si no se encontraron resultados
                return [
                    "success" => false,
                    "message" => "No se encontró ningún cliente con el ID proporcionado."
                ];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener el cliente desde la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return [
                    "success" => false,
                    "message" => $userMessage
                ];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) {
                    mysqli_stmt_close($stmt);
                }
                if (isset($conn)) {
                    mysqli_close($conn);
                }
            }
        }
        
    }

?>