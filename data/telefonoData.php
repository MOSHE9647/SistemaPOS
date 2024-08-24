<?php

    include_once 'data.php';
    include __DIR__ . '/../domain/Telefono.php';
    include_once __DIR__ . '/../utils/Variables.php';
    include_once __DIR__ . '/../utils/Utils.php';

    class TelefonoData extends Data {

        // Constructor
		public function __construct() {
			parent::__construct();
		}

        public function existeTelefono($telefonoID = null, $telefonoCodigoPais = null, $telefonoNumero = null) {
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                // Inicializa la consulta base
                $queryCheck = "SELECT 1 FROM " . TB_TELEFONO . " WHERE ";
                $params = [];
                $types = "";
        
                if ($telefonoID !== null) {
                    // Verificar existencia por ID
                    $queryCheck .= TELEFONO_ID . " = ? AND " . TELEFONO_ESTADO . " != false";
                    $params[] = $telefonoID;
                    $types .= 'i';
                } elseif ($telefonoCodigoPais !== null && $telefonoNumero !== null) {
                    // Verificar existencia por código de país y número de teléfono
                    $queryCheck .= TELEFONO_CODIGO_PAIS . " = ? AND " . TELEFONO_NUMERO . " = ? AND " . TELEFONO_ESTADO . " != false";
                    $params[] = $telefonoCodigoPais;
                    $params[] = $telefonoNumero;
                    $types .= 'ss';
                } else {
                    $message = "No se proporcionaron los parámetros necesarios para verificar la existencia del telefono";
                    Utils::writeLog("$message. Parámetros: 'telefonoID [$telefonoID]', 'telefonoCodigoPais [$telefonoCodigoPais]', 'telefonoNumero [$telefonoNumero]'", DATA_LOG_FILE);
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
                    'Error al verificar la existencia del telefono en la base de datos'
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
            try {
                // Obtener los valores de las propiedades del objeto
                $telefonoProveedorID = $telefono->getTelefonoProveedorID();
                $telefonoTipo = $telefono->getTelefonoTipo();
                $telefonoExtension = $telefono->getTelefonoExtension();
                $telefonoCodigoPais = $telefono->getTelefonoCodigoPais();
                $telefonoNumero = $telefono->getTelefonoNumero();

                // Verifica si ya existe el Telefono
                $check = $this->existeTelefono(null, $telefonoCodigoPais, $telefonoNumero);
                if (!$check['success']) {
                    return $check;
                }
                if ($check['exists']) {
                    Utils::writeLog("El telefono con 'Código [$telefonoCodigoPais]' y 'Número [$telefonoNumero]' ya existe en la base de datos.", DATA_LOG_FILE);
					throw new Exception("Ya existe un telefono con el mismo número y código de área.");
                }

                // Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];

                // Obtenemos el último ID de la tabla tbtelefono
				$queryGetLastId = "SELECT MAX(" . TELEFONO_ID . ") AS telefonoID FROM " . TB_TELEFONO;
				$idCont = mysqli_query($conn, $queryGetLastId);
				$nextId = 1;
		
				// Calcula el siguiente ID para la nueva entrada
				if ($row = mysqli_fetch_row($idCont)) {
					$nextId = (int) trim($row[0]) + 1;
				}

                // Crea una consulta y un statement SQL para insertar el nuevo registro
				$queryInsert = "INSERT INTO " . TB_TELEFONO . " ("
                    . TELEFONO_ID . ", "
                    . TELEFONO_PROVEEDOR_ID . ", "
                    . TELEFONO_TIPO . ", "
                    . TELEFONO_EXTENSION . ", "
                    . TELEFONO_CODIGO_PAIS . ", "
                    . TELEFONO_NUMERO
                    . ") VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $queryInsert);

                // Asigna los valores a cada '?' de la consulta
				mysqli_stmt_bind_param(
					$stmt,
					'iissss', // i: Entero, s: Cadena
					$nextId,
					$telefonoProveedorID,
					$telefonoTipo,
					$telefonoExtension,
					$telefonoCodigoPais,
                    $telefonoNumero
				);

                // Ejecuta la consulta de inserción
				$result = mysqli_stmt_execute($stmt);
				return ["success" => true, "message" => "Telefono insertado exitosamente"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al insertar el telefono en la base de datos'
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
            try {
                // Obtener el ID, Codigo de Pais y Número de telefono
                $telefonoID = $telefono->getTelefonoID();
                $telefonoCodigoPais = $telefono->getTelefonoCodigoPais();
                $telefonoNumero = $telefono->getTelefonoNumero();

                $checkID = $this->existeTelefono($telefonoID);
                if ($checkID['success']) {
					if ($checkID['exists']) {
                        $checkPhone = $this->existeTelefono(null, $telefonoCodigoPais, $telefonoNumero);
                        if (!$checkPhone['success']) { return $checkPhone; }
                        if ($checkPhone['exists']) {
                            Utils::writeLog("El telefono con 'Codigo [$telefonoCodigoPais]' y 'Numero [$telefonoNumero]' ya existe en la base de datos.", DATA_LOG_FILE);
                            throw new Exception("Ya existe un telefono con el mismo número y código de área.");
                        }
                    } else {
                        Utils::writeLog("El telefono con 'ID [$telefonoID]' no existe en la base de datos", DATA_LOG_FILE);
                        throw new Exception("No existe ningún telefono en la base de datos que coincida con la información proporcionada.");
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
				$queryUpdate = 
                    "UPDATE " . TB_TELEFONO . 
                    " SET " . 
                        TELEFONO_PROVEEDOR_ID . " = ?, " . 
                        TELEFONO_TIPO . " = ?, " .
                        TELEFONO_EXTENSION . " = ?, " .
                        TELEFONO_CODIGO_PAIS . " = ?, " .
                        TELEFONO_NUMERO . " = ? " .
                    "WHERE " . TELEFONO_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryUpdate);

                // Obtener los valores de las propiedades del objeto
                $telefonoProveedorID = $telefono->getTelefonoProveedorID();
                $telefonoTipo = $telefono->getTelefonoTipo();
                $telefonoExtension = $telefono->getTelefonoExtension();

                mysqli_stmt_bind_param(
					$stmt,
					'issssi', // s: Cadena, i: Entero
					$telefonoProveedorID,
					$telefonoTipo,
					$telefonoExtension,
					$telefonoCodigoPais,
					$telefonoNumero,
                    $telefonoID
				);

                // Ejecuta la consulta de actualización
				$result = mysqli_stmt_execute($stmt);

				// Devuelve el resultado de la consulta
				return ["success" => true, "message" => "Telefono actualizado exitosamente"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
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

        public function deleteTelefono($telefonoID) {
            try {
                // Verifica si existe un Telefono con el mismo ID en la BD
                $check = $this->existeTelefono($telefonoID);
                if (!$check["success"]) {
					return $check; // Error al verificar la existencia
				}
				if (!$check["exists"]) {
					Utils::writeLog("El telefono con 'ID [$telefonoID]' no existe en la base de datos.", DATA_LOG_FILE);
					throw new Exception("No existe ningún telefono en la base de datos que coincida con la información proporcionada.");
				}

                // Establece una conexion con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
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
                    $e->getCode(), 
                    $e->getMessage(),
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

        public function getPaginatedTelefonos($page, $size, $sort = null) {
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
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_TELEFONO . " WHERE " . TELEFONO_ESTADO . " != false";
                $totalResult = mysqli_query($conn, $queryTotalCount);
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = (int) $totalRow['total'];
                $totalPages = ceil($totalRecords / $size);

                // Construir la consulta SQL para paginación
                $querySelect = "
                    SELECT
                        T." . TELEFONO_ID . ",
                        P." . PROVEEDOR_ID . ",
                        P." . PROVEEDOR_NOMBRE . ",
                        P." . PROVEEDOR_EMAIL . ",
                        P." . PROVEEDOR_TIPO . ",
                        T." . TELEFONO_FECHA_CREACION . ",
                        T." . TELEFONO_TIPO . ",
                        T." . TELEFONO_EXTENSION . ",
                        T." . TELEFONO_CODIGO_PAIS . ",
                        T." . TELEFONO_NUMERO . ",
                        T." . TELEFONO_ESTADO . "
                    FROM
                        " . TB_TELEFONO . " T
                    INNER JOIN
                        " . TB_PROVEEDOR . " P ON T." . TELEFONO_PROVEEDOR_ID . " = P." . PROVEEDOR_ID . "
                    WHERE
                        T." . TELEFONO_ESTADO . " != FALSE AND 
                        P." . PROVEEDOR_ESTADO . " != FALSE
                ";

                // Añadir la cláusula de ordenamiento si se proporciona
                if ($sort) {
                    $querySelect .= "
                        ORDER BY 
                            telefono" . $sort . " 
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

                $listaTelefonos = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $listaTelefonos[] = [
                        'ID' => $row[TELEFONO_ID],
						'Proveedor' => [
                            'ID' => $row[PROVEEDOR_ID],
                            'Nombre' => $row[PROVEEDOR_NOMBRE],
                            'Email' => $row[PROVEEDOR_EMAIL],
                            'Tipo' => $row[PROVEEDOR_TIPO]
                        ],
						'FechaISO' => Utils::formatearFecha($row[TELEFONO_FECHA_CREACION], 'Y-MM-dd'),
						'Fecha' => Utils::formatearFecha($row[TELEFONO_FECHA_CREACION]),
						'Tipo' => $row[TELEFONO_TIPO],
						'Extension' => $row[TELEFONO_EXTENSION],
						'CodigoPais' => $row[TELEFONO_CODIGO_PAIS],
						'Numero' => $row[TELEFONO_NUMERO],
						'Estado' => $row[TELEFONO_ESTADO]
                    ];
                }

                return [
                    "success" => true,
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "listaTelefonos" => $listaTelefonos
                ];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al obtener la lista de telefonos desde la base de datos'
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