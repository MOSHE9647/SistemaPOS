<?php

    require_once dirname(__DIR__, 1) . '/data/data.php';
    require_once dirname(__DIR__, 1) . '/domain/Direccion.php';
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';
    require_once dirname(__DIR__, 1) . '/utils/Variables.php';

    class DireccionData extends Data {

        private $className;

        // Constructor
		public function __construct() {
            $this->className = get_class($this);
			parent::__construct();
		}

        public function existeDireccion($direccion, $update = false, $insert = false) {
            $conn = null; $stmt = null;
            
            try {
                if (!$direccion) {
                    $errorMessage = "No se proporcionó una dirección para verificar su existencia.";
                    Utils::writeLog($errorMessage, DATA_LOG_FILE, WARN_MESSAGE, $this->className, __LINE__);
                    throw new Exception($errorMessage);
                }
            
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Crea una consulta y un statement SQL para buscar el registro
                $queryCheck = "
                    SELECT " . 
                        DIRECCION_ID . ", " .
                        DIRECCION_PROVINCIA . ", " .
                        DIRECCION_CANTON . ", " .
                        DIRECCION_DISTRITO . ", " .
                        DIRECCION_BARRIO . ", " . 
                        DIRECCION_SENNAS . ", " .
                        DIRECCION_ESTADO . "
                    FROM " .
                        TB_DIRECCION;
                $params = [];
                $types = "";

                // Obtener los valores de las propiedades del objeto $direccion
                $direccionID = $direccion->getDireccionID();
                if ($direccionID === -1 && $update) {
                    $missingParamsLog = "Faltan parámetros para verificar la existencia de la dirección:";
                    $missingParamsLog .= " direccionID [" . ($direccionID ?? 'null') . "]";
                    Utils::writeLog($missingParamsLog, DATA_LOG_FILE, WARN_MESSAGE, $this->className, __LINE__);
                    throw new Exception("Faltan parámetros para verificar la existencia de la dirección.");
                }

                // Consulta para verificar si existe una Direccion con el ID ingresado
                if ((!$update && !$insert)) {
                    $queryCheck .= " WHERE " . DIRECCION_ID . " = ?";
                    $params[] = $direccionID;
                    $types .= "i";
                }
                else if ($update && !$insert) {
                    $queryCheck .= " WHERE " . DIRECCION_ID . " <> ?";
                    $params[] = $direccionID;
                    $types .= "i";
                }

                // Preparar la consulta y vincular los parámetros
                $stmt = mysqli_prepare($conn, $queryCheck);
                if (!$insert) mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                // Verifica si existe algún registro con los criterios dados
                if ((!$insert && !$update) && mysqli_num_rows($result) > 0) {
                    return ["success" => true, "exists" => true];
                }

                // Verificar si existe una dirección con los mismos datos
                while ($row = mysqli_fetch_assoc($result)) {
                    // Obtener los datos de la dirección desde la base de datos
                    $id = $row[DIRECCION_ID];
                    $sennas = $row[DIRECCION_SENNAS];
                    $direccionEnBD = new Direccion(
                        $id,
                        $row[DIRECCION_PROVINCIA],
                        $row[DIRECCION_CANTON],
                        $row[DIRECCION_DISTRITO],
                        $row[DIRECCION_BARRIO],
                        $sennas
                    );
                    $inactivo = $row[DIRECCION_ESTADO] == 0;

                    // Concatenar los datos de la dirección para comparar con la dirección ingresada
                    $direccionCompletaBD = $direccionEnBD->getDireccionCompleta();
                    $direccionCompleta = $direccion->getDireccionCompleta();

                    // // Verificar si la dirección ya existe en la base de datos con un 98% de similitud
                    // $exists = Utils::esSimiliar($direccionCompletaBD, $direccionCompleta, 98, $sennas != "");
                    // if ($exists) {
                    //     $message = "Ya existe una dirección similar en la base de datos: [" . $direccionCompletaBD . "].";
                    //     return ["success" => true, "exists" => $exists, "message" => $message, "inactive" => $inactivo, "direccionID" => $id];
                    // }

                    if ($direccionCompletaBD == $direccionCompleta) {
                        $message = "Ya existe una dirección igual en la base de datos: [" . $direccionCompletaBD . "].";
                        return ["success" => true, "exists" => true, "message" => $message, "inactive" => $inactivo, "direccionID" => $id];
                    }
                }
        
                // Retorna false si no se encontraron resultados
                $message = "No se encontró ninguna dirección que coincida en la base de datos.";
                return ["success" => true, "exists" => false, "message" => $message];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al verificar la existencia de la dirección en la base de datos',
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

        public function insertDireccion($direccion, $conn = null) {
            $createdConn = false;
            $stmt = null;

            try {
                // Verificar si la dirección ya existe en la base de datos
                $check = $this->existeDireccion($direccion, false, true);
                if (!$check["success"]) { throw new Exception($check['message']); } // Error al verificar la existencia

                // En caso de ya existir pero estar inactiva, se activa y se actualiza
                if ($check["exists"] && $check["inactive"]) {
                    $direccion->setDireccionID($check["direccionID"]);
                    return $this->updateDireccion($direccion, $conn);
                }
                else if ($check["exists"]) {
                    $message = "Ya existe una dirección similar en la base de datos: [" . $direccion->getDireccionCompleta() . "]";
                    Utils::writeLog($message, DATA_LOG_FILE, WARN_MESSAGE, $this->className, __LINE__);
                    return ['success' => false, 'message' => $message];
                }

                // Establece una conexión con la base de datos
                if (is_null($conn)) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConn = true;
                }
        
                // Obtiene el último ID de la tabla tbdireccion
                $queryGetLastId = "SELECT MAX(" . DIRECCION_ID . ") FROM " . TB_DIRECCION;
                $idCont = mysqli_query($conn, $queryGetLastId);
                $nextId = 1;
        
                // Calcula el siguiente ID para la nueva entrada
                if ($row = mysqli_fetch_row($idCont)) {
                    $nextId = (int) trim($row[0]) + 1;
                }
        
                // Crea una consulta y un statement SQL para insertar el registro
                $queryInsert = 
                    "INSERT INTO " . TB_DIRECCION . " ("
                        . DIRECCION_ID . ", "
                        . DIRECCION_PROVINCIA . ", "
                        . DIRECCION_CANTON . ", "
                        . DIRECCION_DISTRITO . ", "
                        . DIRECCION_BARRIO . ", "
                        . DIRECCION_SENNAS . ", "
                        . DIRECCION_DISTANCIA
                    . ") VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $queryInsert);
        
                // Obtener los valores de las propiedades del objeto $direccion
                $direccionProvincia = $direccion->getDireccionProvincia();
                $direccionCanton = $direccion->getDireccionCanton();
                $direccionDistrito = $direccion->getDireccionDistrito();
                $direccionBarrio = $direccion->getDireccionBarrio();
                $direccionSennas = $direccion->getDireccionSennas();
                $direccionDistancia = $direccion->getDireccionDistancia();
        
                // Asigna los valores a cada '?' de la consulta
                mysqli_stmt_bind_param(
                    $stmt,
                    'issssss', // i: Entero, s: Cadena
                    $nextId,
                    $direccionProvincia,
                    $direccionCanton,
                    $direccionDistrito,
                    $direccionBarrio,
                    $direccionSennas,
                    $direccionDistancia
                );
        
                // Ejecuta la consulta de inserción
                mysqli_stmt_execute($stmt);
                return ["success" => true, "message" => "Dirección insertada exitosamente", "id" => $nextId];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al insertar la dirección en la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra el statement y la conexión si están definidos
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if ($createdConn && isset($conn)) { mysqli_close($conn); }
            }
        }

        public function updateDireccion($direccion, $conn = null) {
            $createdConn = false; 
            $stmt = null;
            
            try {
                // Establece una conexion con la base de datos
				if (is_null($conn)) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConn = true;
                }
        
                // Crea una consulta y un statement SQL para actualizar el registro
                $queryUpdate = 
                    "UPDATE " . TB_DIRECCION . 
                    " SET " . 
                        DIRECCION_PROVINCIA . " = ?, " . 
                        DIRECCION_CANTON . " = ?, " .
                        DIRECCION_DISTRITO . " = ?, " .
                        DIRECCION_BARRIO . " = ?, " .
                        DIRECCION_SENNAS . " = ?, " .
                        DIRECCION_DISTANCIA . " = ?, " .
                        DIRECCION_ESTADO . " = TRUE " .
                    "WHERE " . DIRECCION_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryUpdate);
        
                // Obtener los valores de las propiedades del objeto $direccion
                $direccionID = $direccion->getDireccionID();
                $direccionProvincia = $direccion->getDireccionProvincia();
                $direccionCanton = $direccion->getDireccionCanton();
                $direccionDistrito = $direccion->getDireccionDistrito();
                $direccionBarrio = $direccion->getDireccionBarrio();
                $direccionSennas = $direccion->getDireccionSennas();
                $direccionDistancia = $direccion->getDireccionDistancia();
        
                // Asigna los valores a cada '?' de la consulta
                mysqli_stmt_bind_param(
                    $stmt,
                    'ssssssi', // s: Cadena, i: Entero
                    $direccionProvincia,
                    $direccionCanton,
                    $direccionDistrito,
                    $direccionBarrio,
                    $direccionSennas,
                    $direccionDistancia,
                    $direccionID
                );
        
                // Ejecuta la consulta de actualización
                mysqli_stmt_execute($stmt);
                return ["success" => true, "message" => "Dirección actualizada exitosamente"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al actualizar la dirección en la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement si están definidos
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if ($createdConn && isset($conn)) { mysqli_close($conn); }
            }
        }

        public function deleteDireccion($direccionID, $conn = null) {
            $createdConn = false;
            $stmt = null;

            try {
                // Verificar si existe la dirección en la base de datos
                $direccion = new Direccion($direccionID);
                $check = $this->existeDireccion($direccion);
                if (!$check["success"]) { throw new Exception($check['message']); } // Error al verificar la existencia
                if (!$check["exists"]) { // No existe la dirección
                    $message = "La dirección con 'ID [$direccionID]' no existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    return ['success' => false, 'message' => "La dirección seleccionada no existe en la base de datos."];
                }

                // Establece una conexion con la base de datos
				if (is_null($conn)) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConn = true;
                }

                // Crea una consulta y un statement SQL para eliminar el registro (borrado logico)
				$queryDelete = "UPDATE " . TB_DIRECCION . " SET " . DIRECCION_ESTADO . " = FALSE WHERE " . DIRECCION_ID . " = ?";
				$stmt = mysqli_prepare($conn, $queryDelete);
				mysqli_stmt_bind_param($stmt, 'i', $direccionID);
                mysqli_stmt_execute($stmt);
		
				// Devuelve el resultado de la operación
				return ["success" => true, "message" => "Dirección eliminada exitosamente."];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al eliminar la direccion de la base de datos',
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

        public function getAllTBDireccion($onlyActive = false, $deleted = false) {
            $conn = null;
            
            try {
                // Establece una conexion con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) { throw new Exception($result["message"]); }
				$conn = $result["connection"];

                // Obtenemos la lista de Impuestos
				$querySelect = "SELECT * FROM " . TB_DIRECCION . " WHERE " . DIRECCION_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE');
				$result = mysqli_query($conn, $querySelect);

				// Creamos la lista con los datos obtenidos
                $direcciones = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $direccion = new Direccion(
                        $row[DIRECCION_ID],
                        $row[DIRECCION_PROVINCIA],
                        $row[DIRECCION_CANTON],
                        $row[DIRECCION_DISTRITO],
                        $row[DIRECCION_BARRIO],
                        $row[DIRECCION_SENNAS],
                        $row[DIRECCION_DISTANCIA],
                        $row[DIRECCION_ESTADO]
                    );
                    $direcciones[] = $direccion;
                }

                return ["success" => true, "direcciones" => $direcciones];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al obtener la lista de direcciones desde la base de datos',
                    $this->className
                );

                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
			} finally {
				// Cerramos la conexion
				if (isset($conn)) { mysqli_close($conn); }
			}
        }

        public function getPaginatedDirecciones($page, $size, $sort = null, $onlyActive = false, $deleted = false) {
            $conn = null; $stmt = null;
            
            try {
                // Calcular el offset para la consulta SQL
                $offset = ($page - 1) * $size;
        
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
        
                // Consultar el total de registros en la tabla de direcciones
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_DIRECCION;
                if ($onlyActive) { $queryTotalCount .= " WHERE " . DIRECCION_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE'); }

                // Ejecutar la consulta y obtener el total de registros
                $totalResult = mysqli_query($conn, $queryTotalCount);
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = (int) $totalRow['total'];
                $totalPages = ceil($totalRecords / $size);
        
                // Construir la consulta SQL para paginación
                $querySelect = "SELECT * FROM " . TB_DIRECCION;
                if ($onlyActive) { $querySelect .= " WHERE " . DIRECCION_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE'); }
        
                // Añadir la cláusula de ordenamiento si se proporciona
                if ($sort) { $querySelect .= " ORDER BY direccion" . $sort; }
        
                // Añadir la cláusula de limitación y offset
                $querySelect .= " LIMIT ? OFFSET ?";
        
                // Preparar la consulta y vincular los parámetros
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, "ii", $size, $offset);
                mysqli_stmt_execute($stmt);
        
                // Obtener el resultado
                $result = mysqli_stmt_get_result($stmt);
        
                // Crear la lista con los datos obtenidos
                $direcciones = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $direccion = new Direccion(
                        $row[DIRECCION_ID],
                        $row[DIRECCION_PROVINCIA],
                        $row[DIRECCION_CANTON],
                        $row[DIRECCION_DISTRITO],
                        $row[DIRECCION_BARRIO],
                        $row[DIRECCION_SENNAS],
                        $row[DIRECCION_DISTANCIA],
                        $row[DIRECCION_ESTADO]
                    );
                    $direcciones[] = $direccion;
                }
        
                // Devolver el resultado con la lista de direcciones y metadatos de paginación
                return [
                    "success" => true,
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "direcciones" => $direcciones
                ];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al obtener la lista de direcciones desde la base de datos',
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
        
        public function getDireccionByID($direccionID, $onlyActive = true, $deleted = false) {
            $conn = null; $stmt = null;

            try {
                // Verificar si existe la dirección en la base de datos
                $direccion = new Direccion($direccionID);
                $check = $this->existeDireccion($direccion);
                if (!$check["success"]) { throw new Exception($check['message']); } // Error al verificar la existencia
                if (!$check["exists"]) { // No existe la dirección
                    $message = "La dirección con 'ID [$direccionID]' no existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    return ['success' => false, 'message' => "La dirección seleccionada no existe en la base de datos."];
                }

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Crea una consulta y un statement SQL para buscar el registro
                $querySelect = "
                    SELECT 
                        * 
                    FROM " . 
                        TB_DIRECCION . " 
                    WHERE " . 
                        DIRECCION_ID . " = ?" . ($onlyActive ? " AND " . 
                        DIRECCION_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE') : '')
                ;
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, "i", $direccionID);
                mysqli_stmt_execute($stmt);

                // Obtener el resultado de la consulta
                $result = mysqli_stmt_get_result($stmt);

                // Verifica si existe algún registro con los criterios dados
                if ($row = mysqli_fetch_assoc($result)) {
                    $direccion = new Direccion(
                        $row[DIRECCION_ID],
                        $row[DIRECCION_PROVINCIA],
                        $row[DIRECCION_CANTON],
                        $row[DIRECCION_DISTRITO],
                        $row[DIRECCION_BARRIO],
                        $row[DIRECCION_SENNAS],
                        $row[DIRECCION_DISTANCIA],
                        $row[DIRECCION_ESTADO]
                    );
                    return ["success" => true, "direccion" => $direccion];
                }
        
                // Retorna false si no se encontraron resultados
                $message = "No se encontró ninguna dirección con el 'ID [$direccionID]' en la base de datos.";
                Utils::writeLog($message, DATA_LOG_FILE, WARN_MESSAGE, $this->className, __LINE__);
                return ["success" => true, "message" => "No se encontró la dirección en la base de datos."];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al obtener la dirección desde la base de datos',
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