<?php
    include_once 'data.php';
    include __DIR__ . '/../domain/ProductoSubcategoria.php';
    require_once __DIR__ . '/../utils/Utils.php';
    require_once __DIR__ . '/../utils/Variables.php';

    class ProductoSubcategoriaData extends Data{
                // Constructor
		public function __construct() {
			parent::__construct();
        }
        function Exists_Producto_Subcategoria_Relation($id_producto, $id_subcategoria, $id_producto_subcategoria = null, $update = false) {
            if (empty($id_producto) || empty($id_subcategoria)) { 
                throw new Exception("Los parámetros no deben estar vacíos.");
            }
        
            if ($update && empty($id_producto_subcategoria)) {
                throw new Exception("El ID del producto subcategoría no puede estar vacío al actualizar.");
            }
        
            try {
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
        
                // Preparación de la consulta
                $query = "SELECT 1 FROM " . TB_PRODUCTO_SUBCATEGORIA . " WHERE " . PRODUCTO_SUBCATEGORIA_PRODUCTO_ID . " = ? AND " . PRODUCTO_SUBCATEGORIA_SUBCATEGORIA_ID . " = ? AND " . PRODUCTO_SUBCATEGORIA_ESTADO . " != false";
                $types = "ii";
                $params = [$id_producto, $id_subcategoria];
        
                if ($update) {
                    $query .= " AND " . PRODUCTO_SUBCATEGORIA_ID . " <> ?";
                    $types .= "i";
                    $params[] = $id_producto_subcategoria;
                }
        
                $stmt = $conn->prepare($query);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . $conn->error);
                }
        
                // Vinculación y ejecución
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $resultado = $stmt->get_result();
        
                // Verificación del resultado
                if ($resultado->num_rows > 0) {
                    return ["success" => true, "exists" => true, "message" => "El registro existe"];
                } else {
                    return ["success" => true, "exists" => false, "message" => "No se encontró el registro en la base de datos."];
                }
            } catch (Exception $e) {
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
                // Cierre de recursos
                if (isset($stmt)) { $stmt->close(); }
                if (isset($conn)) { $conn->close(); }
            }
        }
        
        
        function Exists_Primary_Key($id, $TABLE_NAME, $COLUMN_ID, $COLUMN_STATUS_NAME) {
            // Validación de parámetros
            if(empty($id) || empty($TABLE_NAME) || empty($COLUMN_STATUS_NAME) || empty($COLUMN_ID)) {
                throw new Exception("¡Los parámetros no pueden estar vacíos, asegúrese de colocar los datos correspondientes!");
            }
            try {
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
        
                // Validación del ID
                if(is_numeric($id) && $id > 0) {
                    // Preparación de la consulta
                    $stmt = $conn->prepare("SELECT 1 FROM " . $TABLE_NAME . " WHERE " . $COLUMN_ID . " = ? AND " . $COLUMN_STATUS_NAME . " != false;");
                    if (!$stmt) {
                        throw new Exception("Error al preparar la consulta: " . $conn->error);
                    }
        
                    // Vinculación y ejecución
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $resultado = $stmt->get_result();
        
                    // Verificación del resultado
                    if ($resultado->num_rows === 0) {
                        throw new Exception("No se encontró el registro en la base de datos.");
                    }
                    return ["success" => true, "message" => "El registro existe y no está borrado."];
                } else {
                    throw new Exception("El ID proporcionado no es válido.");
                }
            } catch (Exception $e) {
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
                // Cierre de la conexión y el statement
                if (isset($stmt)) { $stmt->close(); }
                if (isset($conn)) { $conn->close(); }
            }
        }
        function insertProductoSubcategoria($ProductoSubcategoria){
            
            try{
                $response = [];
                /************************************
                 * Verificacion
                 */
                $id_producto = $ProductoSubcategoria->getIdProducto();
                $id_subcategoria = $ProductoSubcategoria->getIdSubcategoria();

                if(empty($id_producto)){ throw new Exception("El id de producto no debe ser vacio."); }
                if(empty($id_subcategoria)){ throw new Exception("El id de la subcategoria no debe ser vacio."); }

                $relation = $this->Exists_Producto_Subcategoria_Relation($id_producto,$id_subcategoria);
                if($relation["exists"]){
                    return $relation;
                }


                $check = $this->Exists_Primary_Key($id_producto,TB_PRODUCTO, PRODUCTO_ID, PRODUCTO_ESTADO);
                if(!$check["success"]){ throw new Exception("id producto inexistente."); }

                $check = $this->Exists_Primary_Key($id_subcategoria,TB_SUBCATEGORIA, SUBCATEGORIA_ID, SUBCATEGORIA_ESTADO);
                if(!$check["success"]){ throw new Exception("id subcategoria inexistente."); }

                /*************************************
                 * Conexion a bd
                 */
                $result = $this->getConnection();
				if (!$result["success"]) { throw new Exception($result["message"]); }
				$conn = $result["connection"];

                /**************************************
                 * Incrementando id
                 */
                $queryGetLastId = "SELECT MAX(" . PRODUCTO_SUBCATEGORIA_ID . ") AS impuestoID FROM " . TB_PRODUCTO_SUBCATEGORIA;
				$idCont = mysqli_query($conn, $queryGetLastId);
				if (!$idCont) {
					throw new Exception("Error al ejecutar la consulta");
				}
				$nextId = 1;
		
				// Calcula el siguiente ID para la nueva entrada
				if ($row = mysqli_fetch_row($idCont)) {
					$nextId = (int) trim($row[0]) + 1;
				}
                /*******************************************
                 * Generando Query
                 */
				$queryInsert = "INSERT INTO " . TB_PRODUCTO_SUBCATEGORIA. " ("
                    . PRODUCTO_SUBCATEGORIA_ID . ", "
                    . PRODUCTO_SUBCATEGORIA_PRODUCTO_ID . ", "
                    . PRODUCTO_SUBCATEGORIA_SUBCATEGORIA_ID . ", "
                    . PRODUCTO_SUBCATEGORIA_ESTADO ." "
                    . ") VALUES (?, ?, ?, true)";
				$stmt = mysqli_prepare($conn, $queryInsert);
				if (!$stmt) {
					throw new Exception("Error al preparar la consulta");
				}
		
				mysqli_stmt_bind_param(
					$stmt,
					'iii', // i: Entero, s: Cadena
					$nextId,
					$id_producto,
					$id_subcategoria
				);
		
				// Ejecuta la consulta de inserción
				$result = mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception("Error al insertar el producto sub categoria");
				}
				return ["success" => true, "message" => "producto subcategoria insertado exitosamente"];
            }catch (Exception $e) {
                // Devuelve el mensaje de error
				return ["success" => false, "message" => $e->getMessage()];
			} finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
        }
        function getALLProductoSubcategoria($page, $size, $sort = null){
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
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_PRODUCTO_SUBCATEGORIA . " WHERE " .PRODUCTO_SUBCATEGORIA_ESTADO . " != false";
                $totalResult = mysqli_query($conn, $queryTotalCount);
                if (!$totalResult) {
                    throw new Exception("Error al obtener el conteo total de registros: " . mysqli_error($conn));
                }
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = (int)$totalRow['total'];
                $totalPages = ceil($totalRecords / $size);

				/*******************************************
                 * Construir la consulta SQL para paginación
                 */

                $querySelect =" 
                SELECT 
                PS.".PRODUCTO_SUBCATEGORIA_ID.",
                PS.".PRODUCTO_SUBCATEGORIA_PRODUCTO_ID.",
                PS.".PRODUCTO_SUBCATEGORIA_SUBCATEGORIA_ID.",
                P.".PRODUCTO_NOMBRE.",
                S.".SUBCATEGORIA_NOMBRE.",
                PS.".PRODUCTO_SUBCATEGORIA_ESTADO."  
                FROM ".TB_PRODUCTO_SUBCATEGORIA." PS
                INNER JOIN ".TB_PRODUCTO." P ON PS.".PRODUCTO_SUBCATEGORIA_PRODUCTO_ID." = P.".PRODUCTO_ID." 
                INNER JOIN ".TB_SUBCATEGORIA." S ON PS.".PRODUCTO_SUBCATEGORIA_SUBCATEGORIA_ID." = S.".SUBCATEGORIA_ID."
                WHERE PS.".PRODUCTO_SUBCATEGORIA_ESTADO." != FALSE ";

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
               
				$listaProductosSubcategorias = [];
				while ($row = mysqli_fetch_assoc($result)) {
					$listaProductosSubcategorias[] = [
						'ID' => $row[PRODUCTO_SUBCATEGORIA_ID],
						'ProductoId' => $row[PRODUCTO_SUBCATEGORIA_PRODUCTO_ID],
						'SubcategoriaId' => $row[PRODUCTO_SUBCATEGORIA_SUBCATEGORIA_ID],
                        'NombreProducto' => $row[PRODUCTO_NOMBRE],
                        'NombreSubcategoria' =>  $row[SUBCATEGORIA_NOMBRE],
                        'Estado' => $row[PRODUCTO_SUBCATEGORIA_ESTADO]
					];
				}
				return [
                    "success" => true,
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "listaProductosSubcategorias" => $listaProductosSubcategorias
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

        function updateProductoSubcategoria($ProductoSubcategoria){
            try {
                /***************************************
                 * Validacion de campos necesarios
                 ***************************************/
                $id_producto_subcategoria = $ProductoSubcategoria->getIdProductoSubcategoria();
                $id_producto = $ProductoSubcategoria->getIdProducto();
                $id_subcategoria = $ProductoSubcategoria->getIdSubcategoria();
                
                if(empty($id_producto_subcategoria)){throw new Exception("Id del producto subcategoria vacio."); }
                if(empty($id_producto)){ throw new Exception("El id de producto no debe ser vacio."); }
                if(empty($id_subcategoria)){ throw new Exception("El id de la subcategoria no debe ser vacio."); }

                $relation = $this->Exists_Producto_Subcategoria_Relation($id_producto,$id_subcategoria, $id_producto_subcategoria, true);
                if($relation["exists"]){
                    return $relation;
                }

                $check = $this->Exists_Primary_Key($id_producto_subcategoria,TB_PRODUCTO_SUBCATEGORIA, PRODUCTO_SUBCATEGORIA_ID, PRODUCTO_SUBCATEGORIA_ESTADO);
                if(!$check["success"]){ throw new Exception("id subcategoria inexistente."); }

                $check = $this->Exists_Primary_Key($id_producto,TB_PRODUCTO, PRODUCTO_ID, PRODUCTO_ESTADO);
                if(!$check["success"]){ throw new Exception("id producto inexistente."); }

                $check = $this->Exists_Primary_Key($id_subcategoria,TB_SUBCATEGORIA, SUBCATEGORIA_ID, SUBCATEGORIA_ESTADO);
                if(!$check["success"]){ throw new Exception("id subcategoria inexistente."); }


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
                    "UPDATE ".TB_PRODUCTO_SUBCATEGORIA ." SET " . 
                        PRODUCTO_SUBCATEGORIA_PRODUCTO_ID. " = ?, " . 
                        PRODUCTO_SUBCATEGORIA_SUBCATEGORIA_ID." = ? ". 
                    "WHERE " .PRODUCTO_SUBCATEGORIA_ID. " = ?;";

                $stmt = mysqli_prepare($conn, $queryUpdate);
                if (!$stmt) { throw new Exception("Error al preparar la consulta"); }
                // Utils::writeLog("Id p :".$ProductoSubcategoria->getIdProducto()."  Id s :".$ProductoSubcategoria->getIdSubcategoria()."   PS: ".$ProductoSubcategoria->getIdProductoSubcategoria());
                mysqli_stmt_bind_param(
                    $stmt,
                    'iii', // s: Cadena, i: Entero
                    $ProductoSubcategoria->getIdProducto(),
                    $ProductoSubcategoria->getIdSubcategoria(),
                    $ProductoSubcategoria->getIdProductoSubcategoria()
                );

                /*********************************
                 * Ejecusion de la sentencia
                 *********************************/
                $result = mysqli_stmt_execute($stmt);
                if (!$result) { throw new Exception("¡Error al actualizar la producto subcategoria!"); }

                // Devuelve el resultado de la consulta
                return ["success" => true, "message" => "¡Producto subcategoria actualizada exitosamente!"];

            } catch (Exception $e) {
                // Devuelve el mensaje de error
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }
        function deleteProductoSubcategoria($id){
            try {
                /************************************
                 * Proceder a verificar si 
                 * existe el subcategoria a eliminar
                 ***********************************/
                if(empty($id) || $id<= 0){
                    throw new Exception("¡El id de la producto subcategoria esta vacio o no es valido!");
                }

                $exist = $this->Exists_Primary_Key($id,TB_PRODUCTO_SUBCATEGORIA,PRODUCTO_SUBCATEGORIA_ID,PRODUCTO_SUBCATEGORIA_ESTADO);
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
                $queryDelete = "UPDATE " . TB_PRODUCTO_SUBCATEGORIA. " SET " . PRODUCTO_SUBCATEGORIA_ESTADO . " = ? WHERE " . PRODUCTO_SUBCATEGORIA_ID . " = ?;";
                $stmt = mysqli_prepare($conn, $queryDelete);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta de eliminación.");
                }
        
                $subcategoriaEstado = false; //<- Para el borrado lógico
                mysqli_stmt_bind_param($stmt, 'ii', $subcategoriaEstado, $id);

                /********************************
                 * Ejecutando la sentencia
                 ********************************/
                $result = mysqli_stmt_execute($stmt);
                if (!$result) { throw new Exception("¡Error al eliminar la subcategoria!"); }
        
                // Devuelve el resultado de la operación
                return ["success" => true, "message" => "¡Subcategoria eliminado exitosamente!"];
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