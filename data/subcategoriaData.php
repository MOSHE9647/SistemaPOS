<?php

	require_once dirname(__DIR__, 1) . '/data/data.php';
    require_once dirname(__DIR__, 1) . '/data/categoriaData.php';
	require_once dirname(__DIR__, 1) . '/domain/Categoria.php';
    require_once dirname(__DIR__, 1) . '/domain/Subcategoria.php';
	require_once dirname(__DIR__, 1) . '/utils/Utils.php';
	require_once dirname(__DIR__, 1) . '/utils/Variables.php';

	class SubcategoriaData extends Data {

		private $className;

		// Constructor
		public function __construct() {
			$this->className = get_class($this);
			parent::__construct();
		}

		// Función para verificar si una subcategoría con el mismo nombre ya existe en la base de datos
		public function subcategoriaExiste($subcategoriaID = null, $subcategoriaNombre = null, $categoriaID = null, $update = false, $insert = false) {
			try {
				// Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) { throw new Exception($result["message"]); }
				$conn = $result["connection"];

				// Inicializa la consulta base
				$queryCheck = "SELECT " . SUBCATEGORIA_ID . ", " . SUBCATEGORIA_ESTADO . " FROM " . TB_SUBCATEGORIA . " WHERE ";
				$params = [];
				$types = "";
				
				// Consulta para verificar si existe una subcategoria con el ID ingresado
                if ($subcategoriaID && (!$update && !$insert)) {
                    $queryCheck .= SUBCATEGORIA_ID . " = ? ";
                    $params[] = $subcategoriaID;
                    $types .= "i";
                }
				
                // Consulta para verificar si existe una subcategoria con el nombre ingresado
				else if ($insert && ($subcategoriaNombre && $categoriaID)) {
					// Verificar existencia por nombre y ID de categoria
					$queryCheck .= SUBCATEGORIA_NOMBRE . " = ? AND " . CATEGORIA_ID . " = ? ";
					$params = [$subcategoriaNombre, $categoriaID];
					$types .= 'si';
				}

				// Consulta en caso de actualizar para verificar si existe ya una subcategoria con el mismo nombre además de la que se va a actualizar
				else if ($update && (($subcategoriaNombre && $categoriaID) && $subcategoriaID)) {
					$queryCheck .= SUBCATEGORIA_NOMBRE . " = ? AND " . CATEGORIA_ID . " = ? AND " . SUBCATEGORIA_ID . " <> ? ";
					$params = [$subcategoriaNombre, $categoriaID, $subcategoriaID];
					$types .= 'sii';
				}

				// En caso de no cumplirse ninguna condicion
                else {
                    // Registrar parámetros faltantes y lanzar excepción
                    $missingParamsLog = "Faltan parámetros para verificar la existencia de la subcategoría:";
                    if (!$categoriaID) $missingParamsLog .= " categoriaID [" . ($categoriaID ?? 'null') . "]";
                    if (!$subcategoriaID) $missingParamsLog .= " subcategoriaID [" . ($subcategoriaID ?? 'null') . "]";
                    if (!$subcategoriaNombre) $missingParamsLog .= " subcategoriaNombre [" . ($subcategoriaNombre ?? 'null') . "]";
                    Utils::writeLog($missingParamsLog, DATA_LOG_FILE, WARN_MESSAGE, $this->className);
                    throw new Exception("Faltan parámetros para verificar la existencia de la subcategoría en la base de datos.");
                }

				// Asignar los parámetros y ejecutar la consulta
                $stmt = mysqli_prepare($conn, $queryCheck);
				mysqli_stmt_bind_param($stmt, $types, ...$params);
				mysqli_stmt_execute($stmt);
				$result = mysqli_stmt_get_result($stmt);

				// Verificar si existe una subcategoria con el ID o nombre ingresado
                if ($row = mysqli_fetch_assoc($result)) {
                    // Verificar si está inactiva (bit de estado en 0)
                    $isInactive = $row[SUBCATEGORIA_ESTADO] == 0;
                    return ["success" => true, "exists" => true, "inactive" => $isInactive, "subcategoriaID" => $row[SUBCATEGORIA_ID]];
                }

				// Retorna false si no se encontraron resultados
                $messageParams = [];
                if ($subcategoriaID) { $messageParams[] = "'ID [$subcategoriaID]'"; }
                if ($subcategoriaNombre) { $messageParams[] = "'Nombre [$subcategoriaNombre]'"; }
                if ($categoriaID) { $messageParams[] = "'Categoria ID [$categoriaID]'"; }
                $params = implode(', ', $messageParams);

                $message = "No se encontró ninguna subcategoria ($params) en la base de datos.";
                return ["success" => true, "exists" => false, "message" => $message];
			} catch (Exception $e) {
				// Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al verificar la existencia de la subcategoria en la base de datos',
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

		// Método para insertar una nueva subcategoría
		public function insertSubcategoria($subcategoria, $conn = null) {
			$createdConnection = false;
			$stmt = null;

			try {
				// Obtener los valores de las propiedades del objeto
				$subcategoriaNombre = $subcategoria->getSubcategoriaNombre();
                $categoriaID = $subcategoria->getSubcategoriaCategoria()->getCategoriaID();
                
				// Verificar si ya existe una subcategoría con el mismo nombre y id de categoria
				$check = $this->subcategoriaExiste(null, $subcategoriaNombre, $categoriaID, false, true);
				if (!$check["success"]) { throw new Exception($check["message"]); }

				// En caso de ya existir la subcategoría pero estar inactiva
				if ($check["exists"] && $check["inactive"]) {
					$message = "Ya existe una subcategoría con el mismo nombre ($subcategoriaNombre) en la base de datos, pero está inactiva. Desea reactivarla?";
                    return ["success" => true, "message" => $message, "inactive" => $result["inactive"], "id" => $result["subcategoriaID"]];
				}

				// En caso de ya existir la subcategoría y estar activa
				if ($check["exists"]) {
                    $categoriaNombre = $subcategoria->getSubcategoriaCategoria()->getCategoriaNombre();
					$message = "La subcategoría con 'Nombre [$subcategoriaNombre]' y 'Categoría [$categoriaID]' ya existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return [
                        "success" => true, 
                        "message" => "Ya existe un subcategoria con el mismo nombre ($subcategoriaNombre) y categoria ($categoriaNombre) en la base de datos."
                    ];
				}

                // Establece una conexión con la base de datos
				if (is_null($conn)) {
					$result = $this->getConnection();
					if (!$result["success"]) { throw new Exception($result["message"]); }
					$conn = $result["connection"];
					$createdConnection = true;
				}

				// Obtenemos el último ID de la tabla tb_subcategoria
				$queryGetLastId = "SELECT MAX(" . SUBCATEGORIA_ID . ") FROM " . TB_SUBCATEGORIA;
				$idCont = mysqli_query($conn, $queryGetLastId);
				$nextId = 1;

				// Calcula el siguiente ID para la nueva entrada
				if ($row = mysqli_fetch_row($idCont)) {
					$nextId = (int) trim($row[0]) + 1;
				}

				// Crea una consulta y un statement SQL para insertar el nuevo registro
				$queryInsert = 
					"INSERT INTO " . TB_SUBCATEGORIA . " ("
					. SUBCATEGORIA_ID . ", "
					. CATEGORIA_ID . ", "
					. SUBCATEGORIA_NOMBRE . ", "
					. SUBCATEGORIA_DESCRIPCION .
					") VALUES (?, ?, ?, ?)";
				$stmt = mysqli_prepare($conn, $queryInsert);

				// Obtener los valores de las propiedades faltantes
				$subcategoriaDescripcion = $subcategoria->getSubcategoriaDescripcion();

				// Asignar los parámetros y ejecutar la consulta
				mysqli_stmt_bind_param($stmt, 'iiss', $nextId, $categoriaID, $subcategoriaNombre, $subcategoriaDescripcion);
				mysqli_stmt_execute($stmt);

				return ["success" => true, "message" => "Categoría insertada exitosamente"];
			}catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al insertar la subcategoría en la base de datos',
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

		// Método para actualizar una subcategoría existente
		public function updateSubcategoria($subcategoria, $conn = null) {
			$createdConnection = false;
			$stmt = null;

			try {
				// Obtener los valores de las propiedades del objeto
                $categoria = $subcategoria->getSubcategoriaCategoria();
                $categoriaID = $categoria->getCategoriaID();
				$subcategoriaID = $subcategoria->getSubcategoriaID();
				$subcategoriaNombre = $subcategoria->getSubcategoriaNombre();

				// Verificar si la subcategoría ya existe
				$check = $this->subcategoriaExiste($subcategoriaID);
				if (!$check["success"]) { throw new Exception($check["message"]); }

				// En caso de no existir la subcategoría
				if (!$check["exists"]) {
					$message = "No se encontró la subcategoría con 'ID [$subcategoriaID]' en la base de datos.";
					Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
					return ["success" => false, "message" => "La subcategoría seleccionada no existe en la base de datos."];
				}

				// Verifica que no exista otra subcategoría con la misma información
				$check = $this->subcategoriaExiste($subcategoriaID, $subcategoriaNombre, $categoriaID, true);
				if (!$check["success"]) { throw new Exception($check["message"]); }

				// En caso de ya existir la subcategoría
				if ($check["exists"]) {
                    $categoriaNombre = $categoria->getCategoriaNombre();
					$message = "La subcategoría con 'Nombre [$subcategoriaNombre]' y 'Categoría [$categoriaID]' ya existe en la base de datos.";
					Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
					return [
                        "success" => true, 
                        "message" => "Ya existe un subcategoria con el mismo nombre ($subcategoriaNombre) y categoria ($categoriaNombre) en la base de datos."
                    ];
				}

				// Establece una conexión con la base de datos
				if (is_null($conn)) {
					$result = $this->getConnection();
					if (!$result["success"]) { throw new Exception($result["message"]); }
					$conn = $result["connection"];
					$createdConnection = true;
				}

				// Crea la consulta SQL para actualizar la subcategoría
				$queryUpdate = "UPDATE " . TB_SUBCATEGORIA . " SET "
                    . CATEGORIA_ID . " = ?, "
					. SUBCATEGORIA_NOMBRE . " = ?, "
					. SUBCATEGORIA_DESCRIPCION . " = ? "
					. "WHERE " . SUBCATEGORIA_ID . " = ?";

				$stmt = mysqli_prepare($conn, $queryUpdate);

				// Obtener los valores de las propiedades faltantes
				$subcategoriaDescripcion = $subcategoria->getSubcategoriaDescripcion();

				// Asignar los parámetros y ejecutar la consulta
				mysqli_stmt_bind_param($stmt, 'issi', $categoriaID, $subcategoriaNombre, $subcategoriaDescripcion, $subcategoriaID);
				mysqli_stmt_execute($stmt);

				return ["success" => true, "message" => "Categoría actualizada exitosamente"];
			}catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al actualizar la subcategoría en la base de datos',
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

		// Método para eliminar (desactivar) una subcategoría
		public function deleteSubcategoria($subcategoriaID, $conn = null) {
			$createdConnection = false;
			$stmt = null;

			try {
				// Verificar si la subcategoría ya existe
				$check = $this->subcategoriaExiste($subcategoriaID);
				if (!$check["success"]) { throw new Exception($check["message"]); }

				// En caso de no existir la subcategoría
				if (!$check["exists"]) {
					$message = "No se encontró la subcategoría con 'ID [$subcategoriaID]' en la base de datos.";
					Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
					return ["success" => false, "message" => "La subcategoría seleccionada no existe en la base de datos."];
				}

				// Establece una conexión con la base de datos
				if (is_null($conn)) {
					$result = $this->getConnection();
					if (!$result["success"]) { throw new Exception($result["message"]); }
					$conn = $result["connection"];
					$createdConnection = true;
				}

				// Crea la consulta SQL para desactivar la subcategoría
				$queryDelete = "UPDATE " . TB_SUBCATEGORIA . " SET " . SUBCATEGORIA_ESTADO . " = false " . " WHERE " . SUBCATEGORIA_ID . " = ?";
				$stmt = mysqli_prepare($conn, $queryDelete);
				mysqli_stmt_bind_param($stmt, 'i', $subcategoriaID);
				mysqli_stmt_execute($stmt);

				// Devolver mensaje de éxito
				return ["success" => true, "message" => "Categoría eliminada exitosamente"];
			}catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al eliminar la subcategoría en la base de datos',
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
		
		// Método para obtener todas las subcategorías activas
		public function getAllTBSubcategorias($onlyActive = false, $deleted = false) {
			$conn = null;

			try {
				// Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

				// Obtenemos la lista de subcategorías
				$querySelect = "SELECT * FROM " . TB_SUBCATEGORIA;
				if ($onlyActive) { $querySelect .= " WHERE " . SUBCATEGORIA_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE'); }
				$result = mysqli_query($conn, $querySelect);

				// Creamos la lista con los datos obtenidos
				$subcategorias = [];
				while ($row = mysqli_fetch_array($result)) {
                    // Obtener la categoría a la que pertenece la subcategoría
                    $categoriaData = new CategoriaData();
                    $categoria = $categoriaData->getCategoriaByID($row[CATEGORIA_ID], false);
                    if (!$categoria["success"]) { throw new Exception($categoria["message"]); }

					$subcategoria = new Subcategoria(
						$row[SUBCATEGORIA_ID],
						$row[SUBCATEGORIA_NOMBRE],
						$row[SUBCATEGORIA_DESCRIPCION],
                        $categoria["categoria"],
						$row[SUBCATEGORIA_ESTADO]
					);
					$subcategorias[] = $subcategoria;
				}

				return ["success" => true, "subcategorias" => $subcategorias];
			} catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener las subcategorías de la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            }  finally {
				// Cierra la conexión y el statement
				if (isset($conn)) { mysqli_close($conn); }
			}
		}

		// Método para obtener subcategorías con paginación
		public function getPaginatedSubcategorias($page, $size, $sort = null, $onlyActive = false, $deleted = false) {
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
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_SUBCATEGORIA;
				if ($onlyActive) { $queryTotalCount .= " WHERE " . SUBCATEGORIA_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE'); }
				
				// Ejecutar la consulta y obtener el total de registros
				$totalResult = mysqli_query($conn, $queryTotalCount);
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = (int) $totalRow['total'];
                $totalPages = ceil($totalRecords / $size);

				// Construir la consulta SQL para paginación
                $querySelect = "SELECT * FROM " . TB_SUBCATEGORIA;
				if ($onlyActive) { $querySelect .= " WHERE " . SUBCATEGORIA_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE'); }

				// Añadir la cláusula de ordenamiento si se proporciona
                if ($sort) { $querySelect .= " ORDER BY subcategoria" . $sort; }

				// Añadir la cláusula de límite y desplazamiento
                $querySelect .= " LIMIT ? OFFSET ?";

				// Crear un statement y ejecutar la consulta
				$stmt = mysqli_prepare($conn, $querySelect);
				mysqli_stmt_bind_param($stmt, 'ii', $size, $offset);
				mysqli_stmt_execute($stmt);

				// Obtener los resultados de la consulta
				$result = mysqli_stmt_get_result($stmt);

				// Creamos la lista con los datos obtenidos
				$subcategorias = [];
				while ($row = mysqli_fetch_assoc($result)) {
                    // Obtener la categoría a la que pertenece la subcategoría
                    $categoriaData = new CategoriaData();
                    $categoria = $categoriaData->getCategoriaByID($row[CATEGORIA_ID], false);
                    if (!$categoria["success"]) { throw new Exception($categoria["message"]); }

					$subcategoria = new Subcategoria(
						$row[SUBCATEGORIA_ID],
						$row[SUBCATEGORIA_NOMBRE],
						$row[SUBCATEGORIA_DESCRIPCION],
                        $categoria["categoria"],
						$row[SUBCATEGORIA_ESTADO]
					);
					$subcategorias[] = $subcategoria;
				}

				return [
					"success" => true,
					"page" => $page,
					"size" => $size,
					"totalPages" => $totalPages,
					"totalRecords" => $totalRecords,
					"subcategorias" => $subcategorias
				];
			}catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener la lista de subcategorias desde la base de datos',
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

        // Método para obtener todas las subcategorías de una categoría
        public function getSubcategoriasByCategoria($categoriaID) {
            $conn = null; $stmt = null;

            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Consultar las subcategorías de una categoría
                $querySelect = "SELECT * FROM " . TB_SUBCATEGORIA . " WHERE " . CATEGORIA_ID . " = ? AND " . SUBCATEGORIA_ESTADO . " != FALSE";
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, 'i', $categoriaID);
                mysqli_stmt_execute($stmt);

                // Obtener los resultados de la consulta
                $result = mysqli_stmt_get_result($stmt);

                // Creamos la lista con los datos obtenidos
                $subcategorias = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    // Obtener la categoría a la que pertenece la subcategoría
                    $categoriaData = new CategoriaData();
                    $categoria = $categoriaData->getCategoriaByID($row[CATEGORIA_ID], false);
                    if (!$categoria["success"]) { throw new Exception($categoria["message"]); }

                    $subcategoria = new Subcategoria(
                        $row[SUBCATEGORIA_ID],
                        $row[SUBCATEGORIA_NOMBRE],
                        $row[SUBCATEGORIA_DESCRIPCION],
                        $categoria["categoria"],
                        $row[SUBCATEGORIA_ESTADO]
                    );
                    $subcategorias[] = $subcategoria;
                }

                return ["success" => true, "subcategorias" => $subcategorias];
            }catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener las subcategorías de la categoría desde la base de datos',
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

		// Método para obtener una subcategoría por su ID
		public function getSubcategoriaByID($subcategoriaID, $onlyActive = true, $deleted = false) {
			$conn = null; $stmt = null;

			try {
				// Verificar si la subcategoría ya existe
				$check = $this->subcategoriaExiste($subcategoriaID);
				if (!$check["success"]) { throw new Exception($check["message"]); }

				// En caso de no existir la subcategoría
				if (!$check["exists"]) {
					$message = "No se encontró la subcategoría con 'ID [$subcategoriaID]' en la base de datos.";
					Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
					return ["success" => false, "message" => "La subcategoría seleccionada no existe en la base de datos."];
				}

				// Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) { throw new Exception($result["message"]); }
				$conn = $result["connection"];

				// Consultar la subcategoría por su ID
				$querySelect = "
					SELECT 
						* 
					FROM " . 
						TB_SUBCATEGORIA . " 
					WHERE " . 
						SUBCATEGORIA_ID . " = ?" . ($onlyActive ? " AND " . 
						SUBCATEGORIA_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE') : '');
				$stmt = mysqli_prepare($conn, $querySelect);
				mysqli_stmt_bind_param($stmt, 'i', $subcategoriaID);
				mysqli_stmt_execute($stmt);

				// Obtener los resultados de la consulta
				$result = mysqli_stmt_get_result($stmt);

				// Verificar si se encontró la subcategoría
				if ($row = mysqli_fetch_assoc($result)) {
                    // Obtener la categoría a la que pertenece la subcategoría
                    $categoriaData = new CategoriaData();
                    $categoria = $categoriaData->getCategoriaByID($row[CATEGORIA_ID], $onlyActive);
                    if (!$categoria["success"]) { throw new Exception($categoria["message"]); }

					$subcategoria = new Subcategoria(
						$row[SUBCATEGORIA_ID],
						$row[SUBCATEGORIA_NOMBRE],
						$row[SUBCATEGORIA_DESCRIPCION],
                        $categoria["categoria"],
						$row[SUBCATEGORIA_ESTADO]
					);
					return ["success" => true, "subcategoria" => $subcategoria];
				}

				// En caso de no encontrarse la subcategoría
				$message = "No se encontró la subcategoría con 'ID [$subcategoriaID]' en la base de datos.";
				Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
				return ["success" => false, "message" => "La subcategoría seleccionada no existe en la base de datos."];
			}catch (Exception $e) {
				// Manejo del error dentro del bloque catch
				$userMessage = $this->handleMysqlError(
					$e->getCode(), $e->getMessage(),
					'Error al obtener la subcategoría por su ID desde la base de datos',
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