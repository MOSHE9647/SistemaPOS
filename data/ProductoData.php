<?php
    include_once 'data.php';
    include __DIR__ . '/../domain/Producto.php';
    require_once __DIR__ . '/../utils/Utils.php';
    require_once __DIR__ . '/../utils/Variables.php';

	class ProductoData extends Data {
        // Constructor
		public function __construct() {
			parent::__construct();
        }

        function VerificarExisteProducto($producto_id = null, $producto_nombre = null, $producto_Fecha = null){
            try {
                /******************************
                 * Conexion a la base de datos
                 ******************************/
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
                
                /****************************************
                * Generando sentencia
                *****************************************/
                $queryCheck = "SELECT * FROM " . TB_PRODUCTO . " WHERE ";
                $params = [];
                $types = "";
                
                if ($producto_id !== null) {
                    // Verificar existencia por ID y que el estado no sea false
                    $queryCheck .= PRODUCTO_ID . " = ? AND " . PRODUCTO_ESTADO . " != false";
                    $params[] = $producto_id;
                    $types .= 'i';
                } elseif ($producto_nombre !== null && $producto_Fecha !== null) {
                    // Verificar existencia por nombre y email
                    $queryCheck .= PRODUCTO_NOMBRE . " = ? AND (" . PRODUCTO_FECHA_ADQ. " = ? OR " . PRODUCTO_ESTADO . " != false)";
                    $params[] = $producto_nombre;
                    $params[] = $producto_Fecha;
                    $types .= 'ss';
                } else {
                    throw new Exception("Se requiere al menos un parámetro: productoID o productoNombre y productoEstado");
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

        function getAllProductos(){
            try {
                /************************************
                 * Conexión con la base de datos
                 ************************************/
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                /************************************
                 * Preparación de query para obtener
                 * los productos
                 ************************************/
                $querySelect = "SELECT * FROM " . TB_PRODUCTO . " WHERE " . PRODUCTO_ESTADO . " != false;";
                $result = mysqli_query($conn, $querySelect);
        
                // Verificamos si ocurrió un error
                if (!$result) {
                    throw new Exception("Ocurrió un error al ejecutar la consulta: " . mysqli_error($conn));
                }
        
                /************************************
                 * Obtención de datos
                 ************************************/
                $listaProductos = [];
                while ($row = mysqli_fetch_assoc($result)) {  // Usamos fetch_assoc para obtener un array asociativo
                    $currentProducto = new Producto(  
                        $row[PRODUCTO_NOMBRE],
                        $row[PRODUCTO_PRECIO_U],
                        $row[PRODUCTO_CANTIDAD],
                        $row[PRODUCTO_FECHA_ADQ],
                        $row[PRODUCTO_ID],
                        $row[PRODUCTO_DESCRIPCION],
                        $row[PRODUCTO_ESTADO]
                    );
                    array_push($listaProductos, $currentProducto);
                }
        
                return ["success" => true, "listaProductos" => $listaProductos];
            } catch (Exception $e) {
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        function getPaginatedProductos($page, $size, $sort = null) {
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
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_PRODUCTO . " WHERE " . PRODUCTO_ESTADO . " != false";
                $totalResult = mysqli_query($conn, $queryTotalCount);
                if (!$totalResult) {
                    throw new Exception("Error al obtener el conteo total de registros: " . mysqli_error($conn));
                }
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = (int)$totalRow['total'];
                $totalPages = ceil($totalRecords / $size);

				// Construir la consulta SQL para paginación
                $querySelect = "SELECT * FROM " . TB_PRODUCTO . " WHERE " . PRODUCTO_ESTADO . " != false ";

				// Añadir la cláusula de ordenamiento si se proporciona
                if ($sort) {
                    $querySelect .= "ORDER BY producto" . $sort . " ";
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

				$listaProductos = [];
				while ($row = mysqli_fetch_assoc($result)) {
					$listaProductos[] = [
                        'ID' => $row[PRODUCTO_ID],
                        'Nombre' => $row[PRODUCTO_NOMBRE],
                        'Descripcion' => $row[PRODUCTO_DESCRIPCION],
                        'Precio' => $row[PRODUCTO_PRECIO_U],
                        'Cantidad' => $row[PRODUCTO_CANTIDAD],
                        'FechaISO' => Utils::formatearFecha($row[PRODUCTO_FECHA_ADQ], 'Y-MM-dd'),
						'Fecha' => Utils::formatearFecha($row[PRODUCTO_FECHA_ADQ]),
                        'Estado' => $row[PRODUCTO_ESTADO]
					];
				}

				return [
                    "success" => true,
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "listaProductos" => $listaProductos
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
        
        function updateProducto($producto){
            try {
                /***************************************
                 * Validacion de campos necesarios
                 ***************************************/
                $idproducto = $producto->getIdProducto();
                $nombre = $producto->getNombreProducto();
                $fechaadquisicionproducto = $producto->getFechaAdquisicion();
                $productoestado = $producto->getEstadoProducto();
                if(empty($idproducto) || $idproducto <= 0){
                    throw new Exception("El id del producto esta vacio o no es valido");
                }
                if(empty($nombre)){
                    throw new Exception("El nombre del producto esta vacio");
                }
                if (empty($fechaadquisicionproducto) || !Utils::validar_fecha($fechaadquisicionproducto)) {
                    throw new Exception("La fecha de adquisicion está vacía o no es válida");
                }
                if ($productoestado === null || empty( $productoestado)) {
                    throw new Exception("El estado del producto no puede estar vacío");
                }
				if (!Utils::fechaMenorOIgualAHoy($fechaadquisicionproducto)) {
					throw new Exception("La fecha de adquisicion debe ser menor o igual a la fecha actual");
				}
                /***********************************
                 * Conexion a la base de datos 
                 ***********************************/
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];

                /***********************************
                 * Creando sentencia update
                 ***********************************/
                $queryUpdate = 
                    "UPDATE " . TB_PRODUCTO . 
                    " SET " . 
                        PRODUCTO_NOMBRE. " = ?, " . 
                        PRODUCTO_DESCRIPCION. " = ?, " .
                        PRODUCTO_FECHA_ADQ . " = ?, " .                      
                        PRODUCTO_PRECIO_U . " = ?, " .
                        PRODUCTO_CANTIDAD. " = ?, " .
                        PRODUCTO_ESTADO." = ? ". 
                    "WHERE " . PRODUCTO_ID. " = ?";

                $stmt = mysqli_prepare($conn, $queryUpdate);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta");
                }

                mysqli_stmt_bind_param(
                    $stmt,
                    'sssdisi', // s: Cadena, i: Entero
                    $producto->getNombreProducto(),
                    $producto->getDescripcionProducto(),
                    $producto->getFechaAdquisicion(),
                    $producto->getPrecioUnitarioProducto(),
                    $producto->getCantidadProducto(),
                    $producto->getEstadoProducto(),
                    $producto->getIdProducto()
                );

                /*********************************
                 * Ejecusion de la sentencia
                 *********************************/
                $result = mysqli_stmt_execute($stmt);
                if (!$result) {
                    throw new Exception("Error al actualizar el producto");
                }

                // Devuelve el resultado de la consulta
                return ["success" => true, "message" => "Producto actualizado exitosamente"];

            } catch (Exception $e) {
                // Devuelve el mensaje de error
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }
        function insertProducto($producto){
            try{
                /***************************************
                 * Validacion de campos necesarios
                 ***************************************/
                $nombre = $producto->getNombreProducto();
                $fechaadquisicionproducto = $producto->getFechaAdquisicion();
                $productoestado = $producto->getEstadoProducto();
                if(empty($nombre)){
                    throw new Exception("El nombre del producto esta vacio");
                }
                if (empty($fechaadquisicionproducto) || !Utils::validar_fecha($fechaadquisicionproducto)) {
                    throw new Exception("La fecha de adquisicion está vacía o no es válida");
                }
                if ($productoestado === null || empty( $productoestado)) {
                    throw new Exception("El estado del producto no puede estar vacío");
                }
            
				if (!Utils::fechaMenorOIgualAHoy($fechaadquisicionproducto)) {
					throw new Exception("La fecha de adquisicion debe ser menor o igual a la fecha actual");
				}
                /******************************************* 
                * Proceder a conexion con la base de datos
                ********************************************/
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
                /***********************************************
                * Obtenemos el último ID de la tabla tbproveedor
                ************************************************/
                $queryGetLastId = "SELECT MAX(" . PRODUCTO_ID . ") AS productID FROM " . TB_PRODUCTO;
                $idCont = mysqli_query($conn, $queryGetLastId);
                if (!$idCont) {
                    throw new Exception("Error al ejecutar la consulta");
                }
                $nextId = 1;//incrementando el id

                // Calcula el siguiente ID para la nueva entrada
				if ($row = mysqli_fetch_row($idCont)) {
					$nextId = (int) trim($row[0]) + 1;
				}
                /*****************************************************
                 * Creando sentencia para insertar el producto
                 *****************************************************/
                $queryInsert = "INSERT INTO " . TB_PRODUCTO . " VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $queryInsert);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta");
                }
                mysqli_stmt_bind_param(
                    $stmt,
                    'isdisss', // i: entero, s: Cadena
                    $nextId,
                    $producto->getNombreProducto(), 
                    $producto->getPrecioUnitarioProducto(),
                    $producto->getCantidadProducto(),
                    $producto->getFechaAdquisicion(),
                    $producto->getDescripcionProducto(),
                    $producto->getEstadoProducto()                
                );
                /**************************************
                 * Ejecucion de la sentencia insertar
                 **************************************/
                $result = mysqli_stmt_execute($stmt);
                if (!$result) {
                    throw new Exception("Error al insertar el producto");
                }
                return ["success" => true, "message" => "Producto insertado exitosamente"];
            }catch (Exception $e) {
				// Devuelve el mensaje de error
				return ["success" => false, "message" => $e->getMessage()];
			} finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
        }
        function deleteProducto($id){
            try {
                /************************************
                 * Proceder a verificar si 
                 * existe el producto a eliminar
                 ***********************************/
                if(empty($id) || $id<= 0){
                    throw new Exception("El id del producto esta vacio o no es valido");
                }

                $exist = $this->VerificarExisteProducto($id);
                if(!$exist["exists"]){
                    throw new Exception("No existe el producto a eliminar");
                }

                /******************************
                 * Conexion a la base de datos
                 ******************************/
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                /*********************************
                 * Creando sentencia de 
                 * eliminado logico
                 *********************************/
                $queryDelete = "UPDATE " . TB_PRODUCTO . " SET " . PRODUCTO_ESTADO. " = ? WHERE " . PRODUCTO_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryDelete);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta de eliminación.");
                }
        
                $productoEstado = false; //<- Para el borrado lógico
                mysqli_stmt_bind_param($stmt, 'ii', $productoEstado, $id);

                /********************************
                 * Ejecutando la sentencia
                 ********************************/
                $result = mysqli_stmt_execute($stmt);
                if (!$result) {
                    throw new Exception("Error al eliminar el producto.");
                }
        
                // Devuelve el resultado de la operación
                return ["success" => true, "message" => "producto eliminado exitosamente."];
            } catch (Exception $e) {
                // Devuelve el mensaje de error
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }
        function getProductoById(){

        }
    }

?>