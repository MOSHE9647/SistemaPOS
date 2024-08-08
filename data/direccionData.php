<?php

    include_once 'data.php';
    include __DIR__ . '/../domain/Direccion.php';
    include_once __DIR__ . '/../utils/Variables.php';

    class DireccionData extends Data {

        // Constructor
		public function __construct() {
			parent::__construct();
		}

        private function validarDireccion($direccion) {
            try {
                // Obtener los valores de las propiedades del objeto
                $direccionID = $direccion->getDireccionID();
                $provincia = $direccion->getDireccionProvincia();
                $canton = $direccion->getDireccionCanton();
                $distrito = $direccion->getDireccionDistrito();
                $distancia = $direccion->getDireccionDistancia();

                // Verifica que las propiedades no estén vacías
                if ($direccionID === null || !is_numeric($direccionID)) {
                    throw new Exception("El ID de la dirección está vacío o no es válido");
                }
                if ($provincia === null || empty($provincia) || is_numeric($provincia)) {
                    throw new Exception("El campo 'Provincia' está vacío o no es válido");
                }
                if ($canton === null || empty($canton) || is_numeric($canton)) {
                    throw new Exception("El campo 'Cantón' está vacío o no es válido");
                }
                if ($distrito === null || empty($distrito) || is_numeric($distrito)) {
                    throw new Exception("El campo 'Distrito' está vacío o no es válido");
                }
                if ($distancia === null || empty($distancia) || !is_numeric($distancia)) {
                    throw new Exception("El campo 'Distancia' en la Dirección está vacío o no es válido");
                }

                return ["is_valid" => true];
            } catch (Exception $e) {
                return ["is_valid" => false, "message" => $e->getMessage()];
            }
        }

        public function insertDireccion($direccion) {
            try {
                // Valida que la direccion sea correcta
                $check = $this->validarDireccion($direccion);
                if (!$check["is_valid"]) {
                    throw new Exception($check["message"]);
                }

                // Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];

                // Obtenemos el último ID de la tabla tbdireccion
				$queryGetLastId = "SELECT MAX(" . DIRECCION_ID . ") FROM " . TB_DIRECCION;
				$idCont = mysqli_query($conn, $queryGetLastId);
				if (!$idCont) {
					throw new Exception("Error al obtener el ID de la dirección en la base de datos: " . mysqli_error($conn));
				}
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
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }

                // Obtener los valores de las propiedades del objeto
                $direccionProvincia = $direccion->getDireccionProvincia();
                $direccionCanton = $direccion->getDireccionCanton();
                $direccionDistrito = $direccion->getDireccionDistrito();
                $direccionBarrio = $direccion->getDireccionBarrio();
                $direccionSennas = $direccion->getDireccionSennas();
                $direccionDistancia = $direccion->getDireccionDistancia();

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
				if (!$result) {
					throw new Exception("Error al insertar la dirección: " . mysqli_error($conn));
				}
                
                return ["success" => true, "message" => "Dirección insertada exitosamente"];
			} catch (Exception $e) {
				// Devuelve el mensaje de error
				return ["success" => false, "message" => $e->getMessage()];
			} finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
        }

        public function updateDireccion($direccion) {
            try {
                // Valida que la direccion sea correcta
                $check = $this->validarDireccion($direccion);
                if (!$check["is_valid"]) {
                    throw new Exception($check["message"]);
                }

                // Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
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
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }

                // Obtener los valores de las propiedades del objeto
                $direccionID = $direccion->getDireccionID();
                $direccionProvincia = $direccion->getDireccionProvincia();
                $direccionCanton = $direccion->getDireccionCanton();
                $direccionDistrito = $direccion->getDireccionDistrito();
                $direccionBarrio = $direccion->getDireccionBarrio();
                $direccionSennas = $direccion->getDireccionSennas();
                $direccionDistancia = $direccion->getDireccionDistancia();

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
				if (!$result) {
					throw new Exception("Error al actualizar el impuesto: " . mysqli_error($conn));
				}

                // Devuelve el resultado de la consulta
				return ["success" => true, "message" => "Dirección actualizada exitosamente"];
            } catch (Exception $e) {
                // Devuelve el mensaje de error
				return ["success" => false, "message" => $e->getMessage()];
			} finally {
				// Cierra la conexión y el statement
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

                // Verificamos si ocurrió un error
				if (!$result) {
					throw new Exception("Ocurrió un error al obtener la información de la base de datos: " . mysqli_error($conn));
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
                // Devuleve el mensaje de error
				return ["success" => false, "message" => $e->getMessage()];
			} finally {
				// Cerramos la conexion
				if (isset($conn)) {
					mysqli_close($conn);
				}
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
				if (!$stmt) {
					throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
				}

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
                // Devuelve el mensaje de error
				return ["success" => false, "message" => $e->getMessage()];
			} finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
        }

        public function deleteDireccion($direccionID) {
            try {
                // Verifica que el ID de la dirección no esté vacío y sea numérico
				if (empty($direccionID) || !is_numeric($direccionID)) {
					throw new Exception("ID de Dirección inválido.");
				}

                // Verificar si existe el ID y que el Estado no sea false
                $check = $this->direccionExiste($direccionID);
                if (!$check["success"]) {
					return $check; // Error al verificar la existencia
				}
				if (!$check["exists"]) {
					throw new Exception("No se encontró una dirección con el ID [" . $direccionID . "]");
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
				if (!$stmt) {
					throw new Exception("Error al preparar la consulta de eliminación: " . mysqli_error($conn));
				}

				mysqli_stmt_bind_param($stmt, 'i', $direccionID);

                // Ejecuta la consulta de eliminación
				$result = mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception("Error al eliminar la dirección: " . mysqli_error($conn));
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
        
    }

?>