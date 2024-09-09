<?php
	require_once 'data.php';
    require_once __DIR__ . '/../domain/Subcategoria.php';
	require_once __DIR__ . '/../utils/Variables.php';
	require_once __DIR__ . '/../utils/Utils.php';

	class ProductoSubcategoriaData extends Data {
	
		private $className;

		public function __construct() {
			parent::__construct();
			$this->className = get_class($this);
		}

		public function existeProductoSubcategoria($productoID = null, $subcategoriaID = null, $tbProducto = false, $tbSubcategoria = false) {
			$conn = null; $stmt = null;

			try {
				// Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

				// Determina la tabla y construye la consulta base
				$tableName = $tbProducto ? TB_PRODUCTO : ($tbSubcategoria ? TB_SUBCATEGORIA : TB_PRODUCTO_SUBCATEGORIA);
				$queryCheck = "SELECT 1 FROM $tableName WHERE ";
				$params = [];
				$types = "";

				if ($productoID && $subcategoriaID) {
					// Consulta para verificar si existe una asignación entre el producto y la subcategoría
					$queryCheck .= PRODUCTO_ID . " = ? AND " . SUBCATEGORIA_ID . " = ? AND " . PRODUCTO_SUBCATEGORIA_ESTADO . " != FALSE";
					$params = [$productoID, $subcategoriaID];
					$types = "ii";
				} else if ($productoID) {
					// Consulta para verificar si existe un producto con el ID ingresado
					$estadoCampo = $tbProducto ? PRODUCTO_ESTADO : PRODUCTO_SUBCATEGORIA_ESTADO;
					$queryCheck .= PRODUCTO_ID . " = ? AND $estadoCampo != FALSE";
					$params = [$productoID];
					$types = "i";
				} else if ($subcategoriaID) {
					// Consulta para verificar si existe una subcategoría con el ID ingresado
					$estadoCampo = $tbSubcategoria ? SUBCATEGORIA_ESTADO : PRODUCTO_SUBCATEGORIA_ESTADO;
					$queryCheck .= SUBCATEGORIA_ID . " = ? AND $estadoCampo != FALSE";
					$params = [$subcategoriaID];
					$types = "i";
				} else {
					// Registrar parámetros faltantes y lanzar excepción
                    $missingParamsLog = "Faltan parámetros para verificar la existencia del producto y/o subcategoria:";
                    if (!$productoID) $missingParamsLog .= " productoID [" . ($productoID ?? 'null') . "]";
                    if (!$subcategoriaID) $missingParamsLog .= " subcategoriaID [" . ($subcategoriaID ?? 'null') . "]";
                    Utils::writeLog($missingParamsLog, DATA_LOG_FILE, WARN_MESSAGE, $this->className);
                    return ["success" => false, "message" => "No se proporcionaron los parámetros necesarios para realizar la verificación."];
				}

				// Prepara y ejecuta la consulta
				$stmt = mysqli_prepare($conn, $queryCheck);
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
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al verificar la existencia del producto y/o de la subcategoria en la base de datos',
                    $this->className
                );
        
                return ["success" => false, "message" => $userMessage];
			} finally {
				// Cierra la conexión y el statement
				if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if (isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
			}
		}

		private function verificarExistenciaProductoSubcategoria($productoID, $subcategoriaID) {
			// Verificar que el producto exista en la base de datos
			$checkProductoID = $this->existeProductoSubcategoria($productoID, null, true);
			if (!$checkProductoID["success"]) { return $checkProductoID; }
			if (!$checkProductoID["exists"]) {
				$message = "El producto con 'ID [$productoID]' no existe en la base de datos.";
                Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ['success' => false, 'message' => "El producto seleccionado no existe en la base de datos."];
			}

			// Verificar que la subcategoría exista en la base de datos
			$checkSubcategoriaID = $this->existeProductoSubcategoria(null, $subcategoriaID, false, true);
			if (!$checkSubcategoriaID["success"]) { return $checkSubcategoriaID; }
			if (!$checkSubcategoriaID["exists"]) {
				$message = "La subcategoría con 'ID [$subcategoriaID]' no existe en la base de datos.";
				Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
				return ['success' => false, 'message' => "La subcategoría seleccionada no existe en la base de datos."];
			}

			return ['success' => true];
		}

		public function addSubcategoriaToProducto($productoID, $subcategoriaID, $conn = null) {
			$createdConnection = false; //<- Indica si la conexión se creó aquí o viene por parámetro
            $stmt = null;

			try {
				// Verificar que el producto y la subcategoría existan en la base de datos
				$checkExistencia = $this->verificarExistenciaProductoSubcategoria($productoID, $subcategoriaID);
				if (!$checkExistencia["success"]) { return $checkExistencia; }

				// Si no se proporcionó una conexión, crea una nueva
                if (is_null($conn)) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConnection = true;
                }

				// Obtenemos el último ID de la tabla tbproductosubcategoria
				$queryGetLastId = "SELECT MAX(" . PRODUCTO_SUBCATEGORIA_ID . ") FROM " . TB_PRODUCTO_SUBCATEGORIA;
				$idCont = mysqli_query($conn, $queryGetLastId);
                $nextId = 1;

				// Calcula el siguiente ID para la nueva entrada
                if ($row = mysqli_fetch_row($idCont)) {
                    $nextId = (int) trim($row[0]) + 1;
                }

				// Crea una consulta y un statement SQL para insertar el nuevo registro
				$queryInsert = "INSERT INTO " . TB_PRODUCTO_SUBCATEGORIA . " ("
                    . PRODUCTO_SUBCATEGORIA_ID . ", "
                    . PRODUCTO_ID . ", "
                    . SUBCATEGORIA_ID
                    . ") VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($conn, $queryInsert);

				// Vincula los parámetros y ejecuta la consulta
                mysqli_stmt_bind_param($stmt, "iii", $nextId, $productoID, $subcategoriaID);
                mysqli_stmt_execute($stmt);

				return ["success" => true, "message" => "Subcategoría asignada exitosamente al producto."];
			} catch (Exception $e) {
				$userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al intentar asignarle la subcategoria al producto en la base de datos',
                    $this->className
                );

				return ["success" => false, "message" => $userMessage];
			} finally {
                // Cierra el statement y la conexión solo si fueron creados en esta función
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if ($createdConnection && isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
            }
		}

		public function removeSubcategoriaFromProducto($productoID, $subcategoriaID, $conn = null) {
			$createdConnection = false; //<- Indica si la conexión se creó aquí o viene por parámetro
			$stmt = null;

			try {
				// Verificar que el producto y la subcategoría existan en la base de datos
				$checkExistencia = $this->verificarExistenciaProductoSubcategoria($productoID, $subcategoriaID);
				if (!$checkExistencia["success"]) { return $checkExistencia; }

				// Verificar si existe la asignación entre el producto y la subcategoría en la base de datos
				$checkAsignacion = $this->existeProductoSubcategoria($productoID, $subcategoriaID);
				if (!$checkAsignacion["success"]) { return $checkAsignacion; }
				if (!$checkAsignacion["exists"]) {
					$message = "La subcategoria con 'ID [$subcategoriaID]' no está asignada al producto con 'ID [$productoID]'.";
					Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
					return ['success' => false, 'message' => "La subcategoría seleccionada no está asignada al producto."];
				}

				// Si no se proporcionó una conexión, crea una nueva
				if (is_null($conn)) {
					$result = $this->getConnection();
					if (!$result["success"]) { throw new Exception($result["message"]); }
					$conn = $result["connection"];
					$createdConnection = true;

					// Desactivar el autocommit para manejar transacciones si la conexión fue creada aquí
                    mysqli_autocommit($conn, false);
				}

				// Crea una consulta y un statement SQL para eliminar el registro
				$queryUpdate = 
					"UPDATE " . TB_PRODUCTO_SUBCATEGORIA . 
					" SET " 
						. PRODUCTO_SUBCATEGORIA_ESTADO . " = FALSE" . 
					" WHERE "
						. PRODUCTO_ID . " = ? AND " 
						. SUBCATEGORIA_ID . " = ?";
				$stmt = mysqli_prepare($conn, $queryUpdate);

				// Vincula los parámetros y ejecuta la consulta
				mysqli_stmt_bind_param($stmt, "ii", $productoID, $subcategoriaID);
				mysqli_stmt_execute($stmt);

				// Eliminar subcategoria de la tabla tbsubcategoria
				$queryUpdateSubcategoria = 
					"UPDATE " . TB_SUBCATEGORIA . 
					" SET " 
						. SUBCATEGORIA_ESTADO . " = FALSE" . 
					" WHERE "
						. SUBCATEGORIA_ID . " = ?";
				$stmt = mysqli_prepare($conn, $queryUpdateSubcategoria);

				// Vincula los parámetros y ejecuta la consulta
				mysqli_stmt_bind_param($stmt, "i", $subcategoriaID);
				mysqli_stmt_execute($stmt);

				// Confirmar la transacción si la conexión fue creada aquí
                if ($createdConnection) {
                    mysqli_commit($conn);
                }

				return ["success" => true, "message" => "Subcategoría eliminada correctamente."];
			} catch (Exception $e) {
				// Revertir la transacción en caso de error si la conexión fue creada aquí
                if ($createdConnection && isset($conn)) { mysqli_rollback($conn); }
        
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al intentar eliminar la subcategoria del producto en la base de datos',
                    $this->className
                );
        
                return ["success" => false, "message" => $userMessage];
			} finally {
				// Cierra el statement y la conexión solo si fueron creados en esta función
				if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
				if ($createdConnection && isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
			}
		}

		public function getSubcategoriasByProducto($productoID, $json = false) {
			$conn = null; $stmt = null;

			try {
				// Verificar que el producto tenga subcategorias registradas
				$checkProductoID = $this->existeProductoSubcategoria($productoID, null, true);
				if (!$checkProductoID["success"]) { throw new Exception($checkProductoID["message"]); }
				if (!$checkProductoID["exists"]) {
					$message = "El producto con 'ID [$productoID]' no tiene subcategorías registradas.";
					Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
					return ['success' => false, 'message' => "El producto seleccionado no tiene subcategorías registradas."];
				}

				// Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) { throw new Exception($result["message"]); }
				$conn = $result["connection"];

				// Consulta para obtener las subcategorias asignadas al producto
				$querySelect = "
					SELECT
						S.*
					FROM " 
						. TB_SUBCATEGORIA . " S
					INNER JOIN " . 
						TB_PRODUCTO_SUBCATEGORIA . " PS
						ON S." . SUBCATEGORIA_ID . " = PS." . SUBCATEGORIA_ID . "
					WHERE
						PS." . PRODUCTO_ID . " = ? AND
						PS." . PRODUCTO_SUBCATEGORIA_ESTADO . " != FALSE AND
						S."  . SUBCATEGORIA_ESTADO . " != FALSE
				";

				// Preparar la consulta, vincular los parámetros y ejecutar la consulta
				$stmt = mysqli_prepare($conn, $querySelect);
				mysqli_stmt_bind_param($stmt, "i", $productoID);
				mysqli_stmt_execute($stmt);
				$result = mysqli_stmt_get_result($stmt);

				// Obtener los resultados de la consulta
				$subcategorias = [];
				while ($row = mysqli_fetch_assoc($result)) {
					if ($json) {
						$subcategorias[] = [
							"ID" => $row[SUBCATEGORIA_ID],
							"Nombre" => $row[SUBCATEGORIA_NOMBRE],
							"Descripcion" => $row[SUBCATEGORIA_DESCRIPCION],
							"Estado" => $row[SUBCATEGORIA_ESTADO]
						];
					} else {
						$subcategorias[] = new Subcategoria(
							$row[SUBCATEGORIA_NOMBRE],
							$row[SUBCATEGORIA_DESCRIPCION],
							$row[SUBCATEGORIA_ID],
							$row[SUBCATEGORIA_ESTADO]
						);
					}
				}

				return ["success" => true, "subcategorias" => $subcategorias];
			} catch (Exception $e) {
				// Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al obtener la lista de subcategorias del producto desde la base de datos',
                    $this->className
                );

                return ["success" => false, "message" => $userMessage];
			} finally {
                // Cierra la conexión y el statement
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if (isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
            }
		}

		public function getPaginatedSubcategoriasByProducto($productoID, $page, $size, $sort = null, $onlyActiveOrInactive = true, $deleted = false) {
			$conn = null; $stmt = null;

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
				$queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_PRODUCTO_SUBCATEGORIA . " WHERE " . PRODUCTO_ID . " = ? ";
				if ($onlyActiveOrInactive) { $queryTotalCount .= "AND " . PRODUCTO_SUBCATEGORIA_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE") . " "; }

				// Preparar y ejecutar la consulta para obtener el total de registros
				$stmt = mysqli_prepare($conn, $queryTotalCount);
				mysqli_stmt_bind_param($stmt, "i", $productoID);
				mysqli_stmt_execute($stmt);
				$result = mysqli_stmt_get_result($stmt);
				$totalRecords = (int) mysqli_fetch_assoc($result)["total"];
				$totalPages = ceil($totalRecords / $size);

				// Construir la consulta SQL para paginación
				$querySelect = "
                    SELECT
                        S.*
                    FROM " . 
                        TB_SUBCATEGORIA . " S
                    INNER JOIN " . 
                        TB_PRODUCTO_SUBCATEGORIA . " PS 
                        ON S." . SUBCATEGORIA_ID . " = PS." . SUBCATEGORIA_ID . "
                    WHERE 
                        PS." . PRODUCTO_ID . " = ? ";
				if ($onlyActiveOrInactive) { $querySelect .= "AND " . PRODUCTO_SUBCATEGORIA_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE") . " "; }

				// Añadir la cláusula de ordenamiento si se proporciona
                if ($sort) { $querySelect .= "ORDER BY subcategoria" . $sort . " "; }

				// Añadir la cláusula de limitación y offset
                $querySelect .= "LIMIT ? OFFSET ?";

                // Preparar la consulta, vincular los parámetros y ejecutar la consulta
				$stmt = mysqli_prepare($conn, $querySelect);
				mysqli_stmt_bind_param($stmt, "iii", $productoID, $size, $offset);
				mysqli_stmt_execute($stmt);
				$result = mysqli_stmt_get_result($stmt);

				// Obtener los resultados de la consulta
				$subcategorias = [];
				$subcategorias = [];
				while ($row = mysqli_fetch_assoc($result)) {
					$subcategorias[] = [
						"ID" => $row[SUBCATEGORIA_ID],
						"Nombre" => $row[SUBCATEGORIA_NOMBRE],
						"Descripcion" => $row[SUBCATEGORIA_DESCRIPCION],
						"Estado" => $row[SUBCATEGORIA_ESTADO]
					];
				}

				return [
                    "success" => true,
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "producto" => $productoID,
                    "subcategorias" => $subcategorias
                ];
			} catch (Exception $e) {
				// Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al obtener la lista de subcategorias del producto desde la base de datos',
                    $this->className
                );
        
                return ["success" => false, "message" => $userMessage];
			} finally {
				// Cierra la conexión y el statement
				if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
				if (isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
			}
		}

	}
	
?>