<?php

    include_once 'data.php';
    include __DIR__ . '/../domain/Proveedor.php';
    include __DIR__ . '/../data/proveedorTelefonoData.php';
    require_once __DIR__ . '/../utils/Utils.php';
    require_once __DIR__ . '/../utils/Variables.php';

    class ProveedorData extends Data {
        private $proveedorTelefonoData;
        // Constructor
        public function __construct() {
            parent::__construct();
            $this->proveedorTelefonoData = new  ProveedorTelefonoData();
        }

        // Función para verificar si un proveedor con el mismo nombre ya existe en la bd
        public function proveedorExiste($proveedorID = null, $proveedorNombre = null, $proveedorEmail = null) {
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
                
                // Inicializa la consulta base
                $queryCheck = "SELECT * FROM " . TB_PROVEEDOR . " WHERE ";
                $params = [];
                $types = "";
                
                if ($proveedorID !== null) {
                    // Verificar existencia por ID y que el estado no sea false
                    $queryCheck .= PROVEEDOR_ID . " = ? AND " . PROVEEDOR_ESTADO . " != false";
                    $params[] = $proveedorID;
                    $types .= 'i';
                } elseif ($proveedorNombre !== null && $proveedorEmail !== null) {
                    // Verificar existencia por nombre y email
                    $queryCheck .= PROVEEDOR_NOMBRE . " = ? OR (" . PROVEEDOR_EMAIL . " = ? AND " . PROVEEDOR_ESTADO . " != false)";
                    $params[] = $proveedorNombre;
                    $params[] = $proveedorEmail;
                    $types .= 'ss';
                } else {
                    $message = "No se proporcionaron los parámetros necesarios para verificar la existencia del proveedor";
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
                    return ["success" => true, "exists" => true];
                }
        
                return ["success" => true, "exists" => false];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al verificar la existencia del proveedor en la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function insertProveedor($proveedor){
            try {
                // Obtener los valores de las propiedades del objeto
                $proveedorNombre = $proveedor->getProveedorNombre();
                $proveedorEmail = $proveedor->getProveedorEmail();
                $telefonoNumero = $proveedor->getProveedorTelefono(); // Obtener teléfono

                // Verifica si el proveedor ya existe
                $check = $this->proveedorExiste(null, $proveedorNombre, $proveedorEmail);
                if (!$check["success"]) {
                    return $check; // Error al verificar la existencia
                }
                if ($check["exists"]) {
                    Utils::writeLog("El proveedor 'Nombre [$proveedorNombre], Correo [$proveedorEmail]' ya existe en la base de datos.", DATA_LOG_FILE);
					throw new Exception("Ya existe un proveedor con el mismo nombre o correo electrónico.");
                }
                //verificar si existe un numero por el id
                $check = $this->proveedorTelefonoData->existeProveedorTelefono(null,$telefonoNumero, false, true);
                if(!$check['success']){ 
                    return !$check; 
                }
                if(!$check["exists"]){
                    return ["success" => true, "message"=> "El numero que deseas asignar ya esta registrado por otro proveedor."]; 
                }


                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
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
                    . PROVEEDOR_ESTADO . " "
                    . ") VALUES (?, ?, ?, true)";
				$stmt = mysqli_prepare($conn, $queryInsert);
        
                mysqli_stmt_bind_param(
                    $stmt,
                    'iss', // i: Entero, s: Cadena
                    $nextId,
                    $proveedorNombre, 
                    $proveedorEmail                
                );
        
                // Ejecuta la consulta de inserción
                $result = mysqli_stmt_execute($stmt);
                $check = $this->proveedorTelefonoData->addTelefonoToProveedor($nextId, $telefonoNumero, $conn);
                if(!$check['success']){
                    return $check;
                }
                return ["success" => true, "message" => "Proveedor insertado exitosamente"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al insertar al proveedor en la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function updateProveedor($proveedor) {
            try {
                // Obtener el ID del proveedor
                $proveedorID = $proveedor->getProveedorID();

                // Obtener el Nombre y el Email del proveedor
                $proveedorNombre = $proveedor->getProveedorNombre(); 
                $proveedorEmail = $proveedor->getProveedorEmail();

                // Verifica si el proveedor ya existe
                $check = $this->proveedorExiste($proveedorID);
                if (!$check["success"]) {
                    return $check; // Error al verificar la existencia
                }
                if (!$check["exists"]) {
                    Utils::writeLog("El proveedor con 'ID [$proveedorID]' no existe en la base de datos.", DATA_LOG_FILE);
					throw new Exception("No existe ningún proveedor en la base de datos que coincida con la información proporcionada.");
                }

                // Verifica que no exista un proveedor con el mismo nombre o email
                $check = $this->proveedorExiste(null, $proveedorNombre, $proveedorEmail);
                if (!$check["success"]) {
                    return $check; // Error al verificar la existencia
                }
                if ($check["exists"]) {
                    Utils::writeLog("El proveedor 'Nombre [$proveedorNombre], Correo [$proveedorEmail]' ya existe en la base de datos.", DATA_LOG_FILE);
					throw new Exception("Ya existe un proveedor con el mismo nombre o correo electrónico.");
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
                        PROVEEDOR_ESTADO . " = true " .
                    "WHERE " . PROVEEDOR_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryUpdate);

                mysqli_stmt_bind_param(
                    $stmt,
                    'ssi', // s: Cadena, i: Entero
                    $proveedorNombre,
                    $proveedorEmail,
                    $proveedorID
                );

                // Ejecuta la consulta de actualización
                $result = mysqli_stmt_execute($stmt);

                // Devuelve el resultado de la consulta
                return ["success" => true, "message" => "Proveedor actualizado exitosamente"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al actualizar el proveedor en la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function deleteProveedor($proveedorID) {
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
        
                // Devuelve el resultado de la operación
                return ["success" => true, "message" => "Proveedor eliminado exitosamente."];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al eliminar al proveedor de la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function getAllTBProveedor() {
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

                    $Telefonos = [];

                    $resultTelefonos =  $this->proveedorTelefonoData->getTelefonosByProveedor( $row[PROVEEDOR_ID],true);

                    if($resultTelefonos['success']){
                        $telefonos = $resultTelefonos['telefonos'];
                    }

                    $listaProveedores[] = [
                        'ID' => $row[PROVEEDOR_ID],
                        'Nombre' => $row[PROVEEDOR_NOMBRE],
                        'Email' => $row[PROVEEDOR_EMAIL],
                        'Telefonos' => (!empty($telefonos))? $telefonos: 'Este proveedor no tiene telefonos registrados',            
                        'FechaISO' => Utils::formatearFecha($row[PROVEEDOR_FECHA_REGISTRO], 'Y-MM-dd'),
						'Fecha' => Utils::formatearFecha($row[PROVEEDOR_FECHA_REGISTRO]),
                        'Estado' => $row[PROVEEDOR_ESTADO]
                    ];
                }

                return ["success" => true, "listaProveedores" => $listaProveedores];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al obtener la lista de proveedores desde la base de datos'
                );

                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cerramos la conexion
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function getPaginatedProveedores($page, $size, $sort = null) {
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

                    
                    $Telefonos = [];

                    $resultTelefonos =   $this->proveedorTelefonoData->getTelefonosByProveedor( $row[PROVEEDOR_ID],true);

                    if($resultTelefonos['success']){
                        $telefonos = $resultTelefonos['telefonos'];
                    }

					$listaProveedores[] = [
						'ID' => $row[PROVEEDOR_ID],
						'Nombre' => $row[PROVEEDOR_NOMBRE],
						'Email' => $row[PROVEEDOR_EMAIL],
                        'Telefonos'=>$telefonos,                                        
						'FechaISO' => Utils::formatearFecha($row[PROVEEDOR_FECHA_REGISTRO], 'Y-MM-dd'),
						'Fecha' => Utils::formatearFecha($row[PROVEEDOR_FECHA_REGISTRO]),
						'Estado' => $row[PROVEEDOR_ESTADO]
					];
				}

				return [
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
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cerrar la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }
        
    }

?>
