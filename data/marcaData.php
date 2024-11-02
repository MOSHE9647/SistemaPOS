<?php

	require_once dirname(__DIR__, 1) . '/data/data.php';
	require_once dirname(__DIR__, 1) . '/domain/Marca.php';
	require_once dirname(__DIR__, 1) . '/utils/Utils.php';
	require_once dirname(__DIR__, 1) . '/utils/Variables.php';

	class MarcaData extends Data {

		private $className;

		// Constructor
		public function __construct() {
			$this->className = get_class($this);
			parent::__construct();
		}

        // Función para verificar si una marca con el mismo nombre ya existe en la base de datos
        public function marcaExiste($marcaID = null, $marcaNombre = null, $update = false, $insert = false) {
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Inicializa la consulta base
                $queryCheck = "SELECT " . MARCA_ID . ", " . MARCA_ESTADO . " FROM " . TB_MARCA . " WHERE ";
                $params = [];
                $types = "";
                
                // Consulta para verificar si existe una marca con el ID ingresado
                if ($marcaID && (!$update && !$insert)) {
                    $queryCheck .= MARCA_ID . " = ? ";
                    $params[] = $marcaID;
                    $types .= "i";
                }
                
                // Consulta para verificar si existe una marca con el nombre ingresado
                else if ($insert && $marcaNombre) {
                    // Verificar existencia por nombre
                    $queryCheck .= MARCA_NOMBRE . " = ? ";
                    $params[] = $marcaNombre;
                    $types .= 's';
                }

                // Consulta en caso de actualizar para verificar si existe ya una marca con el mismo nombre además de la que se va a actualizar
                else if ($update && ($marcaNombre && $marcaID)) {
                    $queryCheck .= MARCA_NOMBRE . " = ? AND " . MARCA_ID . " <> ? ";
                    $params = [$marcaNombre, $marcaID];
                    $types .= 'si';
                }

                // En caso de no cumplirse ninguna condicion
                else {
                    // Registrar parámetros faltantes y lanzar excepción
                    $missingParamsLog = "Faltan parámetros para verificar la existencia de la marca:";
                    if (!$marcaID) $missingParamsLog .= " marcaID [" . ($marcaID ?? 'null') . "]";
                    if (!$marcaNombre) $missingParamsLog .= " marcaNombre [" . ($marcaNombre ?? 'null') . "]";
                    Utils::writeLog($missingParamsLog, DATA_LOG_FILE, WARN_MESSAGE, $this->className, __LINE__);
                    throw new Exception("Faltan parámetros para verificar la existencia de la marca en la base de datos.");
                }

                // Asignar los parámetros y ejecutar la consulta
                $stmt = mysqli_prepare($conn, $queryCheck);
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                // Verificar si existe una marca con el ID o nombre ingresado
                if ($row = mysqli_fetch_assoc($result)) {
                    // Verificar si está inactiva (bit de estado en 0)
                    $isInactive = $row[MARCA_ESTADO] == 0;
                    return ["success" => true, "exists" => true, "inactive" => $isInactive, "marcaID" => $row[MARCA_ID]];
                }

                // Retorna false si no se encontraron resultados
                $messageParams = [];
                if ($marcaID) { $messageParams[] = "'ID [$marcaID]'"; }
                if ($marcaNombre)  { $messageParams[] = "'Nombre [$marcaNombre]'"; }
                $params = implode(', ', $messageParams);

                $message = "No se encontró ninguna marca ($params) en la base de datos.";
                return ["success" => true, "exists" => false, "message" => $message];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al verificar la existencia de la marca en la base de datos',
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

        // Método para insertar una nueva marca
        public function insertMarca($marca, $conn = null) {
            $createdConnection = false;
            $stmt = null;

            try {
                // Obtener los valores de las propiedades del objeto
                $marcaNombre = $marca->getMarcaNombre();

                // Verificar si ya existe una marca con el mismo nombre
                $check = $this->marcaExiste(null, $marcaNombre, false, true);
                if (!$check["success"]) { throw new Exception($check["message"]); }

                // En caso de ya existir la marca pero estar inactiva
                if ($check["exists"] && $check["inactive"]) {
                    $message = "Ya existe una marca con el mismo nombre ($marcaNombre) en la base de datos, pero está inactiva. Desea reactivarla?";
                    return ["success" => true, "message" => $message, "inactive" => $result["inactive"], "id" => $result["marcaID"]];
                }

                // En caso de ya existir la marca y estar activa
                if ($check["exists"]) {
                    $message = "La marca con 'Nombre [$marcaNombre]' ya existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    return ["success" => true, "message" => "Ya existe una marca con el mismo nombre ($marcaNombre) en la base de datos."];
                }

                // Establece una conexión con la base de datos
                if (is_null($conn)) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConnection = true;
                }

                // Obtenemos el último ID de la tabla tb_marca
                $queryGetLastId = "SELECT MAX(" . MARCA_ID . ") FROM " . TB_MARCA;
                $idCont = mysqli_query($conn, $queryGetLastId);
                $nextId = 1;

                // Calcula el siguiente ID para la nueva entrada
                if ($row = mysqli_fetch_row($idCont)) {
                    $nextId = (int) trim($row[0]) + 1;
                }

                // Crea una consulta y un statement SQL para insertar el nuevo registro
                $queryInsert = 
                    "INSERT INTO " . TB_MARCA . " ("
                    . MARCA_ID . ", "
                    . MARCA_NOMBRE . ", "
                    . MARCA_DESCRIPCION . " " .
                    ") VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($conn, $queryInsert);

                // Obtener los valores de las propiedades faltantes
                $marcaDescripcion = $marca->getMarcaDescripcion();

                // Asignar los parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, 'iss', $nextId, $marcaNombre, $marcaDescripcion);
                mysqli_stmt_execute($stmt);

                return ["success" => true, "message" => "Marca insertada exitosamente", "id" => $nextId];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al insertar la marca en la base de datos',
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

        // Método para actualizar una marca existente
        public function updateMarca($marca, $conn = null) {
            $createdConnection = false;
            $stmt = null;

            try {
                // Obtener los valores de las propiedades del objeto
                $marcaID = $marca->getMarcaID();
                $marcaNombre = $marca->getMarcaNombre();

                // Verificar si la marca ya existe
                $check = $this->marcaExiste($marcaID);
                if (!$check["success"]) { throw new Exception($check["message"]); }

                // En caso de no existir la marca
                if (!$check["exists"]) {
                    $message = "No se encontró la marca con 'ID [$marcaID]' en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    return ["success" => false, "message" => "La marca seleccionada no existe en la base de datos."];
                }

                // Verifica que no exista otra marca con la misma información
                $check = $this->marcaExiste($marcaID, $marcaNombre, true);
                if (!$check["success"]) { throw new Exception($check["message"]); }

                // En caso de ya existir la marca
                if ($check["exists"]) {
                    $message = "La marca con 'Nombre [$marcaNombre]' ya existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    return ["success" => true, "message" => "Ya existe una marca con el mismo nombre ($marcaNombre) en la base de datos."];
                }

                // Establece una conexión con la base de datos
                if (is_null($conn)) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConnection = true;
                }

                // Crea la consulta SQL para actualizar la marca
                $queryUpdate = "UPDATE " . TB_MARCA . " SET "
                    . MARCA_NOMBRE . " = ?, "
                    . MARCA_DESCRIPCION . " = ? "
                    . "WHERE " . MARCA_ID . " = ?";

                $stmt = mysqli_prepare($conn, $queryUpdate);

                // Obtener los valores de las propiedades faltantes
                $marcaDescripcion = $marca->getMarcaDescripcion();

                // Asignar los parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, 'ssi', $marcaNombre, $marcaDescripcion, $marcaID);
                mysqli_stmt_execute($stmt);

                return ["success" => true, "message" => "Marca actualizada exitosamente"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al actualizar la marca en la base de datos',
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

        // Método para eliminar (desactivar) una marca
        public function deleteMarca($marcaID, $conn = null) {
            $createdConnection = false;
            $stmt = null;

            try {
                // Verificar si la marca ya existe
                $check = $this->marcaExiste($marcaID);
                if (!$check["success"]) { throw new Exception($check["message"]); }

                // En caso de no existir la marca
                if (!$check["exists"]) {
                    $message = "No se encontró la marca con 'ID [$marcaID]' en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    return ["success" => false, "message" => "La marca seleccionada no existe en la base de datos."];
                }

                // Establece una conexión con la base de datos
                if (is_null($conn)) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConnection = true;
                }

                // Crea la consulta SQL para desactivar la marca
                $queryDelete = "UPDATE " . TB_MARCA . " SET " . MARCA_ESTADO . " = false " . " WHERE " . MARCA_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryDelete);
                mysqli_stmt_bind_param($stmt, 'i', $marcaID);
                mysqli_stmt_execute($stmt);

                // Devolver mensaje de éxito
                return ["success" => true, "message" => "Marca eliminada exitosamente"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al eliminar la marca en la base de datos',
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
		
        // Método para obtener todas las marcas activas
        public function getAllTBMarcas($onlyActive = false, $deleted = false) {
            $conn = null;

            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Obtenemos la lista de marcas
                $querySelect = "SELECT * FROM " . TB_MARCA;
                if ($onlyActive) { $querySelect .= " WHERE " . MARCA_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE'); }
                $result = mysqli_query($conn, $querySelect);

                // Creamos la lista con los datos obtenidos
                $marcas = [];
                while ($row = mysqli_fetch_array($result)) {
                    $marca = new Marca(
                        $row[MARCA_ID],
                        $row[MARCA_NOMBRE],
                        $row[MARCA_DESCRIPCION],
                        $row[MARCA_ESTADO]
                    );
                    $marcas[] = $marca;
                }

                return ["success" => true, "marcas" => $marcas];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener las marcas de la base de datos',
                    $this->className
                );
            
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        // Método para obtener marcas con paginación
        public function getPaginatedMarcas($page, $size, $sort = null, $onlyActive = false, $deleted = false) {
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
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_MARCA;
                if ($onlyActive) { $queryTotalCount .= " WHERE " . MARCA_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE'); }
                
                // Ejecutar la consulta y obtener el total de registros
                $totalResult = mysqli_query($conn, $queryTotalCount);
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = (int) $totalRow['total'];
                $totalPages = ceil($totalRecords / $size);

                // Construir la consulta SQL para paginación
                $querySelect = "SELECT * FROM " . TB_MARCA;
                if ($onlyActive) { $querySelect .= " WHERE " . MARCA_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE'); }

                // Añadir la cláusula de ordenamiento si se proporciona
                if ($sort) { $querySelect .= " ORDER BY marca" . $sort; }

                // Añadir la cláusula de límite y desplazamiento
                $querySelect .= " LIMIT ? OFFSET ?";

                // Crear un statement y ejecutar la consulta
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, 'ii', $size, $offset);
                mysqli_stmt_execute($stmt);

                // Obtener los resultados de la consulta
                $result = mysqli_stmt_get_result($stmt);

                // Creamos la lista con los datos obtenidos
                $marcas = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $marca = new Marca(
                        $row[MARCA_ID],
                        $row[MARCA_NOMBRE],
                        $row[MARCA_DESCRIPCION],
                        $row[MARCA_ESTADO]
                    );
                    $marcas[] = $marca;
                }

                return [
                    "success" => true,
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "marcas" => $marcas
                ];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener la lista de marcas desde la base de datos',
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

        // Método para obtener una marca por su ID
        public function getMarcaByID($marcaID, $onlyActive = true, $deleted = false) {
            $conn = null; $stmt = null;

            try {
                // Verificar si la marca ya existe
                $check = $this->marcaExiste($marcaID);
                if (!$check["success"]) { throw new Exception($check["message"]); }

                // En caso de no existir la marca
                if (!$check["exists"]) {
                    $message = "No se encontró la marca con 'ID [$marcaID]' en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    return ["success" => false, "message" => "La marca seleccionada no existe en la base de datos."];
                }

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Consultar la marca por su ID
                $querySelect = "
                    SELECT 
                        * 
                    FROM " . 
                        TB_MARCA . " 
                    WHERE " . 
                        MARCA_ID . " = ?" . ($onlyActive ? " AND " . 
                        MARCA_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE') : '');
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, 'i', $marcaID);
                mysqli_stmt_execute($stmt);

                // Obtener los resultados de la consulta
                $result = mysqli_stmt_get_result($stmt);

                // Verificar si se encontró la marca
                if ($row = mysqli_fetch_assoc($result)) {
                    $marca = new Marca(
                        $row[MARCA_ID],
                        $row[MARCA_NOMBRE],
                        $row[MARCA_DESCRIPCION],
                        $row[MARCA_ESTADO]
                    );
                    return ["success" => true, "marca" => $marca];
                }

                // En caso de no encontrarse la marca
                $message = "No se encontró la marca con 'ID [$marcaID]' en la base de datos.";
                Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                return ["success" => false, "message" => "La marca seleccionada no existe en la base de datos."];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener la marca por su ID desde la base de datos',
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