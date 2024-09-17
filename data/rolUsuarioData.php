<?php

    require_once 'data.php';
    require_once __DIR__ . '/../domain/RolUsuario.php';
    require_once __DIR__ . '/../utils/Variables.php';
    require_once __DIR__ . '/../utils/Utils.php';

    class RolData extends Data {

        private $className;

        public function __construct() {
            $this->className = get_class($this);
            parent::__construct();
        }

        public function existeRolUsuario($rolID = null, $rolNombre = null, $update = false, $insert = false) {
            $conn = null; $stmt = null;

            try {
                // Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) { throw new Exception($result["message"]); }
				$conn = $result["connection"];

                // Inicializa la consulta base
				$queryCheck = "SELECT " . ROL_ID . ", " . ROL_ESTADO . " FROM " . TB_ROL . " WHERE ";
				$params = [];
				$types = "";

                // Consulta para verificar si existe un rol con el ID ingresado
                if ($rolID && (!$update && !$insert)) {
                    $queryCheck .= ROL_ID . " = ? ";
                    $params[] = $rolID;
                    $types .= "i";
                }

                // Consulta para verificar si existe un rol con el nombre ingresado
                else if ($insert && $rolNombre) {
                    $queryCheck .= ROL_NOMBRE . " = ? ";
                    $params[] = $rolNombre;
                    $types .= "s";
                }

                // Consulta en caso de actualizar para verificar si existe ya un rol con el mismo nombre además del que se va a actualizar
                else if ($update && ($rolID && $rolNombre)) {
                    $queryCheck .= ROL_NOMBRE . " = ? AND " . ROL_ID . " != ? ";
                    $params = [$rolNombre, $rolID];
                    $types .= "si";
                }

                // En caso de no cumplirse ninguna condicion
                else {
                    // Registrar parámetros faltantes y lanzar excepción
                    $missingParamsLog = "Faltan parámetros para verificar la existencia del rol:";
                    if (!$rolID) $missingParamsLog .= " rolID [" . ($rolID ?? 'null') . "]";
                    if (!$rolNombre) $missingParamsLog .= " rolNombre [" . ($rolNombre ?? 'null') . "]";
                    Utils::writeLog($missingParamsLog, DATA_LOG_FILE, WARN_MESSAGE, $this->className);
                    throw new Exception("Faltan parámetros para verificar la existencia del rol en la base de datos.");
                }

                // Asignar los parámetros y ejecutar la consulta
                $stmt = mysqli_prepare($conn, $queryCheck);
				mysqli_stmt_bind_param($stmt, $types, ...$params);
				mysqli_stmt_execute($stmt);
				$result = mysqli_stmt_get_result($stmt);

                // Verificar si existe un rol con el ID o nombre ingresado
                if ($row = mysqli_fetch_assoc($result)) {
                    // Verificar si está inactivo (bit de estado en 0)
                    $isInactive = $row[ROL_ESTADO] == 0;
                    return ["success" => true, "exists" => true, "inactive" => $isInactive, "rolID" => $row[ROL_ID]];
                }

                // Retorna false si no se encontraron resultados
                $messageParams = [];
                if ($rolID) { $messageParams[] = "'ID [$rolID]'"; }
                if ($rolNombre)  { $messageParams[] = "'Nombre [$rolNombre]'"; }
                $params = implode(', ', $messageParams);

                $message = "No se encontró ningún rol ($params) en la base de datos.";
                return ["success" => true, "exists" => false, "message" => $message];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al verificar la existencia del rol en la base de datos',
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

        public function insertRolUsuario($rol) {
            $conn = null; $stmt = null;

            try {
                // Obtener los datos del rol
                $rolNombre = $rol->getRolNombre();

                // Verificar si ya existe un rol con el nombre ingresado
                $result = $this->existeRolUsuario(null, $rolNombre, false, true);
                if (!$result["success"]) { throw new Exception($result["message"]); }

                // En caso de ya existir el rol pero estar inactivo
                if ($result["exists"] && $result["inactive"]) { 
                    $message = "Ya existe un rol con el mismo nombre ($rolNombre) en la base de datos, pero está inactivo. Desea reactivarlo?";
                    return ["success" => true, "message" => $message, "inactive" => $result["inactive"], "id" => $result["rolID"]];
                }

                // En caso de ya existir el rol y estar activo
                if ($result["exists"]) {
                    $message = "El rol con 'Nombre [$rolNombre]' ya existe en la base de datos.";
					Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => true, "message" => "Ya existe un rol con el mismo nombre ($rolNombre) en la base de datos."];
                }

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Obtenemos el último ID de la tabla tbrolusuario
				$queryGetLastId = "SELECT MAX(" . ROL_ID . ") FROM " . TB_ROL;
				$idCont = mysqli_query($conn, $queryGetLastId);
				$nextId = 1;

                // Calcula el siguiente ID para la nueva entrada
				if ($row = mysqli_fetch_row($idCont)) {
					$nextId = (int) trim($row[0]) + 1;
				}

                // Crea una consulta y un statement SQL para insertar el nuevo registro
                $queryInsert = 
                    "INSERT INTO " . TB_ROL . " ("
                        . ROL_ID . ", "
                        . ROL_NOMBRE . ", "
                        . ROL_DESCRIPCION . 
                    ") VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($conn, $queryInsert);

                // Obtener los valores de las propiedades faltantes
                $rolNombre = $rol->getRolNombre();
                $rolDescripcion = $rol->getRolDescripcion();

                // Asigna los valores a cada '?' de la consulta
                mysqli_stmt_bind_param($stmt, "iss", $nextId, $rolNombre, $rolDescripcion);
                mysqli_stmt_execute($stmt);

                return ["success" => true, "message" => "Rol insertado exitosamente"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al insertar el rol en la base de datos',
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

        public function updateRolUsuario($rol) {
            $conn = null; $stmt = null;

            try {
                // Obtener los datos del Rol
                $rolID = $rol->getRolID();
                $rolNombre = $rol->getRolNombre();

                // Verificar si el rol a actualizar existe en la base de datos
                $result = $this->existeRolUsuario($rolID);
                if (!$result["success"]) { return $result["message"]; }

                // En caso de no existir el rol
                if (!$result["exists"]) {
                    $message = "No se encontró el rol con 'ID [$rolID]' en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => true, "message" => "El rol seleccionado no existe en la base de datos."];
                }

                // Verifica que no exista otro rol con la misma información
                $result = $this->existeRolUsuario($rolID, $rolNombre, true);
                if (!$result["success"]) { return $result["message"]; }

                // En caso de ya existir el rol
                if ($result["exists"]) {
                    $message = "Ya existe un rol con el mismo 'Nombre [$rolNombre]' en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => true, "message" => "Ya existe un rol con el mismo nombre ($rolNombre) en la base de datos."];
                }

                // Establece una conexion con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) { throw new Exception($result["message"]); }
				$conn = $result["connection"];

                // Crea una consulta y un statement SQL para actualizar el registro
                $queryUpdate = 
                    "UPDATE " . TB_ROL . " SET "
                        . ROL_NOMBRE . " = ?, "
                        . ROL_DESCRIPCION . " = ?, "
                        . ROL_ESTADO . " = TRUE "
                    . "WHERE " . ROL_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryUpdate);

                // Obtener los valores de las propiedades faltantes
                $rolNombre = $rol->getRolNombre();
                $rolDescripcion = $rol->getRolDescripcion();

                // Asigna los valores a cada '?' de la consulta
                mysqli_stmt_bind_param($stmt, "ssi", $rolNombre, $rolDescripcion, $rolID);
                mysqli_stmt_execute($stmt);

                return ["success" => true, "message" => "Rol actualizado exitosamente"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al actualizar el rol en la base de datos',
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

        public function deleteRolUsuario($rolID) {
            $conn = null; $stmt = null;

            try {
                // Verificar si el rol a eliminar existe en la base de datos
                $result = $this->existeRolUsuario($rolID);
                if (!$result["success"]) { return $result["message"]; }

                // En caso de no existir el rol
                if (!$result["exists"]) {
                    $message = "No se encontró el rol con 'ID [$rolID]' en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => true, "message" => "El rol seleccionado no existe en la base de datos."];
                }

                // Establece una conexion con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Crea una consulta y un statement SQL para eliminar el registro
                $queryDelete = "UPDATE " . TB_ROL . " SET " . ROL_ESTADO . " = FALSE WHERE " . ROL_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryDelete);
                mysqli_stmt_bind_param($stmt, "i", $rolID);
                mysqli_stmt_execute($stmt);

                // Devuelve un mensaje de éxito
                return ["success" => true, "message" => "Rol eliminado exitosamente"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al eliminar el rol en la base de datos',
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

        public function getAllTBRolUsuario($onlyActiveOrInactive = false, $deleted = false) {
            $conn = null;

            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Inicializa la consulta base
                $querySelect = "SELECT * FROM " . TB_ROL;
                if ($onlyActiveOrInactive) { $querySelect .= " WHERE " . ROL_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE"); }
                $result = mysqli_query($conn, $querySelect);

                // Crear un array con los roles obtenidos
                $roles = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $roles [] = [
                        "ID" => $row[ROL_ID],
                        "Nombre" => $row[ROL_NOMBRE],
                        "Descripcion" => $row[ROL_DESCRIPCION],
                        "Estado" => $row[ROL_ESTADO]
                    ];
                }

                return ["success" => true, "roles" => $roles];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener los roles de la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cerrar la conexión y liberar recursos
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function getPaginatedRoles($page, $size, $sort = null, $onlyActiveOrInactive = false, $deleted = false) {
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
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_ROL . " ";
                if ($onlyActiveOrInactive) { $queryTotalCount .= " WHERE " . ROL_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE") . " "; }

                // Ejecutar la consulta y obtener el total de registros
                $totalResult = mysqli_query($conn, $queryTotalCount);
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = (int) $totalRow['total'];
                $totalPages = ceil($totalRecords / $size);

                // Construir la consulta SQL para paginación
                $querySelect = "SELECT * FROM " . TB_ROL . " ";
                if ($onlyActiveOrInactive) { $querySelect .= " WHERE " . ROL_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE") . " "; }

                // Agregar ordenamiento si se especifica
                if ($sort) { $querySelect .= " ORDER BY rolusuario" . $sort . " "; }

                // Agregar límite y desplazamiento
                $querySelect .= " LIMIT ? OFFSET ?";

                // Preparar la consulta y ejecutarla
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, "ii", $size, $offset);
                mysqli_stmt_execute($stmt);

                // Obtener los resultados de la consulta
                $result = mysqli_stmt_get_result($stmt);

                // Crear un array con los roles obtenidos
                $roles = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $roles[] = [
                        "ID" => $row[ROL_ID],
                        "Nombre" => $row[ROL_NOMBRE],
                        "Descripcion" => $row[ROL_DESCRIPCION],
                        "Estado" => $row[ROL_ESTADO]
                    ];
                }

                return [
                    "success" => true,
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "roles" => $roles
                ];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener la lista de roles desde la base de datos',
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

        public function getRolByID($rolID, $json = true) {
            $conn = null; $stmt = null;

            try {
                // Verifica si el rol existe en la base de datos
                $result = $this->existeRolUsuario($rolID);
                if (!$result["success"]) { return $result["message"]; }

                // En caso de no existir el rol
                if (!$result["exists"]) {
                    $message = "No se encontró el rol con 'ID [$rolID]' en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => true, "message" => "El rol seleccionado no existe en la base de datos."];
                }

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Crea una consulta y un statement SQL para obtener el registro
                $querySelect = "SELECT * FROM " . TB_ROL . " WHERE " . ROL_ID . " = ? AND " . ROL_ESTADO . " != FALSE";
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, "i", $rolID);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                // Obtener los resultados de la consulta
                if (mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    $rol = null;
                    if ($json) {
                        $rol = [
                            "ID" => $row[ROL_ID],
                            "Nombre" => $row[ROL_NOMBRE],
                            "Descripcion" => $row[ROL_DESCRIPCION],
                            "Estado" => $row[ROL_ESTADO]
                        ];
                    } else {
                        $rol = new RolUsuario(
                            $row[ROL_ID],
                            $row[ROL_NOMBRE],
                            $row[ROL_DESCRIPCION],
                            $row[ROL_ESTADO]
                        );
                    }

                    return ["success" => true, "rol" => $rol];
                }
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener el rol desde la base de datos',
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