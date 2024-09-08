<?php

    require_once 'data.php';
    require_once __DIR__ . '/../domain/Telefono.php';
    require_once __DIR__ . '/../utils/Variables.php';
    require_once __DIR__ . '/../utils/Utils.php';

    class TelefonoData extends Data {

        private $className;

        // Constructor
		public function __construct() {
			$this->className = get_class($this);
            parent::__construct();
		}

        public function existeTelefono($telefonoID = null, $telefonoCodigoPais = null, $telefonoNumero = null, $update = false, $insert = false) {
            $conn = null; $stmt = null;

            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
        
                // Inicializa la consulta base
                $queryCheck = "SELECT 1 FROM " . TB_TELEFONO . " WHERE ";
                $params = [];
                $types = "";
        
                // Consulta para verificar si existe un telefono con el ID ingresado
                if ($telefonoID && (!$update && !$insert)) {
                    $queryCheck .= TELEFONO_ID . " = ? AND " . TELEFONO_ESTADO . " != FALSE";
                    $params[] = $telefonoID;
                    $types .= 'i';
                }
                
                // Consulta en caso de insertar para verificar si existe un telefono con el código y número ingresados
                else if ($insert && ($telefonoCodigoPais && $telefonoNumero)) {
                    $queryCheck .= TELEFONO_CODIGO_PAIS . " = ? AND " . TELEFONO_NUMERO . " = ? AND " . TELEFONO_ESTADO . " != FALSE";
                    $params = [$telefonoCodigoPais, $telefonoNumero];
                    $types .= 'ss';
                }
                
                // Consulta en caso de actualizar para verificar si existe ya un telefono con el mismo código y número además del que se va a actualizar
                else if ($update && ($telefonoID && $telefonoCodigoPais && $telefonoNumero)) {
                    $queryCheck .= TELEFONO_CODIGO_PAIS . " = ? AND " . TELEFONO_NUMERO . " = ? AND " . TELEFONO_ESTADO . " != FALSE AND " . TELEFONO_ID . " != ?";
                    $params = [$telefonoCodigoPais, $telefonoNumero, $telefonoID];
                    $types .= 'ssi';
                }
                
                else {
                    // Registrar parámetros faltantes y lanzar excepción
                    $missingParamsLog = "Faltan parámetros para verificar la existencia del telefono:";
                    if (!$telefonoID) $missingParamsLog .= " telefonoID [" . ($telefonoID ?? 'null') . "]";
                    if (!$telefonoCodigoPais) $missingParamsLog .= " telefonoCodigoPais [" . ($telefonoCodigoPais ?? 'null') . "]";
                    if (!$telefonoNumero) $missingParamsLog .= " telefonoNumero [" . ($telefonoNumero ?? 'null') . "]";
                    Utils::writeLog($missingParamsLog, DATA_LOG_FILE, WARN_MESSAGE, $this->className);
                    throw new Exception("Faltan parámetros para verificar la existencia del telefono.");
                }
        
                // Asignar los parámetros y ejecutar la consulta
                $stmt = mysqli_prepare($conn, $queryCheck);
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
        
                // Verifica si existe algún registro con los criterios dados
                if (mysqli_num_rows($result) > 0) {
                    return ["success" => true, "exists" => true];
                }
        
                // Retorna false si no se encontraron resultados
                $messageParams = [];
                if ($telefonoID) { $messageParams[] = "ID [$telefonoID]"; }
                if ($telefonoCodigoPais)  { $messageParams[] = "Código de Pais ['$telefonoCodigoPais']"; }
                if ($telefonoNumero)  { $messageParams[] = "Número ['$telefonoNumero']"; }
                $params = implode(', ', $messageParams);

                $message = "No se encontró ningún telefono ($params) en la base de datos.";
                Utils::writeLog($message, DATA_LOG_FILE, WARN_MESSAGE, $this->className);

                return ["success" => true, "exists" => false, "message" => $message];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al verificar la existencia del telefono en la base de datos',
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

        public function insertTelefono($telefono) {
            $conn = null; $stmt = null;

            try {
                // Obtener los valores de las propiedades del objeto para verificación
                $telefonoCodigoPais = $telefono->getTelefonoCodigoPais();
                $telefonoNumero = $telefono->getTelefonoNumero();

                // Verifica si ya existe un telefono con el mismo número o código de país
                $check = $this->existeTelefono(null, $telefonoCodigoPais, $telefonoNumero, false, true);
                if (!$check['success']) { return $check; } //<- Error al verificar la existencia
                
                // En caso de ya existir el telefono
                if ($check['exists']) {
                    $message = "El telefono con 'Código [$telefonoCodigoPais]' y 'Número [$telefonoNumero]' ya existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
					return ["success" => false, "message" => "Ya existe un telefono con el mismo número y código de país."];
                }

                // Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) { throw new Exception($result["message"]); }
				$conn = $result["connection"];

                // Obtenemos el último ID de la tabla tbtelefono
				$queryGetLastId = "SELECT MAX(" . TELEFONO_ID . ") FROM " . TB_TELEFONO;
				$idCont = mysqli_query($conn, $queryGetLastId);
				$nextId = 1;
		
				// Calcula el siguiente ID para la nueva entrada
				if ($row = mysqli_fetch_row($idCont)) {
					$nextId = (int) trim($row[0]) + 1;
				}

                // Crea una consulta y un statement SQL para insertar el nuevo registro
				$queryInsert = "INSERT INTO " . TB_TELEFONO . " ("
                    . TELEFONO_ID . ", "
                    . TELEFONO_TIPO . ", "
                    . TELEFONO_CODIGO_PAIS . ", "
                    . TELEFONO_NUMERO . ", "
                    . TELEFONO_EXTENSION
                    . ") VALUES (?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $queryInsert);

                // Obtiene los valores de las propiedades faltantes
                $telefonoTipo = $telefono->getTelefonoTipo();
                $telefonoExtension = $telefono->getTelefonoExtension();

                // Asigna los valores a cada '?' de la consulta
				mysqli_stmt_bind_param(
					$stmt,
					'issss', // i: Entero, s: Cadena
					$nextId,
					$telefonoTipo,
					$telefonoCodigoPais,
                    $telefonoNumero,
					$telefonoExtension
				);

                // Ejecuta la consulta de inserción
				$result = mysqli_stmt_execute($stmt);
				return ["success" => true, "message" => "Telefono insertado exitosamente", "id" => $nextId];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al insertar el telefono en la base de datos',
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

        public function updateTelefono($telefono) {
            $conn = null; $stmt = null;

            try {
                // Obtener el ID, Codigo de Pais y Número de telefono
                $telefonoID = $telefono->getTelefonoID();
                $telefonoCodigoPais = $telefono->getTelefonoCodigoPais();
                $telefonoNumero = $telefono->getTelefonoNumero();

                // Verifica si el telefono existe en la base de datos
                $checkID = $this->existeTelefono($telefonoID);
                if (!$checkID['success']) { return $checkID; } //<- Error al verificar la existencia
                if (!$checkID['exists']) { //<- El telefono no existe
                    $message = "El telefono con 'ID [$telefonoID]' no existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => false, "message" => "El teléfono seleccionado no existe en la base de datos"];
                }

                // Verifica que no exista otro telefono con la misma información
                $check = $this->existeTelefono($telefonoID, $telefonoCodigoPais, $telefonoNumero, true);
                if (!$check['success']) { return $check; } //<- Error al verificar la existencia
                if ($check['exists']) { //<- El telefono existe
                    $message = "El telefono con 'ID [$telefonoID]', 'Código [$telefonoCodigoPais]' y 'Número [$telefonoNumero]' ya existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
					return ["success" => false, "message" => "Ya existe un telefono con el mismo número y código de país."];
                }

                // Establece una conexion con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) { throw new Exception($result["message"]); }
				$conn = $result["connection"];

                // Crea una consulta y un statement SQL para actualizar el registro
				$queryUpdate = 
                    "UPDATE " . TB_TELEFONO . 
                    " SET " . 
                        TELEFONO_TIPO . " = ?, " .
                        TELEFONO_CODIGO_PAIS . " = ?, " .
                        TELEFONO_NUMERO . " = ?, " .
                        TELEFONO_EXTENSION . " = ? " .
                    "WHERE " . TELEFONO_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryUpdate);

                // Obtener los valores de las propiedades faltantes
                $telefonoTipo = $telefono->getTelefonoTipo();
                $telefonoExtension = $telefono->getTelefonoExtension();

                mysqli_stmt_bind_param(
					$stmt,
					'ssssi', // s: Cadena, i: Entero
					$telefonoTipo,
					$telefonoCodigoPais,
					$telefonoNumero,
					$telefonoExtension,
                    $telefonoID
				);

                // Ejecuta la consulta de actualización
				$result = mysqli_stmt_execute($stmt);

				// Devuelve el resultado de la consulta
				return ["success" => true, "message" => "Telefono actualizado exitosamente"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al actualizar el teléfono en la base de datos',
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

        public function deleteTelefono($telefonoID) {
            $conn = null; $stmt = null;

            try {
                // Verifica si existe un Telefono con el mismo ID en la BD
                $check = $this->existeTelefono($telefonoID);
                if (!$check["success"]) { return $check; } // Error al verificar la existencia
				if (!$check["exists"]) { //<- El telefono no existe
					$message = "El telefono con 'ID [$telefonoID]' no existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => false, "message" => "El teléfono seleccionado no existe en la base de datos"];
				}

                // Establece una conexion con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) { throw new Exception($result["message"]); }
				$conn = $result["connection"];

                // Crea una consulta y un statement SQL para eliminar el registro (borrado logico)
				$queryDelete = "UPDATE " . TB_TELEFONO . " SET " . TELEFONO_ESTADO . " = false WHERE " . TELEFONO_ID . " = ?";
				$stmt = mysqli_prepare($conn, $queryDelete);
				mysqli_stmt_bind_param($stmt, 'i', $telefonoID);

                // Ejecuta la consulta de eliminación
				$result = mysqli_stmt_execute($stmt);
		
				// Devuelve el resultado de la operación
				return ["success" => true, "message" => "Teléfono eliminado exitosamente."];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al eliminar el teléfono de la base de datos',
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

        public function getAllTBTelefono($onlyActiveOrInactive = false, $deleted = false) {
            $conn = null;
            
            try {
                // Establece una conexion con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) { throw new Exception($result["message"]); }
				$conn = $result["connection"];

                // Consulta SQL para obtener todos los registros de la tabla tbtelefono
                $querySelect = "SELECT * FROM " . TB_TELEFONO;
                if ($onlyActiveOrInactive) { $querySelect .= " WHERE " . TELEFONO_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE"); }
                $result = mysqli_query($conn, $querySelect);

                // Crear un array para almacenar los telefonos
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

                // Devuelve el resultado de la consulta
                return ["success" => true, "telefonos" => $telefonos];
            } catch (Exception $e) {
				// Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener la lista de telefono desde la base de datos',
                    $this->className
                );

                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
			} finally {
				// Cerramos la conexion
				if (isset($conn)) { mysqli_close($conn); }
			}
        }

        public function getPaginatedTelefonos($page, $size, $sort = null, $onlyActiveOrInactive = false, $deleted = false) {
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
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_TELEFONO . " ";
                if ($onlyActiveOrInactive) { $queryTotalCount .= " WHERE " . TELEFONO_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE") . " "; }

                $totalResult = mysqli_query($conn, $queryTotalCount);
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = (int) $totalRow['total'];
                $totalPages = ceil($totalRecords / $size);

                // Construir la consulta SQL para paginación
                $querySelect = "SELECT * FROM " . TB_TELEFONO . " ";
                if ($onlyActiveOrInactive) { $querySelect .= " WHERE " . TELEFONO_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE") . " "; }

                // Añadir la cláusula de ordenamiento si se proporciona
                if ($sort) { $querySelect .= "ORDER BY telefono" . $sort . " "; }

				// Añadir la cláusula de limitación y offset
                $querySelect .= "LIMIT ? OFFSET ?";

                // Preparar la consulta y vincular los parámetros
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, "ii", $size, $offset);

				// Ejecutar la consulta
                $result = mysqli_stmt_execute($stmt);

				// Obtener el resultado
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
                    "telefonos" => $telefonos
                ];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener la lista de telefonos desde la base de datos',
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
        
        public function getTelefonoByID($telefonoID, $json = true) {
            $conn = null; $stmt = null;
            
            try {
                // Verifica si el telefono existe en la base de datos
                $checkID = $this->existeTelefono($telefonoID);
                if (!$checkID["success"]) { return $checkID; } // Error al verificar la existencia
                if (!$checkID["exists"]) { // El telefono no existe
                    $message = "El telefono con 'ID [$telefonoID]' no existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => false, "message" => "El teléfono seleccionado no existe en la base de datos"];
                }

                // Establece una conexion con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Consulta SQL para obtener el telefono con el ID proporcionado
                $querySelect = "SELECT * FROM " . TB_TELEFONO . " WHERE " . TELEFONO_ID . " = ? AND " . TELEFONO_ESTADO . " != FALSE";
                $stmt = mysqli_prepare($conn, $querySelect);

                // Asignar los parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, "i", $telefonoID);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                // Verifica si existe algún registro con los criterios dados
                if (mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    $telefono = null;
                    if ($json) {
                        $telefono = [
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
                    }
                    return ["success" => true, "telefono" => $telefono];
                }
                
                // Retorna false si no se encontraron resultados
                $message = "No se encontró ningún teléfono con el 'ID [$telefonoID]' en la base de datos.";
                Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ["success" => false, "message" => "No se encontró el teléfono en la base de datos"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError($e->getCode(), $e->getMessage(),
                    'Error al obtener el teléfono desde la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cerrar la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }
        public function getTelefonoProveedorID($idproveedor){
            try {
                if(!is_numeric($idproveedor) || $idproveedor <= 0){
                    throw new Exception("El 'ID [$proveedor]' para proveedor es invalido.");
                }
                // Establece una conexion con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Consulta SQL para obtener el telefono con el ID proveedor proporcionado
                $querySelect = "SELECT * FROM " . TB_TELEFONO . " WHERE " . TELEFONO_PROVEEDOR_ID . " = ? AND " . TELEFONO_ESTADO . " != FALSE";
                $stmt = mysqli_prepare($conn, $querySelect);

                // Asignar los parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, "i", $idproveedor);
                mysqli_stmt_execute($stmt);
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

                if(!empty($telefonos)){
                    return ["success" => true, "listaTelefonos" => $telefonos];
                }

                Utils::writeLog("No se encontró ningún teléfono para el proveeedor con ID [$idproveedor] en la base de datos.", DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ["success" => false, "message" => "No se encontraron telefonos en la base de datos"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError($e->getCode(), $e->getMessage(),
                    'Error al obtener el teléfono desde la base de datos'
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