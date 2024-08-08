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

		// Función para verificar si un impuesto con el mismo nombre ya existe en la bd
		public function impuestoExiste($impuestoID = null, $impuestoNombre = null, $impuestoFecha = null) {
			try {
				// Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];
				
				// Inicializa la consulta base
				$queryCheck = "SELECT * FROM " . TB_IMPUESTO . " WHERE ";
				$params = [];
				$types = "";
				
				if ($impuestoID !== null) {
					// Verificar existencia por ID y que el estado no sea false
					$queryCheck .= IMPUESTO_ID . " = ? AND " . IMPUESTO_ESTADO . " != false";
					$params[] = $impuestoID;
					$types .= 'i';
				} elseif ($impuestoNombre !== null && $impuestoFecha !== null) {
					// Verificar existencia por nombre, luego comprobar fecha o estado
					$queryCheck .= IMPUESTO_NOMBRE . " = ? AND (" . IMPUESTO_FECHA_VIGENCIA . " = ? AND " . IMPUESTO_ESTADO . " != false)";
					$params[] = $impuestoNombre;
					$params[] = $impuestoFecha;
					$types .= 'ss';
				} else {
					throw new Exception("Se requiere al menos un parámetro: impuestoID o impuestoNombre y impuestoFecha");
				}
				
				$stmt = mysqli_prepare($conn, $queryCheck);
				if (!$stmt) {
					throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
				}
				
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
				// Devuelve el mensaje de error
				return ["success" => false, "message" => $e->getMessage()];
			} finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
		}

		public function insertImpuesto($impuesto) {
			try {
				// Obtener los valores de las propiedades del objeto
				$impuestoNombre = $impuesto->getImpuestoNombre();
				$impuestoValor = $impuesto->getImpuestoValor();
				$impuestoDescripcion = $impuesto->getImpuestoDescripcion();
				$impuestoFechaVigencia = $impuesto->getImpuestoFechaVigencia();
		
				// Verifica que las propiedades no estén vacías
				if (empty($impuestoNombre)) {
					throw new Exception("El nombre del impuesto está vacío");
				}
				if (empty($impuestoValor)) {
					throw new Exception("El valor del impuesto está vacío");
				}
				if (empty($impuestoFechaVigencia) || !Utils::validar_fecha($impuestoFechaVigencia)) {
					throw new Exception("La fecha de vigencia del impuesto está vacía o no es válida");
				}

				// Verificar si la fecha de vigencia es menor o igual a la de hoy
				if (!Utils::fechaMenorOIgualAHoy($impuestoFechaVigencia)) {
					throw new Exception("La fecha de vigencia debe ser menor o igual a la fecha actual");
				}
		
				// Verifica si el impuesto ya existe
				$check = $this->impuestoExiste(null, $impuestoNombre, $impuestoFechaVigencia);
				if (!$check["success"]) {
					return $check; // Error al verificar la existencia
				}
				if ($check["exists"]) {
					throw new Exception("Ya existe un impuesto con el mismo nombre o fecha de vigencia");
				}
		
				// Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];
		
				// Obtenemos el último ID de la tabla tbimpuesto
				$queryGetLastId = "SELECT MAX(" . IMPUESTO_ID . ") AS impuestoID FROM " . TB_IMPUESTO;
				$idCont = mysqli_query($conn, $queryGetLastId);
				if (!$idCont) {
					throw new Exception("Error al ejecutar la consulta");
				}
				$nextId = 1;
		
				// Calcula el siguiente ID para la nueva entrada
				if ($row = mysqli_fetch_row($idCont)) {
					$nextId = (int) trim($row[0]) + 1;
				}
		
				// Crea una consulta y un statement SQL para insertar el nuevo registro
				$queryInsert = "INSERT INTO " . TB_IMPUESTO . " ("
                    . IMPUESTO_ID . ", "
                    . IMPUESTO_NOMBRE . ", "
                    . IMPUESTO_VALOR . ", "
                    . IMPUESTO_DESCRIPCION . ", "
                    . IMPUESTO_ESTADO . ", "
                    . IMPUESTO_FECHA_VIGENCIA
                    . ") VALUES (?, ?, ?, ?, true, ?)";
				$stmt = mysqli_prepare($conn, $queryInsert);
				if (!$stmt) {
					throw new Exception("Error al preparar la consulta");
				}
		
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
				if (!$result) {
					throw new Exception("Error al insertar el impuesto");
				}
		
				return ["success" => true, "message" => "Impuesto insertado exitosamente"];
			} catch (Exception $e) {
				// Devuelve el mensaje de error
				return ["success" => false, "message" => $e->getMessage()];
			} finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
		}		

		public function getAllTBImpuesto() {
			try {
				// Establece una conexion con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];

				// Obtenemos la lista de Impuestos
				$querySelect = "SELECT * FROM " . TB_IMPUESTO . " WHERE " . IMPUESTO_ESTADO . " != false ";
				$result = mysqli_query($conn, $querySelect);

				// Verificamos si ocurrió un error
				if (!$result) {
					throw new Exception("Ocurrió un error al obtener la información de la base de datos: " . mysqli_error($conn));
				}

				// Creamos la lista con los datos obtenidos
				$listaImpuestos = [];
				while ($row = mysqli_fetch_array($result)) {
					$currentImpuesto = new Impuesto(
						$row[IMPUESTO_NOMBRE],
						$row[IMPUESTO_VALOR],
						$row[IMPUESTO_FECHA_VIGENCIA],
						$row[IMPUESTO_ID],
						$row[IMPUESTO_DESCRIPCION],
						$row[IMPUESTO_ESTADO]
					);
					array_push($listaImpuestos, $currentImpuesto);
				}

				return ["success" => true, "listaImpuestos" => $listaImpuestos];
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

		public function updateImpuesto($impuesto) {
			try {
				// Obtener los valores de las propiedades del objeto
				$impuestoID = $impuesto->getImpuestoID();
				$impuestoNombre = $impuesto->getImpuestoNombre();
				$impuestoValor = $impuesto->getImpuestoValor();
				$impuestoDescripcion = $impuesto->getImpuestoDescripcion();
				$impuestoFechaVigencia = $impuesto->getImpuestoFechaVigencia();
		
				// Verifica que las propiedades no estén vacías
				if (empty($impuestoID) || !is_numeric($impuestoID)) {
					throw new Exception("No se encontró el ID del Impuesto o este no es válido");
				}
				if (empty($impuestoNombre)) {
					throw new Exception("El nombre del impuesto está vacío");
				}
				if (empty($impuestoValor)) {
					throw new Exception("El valor del impuesto está vacío");
				}
				if (empty($impuestoFechaVigencia) || !Utils::validar_fecha($impuestoFechaVigencia)) {
					throw new Exception("La fecha de vigencia del impuesto está vacía o no es válida");
				}

				// Establece una conexion con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];

				// Crea una consulta y un statement SQL para actualizar el registro
				$queryUpdate = 
					"UPDATE " . TB_IMPUESTO . 
					" SET " . 
						IMPUESTO_NOMBRE . " = ?, " . 
						IMPUESTO_VALOR . " = ?, " .
						IMPUESTO_DESCRIPCION . " = ?, " .
						IMPUESTO_ESTADO . " = true, " .
						IMPUESTO_FECHA_VIGENCIA . " = ? " .
					"WHERE " . IMPUESTO_ID . " = ?";
				$stmt = mysqli_prepare($conn, $queryUpdate);
				if (!$stmt) {
					throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
				}

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
				if (!$result) {
					throw new Exception("Error al actualizar el impuesto: " . mysqli_error($conn));
				}

				// Devuelve el resultado de la consulta
				return ["success" => true, "message" => "Impuesto actualizado exitosamente"];
			} catch (Exception $e) {
				// Devuelve el mensaje de error
				return ["success" => false, "message" => $e->getMessage()];
			} finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
		}

		public function deleteImpuesto($impuestoID) {
			try {
				// Verifica que el ID del impuesto no esté vacío y sea numérico
				if (empty($impuestoID) || !is_numeric($impuestoID)) {
					throw new Exception("ID de impuesto inválido.");
				}
				
				// Verificar si existe el ID y que el Estado no sea false
				$check = $this->impuestoExiste($impuestoID);
				if (!$check["success"]) {
					return $check; // Error al verificar la existencia
				}
				if (!$check["exists"]) {
					throw new Exception("No se encontró un impuesto con el ID [" . $impuestoID . "]");
				}
		
				// Establece una conexion con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];
		
				// Crea una consulta y un statement SQL para eliminar el registro (borrado logico)
				$queryDelete = "UPDATE " . TB_IMPUESTO . " SET " . IMPUESTO_ESTADO . " = false WHERE " . IMPUESTO_ID . " = ?";
				$stmt = mysqli_prepare($conn, $queryDelete);
				if (!$stmt) {
					throw new Exception("Error al preparar la consulta de eliminación: " . mysqli_error($conn));
				}
		
				mysqli_stmt_bind_param($stmt, 'i', $impuestoID);
		
				// Ejecuta la consulta de eliminación
				$result = mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception("Error al eliminar el impuesto: " . mysqli_error($conn));
				}
		
				// Devuelve el resultado de la operación
				return ["success" => true, "message" => "Impuesto eliminado exitosamente."];
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