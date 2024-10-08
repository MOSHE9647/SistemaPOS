<?php
    require_once 'data.php';
    require_once __DIR__ . '/../domain/Producto.php';
    require_once __DIR__ . '/../utils/Utils.php';
    require_once __DIR__ . '/../utils/Variables.php';

	class ProductoData extends Data {
        
        // Constructor
		public function __construct() {
			parent::__construct();
        }

        private function productoExiste($productoID = null, $productoNombre = null, $productoCodigoBarras = null, $update = false) {
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
        
                if ($productoID !== null && !$update) {
                    // Verificar existencia por ID
                    $queryCheck .= PRODUCTO_ID . " = ? AND " . PRODUCTO_ESTADO . " != false";
                    $params[] = $productoID;
                    $types .= 'i';
                } else if ($productoNombre !== null && $productoCodigoBarras !== null) {
                    // Verificar existencia por nombre y codigo de barras
                    $queryCheck .= PRODUCTO_NOMBRE . " = ? OR (" . PRODUCTO_CODIGO_BARRAS_ID . " = ? AND " . PRODUCTO_ESTADO . " != false)";
                    $params[] = $productoNombre;
                    $params[] = $productoCodigoBarras;
                    $types .= 'ss';
        
                    if ($productoID !== null) {
                        $queryCheck .= " AND " . PRODUCTO_ID . " <> ?";
                        $params[] = $productoID;
                        $types .= 'i';
                    }
                } else {
                    $message = "No se proporcionaron los parámetros necesarios para verificar la existencia del producto";
                    Utils::writeLog("$message. Parámetros: 'productoID [$productoID]', 'productoNombre [$productoNombre]', 'productoCodigoBarras [$productoCodigoBarras]'", DATA_LOG_FILE);
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
        
        
        public function insertProducto($producto) {
            try {
                // Obtener los valores de las propiedades del objeto
                $productoID = $producto->getProductoID();
                $productoNombre = $producto->getProductoNombre();
                $productoPrecioCompra = $producto->getProductoPrecioCompra();
                $productoDescripcion = $producto->getProductoDescripcion();
                $productoCodigoBarrasID = $producto->getProductoCodigoBarrasID();
                $productoImagen = $producto->getProductoImagen();
                $productoPorcentajeGanancia = $producto->getPorcentajeGanancia();
                $productoEstado = $producto->getProductoEstado(); // Añadido para el estado del producto
        
                // **NUEVO** Verifica que la ruta de la imagen exista antes de insertarla
                if (!file_exists($productoImagen)) {
                    return ["success" => false, "message" => "La imagen no existe en el servidor"];
                }
        
                // Verifica si el producto ya existe
                $check = $this->productoExiste(null, $productoNombre, $productoCodigoBarrasID);
                if (!$check["success"]) {
                    return $check; // Error al verificar la existencia
                }
                if ($check["exists"]) {
                    Utils::writeLog("El producto [Nombre: $productoNombre, Código: $productoCodigoBarrasID] ya existe en la base de datos.", DATA_LOG_FILE);
                    throw new Exception("Ya existe un producto con el mismo nombre o código de barras.");
                }
        
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                // Obtenemos el último ID de la tabla tbproducto
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
                    . PRODUCTO_PRECIO_COMPRA . ", "
                    . PRODUCTO_DESCRIPCION . ", "
                    . PRODUCTO_CODIGO_BARRAS_ID . ", "
                    . PRODUCTO_IMAGEN . ", "
                    . PRODUCTO_PORCENTAJE_GANANCIA . ", "
                    . PRODUCTO_ESTADO
                    . ") VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $queryInsert);
        
                // Asigna los valores a cada '?' de la consulta
                mysqli_stmt_bind_param(
                    $stmt,
                    'isdsisds', // i: entero, s: Cadena
                    $nextId,
                    $productoNombre,
                    $productoPrecioCompra,
                    $productoDescripcion,
                    $productoCodigoBarrasID,
                    $productoImagen,
                    $productoPorcentajeGanancia,
                    $productoEstado
                );
        
                // Ejecuta la consulta de inserción
                $result = mysqli_stmt_execute($stmt);
                return ["success" => true, "message" => "Producto insertado exitosamente", "id"=>$nextId];
            } catch (Exception $e) {
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
        
        
        public function updateProducto($producto) {
            try {
                // Obtener el ID del Producto
                $productoID = $producto->getProductoID();
                $productoNombre = $producto->getProductoNombre();
                $productoPrecioCompra = $producto->getProductoPrecioCompra();
                $productoDescripcion = $producto->getProductoDescripcion();
                $productoCodigoBarrasID = $producto->getProductoCodigoBarrasID();
                $productoImagen = $producto->getProductoImagen();
                $productoPorcentajeGanancia = $producto->getPorcentajeGanancia();
                $productoEstado = $producto->getProductoEstado(); // Añadido para el estado del producto
        
                // Verifica si el producto ya existe
                $check = $this->productoExiste($productoID);
                if (!$check["success"]) {
                    return $check; // Error al verificar la existencia
                }
                if (!$check["exists"]) {
                    Utils::writeLog("El producto con ID [$productoID] no existe en la base de datos.", DATA_LOG_FILE);
                    throw new Exception("No existe ningún producto en la base de datos que coincida con la información proporcionada.");
                }
        
                // Establece una conexión con la base de datos
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
                        PRODUCTO_PRECIO_COMPRA . " = ?, " .                     
                        PRODUCTO_DESCRIPCION . " = ?, " .
                        PRODUCTO_CODIGO_BARRAS_ID . " = ?, " .
                        PRODUCTO_IMAGEN . " = ?, " .
                        PRODUCTO_PORCENTAJE_GANANCIA . " = ?, " .
                        PRODUCTO_ESTADO . " = ? " .
                    "WHERE " . PRODUCTO_ID . " = ?";
        
                $stmt = mysqli_prepare($conn, $queryUpdate);
        
                // Asigna los valores a cada '?' de la consulta
                mysqli_stmt_bind_param(
                    $stmt,
                    'sdsisdsi', // s: Cadena, i: Entero
                    $productoNombre,
                    $productoPrecioCompra,
                    $productoDescripcion,
                    $productoCodigoBarrasID,
                    $productoImagen,
                    $productoPorcentajeGanancia,
                    $productoEstado,
                    $productoID
                );
        
                // Ejecuta la consulta de actualización
                $result = mysqli_stmt_execute($stmt);
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
        
        public function deleteProducto($productoID) {
            try {
                // Verifica si el producto ya existe
                $check = $this->productoExiste($productoID);
                if (!$check["success"]) {
                    return $check; // Error al verificar la existencia
                }
                if (!$check["exists"]) {
                    throw new Exception("No existe ningún producto en la base de datos que coincida con la información proporcionada.");
                }
        
                // Obtiene la imagen del producto para poder eliminarla
                $producto = $this->getProductoByID($productoID); // Asegúrate de tener este método
                $rutaImagen = $producto->getProductoImagen();
        
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                // Crea una consulta y un statement SQL para eliminar el registro (borrado lógico)
                $queryDelete = "UPDATE " . TB_PRODUCTO . " SET " . PRODUCTO_ESTADO . " = false WHERE " . PRODUCTO_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryDelete);
                mysqli_stmt_bind_param($stmt, 'i', $productoID);
        
                // Ejecuta la consulta de eliminación
                $result = mysqli_stmt_execute($stmt);
        
                // **NUEVO** Elimina la imagen del servidor si existe
                if (file_exists($rutaImagen)) {
                    unlink($rutaImagen); // Elimina el archivo de la imagen
                }
        
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
        
                // Verifica si el producto ya existe
                $check = $this->productoExiste($productoID);
                if (!$check["success"]) {
                    return $check; // Error al verificar la existencia
                }
                if (!$check["exists"]) {
                    throw new Exception("No existe ningún producto en la base de datos que coincida con la información proporcionada.");
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
        
                // Asigna los parámetros y ejecuta la consulta
                mysqli_stmt_bind_param($stmt, 'i', $productoID);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
        
                // Verifica si existe algún registro con los criterios dados
                $producto = null;
                if ($row = mysqli_fetch_assoc($result)) {
                    $producto = new Producto(
                        $row[PRODUCTO_ID], 
                        $row[PRODUCTO_CODIGO_BARRAS_ID],
                        $row[PRODUCTO_NOMBRE],
                        $row[PRODUCTO_PRECIO_COMPRA], // Cambié PRODUCTO_PRECIO_U a PRODUCTO_PRECIO_COMPRA
                        $row[PRODUCTO_PORCENTAJE_GANANCIA],
                        $row[PRODUCTO_DESCRIPCION],
                        $row[PRODUCTO_CATEGORIA_ID],
                        $row[PRODUCTO_SUBCATEGORIA_ID],
                        $row[PRODUCTO_MARCA_ID],
                        $row[PRODUCTO_PRESENTACION_ID],
                        $row[PRODUCTO_IMAGEN],
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
        
        
        
        public function getAllTBCompraDetalleProducto(){
            $response = [];
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                // Obtenemos la lista de Productos
                $querySelect = "SELECT " . PRODUCTO_ID . ", "  . PRODUCTO_NOMBRE . " FROM " . TB_PRODUCTO . " WHERE " . PRODUCTO_ESTADO . " != false;";
                $result = mysqli_query($conn, $querySelect);
        
                // Creamos la lista con los datos obtenidos
                $listaCompraDetalleProducto = [];
                while ($row = mysqli_fetch_assoc($result)) {  // Usamos fetch_assoc para obtener un array asociativo
                    $listaCompraDetalleProducto[] = [
                        'ID' => $row[PRODUCTO_ID], // Asegúrate de incluir el ID en el constructor si es necesario
                        'ProductoNombre' => $row[PRODUCTO_NOMBRE],
                    ];
                }
        
                return ["success" => true, "listaCompraDetalleProducto" => $listaCompraDetalleProducto];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al obtener la lista de productos desde la base de datos'
                );
        
                 // Devolver mensaje amigable para el usuario
        $response = ["success" => false, "message" => $userMessage];
    } finally {
        // Cerramos la conexión
        if (isset($conn)) { mysqli_close($conn); }
    }
    return $response;
}
        
public function getAllProductos() {
    try {
        // Establece una conexión con la base de datos
        $result = $this->getConnection();
        if (!$result["success"]) {
            throw new Exception($result["message"]);
        }
        $conn = $result["connection"];

        // Construir la consulta SQL para obtener todos los productos activos
        $querySelect = "
        SELECT 
            p." . PRODUCTO_ID . ", 
            cb.codigobarrasnumero codigoBarrasNumero,
            p." . PRODUCTO_NOMBRE . ", 
            p." . PRODUCTO_PRECIO_COMPRA . ", 
            p." . PRODUCTO_PORCENTAJE_GANANCIA . ", 
            p." . PRODUCTO_DESCRIPCION . ", 
            c.categorianombre AS categoriaNombre,
            s.subcategorianombre AS subCategoriaNombre,
            m.marcanombre AS marcaNombre,
            pr.presentacionnombre AS presentacionNombre,
            p." . PRODUCTO_IMAGEN . ", 
            p." . PRODUCTO_ESTADO . "
        FROM " . TB_PRODUCTO . " p
        JOIN tbcodigobarras cb ON p." . PRODUCTO_CODIGO_BARRAS_ID . " = cb.codigobarrasid 
        JOIN tbcategoria c ON p." . PRODUCTO_CATEGORIA_ID . " = c.categoriaid 
        JOIN tbsubcategoria s ON p." . PRODUCTO_SUBCATEGORIA_ID . " = s.subcategoriaid
        JOIN tbmarca m ON p." . PRODUCTO_MARCA_ID . " = m.marcaid
        JOIN tbpresentacion pr ON p." . PRODUCTO_PRESENTACION_ID . " = pr.presentacionid
        WHERE p." . PRODUCTO_ESTADO . " != false;
        ";

        $result = mysqli_query($conn, $querySelect);

        // Crear la lista con los datos obtenidos
        $listaProductos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $currentProducto = [
                $row[PRODUCTO_ID],
                $row["codigoBarrasNumero"],
                $row[PRODUCTO_NOMBRE],
                $row[PRODUCTO_PRECIO_COMPRA],
                $row[PRODUCTO_PORCENTAJE_GANANCIA],
                $row[PRODUCTO_DESCRIPCION],
                $row['categoriaNombre'],
                $row['subCategoriaNombre'],
                $row['marcaNombre'],
                $row['presentacionNombre'],
                $row[PRODUCTO_IMAGEN],
                $row[PRODUCTO_ESTADO],
            ];
            array_push($listaProductos, $currentProducto);
        }
        error_log('Productos obtenidos: ' . print_r($listaProductos, true)); // Registro para depurar
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
        // Cerramos la conexión
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
                $querySelect = "
                 SELECT 
                 p." . PRODUCTO_ID . ",
                 cb.codigobarrasnumero AS codigoBarrasNumero,
                 p." . PRODUCTO_NOMBRE . ", 
                 p." . PRODUCTO_PRECIO_COMPRA . ", 
                 p." . PRODUCTO_PORCENTAJE_GANANCIA . ", 
                 p." . PRODUCTO_DESCRIPCION . ", 
                 c.categorianombre AS categoriaNombre,
                s.subcategorianombre AS subCategoriaNombre,
                m.marcanombre AS marcaNombre,
                pr.presentacionnombre AS presentacionNombre,
                p." . PRODUCTO_IMAGEN . ", 
                p." . PRODUCTO_ESTADO . "
        FROM " . TB_PRODUCTO . " p
        JOIN tbcodigobarras cb ON p." . PRODUCTO_CODIGO_BARRAS_ID . " = cb.codigobarrasid 
        JOIN tbcategoria c ON p." . PRODUCTO_CATEGORIA_ID . " = c.categoriaid 
        JOIN tbsubcategoria s ON p." . PRODUCTO_SUBCATEGORIA_ID . " = s.subcategoriaid
        JOIN tbmarca m ON p." . PRODUCTO_MARCA_ID . " = m.marcaid
        JOIN tbpresentacion pr ON p." . PRODUCTO_PRESENTACION_ID . " = pr.presentacionid
        WHERE p." . PRODUCTO_ESTADO . " != false 
        ";
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
                    'CodigoBarrasNumero' => $row['codigoBarrasNumero'],
                    'ProductoNombre' => $row[PRODUCTO_NOMBRE],
                    'PrecioCompra' => $row[PRODUCTO_PRECIO_COMPRA],
                    'ProductoPorcentajeGanancia' => $row[PRODUCTO_PORCENTAJE_GANANCIA],
                    'ProductoDescripcion' => $row[PRODUCTO_DESCRIPCION],
                    'CategoriaNombre' => $row['categoriaNombre'],
                    'SubCategoriaNombre' => $row['subCategoriaNombre'],
                    'MarcaNombre' => $row['marcaNombre'],
                    'PresentacionNombre' => $row['presentacionNombre'],
                    'ProductoImagen' => $row[PRODUCTO_IMAGEN],
                    'ProductoEstado' => $row[PRODUCTO_ESTADO]
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