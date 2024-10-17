<?php
	require_once dirname(__DIR__, 1) . '/data/data.php';
    require_once __DIR__ . '/../domain/Categoria.php';
	require_once __DIR__ . '/../utils/Variables.php';
	require_once __DIR__ . '/../utils/Utils.php';

	class ProductoCategoriaData extends Data {
	
		private $className;

		public function __construct() {
			parent::__construct();
			$this->className = get_class($this);
		}

		public function existeProductoCategoria($productoID = null, $categoriaID = null, $tbProducto = false, $tbCategoria = false) {
			$conn = null; $stmt = null;

			try {
				// Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

				// Determina la tabla y construye la consulta base
				$tableName = $tbProducto ? TB_PRODUCTO : ($tbCategoria ? TB_CATEGORIA : TB_PRODUCTO_CATEGORIA);
				$queryCheck = "SELECT 1 FROM $tableName WHERE ";
				$params = [];
				$types = "";

				if ($productoID && $categoriaID) {
					// Consulta para verificar si existe una asignación entre el producto y la categoría
					$queryCheck .= PRODUCTO_ID . " = ? AND " . CATEGORIA_ID . " = ? AND " . PRODUCTO_CATEGORIA_ESTADO . " != FALSE";
					$params = [$productoID, $categoriaID];
					$types = "ii";
				} else if ($productoID) {
					// Consulta para verificar si existe un producto con el ID ingresado
					$estadoCampo = $tbProducto ? PRODUCTO_ESTADO : PRODUCTO_CATEGORIA_ESTADO;
					$queryCheck .= PRODUCTO_ID . " = ? AND $estadoCampo != FALSE";
					$params = [$productoID];
					$types = "i";
				} else if ($categoriaID) {
					// Consulta para verificar si existe una categoría con el ID ingresado
					$estadoCampo = $tbCategoria ? CATEGORIA_ESTADO : PRODUCTO_CATEGORIA_ESTADO;
					$queryCheck .= CATEGORIA_ID . " = ? AND $estadoCampo != FALSE";
					$params = [$categoriaID];
					$types = "i";
				} else {
					// Registrar parámetros faltantes y lanzar excepción
                    $missingParamsLog = "Faltan parámetros para verificar la existencia del producto y/o categoria:";
                    if (!$productoID) $missingParamsLog .= " productoID [" . ($productoID ?? 'null') . "]";
                    if (!$categoriaID) $missingParamsLog .= " categoriaID [" . ($categoriaID ?? 'null') . "]";
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
                    'Ocurrió un error al verificar la existencia del producto y/o de la categoria en la base de datos',
                    $this->className
                );
        
                return ["success" => false, "message" => $userMessage];
			} finally {
				// Cierra la conexión y el statement
				if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if (isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
			}
		}

		private function verificarExistenciaProductoCategoria($productoID, $categoriaID) {
			// Verificar que el producto exista en la base de datos
			$checkProductoID = $this->existeProductoCategoria($productoID, null, true);
			if (!$checkProductoID["success"]) { return $checkProductoID; }
			if (!$checkProductoID["exists"]) {
				$message = "El producto con 'ID [$productoID]' no existe en la base de datos.";
                Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ['success' => false, 'message' => "El producto seleccionado no existe en la base de datos."];
			}

			// Verificar que la categoría exista en la base de datos
			$checkCategoriaID = $this->existeProductoCategoria(null, $categoriaID, false, true);
			if (!$checkCategoriaID["success"]) { return $checkCategoriaID; }
			if (!$checkCategoriaID["exists"]) {
				$message = "La categoría con 'ID [$categoriaID]' no existe en la base de datos.";
				Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
				return ['success' => false, 'message' => "La categoría seleccionada no existe en la base de datos."];
			}

			return ['success' => true];
		}

		public function addCategoriaToProducto($productoID, $categoriaID, $conn = null) {
			$createdConnection = false; //<- Indica si la conexión se creó aquí o viene por parámetro
            $stmt = null;

			try {
				// Verificar que el producto y la categoría existan en la base de datos
				$checkExistencia = $this->verificarExistenciaProductoCategoria($productoID, $categoriaID);
				if (!$checkExistencia["success"]) { return $checkExistencia; }

				// Si no se proporcionó una conexión, crea una nueva
                if (is_null($conn)) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConnection = true;
                }

				// Obtenemos el último ID de la tabla tbproductocategoria
				$queryGetLastId = "SELECT MAX(" . PRODUCTO_CATEGORIA_ID . ") FROM " . TB_PRODUCTO_CATEGORIA;
				$idCont = mysqli_query($conn, $queryGetLastId);
                $nextId = 1;

				// Calcula el siguiente ID para la nueva entrada
                if ($row = mysqli_fetch_row($idCont)) {
                    $nextId = (int) trim($row[0]) + 1;
                }

				// Crea una consulta y un statement SQL para insertar el nuevo registro
				$queryInsert = "INSERT INTO " . TB_PRODUCTO_CATEGORIA . " ("
                    . PRODUCTO_CATEGORIA_ID . ", "
                    . PRODUCTO_ID . ", "
                    . CATEGORIA_ID
                    . ") VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($conn, $queryInsert);

				// Vincula los parámetros y ejecuta la consulta
                mysqli_stmt_bind_param($stmt, "iii", $nextId, $productoID, $categoriaID);
                mysqli_stmt_execute($stmt);

				return ["success" => true, "message" => "Categoría asignada exitosamente al producto."];
			} catch (Exception $e) {
				$userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al intentar asignarle la categoria al producto en la base de datos',
                    $this->className
                );

				return ["success" => false, "message" => $userMessage];
			} finally {
                // Cierra el statement y la conexión solo si fueron creados en esta función
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if ($createdConnection && isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
            }
		}

		public function removeCategoriaFromProducto($productoID, $categoriaID, $conn = null) {
			$createdConnection = false; //<- Indica si la conexión se creó aquí o viene por parámetro
			$stmt = null;

			try {
				// Verificar que el producto y la categoría existan en la base de datos
				$checkExistencia = $this->verificarExistenciaProductoCategoria($productoID, $categoriaID);
				if (!$checkExistencia["success"]) { return $checkExistencia; }

				// Verificar si existe la asignación entre el producto y la categoría en la base de datos
				$checkAsignacion = $this->existeProductoCategoria($productoID, $categoriaID);
				if (!$checkAsignacion["success"]) { return $checkAsignacion; }
				if (!$checkAsignacion["exists"]) {
					$message = "La categoria con 'ID [$categoriaID]' no está asignada al producto con 'ID [$productoID]'.";
					Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
					return ['success' => false, 'message' => "La categoría seleccionada no está asignada al producto."];
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
					"UPDATE " . TB_PRODUCTO_CATEGORIA . 
					" SET " 
						. PRODUCTO_CATEGORIA_ESTADO . " = FALSE" . 
					" WHERE "
						. PRODUCTO_ID . " = ? AND " 
						. CATEGORIA_ID . " = ?";
				$stmt = mysqli_prepare($conn, $queryUpdate);

				// Vincula los parámetros y ejecuta la consulta
				mysqli_stmt_bind_param($stmt, "ii", $productoID, $categoriaID);
				mysqli_stmt_execute($stmt);

				// Eliminar categoria de la tabla tbcategoria
				$queryUpdateCategoria = 
					"UPDATE " . TB_CATEGORIA . 
					" SET " 
						. CATEGORIA_ESTADO . " = FALSE" . 
					" WHERE "
						. CATEGORIA_ID . " = ?";
				$stmt = mysqli_prepare($conn, $queryUpdateCategoria);

				// Vincula los parámetros y ejecuta la consulta
				mysqli_stmt_bind_param($stmt, "i", $categoriaID);
				mysqli_stmt_execute($stmt);

				// Confirmar la transacción si la conexión fue creada aquí
                if ($createdConnection) {
                    mysqli_commit($conn);
                }

				return ["success" => true, "message" => "Categoría eliminada correctamente."];
			} catch (Exception $e) {
				// Revertir la transacción en caso de error si la conexión fue creada aquí
                if ($createdConnection && isset($conn)) { mysqli_rollback($conn); }
        
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al intentar eliminar la categoria del producto en la base de datos',
                    $this->className
                );
        
                return ["success" => false, "message" => $userMessage];
			} finally {
				// Cierra el statement y la conexión solo si fueron creados en esta función
				if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
				if ($createdConnection && isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
			}
		}


		public function getAllTBProductoCategoria() {
			$response = [];
			try {
				// Establece una conexion con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];
	
				// Construir la consulta SQL con joins para obtener nombres en lugar de IDs
			$querySelect = "SELECT " . CATEGORIA_ID . ", " . CATEGORIA_NOMBRE . " FROM " . TB_CATEGORIA . " WHERE " . CATEGORIA_ESTADO . " !=false";
			$result = mysqli_query($conn, $querySelect);
	
			   // Crear la lista con los datos obtenidos
			$listaProductoCategorias = [];
			while ($row = mysqli_fetch_assoc($result)) {
				$listaProductoCategorias []= [
					"ID" => $row[CATEGORIA_ID],
					"CategoriaNombre" =>  $row[CATEGORIA_NOMBRE],
				];
			}
	
				return ["success" => true, "listaProductoCategorias" => $listaProductoCategorias];
			} catch (Exception $e) {
				// Manejo del error dentro del bloque catch
				$userMessage = $this->handleMysqlError(
					$e->getCode(), 
					$e->getMessage(),
					'Error al obtener la lista de categorias desde la base de datos'
				);
				// Devolver mensaje amigable para el usuario
				$response = ["success" => false, "message" => $userMessage];
			} finally {
				// Cerramos la conexion
				if (isset($conn)) { mysqli_close($conn); }
			}
			return $response;
		}


		public function getCategoriasByProducto($productoID, $json = false) {
			$conn = null; $stmt = null;

			try {
				// Verificar que el producto tenga categorias registradas
				$checkProductoID = $this->existeProductoCategoria($productoID, null, true);
				if (!$checkProductoID["success"]) { throw new Exception($checkProductoID["message"]); }
				if (!$checkProductoID["exists"]) {
					$message = "El producto con 'ID [$productoID]' no tiene categorías registradas.";
					Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
					return ['success' => false, 'message' => "El producto seleccionado no tiene categorías registradas."];
				}

				// Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) { throw new Exception($result["message"]); }
				$conn = $result["connection"];

				// Consulta para obtener las categorias asignadas al producto
				$querySelect = "
					SELECT
						C.*
					FROM " 
						. TB_CATEGORIA . " C
					INNER JOIN " . 
						TB_PRODUCTO_CATEGORIA . " PC
						ON C." . CATEGORIA_ID . " = PC." . CATEGORIA_ID . "
					WHERE
						PC." . PRODUCTO_ID . " = ? AND
						PC." . PRODUCTO_CATEGORIA_ESTADO . " != FALSE AND
						C."  . CATEGORIA_ESTADO . " != FALSE
				";

				// Preparar la consulta, vincular los parámetros y ejecutar la consulta
				$stmt = mysqli_prepare($conn, $querySelect);
				mysqli_stmt_bind_param($stmt, "i", $productoID);
				mysqli_stmt_execute($stmt);
				$result = mysqli_stmt_get_result($stmt);

				// Obtener los resultados de la consulta
				$categorias = [];
				while ($row = mysqli_fetch_assoc($result)) {
					if ($json) {
						$categorias[] = [
							"ID" => $row[CATEGORIA_ID],
							"Nombre" => $row[CATEGORIA_NOMBRE],
							"Descripcion" => $row[CATEGORIA_DESCRIPCION],
							"Estado" => $row[CATEGORIA_ESTADO]
						];
					} else {
						$categorias[] = new Categoria(
							$row[CATEGORIA_NOMBRE],
							$row[CATEGORIA_DESCRIPCION],
							$row[CATEGORIA_ID],
							$row[CATEGORIA_ESTADO]
						);
					}
				}

				return ["success" => true, "categorias" => $categorias];
			} catch (Exception $e) {
				// Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al obtener la lista de categorias del producto desde la base de datos',
                    $this->className
                );

                return ["success" => false, "message" => $userMessage];
			} finally {
                // Cierra la conexión y el statement
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if (isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
            }
		}

		public function getPaginatedCategoriasByProducto($productoID, $page, $size, $sort = null, $onlyActive = true, $deleted = false) {
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
				$queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_PRODUCTO_CATEGORIA . " WHERE " . PRODUCTO_ID . " = ? ";
				if ($onlyActive) { $queryTotalCount .= "AND " . PRODUCTO_CATEGORIA_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE") . " "; }

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
                        C.*
                    FROM " . 
                        TB_CATEGORIA . " C
                    INNER JOIN " . 
                        TB_PRODUCTO_CATEGORIA . " PC 
                        ON C." . CATEGORIA_ID . " = PC." . CATEGORIA_ID . "
                    WHERE 
                        PC." . PRODUCTO_ID . " = ? ";
				if ($onlyActive) { $querySelect .= "AND " . PRODUCTO_CATEGORIA_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE") . " "; }

				// Añadir la cláusula de ordenamiento si se proporciona
                if ($sort) { $querySelect .= "ORDER BY categoria" . $sort . " "; }

				// Añadir la cláusula de limitación y offset
                $querySelect .= "LIMIT ? OFFSET ?";

                // Preparar la consulta, vincular los parámetros y ejecutar la consulta
				$stmt = mysqli_prepare($conn, $querySelect);
				mysqli_stmt_bind_param($stmt, "iii", $productoID, $size, $offset);
				mysqli_stmt_execute($stmt);
				$result = mysqli_stmt_get_result($stmt);

				// Obtener los resultados de la consulta
				$categorias = [];
				$categorias = [];
				while ($row = mysqli_fetch_assoc($result)) {
					$categorias[] = [
						"ID" => $row[CATEGORIA_ID],
						"Nombre" => $row[CATEGORIA_NOMBRE],
						"Descripcion" => $row[CATEGORIA_DESCRIPCION],
						"Estado" => $row[CATEGORIA_ESTADO]
					];
				}

				return [
                    "success" => true,
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "producto" => $productoID,
                    "categorias" => $categorias
                ];
			} catch (Exception $e) {
				// Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al obtener la lista de categorias del producto desde la base de datos',
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