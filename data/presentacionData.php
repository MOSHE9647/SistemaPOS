<?php

	require_once dirname(__DIR__, 1) . '/data/data.php';
	require_once dirname(__DIR__, 1) . '/domain/Presentacion.php';
	require_once dirname(__DIR__, 1) . '/utils/Utils.php';
	require_once dirname(__DIR__, 1) . '/utils/Variables.php';

	class PresentacionData extends Data {

		private $className;

		// Constructor
		public function __construct() {
			$this->className = get_class($this);
			parent::__construct();
		}

        // Función para verificar si una presentación con el mismo nombre ya existe en la base de datos
        public function presentacionExiste($presentacionID = null, $presentacionNombre = null, $update = false, $insert = false) {
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Inicializa la consulta base
                $queryCheck = "SELECT " . PRESENTACION_ID . ", " . PRESENTACION_ESTADO . " FROM " . TB_PRESENTACION . " WHERE ";
                $params = [];
                $types = "";
                
                // Consulta para verificar si existe una presentación con el ID ingresado
                if ($presentacionID && (!$update && !$insert)) {
                    $queryCheck .= PRESENTACION_ID . " = ? ";
                    $params[] = $presentacionID;
                    $types .= "i";
                }
                
                // Consulta para verificar si existe una presentación con el nombre ingresado
                else if ($insert && $presentacionNombre) {
                    // Verificar existencia por nombre
                    $queryCheck .= PRESENTACION_NOMBRE . " = ? ";
                    $params[] = $presentacionNombre;
                    $types .= 's';
                }

                // Consulta en caso de actualizar para verificar si existe ya una presentación con el mismo nombre además de la que se va a actualizar
                else if ($update && ($presentacionNombre && $presentacionID)) {
                    $queryCheck .= PRESENTACION_NOMBRE . " = ? AND " . PRESENTACION_ID . " <> ? ";
                    $params = [$presentacionNombre, $presentacionID];
                    $types .= 'si';
                }

                // En caso de no cumplirse ninguna condicion
                else {
                    // Registrar parámetros faltantes y lanzar excepción
                    $missingParamsLog = "Faltan parámetros para verificar la existencia de la presentación:";
                    if (!$presentacionID) $missingParamsLog .= " presentacionID [" . ($presentacionID ?? 'null') . "]";
                    if (!$presentacionNombre) $missingParamsLog .= " presentacionNombre [" . ($presentacionNombre ?? 'null') . "]";
                    Utils::writeLog($missingParamsLog, DATA_LOG_FILE, WARN_MESSAGE, $this->className, __LINE__);
                    throw new Exception("Faltan parámetros para verificar la existencia de la presentación en la base de datos.");
                }

                // Asignar los parámetros y ejecutar la consulta
                $stmt = mysqli_prepare($conn, $queryCheck);
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                // Verificar si existe una presentación con el ID o nombre ingresado
                if ($row = mysqli_fetch_assoc($result)) {
                    // Verificar si está inactiva (bit de estado en 0)
                    $isInactive = $row[PRESENTACION_ESTADO] == 0;
                    return ["success" => true, "exists" => true, "inactive" => $isInactive, "presentacionID" => $row[PRESENTACION_ID]];
                }

                // Retorna false si no se encontraron resultados
                $messageParams = [];
                if ($presentacionID) { $messageParams[] = "'ID [$presentacionID]'"; }
                if ($presentacionNombre)  { $messageParams[] = "'Nombre [$presentacionNombre]'"; }
                $params = implode(', ', $messageParams);

                $message = "No se encontró ninguna presentación ($params) en la base de datos.";
                return ["success" => true, "exists" => false, "message" => $message];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al verificar la existencia de la presentación en la base de datos',
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

        // Método para insertar una nueva presentación
        public function insertPresentacion($presentacion, $conn = null) {
            $createdConnection = false;
            $stmt = null;

            try {
                // Obtener los valores de las propiedades del objeto
                $presentacionNombre = $presentacion->getPresentacionNombre();

                // Verificar si ya existe una presentación con el mismo nombre
                $check = $this->presentacionExiste(null, $presentacionNombre, false, true);
                if (!$check["success"]) { throw new Exception($check["message"]); }

                // En caso de ya existir la presentación pero estar inactiva
                if ($check["exists"] && $check["inactive"]) {
                    $message = "Ya existe una presentación con el mismo nombre ($presentacionNombre) en la base de datos, pero está inactiva. Desea reactivarla?";
                    return ["success" => true, "message" => $message, "inactive" => $result["inactive"], "id" => $result["presentacionID"]];
                }

                // En caso de ya existir la presentación y estar activa
                if ($check["exists"]) {
                    $message = "La presentación con 'Nombre [$presentacionNombre]' ya existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    return ["success" => true, "message" => "Ya existe una presentación con el mismo nombre ($presentacionNombre) en la base de datos."];
                }

                // Establece una conexión con la base de datos
                if (is_null($conn)) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConnection = true;
                }

                // Obtenemos el último ID de la tabla tb_presentacion
                $queryGetLastId = "SELECT MAX(" . PRESENTACION_ID . ") FROM " . TB_PRESENTACION;
                $idCont = mysqli_query($conn, $queryGetLastId);
                $nextId = 1;

                // Calcula el siguiente ID para la nueva entrada
                if ($row = mysqli_fetch_row($idCont)) {
                    $nextId = (int) trim($row[0]) + 1;
                }

                // Crea una consulta y un statement SQL para insertar el nuevo registro
                $queryInsert = 
                    "INSERT INTO " . TB_PRESENTACION . " ("
                    . PRESENTACION_ID . ", "
                    . PRESENTACION_NOMBRE . ", "
                    . PRESENTACION_DESCRIPCION .", "
                    . PRESENTACION_ESTADO ." " . 
                    ") VALUES (?, ?, ?, true)";
                $stmt = mysqli_prepare($conn, $queryInsert);

                // Obtener los valores de las propiedades faltantes
                $presentacionDescripcion = $presentacion->getPresentacionDescripcion();

                // Asignar los parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, 'iss', $nextId, $presentacionNombre, $presentacionDescripcion);
                mysqli_stmt_execute($stmt);

                return ["success" => true, "message" => "Presentación insertada exitosamente", "id" => $nextId];
            }catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al insertar la presentación en la base de datos',
                    $this->className
                );
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if ($createdConnection && isset($conn)) { mysqli_close($conn); }
            }
        }

        // Método para actualizar una presentación existente
        public function updatePresentacion($presentacion, $conn = null) {
            $createdConnection = false;
            $stmt = null;

            try {
                // Obtener los valores de las propiedades del objeto
                $presentacionID = $presentacion->getPresentacionID();
                $presentacionNombre = $presentacion->getPresentacionNombre();

                // Verificar si la presentación ya existe
                $check = $this->presentacionExiste($presentacionID);
                if (!$check["success"]) { throw new Exception($check["message"]); }

                // En caso de no existir la presentación
                if (!$check["exists"]) {
                    $message = "No se encontró la presentación con 'ID [$presentacionID]' en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    return ["success" => false, "message" => "La presentación seleccionada no existe en la base de datos."];
                }

                // Verifica que no exista otra presentación con la misma información
                $check = $this->presentacionExiste($presentacionID, $presentacionNombre, true);
                if (!$check["success"]) { throw new Exception($check["message"]); }

                // En caso de ya existir la presentación
                if ($check["exists"]) {
                    $message = "La presentación con 'Nombre [$presentacionNombre]' ya existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    return ["success" => true, "message" => "Ya existe una presentación con el mismo nombre ($presentacionNombre) en la base de datos."];
                }

                // Establece una conexión con la base de datos
                if (is_null($conn)) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConnection = true;
                }

                // Crea la consulta SQL para actualizar la presentación
                $queryUpdate = "UPDATE " . TB_PRESENTACION . " SET "
                    . PRESENTACION_NOMBRE . " = ?, "
                    . PRESENTACION_DESCRIPCION . " = ?, "
                    . PRESENTACION_ESTADO . " = true "
                    . "WHERE " . PRESENTACION_ID . " = ?";

                $stmt = mysqli_prepare($conn, $queryUpdate);

                // Obtener los valores de las propiedades faltantes
                $presentacionDescripcion = $presentacion->getPresentacionDescripcion();

                // Asignar los parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, 'ssi', $presentacionNombre, $presentacionDescripcion, $presentacionID);
                mysqli_stmt_execute($stmt);

                return ["success" => true, "message" => "Presentación actualizada exitosamente"];
            }catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al actualizar la presentación en la base de datos',
                    $this->className
                );

                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if ($createdConnection && isset($conn)) { mysqli_close($conn); }
            }
        }

        // Método para eliminar (desactivar) una presentación
        public function deletePresentacion($presentacionID, $conn = null) {
            $createdConnection = false;
            $stmt = null;

            try {
                // Verificar si la presentación ya existe
                $check = $this->presentacionExiste($presentacionID);
                if (!$check["success"]) { throw new Exception($check["message"]); }

                // En caso de no existir la presentación
                if (!$check["exists"]) {
                    $message = "No se encontró la presentación con 'ID [$presentacionID]' en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    return ["success" => false, "message" => "La presentación seleccionada no existe en la base de datos."];
                }

                // Establece una conexión con la base de datos
                if (is_null($conn)) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConnection = true;
                }

                // Crea la consulta SQL para desactivar la presentación
                $queryDelete = "UPDATE " . TB_PRESENTACION . " SET " . PRESENTACION_ESTADO . " = false " . " WHERE " . PRESENTACION_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryDelete);
                mysqli_stmt_bind_param($stmt, 'i', $presentacionID);
                mysqli_stmt_execute($stmt);

                // Devolver mensaje de éxito
                return ["success" => true, "message" => "Presentación eliminada exitosamente"];
            }catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al eliminar la presentación en la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if ($createdConnection && isset($conn)) { mysqli_close($conn); }
            }
        }
		
        // Método para obtener todas las presentaciones activas
        public function getAllTBPresentaciones($onlyActive = false, $deleted = false) {
            $conn = null;

            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Obtenemos la lista de presentaciones
                $querySelect = "SELECT * FROM " . TB_PRESENTACION;
                if ($onlyActive) { $querySelect .= " WHERE " . PRESENTACION_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE'); }
                $result = mysqli_query($conn, $querySelect);

                // Creamos la lista con los datos obtenidos
                $presentaciones = [];
                while ($row = mysqli_fetch_array($result)) {
                    $presentacion = new Presentacion(
                        $row[PRESENTACION_ID],
                        $row[PRESENTACION_NOMBRE],
                        $row[PRESENTACION_DESCRIPCION],
                        $row[PRESENTACION_ESTADO]
                    );
                    $presentaciones[] = $presentacion;
                }

                return ["success" => true, "presentaciones" => $presentaciones];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener las presentaciones de la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            }  finally {
                // Cierra la conexión y el statement
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        // Método para obtener presentaciones con paginación
        public function getPaginatedPresentaciones($page, $size, $sort = null, $onlyActive = false, $deleted = false) {
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
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_PRESENTACION;
                if ($onlyActive) { $queryTotalCount .= " WHERE " . PRESENTACION_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE'); }
                
                // Ejecutar la consulta y obtener el total de registros
                $totalResult = mysqli_query($conn, $queryTotalCount);
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = (int) $totalRow['total'];
                $totalPages = ceil($totalRecords / $size);

                // Construir la consulta SQL para paginación
                $querySelect = "SELECT * FROM " . TB_PRESENTACION;
                if ($onlyActive) { $querySelect .= " WHERE " . PRESENTACION_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE'); }

                // Añadir la cláusula de ordenamiento si se proporciona
                if ($sort) { $querySelect .= " ORDER BY presentacion" . $sort; }

                // Añadir la cláusula de límite y desplazamiento
                $querySelect .= " LIMIT ? OFFSET ?";

                // Crear un statement y ejecutar la consulta
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, 'ii', $size, $offset);
                mysqli_stmt_execute($stmt);

                // Obtener los resultados de la consulta
                $result = mysqli_stmt_get_result($stmt);

                // Creamos la lista con los datos obtenidos
                $presentaciones = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $presentacion = new Presentacion(
                        $row[PRESENTACION_ID],
                        $row[PRESENTACION_NOMBRE],
                        $row[PRESENTACION_DESCRIPCION],
                        $row[PRESENTACION_ESTADO]
                    );
                    $presentaciones[] = $presentacion;
                }

                return [
                    "success" => true,
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "presentaciones" => $presentaciones
                ];
            }catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener la lista de presentaciones desde la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            }  finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        // Método para obtener una presentación por su ID
        public function getPresentacionByID($presentacionID, $onlyActive = false, $deleted = false) {
            $conn = null; $stmt = null;

            try {
                // Verificar si la presentación ya existe
                $check = $this->presentacionExiste($presentacionID);
                if (!$check["success"]) { throw new Exception($check["message"]); }

                // En caso de no existir la presentación
                if (!$check["exists"]) {
                    $message = "No se encontró la presentación con 'ID [$presentacionID]' en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    return ["success" => false, "message" => "La presentación seleccionada no existe en la base de datos."];
                }

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Consultar la presentación por su ID
                $querySelect = "
                    SELECT 
                        * 
                    FROM " . 
                        TB_PRESENTACION . " 
                    WHERE " . 
                        PRESENTACION_ID . " = ?" . ($onlyActive ? " AND " . 
                        PRESENTACION_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE') : '');
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, 'i', $presentacionID);
                mysqli_stmt_execute($stmt);

                // Obtener los resultados de la consulta
                $result = mysqli_stmt_get_result($stmt);

                // Verificar si se encontró la presentación
                if ($row = mysqli_fetch_assoc($result)) {
                    $presentacion = new Presentacion(
                        $row[PRESENTACION_ID],
                        $row[PRESENTACION_NOMBRE],
                        $row[PRESENTACION_DESCRIPCION],
                        $row[PRESENTACION_ESTADO]
                    );
                    return ["success" => true, "presentacion" => $presentacion];
                }

                // En caso de no encontrarse la presentación
                $message = "No se encontró la presentación con 'ID [$presentacionID]' en la base de datos.";
                Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                return ["success" => false, "message" => "La presentación seleccionada no existe en la base de datos."];
            }catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener la presentación por su ID desde la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            }  finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

	}

?>