<?php

    require_once 'data.php';
    require_once __DIR__ . '/../domain/Proveedor.php';
    require_once __DIR__ . '/../data/proveedorTelefonoData.php';
    require_once __DIR__ . '/../data/proveedorDireccionData.php';
    require_once __DIR__ . '/../data/categoriaData.php';
    require_once __DIR__ . '/../utils/Utils.php';
    require_once __DIR__ . '/../utils/Variables.php';

    class ProveedorData extends Data {
        private $proveedorTelefonoData;
        private $proveedorDireccionData;
        private $categoriaData;
        // Constructor
        public function __construct() {
            parent::__construct();
            $this->proveedorTelefonoData = new  ProveedorTelefonoData();
            $this->proveedorDireccionData = new ProveedorDireccionData();
            $this->categoriaData = new CategoriaData();
        }

        // Función para verificar si un proveedor con el mismo nombre ya existe en la bd
        public function proveedorExiste($proveedorID = null, $proveedorNombre = null, $proveedorEmail = null, $update = false) {
            $response  = [];//para retornar el resultado y asi no se salte el cierre de la conexion
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
                
                // Inicializa la consulta base
                $queryCheck = "SELECT * FROM " . TB_PROVEEDOR . " WHERE ";
                $params = [];
                $types = "";
                if ($proveedorID !== null && !$update) {
                    // Verificar existencia por ID y que el estado no sea false
                    $queryCheck .= PROVEEDOR_ID . " = ? "; //" AND " . PROVEEDOR_ESTADO . " != false";
                    $params[] = $proveedorID;
                    $types .= 'i';
                } elseif ($proveedorNombre !== null && $proveedorEmail !== null && !$update) {
                    // Verificar existencia por nombre y email
                    $queryCheck .= PROVEEDOR_NOMBRE . " = ? OR (" . PROVEEDOR_EMAIL . " = ? AND " . PROVEEDOR_ESTADO . " != false)";
                    $params[] = $proveedorNombre;
                    $params[] = $proveedorEmail;
                    $types .= 'ss';
                }elseif ($proveedorNombre !== null && $proveedorEmail !== null && $update) {
                    // Verificar existencia por nombre y email
                    $queryCheck .= PROVEEDOR_NOMBRE . " = ? OR (" . PROVEEDOR_EMAIL . " = ? AND " . PROVEEDOR_ESTADO . " != false) AND ". PROVEEDOR_ID ." <> ? ";
                    $params[] = $proveedorNombre;
                    $params[] = $proveedorEmail;
                    $params[] = $proveedorID;
                    $types .= 'ssi';
                
                }else {
                    $message = "No se proporcionaron todos los parametros necesarios para verificar el proveedor.";
					Utils::writeLog("$message. Parámetros: 'proveedorID [$proveedorID]', 'proveedorNombre [$proveedorNombre]', 'proveedorEmail [$proveedorEmail]'", DATA_LOG_FILE);
					throw new Exception($message);
                }
                $stmt = mysqli_prepare($conn, $queryCheck);
                
                // Asignar los parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                // Verifica si existe algún registro con los criterios dados
                if (mysqli_num_rows($result) > 0) {
                    $response = ["success" => true, "exists" => true];
                }else{
                    $response = ["success" => true, "exists" => false];
                }
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al verificar la existencia del proveedor en la base de datos'
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
        public function nombreProveedorExiste($nombre,$proveedorID = null){
            $response  = [];//para retornar el resultado y asi no se salte el cierre de la conexion
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
                
                // Inicializa la consulta base
                $queryCheck = "SELECT * FROM " . TB_PROVEEDOR . " WHERE ";
                $params = [];
                $types = "";

                if ($nombre !== null && $proveedorID !== null) {
                    // Verificar existencia por nombre y email
                    $queryCheck .= PROVEEDOR_NOMBRE . " = ?  AND " . PROVEEDOR_ESTADO . " != false AND ". PROVEEDOR_ID . " <> ? ";
                    $params[] = $nombre;
                    $params[] = $proveedorID;
                    $types .= 'si';
                }elseif ($nombre !== null) {
                    // Verificar existencia por nombre y email
                    $queryCheck .= PROVEEDOR_NOMBRE . " = ? ";
                    $params[] = $nombre;
                    $types .= 's';  
                }else {
                    $message = "No se proporciono el nombre del proveedor.";
					Utils::writeLog("$message. Parámetros: 'Nombre'[$nombre] ", DATA_LOG_FILE);
					throw new Exception($message);
                }
                $stmt = mysqli_prepare($conn, $queryCheck);
                
                // Asignar los parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                // Verifica si existe algún registro con los criterios dados
                if (mysqli_num_rows($result) > 0) {
                 
                    if ($row = mysqli_fetch_assoc($result)) {
                        // Verificar si está inactivo (bit de estado en 0)
                        $isInactive = $row[PROVEEDOR_ESTADO] == 0;
                        $response = ["success" => true, "exists" => true, "inactive" => $isInactive, "id" => $row[PROVEEDOR_ID]];
                    }
                }else{
                    $response = ["success" => true, "exists" => false];
                }
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al verificar la existencia del nombre del proveedor en la base de datos'
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
        public function emailProveedorExiste($email, $proveedorID = null){
            $response  = [];//para retornar el resultado y asi no se salte el cierre de la conexion
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
                
                // Inicializa la consulta base
                $queryCheck = "SELECT * FROM " . TB_PROVEEDOR . " WHERE ";
                $params = [];
                $types = "";

                if ($email !== null && $proveedorID !== null) {
                    // Verificar existencia por nombre y email
                    $queryCheck .= PROVEEDOR_EMAIL . " = ?  AND " . PROVEEDOR_ESTADO . " != false AND ". PROVEEDOR_ID . " <> ? ";
                    $params[] = $email;
                    $params[] = $proveedorID;
                    $types .= 'si';
                }elseif ($email !== null) {
                    // Verificar existencia por nombre y email
                    $queryCheck .= PROVEEDOR_EMAIL . " = ? AND " . PROVEEDOR_ESTADO . " != false ";
                    $params[] = $email;
                    $types .= 's';  
                }else {
                    $message = "No se proporciono el email del proveedor.";
					Utils::writeLog("$message. Parámetros: '' ", DATA_LOG_FILE);
					throw new Exception($message);
                }
                $stmt = mysqli_prepare($conn, $queryCheck);
                
                // Asignar los parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                // Verifica si existe algún registro con los criterios dados
                if (mysqli_num_rows($result) > 0) {
                    $response = ["success" => true, "exists" => true];
                }else{
                    $response = ["success" => true, "exists" => false];
                }
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al verificar la existencia del nombre del proveedor en la base de datos'
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
        public function insertProveedor($proveedor){
            $response = [];
            try {
                // Obtener los valores de las propiedades del objeto
                $proveedorNombre = $proveedor->getProveedorNombre();
                $proveedorEmail = $proveedor->getProveedorEmail();
                $telefonoNumero = $proveedor->getProveedorTelefono();
                $direccionID = $proveedor->getProveedorDireccionId();
                $idcategoria = $proveedor->getProveedorCategoria();
                Utils::writeLog("Nombre > $proveedorNombre",UTILS_LOG_FILE);
                // Verifica si el proveedor ya existe
                // Verificacion nombre
                $check = $this->nombreProveedorExiste($proveedorNombre);
                if(!$check['success']){
                    return $check;
                }
                if($check['exists'] && $check["inactive"]){
                    return ["success"=>true, "message"=>"Hay un proveedor con el nombre [$proveedorNombre] inactivo, ¿Deseas reactivarlo?","id"=>$check['id']];
                }
                if($check['exists']){
                    throw new Exception("El nombre del proveedor ya existe.");
                }

                //verificacion email
                $check = $this->emailProveedorExiste($proveedorEmail);
                if(!$check['success']){
                    return $check;
                }
                if($check['exists']){
                    throw new Exception("El email del proveedor ya existe.");
                }
                //Verificacion categoria
                $check = $this->categoriaData->categoriaExiste($idcategoria);
                if(!$check['success']){
                    return  $check;
                }
                if(!$check["exists"]){
                    throw new Exception("La categoria a asignar no existe o no es valido.");
                }

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
        
                // Obtenemos el último ID de la tabla tbproveedor
                $queryGetLastId = "SELECT MAX(" . PROVEEDOR_ID . ") AS proveedorID FROM " . TB_PROVEEDOR;
                $idCont = mysqli_query($conn, $queryGetLastId);
                $nextId = 1;
        
                // Calcula el siguiente ID para la nueva entrada
                if ($row = mysqli_fetch_row($idCont)) {
                    $nextId = (int) trim($row[0]) + 1;
                }
        
                // Crea una consulta y un statement SQL para insertar el nuevo registro
                $queryInsert = "INSERT INTO " . TB_PROVEEDOR . " ("
                    . PROVEEDOR_ID . ", "
                    . PROVEEDOR_NOMBRE . ", "
                    . PROVEEDOR_EMAIL . ", "
                    . PROVEEDOR_CATEGORIA_ID . ", "
                    . PROVEEDOR_ESTADO . " "
                    . ") VALUES (?, ?, ?, ?, true)";
				$stmt = mysqli_prepare($conn, $queryInsert);
        
                mysqli_stmt_bind_param(
                    $stmt,
                    'issi', // i: Entero, s: Cadena
                    $nextId,
                    $proveedorNombre, 
                    $proveedorEmail,
                    $idcategoria                
                );
        
                // Ejecuta la consulta de inserción
                $result = mysqli_stmt_execute($stmt);
                if(!$result){
                    $response = ["success" => false, "message" => "Error al registrar el proveedor."];
                }else{
                    $response = ["success" => true, "message" => "Proveedor insertado exitosamente", "id"=>$nextId];
                }
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al insertar al proveedor en la base de datos'
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

        public function updateProveedor($proveedor) {
            $response = [];
            try {
                // Obtener el ID del proveedor
                $proveedorID = $proveedor->getProveedorID();
                // Obtener el Nombre y el Email del proveedor
                $proveedorNombre = $proveedor->getProveedorNombre(); 
                $proveedorEmail = $proveedor->getProveedorEmail();
                $idcategoria = $proveedor->getProveedorCategoria();

                // Verifica si el proveedor ya existe
                $check = $this->proveedorExiste($proveedorID);
                if (!$check["success"]) {
                    return $check; // Error al verificar la existencia
                }
                if (!$check["exists"]) {
                    Utils::writeLog("El proveedor con 'ID [$proveedorID]' no existe en la base de datos.", DATA_LOG_FILE);
					throw new Exception("No existe ningún proveedor en la base de datos que coincida con la información proporcionada.");
                }

                // Verificacion nombre
                $check = $this->nombreProveedorExiste($proveedorNombre,$proveedorID);
                if(!$check['success']){
                    return $check;
                }
                if($check['exists']){
                    throw new Exception("El nombre del proveedor ya existe.");
                }
                
                //verificacion email
                $check = $this->emailProveedorExiste($proveedorEmail,$proveedorID);
                if(!$check['success']){
                    return $check;
                }
                if($check['exists']){
                    throw new Exception("El email del proveedor ya existe.");
                }

                $check = $this->categoriaData->categoriaExiste($idcategoria);
                if(!$check['success']){
                    return  $check;
                }
                if(!$check["exists"]){
                    throw new Exception("La categoria a asignar no existe o no es valido.");
                }

                // Establece una conexion con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
                // Crea una consulta y un statement SQL para actualizar el registro
                $queryUpdate = 
                    "UPDATE " . TB_PROVEEDOR . 
                    " SET " . 
                        PROVEEDOR_NOMBRE . " = ?, " . 
                        PROVEEDOR_EMAIL . " = ?, " .
                        PROVEEDOR_CATEGORIA_ID . " = ?, " .                    
                        PROVEEDOR_ESTADO . " = true " .
                    "WHERE " . PROVEEDOR_ID . " = ? ";
                $stmt = mysqli_prepare($conn, $queryUpdate);

                mysqli_stmt_bind_param(
                    $stmt,
                    'ssii', // s: Cadena, i: Entero
                    $proveedorNombre,
                    $proveedorEmail,
                    $idcategoria,
                    $proveedorID
                );
                // Ejecuta la consulta de actualización
                $result = mysqli_stmt_execute($stmt);
                if($result){
                    $response = ["success" => true, "message" => "Proveedor actualizado exitosamente."];
                }else{
                    $response = ["success" => false, "message" => "Error al actualizar el proveedor."];
                }
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError( $e->getCode(),  $e->getMessage(),
                                        'Error al actualizar el proveedor en la base de datos'
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

        public function deleteProveedor($proveedorID) {
            $response = [];
            try {
                // Verificar si existe el ID y que el Estado no sea false
                $check = $this->proveedorExiste($proveedorID);
                if (!$check["success"]) {
                    return $check; // Error al verificar la existencia
                }
                if (!$check["exists"]) {
                    Utils::writeLog("El proveedor con ID '[$proveedorID]' no existe en la base de datos.", DATA_LOG_FILE);
					throw new Exception("No existe ningún proveedor en la base de datos que coincida con la información proporcionada.");
                }

                // Establece una conexion con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                // Crea una consulta y un statement SQL para eliminar el registro (borrado logico)
                $queryDelete = "UPDATE " . TB_PROVEEDOR . " SET " . PROVEEDOR_ESTADO . " = false WHERE " . PROVEEDOR_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryDelete);
                mysqli_stmt_bind_param($stmt, 'i', $proveedorID);
        
                // Ejecuta la consulta de eliminación
                $result = mysqli_stmt_execute($stmt);
                if($result){
                    $response =  ["success" => true, "message" => "Proveedor eliminado exitosamente."];
                }else{
                    $response =  ["success" => false, "message" => "Error al eliminar al proveedor."];
                }
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError( $e->getCode(), $e->getMessage(), 
                                'Error al eliminar al proveedor de la base de datos'
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

        public function getAllTBProveedor() {
            $response = [];
            try {
                // Establece una conexion con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];

                // Obtenemos la lista de Proveedores
                $querySelect = "SELECT * FROM " . TB_PROVEEDOR . " WHERE " . PROVEEDOR_ESTADO . " != false ";
                $result = mysqli_query($conn, $querySelect);

                // Creamos la lista con los datos obtenidos
                $listaProveedores = [];
                while ($row = mysqli_fetch_assoc($result)) {

                    $telefonos =[];
                    $direcciones =[];


                    $resultTelefonos =  $this->proveedorTelefonoData->getTelefonosByProveedor( $row[PROVEEDOR_ID],true);
                    if($resultTelefonos['success']){
                        $telefonos = (array_key_exists('telefonos',$resultTelefonos))?$resultTelefonos['telefonos']:[];
                    }

                    $resultDireccion = $this->proveedorDireccionData->getDireccionesByProveedor($row[PROVEEDOR_ID],true);
                    if($resultDireccion["success"]){
                        $direcciones = (array_key_exists('direcciones',$resultDireccion))?$resultDireccion['direcciones']:[];
                    }
                    $listaProveedores[] = [
                        'ID' => $row[PROVEEDOR_ID],
                        'Nombre' => $row[PROVEEDOR_NOMBRE],
                        'Email' => $row[PROVEEDOR_EMAIL],
                        'Telefonos' => (!empty($telefonos))? $telefonos: 'Este proveedor no tiene telefonos registrados',
                        'Direcciones'=>(!empty($direcciones))?$direcciones : 'Este proveedor no posee direcciones',               
                        'FechaISO' => Utils::formatearFecha($row[PROVEEDOR_FECHA_CREACION], 'Y-MM-dd'),
						'Fecha' => Utils::formatearFecha($row[PROVEEDOR_FECHA_CREACION]),
                        'FechaModificacionISO'=>Utils::formatearFecha($row[PROVEEDOR_FECHA_MODIFICACION],'Y-MM-dd'),
                        'FechaModificacion'=>Utils::formatearFecha($row[PROVEEDOR_FECHA_MODIFICACION]),
                        'Estado' => $row[PROVEEDOR_ESTADO]
                    ];
                }
                $response = ["success" => true, "listaProveedores" => $listaProveedores];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError( $e->getCode(),  $e->getMessage(),
                    'Error al obtener la lista de proveedores desde la base de datos'
                );
                // Devolver mensaje amigable para el usuario
                $response = ["success" => false, "message" => $userMessage];
            } finally {
                // Cerramos la conexion
                if (isset($conn)) { mysqli_close($conn); }
            }
            return $response;
        }

        public function getPaginatedProveedores($page, $size, $sort = null) {
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
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_PROVEEDOR . " WHERE " . PROVEEDOR_ESTADO . " != false";
                $totalResult = mysqli_query($conn, $queryTotalCount);
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = (int) $totalRow['total'];
                $totalPages = ceil($totalRecords / $size);

				// Construir la consulta SQL para paginación
                $querySelect = "SELECT * FROM " . TB_PROVEEDOR . " WHERE " . PROVEEDOR_ESTADO . " != false ";

				// Añadir la cláusula de ordenamiento si se proporciona
                if ($sort) {
                    $querySelect .= "ORDER BY proveedor" . $sort . " ";
                }

				// Añadir la cláusula de limitación y offset
                $querySelect .= "LIMIT ? OFFSET ?";

				// Preparar la consulta y vincular los parámetros
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, "ii", $size, $offset);

				// Ejecutar la consulta
                $result = mysqli_stmt_execute($stmt);

				// Obtener el resultado
                $result = mysqli_stmt_get_result($stmt);

				$listaProveedores = [];
				while ($row = mysqli_fetch_assoc($result)) {
                    $telefonos =[];
                    $direcciones =[];
                    
                    $resultTelefonos =  $this->proveedorTelefonoData->getTelefonosByProveedor( $row[PROVEEDOR_ID],true);
                    if($resultTelefonos['success']){
                        $telefonos = (array_key_exists('telefonos',$resultTelefonos))?$resultTelefonos['telefonos']:[];
                    }

                    $resultDireccion = $this->proveedorDireccionData->getDireccionesByProveedor($row[PROVEEDOR_ID],true);
                    if($resultDireccion["success"]){
                        $direcciones = (array_key_exists('direcciones',$resultDireccion))?$resultDireccion['direcciones']:[];
                    }

					$listaProveedores[] = [
						'ID' => $row[PROVEEDOR_ID],
						'Nombre' => $row[PROVEEDOR_NOMBRE],
						'Email' => $row[PROVEEDOR_EMAIL],
                        'Telefonos'=>(!empty($telefonos))? $telefonos: 'Este proveedor no tiene telefonos registrados', 
                        'Direcciones'=>(!empty($direcciones))?$direcciones : 'Este proveedor no posee direcciones',                              
						'FechaISO' => Utils::formatearFecha($row[PROVEEDOR_FECHA_CREACION], 'Y-MM-dd'),
						'Fecha' => Utils::formatearFecha($row[PROVEEDOR_FECHA_CREACION]),
                        'FechaModificacionISO'=>Utils::formatearFecha($row[PROVEEDOR_FECHA_MODIFICACION],'Y-MM-dd'),
                        'FechaModificacion'=>Utils::formatearFecha($row[PROVEEDOR_FECHA_MODIFICACION]),
						'Estado' => $row[PROVEEDOR_ESTADO]
					];
				}

				$response = [
                    "success" => true,
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "listaProveedores" => $listaProveedores
                ];
			} catch (Exception $e) {
				// Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al obtener la lista de proveedores desde la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                $response = ["success" => false, "message" => $userMessage];
            } finally {
                // Cerrar la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
            return $response;
        }
        
    }

?>
