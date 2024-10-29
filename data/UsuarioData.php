<?php

    require_once dirname(__DIR__, 1) . '/data/data.php';
    require_once dirname(__DIR__, 1) . '/data/rolUsuarioData.php';
    require_once dirname(__DIR__, 1) . '/domain/Usuario.php';
    require_once dirname(__DIR__, 1) . '/domain/RolUsuario.php';

    class UsuarioData extends Data {

        private $className;

        public function __construct() {
            $this->className = get_class($this);
            parent::__construct();
        }

        public function existeUsuario($usuarioID = null, $usuarioEmail = null, $update = false, $insert = false) {
            $conn = null; $stmt = null;

            try {
                // Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) { throw new Exception($result["message"]); }
				$conn = $result["connection"];

                // Inicializa la consulta base
				$queryCheck = "SELECT " .  USUARIO_ID . ", " . USUARIO_ESTADO . ", " . USUARIO_PASSWORD . " FROM " . TB_USUARIO . " WHERE ";
				$params = [];
				$types = "";

                // Consulta para verificar si existe un usuario con el ID ingresado
                if ($usuarioID && (!$update && !$insert)) {
                    $queryCheck .= USUARIO_ID . " = ? ";
                    $params = [$usuarioID];
                    $types .= "i";
                }

                // Consulta para verificar si existe un usuario con el correo ingresado
                else if ($usuarioEmail && (!$update && !$insert)) {
                    $queryCheck .= USUARIO_EMAIL . " = ? ";
                    $params = [$usuarioEmail];
                    $types .= "s";
                }

                // Consulta en caso de insertar para verificar si existe un usuario con el correo ingresado
                else if ($insert && $usuarioEmail) {
                    $queryCheck .= USUARIO_EMAIL . " = ? ";
                    $params = [$usuarioEmail];
                    $types .= "s";
                }

                // Consulta en caso de actualizar para verificar si existe ya un usuario con el mismo email además del que se va a actualizar
                else if ($update && ($usuarioID && $usuarioEmail)) {
                    $queryCheck .= USUARIO_EMAIL . " = ? AND " . USUARIO_ID . " != ? ";
                    $params = [$usuarioEmail, $usuarioID];
                    $types .= "si";
                }

                // En caso de no cumplirse ninguna condicion
                else {
                    // Registrar parámetros faltantes y lanzar excepción
                    $missingParamsLog = "Faltan parámetros para verificar la existencia del usuario:";
                    if (!$usuarioID) $missingParamsLog .= " usuarioID [" . ($usuarioID ?? 'null') . "]";
                    if (!$usuarioEmail) $missingParamsLog .= " usuarioEmail [" . ($usuarioEmail ?? 'null') . "]";
                    Utils::writeLog($missingParamsLog, DATA_LOG_FILE, WARN_MESSAGE, $this->className);
                    throw new Exception("Faltan parámetros para verificar la existencia del usuario.");
                }

                // Asignar los parámetros y ejecutar la consulta
                $stmt = mysqli_prepare($conn, $queryCheck);
				mysqli_stmt_bind_param($stmt, $types, ...$params);
				mysqli_stmt_execute($stmt);
				$result = mysqli_stmt_get_result($stmt);

                // Verificar si el usuario existe
                if ($row = mysqli_fetch_assoc($result)) {
                    // Verificar si está inactivo (bit de estado en 0)
                    $isInactive = $row[USUARIO_ESTADO] == 0;
                    return [
                        "success" => true, "exists" => true, 
                        "inactive" => $isInactive, "usuarioID" => $row[USUARIO_ID],
                        "password" => $row[USUARIO_PASSWORD]
                    ];
                }

                // Retorna false si no se encontraron resultados
                $messageParams = [];
                if ($usuarioID) { $messageParams[] = "'ID [$usuarioID]'"; }
                if ($usuarioEmail)  { $messageParams[] = "'Email [$usuarioEmail]'"; }
                $params = implode(', ', $messageParams);

                $message = "No se encontró ningún usuario ($params) en la base de datos.";
                return ["success" => true, "exists" => false, "message" => $message];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al verificar la existencia del usuario en la base de datos',
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

        public function insertUsuario($usuario) {
            $conn = null; $stmt = null;

            try {
                // Obtener los valores de las propiedades del objeto Usuario
                $usuarioEmail = $usuario->getUsuarioEmail();

                // Verificar si el usuario ya existe en la base de datos
                $check = $this->existeUsuario(null, $usuarioEmail, false, true);
                if (!$check["success"]) { return $check; }

                // En caso de que el usuario ya exista, pero esté inactivo
                if ($check["exists"] && $check["inactive"]) {
                    $message = "Ya existe un usuario con el mismo correo ($usuarioEmail) en la base de datos, pero está inactivo. Desea reactivarlo?";
                    return ["success" => true, "message" => $message, "inactive" => $check["inactive"], "id" => $check["usuarioID"]];
                }
                
                // En caso de que el usuario ya exista
                if ($check["exists"]) {
                    $message = "El usuario con 'Correo [$usuarioEmail]' ya existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => false, "message" => "Ya existe un usuario con el mismo correo en la base de datos."];
                }

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Obtenemos el último ID de la tabla tbusuario
                $queryGetLastId = "SELECT MAX(" . USUARIO_ID . ") FROM " . TB_USUARIO;
                $idCont = mysqli_query($conn, $queryGetLastId);
                $nextId = 1;

                // Calcula el siguiente ID para la nueva entrada
				if ($row = mysqli_fetch_row($idCont)) {
					$nextId = (int) trim($row[0]) + 1;
				}

                // Crea una consulta y un statement SQL para insertar el nuevo registro
                $queryInsert = 
                    "INSERT INTO " . TB_USUARIO . " ("
                        . USUARIO_ID . ", "
                        . ROL_ID . ", "
                        . USUARIO_NOMBRE . ", "
                        . USUARIO_APELLIDO_1 . ", "
                        . USUARIO_APELLIDO_2 . ", "
                        . USUARIO_EMAIL . ", "
                        . USUARIO_PASSWORD . 
                    ") VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $queryInsert);

                // Obtener los valores de las propiedades faltantes
                $usuarioNombre = $usuario->getUsuarioNombre();
                $usuarioApellido1 = $usuario->getUsuarioApellido1();
                $usuarioApellido2 = $usuario->getUsuarioApellido2();
                $usuarioRolID = $usuario->getUsuarioRolUsuario()->getRolID();
                $usuarioPassword = password_hash($usuario->getUsuarioPassword(), PASSWORD_BCRYPT);

                // Asigna los valores a cada '?' de la consulta
                mysqli_stmt_bind_param(
                    $stmt, 
                    "iisssss", 
                    $nextId, 
                    $usuarioRolID,
                    $usuarioNombre, 
                    $usuarioApellido1, 
                    $usuarioApellido2, 
                    $usuarioEmail, 
                    $usuarioPassword
                );

                // Ejecuta la consulta de inserción
                mysqli_stmt_execute($stmt);
                return ["success" => true, "message" => "Usuario insertado exitosamente"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al insertar el usuario en la base de datos',
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

        public function updateUsuario($usuario) {
            $conn = null; $stmt = null;

            try {
                // Obtener los valores de las propiedades del objeto Usuario
                $usuarioID = $usuario->getUsuarioID();
                $usuarioEmail = $usuario->getUsuarioEmail();

                // Verificar si el usuario ya existe en la base de datos
                $check = $this->existeUsuario($usuarioID);
                if (!$check["success"]) { return $check; }

                // En caso de que el usuario no exista
                if (!$check["exists"]) {
                    $message = "El usuario con 'ID [$usuarioID]' no existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => false, "message" => "El usuario seleccionado no existe en la base de datos."];
                }
                
                // Obtener la contraseña actual del usuario
                $passwordActual = $check["password"];
                $usuarioPassword = $usuario->getUsuarioPassword();

                // Si la contraseña es diferente y no está en blanco, hacerle hash (encriptar)
                if (!empty($usuarioPassword) && ($usuarioPassword != $passwordActual)) {
                    $usuarioPassword = password_hash($usuarioPassword, PASSWORD_BCRYPT);
                } else {
                    // Mantener la contraseña actual si no se cambia
                    $usuarioPassword = $passwordActual;
                }

                // Verifica que no exista otro usuario con la misma información
                $check = $this->existeUsuario($usuarioID, $usuarioEmail, true);
                if (!$check["success"]) { return $check; }

                // En caso de que el usuario exista
                if ($check["exists"]) {
                    $message = "El usuario con 'Correo [$usuarioEmail]' ya existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => false, "message" => "Ya existe un usuario con el mismo correo ($usuarioEmail) en la base de datos."];
                }

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Crea una consulta y un statement SQL para actualizar el registro
                $queryUpdate = 
                    "UPDATE " . TB_USUARIO . " SET "
                        . ROL_ID . " = ?, "
                        . USUARIO_NOMBRE . " = ?, "
                        . USUARIO_APELLIDO_1 . " = ?, "
                        . USUARIO_APELLIDO_2 . " = ?, "
                        . USUARIO_EMAIL . " = ?, "
                        . USUARIO_PASSWORD . " = ?, "
                        . USUARIO_ESTADO . " = TRUE "
                    . "WHERE " . USUARIO_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryUpdate);

                // Obtener los valores de las propiedades faltantes
                $usuarioNombre = $usuario->getUsuarioNombre();
                $usuarioApellido1 = $usuario->getUsuarioApellido1();
                $usuarioApellido2 = $usuario->getUsuarioApellido2();
                $usuarioRolID = $usuario->getUsuarioRolUsuario()->getRolID();

                // Asigna los valores a cada '?' de la consulta
                mysqli_stmt_bind_param(
                    $stmt, 
                    "isssssi", 
                    $usuarioRolID, 
                    $usuarioNombre, 
                    $usuarioApellido1, 
                    $usuarioApellido2, 
                    $usuarioEmail, 
                    $usuarioPassword, 
                    $usuarioID
                );

                // Ejecuta la consulta de actualización
                mysqli_stmt_execute($stmt);

                // Devuelve un mensaje de éxito
                return ["success" => true, "message" => "Usuario actualizado exitosamente"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al actualizar el usuario en la base de datos',
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

        public function deleteUsuario($usuarioID) {
            $conn = null; $stmt = null;

            try {
                // Verificar si el usuario ya existe en la base de datos
                $check = $this->existeUsuario($usuarioID);
                if (!$check["success"]) { return $check; }

                // En caso de que el usuario no exista
                if (!$check["exists"]) {
                    $message = "El usuario con 'ID [$usuarioID]' no existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => false, "message" => "El usuario seleccionado no existe en la base de datos."];
                }

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Crea una consulta y un statement SQL para eliminar el registro
                $queryDelete = "UPDATE " . TB_USUARIO . " SET " . USUARIO_ESTADO . " = FALSE WHERE " . USUARIO_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryDelete);
                mysqli_stmt_bind_param($stmt, "i", $usuarioID);
                mysqli_stmt_execute($stmt);

                // Devuelve un mensaje de éxito
                return ["success" => true, "message" => "Usuario eliminado exitosamente"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al eliminar el usuario en la base de datos',
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

        public function getAllTBUsuario($onlyActive = false, $deleted = false) {
            $conn = null;

            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Inicializa la consulta base
                $querySelect = "SELECT * FROM " . TB_USUARIO;
                if ($onlyActive) { $querySelect .= " WHERE " . USUARIO_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE"); }
                $result = mysqli_query($conn, $querySelect);

                // Crea un array para almacenar los usuarios
                $usuarios = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    // Obtiene el rol del usuario
                    $rolData = new RolData();
                    $rolUsuario = $rolData->getRolByID($row[ROL_ID], false);
                    if (!$rolUsuario["success"]) { throw new Exception($rolUsuario["message"]); }

                    // Crea un objeto Usuario con los datos obtenidos
                    $usuario = new Usuario(
                        $row[USUARIO_ID],
                        $row[USUARIO_NOMBRE],
                        $row[USUARIO_APELLIDO_1],
                        $row[USUARIO_APELLIDO_2],
                        $row[USUARIO_EMAIL],
                        $row[USUARIO_PASSWORD],
                        $rolUsuario["rol"],
                        $row[USUARIO_CREACION],
                        $row[USUARIO_MODIFICACION],
                        $row[USUARIO_ESTADO]
                    );
                    $usuarios[] = $usuario;
                }

                // Devuelve un mensaje de éxito
                return ["success" => true, "usuarios" => $usuarios];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al obtener los usuarios de la base de datos',
                    $this->className
                );
            
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cerrar la conexión y liberar recursos
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function getPaginatedUsuarios($search, $page, $size, $sort = null, $onlyActive = false, $deleted = false) {
            $conn = null; $stmt = null;

            try {
                // Calcula el offset para la consulta
                $offset = ($page - 1) * $size;

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Consultar el total de registros
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_USUARIO . " ";
                if ($onlyActive) { $queryTotalCount .= " WHERE " . USUARIO_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE"); }

                // Ejecutar la consulta y obtener el total de registros
                $totalResult = mysqli_query($conn, $queryTotalCount);
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = (int) $totalRow['total'];
                $totalPages = ceil($totalRecords / $size);

                // Construir la consulta SQL para paginación
                $querySelect = "
                    SELECT 
                        U.*, R." . ROL_NOMBRE . " 
                    FROM " . 
                        TB_USUARIO . " U
                    INNER JOIN " .
                        TB_ROL . " R ON U." . ROL_ID . " = R." . ROL_ID
                ;

                // Agregar filtro de búsqueda a la consulta
                $params = [];
                $types = "";
                if ($search) {
                    $querySelect .= " WHERE (" . USUARIO_NOMBRE . " LIKE ?";
                    $querySelect .= " OR " . USUARIO_APELLIDO_1 . " LIKE ?";
                    $querySelect .= " OR " . USUARIO_APELLIDO_2 . " LIKE ?";
                    $querySelect .= " OR " . USUARIO_EMAIL . " LIKE ?";
                    $querySelect .= " OR R." . ROL_NOMBRE . " LIKE ?)";
                    $params = array_fill(0, 5, "%" . $search . "%");
                    $types = "sssss";
                }

                // Agregar filtro de estado a la consulta
                if ($onlyActive) {
                    $querySelect .= ($search ? " AND " : " WHERE ") . "U." . USUARIO_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE");
                }

                // Agregar ordenamiento a la consulta
                if ($sort) {
                    $querySelect .= " ORDER BY " . ($sort == 'rol' ? "R." . ROL_NOMBRE : "U.usuario" . $sort);
                } else {
                    $querySelect .= " ORDER BY U." . USUARIO_ID . " DESC";
                }
                
                // Agregar límite y desplazamiento a la consulta
                $querySelect .= " LIMIT ? OFFSET ?";
                $params = array_merge($params, [$size, $offset]);
                $types .= "ii";

                // Preparar la consulta y vincular los parámetros
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);

                // Obtener los resultados de la consulta
                $result = mysqli_stmt_get_result($stmt);

                // Crea un array para almacenar los usuarios
                $usuarios = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    // Obtiene el rol del usuario
                    $rolData = new RolData();
                    $rolUsuario = $rolData->getRolByID($row[ROL_ID], false);
                    if (!$rolUsuario["success"]) { throw new Exception($rolUsuario["message"]); }

                    // Crea un objeto Usuario con los datos obtenidos
                    $usuario = new Usuario(
                        $row[USUARIO_ID],
                        $row[USUARIO_NOMBRE],
                        $row[USUARIO_APELLIDO_1],
                        $row[USUARIO_APELLIDO_2],
                        $row[USUARIO_EMAIL],
                        $row[USUARIO_PASSWORD],
                        $rolUsuario["rol"],
                        $row[USUARIO_CREACION],
                        $row[USUARIO_MODIFICACION],
                        $row[USUARIO_ESTADO]
                    );
                    $usuarios[] = $usuario;
                }

                // Devuelve un mensaje de éxito
                return [
                    "success" => true, 
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords, 
                    "usuarios" => $usuarios
                ];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al obtener los usuarios de la base de datos',
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

        public function getUsuarioByID($usuarioID, $onlyActive = true, $deleted = false) {
            $conn = null; $stmt = null;

            try {
                // Verifica si el usuario existe en la base de datos
                $check = $this->existeUsuario($usuarioID);
                if (!$check["success"]) { return $check; }

                // En caso de que el usuario no exista
                if (!$check["exists"]) {
                    $message = "El usuario con 'ID [$usuarioID]' no existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => false, "message" => "El usuario seleccionado no existe en la base de datos."];
                }

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Crea una consulta SQL para obtener el registro
                $querySelect = "
                    SELECT 
                        * 
                    FROM " . 
                        TB_USUARIO . " 
                    WHERE " . 
                        USUARIO_ID . " = ?" . ($onlyActive ? " AND " . 
                        USUARIO_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE") : "");
                $stmt = mysqli_prepare($conn, $querySelect);

                // Asignar el parámetro y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, "i", $usuarioID);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                // Verifica si existe algún registro con los criterios dados
                if (mysqli_num_rows($result) > 0) {
                    // Obtiene el primer registro encontrado
                    $row = mysqli_fetch_assoc($result);

                    // Obtiene el rol del usuario
                    $rolData = new RolData();
                    $rolUsuario = $rolData->getRolByID($row[ROL_ID], false);
                    if (!$rolUsuario["success"]) { throw new Exception($rolUsuario["message"]); }

                    // Crea un objeto Usuario con los datos obtenidos
                    $usuario = new Usuario(
                        $row[USUARIO_ID],
                        $row[USUARIO_NOMBRE],
                        $row[USUARIO_APELLIDO_1],
                        $row[USUARIO_APELLIDO_2],
                        $row[USUARIO_EMAIL],
                        $row[USUARIO_PASSWORD],
                        $rolUsuario["rol"],
                        $row[USUARIO_CREACION],
                        $row[USUARIO_MODIFICACION],
                        $row[USUARIO_ESTADO]
                    );

                    return ["success" => true, "usuario" => $usuario];
                }

                // Retorna false si no se encontraron resultados
                $message = "No se encontró ningún usuario con 'ID [$usuarioID]' en la base de datos.";
                Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ["success" => false, "message" => "No se encontró ningún usuario con el ID proporcionado."];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al obtener el usuario de la base de datos',
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

        public function getUsuarioByEmail($usuarioEmail) {
            $conn = null; $stmt = null;

            try {
                // Verifica si el usuario existe en la base de datos
                $check = $this->existeUsuario(null, $usuarioEmail);
                if (!$check["success"]) { return $check; }

                // En caso de que el usuario no exista
                if (!$check["exists"]) {
                    $message = "El usuario con 'Correo [$usuarioEmail]' no existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => false, "message" => "El correo ingresado no existe. Verifique su correo y vuelva a intentarlo."];
                }

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Crea una consulta SQL para obtener el registro
                $querySelect = "SELECT * FROM " . TB_USUARIO . " WHERE " . USUARIO_EMAIL . " = ?;";
                $stmt = mysqli_prepare($conn, $querySelect);

                // Asignar el parámetro y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, "s", $usuarioEmail);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                // Verifica si existe algún registro con los criterios dados
                if (mysqli_num_rows($result) > 0) {
                    // Obtiene el primer registro encontrado
                    $row = mysqli_fetch_assoc($result);
                    
                    // Obtiene el rol del usuario
                    $rolData = new RolData();
                    $rolUsuario = $rolData->getRolByID($row[ROL_ID], false);
                    if (!$rolUsuario["success"]) { throw new Exception($rolUsuario["message"]); }

                    // Crea un objeto Usuario con los datos obtenidos
                    $usuario = new Usuario(
                        $row[USUARIO_ID],
                        $row[USUARIO_NOMBRE],
                        $row[USUARIO_APELLIDO_1],
                        $row[USUARIO_APELLIDO_2],
                        $row[USUARIO_EMAIL],
                        $row[USUARIO_PASSWORD],
                        $rolUsuario["rol"],
                        $row[USUARIO_CREACION],
                        $row[USUARIO_MODIFICACION],
                        $row[USUARIO_ESTADO]
                    );

                    return ["success" => true, "usuario" => $usuario, "password" => $row[USUARIO_PASSWORD]];
                }

                // Retorna false si no se encontraron resultados
                $message = "No se encontró ningún usuario con 'Correo [$usuarioEmail]' en la base de datos.";
                Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ["success" => false, "message" => "No se encontró ningún usuario con el correo proporcionado."];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al obtener el usuario de la base de datos',
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