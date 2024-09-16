<?php
    require_once 'data.php';
    require_once __DIR__ . '/../domain/Subcategoria.php';
    require_once __DIR__ .'/../data/categoriaData.php';
    require_once __DIR__ . '/../utils/Utils.php';
    require_once __DIR__ . '/../utils/Variables.php';

    class SubcategoriaData extends Data {
        private $categoriaData;
        // Constructor
		public function __construct() {
			parent::__construct();
            $this->categoriaData = new CategoriaData();
        }
        function VerificarExisteSubcategoria($subcategoria_id = null, $subcategoria_nombre = null, $update = false){
            try {
                /******************************
                 * Conexion a la base de datos
                 ******************************/
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
                
                /****************************************
                * Generando sentencia
                *****************************************/
                $queryCheck = "SELECT * FROM " . TB_SUBCATEGORIA. " WHERE ";
                $params = [];
                $types = "";
                
                if ($subcategoria_id !== null && !$update) {
                    // Verificar existencia por ID
                    $queryCheck .= SUBCATEGORIA_ID . " = ?;";
                    $params[] = $subcategoria_id;
                    $types .= 'i';
                } elseif ($subcategoria_nombre !== null) {
                    // Verificar existencia por nombre depende si actualiza o no
                    if(!$update){
                        $queryCheck .= SUBCATEGORIA_NOMBRE. " = ?;";
                        $params[] = $subcategoria_nombre;
                        $types .= 's';
                    }else{
                        $queryCheck .= SUBCATEGORIA_NOMBRE. " = ? AND ".SUBCATEGORIA_ID." <> ? ;";
                        $params[] = $subcategoria_nombre;
                        $params[] =$subcategoria_id;
                        $types .= 'si';
                    }
                } else {
                    throw new Exception("Se requiere al menos un parámetro: subcategoria id o subcategoria nombre");
                }
                
                $stmt = mysqli_prepare($conn, $queryCheck);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }
                /***************************************
                 * Ejecucion del query
                 ***************************************/
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

        function nombreSubcategoriaExiste($nombre, $categoriaID, $subcategoriaID = null){
            $response = [];

            try {
                /******************************
                 * Conexion a la base de datos
                 ******************************/
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
                
                /****************************************
                * Generando sentencia
                *****************************************/
                $queryCheck = "SELECT * FROM " . TB_SUBCATEGORIA. " WHERE ";
                $params = [];
                $types = "";
                
                if ($nombre !== null && $categoriaID !== null && $subcategoriaID !== null) {
                    // Verificar existencia por ID
                    $queryCheck .= SUBCATEGORIA_NOMBRE . " = ? AND ". SUBCATEGORIA_CATEGORIA_ID . " = ? AND " . SUBCATEGORIA_ID . " <> ? " ;
                    $params[] = $nombre;
                    $params[] = $categoriaID;
                    $params[] = $subcategoriaID;
                    $types .= 'sii';
                } elseif ($nombre !== null && $categoriaID) {
                    
                    $queryCheck .= SUBCATEGORIA_NOMBRE . " = ? AND ". SUBCATEGORIA_CATEGORIA_ID . " = ? " ;
                    $params[] = $nombre;
                    $params[] = $categoriaID;
                    $types .= 'si';
                    
                } else {
                    throw new Exception("Se requiere al menos un parámetro: subcategoria id, subcategoria nombre o categoria a la que pertenece");
                }
                
                $stmt = mysqli_prepare($conn, $queryCheck);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }
                /***************************************
                 * Ejecucion del query
                 ***************************************/
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);

                $result = mysqli_stmt_get_result($stmt);
                // Verifica si existe algún registro con los criterios dados
                if (mysqli_num_rows($result) > 0) {
                    if ($row = mysqli_fetch_assoc($result)) {
                        // Verificar si está inactivo (bit de estado en 0)
                        $isInactive = $row[SUBCATEGORIA_ESTADO] == 0;
                        $response = ["success" => true, "exists" => true, "inactive" => $isInactive, "id" => $row[SUBCATEGORIA_ID]];
                    }
                }else{
                    $response = ["success" => true, "exists" => false];
                }
            } catch (Exception $e) {

                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al verificar la existencia del nombre de la subcategoria en la base de datos'
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


        function getAllSubcategorias(){
            try {
                /************************************
                 * Conexión con la base de datos
                 ************************************/
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
                /************************************
                 * Preparación de query para obtener
                 * los productos
                 ************************************/
                $querySelect = "SELECT * FROM " . TB_SUBCATEGORIA. " S ".
                " INNER JOIN ". TB_CATEGORIA . " C ON C." . CATEGORIA_ID . " = S.". SUBCATEGORIA_CATEGORIA_ID .
                " WHERE S." . SUBCATEGORIA_ESTADO . " != false ";             
                
                
                $result = mysqli_query($conn, $querySelect);
        
                // Verificamos si ocurrió un error
                if (!$result) {
                    throw new Exception("Ocurrió un error al ejecutar la consulta: " . mysqli_error($conn));
                }
                /************************************
                 * Obtención de datos
                 ************************************/
                $listaSubcategorias = [];
                while ($row = mysqli_fetch_assoc($result)) {  // Usamos fetch_assoc para obtener un array asociativo
                    $listaSubcategorias[] = [
                        'ID' => $row[SUBCATEGORIA_ID],
                        'Categoria'=>$row[CATEGORIA_NOMBRE],
                        'id_categoria'=>$row[CATEGORIA_ID],
                        'Nombre' => $row[SUBCATEGORIA_NOMBRE],
                        'Descripcion'=>$row[SUBCATEGORIA_DESCRIPCION],
                        'Estado' => $row[SUBCATEGORIA_ESTADO]
					];
                }


                return ["success" => true, "listaSubcategorias" => $listaSubcategorias];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al obtener la lista de subcategorias desde la base de datos'
                );
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cerramos la conexion
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        function getPaginatedSubcategorias($page, $size, $sort = null) {
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
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_SUBCATEGORIA. " WHERE " . SUBCATEGORIA_ESTADO . " != false";
                $totalResult = mysqli_query($conn, $queryTotalCount);
                if (!$totalResult) {
                    throw new Exception("Error al obtener el conteo total de registros: " . mysqli_error($conn));
                }
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = (int)$totalRow['total'];
                $totalPages = ceil($totalRecords / $size);

				// Construir la consulta SQL para paginación
                $querySelect =  "SELECT * FROM " . TB_SUBCATEGORIA. " S ".
                " INNER JOIN ". TB_CATEGORIA . " C ON C." . CATEGORIA_ID . " = S.". SUBCATEGORIA_CATEGORIA_ID .
                " WHERE S." . SUBCATEGORIA_ESTADO . " != false ";             
                

				// Añadir la cláusula de ordenamiento si se proporciona
                if ($sort) {
                    $querySelect .= "ORDER BY tbsubcategoria" . $sort . " ";
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

				$listaSubcategorias = [];
				while ($row = mysqli_fetch_assoc($result)) {
					$listaSubcategorias[] = [
                        'ID' => $row[SUBCATEGORIA_ID],
                        'Categoria' => $row[CATEGORIA_NOMBRE],
                        'id_categoria'=>$row[CATEGORIA_ID],
                        'Nombre' => $row[SUBCATEGORIA_NOMBRE],
                        'Descripcion'=>$row[SUBCATEGORIA_DESCRIPCION],
                        'Estado' => $row[SUBCATEGORIA_ESTADO]
					];
				}
				return [
                    "success" => true,
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "listaSubcategorias" => $listaSubcategorias
                ];
			} catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al obtener la lista de subcategorias desde la base de datos'
                );
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cerrar la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }
        function updateSubcategorias($Subcategoria){
            $response = [];
            try {
                /***************************************
                 * Validacion de campos necesarios
                 ***************************************/
                $id_subcategoria = $Subcategoria->getSubcategoriaId();
                $nombre_subcategoria = $Subcategoria->getSubcategoriaNombre();
                $descripcion = $Subcategoria->getSubcategoriaDescripcion();
                $categoriaID = $Subcategoria-> getSubcategoriaCategoriaId();


                if(empty($id_subcategoria) || !is_numeric($id_subcategoria)){
                    throw new Exception("¡El id de la subcategoria esta vacio o no es valido!");
                }
                if(empty($nombre_subcategoria)){
                    throw new Exception("¡El nombre de la subcategoria esta vacia!");
                }
                
                $check = $this->VerificarExisteSubcategoria($id_subcategoria);
                if(!$check['success']){
                    return $check;
                }
                if(empty($categoriaID)){
                    throw new Exception("El id de la categoria seleccionada esta vacia.");
                }

                $check = $this->categoriaData->categoriaExiste($categoriaID);
                if(!$check['success']){
                    return $check;
                }
                if(!$check['exists']){
                    throw new Exception('No existe el id de categoria seleccionado');
                }


                if(!$check['exists']){
                    throw new Exception("No existe el id de la subcategoria en la base de datos.");
                }

                $check = $this->nombreSubcategoriaExiste($nombre_subcategoria, $categoriaID,$id_subcategoria);
                if(!$check['success']){
                    return $check;
                }
                if($check['exists']){
                    throw new Exception("Ya existe una subcategoria con el mismo nombre para esta categoria");
                }
                /***********************************
                 * Conexion a la base de datos 
                 ***********************************/
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                /***********************************
                 * Creando sentencia update
                 ***********************************/
                $queryUpdate = 
                    "UPDATE ".TB_SUBCATEGORIA ." SET " . 
                        SUBCATEGORIA_NOMBRE. " = ?, " . 
                        SUBCATEGORIA_CATEGORIA_ID . " = ? ,".
                        SUBCATEGORIA_DESCRIPCION ." = ?, " .
                        SUBCATEGORIA_ESTADO." = true ". 
                    "WHERE " . SUBCATEGORIA_ID. " = ?;";

                $stmt = mysqli_prepare($conn, $queryUpdate);
                if (!$stmt) { throw new Exception("Error al preparar la consulta"); }

                mysqli_stmt_bind_param(
                    $stmt,
                    'sisi', // s: Cadena, i: Entero
                    $nombre_subcategoria,
                    $categoriaID,
                    $descripcion,
                    $id_subcategoria
                );

                /*********************************
                 * Ejecusion de la sentencia
                 *********************************/
                $result = mysqli_stmt_execute($stmt);
                if (!$result) { 
                    $response = ["success" => true, "message" => "¡No se pudo actualizar la subcategoria!"];
                }else{
                // Devuelve el resultado de la consulta
                    $response = ["success" => true, "message" => "¡Subcategoria actualizada exitosamente!"];
                }
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al actualizar la subcategorias en la base de datos'
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

        function insertSubcategoria($subcategoria){
            $response = [];
            try{
                /***************************************
                 * Validacion de campos necesarios
                 ***************************************/
                $nombre = $subcategoria->getSubcategoriaNombre();    
                $descripcion = $subcategoria->getSubcategoriaDescripcion();
                $categoriaID = $subcategoria-> getSubcategoriaCategoriaId();

                if(empty($nombre)){
                    throw new Exception("¡El nombre de la subcategoria esta vacia!");
                }
                if(empty($categoriaID)){
                    throw new Exception("¡El id de la categoria esta vacia!");
                }
                $check = $this->categoriaData->categoriaExiste($categoriaID);
                if(!$check['success']){
                    return $check;
                }
                if(!$check['exists']){
                    throw new Exception('No existe el id de categoria seleccionado');
                }

                $check = $this->nombreSubcategoriaExiste($nombre,$categoriaID);
                if(!$check['success']){
                    return $check;
                }
                if($check['exists'] && $check['inactive']){
                    return ["success" => true, "message"=>"Hay una subcategoria con el nombre [$nombre] incativo, ¿Deseas reactivarlo?", "id"=>$check["id"]];
                }
                if($check['exists']){
                    throw new Exception("El nombre de la subcategoria ya existe.");
                }

                /******************************************* 
                * Proceder a conexion con la base de datos
                ********************************************/
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
                /***********************************************
                * Obtenemos el último ID de la tabla tbsubcategoria
                ************************************************/
                $queryGetLastId = "SELECT MAX(" . SUBCATEGORIA_ID . ") AS subcategoriaID FROM " . TB_SUBCATEGORIA;
                $idCont = mysqli_query($conn, $queryGetLastId);
                if (!$idCont) { throw new Exception("Error al ejecutar la consulta");}
                $nextId = 1;//incrementando el id
                // Calcula el siguiente ID para la nueva entrada
				if ($row = mysqli_fetch_row($idCont)) {
					$nextId = (int) trim($row[0]) + 1;
				}
                /*****************************************************
                 * Creando sentencia para insertar el subcategoria
                 *****************************************************/
                $queryInsert = "INSERT INTO " . TB_SUBCATEGORIA . " ("
                . SUBCATEGORIA_ID . ","
                . SUBCATEGORIA_NOMBRE . ","
                . SUBCATEGORIA_CATEGORIA_ID .","
                . SUBCATEGORIA_DESCRIPCION . ","
                . SUBCATEGORIA_ESTADO . " )"
                . " VALUES (?, ?, ?, ?, true)";
                $stmt = mysqli_prepare($conn, $queryInsert);
                if (!$stmt) { throw new Exception("Error al preparar la consulta"); }

                mysqli_stmt_bind_param(
                    $stmt,
                    'isis', // i: entero, s: Cadena
                    $nextId,
                    $nombre,
                    $categoriaID,
                    $descripcion   
                );
                /**************************************
                 * Ejecucion de la sentencia insertar
                 **************************************/
                $result = mysqli_stmt_execute($stmt);
                if (!$result) { throw new Exception("¡Error al insertar nueva subcategoria!"); }

                $response = ["success" => true, "message" => "¡Subcategoria insertada excitosamente!", "id" => $nextId];
            }catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al obtener la lista de subcategorias desde la base de datos!!'
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
        
        function deleteSubcategoria($id){
            $response = [];
            try {
                /************************************
                 * Proceder a verificar si 
                 * existe el subcategoria a eliminar
                 ***********************************/
                if(empty($id) || $id<= 0){
                    throw new Exception("¡El id de la subcategoria esta vacio o no es valido!");
                }

                $exist = $this->VerificarExisteSubcategoria($id);
                if(!$exist["exists"]){ return $check; }

                /******************************
                 * Conexion a la base de datos
                 ******************************/
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
        
                /*********************************
                 * Creando sentencia de 
                 * eliminado logico
                 *********************************/
                $queryDelete = "UPDATE " . TB_SUBCATEGORIA . " SET " . SUBCATEGORIA_ESTADO. " = false WHERE " . SUBCATEGORIA_ID . " = ?;";
                $stmt = mysqli_prepare($conn, $queryDelete);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta de eliminación.");
                }
        
                mysqli_stmt_bind_param($stmt, 'i', $id);

                /********************************
                 * Ejecutando la sentencia
                 ********************************/
                $result = mysqli_stmt_execute($stmt);
                if (!$result) {
                    $response = ["success" => true, "message" => "¡No se pudo eliminar la subcategoria!"];
                }else{
                    // Devuelve el resultado de la operación
                    $response = ["success" => true, "message" => "¡Subcategoria eliminado exitosamente!"];
                }
            }catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al obtener la lista de subcategorias desde la base de datos'
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

        function getSubcategoriasBy($id){
            try {
                /************************************
                 * Conexión con la base de datos
                 ************************************/
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
                
                $check = $this->VerificarExisteSubcategoria($id);
                if(!$check["exists"]){ return $check; }

                /************************************
                 * Preparación de query para obtener
                 * el producto por ID
                 ************************************/
                $querySelect = "SELECT * FROM " . TB_SUBCATEGORIA. " WHERE " . SUBCATEGORIA_ID . " = ? AND " . SUBCATEGORIA_ESTADO . " != false;";
                $stmt = mysqli_prepare($conn, $querySelect);
                if (!$stmt) { throw new Exception("Error al preparar la consulta");}
        
                mysqli_stmt_bind_param($stmt, 'i', $id); 
        
                // Ejecución
                mysqli_stmt_execute($stmt);
        
                // Obtención del resultado
                $result = mysqli_stmt_get_result($stmt);
        
                // Verificación si se encontró algún producto
                if ($row = mysqli_fetch_assoc($result)) {  // Usamos fetch_assoc para obtener un array asociativo
                    $Subcategoria = new Subcategoria(  
                        $row[SUBCATEGORIA_NOMBRE],
                        $row[SUBCATEGORIA_ID],
                        $row[SUBCATEGORIA_ESTADO]
                    );
                    return ["success" => true, "subcategoria" => $Subcategoria];
                } else {
                    return ["success" => false, "message" => "¡Subcategoria no encontrada.!"];
                }
            } catch (Exception $e) {
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }

        }
    }

?>