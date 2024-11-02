<?php

	require_once dirname(__DIR__, 1) . '/data/data.php';
	require_once dirname(__DIR__, 1) . '/domain/Categoria.php';
	require_once dirname(__DIR__, 1) . '/utils/Utils.php';
	require_once dirname(__DIR__, 1) . '/utils/Variables.php';

	class CategoriaData extends Data {

		private $className;

		// Constructor
		public function __construct() {
			$this->className = get_class($this);
			parent::__construct();
		}

		// Función para verificar si una categoría con el mismo nombre ya existe en la base de datos
		public function categoriaExiste($categoriaID = null, $categoriaNombre = null, $update = false, $insert = false) {
            $conn = null; $stmt = null;

			try {
				// Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) { throw new Exception($result["message"]); }
				$conn = $result["connection"];

				// Inicializa la consulta base
				$queryCheck = "SELECT " . CATEGORIA_ID . ", " . CATEGORIA_ESTADO . " FROM " . TB_CATEGORIA . " WHERE ";
				$params = [];
				$types = "";
				
				// Consulta para verificar si existe una categoria con el ID ingresado
                if ($categoriaID && (!$update && !$insert)) {
                    $queryCheck .= CATEGORIA_ID . " = ? ";
                    $params[] = $categoriaID;
                    $types .= "i";
                }
				
                // Consulta para verificar si existe una categoria con el nombre ingresado
				else if ($insert && $categoriaNombre) {
					// Verificar existencia por nombre
					$queryCheck .= CATEGORIA_NOMBRE . " = ? ";
					$params[] = $categoriaNombre;
					$types .= 's';
				}

				// Consulta en caso de actualizar para verificar si existe ya una categoria con el mismo nombre además de la que se va a actualizar
				else if ($update && ($categoriaNombre && $categoriaID)) {
					$queryCheck .= CATEGORIA_NOMBRE . " = ? AND " . CATEGORIA_ID . " <> ? ";
					$params = [$categoriaNombre, $categoriaID];
					$types .= 'si';
				}

				// En caso de no cumplirse ninguna condicion
                else {
                    // Registrar parámetros faltantes y lanzar excepción
                    $missingParamsLog = "Faltan parámetros para verificar la existencia de la categoría:";
                    if (!$categoriaID) $missingParamsLog .= " categoriaID [" . ($categoriaID ?? 'null') . "]";
                    if (!$categoriaNombre) $missingParamsLog .= " categoriaNombre [" . ($categoriaNombre ?? 'null') . "]";
                    Utils::writeLog($missingParamsLog, DATA_LOG_FILE, WARN_MESSAGE, $this->className, __LINE__);
                    throw new Exception("Faltan parámetros para verificar la existencia de la categoría en la base de datos.");
                }

				// Asignar los parámetros y ejecutar la consulta
                $stmt = mysqli_prepare($conn, $queryCheck);
				mysqli_stmt_bind_param($stmt, $types, ...$params);
				mysqli_stmt_execute($stmt);
				$result = mysqli_stmt_get_result($stmt);

				// Verificar si existe una categoria con el ID o nombre ingresado
                if ($row = mysqli_fetch_assoc($result)) {
                    // Verificar si está inactiva (bit de estado en 0)
                    $isInactive = $row[CATEGORIA_ESTADO] == 0;
                    return ["success" => true, "exists" => true, "inactive" => $isInactive, "categoriaID" => $row[CATEGORIA_ID]];
                }

				// Retorna false si no se encontraron resultados
                $messageParams = [];
                if ($categoriaID) { $messageParams[] = "'ID [$categoriaID]'"; }
                if ($categoriaNombre)  { $messageParams[] = "'Nombre [$categoriaNombre]'"; }
                $params = implode(', ', $messageParams);

                $message = "No se encontró ninguna categoria ($params) en la base de datos.";
                return ["success" => true, "exists" => false, "message" => $message];
			} catch (Exception $e) {
				// Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al verificar la existencia de la categoria en la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
			} finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
		}

		// Método para insertar una nueva categoría
		public function insertCategoria($categoria, $conn = null) {
			$createdConnection = false;
			$stmt = null;

			try {
				// Obtener los valores de las propiedades del objeto
				$categoriaNombre = $categoria->getCategoriaNombre();

				// Verificar si ya existe una categoría con el mismo nombre
				$check = $this->categoriaExiste(null, $categoriaNombre, false, true);
				if (!$check["success"]) { throw new Exception($check["message"]); }

				// En caso de ya existir la categoría pero estar inactiva
				if ($check["exists"] && $check["inactive"]) {
					$message = "Ya existe una categoría con el mismo nombre ($categoriaNombre) en la base de datos, pero está inactiva. Desea reactivarla?";
                    return ["success" => true, "message" => $message, "inactive" => $result["inactive"], "id" => $result["categoriaID"]];
				}

				// En caso de ya existir la categoría y estar activa
				if ($check["exists"]) {
					$message = "La categoría con 'Nombre [$categoriaNombre]' ya existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    return ["success" => true, "message" => "Ya existe un categoria con el mismo nombre ($categoriaNombre) en la base de datos."];
				}

				// Establece una conexión con la base de datos
				if (is_null($conn)) {
					$result = $this->getConnection();
					if (!$result["success"]) { throw new Exception($result["message"]); }
					$conn = $result["connection"];
					$createdConnection = true;
				}

				// Obtenemos el último ID de la tabla tb_categoria
				$queryGetLastId = "SELECT MAX(" . CATEGORIA_ID . ") FROM " . TB_CATEGORIA;
				$idCont = mysqli_query($conn, $queryGetLastId);
				$nextId = 1;

				// Calcula el siguiente ID para la nueva entrada
				if ($row = mysqli_fetch_row($idCont)) {
					$nextId = (int) trim($row[0]) + 1;
				}

				// Crea una consulta y un statement SQL para insertar el nuevo registro
				$queryInsert = 
					"INSERT INTO " . TB_CATEGORIA . " ("
					. CATEGORIA_ID . ", "
					. CATEGORIA_NOMBRE . ", "
					. CATEGORIA_DESCRIPCION .", "
					. CATEGORIA_ESTADO ." " . 
					") VALUES (?, ?, ?, true)";
				$stmt = mysqli_prepare($conn, $queryInsert);

				// Obtener los valores de las propiedades faltantes
				$categoriaDescripcion = $categoria->getCategoriaDescripcion();

				// Asignar los parámetros y ejecutar la consulta
				mysqli_stmt_bind_param($stmt, 'iss', $nextId, $categoriaNombre, $categoriaDescripcion);
				mysqli_stmt_execute($stmt);

				return ["success" => true, "message" => "Categoría insertada exitosamente", "id" => $nextId];
			}catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al insertar la categoría en la base de datos',
                    $this->className
                );
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if ($createdConnection && isset($conn)) { mysqli_close($conn); }
			}
		}

		// Método para actualizar una categoría existente
		public function updateCategoria($categoria, $conn = null) {
			$createdConnection = false;
			$stmt = null;

			try {
				// Obtener los valores de las propiedades del objeto
				$categoriaID = $categoria->getCategoriaID();
				$categoriaNombre = $categoria->getCategoriaNombre();

				// Verificar si la categoría ya existe
				$check = $this->categoriaExiste($categoriaID);
				if (!$check["success"]) { throw new Exception($check["message"]); }

				// En caso de no existir la categoría
				if (!$check["exists"]) {
					$message = "No se encontró la categoría con 'ID [$categoriaID]' en la base de datos.";
					Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
					return ["success" => false, "message" => "La categoría seleccionada no existe en la base de datos."];
				}

				// Verifica que no exista otra categoría con la misma información
				$check = $this->categoriaExiste($categoriaID, $categoriaNombre, true);
				if (!$check["success"]) { throw new Exception($check["message"]); }

				// En caso de ya existir la categoría
				if ($check["exists"]) {
					$message = "La categoría con 'Nombre [$categoriaNombre]' ya existe en la base de datos.";
					Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
					return ["success" => true, "message" => "Ya existe un categoria con el mismo nombre ($categoriaNombre) en la base de datos."];
				}

				// Establece una conexión con la base de datos
				if (is_null($conn)) {
					$result = $this->getConnection();
					if (!$result["success"]) { throw new Exception($result["message"]); }
					$conn = $result["connection"];
					$createdConnection = true;
				}

				// Crea la consulta SQL para actualizar la categoría
				$queryUpdate = "UPDATE " . TB_CATEGORIA . " SET "
					. CATEGORIA_NOMBRE . " = ?, "
					. CATEGORIA_DESCRIPCION . " = ?, "
					. CATEGORIA_ESTADO . " = TRUE "
					. "WHERE " . CATEGORIA_ID . " = ?";

				$stmt = mysqli_prepare($conn, $queryUpdate);

				// Obtener los valores de las propiedades faltantes
				$categoriaDescripcion = $categoria->getCategoriaDescripcion();

				// Asignar los parámetros y ejecutar la consulta
				mysqli_stmt_bind_param($stmt, 'ssi', $categoriaNombre, $categoriaDescripcion, $categoriaID);
				mysqli_stmt_execute($stmt);

				return ["success" => true, "message" => "Categoría actualizada exitosamente"];
			}catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al actualizar la categoría en la base de datos',
                    $this->className
                );

                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if ($createdConnection && isset($conn)) { mysqli_close($conn); }
			}
		}

		// Método para eliminar (desactivar) una categoría
		public function deleteCategoria($categoriaID, $conn = null) {
			$createdConnection = false;
			$stmt = null;

			try {
				// Verificar si la categoría ya existe
				$check = $this->categoriaExiste($categoriaID);
				if (!$check["success"]) { throw new Exception($check["message"]); }

				// En caso de no existir la categoría
				if (!$check["exists"]) {
					$message = "No se encontró la categoría con 'ID [$categoriaID]' en la base de datos.";
					Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
					return ["success" => false, "message" => "La categoría seleccionada no existe en la base de datos."];
				}

				// Establece una conexión con la base de datos
				if (is_null($conn)) {
					$result = $this->getConnection();
					if (!$result["success"]) { throw new Exception($result["message"]); }
					$conn = $result["connection"];
					$createdConnection = true;
				}

				// Crea la consulta SQL para desactivar la categoría
				$queryDelete = "UPDATE " . TB_CATEGORIA . " SET " . CATEGORIA_ESTADO . " = false " . " WHERE " . CATEGORIA_ID . " = ?";
				$stmt = mysqli_prepare($conn, $queryDelete);
				mysqli_stmt_bind_param($stmt, 'i', $categoriaID);
				mysqli_stmt_execute($stmt);

				// Devolver mensaje de éxito
				return ["success" => true, "message" => "Categoría eliminada exitosamente"];
			}catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al eliminar la categoría en la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if ($createdConnection && isset($conn)) { mysqli_close($conn); }
			}
		}
		
		// Método para obtener todas las categorías activas
		public function getAllTBCategorias($onlyActive = false, $deleted = false) {
			$conn = null;

			try {
				// Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

				// Obtenemos la lista de categorías
				$querySelect = "SELECT * FROM " . TB_CATEGORIA;
				if ($onlyActive) { $querySelect .= " WHERE " . CATEGORIA_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE'); }
				$result = mysqli_query($conn, $querySelect);

				// Creamos la lista con los datos obtenidos
				$categorias = [];
				while ($row = mysqli_fetch_array($result)) {
					$categoria = new Categoria(
						$row[CATEGORIA_ID],
						$row[CATEGORIA_NOMBRE],
						$row[CATEGORIA_DESCRIPCION],
						$row[CATEGORIA_ESTADO]
					);
					$categorias[] = $categoria;
				}

				return ["success" => true, "categorias" => $categorias];
			} catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener las categorías de la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            }  finally {
				// Cierra la conexión
				if (isset($conn)) { mysqli_close($conn); }
			}
		}

		// Método para obtener categorías con paginación
		public function getPaginatedCategorias($page, $size, $sort = null, $onlyActive = false, $deleted = false) {
			$conn = null; $stmt = null;

			try {
				// Calcular el offset
                $offset = ($page - 1) * $size;

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

				// Consultar el total de registros
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_CATEGORIA;
				if ($onlyActive) { $queryTotalCount .= " WHERE " . CATEGORIA_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE'); }
				
				// Ejecutar la consulta y obtener el total de registros
				$totalResult = mysqli_query($conn, $queryTotalCount);
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = (int) $totalRow['total'];
                $totalPages = ceil($totalRecords / $size);

				// Construir la consulta SQL para paginación
                $querySelect = "SELECT * FROM " . TB_CATEGORIA;
				if ($onlyActive) { $querySelect .= " WHERE " . CATEGORIA_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE'); }

				// Añadir la cláusula de ordenamiento si se proporciona
                if ($sort) { $querySelect .= " ORDER BY categoria" . $sort; }

				// Añadir la cláusula de límite y desplazamiento
                $querySelect .= " LIMIT ? OFFSET ?";

				// Crear un statement y ejecutar la consulta
				$stmt = mysqli_prepare($conn, $querySelect);
				mysqli_stmt_bind_param($stmt, 'ii', $size, $offset);
				mysqli_stmt_execute($stmt);

				// Obtener los resultados de la consulta
				$result = mysqli_stmt_get_result($stmt);

				// Creamos la lista con los datos obtenidos
				$categorias = [];
				while ($row = mysqli_fetch_assoc($result)) {
					$categoria = new Categoria(
						$row[CATEGORIA_ID],
						$row[CATEGORIA_NOMBRE],
						$row[CATEGORIA_DESCRIPCION],
						$row[CATEGORIA_ESTADO]
					);
					$categorias[] = $categoria;
				}

				return [
					"success" => true,
					"page" => $page,
					"size" => $size,
					"totalPages" => $totalPages,
					"totalRecords" => $totalRecords,
					"categorias" => $categorias
				];
			} catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener la lista de categorias desde la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            }  finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
		}

		// Método para obtener una categoría por su ID
		public function getCategoriaByID($categoriaID, $onlyActive = true, $deleted = false) {
			$conn = null; $stmt = null;

			try {
				// Verificar si la categoría ya existe
				$check = $this->categoriaExiste($categoriaID);
				if (!$check["success"]) { throw new Exception($check["message"]); }

				// En caso de no existir la categoría
				if (!$check["exists"]) {
					$message = "No se encontró la categoría con 'ID [$categoriaID]' en la base de datos.";
					Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
					return ["success" => false, "message" => "La categoría seleccionada no existe en la base de datos."];
				}

				// Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) { throw new Exception($result["message"]); }
				$conn = $result["connection"];

				// Consultar la categoría por su ID
				$querySelect = "
					SELECT 
						* 
					FROM " . 
						TB_CATEGORIA . " 
					WHERE " . 
						CATEGORIA_ID . " = ?" . ($onlyActive ? " AND " . 
						CATEGORIA_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE') : '')
				;
				$stmt = mysqli_prepare($conn, $querySelect);
				mysqli_stmt_bind_param($stmt, 'i', $categoriaID);
				mysqli_stmt_execute($stmt);

				// Obtener los resultados de la consulta
				$result = mysqli_stmt_get_result($stmt);

				// Verificar si se encontró la categoría
				if ($row = mysqli_fetch_assoc($result)) {
					$categoria = new Categoria(
						$row[CATEGORIA_ID],
						$row[CATEGORIA_NOMBRE],
						$row[CATEGORIA_DESCRIPCION],
						$row[CATEGORIA_ESTADO]
					);
					return ["success" => true, "categoria" => $categoria];
				}

				// En caso de no encontrarse la categoría
				$message = "No se encontró la categoría con 'ID [$categoriaID]' en la base de datos.";
				Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
				return ["success" => false, "message" => "La categoría seleccionada no existe en la base de datos."];
			}catch (Exception $e) {
				// Manejo del error dentro del bloque catch
				$userMessage = $this->handleMysqlError(
					$e->getCode(), $e->getMessage(),
					'Error al obtener la categoría por su ID desde la base de datos',
					$this->className
				);
		
				// Devolver mensaje amigable para el usuario
				return ["success" => false, "message" => $userMessage];
			}  finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
		}

	}

?>