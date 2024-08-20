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

        private function productoExiste($productoID = null, $productoNombre = null, $productoCodigoBarras = null){
            try {
				// Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
                
				// Inicializa la consulta base
                $queryCheck = "SELECT * FROM " . TB_PRODUCTO . " WHERE ";
                $params = [];
                $types = "";
                
                if ($productoID !== null) {
                    // Verificar existencia por ID
                    $queryCheck .= PRODUCTO_ID . " = ? AND " . PRODUCTO_ESTADO . " != false";
                    $params[] = $productoID;
                    $types .= 'i';
                } else if ($productoNombre !== null && $productoCodigoBarras !== null) {
                    // Verificar existencia por nombre y codigo de barras
                    $queryCheck .= PRODUCTO_CODIGO_BARRAS . " = ? AND (" . PRODUCTO_NOMBRE . " = ? AND " . PRODUCTO_ESTADO . " != false)";
                    $params[] = $productoNombre;
                    $params[] = $productoCodigoBarras;
                    $types .= 'ss';
                } else {
                    $message = "No se proporcionaron los parámetros necesarios para verificar la existencia del producto";
					Utils::writeLog("$message. Parámetros: productoID [$productoID], productoNombre [$productoNombre], productoCodigoBarras [$productoCodigoBarras]", DATA_LOG_FILE);
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
                    'Error al verificar la existencia del producto en la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }
        
        public function insertProducto($producto){
            try{
                $productoID = $producto->getProductoID();
                $productoNombre = $producto->getProductoNombre();
                $productoPrecio = $producto->getProductoPrecio();
                $productoCantidad = $producto->getProductoCantidad();
                $productoFechaAdquisicion = $producto->getProductoFechaAdquisicion();
                $productoDescripcion = $producto->getProductoDescripcion();
                $productoCodigoBarras = $producto->getProductoCodigoBarras();

                // Verifica si el producto ya existe
				$check = $this->productoExiste(null, $productoNombre, $productoCodigoBarras);
				if (!$check["success"]) {
					return $check; // Error al verificar la existencia
				}
				if ($check["exists"]) {
					Utils::writeLog("El producto [Nombre: $productoNombre, Código: $productoCodigoBarras] ya existe en la base de datos.", DATA_LOG_FILE);
					throw new Exception("Ya existe un producto con el mismo nombre o código de barras.");
				}
                
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];

                // Obtenemos el último ID de la tabla tproducto
                $queryGetLastId = "SELECT MAX(" . PRODUCTO_ID . ") AS productID FROM " . TB_PRODUCTO;
                $idCont = mysqli_query($conn, $queryGetLastId);
                $nextId = 1;

                // Calcula el siguiente ID para la nueva entrada
				if ($row = mysqli_fetch_row($idCont)) {
					$nextId = (int) trim($row[0]) + 1;
				}
                
                // Crea una consulta y un statement SQL para insertar el nuevo registro
                $queryInsert = "INSERT INTO " . TB_PRODUCTO . " ("
                    . PRODUCTO_ID . ", "
                    . PRODUCTO_NOMBRE . ", "
                    . PRODUCTO_PRECIO_U . ", "
                    . PRODUCTO_CANTIDAD . ", "
                    . PRODUCTO_FECHA_ADQ . ", "
                    . PRODUCTO_DESCRIPCION . ", "
                    . PRODUCTO_CODIGO_BARRAS . ", "
                    . PRODUCTO_ESTADO
                    . ") VALUES (?, ?, ?, ?, ?, ?, ?, true)";
				$stmt = mysqli_prepare($conn, $queryInsert);

                // Asigna los valores a cada '?' de la consulta
                mysqli_stmt_bind_param(
                    $stmt,
                    'isdisss', // i: entero, s: Cadena
                    $nextId,
                    $productoNombre,
                    $productoPrecio,
                    $productoCantidad,
                    $productoFechaAdquisicion,
                    $productoDescripcion,
                    $productoCodigoBarras
                );
                
                // Ejecuta la consulta de inserción
                $result = mysqli_stmt_execute($stmt);
                return ["success" => true, "message" => "Producto insertado exitosamente"];
            }catch (Exception $e) {
				// Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al insertar el producto en la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
			} finally {
				// Cierra la conexión y el statement
				if (isset($stmt)) { mysqli_stmt_close($stmt); }
				if (isset($conn)) { mysqli_close($conn); }
			}
        }

        public function updateProducto($producto){
            try {
                // Obtener el ID del Producto
                $productoID = $producto->getProductoID();

                // Verifica si el producto ya existe
				$check = $this->productoExiste($productoID);
				if (!$check["success"]) {
					return $check; // Error al verificar la existencia
				}
				if (!$check["exists"]) {
					Utils::writeLog("El producto con ID [$productoID] no existe en la base de datos.", DATA_LOG_FILE);
					throw new Exception("No existe ningún producto en la base de datos que coincida con la información proporcionada.");
				}

                // Establece una conexion con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];

                // Crea una consulta y un statement SQL para actualizar el registro
                $queryUpdate = 
                    "UPDATE " . TB_PRODUCTO . 
                    " SET " . 
                        PRODUCTO_NOMBRE . " = ?, " . 
                        PRODUCTO_PRECIO_U . " = ?, " .
                        PRODUCTO_CANTIDAD . " = ?, " .
                        PRODUCTO_FECHA_ADQ . " = ?, " .                      
                        PRODUCTO_DESCRIPCION . " = ?, " .
                        PRODUCTO_CODIGO_BARRAS . " = ?, " .
                        PRODUCTO_ESTADO . " = true ". 
                    "WHERE " . PRODUCTO_ID . " = ?";

                $stmt = mysqli_prepare($conn, $queryUpdate);

                // Obtener los valores de las propiedades del objeto
                $productoNombre = $producto->getProductoNombre();
                $productoPrecio = $producto->getProductoPrecio();
                $productoCantidad = $producto->getProductoCantidad();
                $productoFechaAdquisicion = $producto->getProductoFechaAdquisicion();
                $productoDescripcion = $producto->getProductoDescripcion();
                $productoCodigoBarras = $producto->getProductoCodigoBarras();

                mysqli_stmt_bind_param(
                    $stmt,
                    'ssssssi', // s: Cadena, i: Entero
                    $productoNombre,
                    $productoPrecio,
                    $productoCantidad,
                    $productoFechaAdquisicion,
                    $productoDescripcion,
                    $productoCodigoBarras,
                    $productoID
                );

                // Ejecuta la consulta de actualización
				$result = mysqli_stmt_execute($stmt);

                // Devuelve el resultado de la consulta
                return ["success" => true, "message" => "Producto actualizado exitosamente"];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al actualizar el producto en la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function deleteProducto($productoID){
            try {
                // Verifica si el producto ya existe
				$check = $this->productoExiste($productoID);
				if (!$check["success"]) {
					return $check; // Error al verificar la existencia
				}
				if (!$check["exists"]) {
					Utils::writeLog("El producto con ID [$productoID] no existe en la base de datos.", DATA_LOG_FILE);
					throw new Exception("No existe ningún producto en la base de datos que coincida con la información proporcionada.");
				}

                // Establece una conexion con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                // Crea una consulta y un statement SQL para eliminar el registro (borrado logico)
                $queryDelete = "UPDATE " . TB_PRODUCTO . " SET " . PRODUCTO_ESTADO. " = false WHERE " . PRODUCTO_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryDelete);
                mysqli_stmt_bind_param($stmt, 'i', $productoID);

                // Ejecuta la consulta de eliminación
				$result = mysqli_stmt_execute($stmt);
        
                // Devuelve el resultado de la operación
                return ["success" => true, "message" => "Producto eliminado exitosamente."];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al eliminar el producto de la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function getProductoById($productoID) {
            try {
                // Verifica que el ID del producto sea válido
                if ($productoID === null || !is_numeric($productoID) || $productoID < 0) {
                    Utils::writeLog("El ID [$productoID] del producto no es válido.", DATA_LOG_FILE);
                    throw new Exception("El ID del producto está vacío o no es válido. Revise que este sea un número y que sea mayor a 0");
                }

                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                // Obtenemos la información del producto
                $querySelect = "SELECT * FROM " . TB_PRODUCTO . " WHERE " . PRODUCTO_ID . " = ? AND " . PRODUCTO_ESTADO . " != false";
                $stmt = mysqli_prepare($conn, $querySelect);
        
                // Asignar los parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, 'i', $productoID);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
        
                // Verifica si existe algún registro con los criterios dados
                $producto = null;
                if ($row = mysqli_fetch_assoc($result)) {
                    $producto = new Producto(
                        $row[PRODUCTO_ID],
                        $row[PRODUCTO_NOMBRE],
                        $row[PRODUCTO_PRECIO_U],
                        $row[PRODUCTO_CANTIDAD],
                        $row[PRODUCTO_FECHA_ADQ],
                        $row[PRODUCTO_DESCRIPCION],
                        $row[PRODUCTO_CODIGO_BARRAS],
                        $row[PRODUCTO_ESTADO]
                    );
                }
        
                return ["success" => true, "producto" => $producto];
        
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(),
                    $e->getMessage(),
                    'Error al obtener el producto de la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }        

        public function getAllProductos(){
            try {
                // Establece una conexion con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                // Obtenemos la lista de Productos
                $querySelect = "SELECT * FROM " . TB_PRODUCTO . " WHERE " . PRODUCTO_ESTADO . " != false;";
                $result = mysqli_query($conn, $querySelect);
        
                // Creamos la lista con los datos obtenidos
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
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al obtener la lista de productos desde la base de datos'
                );

                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cerramos la conexion
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function getPaginatedProductos($page, $size, $sort = null) {
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
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = (int) $totalRow['total'];
                $totalPages = ceil($totalRecords / $size);

				// Construir la consulta SQL para paginación
                $querySelect = "SELECT * FROM " . TB_PRODUCTO . " WHERE " . PRODUCTO_ESTADO . " != false ";

				// Añadir la cláusula de ordenamiento si se proporciona
                if ($sort) {
                    $querySelect .= "ORDER BY producto" . $sort . " ";
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

				$listaProductos = [];
				while ($row = mysqli_fetch_assoc($result)) {
					$listaProductos[] = [
                        'ID' => $row[PRODUCTO_ID],
                        'Nombre' => $row[PRODUCTO_NOMBRE],
                        'Precio' => $row[PRODUCTO_PRECIO_U],
                        'Cantidad' => $row[PRODUCTO_CANTIDAD],
                        'FechaISO' => Utils::formatearFecha($row[PRODUCTO_FECHA_ADQ], 'Y-MM-dd'),
						'Fecha' => Utils::formatearFecha($row[PRODUCTO_FECHA_ADQ]),
                        'Descripcion' => $row[PRODUCTO_DESCRIPCION],
                        'CodigoBarras' => $row[PRODUCTO_CODIGO_BARRAS],
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
				// Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al obtener la lista de productos desde la base de datos'
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