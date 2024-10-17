<?php
    require_once dirname(__DIR__, 1) . '/data/data.php';
    require_once __DIR__ . '/../domain/Producto.php';
    require_once __DIR__ . '/../utils/Variables.php';
    require_once __DIR__ . '/../utils/Variables.php';
    class ProveedorProducto extends Data{

        private $className;
        public function __construct(){
            $this->className = get_class($this);
            parent::__construct();
        }
        

        public function existeProveedorProducto($proveedorID = null, $productoID = null, $tbProveedor = false, $tbProducto = false){
            $conn = null; $stmt = null;
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Determina la tabla y construye la consulta base
                $tableName = TB_PROVEEDOR_PRODUCTO; 
                if ($tbProveedor) {
                    $tableName = TB_PROVEEDOR;
                } elseif ($tbProducto) {
                    $tableName = TB_PRODUCTO;
                }

                $queryCheck = "SELECT 1 FROM $tableName WHERE ";
                $params = [];
                $types = "";

                if ($proveedorID !== null && $productoID !== null) {
                    // Si ambos parámetros están presentes, verificar la relación entre proveedor y producto
                    $queryCheck .= PROVEEDOR_ID . " = ? AND " .  PRODUCTO_ID . " = ? AND " . PROVEEDOR_PRODUCTO_ESTADO . " != FALSE";
                    $params = [$proveedorID, $productoID];
                    $types = "ii";
                    Utils::writeLog("Consulta con proveedor y producto: " . $queryCheck, UTILS_LOG_FILE);
                } elseif ($proveedorID !== null) {
                    // Si solo hay proveedorID, buscar solo el proveedor
                    $estadoCampo = $tbProveedor ? PROVEEDOR_ESTADO : PROVEEDOR_PRODUCTO_ESTADO;
                    $queryCheck .= PROVEEDOR_ID . " = ? AND $estadoCampo != FALSE";
                    $params = [$proveedorID];
                    $types = "i";
                    Utils::writeLog("Consulta solo con proveedor: " . $queryCheck, UTILS_LOG_FILE);
                } elseif ($productoID !== null) {
                    // Si solo hay productoID, buscar solo el producto
                    $estadoCampo = $tbProducto ? PRODUCTO_ESTADO : PROVEEDOR_PRODUCTO_ESTADO;
                    $queryCheck .= PRODUCTO_ID . " = ? AND $estadoCampo != FALSE";
                    $params = [$productoID];
                    $types = "i";
                    Utils::writeLog("Consulta solo con producto: " . $queryCheck, UTILS_LOG_FILE);
                } else {
                    // Si faltan parámetros, loguear y retornar error
                    $missingParamsLog = "Faltan parámetros para verificar la existencia del proveedor y/o producto: ";
                    if ($proveedorID === null) $missingParamsLog .= "proveedorID [null] ";
                    if ($productoID === null) $missingParamsLog .= "productoID [null] ";
                    Utils::writeLog($missingParamsLog, DATA_LOG_FILE, WARN_MESSAGE, $this->className);
                    return ["success" => false, "message" => "No se proporcionaron los parámetros necesarios para realizar la verificación."];
                }


                // Prepara la consulta y ejecuta la verificación
                $stmt = mysqli_prepare($conn, $queryCheck);
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                // Verifica si existe algún registro con los criterios dados
                if (mysqli_num_rows($result) > 0) {
                    return ["success" => true, "exists" => true];
                }

                // Retorna false si no se encontraron resultados
                return ["success" => true, "exists" => false, "message" => $message];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al verificar la existencia de producto y proveedor en la base de datos',
                    $this->className
                );
        
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if (isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
            }
        }

        public function existeRelacionProveedorProducto($proveedorID,$productoID, $idproductoproveedor = null){
            $response = [];
            $conn = null; $stmt = null;
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                $queryCheck = "SELECT * FROM ". TB_PROVEEDOR_PRODUCTO . " WHERE ";
                $params = [];
                $types = "";

                if($idproductoproveedor !== null){
                    $queryCheck .= PROVEEDOR_PRODUCTO_ID . " <> ? AND ";
                    $params[] = $idproductoproveedor;
                    $types .= "i";
                }
                if ($proveedorID !== null && $productoID !== null) {
                    $queryCheck .= PROVEEDOR_ID . " = ? AND " . PRODUCTO_ID . " = ? ";
                    $params[] = $proveedorID;
                    $params[] = $productoID;
                    $types .= "ii";
                }

                // Prepara la consulta y ejecuta la verificación
                $stmt = mysqli_prepare($conn, $queryCheck);
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                // Verifica si existe algún registro con los criterios dados
                if (mysqli_num_rows($result) > 0) {
                    if ($row = mysqli_fetch_assoc($result)) {
                        // Verificar si está inactivo (bit de estado en 0)
                        $isInactive = $row[PROVEEDOR_PRODUCTO_ESTADO] == 0;
                        $response = ["success" => true, "exists" => true, "inactive" => $isInactive, "id" => $row[PROVEEDOR_PRODUCTO_ID]];
                    }
                }else{
                        $response = ["success" => true, "exists" => false];
                }
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError($e->getCode(), $e->getMessage(),
                    'Ocurrió un error al verificar la existencia de producto y proveedor en la base de datos',
                    $this->className
                );
        
                $response = ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if (isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
            }
            return $response;
        }
        public function existeIdproveedorProducto($idproductproveedor){
            $response = [];
            $conn = null; $stmt = null;
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                $queryCheck = "SELECT * FROM ". TB_PROVEEDOR_PRODUCTO . " WHERE " . PROVEEDOR_PRODUCTO_ID . " = ? ";
                $params = [$idproductproveedor];
                $types = "i";
                // Prepara la consulta y ejecuta la verificación
                $stmt = mysqli_prepare($conn, $queryCheck);
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                // Verifica si existe algún registro con los criterios dados
                if (mysqli_num_rows($result) > 0) {
                    if ($row = mysqli_fetch_assoc($result)) {
                        // Verificar si está inactivo (bit de estado en 0)
                        $isInactive = $row[PROVEEDOR_PRODUCTO_ESTADO] == 0;
                        $response = ["success" => true, "exists" => true, "inactive" => $isInactive, "id" => $row[PROVEEDOR_PRODUCTO_ID]];
                    }
                }else{
                        $response = ["success" => true, "exists" => false];
                }
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError($e->getCode(), $e->getMessage(),
                    'Ocurrió un error al verificar la existencia de id productoProveedor en la base de datos',
                    $this->className
                );
        
                $response = ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if (isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
            }
            return $response;

        }

        public function ExistenciaDeProveedorYProducto($idproveedor, $idproducto){
            $check = $this->existeProveedorProducto($idproveedor,null,true);
            if(!$check['success']){ return $check; }
            if(!$check['exists']){
                return ['success'=> false, 'message' => 'Este proveedor no existe'];
            }
            $check = $this->existeProveedorProducto(null,$idproducto,false,true);
            if(!$check['success']){ return $check; }
            if(!$check['exists']){
                return ['success'=> false, 'message' => 'Este producto no existe'];
            }
            return ['success'=> true];
        }

        public function addProductoToProveedor($idproveedor, $idproducto, $conn = null){
            $response = [];
            $conexionExterna = false;
            $stmt = null;
            try{
                $check = $this->ExistenciaDeProveedorYProducto($idproveedor, $idproducto);
                if(!$check['success']){
                    return $check;
                }
                if($check['exists']){
                    throw new Exception("La relacion proveedor producto ya existe");
                }

                $check = $this->existeRelacionProveedorProducto($idproveedor,$idproducto);

                if(!$check['success']){
                    return $check;
                }
                if($check['exists'] && $check['inactive']){
                    return ['success' => true, "inactive" => $check['inactive'],"message" => "Ya existe el producto registrado para este proveedor, ¿Deseas reactivarlo?","id"=>$check['id'] ];
                }
                if($check['exists']){
                    throw new Exception("Ya existe la relacion del producto con este proveedor.");
                }
                // Si no se proporcionó una conexión, crea una nueva
                if (is_null($conn)) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $conexionExterna = true;
                }
                // Obtenemos el último ID de la tabla tbproveedordireccion
                // generacion de id
				$queryGetLastId = "SELECT MAX(" . PROVEEDOR_PRODUCTO_ID . ") FROM " . TB_PROVEEDOR_PRODUCTO;
				$idCont = mysqli_query($conn, $queryGetLastId);
				$nextId = 1;
				if ($row = mysqli_fetch_row($idCont)) {
					$nextId = (int) trim($row[0]) + 1;
				}

                // Crea una consulta y un statement SQL para insertar el registro
                $queryInsert = "INSERT INTO " . TB_PROVEEDOR_PRODUCTO . " ("
                    . PROVEEDOR_PRODUCTO_ID . ", "
                    . PROVEEDOR_ID . ", "
                    . PRODUCTO_ID
                    . ") VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($conn, $queryInsert);

                // Prepara y ejecuta la consulta de inserción
                mysqli_stmt_bind_param($stmt, 'iii', $nextId, $idproveedor, $idproducto);
                $result = mysqli_stmt_execute($stmt);

                $response = ["success" => true, "message" => "Producto asignada exitosamente al proveedor."];
            }catch (Exception $e) {
                // Manejo del error dentro del bloque catch
				$userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al intentar asignarle un producto al proveedor en la base de datos',
                    $this->className
                );
                $response = ["success" => false, "message" => $userMessage];
			} finally{
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if ($conexionExterna && isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
            }
            return $response;
        }

        public function updateProductoProveedor($idproductproveedor, $idproducto, $idproveedor){
            $response = [];
            try {
                $check =  $this->existeIdproveedorProducto($idproductproveedor);
                if(!$check['success']){
                    return $check;
                }
                if(!$check['exists']){
                    throw new Exception("No existe el id de la relacion proveedorproducto.");
                }

                $check = $this->existeRelacionProveedorProducto($idproveedor,$idproducto, $idproductproveedor);
                if(!$check['success']){
                    return $check;
                }
                if($check['exists']){
                    throw new Exception("Ya existe la relacion del producto con este proveedor.");
                }
                // Establece una conexion con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
                // Crea una consulta y un statement SQL para actualizar el registro
                $queryUpdate = 
                    "UPDATE " . TB_PROVEEDOR_PRODUCTO . 
                    " SET " . 
                        PROVEEDOR_ID . " = ?, " . 
                        PRODUCTO_ID . " = ?, " .                
                        PROVEEDOR_PRODUCTO_ESTADO. " = true " .
                    " WHERE " . PROVEEDOR_PRODUCTO_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryUpdate);

                mysqli_stmt_bind_param(
                    $stmt,
                    'iii', // s: Cadena, i: Entero
                    $idproveedor,
                    $idproducto,
                    $idproductproveedor
                );
                // Ejecuta la consulta de actualización
                $result = mysqli_stmt_execute($stmt);
                if($result){
                    $response = ["success" => true, "message" => "ProveedorProducto actualizado exitosamente."];
                }else{
                    $response = ["success" => false, "message" => "Error al actualizar el proveedorproducto."];
                }
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError( $e->getCode(),  $e->getMessage(),
                                        'Error al actualizar el proveedorproveedor en la base de datos'
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


        public function getProductoByProveedor($idproveedor, $json = false){
            $conn = null; $stmt = null;

            try {
                
                $check = $this->existeProveedorProducto($idproveedor, null, true);
                if(!$check["success"]){ return $check;}
                if(!$check["exists"]){
                    return ['success' => false, 'message'=> 'El proveedor no existe en la base de datos.'];
                }
                $check = $this->existeProveedorProducto($idproveedor);
                if(!$check["success"]){ return $check; }
                if(!$check["exists"]){
                    return [ 'success' => false, 'message'=> 'Este proveedor no tiene productos asignados.'];
                }

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];


                // Consulta para obtener las direcciones de un proveedor
				$querySelect = "
                    SELECT
                        P.*
                    FROM " . TB_PRODUCTO . " P "
                    . " INNER JOIN " . TB_PROVEEDOR_PRODUCTO. " PP ON P." . PRODUCTO_ID . " = PP." . PRODUCTO_ID . "
                    WHERE
                        PP." . PROVEEDOR_ID . " = ? AND 
                        PP." . PROVEEDOR_PRODUCTO_ESTADO . " != FALSE; ";

                // Preparar la consulta, vincular los parámetros y ejecutar la consulta
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, 'i', $idproveedor);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                // Creamos la lista con los datos obtenidos
                $productos = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    if($json){
                        $productos[] = [
                            "ID"            =>$row[PRODUCTO_ID],
                            "Nombre"        =>$row[PRODUCTO_NOMBRE],
                            "PrecioCompra"  =>$row[PRODUCTO_PRECIO_COMPRA],
                            "PorcentajeGanancia" =>$row[PRODUCTO_PORCENTAJE_GANANCIA],
                            "Descripcion"   =>$row[PRODUCTO_DESCRIPCION],
                            "CodigoBarras"  =>$row[PRODUCTO_CODIGO_BARRAS_ID],
                            "Imagen"        =>$row[PRODUCTO_IMAGEN],
                            "Estado"        =>$row[PRODUCTO_ESTADO]
                        ];
                    }else{
                        $productos[] = new Producto(
                            $row[PRODUCTO_NOMBRE],
                            $row[PRODUCTO_PRECIO_COMPRA],
                            $row[PRODUCTO_CODIGO_BARRAS_ID],
                            $row[PRODUCTO_IMAGEN],
                            $row[PRODUCTO_PORCENTAJE_GANANCIA],
                            $row[PRODUCTO_ID],
                            $row[PRODUCTO_DESCRIPCION],
                            $row[PRODUCTO_ESTADO]);
                    }
                }
                return ["success" => true, "productos" => $productos];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al obtener la lista de productos del proveedor desde la base de datos',
                    $this->className
                );

                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if (isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
            }
        }

        public function getPaginateProductoProveedor($idproveedor,$page,$size, $sort= null, $onlyActive = true, $deleted = false){
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

                $check = $this->existeProveedorProducto($idproveedor, null, true);
                if(!$check["success"]){ return $check;}
                if(!$check["exists"]){
                    return ['success' => false, 'message'=> 'El proveedor no existe en la base de datos.'];
                }

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Consultar el total de registros
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_PROVEEDOR_PRODUCTO . " WHERE " . PROVEEDOR_ID . " = ? ";
                if ($onlyActive) { $queryTotalCount .= " AND " . PROVEEDOR_PRODUCTO_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE") . " "; }

                // Preparar la consulta y ejecutarla
                $stmt = mysqli_prepare($conn, $queryTotalCount);
                mysqli_stmt_bind_param($stmt, 'i', $idproveedor);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $totalRecords = (int) mysqli_fetch_assoc($result)["total"];
                $totalPages = ceil($totalRecords / $size);

                // Construir la consulta SQL para paginación
                $querySelect = "
                    SELECT
                        P.*
                    FROM " . TB_PRODUCTO . " P "
                    . " INNER JOIN " . TB_PROVEEDOR_PRODUCTO. " PP ON P." . PRODUCTO_ID . " = PP." . PRODUCTO_ID . "
                    WHERE PP." . PROVEEDOR_ID . " = ? ";
                if ($onlyActive) { $querySelect .= " AND PP." . PROVEEDOR_PRODUCTO_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE") . " "; }

                // Añadir la cláusula de ordenamiento si se proporciona
                if ($sort) { $querySelect .= "ORDER BY producto" . $sort . " "; }

				// Añadir la cláusula de limitación y offset
                $querySelect .= " LIMIT ? OFFSET ?";

                // Preparar la consulta, vincular los parámetros y ejecutar la consulta
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, "iii", $idproveedor, $size, $offset);
                mysqli_stmt_execute($stmt);

                // Obtener los resultados de la consulta
                $result = mysqli_stmt_get_result($stmt);

                // Creamos la lista con los datos obtenidos
                $productos = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $productos[] = [
                        "ID"            =>$row[PRODUCTO_ID],
                        "Nombre"        =>$row[PRODUCTO_NOMBRE],
                        "PrecioCompra"  =>$row[PRODUCTO_PRECIO_COMPRA],
                        "PorcentajeGanancia" =>$row[PRODUCTO_PORCENTAJE_GANANCIA],
                        "Descripcion"   =>$row[PRODUCTO_DESCRIPCION],
                        "CodigoBarras"  =>$row[PRODUCTO_CODIGO_BARRAS_ID],
                        "Imagen"        =>$row[PRODUCTO_IMAGEN],
                        "Estado"        =>$row[PRODUCTO_ESTADO]
                    ];
                }

                return [
                    "success" => true,
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "proveedorID" => $idproveedor,
                    "productos" => $productos
                ];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al obtener la lista de productos del proveedor desde la base de datos',
                    $this->className
                );
        
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if (isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
            }
        }

        public function removeProductoToProveedor($idproveedor, $idproducto,$conn = null){
            $createdConnection = false;
            $stmt = null;

            try {
                 $check  = $this->ExistenciaDeProveedorYProducto($idproveedor,$idproducto);
                if(!$check['success']){ return $check; }

                //existencia de una relaccion
                $check = $this->existeProveedorProducto($idproveedor, $idproducto);
                if(!$check['success']){ return $check;}
                if(!$check['exists']){
                    return ['success' => false, 'message' => 'No existe relacion entre este producto y el proveedor.'];
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

                // Crea una consulta y un statement SQL para eliminar el registro (borrado logico)
				$queryUpdate = 
                    "UPDATE " . TB_PROVEEDOR_PRODUCTO . 
                    " SET " . PROVEEDOR_PRODUCTO_ESTADO . " = FALSE " .
					" WHERE " 
                        . PROVEEDOR_ID . " = ? AND " 
                        . PRODUCTO_ID . " = ?";
				$stmt = mysqli_prepare($conn, $queryUpdate);
                mysqli_stmt_bind_param($stmt, 'ii', $idproveedor, $idproducto);
				mysqli_stmt_execute($stmt);

                // Confirmar la transacción si la conexión fue creada aquí
                if ($createdConnection) {
                    mysqli_commit($conn);
                }
		
				// Devuelve el resultado de la operación
				return ["success" => true, "message" => "Producto del proveedor ha sido eliminada exitosamente."];
            } catch (Exception $e) {
                // Revertir la transacción en caso de error si la conexión fue creada aquí
                if ($createdConnection && isset($conn)) { mysqli_rollback($conn); }
        
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al intentar eliminar el producto del proveedor en la base de datos',
                    $this->className
                );
                return ["success" => false, "message" => $userMessage];
			} finally {
				// Cierra el statement y la conexión solo si fueron creados en esta función
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if ($createdConnection && isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
			}    
        }



        //metodos viejos
        private function obtenerNuevoId() {
            try {
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];

                $query = "SELECT MAX(" . PROVEEDOR_PRODUCTO_ID . ") AS max_id FROM " . TB_PROVEEDOR_PRODUCTO;
                $stmt = mysqli_prepare($conn, $query);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }

                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if (!$result) {
                    throw new Exception("Error al obtener el resultado: " . mysqli_error($conn));
                }

                $row = mysqli_fetch_assoc($result);
                if ($row['max_id'] === null) {
                    return 1;
                }
                return $row['max_id'] + 1;
            } catch (Exception $e) {
                error_log($e->getMessage());
                return false;
            } finally {
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        function verificarExisteProducto($producto_id = null, $producto_nombre = null, $producto_Fecha = null){
            try {
                
                 //Conexion a la base de datos
                 
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
                
                //Generando sentencia SQL dinámica
               
                $queryCheck = "SELECT * FROM " . TB_PRODUCTO . " WHERE ";
                $conditions = [];
                $params = [];
                $types = "";
        
                if ($producto_id !== null) {
                    // Verificar existencia por ID
                    $conditions[] = PRODUCTO_ID . " = ?";
                    $params[] = $producto_id;
                    $types .= 'i';
                }
                if ($producto_nombre !== null) {
                    // Verificar existencia por nombre
                    $conditions[] = PRODUCTO_NOMBRE . " = ?";
                    $params[] = $producto_nombre;
                    $types .= 's';
                }
                if ($producto_Fecha !== null) {
                    // Verificar existencia por fecha de adquisición
                    $conditions[] = PRODUCTO_FECHA_ADQ . " = ?";
                    $params[] = $producto_Fecha;
                    $types .= 's';
                }
        
                // Asegurar que el producto esté activo
                $conditions[] = PRODUCTO_ESTADO . " != false";
        
                // Unir todas las condiciones
                $queryCheck .= implode(' AND ', $conditions);
                
                
                //Preparar y ejecutar la consulta

                $stmt = mysqli_prepare($conn, $queryCheck);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }
        
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

        private function verificarProveedorExiste($proveedorId) {
            try {
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                $query = "SELECT COUNT(*) AS count FROM " . TB_PROVEEDOR . " WHERE " . PROVEEDOR_ID . " = ? AND " . PROVEEDOR_ESTADO . " != false";
                $stmt = mysqli_prepare($conn, $query);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }
        
                mysqli_stmt_bind_param($stmt, 'i', $proveedorId);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if (!$result) {
                    throw new Exception("Error al obtener el resultado: " . mysqli_error($conn));
                }
        
                $row = mysqli_fetch_assoc($result);
                return $row['count'] > 0;
            } catch (Exception $e) {
                error_log($e->getMessage());
                return false;
            } finally {
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }
        

        public function insertarProveedorProducto($proveedorId, $productoId) {
            try {
                // Verifica si el producto y el proveedor existen
                if (!$this->verificarExisteProducto($productoId)) {
                    throw new Exception("El producto con ID $productoId no existe.");
                }
                if (!$this->verificarProveedorExiste($proveedorId)) {
                    throw new Exception("El proveedor con ID $proveedorId no existe.");
                }
        
                // Genera un nuevo ID para la relación
                $nuevoId = $this->obtenerNuevoId();
                if (!$nuevoId) {
                    throw new Exception("No se pudo generar un nuevo ID.");
                }
        
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                $query = "INSERT INTO " . TB_PROVEEDOR_PRODUCTO . " (" . PROVEEDOR_PRODUCTO_ID . ", " . PROVEEDOR_ID . ", " . PRODUCTO_ID . ") VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($conn, $query);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }
        
                mysqli_stmt_bind_param($stmt, 'iii', $nuevoId, $proveedorId, $productoId);
                $success = mysqli_stmt_execute($stmt);
                if (!$success) {
                    throw new Exception("Error al ejecutar la consulta: " . mysqli_error($conn));
                }
        
                return ["success" => true, "message" => "Guardado correctamente."];
            }  catch (Exception $e) {
                // Manejo del error dentro del bloque catch
				$userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al intentar asignarle Producto al proveedor en la base de datos',
                    $this->className
                );
        
                return ["success" => false, "message" => $userMessage];
			}  finally {
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        //Obtener nombres de Proveedores-Producto junto con sus ID
        public function obtenerTodosProveedorProducto() {
            try {
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                //Inner Join
                $query = "SELECT pp." . PROVEEDOR_PRODUCTO_ID . ", 
                                 p." . PRODUCTO_ID . " AS producto_id, 
                                 p." . PRODUCTO_NOMBRE . " AS producto_nombre, 
                                 pr." . PROVEEDOR_ID . " AS proveedor_id, 
                                 pr." . PROVEEDOR_NOMBRE . " AS proveedor_nombre 
                          FROM " . TB_PROVEEDOR_PRODUCTO . " pp
                          INNER JOIN " . TB_PRODUCTO . " p ON pp." . PRODUCTO_ID . " = p." . PRODUCTO_ID . "
                          INNER JOIN " . TB_PROVEEDOR . " pr ON pp." . PROVEEDOR_ID . " = pr." . PROVEEDOR_ID;
         
                $stmt = mysqli_prepare($conn, $query);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }
        
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if (!$result) {
                    throw new Exception("Error al obtener el resultado: " . mysqli_error($conn));
                }
        
                $proveedorProductos = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $proveedorProductos[] = $row;
                }
        
                return ["success" => true, "data" => $proveedorProductos];
            }catch (Exception $e) {
                // Manejo del error dentro del bloque catch
				$userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al obtener proveedor productos de la base de datos',
                    $this->className
                );
        
                return ["success" => false, "message" => $userMessage];
			}  finally {
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }
        

        public function actualizarProveedorProducto($proveedorProductoId, $nuevoProveedorId, $nuevoProductoId) {
            try {
                // Verifica si el nuevo producto y proveedor existen
                if (!$this->verificarExisteProducto($nuevoProductoId)) {
                    throw new Exception("El producto con ID $nuevoProductoId no existe.");
                }
                if (!$this->verificarProveedorExiste($nuevoProveedorId)) {
                    throw new Exception("El proveedor con ID $nuevoProveedorId no existe.");
                }
        
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                $query = "UPDATE " . TB_PROVEEDOR_PRODUCTO . " 
                          SET " . PROVEEDOR_ID . " = ?, " . PRODUCTO_ID . " = ? 
                          WHERE " . PROVEEDOR_PRODUCTO_ID . " = ?";
                $stmt = mysqli_prepare($conn, $query);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }
        
                mysqli_stmt_bind_param($stmt, 'iii', $nuevoProveedorId, $nuevoProductoId, $proveedorProductoId);
                $success = mysqli_stmt_execute($stmt);
                if (!$success) {
                    throw new Exception("Error al ejecutar la consulta: " . mysqli_error($conn));
                }
        
                return ["success" => true, "message" => "Actualizado correctamente."];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
				$userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al intentar actualizar producto proveedor en la base de datos',
                    $this->className
                );
        
                return ["success" => false, "message" => $userMessage];
			}  finally {
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function eliminarProveedorProducto($proveedorProductoId) {
            try {
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];

                $query = "UPDATE " . TB_PROVEEDOR_PRODUCTO . 
                "SET " .PROVEEDOR_PRODUCTO_ESTADO . " = false "   
                . " WHERE " . PROVEEDOR_PRODUCTO_ID . " = ? ";
                $stmt = mysqli_prepare($conn, $query);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }
                mysqli_stmt_bind_param($stmt, 'i', $proveedorProductoId);
                $success = mysqli_stmt_execute($stmt);
                if (!$success) {
                    throw new Exception("Error al ejecutar la consulta: " . mysqli_error($conn));
                }

                return ["success" => true, "message" => "Eliminado correctamente."];
            } catch (Exception $e) {
                error_log($e->getMessage());
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function obtenerProveedorProductoPorId($proveedorProductoId) {
            try {
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                $query = "SELECT pp." . PROVEEDOR_PRODUCTO_ID . ", 
                                 p." . PRODUCTO_ID . " AS producto_id, 
                                 p." . PRODUCTO_NOMBRE . " AS producto_nombre, 
                                 pr." . PROVEEDOR_ID . " AS proveedor_id, 
                                 pr." . PROVEEDOR_NOMBRE . " AS proveedor_nombre 
                          FROM " . TB_PROVEEDOR_PRODUCTO . " pp
                          INNER JOIN " . TB_PRODUCTO . " p ON pp." . PRODUCTO_ID . " = p." . PRODUCTO_ID . "
                          INNER JOIN " . TB_PROVEEDOR . " pr ON pp." . PROVEEDOR_ID . " = pr." . PROVEEDOR_ID . "
                          WHERE pp." . PROVEEDOR_PRODUCTO_ID . " = ?";
        
                $stmt = mysqli_prepare($conn, $query);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }
        
                mysqli_stmt_bind_param($stmt, 'i', $proveedorProductoId);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if (!$result) {
                    throw new Exception("Error al obtener el resultado: " . mysqli_error($conn));
                }
        
                $proveedorProducto = mysqli_fetch_assoc($result);
                return ["success" => true, "data" => $proveedorProducto];
            } catch (Exception $e) {
                error_log($e->getMessage());
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }        
    }

?>