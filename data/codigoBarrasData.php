<?php

    include_once 'data.php';
    include __DIR__ . '/../domain/CodigoBarras.php';
    include_once __DIR__ . '/../utils/Variables.php';
    include_once __DIR__ . '/../utils/Utils.php';

    class CodigoBarrasData extends Data {

        // Constructor
		public function __construct() {
			parent::__construct();
		}

        public function existeCodigoBarras($codigoBarrasID = null, $codigoBarrasNumero = null, $update = false) {
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];

                // Inicializa la consulta base
                $queryCheck = "SELECT 1 FROM " . TB_CODIGO_BARRAS . " WHERE ";
                $params = [];
                $types = "";

                if ($codigoBarrasID !== null && !$update) {
                    // Verificar existencia por ID
                    $queryCheck .= CODIGO_BARRAS_ID . " = ? AND " . CODIGO_BARRAS_ESTADO . " != false";
                    $params[] = $codigoBarrasID;
                    $types .= 'i';
                } else if ($codigoBarrasNumero !== null) {
                    // Verificar existencia por código de barras
                    $queryCheck .= CODIGO_BARRAS_NUMERO . " = ? AND " . CODIGO_BARRAS_ESTADO . " != false";
                    $params[] = $codigoBarrasNumero;
                    $types .= 's';

                    if ($update && $codigoBarrasID !== null) {
                        $queryCheck .= " AND " . CODIGO_BARRAS_ID . " <> ?;";
                        $params[] = $codigoBarrasID;
                        $types .= 'i';
                    }
                } else {
                    $message = "No se proporcionaron los parámetros necesarios para verificar la existencia del código de barras";
                    Utils::writeLog("$message. Parámetros: 'codigoBarrasID [$codigoBarrasID]', 'codigoBarrasNumero [$codigoBarrasNumero]'", DATA_LOG_FILE);
                    throw new Exception($message);
                }
                $stmt = mysqli_prepare($conn, $queryCheck);
                
                // Asignar los parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
        
                // Verifica si existe algún registro con los criterios dados
                if (mysqli_num_rows($result) > 0) {
                    return ["success" => true, "exists" => true];
                }
        
                return ["success" => true, "exists" => false];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al verificar la existencia del código de barras en la base de datos'
                );
                
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function insertCodigoBarras($codigoBarras) {
            try {
                // Obtener los valores de las propiedades del objeto
                $codigoBarrasNumero = $codigoBarras->getCodigoBarrasNumero();

                // Verifica si ya existe el código de barras
                $check = $this->existeCodigoBarras(null, $codigoBarrasNumero);
                if (!$check['success']) {
                    return $check;
                }
                if ($check['exists']) {
                    Utils::writeLog("El código de barras con 'Número [$codigoBarrasNumero]' ya existe en la base de datos.", DATA_LOG_FILE);
					throw new Exception("Ya existe un código de barras con la misma información.");
                }

                // Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];

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
				return ["success" => true, "message" => "Código de Barras insertado exitosamente", "codigoID" => $nextId];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al insertar el código de barras en la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function updateCodigoBarras($codigoBarras) {
            try {
                // Obtener el ID y Codigo de Barras
                $codigoBarrasID = $codigoBarras->getCodigoBarrasID();
                $codigoBarrasNumero = $codigoBarras->getCodigoBarrasNumero();

                $checkID = $this->existeCodigoBarras($codigoBarrasID);
                if ($checkID['success']) {
					if ($checkID['exists']) {
                        $checkCodigo = $this->existeCodigoBarras($codigoBarrasID, $codigoBarrasNumero, true);
                        if (!$checkCodigo['success']) { return $checkCodigo; }
                        if ($checkCodigo['exists']) {
                            Utils::writeLog("El código de barras con 'Numero [$codigoBarrasNumero]' ya existe en la base de datos.", DATA_LOG_FILE);
                            throw new Exception("Ya existe un código de barras con el mismo número en la base de datos");
                        }
                    } else {
                        Utils::writeLog("El código de barras con 'ID [$codigoBarrasID]' no existe en la base de datos", DATA_LOG_FILE);
                        throw new Exception("No existe ningún código de barras en la base de datos que coincida con la información proporcionada.");
                    }
				} else {
                    Utils::writeLog($checkID['message'], DATA_LOG_FILE);
                    return $checkID;
                }

                // Establece una conexion con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];

                // Crea una consulta y un statement SQL para actualizar el registro
				$queryUpdate = "UPDATE " . TB_CODIGO_BARRAS . " SET " . CODIGO_BARRAS_NUMERO . " = ? " . "WHERE " . CODIGO_BARRAS_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryUpdate);
                mysqli_stmt_bind_param($stmt, 'si', $telefonoNumero, $telefonoID);

                // Ejecuta la consulta de actualización
				$result = mysqli_stmt_execute($stmt);

				// Devuelve el resultado de la consulta
				return ["success" => true, "message" => "Código de Barras actualizado exitosamente"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al actualizar el código de barras en la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function deleteCodigoBarras($codigoBarrasID) {
            try {
                // Verifica si existe un Código de Barras con el mismo ID en la BD
                $check = $this->existeCodigoBarrasID($codigoBarrasID);
                if (!$check["success"]) {
					return $check; // Error al verificar la existencia
				}
				if (!$check["exists"]) {
					Utils::writeLog("El código de barras con 'ID [$codigoBarrasID]' no existe en la base de datos.", DATA_LOG_FILE);
					throw new Exception("No existe ningún código de barras en la base de datos que coincida con la información proporcionada.");
				}

                // Establece una conexion con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];

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
                    'Error al eliminar el código de barras de la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function getPaginatedCodigosBarras($page, $size, $sort = null) {
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
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];

                // Consultar el total de registros
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_CODIGO_BARRAS . " WHERE " . CODIGO_BARRAS_ESTADO . " != false";
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
                    WHERE
                        C." . CODIGO_BARRAS_ESTADO . " != FALSE
                ";

                // Añadir la cláusula de ordenamiento si se proporciona
                if ($sort) {
                    $querySelect .= "
                        ORDER BY 
                            codigobarras" . $sort . " 
                    ";
                }

                // Añadir la cláusula de limitación y offset
                $querySelect .= "
                    LIMIT ? OFFSET ?
                ";

                // Preparar la consulta y vincular los parámetros
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, "ii", $size, $offset);

                // Ejecutar la consulta
                $result = mysqli_stmt_execute($stmt);

				// Obtener el resultado
                $result = mysqli_stmt_get_result($stmt);

                $listaCodigoBarras = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $listaCodigoBarras[] = [
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
                    "listaCodigoBarras" => $listaCodigoBarras
                ];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al obtener la lista de códigos de barras desde la base de datos'
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