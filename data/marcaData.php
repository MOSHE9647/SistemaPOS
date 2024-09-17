<?php
    require_once __DIR__ . '/../domain/Marca.php';
    require_once __DIR__ . '/../utils/Utils.php';
    require_once __DIR__ . '/../utils/Variables.php';
    require_once 'data.php';



    class MarcaData extends Data {

        // Constructor que llama al constructor de la clase base (Data)
        public function __construct() {
            parent::__construct(); // Llama al constructor de la clase Data
        }

        public function marcaExiste($marcaID = null, $marcaNombre = null) {
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                // Inicializa la consulta base
                $queryCheck = "SELECT * FROM " . TB_MARCA . " WHERE ";
                $params = [];
                $types = "";
        
                if ($marcaID !== null) {
                    // Verificar existencia por ID y que el estado no sea 0 (borrado lógico)
                    $queryCheck .= MARCA_ID . " = ? AND " . MARCA_ESTADO . " != 0";
                    $params[] = $marcaID;
                    $types .= 'i';
                } elseif ($marcaNombre !== null) {
                    // Verificar existencia por nombre
                    $queryCheck .= MARCA_NOMBRE . " = ? AND " . MARCA_ESTADO . " != 0";
                    $params[] = $marcaNombre;
                    $types .= 's';
                } else {
                    throw new Exception("Se requiere al menos un parámetro: marcaID o marcaNombre");
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

        public function insertMarca($marca) {
            try {
                // Obtener los valores de las propiedades del objeto
                $marcaNombre = $marca->getMarcaNombre();
                $marcaDescripcion = $marca->getMarcaDescripcion();
                $marcaEstado = 1; // Estado por defecto: activo (1)
        
                // Verifica que las propiedades no estén vacías
                if (empty($marcaNombre)) {
                    throw new Exception("El nombre de la marca está vacío");
                }
        
                // Verifica si la marca ya existe
                $check = $this->marcaExiste(null, $marcaNombre);
                if (!$check["success"]) {
                    return $check; // Error al verificar la existencia
                }
                if ($check["exists"]) {
                    throw new Exception("Ya existe una marca con el mismo nombre");
                }
        
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                // Obtenemos el último ID de la tabla tb_marca
                $queryGetLastId = "SELECT MAX(" . MARCA_ID . ") AS marcaID FROM " . TB_MARCA;
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
                $queryInsert = "INSERT INTO " . TB_MARCA . " ("
                    . MARCA_ID . ", "
                    . MARCA_NOMBRE . ", "
                    . MARCA_DESCRIPCION . ", "
                    . MARCA_ESTADO . ") VALUES (?, ?, ?, ?)";
        
                $stmt = mysqli_prepare($conn, $queryInsert);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta");
                }
        
                mysqli_stmt_bind_param(
                    $stmt,
                    'issi', // i: Entero, s: Cadena
                    $nextId,
                    $marcaNombre,
                    $marcaDescripcion,
                    $marcaEstado
                );
        
                // Ejecuta la consulta de inserción
                $result = mysqli_stmt_execute($stmt);
                if (!$result) {
                    throw new Exception("Error al insertar la marca");
                }
        
                return ["success" => true, "message" => "Marca insertada exitosamente", "id" => $nextId];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al insertar marca en la base de datos'
                );
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage, "id" => $nextId];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function actualizarMarca($marca) {
            // Validar que el ID de la marca exista
            if (empty($marca->getMarcaId())) {
                throw new Exception("No se encontró el registro de la marca. El ID es obligatorio para actualizar.");
            }
        
            // Validar los campos obligatorios
            if (empty($marca->getMarcaNombre())) {
                throw new Exception("El nombre de la marca no puede estar vacío. Por favor, proporcione un nombre válido.");
            }
        
            if (empty($marca->getMarcaDescripcion())) {
                throw new Exception("La descripción de la marca es obligatoria. Proporcione una descripción detallada.");
            }
        
            if (empty($marca->getMarcaEstado())) {
                throw new Exception("El estado de la marca es obligatorio. Asegúrese de especificar un estado válido.");
            }
        
            // Preparar la consulta de actualización
            $query = "UPDATE " . TB_MARCA . " 
                      SET " . MARCA_NOMBRE . " = ?, " . MARCA_DESCRIPCION . " = ?, " . MARCA_ESTADO . " = ? 
                      WHERE " . MARCA_ID . " = ?;";
        
            $conn = $this->getConnection(); // Obtiene la conexión a la base de datos
            $stmt = $conn['connection']->prepare($query); // Prepara la consulta SQL
            if (!$stmt) {
                throw new Exception("Error preparando la consulta: " . $conn['connection']->error);
            }
        
            // Bind de los parámetros (nombre, descripción, estado, id)
            $stmt->bind_param("sssi", $marca->getMarcaNombre(), $marca->getMarcaDescripcion(), $marca->getMarcaEstado(), $marca->getMarcaId());
        
            // Ejecutar la sentencia
            if (!$stmt->execute()) {
                throw new Exception("Error al intentar actualizar la marca: " . $stmt->error);
            }
        
            // Verificar si se actualizó algún registro
            if ($stmt->affected_rows == 0) {
                throw new Exception("No se encontró ningún registro con el ID proporcionado. No se realizó ninguna actualización.");
            }
        
            // Cerrar la sentencia
            $stmt->close();
        
            return true; // Retornar true si la actualización fue exitosa
        }
        
    
        // Método para generar manualmente el siguiente ID
        /*private function generarNuevoIdMarca() {
            $conn = $this->getConnection(); // Obtiene la conexión a la base de datos
            $query = "SELECT MAX(" . MARCA_ID . ") AS maxId FROM " . TB_MARCA . ";";
            $result = $conn['connection']->query($query); // Ejecuta la consulta directamente
    
            if ($result && $row = $result->fetch_assoc()) {
                $nuevoId = $row['maxId'] + 1; // Sumar 1 al máximo ID
            } else {
                $nuevoId = 1; // Si no hay registros, el ID será 1
            }
    
            return $nuevoId;
        }*/
        // Método para insertar una nueva marca
       
        public function eliminarMarca($marcaId) {
            // Validar que el ID de la marca exista
            if (empty($marcaId)) {
                throw new Exception("No se puede eliminar el registro. El ID de la marca es obligatorio.");
            }
        
            // Preparar la consulta para realizar el borrado lógico (cambiar el estado)
            $query = "UPDATE " . TB_MARCA . " 
                      SET " . MARCA_ESTADO . " = 0 
                      WHERE " . MARCA_ID . " = ?;";
        
            $conn = $this->getConnection(); // Obtener la conexión a la base de datos
            $stmt = $conn['connection']->prepare($query); // Prepara la consulta SQL
            if (!$stmt) {
                throw new Exception("Error preparando la consulta para eliminar: " . $conn['connection']->error);
            }
        
            // Bind del parámetro (ID de la marca)
            $stmt->bind_param("i", $marcaId);
        
            // Ejecutar la sentencia
            if (!$stmt->execute()) {
                throw new Exception("Error al intentar eliminar la marca: " . $stmt->error);
            }
        
            // Verificar si se actualizó algún registro
            if ($stmt->affected_rows == 0) {
                throw new Exception("No se encontró ningún registro con el ID proporcionado. No se realizó ninguna eliminación.");
            }
        
            // Cerrar la sentencia
            $stmt->close();
        
            return true; // Retorna true si el borrado lógico fue exitoso
        }
        
        public function getPaginatedMarcas($page, $size, $sort = null) {
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
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_MARCA . " WHERE " . MARCA_ESTADO . " != 0";
                $totalResult = mysqli_query($conn, $queryTotalCount);
                if (!$totalResult) {
                    throw new Exception("Error al obtener el conteo total de registros: " . mysqli_error($conn));
                }
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = (int)$totalRow['total'];
                $totalPages = ceil($totalRecords / $size);
        
                // Construir la consulta SQL para paginación
                $querySelect = "SELECT * FROM " . TB_MARCA . " WHERE " . MARCA_ESTADO . " != 0 ";
        
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
                $listaMarcas = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $listaMarcas[] = [
                        "ID" => $row[MARCA_ID],
                        "Nombre" => $row[MARCA_NOMBRE],
                        "Descripcion" => $row[MARCA_DESCRIPCION],
                        "Estado" => $row[MARCA_ESTADO]
                    ];
                }
        
                return [
                    "success" => true,
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "listaMarcas" => $listaMarcas
                ];
        
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al listar las marcas de la base de datos'
                );
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }

            
        }

        public function obtenerListaMarcas() {
            try {
                // Establece la conexión a la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                // Preparar la consulta SQL para obtener todas las marcas activas
                $query = "SELECT * FROM " . TB_MARCA . " WHERE " . MARCA_ESTADO . " != 0";
                $stmt = mysqli_prepare($conn, $query);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }
        
                // Ejecutar la consulta
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
        
                // Verificar si ocurrió un error
                if (!$result) {
                    throw new Exception("Error al ejecutar la consulta: " . mysqli_error($conn));
                }
        
                // Crear la lista de marcas
                $listaMarcas = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $listaMarcas[] = [
                        "ID" => $row[MARCA_ID],
                        "Nombre" => $row[MARCA_NOMBRE],
                        "Descripcion" => $row[MARCA_DESCRIPCION],
                        "Estado" => $row[MARCA_ESTADO]
                    ];
                }
        
                // Retornar la lista de marcas
                return ["success" => true, "listaMarcas" => $listaMarcas];
        
            } catch (Exception $e) {
                // Manejo de errores
                $userMessage = $this->handleMysqlError(
                    $e->getCode(),
                    $e->getMessage(),
                    'Error al obtener la lista de marcas'
                );
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cerrar el statement y la conexión
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }
        
        
    }


?>
