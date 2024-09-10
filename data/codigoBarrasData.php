<?php

    require_once 'data.php';
    require_once __DIR__ . '/../domain/CodigoBarras.php';
    require_once __DIR__ . '/../utils/Variables.php';
    require_once __DIR__ . '/../utils/Utils.php';

    class CodigoBarrasData extends Data {

        private $className;

        // Constructor
		public function __construct() {
			parent::__construct();
            $this->className = get_class($this);
		}

        public function existeCodigoBarras($codigoBarrasID = null, $codigoBarrasNumero = null, $update = false, $insert = false) {
            $conn = null; $stmt = null;

            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Inicializa la consulta base
                $queryCheck = "SELECT 1 FROM " . TB_CODIGO_BARRAS . " WHERE ";
                $params = [];
                $types = "";

                // Consulta para verificar si existe un codigo de barras con el ID ingresado
                if ($codigoBarrasID && (!$update && !$insert)) {
                    $queryCheck .= CODIGO_BARRAS_ID . " = ? AND " . CODIGO_BARRAS_ESTADO . " != FALSE";
                    $params[] = $codigoBarrasID;
                    $types .= 'i';
                }

                // Consulta en caso de insertar para verificar si existe un codigo de barras con el mismo numero
                else if ($insert && $codigoBarrasNumero) {
                    $queryCheck .= CODIGO_BARRAS_NUMERO . " = ? AND " . CODIGO_BARRAS_ESTADO . " != FALSE";
                    $params[] = $codigoBarrasNumero;
                    $types .= 's';
                }

                // Consulta en caso de actualizar para verificar si existe ya un codigo de barras con el mismo numero además del que se va a actualizar
                else if ($update && ($codigoBarrasID && $codigoBarrasNumero)) {
                    $queryCheck .= CODIGO_BARRAS_NUMERO . " = ? AND " . CODIGO_BARRAS_ID . " != ? AND " . CODIGO_BARRAS_ESTADO . " != FALSE";
                    $params[] = $codigoBarrasNumero;
                    $params[] = $codigoBarrasID;
                    $types .= 'si';
                }

                // Registrar parámetros faltantes y lanzar excepción si no se proporcionan
                else {
                    $missingParamsLog = "Faltan parámetros para verificar la existencia del codigo de barras:";
                    if (!$codigoBarrasID) { $missingParamsLog .= " codigoBarrasID [" . ($codigoBarrasID ?? 'null') . "]"; }
                    if (!$codigoBarrasNumero) { $missingParamsLog .= " codigoBarrasNumero [" . ($codigoBarrasNumero ?? 'null') . "]"; }
                    Utils::writeLog($missingParamsLog, DATA_LOG_FILE, WARN_MESSAGE, $this->className);
                    throw new Exception("Faltan parámetros para verificar la existencia del codigo de barras.");
                }

                // Preparar la consulta y vincular los parámetros
                $stmt = mysqli_prepare($conn, $queryCheck);
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                // Verificar si existe un registro con los datos proporcionados
                if (mysqli_num_rows($result) > 0) {
                    return ["success" => true, "exists" => true];
                }

                // Retorna false si no se encontraron resultados
                $messageParams = [];
                if ($codigoBarrasID) { $messageParams[] = "'ID [$codigoBarrasID]'"; }
                if ($codigoBarrasNumero) { $messageParams[] = "'Número [$codigoBarrasNumero]'"; }
                $params = implode(" y ", $messageParams);

                $message = "No se encontró ningún código de barras con $params en la base de datos.";
                Utils::writeLog($message, DATA_LOG_FILE, WARN_MESSAGE, $this->className);

                return ["success" => true, "exists" => false, "message" => $message];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al verificar la existencia del codigo de barras en la base de datos',
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

        public function insertCodigoBarras($codigoBarras, $conn = null) {
            $createdConnection = false; //<- Indica si la conexión se creó aquí o viene por parámetro
            $stmt = null;
            
            try {
                // Obtener los valores de las propiedades del objeto
                $codigoBarrasNumero = $codigoBarras->getCodigoBarrasNumero();

                // Verifica si ya existe el código de barras
                $check = $this->existeCodigoBarras(null, $codigoBarrasNumero, false, true);
                if (!$check['success']) { return $check; } //<- Error al verificar la existencia

                // En caso de ya existir un código de barras con el mismo número
                if ($check['exists']) {
                    $message = "El código de barras con 'Número [$codigoBarrasNumero]' ya existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
					return ["success" => false, "message" => "Ya existe un código de barras con el mismo número en la base de datos"];
                }

                // Si no se proporcionó una conexión, crea una nueva
                if (is_null($conn)) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConnection = true;
                }

                // Obtenemos el último ID de la tabla tbcodigobarras
				$queryGetLastId = "SELECT MAX(" . CODIGO_BARRAS_ID . ") AS codigoBarrasID FROM " . TB_CODIGO_BARRAS;
				$idCont = mysqli_query($conn, $queryGetLastId);
				$nextId = 1;
		
				// Calcula el siguiente ID para la nueva entrada
				if ($row = mysqli_fetch_row($idCont)) {
					$nextId = (int) trim($row[0]) + 1;
				}

                // Crea una consulta y un statement SQL para insertar el nuevo registro
				$queryInsert = "INSERT INTO " . TB_CODIGO_BARRAS . " ("
                    . CODIGO_BARRAS_ID . ", "
                    . CODIGO_BARRAS_NUMERO
                    . ") VALUES (?, ?)";
                $stmt = mysqli_prepare($conn, $queryInsert);

                // Asigna los valores a cada '?' de la consulta
				mysqli_stmt_bind_param($stmt, 'is', $nextId, $codigoBarrasNumero);

                // Ejecuta la consulta de inserción
				$result = mysqli_stmt_execute($stmt);
				return ["success" => true, "message" => "Código de Barras insertado exitosamente", "id" => $nextId];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al insertar el código de barras en la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra el statement y la conexión solo si fueron creados en esta función
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if ($createdConnection && isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
            }
        }

        public function updateCodigoBarras($codigoBarras) {
            $conn = null; $stmt = null;

            try {
                // Obtener el ID y Codigo de Barras
                $codigoBarrasID = $codigoBarras->getCodigoBarrasID();
                $codigoBarrasNumero = $codigoBarras->getCodigoBarrasNumero();

                // Verifica si el telefono existe en la base de datos
                $checkID = $this->existeCodigoBarras($codigoBarrasID);
                if (!$checkID["success"]) { return $checkID; } // Error al verificar la existencia
                if (!$checkID["exists"]) {
                    $message = "El código de barras con 'ID [$codigoBarrasID]' no existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    throw new Exception("No existe ningún código de barras en la base de datos que coincida con la información proporcionada.");
                }

                // Verifica si ya existe un código de barras con el mismo número
                $checkNumero = $this->existeCodigoBarras($codigoBarrasID, $codigoBarrasNumero, true);
                if (!$checkNumero["success"]) { return $checkNumero; } // Error al verificar la existencia
                if ($checkNumero["exists"]) {
                    $message = "El código de barras con 'Número [$codigoBarrasNumero]' ya existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
					return ["success" => false, "message" => "Ya existe un código de barras con el mismo número en la base de datos"];
                }

                // Establece una conexion con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) { throw new Exception($result["message"]); }
				$conn = $result["connection"];

                // Crea una consulta y un statement SQL para actualizar el registro
				$queryUpdate = "UPDATE " . TB_CODIGO_BARRAS . " SET " . CODIGO_BARRAS_NUMERO . " = ? " . "WHERE " . CODIGO_BARRAS_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryUpdate);
                mysqli_stmt_bind_param($stmt, 'si', $codigoBarrasNumero, $codigoBarrasID);

                // Ejecuta la consulta de actualización
				$result = mysqli_stmt_execute($stmt);

				// Devuelve el resultado de la consulta
				return ["success" => true, "message" => "Código de Barras actualizado exitosamente"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al actualizar el código de barras en la base de datos',
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

        public function deleteCodigoBarras($codigoBarrasID, $conn = null) {
            $createdConnection = false; //<- Indica si la conexión se creó aquí o viene por parámetro
            $stmt = null;

            try {
                // Verifica si existe un Código de Barras con el mismo ID en la BD
                $check = $this->existeCodigoBarras($codigoBarrasID);
                if (!$check["success"]) { return $check; } // Error al verificar la existencia
				if (!$check["exists"]) { // No existe el código de barras
                    $message = "El código de barras con 'ID [$codigoBarrasID]' no existe en la base de datos.";
					Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
					return ["success" => false, "message" => "No existe ningún código de barras en la base de datos que coincida con la información proporcionada."];
				}

                // Si no se proporcionó una conexión, crea una nueva
                if (is_null($conn)) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConnection = true;
                }

                // Crea una consulta y un statement SQL para eliminar el registro (borrado logico)
				$queryDelete = "UPDATE " . TB_CODIGO_BARRAS . " SET " . CODIGO_BARRAS_ESTADO . " = false WHERE " . CODIGO_BARRAS_ID . " = ?";
				$stmt = mysqli_prepare($conn, $queryDelete);
				mysqli_stmt_bind_param($stmt, 'i', $codigoBarrasID);

                // Ejecuta la consulta de eliminación
				$result = mysqli_stmt_execute($stmt);
		
				// Devuelve el resultado de la operación
				return ["success" => true, "message" => "Código de Barras eliminado exitosamente."];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al eliminar el código de barras de la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra el statement y la conexión solo si fueron creados en esta función
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if ($createdConnection && isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
            }
        }

        public function getAllTBCodigoBarras($onlyActiveOrInactive = false, $deleted = false) {
            $conn = null;

            try {
                // Establece una conexion con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) { throw new Exception($result["message"]); }
				$conn = $result["connection"];

                // Consulta SQL para obtener todos los registros de la tabla tbtelefono
                $querySelect = "SELECT * FROM " . TB_CODIGO_BARRAS;
                if ($onlyActiveOrInactive) { $querySelect .= " WHERE " . CODIGO_BARRAS_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE"); }
                $result = mysqli_query($conn, $querySelect);

                // Crear un array para almacenar los registros
                $codigosBarras = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $codigoBarras = [
                        'ID' => $row[CODIGO_BARRAS_ID],
                        'Numero' => $row[CODIGO_BARRAS_NUMERO],
                        'CreacionISO' => Utils::formatearFecha($row[CODIGO_BARRAS_FECHA_CREACION], 'Y-MM-dd'),
                        'Creacion' => Utils::formatearFecha($row[CODIGO_BARRAS_FECHA_CREACION]),
                        'ModificacionISO' => Utils::formatearFecha($row[CODIGO_BARRAS_FECHA_MODIFICACION], 'Y-MM-dd'),
                        'Modificacion' => Utils::formatearFecha($row[CODIGO_BARRAS_FECHA_MODIFICACION]),
                        'Estado' => $row[CODIGO_BARRAS_ESTADO]
                    ];
                }

                // Devuelve el resultado de la consulta
                return ["success" => true, "codigosBarras" => $codigosBarras];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener la lista de códigos de barras desde la base de datos',
                    $this->className
                );

                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
				// Cerramos la conexion
				if (isset($conn)) { mysqli_close($conn); }
			}
        }

        public function getPaginatedCodigosBarras($page, $size, $sort = null, $onlyActiveOrInactive = false, $deleted = false) {
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
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_CODIGO_BARRAS . " ";
                if ($onlyActiveOrInactive) { $queryTotalCount .= " WHERE " . CODIGO_BARRAS_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE") . " "; }
                $totalResult = mysqli_query($conn, $queryTotalCount);
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = (int) $totalRow['total'];
                $totalPages = ceil($totalRecords / $size);

                // Construir la consulta SQL para paginación
                $querySelect = "
                    SELECT
                        C." . CODIGO_BARRAS_ID . ",
                        C." . CODIGO_BARRAS_NUMERO . ",
                        C." . CODIGO_BARRAS_FECHA_CREACION . ",
                        C." . CODIGO_BARRAS_FECHA_MODIFICACION . ",
                        C." . CODIGO_BARRAS_ESTADO . "
                    FROM
                        " . TB_CODIGO_BARRAS . " C
                ";
                if ($onlyActiveOrInactive) { $querySelect .= " WHERE C." . CODIGO_BARRAS_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE") . " "; }

                // Añadir la cláusula de ordenamiento si se proporciona
                if ($sort) { $querySelect .= "ORDER BY codigobarras" . $sort . " ";
                }

                // Añadir la cláusula de limitación y offset
                $querySelect .= "LIMIT ? OFFSET ?";

                // Preparar la consulta y vincular los parámetros
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, "ii", $size, $offset);

                // Ejecutar la consulta
                $result = mysqli_stmt_execute($stmt);

				// Obtener el resultado
                $result = mysqli_stmt_get_result($stmt);

                $codigosBarras = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $codigosBarras[] = [
                        'ID' => $row[CODIGO_BARRAS_ID],
						'Numero' => $row[CODIGO_BARRAS_NUMERO],
						'FechaCreacion' => Utils::formatearFecha($row[CODIGO_BARRAS_FECHA_CREACION]), //<- 20 ago. 2024
						'FechaModificacion' => Utils::formatearFecha($row[CODIGO_BARRAS_FECHA_CREACION]),
						'FechaCreacionISO' => Utils::formatearFecha($row[CODIGO_BARRAS_FECHA_MODIFICACION], 'Y-MM-dd'), //<- 2024-08-20
						'FechaModificacionISO' => Utils::formatearFecha($row[CODIGO_BARRAS_FECHA_MODIFICACION], 'Y-MM-dd'),
						'Estado' => $row[CODIGO_BARRAS_ESTADO]
                    ];
                }

                return [
                    "success" => true,
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "codigosBarras" => $codigosBarras
                ];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al obtener la lista de códigos de barras desde la base de datos',
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

        public function getCodigoBarrasByID($codigoBarrasID, $json = true) {
            $conn = null; $stmt = null;

            try {
                // Verifica si existe un Código de Barras con el mismo ID en la BD
                $check = $this->existeCodigoBarrasID($codigoBarrasID);
                if (!$check["success"]) { return $check; } // Error al verificar la existencia
                if (!$check["exists"]) { // No existe el código de barras
                    $message = "El código de barras con 'ID [$codigoBarrasID]' no existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => false, "message" => "No existe ningún código de barras en la base de datos que coincida con la información proporcionada."];
                }

                // Establece una conexion con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Consulta SQL para obtener el código de barras con el ID proporcionado
                $querySelect = "SELECT * FROM " . TB_CODIGO_BARRAS . " WHERE " . CODIGO_BARRAS_ID . " = ? AND " . CODIGO_BARRAS_ESTADO . " != FALSE";
                $stmt = mysqli_prepare($conn, $querySelect);

                // Asignar los parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, 'i', $codigoBarrasID);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                // Verifica si existe algún registro con los criterios dados
                if (mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    $codigoBarras = null;
                    if ($json) {
                        $codigoBarras = [
                            'ID' => $row[CODIGO_BARRAS_ID],
                            'Numero' => $row[CODIGO_BARRAS_NUMERO],
                            'CreacionISO' => Utils::formatearFecha($row[CODIGO_BARRAS_FECHA_CREACION], 'Y-MM-dd'),
                            'Creacion' => Utils::formatearFecha($row[CODIGO_BARRAS_FECHA_CREACION]),
                            'ModificacionISO' => Utils::formatearFecha($row[CODIGO_BARRAS_FECHA_MODIFICACION], 'Y-MM-dd'),
                            'Modificacion' => Utils::formatearFecha($row[CODIGO_BARRAS_FECHA_MODIFICACION]),
                            'Estado' => $row[CODIGO_BARRAS_ESTADO]
                        ];
                    } else {
                        $codigoBarras = new CodigoBarras(
                            $row[CODIGO_BARRAS_ID],
                            $row[CODIGO_BARRAS_NUMERO],
                            $row[CODIGO_BARRAS_FECHA_CREACION],
                            $row[CODIGO_BARRAS_FECHA_MODIFICACION],
                            $row[CODIGO_BARRAS_ESTADO]
                        );
                    }

                    // Devuelve el resultado de la consulta
                    return ["success" => true, "codigoBarras" => $codigoBarras];
                }

                // Retorna false si no se encontraron resultados
                $message = "No se encontró ningún código de barras con 'ID [$codigoBarrasID]' en la base de datos.";
                Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ["success" => false, "message" => "No se encontró el código de barras en la base de datos"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al obtener el código de barras desde la base de datos',
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

    }

?>