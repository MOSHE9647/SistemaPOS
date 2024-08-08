<?php

    include_once 'data.php';
    include __DIR__ . '/../domain/Proveedor.php';
	include __DIR__ . '/../domain/Direccion.php';
	require_once __DIR__ . '/../utils/Variables.php';

    class ProveedorDireccionData extends Data {

        // Constructor
		public function __construct() {
			parent::__construct();
		}

        private function proveedorDireccionExisten($proveedorID = null, $direccionID = null) {
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                // Verificar existencia del proveedor
                if ($proveedorID !== null && is_numeric($proveedorID)) {
                    $stmtProveedor = $conn->prepare("SELECT 1 FROM " . TB_PROVEEDOR . " WHERE " . PROVEEDOR_ID . " = ? AND " . PROVEEDOR_ESTADO . " != false");
                    if (!$stmtProveedor) {
                        throw new Exception("Error al preparar la consulta del proveedor: " . $conn->error);
                    }
                    $stmtProveedor->bind_param("i", $proveedorID);
                    $stmtProveedor->execute();
                    $resultadoProveedor = $stmtProveedor->get_result();
                    if ($resultadoProveedor->num_rows === 0) {
                        throw new Exception("No se encontró al Proveedor en la base de datos.");
                    }
                } else {
                    throw new Exception("El ID del Proveedor no es válido");
                }
        
                // Verificar existencia de la dirección
                if ($direccionID !== null && is_numeric($direccionID)) {
                    $stmtDireccion = $conn->prepare("SELECT 1 FROM " . TB_DIRECCION . " WHERE " . DIRECCION_ID . " = ? AND " . DIRECCION_ESTADO . " != false");
                    if (!$stmtDireccion) {
                        throw new Exception("Error al preparar la consulta de la dirección: " . $conn->error);
                    }
                    $stmtDireccion->bind_param("i", $direccionID);
                    $stmtDireccion->execute();
                    $resultadoDireccion = $stmtDireccion->get_result();
                    if ($resultadoDireccion->num_rows === 0) {
                        throw new Exception("No se encontró la Dirección en la base de datos.");
                    }
                } else {
                    throw new Exception("El ID de la Dirección no es válido");
                }
        
                return ["success" => true];
            } catch (Exception $e) {
                // Devuelve el mensaje de error
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
                // Cierra el statement y la conexión
                if (isset($stmtProveedor)) { $stmtProveedor->close(); }
                if (isset($stmtDireccion)) { $stmtDireccion->close(); }
                if (isset($conn)) { $conn->close(); }
            }
        }

        private function verificarAsignacionDireccion($direccionID) {
            try {
                // Verifica que el ID sea válido
                if ($direccionID === null || !is_numeric($direccionID)) {
                    throw new Exception("El ID de la Dirección no es válido");
                }

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];

                // Verificar existencia de la direccion
                $queryCheck = "SELECT 1 FROM " . TB_PROVEEDOR_DIRECCION . " WHERE " . 
                    DIRECCION_ID . " = ? AND " . 
                    PROVEEDOR_DIRECCION_ESTADO . " != false"
                ;
                $stmt = mysqli_prepare($conn, $queryCheck);
				if (!$stmt) {
					throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
				}

                // Asignar los parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, 'i', $direccionID);
                mysqli_stmt_execute($stmt);
				$result = mysqli_stmt_get_result($stmt);

                // Verifica si existe algún registro con los criterios dados
				if (mysqli_num_rows($result) > 0) {
					return ["success" => true, "assigned" => true];
				}

                return ["success" => true, "assigned" => false];
            } catch (Exception $e) {
                // Devuelve el mensaje de error
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
        }

        public function addDireccionToProveedor($proveedorID, $direccionID) {
            try {
                // Verifica que el Proveedor y la Direccion existan en la BD
                $check = $this->proveedorDireccionExisten($proveedorID, $direccionID);
                if (!$check["success"]) {
                    throw new Exception($check["message"]);
                }

                // Verifica que la Direccion no esté asignada a otro Proveedor
                $check = $this->verificarAsignacionDireccion($direccionID);
                if (!$check['success']) {
                    return $check; // Error al verificar si estaba asignada
                }
                if ($check['assigned']) {
                    throw new Exception("No pueden existir 2 o más proveedores con la misma dirección.");
                }

                // Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];

                // Obtenemos el último ID de la tabla tbproveedordireccion
				$queryGetLastId = "SELECT MAX(" . PROVEEDOR_DIRECCION_ID . ") FROM " . TB_PROVEEDOR_DIRECCION;
				$idCont = mysqli_query($conn, $queryGetLastId);
				if (!$idCont) {
					throw new Exception("Error al obtener la información del proveedor de la base de datos: " . mysqli_error($conn));
				}
				$nextId = 1;

                // Calcula el siguiente ID para la nueva entrada
				if ($row = mysqli_fetch_row($idCont)) {
					$nextId = (int) trim($row[0]) + 1;
				}

                // Crea una consulta y un statement SQL para insertar el registro
                $queryInsert = "INSERT INTO " . TB_PROVEEDOR_DIRECCION . " ("
                    . PROVEEDOR_DIRECCION_ID . ", "
                    . PROVEEDOR_ID . ", "
                    . DIRECCION_ID . ", "
                    . PROVEEDOR_DIRECCION_ESTADO
                    . ") VALUES (?, ?, ?, true)";
                $stmt = mysqli_prepare($conn, $queryInsert);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }

                // Prepara y ejecuta la consulta de inserción
                mysqli_stmt_bind_param($stmt, 'iii', $nextId, $proveedorID, $direccionID);
                $result = mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception("Error al asignarle la dirección al proveedor: " . mysqli_error($conn));
				}

                return ["success" => true, "message" => "Dirección asignada exitosamente"];
            } catch (Exception $e) {
                // Devuelve el mensaje de error
				return ["success" => false, "message" => $e->getMessage()];
			} finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
        }

        private function existeAsignacion($proveedorID, $direccionID) {
            try {
                // Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];

                // Crea una consulta y un statement SQL para buscar el registro
                $queryCheck = "SELECT 1 FROM " . TB_PROVEEDOR_DIRECCION . " WHERE " . 
                    PROVEEDOR_ID . " = ? AND " . 
                    DIRECCION_ID . " = ? AND " . 
                    PROVEEDOR_DIRECCION_ESTADO . " != false";
                $stmt = mysqli_prepare($conn, $queryCheck);
				if (!$stmt) {
					throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
				}

                // Asignar los parámetros y ejecutar la consulta
				mysqli_stmt_bind_param($stmt, "ii", $proveedorID, $direccionID);
				mysqli_stmt_execute($stmt);
				$result = mysqli_stmt_get_result($stmt);

                // Verifica si existe algún registro con los criterios dados
				if (mysqli_num_rows($result) > 0) {
					return ["success" => true, "exists" => true];
				}
		
				return ["success" => true, "exists" => false];
            } catch (Exception $e) {
                // Devuelve el mensaje de error
				return ["success" => false, "message" => $e->getMessage()];
			} finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
        }

        public function removeDireccionFromProveedor($proveedorID, $direccionID) {
            try {
                // Verifica que el Proveedor y la Direccion existan en la BD
                $check = $this->proveedorDireccionExisten($proveedorID, $direccionID);
                if (!$check["success"]) {
                    throw new Exception($check["message"]);
                }

                // Verificar si la asignacion entre el proveedor y la direccion existe
                $check = $this->existeAsignacion($proveedorID, $direccionID);
                if (!$check["success"]) {
					return $check; // Error al verificar la existencia
				}
				if (!$check["exists"]) {
					throw new Exception("Ningún proveedor tiene esta dirección asignada.");
				}

                // Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];

                // Crea una consulta y un statement SQL para eliminar el registro (borrado logico)
				$queryDelete = 
                    "UPDATE " . TB_PROVEEDOR_DIRECCION . " SET " .
						PROVEEDOR_DIRECCION_ESTADO . " = false, " .
					"WHERE " .
                        PROVEEDOR_ID . " = ? AND " .
                        DIRECCION_ID . " = ?";
				$stmt = mysqli_prepare($conn, $queryDelete);
				if (!$stmt) {
					throw new Exception("Error al preparar la consulta de eliminación: " . mysqli_error($conn));
				}

                // Prepara y ejecuta la consulta de eliminación
                mysqli_stmt_bind_param($stmt, 'i', $direccionID);
				$result = mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception("Error al eliminar la dirección del proveedor: " . mysqli_error($conn));
				}
		
				// Devuelve el resultado de la operación
				return ["success" => true, "message" => "Dirección eliminada exitosamente."];
            } catch (Exception $e) {
                // Devuelve el mensaje de error
				return ["success" => false, "message" => $e->getMessage()];
			} finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
        }

        public function getDireccionesByProveedor($proveedorID) {
            try {
                // Verifica que el Proveedor exista en la BD
                $check = $this->proveedorDireccionExisten($proveedorID);
                if (!$check["success"]) {
                    throw new Exception($check["message"]);
                }

                // Establece una conexion con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];

                // Obtenemos la lista de direcciones
				$querySelect = "
                    SELECT
                        D." . DIRECCION_ID . ",
                        D." . DIRECCION_PROVINCIA . ",
                        D." . DIRECCION_CANTON . ",
                        D." . DIRECCION_DISTRITO . ",
                        D." . DIRECCION_BARRIO . ",
                        D." . DIRECCION_SENNAS . ",
                        D." . DIRECCION_DISTANCIA . ",
                        D." . DIRECCION_ESTADO . "
                    FROM
                        " . TB_DIRECCION . " D
                    INNER JOIN
                        " . TB_PROVEEDOR_DIRECCION . " PD ON D." . DIRECCION_ID . " = PD." . DIRECCION_ID . "
                    WHERE
                        PD." . PROVEEDOR_ID . " = ? AND
                        D." . DIRECCION_ESTADO . " != FALSE AND
                        PD." . PROVEEDOR_DIRECCION_ESTADO . " != FALSE;
                ";
				$result = mysqli_query($conn, $querySelect);

                // Verificamos si ocurrió un error
				if (!$result) {
					throw new Exception("Ocurrió un error al obtener las direcciones del proveedor: " . mysqli_error($conn));
				}

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
                // Devuelve el mensaje de error
				return ["success" => false, "message" => $e->getMessage()];
			} finally {
				// Cerramos la conexion
				if (isset($conn)) { mysqli_close($conn); }
			}
        }

        public function getProveedoresByDireccion($direccionID) {
            try {
                // Verifica que la Direccion exista en la BD
                $check = $this->proveedorDireccionExisten($direccionID);
                if (!$check["success"]) {
                    throw new Exception($check["message"]);
                }

                // Establece una conexion con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];

                // Obtenemos la lista de proveedores
				$querySelect = "
                    SELECT
                        P." . PROVEEDOR_ID . ",
                        P." . PROVEEDOR_ESTADO . "
                    FROM
                        " . TB_PROVEEDOR . " P
                    INNER JOIN
                        " . TB_PROVEEDOR_DIRECCION . " PD ON P." . PROVEEDOR_ID . " = PD." . PROVEEDOR_ID . "
                    WHERE
                        PD." . DIRECCION_ID . " = 5 AND
                        P." . PROVEEDOR_ESTADO . " != FALSE AND
                        PD." . PROVEEDOR_DIRECCION_ESTADO . " != FALSE;
                ";
				$result = mysqli_query($conn, $querySelect);

                // Verificamos si ocurrió un error
				if (!$result) {
					throw new Exception("Ocurrió un error al obtener la lista de proveedores: " . mysqli_error($conn));
				}

                // Creamos la lista con los datos obtenidos
                $listaProveedores = [];
                while ($row = mysqli_fetch_array($result)) {
                    // AGREGAR LOS DEMÁS ATRIBUTOS
                    $currentProveedor = new Proveedor(
                        $row[DIRECCION_ID],
                        $row[DIRECCION_ESTADO]
                    );
                    array_push($listaProveedores, $currentProveedor);
                }

                return ["success" => true, "listaProveedores" => $listaProveedores];
            } catch (Exception $e) {
                // Devuelve el mensaje de error
				return ["success" => false, "message" => $e->getMessage()];
			} finally {
				// Cerramos la conexion
				if (isset($conn)) { mysqli_close($conn); }
			}
        }

    }

?>