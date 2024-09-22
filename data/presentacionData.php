<?php

    require_once __DIR__ . '/../domain/Presentacion.php';
    require_once __DIR__ . '/../utils/Utils.php';
    require_once __DIR__ . '/../utils/Variables.php';
    require_once 'data.php';

    class PresentacionData extends Data {

        public function __construct() {
            parent::__construct(); // Llama al constructor de la clase base (Data)
        }
    
        public function presentacionExiste($presentacionID = null, $presentacionNombre = null) {
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
    
                // Inicializa la consulta base
                $queryCheck = "SELECT * FROM " . TB_PRESENTACION . " WHERE ";
                $params = [];
                $types = "";
    
                if ($presentacionID !== null) {
                    // Verificar existencia por ID y que el estado no sea 0 (borrado lógico)
                    $queryCheck .= PRESENTACION_ID . " = ? AND " . PRESENTACION_ESTADO . " != 0";
                    $params[] = $presentacionID;
                    $types .= 'i';
                } elseif ($presentacionNombre !== null) {
                    // Verificar existencia por nombre
                    $queryCheck .= PRESENTACION_NOMBRE . " = ? AND " . PRESENTACION_ESTADO . " != 0";
                    $params[] = $presentacionNombre;
                    $types .= 's';
                } else {
                    throw new Exception("Se requiere al menos un parámetro: presentacionID o presentacionNombre");
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

        public function insertPresentacion($presentacion) {
            try {
                // Obtener los valores de las propiedades del objeto
                $presentacionNombre = $presentacion->getPresentacionNombre();
                $presentacionDescripcion = $presentacion->getPresentacionDescripcion();
                $presentacionEstado = 1; // Estado por defecto: activo (1)
        
                // Verifica que las propiedades no estén vacías
                if (empty($presentacionNombre)) {
                    throw new Exception("El nombre de la presentación está vacío");
                }
        
                // Verifica si la presentación ya existe
                $check = $this->presentacionExiste(null, $presentacionNombre);
                if (!$check["success"]) {
                    return $check; // Error al verificar la existencia
                }
                if ($check["exists"]) {
                    throw new Exception("Ya existe una presentación con el mismo nombre");
                }
        
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                // Obtenemos el último ID de la tabla tb_presentacion
                $queryGetLastId = "SELECT MAX(" . PRESENTACION_ID . ") AS presentacionID FROM " . TB_PRESENTACION;
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
                $queryInsert = "INSERT INTO " . TB_PRESENTACION . " ("
                    . PRESENTACION_ID . ", "
                    . PRESENTACION_NOMBRE . ", "
                    . PRESENTACION_DESCRIPCION . ", "
                    . PRESENTACION_ESTADO . ") VALUES (?, ?, ?, ?)";
        
                $stmt = mysqli_prepare($conn, $queryInsert);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta");
                }
        
                mysqli_stmt_bind_param(
                    $stmt,
                    'issi', // i: Entero, s: Cadena
                    $nextId,
                    $presentacionNombre,
                    $presentacionDescripcion,
                    $presentacionEstado
                );
        
                // Ejecuta la consulta de inserción
                $result = mysqli_stmt_execute($stmt);
                if (!$result) {
                    throw new Exception("Error al insertar la presentación");
                }
        
                return ["success" => true, "message" => "Presentación insertada exitosamente", "id" => $nextId];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al insertar presentación en la base de datos'
                );
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage, "id" => $nextId];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function actualizarPresentacion($presentacion) {
            // Validar que el ID de la presentación exista
            if (empty($presentacion->getPresentacionId())) {
                throw new Exception("No se encontró el registro de la presentación. El ID es obligatorio para actualizar.");
            }
        
            // Validar los campos obligatorios
            if (empty($presentacion->getPresentacionNombre())) {
                throw new Exception("El nombre de la presentación no puede estar vacío. Por favor, proporcione un nombre válido.");
            }
        
            if (empty($presentacion->getPresentacionDescripcion())) {
                throw new Exception("La descripción de la presentación es obligatoria. Proporcione una descripción detallada.");
            }
        
            if (empty($presentacion->getPresentacionEstado())) {
                throw new Exception("El estado de la presentación es obligatorio. Asegúrese de especificar un estado válido.");
            }
        
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"]; // Extraer el objeto de conexión del array
        
                // Preparar la consulta de actualización
                $query = "UPDATE " . TB_PRESENTACION . " 
                          SET " . PRESENTACION_NOMBRE . " = ?, " . PRESENTACION_DESCRIPCION . " = ?, " . PRESENTACION_ESTADO . " = ? 
                          WHERE " . PRESENTACION_ID . " = ?;";
        
                $stmt = $conn->prepare($query); // Prepara la consulta SQL
                if (!$stmt) {
                    throw new Exception("Error preparando la consulta: " . $conn->error);
                }
        
                // Bind de los parámetros (nombre, descripción, estado, id)
                $stmt->bind_param("sssi", 
                    $presentacion->getPresentacionNombre(), 
                    $presentacion->getPresentacionDescripcion(), 
                    $presentacion->getPresentacionEstado(), 
                    $presentacion->getPresentacionId());
        
                // Ejecutar la sentencia
                if (!$stmt->execute()) {
                    throw new Exception("Error al intentar actualizar la presentación: " . $stmt->error);
                }
        
                // Verificar si se actualizó algún registro
                if ($stmt->affected_rows == 0) {
                    throw new Exception("No se encontró ningún registro con el ID proporcionado. No se realizó ninguna actualización.");
                }
        
                // Cerrar la sentencia
                $stmt->close();
        
                return ["success" => true, "message" => "Presentación actualizada exitosamente"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
                // Cerrar la conexión correctamente
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function eliminarPresentacion($presentacionId) {
            // Validar que el ID de la presentación exista
            if (empty($presentacionId)) {
                throw new Exception("No se puede eliminar el registro. El ID de la presentación es obligatorio.");
            }
        
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"]; // Extraer el objeto de conexión del array
        
                // Preparar la consulta para realizar el borrado lógico (cambiar el estado)
                $query = "UPDATE " . TB_PRESENTACION . " 
                          SET " . PRESENTACION_ESTADO . " = 0 
                          WHERE " . PRESENTACION_ID . " = ?;";
        
                $stmt = $conn->prepare($query); // Prepara la consulta SQL
                if (!$stmt) {
                    throw new Exception("Error preparando la consulta para eliminar: " . $conn->error);
                }
        
                // Bind del parámetro (ID de la presentación)
                $stmt->bind_param("i", $presentacionId);
        
                // Ejecutar la sentencia
                if (!$stmt->execute()) {
                    throw new Exception("Error al intentar eliminar la presentación: " . $stmt->error);
                }
        
                // Verificar si se actualizó algún registro
                if ($stmt->affected_rows == 0) {
                    throw new Exception("No se encontró ningún registro con el ID proporcionado. No se realizó ninguna eliminación.");
                }
        
                // Cerrar la sentencia
                $stmt->close();
        
                return ["success" => true, "message" => "Presentación eliminada exitosamente"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
                // Cerrar la conexión correctamente
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function getAllTBProductoPresentacion() {
            $response = [];
            try {
                // Establece una conexion con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
    
                // Construir la consulta SQL con joins para obtener nombres en lugar de IDs
            $querySelect = "SELECT " . PRESENTACION_ID . ", " . PRESENTACION_NOMBRE . " FROM " . TB_PRESENTACION . " WHERE " . PRESENTACION_ESTADO . " !=false";
            $result = mysqli_query($conn, $querySelect);
    
               // Crear la lista con los datos obtenidos
            $listaProductoPresentacions = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $listaProductoPresentacions []= [
                    "ID" => $row[PRESENTACION_ID],
                    "PresentacionNombre" =>  $row[PRESENTACION_NOMBRE],
                ];
            }
    
                return ["success" => true, "listaProductoPresentacions" => $listaProductoPresentacions];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al obtener la lista de presentacion desde la base de datos'
                );
                // Devolver mensaje amigable para el usuario
                $response = ["success" => false, "message" => $userMessage];
            } finally {
                // Cerramos la conexion
                if (isset($conn)) { mysqli_close($conn); }
            }
            return $response;
        }

        public function getPaginatedPresentaciones($page, $size, $sort = null) {
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
                $conn = $result["connection"]; // Extraer el objeto de conexión del array
        
                // Consultar el total de registros
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_PRESENTACION . " WHERE " . PRESENTACION_ESTADO . " != 0";
                $totalResult = mysqli_query($conn, $queryTotalCount);
                if (!$totalResult) {
                    throw new Exception("Error al obtener el conteo total de registros: " . mysqli_error($conn));
                }
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = (int)$totalRow['total'];
                $totalPages = ceil($totalRecords / $size);
        
                // Construir la consulta SQL para paginación
                $querySelect = "SELECT * FROM " . TB_PRESENTACION . " WHERE " . PRESENTACION_ESTADO . " != 0 ";
        
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
                $listaPresentaciones = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $listaPresentaciones[] = [
                        "ID" => $row[PRESENTACION_ID],
                        "Nombre" => $row[PRESENTACION_NOMBRE],
                        "Descripcion" => $row[PRESENTACION_DESCRIPCION],
                        "Estado" => $row[PRESENTACION_ESTADO]
                    ];
                }
        
                return [
                    "success" => true,
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "listaPresentaciones" => $listaPresentaciones
                ];
        
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }
        
        
        public function obtenerListaPresentaciones() {
            try {
                // Establece la conexión a la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"]; // Extraer el objeto de conexión del array
        
                // Preparar la consulta SQL para obtener todas las presentaciones activas
                $query = "SELECT * FROM " . TB_PRESENTACION . " WHERE " . PRESENTACION_ESTADO . " != 0";
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
        
                // Crear la lista de presentaciones
                $listaPresentaciones = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $listaPresentaciones[] = [
                        "ID" => $row[PRESENTACION_ID],
                        "Nombre" => $row[PRESENTACION_NOMBRE],
                        "Descripcion" => $row[PRESENTACION_DESCRIPCION],
                        "Estado" => $row[PRESENTACION_ESTADO]
                    ];
                }
        
                // Retornar la lista de presentaciones
                return ["success" => true, "listaPresentaciones" => $listaPresentaciones];
        
            } catch (Exception $e) {
                // Manejo de errores
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
                // Cerrar el statement y la conexión
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }
        
        
    }

?>
