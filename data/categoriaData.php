<?php

	require_once 'data.php';
	require_once __DIR__ . '/../domain/Categoria.php';
	require_once __DIR__ . '/../utils/Utils.php';
	require_once __DIR__ . '/../utils/Variables.php';

	class CategoriaData extends Data {

		// Constructor
		public function __construct() {
			parent::__construct();
		}

		// Función para verificar si una categoría con el mismo nombre ya existe en la base de datos
		public function categoriaExiste($categoriaID = null, $categoriaNombre = null) {
			try {
				// Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];

				// Inicializa la consulta base
				$queryCheck = "SELECT * FROM " . TB_CATEGORIA . " WHERE ";
				$params = [];
				$types = "";
				
				if ($categoriaID !== null) {
					// Verificar existencia por ID y que el estado no sea false
					$queryCheck .= CATEGORIA_ID . " = ? "; //"AND " . CATEGORIA_ESTADO . " != false";
					$params[] = $categoriaID;
					$types .= 'i';
				} elseif ($categoriaNombre !== null) {
					// Verificar existencia por nombre
					$queryCheck .= CATEGORIA_NOMBRE . " = ? AND " . CATEGORIA_ESTADO . " != false";
					$params[] = $categoriaNombre;
					$types .= 's';
				} else {
					throw new Exception("Se requiere al menos un parámetro: categoriaID o categoriaNombre");
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

		public function nombreCategoriaExiste($categoriaNombre,$categoriaID= null){
			$response = [];
			try {
				// Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) { throw new Exception($result["message"]); }
				$conn = $result["connection"];

				// Inicializa la consulta base
				$queryCheck = "SELECT * FROM " . TB_CATEGORIA . " WHERE ";
				$params = [];
				$types = "";
				
				if ($categoriaNombre !== null && $categoriaID !== null) {
					// Verificar existencia por ID y que el estado no sea false
					$queryCheck .= CATEGORIA_NOMBRE . " = ? AND " . CATEGORIA_ESTADO . " != false AND " . CATEGORIA_ID ." <> ? ";
					$params[] = $categoriaNombre;
					$params[] = $categoriaID;
					$types .= 'si';
				} elseif ($categoriaNombre !== null) {
					// Verificar existencia por nombre
					$queryCheck .= CATEGORIA_NOMBRE . " = ? ";
					$params[] = $categoriaNombre;
					$types .= 's';
				} else {
					throw new Exception("Se requiere al menos un parámetro: categoriaID o categoriaNombre");
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
                    if ($row = mysqli_fetch_assoc($result)) {
                        // Verificar si está inactivo (bit de estado en 0)
                        $isInactive = $row[CATEGORIA_ESTADO] == 0;
                        $response = ["success" => true, "exists" => true, "inactive" => $isInactive, "id" => $row[CATEGORIA_ID]];
                    }
				}else{
					$response =  ["success" => true, "exists" => false];
				}
			} catch (Exception $e) {
				$userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al verificar la existencia del nombre de la categoria en la base de datos'
                );
                // Devolver mensaje amigable para el usuario
                $response =  ["success" => false, "message" => $userMessage];
			} finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}

			return $response;
		}

		// Método para insertar una nueva categoría
		public function insertCategoria($categoria) {
			$response = [];
			try {
				// Obtener los valores de las propiedades del objeto
				$categoriaNombre = $categoria->getCategoriaNombre();
				$categoriaDescripcion = $categoria->getCategoriaDescripcion();
				$categoriaEstado = true; // Estado por defecto: activo

				// Verifica que las propiedades no estén vacías
				if (empty($categoriaNombre)) {
					throw new Exception("El nombre de la categoría está vacío");
				}


				$check = $this->nombreCategoriaExiste($categoriaNombre);
				if(!$check['success']){
					return $check;
				}
				if($check['exists'] && $check["inactive"]){
					return ["success" => true, "inactive" => true, "message"=>"Hay una categoria con el nombre [$categoriaNombre] incativo, ¿Deseas reactivarlo?", "id"=>$check["id"]];
				}
				if($check['exists']){
					throw new Exception("El nombre de la categoria ya existe.");
				}
				// Verifica si la categoría ya existe
				// $check = $this->categoriaExiste(null, $categoriaNombre);
				// if (!$check["success"]) {
				// 	return $check; // Error al verificar la existencia
				// }
				// if ($check["exists"]) {
				// 	throw new Exception("Ya existe una categoría con el mismo nombre");
				// }

				// Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];

				// Obtenemos el último ID de la tabla tb_categoria
				$queryGetLastId = "SELECT MAX(" . CATEGORIA_ID . ") AS categoriaID FROM " . TB_CATEGORIA;
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
				$queryInsert = "INSERT INTO " . TB_CATEGORIA . " ("
                    . CATEGORIA_ID . ", "
                    . CATEGORIA_NOMBRE . ", "
					. CATEGORIA_DESCRIPCION .", "
                    . CATEGORIA_ESTADO ." "
                    . ") VALUES (?, ?, ?, true)";
				$stmt = mysqli_prepare($conn, $queryInsert);
				if (!$stmt) {
					throw new Exception("Error al preparar la consulta");
				}

				mysqli_stmt_bind_param(
					$stmt,
					'iss', // i: Entero, s: Cadena
					$nextId,
					$categoriaNombre,
					$categoriaDescripcion
				);

				// Ejecuta la consulta de inserción
				$result = mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception("Error al insertar la categoría");
				}

				$response = ["success" => true, "message" => "Categoría insertada exitosamente", "id"=> $nextId];
			}catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al insertar categoria a la base de datos'
                );
                // Devolver mensaje amigable para el usuario
                $response = ["success" => false, "message" => $userMessage, "id"=>$nextId];
            } finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
			return $response;
		}

		// Método para obtener todas las categorías activas
		public function getAllCategorias() {
			$response = [];
			try {
				// Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];

				// Obtenemos la lista de categorías
				$querySelect = "SELECT * FROM " . TB_CATEGORIA . " WHERE " . CATEGORIA_ESTADO . " != false ";
				$result = mysqli_query($conn, $querySelect);

				// Verificamos si ocurrió un error
				if (!$result) {
					throw new Exception("Ocurrió un error al obtener la información de la base de datos: " . mysqli_error($conn));
				}

				// Creamos la lista con los datos obtenidos
				$listaCategorias = [];
				while ($row = mysqli_fetch_array($result)) {
					$listaCategorias[] = [
						"ID" =>$row[CATEGORIA_ID],
						"Nombre"=>$row[CATEGORIA_NOMBRE],
						"Descripcion" => $row[CATEGORIA_DESCRIPCION],
						"Estado" => $row[CATEGORIA_ESTADO]
					];
					// array_push($listaCategorias, $currentCategoria);
				}
				$response = ["success" => true, "listaCategorias" => $listaCategorias];
			} catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al listar las categorias de la base de datos'
                );
                // Devolver mensaje amigable para el usuario
                $response = ["success" => false, "message" => $userMessage];
            }  finally {
				// Cierra la conexión y el statement
				if (isset($conn)) { mysqli_close($conn); }
			}
			return $response;
		}

		// Método para obtener categorías con paginación
		public function getPaginatedCategorias($page, $size, $sort = null) {
			$response = [];
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
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];

				// Consultar el total de registros
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_CATEGORIA . " WHERE " . CATEGORIA_ESTADO . " != false";
                $totalResult = mysqli_query($conn, $queryTotalCount);
                if (!$totalResult) {
                    throw new Exception("Error al obtener el conteo total de registros: " . mysqli_error($conn));
                }
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = (int)$totalRow['total'];
                $totalPages = ceil($totalRecords / $size);

				// Construir la consulta SQL para paginación
                $querySelect = "SELECT * FROM " . TB_CATEGORIA . " WHERE " . CATEGORIA_ESTADO . " != false ";

				// Añadir la cláusula de ordenamiento si se proporciona
                if ($sort) {
                    $querySelect .= " ORDER BY " . $sort;
                }

				// Añadir la cláusula de límite y desplazamiento
                $querySelect .= " LIMIT ? OFFSET ?";

				$stmt = mysqli_prepare($conn, $querySelect);
				if (!$stmt) {
					throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
				}

				// Asignar los parámetros de límite y desplazamiento
				mysqli_stmt_bind_param($stmt, 'ii', $size, $offset);
				mysqli_stmt_execute($stmt);
				$result = mysqli_stmt_get_result($stmt);

				// Verificamos si ocurrió un error
				if (!$result) {
					throw new Exception("Error al ejecutar la consulta de paginación: " . mysqli_error($conn));
				}

				// Creamos la lista con los datos obtenidos
				$listaCategorias = [];
				while ($row = mysqli_fetch_assoc($result)) {
					$listaCategorias[] = [
						"ID" =>$row[CATEGORIA_ID],
						"Nombre"=>$row[CATEGORIA_NOMBRE],
						"Descripcion" => $row[CATEGORIA_DESCRIPCION],
						"Estado" => $row[CATEGORIA_ESTADO]
					];
					// array_push($listaCategorias, $currentCategoria);
				}

				$response = [
					"success" => true,
					"page" => $page,
					"size" => $size,
					"totalPages" => $totalPages,
					"totalRecords" => $totalRecords,
					"listaCategorias" => $listaCategorias
				];
			}catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al listar las categorias de la base de datos'
                );
                // Devolver mensaje amigable para el usuario
                $response = ["success" => false, "message" => $userMessage];
            }  finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
			return $response;
		}

		// Método para actualizar una categoría existente
		public function updateCategoria($categoria) {
			$response = [];
			try {
				$categoriaID = $categoria->getCategoriaID();
				$categoriaNombre = $categoria->getCategoriaNombre();
				$categoriaDescripcion = $categoria->getCategoriaDescripcion();

				// Verifica que las propiedades no estén vacías
				if (empty($categoriaNombre)) {
					throw new Exception("El nombre de la categoría está vacío");
				}

				// Verifica si la categoría ya existe
				$check = $this->categoriaExiste($categoriaID, null);
				if (!$check["success"]) {
					return $check; // Error al verificar la existencia
				}
				if (!$check["exists"]) {
					throw new Exception("No existe una categoría con el ID proporcionado");
				}
				//verificar nombre categoria
				$check = $this->nombreCategoriaExiste($categoriaNombre, $categoriaID);
				if(!$check['success']){
					return $check;
				}
				if($check['exists']){
					throw new Exception("El nombre de la categoria ya existe.");
				}

				// Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) {
					throw new Exception($result["message"]);
				}
				$conn = $result["connection"];

				// Crea la consulta SQL para actualizar la categoría
				$queryUpdate = "UPDATE " . TB_CATEGORIA . " SET "
					. CATEGORIA_NOMBRE . " = ?, "
					. CATEGORIA_DESCRIPCION . " = ?, "
					. CATEGORIA_ESTADO . " = true "
					. "WHERE " . CATEGORIA_ID . " = ?";

				$stmt = mysqli_prepare($conn, $queryUpdate);
				if (!$stmt) {
					throw new Exception("Error al preparar la consulta");
				}

				mysqli_stmt_bind_param(
					$stmt,
					'ssi', // s: Cadena, i: Entero
					$categoriaNombre,
					$categoriaDescripcion,
					$categoriaID
				);

				// Ejecuta la consulta de actualización
				$result = mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception("Error al actualizar la categoría");
				}

				$response = ["success" => true, "message" => "Categoría actualizada exitosamente"];
			}catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al actualizar la categoria a la base de datos'
                );
                // Devolver mensaje amigable para el usuario
                $response = ["success" => false, "message" => $userMessage];
            } finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
			return $response;
		}

		// Método para eliminar (desactivar) una categoría
		public function deleteCategoria($categoriaID) {
			$response = [];

			try {
				// Verifica que el ID de la categoría no esté vacío
				if (empty($categoriaID)) {
					throw new Exception("El ID de la categoría está vacío.");
				}

				// Verifica si la categoría ya existe
				$check = $this->categoriaExiste($categoriaID, null);
				if (!$check["success"]) {
					return $check; // Error al verificar la existencia
				}
				if (!$check["exists"]) {
					throw new Exception("No existe una categoría con el ID proporcionado.");
				}

				// Establece una conexión con la base de datos
				$result = $this->getConnection();
				if (!$result["success"]) { throw new Exception($result["message"]); }
				$conn = $result["connection"];

				// Crea la consulta SQL para desactivar la categoría
				$queryDelete = "UPDATE " . TB_CATEGORIA . " SET "
					. CATEGORIA_ESTADO . " = false "
					. " WHERE " . CATEGORIA_ID . " = ?";

				$stmt = mysqli_prepare($conn, $queryDelete);
				if (!$stmt) {
					throw new Exception("Error al preparar la consulta");
				}

				mysqli_stmt_bind_param(
					$stmt,
					'i', // i: Entero
					$categoriaID
				);

				// Ejecuta la consulta de eliminación
				$result = mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception("Error al eliminar la categoría");
				}

				$response = ["success" => true, "message" => "Categoría eliminada exitosamente"];
			}catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al eliminar la categoria a la base de datos'
                );
                // Devolver mensaje amigable para el usuario
                $response = ["success" => false, "message" => $userMessage];
            }finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
			return $response;
		}
	}
?>
