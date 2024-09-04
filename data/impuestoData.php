<?php

    include_once 'data.php';
    include __DIR__ . '/../domain/Impuesto.php';
    require_once __DIR__ . '/../utils/Utils.php';
    require_once __DIR__ . '/../utils/Variables.php';

    class ImpuestoData extends Data {

        // Constructor
		public function __construct() {
			parent::__construct();
		}

        public function existeImpuesto($impuestoID = null, $impuestoNombre = null, $impuestoFecha = null, $update = false, $insert = false) {
            try {
                // Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) { throw new Exception($result["message"]); }
				$conn = $result["connection"];

                // Inicializa la consulta base
				$queryCheck = "SELECT 1 FROM " . TB_IMPUESTO . " WHERE ";
				$params = [];
				$types = "";

                // Consulta para verificar si existe un impuesto con el ID ingresado
                if ($impuestoID && (!$update && !$insert)) {
                    $queryCheck .= IMPUESTO_ID . " = ? AND " . IMPUESTO_ESTADO . " != FALSE";
					$params[] = $impuestoID;
					$types .= 'i';
                }

                // Consulta en caso de insertar para verificar si existe un impuesto con el nombre y fecha ingresados
                else if ($insert && ($impuestoNombre && $impuestoFecha)) {
                    $queryCheck .= IMPUESTO_NOMBRE . " = ? AND " . IMPUESTO_FECHA_VIGENCIA . " = ? AND " . IMPUESTO_ESTADO . " != FALSE";
                    $params[] = $impuestoNombre;
					$params[] = $impuestoFecha;
					$types .= 'ss';
                }

                // Consulta en caso de actualizar para verificar si existe ya un impuesto con el mismo nombre y fecha además del que se va a actualizar
                else if ($update && ($impuestoID && $impuestoNombre && $impuestoFecha)) {
                    $queryCheck .= IMPUESTO_NOMBRE . " = ? AND " . IMPUESTO_FECHA_VIGENCIA . " = ? AND " . IMPUESTO_ESTADO . " != FALSE AND " . IMPUESTO_ID . " != ?";
                    $params[] = $impuestoNombre;
                    $params[] = $impuestoFecha;
                    $params[] = $impuestoID;
                    $types .= 'ssi';
                }

                // En caso de no cumplirse ninguna condicion
                else {
                    $message = "No se proporcionaron los parámetros necesarios para verificar la existencia del impuesto";
					Utils::writeLog("$message ('impuestoID [$impuestoID]', 'impuestoNombre [$impuestoNombre]', 'impuestoFecha [$impuestoFecha]')", DATA_LOG_FILE);
                    return ["success" => false, "message" => "Ocurrió un error al verificar la existencia del impuesto en la base de datos"];
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
                Utils::writeLog("No se encontró ningún impuesto con el ID [$impuestoID] en la base de datos.", DATA_LOG_FILE);
                return ["success" => true, "exists" => false, "message" => "No se encontró el impuesto en la base de datos"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError($e->getCode(), $e->getMessage(),
                    'Ocurrió un error al verificar la existencia del impuesto en la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cerrar la conexión y liberar recursos
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function insertImpuesto($impuesto) {
            try {
                // Obtener los nombres de las propiedades del objeto para verificación
                $impuestoNombre = $impuesto->getImpuestoNombre();
                $impuestoFechaVigencia = $impuesto->getImpuestoFechaVigencia();

                // Verifica si ya existe un impuesto con el mismo nombre o fecha
                $check = $this->existeImpuesto(null, $impuestoNombre, $impuestoFechaVigencia, false, true);
                if (!$check["success"]) { return $check; } //<- Error al verificar la existencia

                // En caso de ya existir el impuesto
                if ($check["exists"]) {
					Utils::writeLog("El impuesto [$impuestoNombre] ya existe en la base de datos.", DATA_LOG_FILE);
					throw new Exception("Ya existe un impuesto con el mismo nombre o fecha de vigencia.");
				}

                // Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) { throw new Exception($result["message"]); }
				$conn = $result["connection"];

                // Obtenemos el último ID de la tabla tbimpuesto
				$queryGetLastId = "SELECT MAX(" . IMPUESTO_ID . ") FROM " . TB_IMPUESTO;
				$idCont = mysqli_query($conn, $queryGetLastId);
				$nextId = 1;
		
				// Calcula el siguiente ID para la nueva entrada
				if ($row = mysqli_fetch_row($idCont)) {
					$nextId = (int) trim($row[0]) + 1;
				}

                // Crea una consulta y un statement SQL para insertar el nuevo registro
				$queryInsert = 
                    "INSERT INTO " . TB_IMPUESTO . " ("
                        . IMPUESTO_ID . ", "
                        . IMPUESTO_NOMBRE . ", "
                        . IMPUESTO_VALOR . ", "
                        . IMPUESTO_DESCRIPCION . ", "
                        . IMPUESTO_FECHA_VIGENCIA . 
                    ") VALUES (?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $queryInsert);

                // // Obtener los valores de las propiedades faltantes
                $impuestoValor = $impuesto->getImpuestoValor();
				$impuestoDescripcion = $impuesto->getImpuestoDescripcion();

                // Asigna los valores a cada '?' de la consulta
				mysqli_stmt_bind_param(
					$stmt,
					'issss', // i: Entero, s: Cadena
					$nextId,
					$impuestoNombre,
					$impuestoValor,
					$impuestoDescripcion,
					$impuestoFechaVigencia
				);

                // Ejecuta la consulta de inserción
				$result = mysqli_stmt_execute($stmt);
				return ["success" => true, "message" => "Impuesto insertado exitosamente"];
            } catch (Exception $e) {
				// Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError($e->getCode(), $e->getMessage(),
                    'Error al insertar el impuesto en la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
			} finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
        }

        public function updateImpuesto($impuesto) {
            try {
                // Obtener el ID del impuesto
				$impuestoID = $impuesto->getImpuestoID();
                $impuestoNombre = $impuesto->getImpuestoNombre();
                $impuestoFechaVigencia = $impuesto->getImpuestoFechaVigencia();

                // Verifica si el impuesto existe en la base de datos
                $check = $this->existeImpuesto($impuestoID, null, null, false, false);
                if (!$check["success"]) { return $check; } //<- Error al verificar la existencia
                if (!$check["exists"]) { //<- El impuesto no existe
					throw new Exception("El impuesto con ID [$impuestoID] no existe en la base de datos.");
                }

                // Verifica que no exista otro impuesto con la misma información
                $check = $this->existeImpuesto($impuestoID, $impuestoNombre, $impuestoFechaVigencia, true, false);
                if (!$check["success"]) { return $check; } //<- Error al verificar la existencia
                if ($check["exists"]) { //<- El impuesto existe
					throw new Exception("Ya existe un impuesto con el mismo nombre o fecha de vigencia.");
                }

                // Establece una conexion con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) { throw new Exception($result["message"]); }
				$conn = $result["connection"];

                // Crea una consulta y un statement SQL para actualizar el registro
				$queryUpdate = 
                    "UPDATE " . TB_IMPUESTO . 
                    " SET " . 
                        IMPUESTO_NOMBRE . " = ?, " . 
                        IMPUESTO_VALOR . " = ?, " .
                        IMPUESTO_DESCRIPCION . " = ?, " .
                        IMPUESTO_FECHA_VIGENCIA . " = ? " .
                    "WHERE " . IMPUESTO_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryUpdate);

                // Obtener los valores de las propiedades faltantes
                $impuestoValor = $impuesto->getImpuestoValor();
                $impuestoDescripcion = $impuesto->getImpuestoDescripcion();

                // Asigna los valores a cada '?' de la consulta
                mysqli_stmt_bind_param(
					$stmt,
					'ssssi', // s: Cadena, i: Entero
					$impuestoNombre,
					$impuestoValor,
					$impuestoDescripcion,
					$impuestoFechaVigencia,
					$impuestoID
				);

                // Ejecuta la consulta de actualización
				$result = mysqli_stmt_execute($stmt);

				// Devuelve el resultado de la consulta
				return ["success" => true, "message" => "Impuesto actualizado exitosamente"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError($e->getCode(), $e->getMessage(),
                    'Error al actualizar el impuesto en la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
        }

        public function deleteImpuesto($impuestoID) {
            try {
                // Verifica si el impuesto existe en la base de datos
                $check = $this->existeImpuesto($impuestoID, null, null, false, false);
                if (!$check["success"]) { return $check; } //<- Error al verificar la existencia
                if (!$check["exists"]) { //<- El impuesto no existe
					throw new Exception("El impuesto con ID [$impuestoID] no existe en la base de datos.");
                }

                // Establece una conexion con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) { throw new Exception($result["message"]); }
				$conn = $result["connection"];

                // Crea una consulta y un statement SQL para eliminar el registro (borrado logico)
				$queryDelete = "UPDATE " . TB_IMPUESTO . " SET " . IMPUESTO_ESTADO . " = false WHERE " . IMPUESTO_ID . " = ?";
				$stmt = mysqli_prepare($conn, $queryDelete);
				mysqli_stmt_bind_param($stmt, 'i', $impuestoID);

                // Ejecuta la consulta de eliminación
				$result = mysqli_stmt_execute($stmt);
		
				// Devuelve el resultado de la operación
				return ["success" => true, "message" => "Impuesto eliminado exitosamente."];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError($e->getCode(), $e->getMessage(),
                    'Error al eliminar el impuesto de la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
			} finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
        }

        public function getAllTBImpuesto($onlyActiveOrInactive = false, $deleted = false) {
            try {
                // Establece una conexion con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) { throw new Exception($result["message"]); }
				$conn = $result["connection"];

                // Crea una consulta SQL para obtener todos los impuestos
                $querySelect = "SELECT * FROM " . TB_IMPUESTO;
                if ($onlyActiveOrInactive) { $querySelect .= " WHERE " . IMPUESTO_ESTADO . " != " . ($deleted ? "true" : "false"); }
				$result = mysqli_query($conn, $querySelect);

                // Creamos la lista con los datos obtenidos
                $impuestos = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $impuestos[] = [
                        'ID' => $row[IMPUESTO_ID],
                        'Nombre' => $row[IMPUESTO_NOMBRE],
                        'Valor' => $row[IMPUESTO_VALOR],
                        'Descripcion' => $row[IMPUESTO_DESCRIPCION],
                        'VigenciaISO' => Utils::formatearFecha($row[IMPUESTO_FECHA_VIGENCIA], 'Y-MM-dd'),
                        'Vigencia' => Utils::formatearFecha($row[IMPUESTO_FECHA_VIGENCIA]),
                        'Estado' => $row[IMPUESTO_ESTADO]
                    ];
                }

                // Devuelve la lista de impuestos
                return ["success" => true, "impuestos" => $impuestos];
            } catch (Exception $e) {
				// Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError($e->getCode(), $e->getMessage(),
                    'Error al obtener la lista de impuestos desde la base de datos'
                );

                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
			} finally {
				// Cerramos la conexion
				if (isset($conn)) { mysqli_close($conn); }
			}
        }

        public function getPaginatedImpuestos($page, $size, $sort = null, $onlyActiveOrInactive = false, $deleted = false) {
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
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_IMPUESTO . " ";
                if ($onlyActiveOrInactive) { $queryTotalCount .= "WHERE " . IMPUESTO_ESTADO . " != " . ($deleted ? "true" : "false") . " "; }

                $totalResult = mysqli_query($conn, $queryTotalCount);
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = (int) $totalRow['total'];
                $totalPages = ceil($totalRecords / $size);

				// Construir la consulta SQL para paginación
                $querySelect = "SELECT * FROM " . TB_IMPUESTO . " ";
                if ($onlyActiveOrInactive) { $querySelect .= "WHERE " . IMPUESTO_ESTADO . " != " . ($deleted ? "true" : "false") . " "; }

				// Añadir la cláusula de ordenamiento si se proporciona
                if ($sort) { $querySelect .= "ORDER BY impuesto" . $sort . " "; }

				// Añadir la cláusula de limitación y offset
                $querySelect .= "LIMIT ? OFFSET ?";

                Utils::writeLog($querySelect);

				// Preparar la consulta y vincular los parámetros
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, "ii", $size, $offset);

				// Ejecutar la consulta
                $result = mysqli_stmt_execute($stmt);

				// Obtener el resultado
                $result = mysqli_stmt_get_result($stmt);

				// Crear la lista con los datos obtenidos
				$impuestos = [];
				while ($row = mysqli_fetch_assoc($result)) {
					$impuestos[] = [
						'ID' => $row[IMPUESTO_ID],
						'Nombre' => $row[IMPUESTO_NOMBRE],
						'Valor' => $row[IMPUESTO_VALOR],
						'Descripcion' => $row[IMPUESTO_DESCRIPCION],
						'VigenciaISO' => Utils::formatearFecha($row[IMPUESTO_FECHA_VIGENCIA], 'Y-MM-dd'),
						'Vigencia' => Utils::formatearFecha($row[IMPUESTO_FECHA_VIGENCIA]),
						'Estado' => $row[IMPUESTO_ESTADO]
					];
				}

				return [
                    "success" => true,
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "impuestos" => $impuestos
                ];
			} catch (Exception $e) {
				// Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al obtener la lista de impuestos desde la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cerrar la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function getImpuestoByID($impuestoID, $json = true) {
            try {
                // Verifica si el impuesto existe en la base de datos
                $check = $this->existeImpuesto($impuestoID, null, null, false, false);
                if (!$check["success"]) { return $check; } //<- Error al verificar la existencia
                if (!$check["exists"]) { //<- El impuesto no existe
					throw new Exception("El impuesto con ID [$impuestoID] no existe en la base de datos.");
                }

                // Establece una conexion con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Crea una consulta SQL para obtener el impuesto
                $querySelect = "SELECT * FROM " . TB_IMPUESTO . " WHERE " . IMPUESTO_ID . " = ? AND " . IMPUESTO_ESTADO . " != false";
                $stmt = mysqli_prepare($conn, $querySelect);

                // Asignar los parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, 'i', $impuestoID);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                // Verifica si existe algún registro con los criterios dados
                if (mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    $impuesto = null;
                    if ($json) {
                        $impuesto = [
                            'ID' => $row[IMPUESTO_ID],
                            'Nombre' => $row[IMPUESTO_NOMBRE],
                            'Valor' => $row[IMPUESTO_VALOR],
                            'Descripcion' => $row[IMPUESTO_DESCRIPCION],
                            'VigenciaISO' => Utils::formatearFecha($row[IMPUESTO_FECHA_VIGENCIA], 'Y-MM-dd'),
                            'Vigencia' => Utils::formatearFecha($row[IMPUESTO_FECHA_VIGENCIA]),
                            'Estado' => $row[IMPUESTO_ESTADO]
                        ];
                    } else {
                        $impuesto = new Impuesto(
                            $row[IMPUESTO_NOMBRE],
                            $row[IMPUESTO_VALOR],
                            $row[IMPUESTO_FECHA_VIGENCIA],
                            $row[IMPUESTO_ID],
                            $row[IMPUESTO_DESCRIPCION],
                            $row[IMPUESTO_ESTADO]
                        );
                    }
                    return ["success" => true, "impuesto" => $impuesto];
                }

                // Retorna false si no se encontraron resultados
                Utils::writeLog("No se encontró ningún impuesto con el ID [$impuestoID] en la base de datos.", DATA_LOG_FILE);
                return ["success" => false, "message" => "No se encontró el impuesto en la base de datos"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError($e->getCode(), $e->getMessage(),
                    'Error al obtener el impuesto desde la base de datos'
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