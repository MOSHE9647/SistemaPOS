<?php

	include_once 'data.php';
	include __DIR__ . '/../domain/Lote.php';
	require_once __DIR__ . '/../utils/Utils.php';
	require_once __DIR__ . '/../utils/Variables.php';

	class LoteData extends Data {

		// Constructor
		public function __construct() {
			parent::__construct();
		}

		// Función para verificar si un lote con el mismo código ya existe en la bd
		public function loteExiste($loteID = null, $loteCodigo = null) {
			try {
				// Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];
				
				// Inicializa la consulta base
				$queryCheck = "SELECT * FROM " . TB_LOTE . " WHERE ";
				$params = [];
				$types = "";
				
				if ($loteID !== null) {
					// Verificar existencia por ID y que el estado no sea false
					$queryCheck .= LOTE_ID . " = ? AND " . LOTE_ESTADO . " != false";
					$params[] = $loteID;
					$types .= 'i';
				} elseif ($loteCodigo !== null) {
					// Verificar existencia por código
					$queryCheck .= LOTE_CODIGO . " = ? AND " . LOTE_ESTADO . " != false";
					$params[] = $loteCodigo;
					$types .= 's';
				} else {
					throw new Exception("Se requiere al menos un parámetro: loteID o loteCodigo");
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

		public function insertLote($lote) {
			try {
				// Obtener los valores de las propiedades del objeto
				$loteCodigo = $lote->getLoteCodigo();
				$productoID = $lote->getProductoID();
				$loteCantidad = $lote->getLoteCantidad();
				$lotePrecio = $lote->getLotePrecio();
				$proveedorID = $lote->getProveedorID();
				$loteFechaIngreso = $lote->getLoteFechaIngreso();
				$loteFechaVencimiento = $lote->getLoteFechaVencimiento();
		
				// Verifica que las propiedades no estén vacías
				if (empty($loteCodigo)) {
					throw new Exception("El código del lote está vacío");
				}
				if (empty($productoID) || !is_numeric($productoID)) {
					throw new Exception("El ID del producto está vacío o no es válido");
				}
				if (empty($loteCantidad) || !is_numeric($loteCantidad)) {
					throw new Exception("La cantidad del lote está vacía o no es válida");
				}
				if (empty($lotePrecio)) {
					throw new Exception("El precio del lote está vacío");
				}
				if (empty($proveedorID) || !is_numeric($proveedorID)) {
					throw new Exception("El ID del proveedor está vacío o no es válido");
				}
				if (empty($loteFechaIngreso) || !Utils::validar_fecha($loteFechaIngreso)) {
					throw new Exception("La fecha de ingreso del lote está vacía o no es válida");
				}
				if (empty($loteFechaVencimiento) || !Utils::validar_fecha($loteFechaVencimiento)) {
					throw new Exception("La fecha de vencimiento del lote está vacía o no es válida");
				}

				// Verificar si el lote ya existe
				$check = $this->loteExiste(null, $loteCodigo);
				if (!$check["success"]) {
					return $check; // Error al verificar la existencia
				}
				if ($check["exists"]) {
					throw new Exception("Ya existe un lote con el mismo código");
				}
		
				// Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];
		
				// Obtenemos el último ID de la tabla tb_lote
				$queryGetLastId = "SELECT MAX(" . LOTE_ID . ") AS loteID FROM " . TB_LOTE;
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
				$queryInsert = "INSERT INTO " . TB_LOTE . " ("
                    . LOTE_ID . ", "
                    . LOTE_CODIGO . ", "
                    . PRODUCTO_ID . ", "
                    . LOTE_CANTIDAD . ", "
                    . LOTE_PRECIO . ", "
                    . PROVEEDOR_ID . ", "
                    . LOTE_FECHA_INGRESO . ", "
                    . LOTE_FECHA_VENCIMIENTO . ", "
                    . LOTE_ESTADO
                    . ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, true)";
				$stmt = mysqli_prepare($conn, $queryInsert);
				if (!$stmt) {
					throw new Exception("Error al preparar la consulta");
				}
		
				mysqli_stmt_bind_param(
					$stmt,
					'iiiiisss', // i: Entero, s: Cadena
					$nextId,
					$loteCodigo,
					$productoID,
					$loteCantidad,
					$lotePrecio,
					$proveedorID,
					$loteFechaIngreso,
					$loteFechaVencimiento
				);
		
				// Ejecuta la consulta de inserción
				$result = mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception("Error al insertar el lote");
				}
		
				return ["success" => true, "message" => "Lote insertado exitosamente"];
			} catch (Exception $e) {
				// Devuelve el mensaje de error
				return ["success" => false, "message" => $e->getMessage()];
			} finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
		}		

		public function getAllLotes() {
			try {
				// Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];

				// Obtenemos la lista de Lotes
				$querySelect = "SELECT * FROM " . TB_LOTE . " WHERE " . LOTE_ESTADO . " != false";
				$result = mysqli_query($conn, $querySelect);

				// Verificamos si ocurrió un error
				if (!$result) {
					throw new Exception("Ocurrió un error al obtener la información de la base de datos: " . mysqli_error($conn));
				}

				// Creamos la lista con los datos obtenidos
				$listaLotes = [];
				while ($row = mysqli_fetch_array($result)) {
					$currentLote = new Lote(
						$row[LOTE_CODIGO],
						$row[PRODUCTO_ID],
						$row[LOTE_CANTIDAD],
						$row[LOTE_PRECIO],
						$row[PROVEEDOR_ID],
						$row[LOTE_FECHA_INGRESO],
						$row[LOTE_FECHA_VENCIMIENTO],
						$row[LOTE_ID],
						$row[LOTE_ESTADO]
					);
					array_push($listaLotes, $currentLote);
				}
				return ["success" => true, "listaLotes" => $listaLotes];
			} catch (Exception $e) {
				// Devuelve el mensaje de error
				return ["success" => false, "message" => $e->getMessage()];
			} finally {
				// Cierra la conexión y el resultado
				if (isset($result)) { mysqli_free_result($result); }
				if (isset($conn)) { mysqli_close($conn); }
			}
		}
        public function getPaginatedLotes($page, $size, $sort = null) {
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                // Calcula el offset para la paginación
                $offset = ($page - 1) * $size;
        
                // Inicializa la consulta base
                $querySelect = "SELECT * FROM " . TB_LOTE . " WHERE " . LOTE_ESTADO . " != false";
                
                // Añade ordenamiento si se proporciona
                if ($sort) {
                    $querySelect .= " ORDER BY " . $sort;
                }
        
                // Añade limitación y offset para la paginación
                $querySelect .= " LIMIT ? OFFSET ?";
        
                $stmt = mysqli_prepare($conn, $querySelect);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }
        
                // Asignar parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, 'ii', $size, $offset);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
        
                // Verificamos si ocurrió un error
                if (!$result) {
                    throw new Exception("Ocurrió un error al obtener los lotes paginados");
                }
        
                // Creamos la lista con los datos obtenidos
                $listaLotes = [];
                while ($row = mysqli_fetch_array($result)) {
                    $currentLote = new Lote(
                        $row[LOTE_CODIGO],
                        $row[PRODUCTO_ID],
                        $row[LOTE_CANTIDAD],
                        $row[LOTE_PRECIO],
                        $row[PROVEEDOR_ID],
                        $row[LOTE_FECHA_INGRESO],
                        $row[LOTE_FECHA_VENCIMIENTO],
                        $row[LOTE_ID],
                        $row[LOTE_ESTADO]
                    );
                    array_push($listaLotes, $currentLote);
                }
        
                return ["success" => true, "listaLotes" => $listaLotes];
            } catch (Exception $e) {
                // Devuelve el mensaje de error
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }
        

		public function getLoteById($loteID) {
			try {
				// Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];

				// Preparamos la consulta para obtener un lote específico por ID
				$querySelect = "SELECT * FROM " . TB_LOTE . " WHERE " . LOTE_ID . " = ? AND " . LOTE_ESTADO . " != false";
				$stmt = mysqli_prepare($conn, $querySelect);
				if (!$stmt) {
					throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
				}
				mysqli_stmt_bind_param($stmt, 'i', $loteID);
				mysqli_stmt_execute($stmt);
				$result = mysqli_stmt_get_result($stmt);

				// Verificamos si ocurrió un error
				if (!$result) {
					throw new Exception("Ocurrió un error al obtener la información del lote");
				}

				// Obtenemos el lote de los resultados
				if ($row = mysqli_fetch_array($result)) {
					return [
						"success" => true,
						"lote" => new Lote(
							$row[LOTE_CODIGO],
							$row[PRODUCTO_ID],
							$row[LOTE_CANTIDAD],
							$row[LOTE_PRECIO],
							$row[PROVEEDOR_ID],
							$row[LOTE_FECHA_INGRESO],
							$row[LOTE_FECHA_VENCIMIENTO],
							$row[LOTE_ID],
							$row[LOTE_ESTADO]
						)
					];
				} else {
					return ["success" => false, "message" => "No se encontró el lote"];
				}
			} catch (Exception $e) {
				// Devuelve el mensaje de error
				return ["success" => false, "message" => $e->getMessage()];
			} finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
		}

		public function updateLote($lote) {
			try {
				// Obtener los valores de las propiedades del objeto
				$loteID = $lote->getLoteID();
				$loteCodigo = $lote->getLoteCodigo();
				$productoID = $lote->getProductoID();
				$loteCantidad = $lote->getLoteCantidad();
				$lotePrecio = $lote->getLotePrecio();
				$proveedorID = $lote->getProveedorID();
				$loteFechaIngreso = $lote->getLoteFechaIngreso();
				$loteFechaVencimiento = $lote->getLoteFechaVencimiento();

				// Verifica que las propiedades no estén vacías
				if (empty($loteID)) {
					throw new Exception("El ID del lote está vacío");
				}
				if (empty($loteCodigo)) {
					throw new Exception("El código del lote está vacío");
				}
				if (empty($productoID) || !is_numeric($productoID)) {
					throw new Exception("El ID del producto está vacío o no es válido");
				}
				if (empty($loteCantidad) || !is_numeric($loteCantidad)) {
					throw new Exception("La cantidad del lote está vacía o no es válida");
				}
				if (empty($lotePrecio)) {
					throw new Exception("El precio del lote está vacío");
				}
				if (empty($proveedorID) || !is_numeric($proveedorID)) {
					throw new Exception("El ID del proveedor está vacío o no es válido");
				}
				if (empty($loteFechaIngreso) || !Utils::validar_fecha($loteFechaIngreso)) {
					throw new Exception("La fecha de ingreso del lote está vacía o no es válida");
				}
				if (empty($loteFechaVencimiento) || !Utils::validar_fecha($loteFechaVencimiento)) {
					throw new Exception("La fecha de vencimiento del lote está vacía o no es válida");
				}

				// Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];

				// Preparamos la consulta para actualizar el lote
				$queryUpdate = "UPDATE " . TB_LOTE . " SET "
					. LOTE_CODIGO . " = ?, "
					. PRODUCTO_ID . " = ?, "
					. LOTE_CANTIDAD . " = ?, "
					. LOTE_PRECIO . " = ?, "
					. PROVEEDOR_ID . " = ?, "
					. LOTE_FECHA_INGRESO . " = ?, "
					. LOTE_FECHA_VENCIMIENTO . " = ? "
					. "WHERE " . LOTE_ID . " = ? AND " . LOTE_ESTADO . " != false";
				$stmt = mysqli_prepare($conn, $queryUpdate);
				if (!$stmt) {
					throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
				}

				mysqli_stmt_bind_param(
					$stmt,
					'iissssis', // i: Entero, s: Cadena
					$loteCodigo,
					$productoID,
					$loteCantidad,
					$lotePrecio,
					$proveedorID,
					$loteFechaIngreso,
					$loteFechaVencimiento,
					$loteID
				);

				// Ejecuta la consulta de actualización
				$result = mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception("Error al actualizar el lote");
				}

				return ["success" => true, "message" => "Lote actualizado exitosamente"];
			} catch (Exception $e) {
				// Devuelve el mensaje de error
				return ["success" => false, "message" => $e->getMessage()];
			} finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
		}

		public function deleteLote($loteID) {
			try {
				// Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];

				// Preparamos la consulta para eliminar el lote
				$queryDelete = "UPDATE " . TB_LOTE . " SET " . LOTE_ESTADO . " = false WHERE " . LOTE_ID . " = ?";
				$stmt = mysqli_prepare($conn, $queryDelete);
				if (!$stmt) {
					throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
				}
				mysqli_stmt_bind_param($stmt, 'i', $loteID);
				mysqli_stmt_execute($stmt);

				// Verificamos si ocurrió un error
				if (!$stmt) {
					throw new Exception("Error al eliminar el lote");
				}

				return ["success" => true, "message" => "Lote eliminado exitosamente"];
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
