<?php

    require_once dirname(__DIR__, 1) . '/data/data.php';
    require_once dirname(__DIR__, 1) . '/data/categoriaData.php';
    require_once dirname(__DIR__, 1) . '/data/direccionData.php';
    require_once dirname(__DIR__, 1) . '/data/proveedorTelefonoData.php';
    require_once dirname(__DIR__, 1) . '/data/proveedorDireccionData.php';
    require_once dirname(__DIR__, 1) . '/domain/Proveedor.php';
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';
    require_once dirname(__DIR__, 1) . '/utils/Variables.php';

    class ProveedorData extends Data {

        private $proveedorTelefonoData;
        private $proveedorDireccionData;
        private $direccionData;
        private $categoriaData;
        private $className;
        
        // Constructor
        public function __construct() {
            parent::__construct();
            $this->className = get_class($this);
            $this->proveedorTelefonoData = new  ProveedorTelefonoData();
            $this->proveedorDireccionData = new ProveedorDireccionData();
            $this->direccionData = new DireccionData();
            $this->categoriaData = new CategoriaData();
        }

        // Función para verificar si un proveedor con el mismo nombre ya existe en la bd
        public function proveedorExiste($proveedorID = null, $proveedorNombre = null, $proveedorEmail = null, $update = false, $insert = false) {
            $conn = null; $stmt = null;

            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
                
                // Inicializa la consulta base
                $queryCheck = "SELECT " . PROVEEDOR_ID . ", " . PROVEEDOR_ESTADO . " FROM " . TB_PROVEEDOR . " WHERE ";
                $params = [];
                $types = "";

                // Consulta para verificar si existe un Proveedor con el ID ingresado
                if ($proveedorID && (!$update && !$insert)) {
                    $queryCheck .= PROVEEDOR_ID . " = ? ";
                    $params[] = $proveedorID;
                    $types .= 'i';
                }

                else if ($insert && ($proveedorNombre && $proveedorEmail)) {
                    // Verificar existencia por nombre y email
                    $queryCheck .= PROVEEDOR_EMAIL . " = ? OR " . PROVEEDOR_NOMBRE . " = ? ";
                    $queryCheck .= "OR (" . PROVEEDOR_EMAIL . " = ? AND " . PROVEEDOR_NOMBRE . " = ?) ";
                    $params = [$proveedorEmail, $proveedorNombre, $proveedorEmail, $proveedorNombre];
                    $types .= 'ssss';
                }

                else if ($update && ($proveedorID && $proveedorNombre && $proveedorEmail)) {
                    // Verificar existencia por nombre y email
                    $queryCheck .= "(" . PROVEEDOR_EMAIL . " = ? OR " . PROVEEDOR_NOMBRE . " = ?) ";
                    $queryCheck .= "AND " . PROVEEDOR_ID . " <> ? ";
                    $params = [$proveedorEmail, $proveedorNombre, $proveedorID];
                    $types .= 'ssi';
                }

                else {
                    $missingParamsLog = "Faltan parámetros para verificar la existencia del proveedor:";
                    if (!$proveedorID) { $missingParamsLog .= " proveedorID [" . ($proveedorID ?? 'null') . "]"; }
                    if (!$proveedorNombre) { $missingParamsLog .= " proveedorNombre [" . ($proveedorNombre ?? 'null') . "]"; }
                    if (!$proveedorEmail) { $missingParamsLog .= " proveedorEmail [" . ($proveedorEmail ?? 'null') . "]"; }
                    Utils::writeLog($missingParamsLog, DATA_LOG_FILE, WARN_MESSAGE, $this->className);
                    throw new Exception("Faltan parámetros para verificar la existencia del proveedor.");
                }

                // Preparar la consulta y vincular los parámetros
                $stmt = mysqli_prepare($conn, $queryCheck);
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                // Verifica si existe algún registro con los criterios dados
                if ($row = mysqli_fetch_assoc($result)) {
                    // Verificar si está inactivo (bit de estado en 0)
                    $isInactive = $row[PROVEEDOR_ESTADO] == 0;
                    return ["success" => true, "exists" => true, "inactive" => $isInactive, "proveedorID" => $row[PROVEEDOR_ID]];
                }

                // Retorna false si no se encontraron resultados
                $messageParams = [];
                if ($proveedorID) { $messageParams[] = "'ID [$proveedorID]'"; }
                if ($proveedorNombre) { $messageParams[] = "'Nombre [$proveedorNombre]'"; }
                if ($proveedorEmail) { $messageParams[] = "'Email [$proveedorEmail]'"; }
                $params = implode(" y ", $messageParams);

                $message = "No se encontró ningún proveedor con $params en la base de datos.";
                return ["success" => true, "exists" => false, "message" => $message];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al verificar la existencia del proveedor en la base de datos',
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

        public function insertProveedor($proveedor, $conn = null) {
            $createdConn = false;
            $stmt = null;

            try {
                // Obtener el Nombre y el Email del proveedor
                $proveedorNombre = $proveedor->getProveedorNombre();
                $proveedorEmail = $proveedor->getProveedorEmail();

                // Verificar si el proveedor ya existe
                $check = $this->proveedorExiste(null, $proveedorNombre, $proveedorEmail, false, true);
                if (!$check["success"]) { return $check; } // Error al verificar la existencia

                // En caso de ya existir el proveedor, pero estar inactivo
                if ($check["exists"] && $check["inactive"]) {
                    $message = "Ya existe un proveedor con el mismo nombre ($proveedorNombre) y correo ($proveedorEmail) en la base de datos, ";
                    $message .= "pero está inactivo. ¿Desea reactivarlo?";
                    return ["success" => true, "message" => $message, "inactive" => $check["inactive"], "id" => $check["proveedorID"]];
                }

                // En caso de ya existir el proveedor y estar activo
                if ($check["exists"]) {
                    $message = "El proveedor con 'Nombre [$proveedorNombre]' y 'Email [$proveedorEmail]' ya existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => false, "message" => "Ya existe un proveedor con el mismo nombre y correo en la base de datos"];
                }

                // Si no se proporcionó una conexión, se crea una nueva
                if (is_null($conn)) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConn = true;

                    // Inicia una transacción si no se proporcionó una conexión
                    mysqli_begin_transaction($conn);
                }

                // Obtenemos el último ID de la tabla tbproducto
                $queryGetLastId = "SELECT MAX(" . PROVEEDOR_ID . ") FROM " . TB_PROVEEDOR;
                $idCont = mysqli_query($conn, $queryGetLastId);
                $nextId = 1;
        
                // Calcula el siguiente ID para la nueva entrada
                if ($row = mysqli_fetch_row($idCont)) {
                    $nextId = (int) trim($row[0]) + 1;
                }

                // Crea una consulta y un statement SQL para insertar el registro
                $queryInsert = 
                    "INSERT INTO " . TB_PROVEEDOR . " (" . 
                        PROVEEDOR_ID . ", " . 
                        PROVEEDOR_NOMBRE . ", " . 
                        PROVEEDOR_EMAIL . ", " . 
                        PROVEEDOR_CATEGORIA_ID . ") " . 
                    "VALUES (?, ?, ?, ?)"
                ;
                $stmt = mysqli_prepare($conn, $queryInsert);

                // Vincula los parámetros de la consulta con los datos del proveedor
                $proveedorCategoriaID = $proveedor->getProveedorCategoria()->getCategoriaID();
                mysqli_stmt_bind_param($stmt, 'issi', $nextId, $proveedorNombre, $proveedorEmail, $proveedorCategoriaID);
                mysqli_stmt_execute($stmt);

                // Insertar las direcciones del proveedor en la base de datos
                $direcciones = $proveedor->getProveedorDirecciones();
                foreach ($direcciones as $direccion) {
                    // Insertar la relación entre el proveedor y la dirección
                    $add = $this->proveedorDireccionData->addDireccionToProveedor($nextId, $direccion, $conn);
                    if (!$add["success"]) { throw new Exception($add["message"]); }
                }

                // Insertar los teléfonos del proveedor en la base de datos
                $telefonos = $proveedor->getProveedorTelefonos();
                foreach ($telefonos as $telefono) {
                    // Insertar la relación entre el proveedor y el teléfono
                    $add = $this->proveedorTelefonoData->addTelefonoToProveedor($nextId, $telefono, $conn);
                    if (!$add["success"]) { throw new Exception($add["message"]); }
                }

                // Confirmar la transacción si no se proporcionó una conexión
                if ($createdConn) { mysqli_commit($conn); }

                return ["success" => true, "message" => "Proveedor insertado exitosamente", "id" => $nextId];
            } catch (Exception $e) {
                // Hacer rollback si se creó una conexión y ocurrió un error
                if ($createdConn && isset($conn)) { mysqli_rollback($conn); }

                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al insertar al proveedor en la base de datos',
                    $this->className
                );
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if ($createdConn && isset($conn)) { mysqli_close($conn); }
            }
        }

        public function updateProveedor($proveedor, $conn = null) {
            $createdConn = false;
            $stmt = null;

            try {
                // Obtener el ID, Nombre y Email del proveedor
                $proveedorID = $proveedor->getProveedorID();
                $proveedorNombre = $proveedor->getProveedorNombre();
                $proveedorEmail = $proveedor->getProveedorEmail();

                // Verificar si el proveedor ya existe
                $check = $this->proveedorExiste($proveedorID);
                if (!$check["success"]) { throw new Exception($check['message']); } // Error al verificar la existencia

                // En caso de no existir el proveedor
                if (!$check["exists"]) {
                    $message = "No existe ningún proveedor en la base de datos con el 'ID [$proveedorID]'.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => false, "message" => "No existe ningún proveedor con el ID proporcionado"];
                }

                // Verificar si el proveedor ya existe con el mismo nombre y correo
                $check = $this->proveedorExiste($proveedorID, $proveedorNombre, $proveedorEmail, true);
                if (!$check["success"]) { throw new Exception($check['message']); } // Error al verificar la existencia

                // En caso de ya existir el proveedor
                if ($check["exists"]) {
                    $message = "Ya existe un proveedor con el mismo 'Nombre [$proveedorNombre}' y/o 'Correo [$proveedorEmail]' en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => false, "message" => "Ya existe un proveedor con el mismo nombre y/o correo en la base de datos"];
                }

                // Si no se proporcionó una conexión, se crea una nueva
                if (is_null($conn)) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConn = true;

                    // Inicia una transacción si no se proporcionó una conexión
                    mysqli_begin_transaction($conn);
                }

                // Crea una consulta y un statement SQL para actualizar el registro
                $queryUpdate = 
                    "UPDATE " . TB_PROVEEDOR . " SET " . 
                        PROVEEDOR_NOMBRE . " = ?, " . 
                        PROVEEDOR_EMAIL . " = ?, " . 
                        PROVEEDOR_CATEGORIA_ID . " = ?, " .
                        PROVEEDOR_ESTADO . " = TRUE " . 
                    "WHERE " . PROVEEDOR_ID . " = ?"
                ;
                $stmt = mysqli_prepare($conn, $queryUpdate);

                // Vincula los parámetros de la consulta con los datos del proveedor
                $proveedorCategoriaID = $proveedor->getProveedorCategoria()->getCategoriaID();
                mysqli_stmt_bind_param($stmt, 'ssii', $proveedorNombre, $proveedorEmail, $proveedorCategoriaID, $proveedorID);
                mysqli_stmt_execute($stmt);

                // Actualizar las direcciones del proveedor
                $direccion = $this->proveedorDireccionData->updateDireccionesProveedor($proveedor, $conn);
                if (!$direccion["success"]) { throw new Exception($direccion["message"]); }

                // Actualizar los teléfonos del proveedor
                $telefono = $this->proveedorTelefonoData->updateTelefonosProveedor($proveedor, $conn);
                if (!$telefono["success"]) { throw new Exception($telefono["message"]); }

                // Confirmar la transacción si no se proporcionó una conexión
                if ($createdConn) { mysqli_commit($conn); }

                // Devuelve un mensaje de éxito
                return ["success" => true, "message" => "Proveedor actualizado exitosamente"];
            } catch (Exception $e) {
                // Hacer rollback si se creó una conexión y ocurrió un error
                if ($createdConn && isset($conn)) { mysqli_rollback($conn); }

                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al actualizar el proveedor en la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if ($createdConn && isset($conn)) { mysqli_close($conn); }
            }
        }

        public function deleteProveedor($proveedorID, $conn = null) {
            $createdConn = false;
            $stmt = null;

            try {
                // Verificar si el proveedor ya existe
                $check = $this->proveedorExiste($proveedorID);
                if (!$check["success"]) { return $check; } // Error al verificar la existencia

                // En caso de no existir el proveedor
                if (!$check["exists"]) {
                    $message = "No existe ningún proveedor en la base de datos con el 'ID [$proveedorID]'.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => false, "message" => "No existe ningún proveedor con el ID proporcionado"];
                }

                // Si no se proporcionó una conexión, se crea una nueva
                if (is_null($conn)) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConn = true;

                    // Inicia una transacción si no se proporcionó una conexión
                    mysqli_begin_transaction($conn);
                }

                // Crea una consulta y un statement SQL para eliminar el registro
                $queryDelete = 
                    "UPDATE " . TB_PROVEEDOR . " SET " . 
                        PROVEEDOR_ESTADO . " = FALSE " . 
                    "WHERE " . PROVEEDOR_ID . " = ?"
                ;
                $stmt = mysqli_prepare($conn, $queryDelete);
                mysqli_stmt_bind_param($stmt, 'i', $proveedorID);
                mysqli_stmt_execute($stmt);

                // Obtener las direcciones del proveedor
                $direcciones = $this->proveedorDireccionData->getDireccionesByProveedorID($proveedorID);
                if (!$direcciones["success"]) { throw new Exception($direcciones["message"]); }

                // Eliminar las direcciones del proveedor
                foreach ($direcciones["direcciones"] as $direccion) {
                    $direccionID = $direccion->getDireccionID();
                    $delete = $this->proveedorDireccionData->removeDireccionFromProveedor($proveedorID, $direccionID, $conn);
                    if (!$delete["success"]) { throw new Exception($delete["message"]); }
                }

                // Obtener los teléfonos del proveedor
                $telefonos = $this->proveedorTelefonoData->getTelefonosByProveedorID($proveedorID);
                if (!$telefonos["success"]) { throw new Exception($telefonos["message"]); }

                // Eliminar los teléfonos del proveedor
                foreach ($telefonos["telefonos"] as $telefono) {
                    $telefonoID = $telefono->getTelefonoID();
                    $delete = $this->proveedorTelefonoData->removeTelefonoFromProveedor($proveedorID, $telefonoID, $conn);
                    if (!$delete["success"]) { throw new Exception($delete["message"]); }
                }

                // Confirmar la transacción si no se proporcionó una conexión
                if ($createdConn) { mysqli_commit($conn); }

                // Devuelve un mensaje de éxito
                return ["success" => true, "message" => "Proveedor eliminado exitosamente"];
            } catch (Exception $e) {
                // Hacer rollback si se creó una conexión y ocurrió un error
                if ($createdConn && isset($conn)) { mysqli_rollback($conn); }

                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al eliminar el proveedor en la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if ($createdConn && isset($conn)) { mysqli_close($conn); }
            }
        }

        public function getAllTBProveedor($onlyActive = false, $deleted = false) {
            $conn = null;

            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Consulta SQL para obtener todos los registros de la tabla tbproveedor
                $querySelect = "SELECT * FROM " . TB_PROVEEDOR;
                if ($onlyActive) { $querySelect .= " WHERE " . PROVEEDOR_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE'); }
                $result = mysqli_query($conn, $querySelect);

                // Crear una lista con todos los proveedores
                $proveedores = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $categoria = $this->categoriaData->getCategoriaByID($row[PROVEEDOR_CATEGORIA_ID], false);
                    if (!$categoria["success"]) { throw new Exception($categoria["message"]); }

                    $direccion = $this->proveedorDireccionData->getDireccionesByProveedorID($row[PROVEEDOR_ID]);
                    if (!$direccion["success"]) { throw new Exception($direccion["message"]); }

                    $telefono = $this->proveedorTelefonoData->getTelefonosByProveedorID($row[PROVEEDOR_ID]);
                    if (!$telefono["success"]) { throw new Exception($telefono["message"]); }

                    // Crea un objeto Proveedor con los datos obtenidos
                    $proveedor = new Proveedor(
                        $row[PROVEEDOR_ID],
                        $row[PROVEEDOR_NOMBRE],
                        $row[PROVEEDOR_EMAIL],
                        $direccion["direcciones"],
                        $categoria["categoria"],
                        [], // Productos
                        $telefono["telefonos"],
                        $row[PROVEEDOR_FECHA_CREACION],
                        $row[PROVEEDOR_FECHA_MODIFICACION],
                        $row[PROVEEDOR_ESTADO]
                    );
                    $proveedores[] = $proveedor;
                }

                // Devuelve la lista de proveedores
                return ["success" => true, "proveedores" => $proveedores];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener la lista de proveedores desde la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión
                if (isset($conn)) { mysqli_close($conn); }
            }
        }
        
        public function getPaginatedProveedores($search, $page, $size, $sort = null, $onlyActive = false, $deleted = false) {
            $conn = null; $stmt = null;

            try {
                // Calcular el offset y el total de páginas
                $offset = ($page - 1) * $size;
        
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

				// Consultar el total de registros
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_PROVEEDOR;
                if ($onlyActive) { $queryTotalCount .= " WHERE " . PROVEEDOR_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE'); }
                
                // Obtener el total de registros y calcular el total de páginas
                $totalResult = mysqli_query($conn, $queryTotalCount);
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = (int) $totalRow['total'];
                $totalPages = ceil($totalRecords / $size);

				// Construir la consulta SQL para paginación
                $querySelect = "
                    SELECT
                        P.*, C." . CATEGORIA_NOMBRE . "
                    FROM " . 
                        TB_PROVEEDOR . " P
                    INNER JOIN " .
                        TB_CATEGORIA . " C ON P." . PROVEEDOR_CATEGORIA_ID . " = C." . CATEGORIA_ID
                ;

                // Agregar filtro de búsqueda a la consulta
                $params = [];
                $types = "";
                if ($search) {
                    $querySelect .= " WHERE (" . PROVEEDOR_NOMBRE . " LIKE ?";
                    $querySelect .= " OR " . PROVEEDOR_EMAIL . " LIKE ?";
                    $querySelect .= " OR C." . CATEGORIA_NOMBRE . " LIKE ?)";
                    $params = array_fill(0, 3, "%$search%");
                    $types = "sss";
                }

                // Agregar filtro de estado a la consulta
                if ($onlyActive) {
                    $querySelect .= ($search ? " AND " : " WHERE ") . "P." . PROVEEDOR_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE');
                }

				// Agregar ordenamiento a la consulta
                if ($sort) {
                    $querySelect .= " ORDER BY " . ($sort == 'categoria' ? "C." . CATEGORIA_NOMBRE : "P.proveedor" . $sort);
                } else {
                    $querySelect .= " ORDER BY P." . PROVEEDOR_ID . " DESC";
                }

				// Añadir la cláusula de limitación y offset
                $querySelect .= " LIMIT ? OFFSET ?";
                $params = array_merge($params, [$size, $offset]);
                $types .= "ii";

                // Preparar la consulta y vincular los parámetros
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);

				// Obtener el resultado
                $result = mysqli_stmt_get_result($stmt);

				$proveedores = [];
				while ($row = mysqli_fetch_assoc($result)) {
                    $categoriaData = new CategoriaData();
                    $categoria = $categoriaData->getCategoriaByID($row[PROVEEDOR_CATEGORIA_ID], false);
                    if (!$categoria["success"]) { throw new Exception($categoria["message"]); }

                    $direccion = $this->proveedorDireccionData->getDireccionesByProveedorID($row[PROVEEDOR_ID]);
                    if (!$direccion["success"]) { throw new Exception($direccion["message"]); }

                    $telefono = $this->proveedorTelefonoData->getTelefonosByProveedorID($row[PROVEEDOR_ID]);
                    if (!$telefono["success"]) { throw new Exception($telefono["message"]); }

                    // Crea un objeto Proveedor con los datos obtenidos
                    $proveedor = new Proveedor(
                        $row[PROVEEDOR_ID],
                        $row[PROVEEDOR_NOMBRE],
                        $row[PROVEEDOR_EMAIL],
                        $direccion["direcciones"],
                        $categoria["categoria"],
                        [], // Productos
                        $telefono["telefonos"],
                        $row[PROVEEDOR_FECHA_CREACION],
                        $row[PROVEEDOR_FECHA_MODIFICACION],
                        $row[PROVEEDOR_ESTADO]
                    );
                    $proveedores[] = $proveedor;
				}

				return [
                    "success" => true,
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "proveedores" => $proveedores
                ];
			} catch (Exception $e) {
				// Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener la lista de proveedores desde la base de datos',
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

        public function getProveedorByID($proveedorID, $onlyActive = false, $deleted = false) {
            $conn = null; $stmt = null;

            try {
                // Verificar si el proveedor ya existe
                $check = $this->proveedorExiste($proveedorID);
                if (!$check["success"]) { return $check; } // Error al verificar la existencia

                // En caso de no existir el proveedor
                if (!$check["exists"]) {
                    $message = "No existe ningún proveedor en la base de datos con el 'ID [$proveedorID]'.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => false, "message" => "No existe ningún proveedor con el ID proporcionado"];
                }

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Consulta SQL para obtener el proveedor con el ID proporcionado
                $querySelect = "
                    SELECT 
                        * 
                    FROM " . 
                        TB_PROVEEDOR . " 
                    WHERE " . 
                        PROVEEDOR_ID . " = ?" . ($onlyActive ? " AND " . 
                        PROVEEDOR_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE') : "");
                $stmt = mysqli_prepare($conn, $querySelect);

                // Vincula los parámetros de la consulta con los datos del proveedor
                mysqli_stmt_bind_param($stmt, 'i', $proveedorID);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                // Obtener el resultado de la consulta
                if ($row = mysqli_fetch_assoc($result)) {
                    $categoria = $this->categoriaData->getCategoriaByID($row[PROVEEDOR_CATEGORIA_ID], false);
                    if (!$categoria["success"]) { throw new Exception($categoria["message"]); }

                    $direccion = $this->proveedorDireccionData->getDireccionesByProveedorID($row[PROVEEDOR_ID]);
                    if (!$direccion["success"]) { throw new Exception($direccion["message"]); }

                    $telefono = $this->proveedorTelefonoData->getTelefonosByProveedorID($row[PROVEEDOR_ID]);
                    if (!$telefono["success"]) { throw new Exception($telefono["message"]); }

                    // Crea un objeto Proveedor con los datos obtenidos
                    $proveedor = new Proveedor(
                        $row[PROVEEDOR_ID],
                        $row[PROVEEDOR_NOMBRE],
                        $row[PROVEEDOR_EMAIL],
                        $direccion["direcciones"],
                        $categoria["categoria"],
                        [], // Productos
                        $telefono["telefonos"],
                        $row[PROVEEDOR_FECHA_CREACION],
                        $row[PROVEEDOR_FECHA_MODIFICACION],
                        $row[PROVEEDOR_ESTADO]
                    );

                    // Devuelve el proveedor encontrado
                    return ["success" => true, "proveedor" => $proveedor];
                }

                // Retorna false si no se encontraron resultados
                $message = "No se encontró ningún proveedor con el 'ID [$proveedorID]' en la base de datos.";
                Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ["success" => true, "message" => "No se encontró ningún el proveedor en la base de datos"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener el proveedor desde la base de datos',
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
        
        public function getCompraProveedorByID($proveedorID) {
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
                        " . PROVEEDOR_ID . ", 
                        " . PROVEEDOR_NOMBRE . " 
                    FROM " . TB_PROVEEDOR . " 
                    WHERE " . PROVEEDOR_ID . " = ?";
                $stmt = mysqli_prepare($conn, $querySelect);
        
                // Vincula los parámetros de la consulta
                mysqli_stmt_bind_param($stmt, 'i', $proveedorID);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
        
              // Obtener el resultado de la consulta
        if ($row = mysqli_fetch_assoc($result)) {
            // Retorna una instancia de Proveedor
            return new Proveedor($row[PROVEEDOR_ID], $row[PROVEEDOR_NOMBRE]);
        }
        
                // Retorna false si no se encontraron resultados
                return [
                    "success" => false,
                    "message" => "No se encontró ningún proveedor con el ID proporcionado."
                ];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener el proveedor desde la base de datos',
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
