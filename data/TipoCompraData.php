<?php

	require_once 'data.php';
	require_once __DIR__ . '/../domain/TipoCompra.php';
	require_once __DIR__ . '/../utils/Utils.php';
	require_once __DIR__ . '/../utils/Variables.php';

	class TipoCompraData extends Data {

		// Constructor
		public function __construct() {
			parent::__construct();
		}

		// Función para verificar si un tipo de compra con el mismo nombre ya existe en la bd
		private function tipoCompraExiste($tipoCompraID = null, $tipoCompraNombre = null) {
			try {
				// Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];
				
				// Inicializa la consulta base
				$queryCheck = "SELECT * FROM " . TB_TIPO_COMPRA . " WHERE ";
				$params = [];
				$types = "";
				
				if ($tipoCompraID !== null) {
					// Verificar existencia por ID y que el estado no sea false
					$queryCheck .= TIPO_COMPRA_ID . " = ? AND " . TIPO_COMPRA_ESTADO . " != false";
					$params[] = $tipoCompraID;
					$types .= 'i';
				} elseif ($tipoCompraNombre !== null) {
					// Verificar existencia por nombre
					$queryCheck .= TIPO_COMPRA_NOMBRE . " = ? AND " . TIPO_COMPRA_ESTADO . " != false";
					$params[] = $tipoCompraNombre;
					$types .= 's';
				} else {
					$message = "No se proporcionaron los parámetros necesarios para verificar la existencia del tipo de compra";
					Utils::writeLog("$message. Parámetros: 'tipoCompraID [$tipoCompraID]', 'tipoCompraNombre [$tipoCompraNombre]'", DATA_LOG_FILE);
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
                    'Error al verificar la existencia del tipo de compra en la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
			} finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
		}

		public function insertTipoCompra($tipoCompra) {
			try {
				// Obtener los valores de las propiedades del objeto
				$tipoCompraNombre = $tipoCompra->getTipoCompraNombre();
				$tipoCompraDescripcion = $tipoCompra->getTipoCompraDescripcion();
		
				// Verifica si el tipo de compra ya existe
				$check = $this->tipoCompraExiste(null, $tipoCompraNombre);
				if (!$check["success"]) {
					return $check; // Error al verificar la existencia
				}
				if ($check["exists"]) {
					Utils::writeLog("El tipo de compra [$tipoCompraNombre] ya existe en la base de datos.", DATA_LOG_FILE);
					throw new Exception("Ya existe un tipo de compra con el mismo nombre.");
				}
		
				// Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];
		
				// Obtenemos el último ID de la tabla tbtipocompra
				$queryGetLastId = "SELECT MAX(" . TIPO_COMPRA_ID . ") AS tipoCompraID FROM " . TB_TIPO_COMPRA;
				$idCont = mysqli_query($conn, $queryGetLastId);
				$nextId = 1;
		
				// Calcula el siguiente ID para la nueva entrada
				if ($row = mysqli_fetch_row($idCont)) {
					$nextId = (int) trim($row[0]) + 1;
				}
		
				// Crea una consulta y un statement SQL para insertar el nuevo registro
				$queryInsert = "INSERT INTO " . TB_TIPO_COMPRA . " ("
                    . TIPO_COMPRA_ID . ", "
                    . TIPO_COMPRA_NOMBRE . ", "
                    . TIPO_COMPRA_DESCRIPCION . ", "
                    . TIPO_COMPRA_ESTADO
                    . ") VALUES (?, ?, ?, true)";
				$stmt = mysqli_prepare($conn, $queryInsert);
		
				// Asigna los valores a cada '?' de la consulta
				mysqli_stmt_bind_param(
					$stmt,
					'iss', // i: Entero, s: Cadena
					$nextId,
					$tipoCompraNombre,
					$tipoCompraDescripcion
				);
		
				// Ejecuta la consulta de inserción
				$result = mysqli_stmt_execute($stmt);
				return ["success" => true, "message" => "Tipo de compra insertado exitosamente"];
			} catch (Exception $e) {
				// Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al insertar el tipo de compra en la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
			} finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
		}

		public function updateTipoCompra($tipoCompra) {
			try {
				// Obtener el ID del tipo de compra
				$tipoCompraID = $tipoCompra->getTipoCompraID();

				// Verifica si el tipo de compra existe en la base de datos
				$check = $this->tipoCompraExiste($tipoCompraID);
				if (!$check["success"]) {
					return $check; // Error al verificar la existencia
				}
				if (!$check["exists"]) {
					Utils::writeLog("El tipo de compra con ID [$tipoCompraID] no existe en la base de datos.", DATA_LOG_FILE);
					throw new Exception("No existe ningún tipo de compra en la base de datos que coincida con la información proporcionada.");
				}
		
				// Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];

				// Crea una consulta y un statement SQL para actualizar el registro
				$queryUpdate = 
					"UPDATE " . TB_TIPO_COMPRA . 
					" SET " . 
						TIPO_COMPRA_NOMBRE . " = ?, " . 
						TIPO_COMPRA_DESCRIPCION . " = ?, " .
						TIPO_COMPRA_ESTADO . " = true " .
					"WHERE " . TIPO_COMPRA_ID . " = ?";
				$stmt = mysqli_prepare($conn, $queryUpdate);

				// Obtener los valores de las propiedades del objeto
				$tipoCompraNombre = $tipoCompra->getTipoCompraNombre();
				$tipoCompraDescripcion = $tipoCompra->getTipoCompraDescripcion();

				mysqli_stmt_bind_param(
					$stmt,
					'ssi', // s: Cadena, i: Entero
					$tipoCompraNombre,
					$tipoCompraDescripcion,
					$tipoCompraID
				);

				// Ejecuta la consulta de actualización
				$result = mysqli_stmt_execute($stmt);

				// Devuelve el resultado de la consulta
				return ["success" => true, "message" => "Tipo de compra actualizado exitosamente"];
			} catch (Exception $e) {
				// Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al actualizar el tipo de compra en la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
			} finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
		}

		public function deleteTipoCompra($tipoCompraID) {
			try {
				// Verifica si el tipo de compra existe en la base de datos
				$check = $this->tipoCompraExiste($tipoCompraID);
				if (!$check["success"]) {
					return $check; // Error al verificar la existencia
				}
				if (!$check["exists"]) {
					Utils::writeLog("El tipo de compra con ID [$tipoCompraID] no existe en la base de datos.", DATA_LOG_FILE);
					throw new Exception("No existe ningún tipo de compra en la base de datos que coincida con la información proporcionada.");
				}

				// Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];

				// Crea una consulta y un statement SQL para eliminar el registro
				$queryDelete = "UPDATE " . TB_TIPO_COMPRA . " SET " . TIPO_COMPRA_ESTADO . " = false WHERE " . TIPO_COMPRA_ID . " = ?";
				$stmt = mysqli_prepare($conn, $queryDelete);

				mysqli_stmt_bind_param($stmt, 'i', $tipoCompraID);

				// Ejecuta la consulta de eliminación
				$result = mysqli_stmt_execute($stmt);

				return ["success" => true, "message" => "Tipo de compra eliminado exitosamente"];
			} catch (Exception $e) {
				// Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al eliminar el tipo de compra en la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
			} finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
		}

		public function getTipoCompra($tipoCompraID = null, $tipoCompraNombre = null) {
			try {
				// Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];

				// Inicializa la consulta base
				$queryGet = "SELECT * FROM " . TB_TIPO_COMPRA . " WHERE ";
				$params = [];
				$types = "";
				
				if ($tipoCompraID !== null) {
					// Obtener tipo de compra por ID
					$queryGet .= TIPO_COMPRA_ID . " = ? AND " . TIPO_COMPRA_ESTADO . " != false";
					$params[] = $tipoCompraID;
					$types .= 'i';
				} elseif ($tipoCompraNombre !== null) {
					// Obtener tipo de compra por nombre
					$queryGet .= TIPO_COMPRA_NOMBRE . " = ? AND " . TIPO_COMPRA_ESTADO . " != false";
					$params[] = $tipoCompraNombre;
					$types .= 's';
				} else {
					$message = "No se proporcionaron los parámetros necesarios para obtener el tipo de compra";
					Utils::writeLog("$message. Parámetros: 'tipoCompraID [$tipoCompraID]', 'tipoCompraNombre [$tipoCompraNombre]'", DATA_LOG_FILE);
					throw new Exception($message);
				}
				$stmt = mysqli_prepare($conn, $queryGet);
				
				// Asignar los parámetros y ejecutar la consulta
				mysqli_stmt_bind_param($stmt, $types, ...$params);
				mysqli_stmt_execute($stmt);
				$result = mysqli_stmt_get_result($stmt);

				if (mysqli_num_rows($result) > 0) {
					$tipoCompra = mysqli_fetch_assoc($result);
					return ["success" => true, "data" => $tipoCompra];
				}
				
				return ["success" => false, "message" => "Tipo de compra no encontrado"];
			} catch (Exception $e) {
				// Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al obtener el tipo de compra de la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
			} finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
		}

		public function getTipoCompras($limit = null, $offset = null) {
			try {
				// Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];

				// Inicializa la consulta para obtener todos los registros
				$queryGet = "SELECT * FROM " . TB_TIPO_COMPRA . " WHERE " . TIPO_COMPRA_ESTADO . " = true";
				if ($limit !== null && $offset !== null) {
					$queryGet .= " LIMIT ? OFFSET ?";
				}
				$stmt = mysqli_prepare($conn, $queryGet);

				// Asigna los parámetros de límite y desplazamiento
				if ($limit !== null && $offset !== null) {
					mysqli_stmt_bind_param($stmt, 'ii', $limit, $offset);
				}

				// Ejecuta la consulta
				mysqli_stmt_execute($stmt);
				$result = mysqli_stmt_get_result($stmt);
				
				$tipoCompras = [];
				while ($row = mysqli_fetch_assoc($result)) {
					$tipoCompras[] = $row;
				}
				
				return ["success" => true, "data" => $tipoCompras];
			} catch (Exception $e) {
				// Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al obtener los tipos de compra de la base de datos'
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
