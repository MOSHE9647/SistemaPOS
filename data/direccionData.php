<?php

    include_once 'data.php';
    include __DIR__ . '/../domain/Direccion.php';
    include_once __DIR__ . '/../utils/Variables.php';

    class DireccionData extends Data {

        // Constructor
		public function __construct() {
			parent::__construct();
		}

        public function insertDireccion($direccion) {
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    // Si no se puede establecer la conexión, lanza una excepción
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                // Obtiene el último ID de la tabla tbdireccion
                $queryGetLastId = "SELECT MAX(" . DIRECCION_ID . ") FROM " . TB_DIRECCION;
                $idCont = mysqli_query($conn, $queryGetLastId);
                $nextId = 1;
        
                // Calcula el siguiente ID para la nueva entrada
                if ($row = mysqli_fetch_row($idCont)) {
                    $nextId = (int) trim($row[0]) + 1;
                }
        
                // Crea una consulta y un statement SQL para insertar el registro
                $queryInsert = "INSERT INTO " . TB_DIRECCION . " ("
                    . DIRECCION_ID . ", "
                    . DIRECCION_PROVINCIA . ", "
                    . DIRECCION_CANTON . ", "
                    . DIRECCION_DISTRITO . ", "
                    . DIRECCION_BARRIO . ", "
                    . DIRECCION_SENNAS . ", "
                    . DIRECCION_DISTANCIA . ", "
                    . DIRECCION_ESTADO
                    . ") VALUES (?, ?, ?, ?, ?, ?, ?, true)";
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
                $result = mysqli_stmt_execute($stmt);
                return ["success" => true, "message" => "Dirección insertada exitosamente"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al insertar la dirección en la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra el statement y la conexión si están definidos
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function updateDireccion($direccion) {
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    // Si no se puede establecer la conexión, lanza una excepción
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
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
                        DIRECCION_ESTADO . " = true " .
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
                $result = mysqli_stmt_execute($stmt);
        
                // Devuelve el resultado de la consulta
                return ["success" => true, "message" => "Dirección actualizada exitosamente"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al actualizar la dirección en la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement si están definidos
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function getAllTBDireccion() {
            try {
                // Establece una conexion con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];

                // Obtenemos la lista de Impuestos
				$querySelect = "SELECT * FROM " . TB_DIRECCION . " WHERE " . DIRECCION_ESTADO . " != false ";
				$result = mysqli_query($conn, $querySelect);

				// Creamos la lista con los datos obtenidos
                $listaDirecciones = [];
                while ($row = mysqli_fetch_array($result)) {
                    $currentDireccion = new Direccion(
                        $row[DIRECCION_PROVINCIA],
                        $row[DIRECCION_CANTON],
                        $row[DIRECCION_DISTRITO],
                        $row[DIRECCION_BARRIO],
                        $row[DIRECCION_ID],
                        $row[DIRECCION_SENNAS],
                        $row[DIRECCION_DISTANCIA],
                        $row[DIRECCION_ESTADO]
                    );
                    array_push($listaDirecciones, $currentDireccion);
                }

                return ["success" => true, "listaDirecciones" => $listaDirecciones];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al obtener la lista de direcciones desde la base de datos'
                );

                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
			} finally {
				// Cerramos la conexion
				if (isset($conn)) { mysqli_close($conn); }
			}
        }

        public function getPaginatedDirecciones($page, $size, $sort = null) {
            try {
                // Verificar que la página y el tamaño sean números enteros positivos
                if (!is_numeric($page) || $page < 1) {
                    throw new Exception("El número de página debe ser un entero positivo.");
                }
                if (!is_numeric($size) || $size < 1) {
                    throw new Exception("El tamaño de la página debe ser un entero positivo.");
                }
                
                // Calcular el offset para la paginación
                $offset = ($page - 1) * $size;
        
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                // Consultar el total de registros en la tabla de direcciones
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_DIRECCION . " WHERE " . DIRECCION_ESTADO . " != false";
                $totalResult = mysqli_query($conn, $queryTotalCount);
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = (int) $totalRow['total'];
                $totalPages = ceil($totalRecords / $size);
        
                // Construir la consulta SQL para paginación
                $querySelect = "SELECT * FROM " . TB_DIRECCION . " WHERE " . DIRECCION_ESTADO . " != false ";
        
                // Añadir la cláusula de ordenamiento si se proporciona
                if ($sort) {
                    $querySelect .= "ORDER BY direccion" . $sort . " ";
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
        
                // Crear la lista con los datos obtenidos
                $listaDirecciones = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $listaDirecciones[] = [
                        'ID' => $row[DIRECCION_ID],
                        'Provincia' => $row[DIRECCION_PROVINCIA],
                        'Canton' => $row[DIRECCION_CANTON],
                        'Distrito' => $row[DIRECCION_DISTRITO],
                        'Barrio' => $row[DIRECCION_BARRIO],
                        'Sennas' => $row[DIRECCION_SENNAS],
                        'Distancia' => $row[DIRECCION_DISTANCIA],
                        'Estado' => $row[DIRECCION_ESTADO]
                    ];
                }
        
                // Devolver el resultado con la lista de direcciones y metadatos de paginación
                return [
                    "success" => true,
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "listaDirecciones" => $listaDirecciones
                ];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al obtener la lista de direcciones desde la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cerrar la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        private function direccionExiste($direccionID) {
            try {
                // Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];

                // Crea una consulta y un statement SQL para buscar el registro
                $queryCheck = "SELECT * FROM " . TB_DIRECCION . " WHERE " . DIRECCION_ID . " = ? AND " . DIRECCION_ESTADO . " != false";
                $stmt = mysqli_prepare($conn, $queryCheck);

                // Asignar los parámetros y ejecutar la consulta
				mysqli_stmt_bind_param($stmt, "i", $direccionID);
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
                    'Error al obtener la lista de direcciones desde la base de datos'
                );

                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
			} finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
        }

        public function deleteDireccion($direccionID) {
            try {
                // Verificar si existe el ID y que el Estado no sea false
                $check = $this->direccionExiste($direccionID);
                if (!$check["success"]) {
					return $check; // Error al verificar la existencia
				}
				if (!$check["exists"]) {
					throw new Exception("No existe ninguna direccion en la base de datos que coincida con la información proporcionada.");
				}

                // Establece una conexion con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];

                // Crea una consulta y un statement SQL para eliminar el registro (borrado logico)
				$queryDelete = "UPDATE " . TB_DIRECCION . " SET " . DIRECCION_ESTADO . " = false WHERE " . DIRECCION_ID . " = ?";
				$stmt = mysqli_prepare($conn, $queryDelete);
				mysqli_stmt_bind_param($stmt, 'i', $direccionID);

                // Ejecuta la consulta de eliminación
				$result = mysqli_stmt_execute($stmt);
		
				// Devuelve el resultado de la operación
				return ["success" => true, "message" => "Dirección eliminada exitosamente."];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al eliminar la direccion de la base de datos'
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