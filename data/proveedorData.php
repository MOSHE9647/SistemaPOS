<?php

    include_once 'data.php';
    include __DIR__ . '/../domain/Proveedor.php';
    require_once __DIR__ . '/../utils/Utils.php';
    require_once __DIR__ . '/../utils/Variables.php';

    class ProveedorData extends Data {

        // Constructor
        public function __construct() {
            parent::__construct();
        }

        // Función para verificar si un proveedor con el mismo nombre ya existe en la bd
        public function proveedorExiste($proveedorID = null, $proveedorNombre = null, $proveedorFecha = null) {
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
                } elseif ($proveedorNombre !== null && $proveedorFecha !== null) {
                    // Verificar existencia por nombre y email
                    $queryCheck .= PROVEEDOR_NOMBRE . " = ? AND (" . PROVEEDOR_FECHA_REGISTRO . " = ? OR " . PROVEEDOR_ESTADO . " != false)";
                    $params[] = $proveedorNombre;
                    $params[] = $proveedorFecha;
                    $types .= 'ss';
                } else {
                    throw new Exception("Se requiere al menos un parámetro: proveedorID o proveedorNombre y proveedorEmail");
                }
                
                $stmt = mysqli_prepare($conn, $queryCheck);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }
                
                // Asignar los parámetros a la consulta
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

        public function insertProveedor($proveedor) {
            try {
                // Obtener los valores de las propiedades del objeto
                $proveedorNombre = $proveedor->getProveedorNombre();
                $proveedorEmail = $proveedor->getProveedorEmail();
                $proveedorEstado = $proveedor->getProveedorEstado();
                $proveedorTipo = $proveedor->getProveedorTipo(); 
                $proveedorFechaRegistro = $proveedor->getProveedorFechaRegistro();
               
        
                // Verifica que las propiedades no estén vacías
                if (empty($proveedorNombre)) {
                    throw new Exception("El nombre del proveedor está vacío");
                }
                if (empty($proveedorEmail) || !filter_var($proveedorEmail, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("El correo electrónico del proveedor está vacío o no es válido");
                }
                  if (empty($proveedorFechaRegistro) || !Utils::validarFecha($proveedorFechaRegistro)) {
                    throw new Exception("La fecha de registro del proveedor está vacía o no es válida");
                }
                if ($proveedorEstado === null || empty($proveedorEstado)) {
                    throw new Exception("El estado del proveedor no puede estar vacío");
                }
                
        
                // Verificar si la fecha de vigencia es menor o igual a la de hoy
				if (!Utils::fechaMenorOIgualAHoy($proveedorFechaRegistro)) {
					throw new Exception("La fecha de vigencia debe ser menor o igual a la fecha actual");
				}

                // Verifica si el proveedor ya existe
                $check = $this->proveedorExiste(null, $proveedorNombre, $proveedorFechaRegistro);
                if (!$check["success"]) {
                    return $check; // Error al verificar la existencia
                }
                if ($check["exists"]) {
                    throw new Exception("Ya existe un proveedor con el mismo nombre o fecha");
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
                if (!$idCont) {
                    throw new Exception("Error al ejecutar la consulta");
                }
                $nextId = 1;
        
                // Calcula el siguiente ID para la nueva entrada
                if ($row = mysqli_fetch_row($idCont)) {
                    $nextId = (int) trim($row[0]) + 1;
                }
        
                // Crea una consulta y un statement SQL para insertar el nuevo registro
                $queryInsert = "INSERT INTO " . TB_PROVEEDOR . " VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $queryInsert);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta");
                }
        
                mysqli_stmt_bind_param(
                    $stmt,
                    'isssss', // i: Entero, s: Cadena
                    $nextId,
                    $proveedorNombre, 
                    $proveedorEmail,
                    $proveedorTipo,
                    $proveedorEstado,
                    $proveedorFechaRegistro                   
                );
        
                // Ejecuta la consulta de inserción
                $result = mysqli_stmt_execute($stmt);
                if (!$result) {
                    throw new Exception("Error al insertar el proveedor");
                }
        
                return ["success" => true, "message" => "Proveedor insertado exitosamente"];
            } catch (Exception $e) {
                // Devuelve el mensaje de error
                return ["success" => false, "message" => $e->getMessage()];
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

                // Verificamos si ocurrió un error
                if (!$result) {
                    throw new Exception("Ocurrió un error al ejecutar la consulta");
                }

                // Creamos la lista con los datos obtenidos
                $listaProveedores = [];
                while ($row = mysqli_fetch_array($result)) {
                    $currentProveedor = new Proveedor(
                        $row[PROVEEDOR_NOMBRE],
                        $row[PROVEEDOR_EMAIL],
                        $row[PROVEEDOR_FECHA_REGISTRO],
                        $row[PROVEEDOR_ID],
                        $row[PROVEEDOR_TIPO],                                             
                        $row[PROVEEDOR_ESTADO]
                    );
                    array_push($listaProveedores, $currentProveedor);
                }

                return ["success" => true, "listaProveedores" => $listaProveedores];
            } catch (Exception $e) {
                // Devuelve el mensaje de error
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
                // Cerramos la conexion
                if (isset($conn)) {
                    mysqli_close($conn);
                }
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
                if (!$totalResult) {
                    throw new Exception("Error al obtener el conteo total de registros: " . mysqli_error($conn));
                }
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = (int)$totalRow['total'];
                $totalPages = ceil($totalRecords / $size);

				// Construir la consulta SQL para paginación
                $querySelect = "SELECT * FROM " . TB_PROVEEDOR . " WHERE " . PROVEEDOR_ESTADO . " != false ";

				// Añadir la cláusula de ordenamiento si se proporciona
                if ($sort) {
                    $querySelect .= "ORDER BY proveedor" . $sort . " ";
                }

				// Añadir la cláusula de limitación y offset
                $querySelect .= "LIMIT ? OFFSET ?";

				// Preparar la consulta
                $stmt = mysqli_prepare($conn, $querySelect);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }
        
                // Vincular los parámetros
                mysqli_stmt_bind_param($stmt, "ii", $size, $offset);

				// Ejecutar la consulta
                $result = mysqli_stmt_execute($stmt);
                if (!$result) {
                    throw new Exception("Error al ejecutar la consulta: " . mysqli_error($conn));
                }

				// Obtener el resultado
                $result = mysqli_stmt_get_result($stmt);
                if (!$result) {
                    throw new Exception("Error al obtener el resultado: " . mysqli_error($conn));
                }

				$listaProveedores = [];
				while ($row = mysqli_fetch_assoc($result)) {
					$listaProveedores[] = [
						'ID' => $row[PROVEEDOR_ID],
						'Nombre' => $row[PROVEEDOR_NOMBRE],
						'Email' => $row[PROVEEDOR_EMAIL],
						'Tipo' => $row[PROVEEDOR_TIPO],                                             
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
				// Devolver el mensaje de error
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
                // Cerrar la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function updateProveedor($proveedor) {
            try {
                // Obtener los valores de las propiedades del objeto
                $proveedorID = $proveedor->getProveedorID();
                $proveedorNombre = $proveedor->getProveedorNombre(); 
                $proveedorEmail = $proveedor->getProveedorEmail();
                $proveedorTipo = $proveedor->getProveedorTipo();             
                $proveedorFechaRegistro = $proveedor->getProveedorFechaRegistro();
                $proveedorEstado = $proveedor->getProveedorEstado();
        
                // Verifica que las propiedades no estén vacías
                if (empty($proveedorID) || !is_numeric($proveedorID)) {
                    throw new Exception("No se encontró el ID del Proveedor o este no es válido");
                }
                if (empty($proveedorNombre)) {
                    throw new Exception("El nombre del proveedor está vacío");
                }
                
               // if (empty($proveedorEmail) || !filter_var($proveedorEmail, FILTER_VALIDATE_EMAIL)) {
                    //throw new Exception("El correo electrónico del proveedor está vacío o no es válido");
                //}
                if (empty($proveedorFechaRegistro) || !Utils::validarFecha($proveedorFechaRegistro)) {
                    throw new Exception("La fecha de registro del proveedor está vacía o no es válida");
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
                        PROVEEDOR_TIPO . " = ?, " .                      
                        PROVEEDOR_ESTADO . " = ?, " .
                        PROVEEDOR_FECHA_REGISTRO . " = ? " . 
                    "WHERE " . PROVEEDOR_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryUpdate);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta");
                }

                mysqli_stmt_bind_param(
                    $stmt,
                    'sssssi', // s: Cadena, i: Entero
                    $proveedorNombre,
                    $proveedorEmail,
                    $proveedorTipo,
                    $proveedorEstado, 
                    $proveedorFechaRegistro,
                    $proveedorID
                );

                // Ejecuta la consulta de actualización
                $result = mysqli_stmt_execute($stmt);
                if (!$result) {
                    throw new Exception("Error al actualizar el proveedor");
                }

                // Devuelve el resultado de la consulta
                return ["success" => true, "message" => "Proveedor actualizado exitosamente"];
            } catch (Exception $e) {
                // Devuelve el mensaje de error
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function deleteProveedor($proveedorID) {
            try {
                // Verifica que el ID del proveedor no esté vacío y sea numérico
                if (empty($proveedorID) || !is_numeric($proveedorID)) {
                    throw new Exception("ID de proveedor inválido.");
                }
                
                // Verificar si existe el ID y que el Estado no sea false
                $check = $this->proveedorExiste($proveedorID);
                if (!$check["success"]) {
                    return $check; // Error al verificar la existencia
                }
                if (!$check["exists"]) {
                    throw new Exception("No se encontró un proveedor con el ID [" . $proveedorID . "]");
                }
        
                // Establece una conexion con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                // Crea una consulta y un statement SQL para eliminar el registro (borrado logico)
                $queryDelete = "UPDATE " . TB_PROVEEDOR . " SET " . PROVEEDOR_ESTADO . " = ? WHERE " . PROVEEDOR_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryDelete);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta de eliminación.");
                }
        
                $proveedorEstado = false; //<- Para el borrado lógico
                mysqli_stmt_bind_param($stmt, 'ii', $proveedorEstado, $proveedorID);
        
                // Ejecuta la consulta de eliminación
                $result = mysqli_stmt_execute($stmt);
                if (!$result) {
                    throw new Exception("Error al eliminar el proveedor.");
                }
        
                // Devuelve el resultado de la operación
                return ["success" => true, "message" => "Proveedor eliminado exitosamente."];
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
