<?php

    require_once 'data.php';
    require_once __DIR__ . '/../domain/Usuario.php';

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
                        . USUARIO_NOMBRE . ", "
                        . USUARIO_APELLIDO_1 . ", "
                        . USUARIO_APELLIDO_2 . ", "
                        . USUARIO_ROL_ID . ", "
                        . USUARIO_EMAIL . ", "
                        . USUARIO_PASSWORD . 
                    ") VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $queryInsert);

                // Obtener los valores de las propiedades faltantes
                $usuarioNombre = $usuario->getUsuarioNombre();
                $usuarioApellido1 = $usuario->getUsuarioApellido1();
                $usuarioApellido2 = $usuario->getUsuarioApellido2();
                $usuarioRolID = $usuario->getUsuarioRolID();
                $usuarioPassword = password_hash($usuario->getUsuarioPassword(), PASSWORD_BCRYPT);

                // Asigna los valores a cada '?' de la consulta
                mysqli_stmt_bind_param(
                    $stmt, 
                    "isssiss", 
                    $nextId, 
                    $usuarioNombre, 
                    $usuarioApellido1, 
                    $usuarioApellido2, 
                    $usuarioRolID, 
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
                        . USUARIO_NOMBRE . " = ?, "
                        . USUARIO_APELLIDO_1 . " = ?, "
                        . USUARIO_APELLIDO_2 . " = ?, "
                        . USUARIO_ROL_ID . " = ?, "
                        . USUARIO_EMAIL . " = ?, "
                        . USUARIO_PASSWORD . " = ?, "
                        . USUARIO_ESTADO . " = TRUE "
                    . "WHERE " . USUARIO_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryUpdate);

                // Obtener los valores de las propiedades faltantes
                $usuarioNombre = $usuario->getUsuarioNombre();
                $usuarioApellido1 = $usuario->getUsuarioApellido1();
                $usuarioApellido2 = $usuario->getUsuarioApellido2();
                $usuarioRolID = $usuario->getUsuarioRolID();

                // Asigna los valores a cada '?' de la consulta
                mysqli_stmt_bind_param(
                    $stmt, 
                    "ssssssi", 
                    $usuarioNombre, 
                    $usuarioApellido1, 
                    $usuarioApellido2, 
                    $usuarioRolID, 
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

        public function getAllTBUsuario($onlyActiveOrInactive = false, $deleted = false) {
            $conn = null;

            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Inicializa la consulta base
                $querySelect = "SELECT U.*, R." . ROL_NOMBRE . " FROM " . TB_USUARIO . " U INNER JOIN " . TB_ROL . " R ON U." . USUARIO_ROL_ID . " = R." . ROL_ID;
                if ($onlyActiveOrInactive) { $querySelect .= " WHERE U." . USUARIO_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE"); }
                $result = mysqli_query($conn, $querySelect);

                // Crea un array para almacenar los usuarios
                $usuarios = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $usuarios [] = [
                    "ID" => $row[USUARIO_ID],
                    "Nombre" => $row[USUARIO_NOMBRE],
                    "Apellido1" => $row[USUARIO_APELLIDO_1],
                    "Apellido2" => $row[USUARIO_APELLIDO_2],
                    "RolID" => $row[USUARIO_ROL_ID],
                    "RolNombre" => $row[ROL_NOMBRE],
                    "Email" => $row[USUARIO_EMAIL],
                    "Password" => $row[USUARIO_PASSWORD],
                    "Creacion" => Utils::formatearFecha($row[USUARIO_FECHA_CREACION]),
                    "CreacionISO" => Utils::formatearFecha($row[USUARIO_FECHA_CREACION], "Y-MM-dd"),
                    "Modificacion" => Utils::formatearFecha($row[USUARIO_FECHA_MODIFICACION]),
                    "ModificacionISO" => Utils::formatearFecha($row[USUARIO_FECHA_MODIFICACION], "Y-MM-dd"),
                    "Estado" => $row[USUARIO_ESTADO]
                    ];
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

        public function getPaginatedUsuarios($page, $size, $sort = null, $onlyActiveOrInactive = false, $deleted = false) {
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
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_USUARIO . " ";
                if ($onlyActiveOrInactive) { $queryTotalCount .= " WHERE " . USUARIO_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE"); }

                // Ejecutar la consulta y obtener el total de registros
                $totalResult = mysqli_query($conn, $queryTotalCount);
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = (int) $totalRow['total'];
                $totalPages = ceil($totalRecords / $size);

                // Construir la consulta SQL para paginación
                $querySelect = "SELECT U.*, R." . ROL_NOMBRE . " FROM " . TB_USUARIO . " U INNER JOIN " . TB_ROL . " R ON U." . USUARIO_ROL_ID . " = R." . ROL_ID;
                if ($onlyActiveOrInactive) { $querySelect .= " WHERE U." . USUARIO_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE") . " "; }
                if ($sort) { $querySelect .= " ORDER BY usuario" . $sort . " "; }
                $querySelect .= " LIMIT ? OFFSET ?";

                // Preparar la consulta SQL y asignar los parámetros
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, "ii", $size, $offset);
                mysqli_stmt_execute($stmt);

                // Obtener los resultados de la consulta
                $result = mysqli_stmt_get_result($stmt);

                // Crea un array para almacenar los usuarios
                $usuarios = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $usuarios [] = [
                    "ID" => $row[USUARIO_ID],
                    "Nombre" => $row[USUARIO_NOMBRE],
                    "Apellido1" => $row[USUARIO_APELLIDO_1],
                    "Apellido2" => $row[USUARIO_APELLIDO_2],
                    "RolID" => $row[USUARIO_ROL_ID],
                    "RolNombre" => $row[ROL_NOMBRE],
                    "Email" => $row[USUARIO_EMAIL],
                    "Password" => $row[USUARIO_PASSWORD],
                    'Creacion' => Utils::formatearFecha($row[USUARIO_FECHA_CREACION]),
                    'CreacionISO' => Utils::formatearFecha($row[USUARIO_FECHA_CREACION], 'Y-MM-dd'),
                    'Modificacion' => Utils::formatearFecha($row[USUARIO_FECHA_MODIFICACION]),
                    'ModificacionISO' => Utils::formatearFecha($row[USUARIO_FECHA_MODIFICACION], 'Y-MM-dd'),
                    "Estado" => $row[USUARIO_ESTADO]
                    ];
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

        public function getUsuarioByID($usuarioID, $json = true) {
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
                $querySelect = "SELECT * FROM " . TB_USUARIO . " WHERE " . USUARIO_ID . " = ? AND " . USUARIO_ESTADO . " != FALSE";
                $stmt = mysqli_prepare($conn, $querySelect);

                // Asignar el parámetro y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, "i", $usuarioID);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                // Verifica si existe algún registro con los criterios dados
                if (mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    $usuario = null;
                    if ($json) {
                        $usuario = [
                            "ID" => $row[USUARIO_ID],
                            "Nombre" => $row[USUARIO_NOMBRE],
                            "Apellido1" => $row[USUARIO_APELLIDO_1],
                            "Apellido2" => $row[USUARIO_APELLIDO_2],
                            "RolID" => $row[USUARIO_ROL_ID],
                            "Email" => $row[USUARIO_EMAIL],
                            "Password" => $row[USUARIO_PASSWORD],
                            'Creacion' => Utils::formatearFecha($row[USUARIO_FECHA_CREACION]),
                            'CreacionISO' => Utils::formatearFecha($row[USUARIO_FECHA_CREACION], 'Y-MM-dd'),
                            'Modificacion' => Utils::formatearFecha($row[USUARIO_FECHA_MODIFICACION]),
                            'ModificacionISO' => Utils::formatearFecha($row[USUARIO_FECHA_MODIFICACION], 'Y-MM-dd'),
                            "Estado" => $row[USUARIO_ESTADO]
                        ];
                    } else {
                        $usuario = new Usuario(
                            $row[USUARIO_ID],
                            $row[USUARIO_NOMBRE],
                            $row[USUARIO_APELLIDO_1],
                            $row[USUARIO_APELLIDO_2],
                            $row[USUARIO_EMAIL],
                            $row[USUARIO_PASSWORD],
                            $row[USUARIO_ROL_ID],
                            $row[USUARIO_FECHA_CREACION],
                            $row[USUARIO_FECHA_MODIFICACION],
                            $row[USUARIO_ESTADO]
                        );
                    }
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

        public function getUsuarioByEmail($usuarioEmail, $json = true) {
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
                    $row = mysqli_fetch_assoc($result);
                    $usuario = null;
                    if ($json) {
                        $usuario = [
                            "ID" => $row[USUARIO_ID],
                            "Nombre" => $row[USUARIO_NOMBRE],
                            "Apellido1" => $row[USUARIO_APELLIDO_1],
                            "Apellido2" => $row[USUARIO_APELLIDO_2],
                            "RolID" => $row[USUARIO_ROL_ID],
                            "Email" => $row[USUARIO_EMAIL],
                            "Password" => $row[USUARIO_PASSWORD],
                            'Creacion' => Utils::formatearFecha($row[USUARIO_FECHA_CREACION]),
                            'CreacionISO' => Utils::formatearFecha($row[USUARIO_FECHA_CREACION], 'Y-MM-dd'),
                            'Modificacion' => Utils::formatearFecha($row[USUARIO_FECHA_MODIFICACION]),
                            'ModificacionISO' => Utils::formatearFecha($row[USUARIO_FECHA_MODIFICACION], 'Y-MM-dd'),
                            "Estado" => $row[USUARIO_ESTADO]
                        ];
                    } else {
                        $usuario = new Usuario(
                            $row[USUARIO_ID],
                            $row[USUARIO_NOMBRE],
                            $row[USUARIO_APELLIDO_1],
                            $row[USUARIO_APELLIDO_2],
                            $row[USUARIO_EMAIL],
                            $row[USUARIO_PASSWORD],
                            $row[USUARIO_ROL_ID],
                            $row[USUARIO_FECHA_CREACION],
                            $row[USUARIO_FECHA_MODIFICACION],
                            $row[USUARIO_ESTADO]
                        );
                    }
                    return ["success" => true, "usuario" => $usuario];
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