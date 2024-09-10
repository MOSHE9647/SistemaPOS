<?php
    require_once 'data.php';
    require_once __DIR__ . '/../domain/Categoria.php';
    require_once __DIR__ . '/../utils/Variables.php';

    class ProveedorCategoriaData extends Data{

        private $className;
        public function __construct(){
            $this->className = get_class($this);
            parent::__construct();
        }
        

        public function existeProveedorCategoria($proveedorID = null, $categoriaID = null, $tbProveedor = false, $tbCategoria = false){
            $conn = null; $stmt = null;
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Determina la tabla y construye la consulta base
                $tableName = $tbProveedor ? TB_PROVEEDOR : ($tbCategoria ? TB_CATEGORIA : TB_PROVEEDOR_CATEGORIA);
                $queryCheck = "SELECT 1 FROM $tableName WHERE ";
                $params = [];
                $types = "";

                if ($proveedorID && $categoriaID) {
                    // Consulta para verificar si existe una asignación entre el proveedor y la dirección
                    $queryCheck .= PROVEEDOR_ID . " = ? AND " .  CATEGORIA_ID. " = ? AND " . PROVEEDOR_CATEGORIA_ESTADO . " != FALSE";
                    $params = [$proveedorID, $categoriaID];
                    $types = "ii";
                } else if ($proveedorID) {
                    // Consulta para verificar si existe un proveedor con el ID ingresado
                    $estadoCampo = $tbProveedor ? PROVEEDOR_ESTADO :PROVEEDOR_CATEGORIA_ESTADO;
                    $queryCheck .= PROVEEDOR_ID . " = ? AND $estadoCampo != FALSE";
                    $params = [$proveedorID];
                    $types = "i";
                } else if ($categoriaID){
                    // Consulta para verificar si existe una dirección con el ID ingresado
                    $estadoCampo = $tbCategoria ? CATEGORIA_ESTADO : PROVEEDOR_CATEGORIA_ESTADO;
                    $queryCheck .= CATEGORIA_ID . " = ? AND $estadoCampo != FALSE";
                    $params = [$categoriaID];
                    $types = "i";
                } else {
                    // Registrar parámetros faltantes y lanzar excepción
                    $missingParamsLog = "Faltan parámetros para verificar la existencia del proveedor y/o categoria:";
                    if (!$proveedorID) $missingParamsLog .= " proveedorID [" . ($proveedorID ?? 'null') . "]";
                    if (!$categoriaID) $missingParamsLog .= " categoriaID [" . ($categoriaID ?? 'null') . "]";
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
                    'Ocurrió un error al verificar la existencia de categoria y proveedor en la base de datos',
                    $this->className
                );
        
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if (isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
            }
        }

        public function ExistenciaDeProveedorYCategoria($idproveedor, $idcategoria){
            $check = $this->existeProveedorCategoria($idproveedor,null,true);
            if(!$check['success']){ return $check; }
            if(!$check['exists']){
                return ['success'=> false, 'message' => 'Este proveedor no existe'];
            }
            $check = $this->existeProveedorCategoria(null,$idcategoria,false,true);
            if(!$check['success']){ return $check; }
            if(!$check['exists']){
                return ['success'=> false, 'message' => 'Esta Categoria no existe'];
            }
            return ['success'=> true];
        }

        public function addCategoriaToProveedor($idproveedor, $idcategoria, $conn = null){
            $conexionExterna = false;
            $stmt = null;
            try{


                $check = $this->ExistenciaDeProveedorYCategoria($idproveedor, $idcategoria);
                if(!$check['success']){
                    return $check;
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
				$queryGetLastId = "SELECT MAX(" . PROVEEDOR_CATEGORIA_ID . ") FROM " . TB_PROVEEDOR_CATEGORIA;
				$idCont = mysqli_query($conn, $queryGetLastId);
				$nextId = 1;
				if ($row = mysqli_fetch_row($idCont)) {
					$nextId = (int) trim($row[0]) + 1;
				}

                // Crea una consulta y un statement SQL para insertar el registro
                $queryInsert = "INSERT INTO " . TB_PROVEEDOR_CATEGORIA . " ("
                    . PROVEEDOR_CATEGORIA_ID. ", "
                    . PROVEEDOR_ID . ", "
                    . CATEGORIA_ID
                    . ") VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($conn, $queryInsert);

                // Prepara y ejecuta la consulta de inserción
                mysqli_stmt_bind_param($stmt, 'iii', $nextId, $idproveedor, $idcategoria);
                $result = mysqli_stmt_execute($stmt);

                return ["success" => true, "message" => "Categoria asignada exitosamente al proveedor."];
            }catch (Exception $e) {
                // Manejo del error dentro del bloque catch
				$userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al intentar asignarle una categoria al proveedor en la base de datos',
                    $this->className
                );
                return ["success" => false, "message" => $userMessage];
			} finally{
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if ($conexionExterna && isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
            }


        }

        public function getCategoriaByProveedor($idproveedor, $json = false){
            $conn = null; $stmt = null;

            try {
                
                $check = $this->existeProveedorCategoria($idproveedor, null, true);
                if(!$check["success"]){ return $check;}
                if(!$check["exists"]){
                    return ['success' => false, 'message'=> 'El proveedor no existe en la base de datos.'];
                }
               
                $check = $this->existeProveedorCategoria($idproveedor);
                if(!$check["success"]){ return $check; }
                if(!$check["exists"]){
                    return [ 'success' => false, 'message'=> 'Este proveedor no tiene categorias asignados.'];
                }

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];


                // Consulta para obtener las direcciones de un proveedor
				$querySelect = "
                    SELECT
                        C.*
                    FROM " . TB_CATEGORIA . " C "
                    . " INNER JOIN " . TB_PROVEEDOR_CATEGORIA. " PC ON C." . CATEGORIA_ID . " = PC." . CATEGORIA_ID . "
                    WHERE
                        PC." . PROVEEDOR_ID . " = ? AND 
                        PC." . PROVEEDOR_CATEGORIA_ESTADO . " != FALSE; ";

                // Preparar la consulta, vincular los parámetros y ejecutar la consulta
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, 'i', $idproveedor);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                // Creamos la lista con los datos obtenidos
                $categorias = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    if($json){
                        $categorias[] = [
                            "ID"            =>$row[CATEGORIA_ID],
                            "Nombre"        =>$row[CATEGORIA_NOMBRE],
                            "Descripcion"   =>$row[CATEGORIA_DESCRIPCION],
                            "Estado"        =>$row[CATEGORIA_ESTADO]
                        ];
                    }else{
                        $categorias[] = new Categoria(
                            $row[CATEGORIA_ID],
                            $row[CATEGORIA_NOMBRE],
                            $row[CATEGORIA_DESCRIPCION],
                            $row[CATEGORIA_ESTADO]
                        );
                    }
                }
                return ["success" => true, "categorias" =>  $categorias ];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al obtener la lista de categorias del proveedor desde la base de datos',
                    $this->className
                );

                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if (isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
            }
        }

        public function getPaginateCategoriaProveedor($idproveedor,$page,$size, $sort= null, $onlyActiveOrInactive = true, $deleted = false){
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

                $check = $this->existeProveedorCategoria($idproveedor, null, true);
                if(!$check["success"]){ return $check;}
                if(!$check["exists"]){
                    return ['success' => false, 'message'=> 'El proveedor no existe en la base de datos.'];
                }
                $check = $this->existeProveedorCategoria($idproveedor);
                if(!$check["success"]){ return $check; }
                if(!$check["exists"]){
                    return [ 'success' => false, 'message'=> 'Este proveedor no tiene categorias asignados.'];
                }

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Consultar el total de registros
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_PROVEEDOR_CATEGORIA . " WHERE " . PROVEEDOR_ID . " = ? ";
                if ($onlyActiveOrInactive) { $queryTotalCount .= " AND " . PROVEEDOR_CATEGORIA_ESTADO. " != " . ($deleted ? "TRUE" : "FALSE") . " "; }

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
                        C.*
                    FROM " . TB_CATEGORIA . " C "
                    . " INNER JOIN " . TB_PROVEEDOR_CATEGORIA. " PC ON C." . CATEGORIA_ID . " = PC." . CATEGORIA_ID . "
                    WHERE PC." . PROVEEDOR_ID . " = ? ";
                if ($onlyActiveOrInactive) { $querySelect .= " AND PC." . PROVEEDOR_CATEGORIA_ESTADO. " != " . ($deleted ? "TRUE" : "FALSE") . " "; }

                // Añadir la cláusula de ordenamiento si se proporciona
                if ($sort) { $querySelect .= "ORDER BY C.categoria" . $sort . " "; }

				// Añadir la cláusula de limitación y offset
                $querySelect .= " LIMIT ? OFFSET ?";

                // Preparar la consulta, vincular los parámetros y ejecutar la consulta
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, "iii", $idproveedor, $size, $offset);
                mysqli_stmt_execute($stmt);

                // Obtener los resultados de la consulta
                $result = mysqli_stmt_get_result($stmt);

                // Creamos la lista con los datos obtenidos
                $categorias = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $categorias[] = [
                        "ID"            =>$row[CATEGORIA_ID],
                        "Nombre"        =>$row[CATEGORIA_NOMBRE],
                        "Descripcion"   =>$row[CATEGORIA_DESCRIPCION],
                        "Estado"        =>$row[CATEGORIA_ESTADO]
                    ];
                }

                return [
                    "success" => true,
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "proveedor" => $idproveedor,
                    "categorias" => $categorias
                ];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al obtener la lista de categorias del proveedor desde la base de datos',
                    $this->className
                );
        
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if (isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
            }
        }

        public function removeCategoriaToProveedor($idproveedor, $idcategoria,$conn = null){
            $createdConnection = false;
            $stmt = null;

            try {
                 $check  = $this->ExistenciaDeProveedorYCategoria($idproveedor,$idcategoria);
                if(!$check['success']){ return $check; }

                //existencia de una relaccion
                $check = $this->existeProveedorCategoria($idproveedor, $idcategoria);
                if(!$check['success']){ return $check;}
                if(!$check['exists']){
                    return ['success' => false, 'message' => 'No existe relacion entre esta categoria y el proveedor.'];
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
                    "UPDATE " . TB_PROVEEDOR_CATEGORIA . 
                    " SET " . PROVEEDOR_CATEGORIA_ESTADO . " = FALSE " .
					" WHERE " 
                        . PROVEEDOR_ID . " = ? AND " 
                        . CATEGORIA_ID . " = ?";
				$stmt = mysqli_prepare($conn, $queryUpdate);
                mysqli_stmt_bind_param($stmt, 'ii', $idproveedor, $idcategoria);
				mysqli_stmt_execute($stmt);

                // Confirmar la transacción si la conexión fue creada aquí
                if ($createdConnection) {
                    mysqli_commit($conn);
                }
		
				// Devuelve el resultado de la operación
				return ["success" => true, "message" => "Categoria del proveedor ha sido eliminada exitosamente."];
            } catch (Exception $e) {
                // Revertir la transacción en caso de error si la conexión fue creada aquí
                if ($createdConnection && isset($conn)) { mysqli_rollback($conn); }
        
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al intentar eliminar la categoria del proveedor en la base de datos',
                    $this->className
                );
                return ["success" => false, "message" => $userMessage];
			} finally {
				// Cierra el statement y la conexión solo si fueron creados en esta función
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { mysqli_stmt_close($stmt); }
                if ($createdConnection && isset($conn) && $conn instanceof mysqli) { mysqli_close($conn); }
			}    
        }
    }
?>