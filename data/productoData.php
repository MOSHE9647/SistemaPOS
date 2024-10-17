<?php
    require_once dirname(__DIR__, 1) . '/data/data.php';
    require_once dirname(__DIR__, 1) . '/data/marcaData.php';
    require_once dirname(__DIR__, 1) . '/data/categoriaData.php';
    require_once dirname(__DIR__, 1) . '/data/codigoBarrasData.php';
    require_once dirname(__DIR__, 1) . '/data/subcategoriaData.php';
    require_once dirname(__DIR__, 1) . '/data/presentacionData.php';
    require_once dirname(__DIR__, 1) . '/domain/Producto.php';
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';
    require_once dirname(__DIR__, 1) . '/utils/Variables.php';

	class ProductoData extends Data {
        
        private $className;

        // Constructor
		public function __construct() {
			parent::__construct();
            $this->className = get_class($this);
        }

        private function productoExiste($productoID = null, $productoNombre = null, $productoCodigoBarrasID = null, $update = false, $insert = false) {
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
        
                // Inicializa la consulta base
                $queryCheck = "SELECT " . PRODUCTO_ID . ", " . PRODUCTO_ESTADO . " FROM " . TB_PRODUCTO . " WHERE (";
                $params = [];
                $types = "";
        
                // Consulta para verificar si existe un producto con el ID ingresado
                if ($productoID && (!$update && !$insert)) {
                    // Consultar: Verificar existencia por ID
                    $queryCheck .= PRODUCTO_ID . " = ?";
                    $params = [$productoID];
                    $types .= 'i';
                }

                // Consulta para verificar si existe un producto con el nombre o código de barras ingresado
                else if ($insert && ($productoNombre && $productoCodigoBarrasID)) {
                    // Insertar: Verificar existencia por nombre o código de barras
                    $queryCheck .= "(" . PRODUCTO_CODIGO_BARRAS_ID . " = ? AND " . PRODUCTO_NOMBRE . " != ?) ";
                    $queryCheck .= "OR (" . PRODUCTO_NOMBRE . " = ? AND " . PRODUCTO_CODIGO_BARRAS_ID . " != ?) ";
                    $queryCheck .= "OR (" . PRODUCTO_NOMBRE . " = ? AND " . PRODUCTO_CODIGO_BARRAS_ID . " = ?)";
                    $params = [$productoCodigoBarrasID, $productoNombre, $productoNombre, $productoCodigoBarrasID, $productoNombre, $productoCodigoBarrasID];
                    $types .= 'ssssss';
                } 
                // Consulta para actualizar: verificar nombre y código de barras
                else if ($update && (($productoNombre && $productoCodigoBarrasID) && $productoID)) {
                    // Actualizar: Excluir el ID actual
                    $queryCheck .= "(" . PRODUCTO_CODIGO_BARRAS_ID . " = ? AND " . PRODUCTO_NOMBRE . " != ? AND " . PRODUCTO_ID . " != ?) ";
                    $queryCheck .= "OR (" . PRODUCTO_NOMBRE . " = ? AND " . PRODUCTO_CODIGO_BARRAS_ID . " != ? AND " . PRODUCTO_ID . " != ?) ";
                    $queryCheck .= "OR (" . PRODUCTO_NOMBRE . " = ? AND " . PRODUCTO_CODIGO_BARRAS_ID . " = ? AND " . PRODUCTO_ID . " != ?)";
                    $params = [$productoCodigoBarrasID, $productoNombre, $productoID, $productoNombre, $productoCodigoBarrasID, $productoID, $productoNombre, $productoCodigoBarrasID, $productoID];
                    $types .= 'isisiisii';
                } 
                // En caso de no cumplirse ninguna condición
                else {
                    // Registrar parámetros faltantes y lanzar excepción
                    $missingParamsLog = "Faltan parámetros para verificar la existencia del producto:";
                    if (!$productoID) $missingParamsLog .= " productoID [" . ($productoID ?? 'null') . "]";
                    if (!$productoNombre) $missingParamsLog .= " productoNombre [" . ($productoNombre ?? 'null') . "]";
                    if (!$productoCodigoBarrasID) $missingParamsLog .= " productoCodigoBarrasID [" . ($productoCodigoBarrasID ?? 'null') . "]";
                    Utils::writeLog($missingParamsLog, DATA_LOG_FILE, WARN_MESSAGE, $this->className);
                    throw new Exception("Faltan parámetros para verificar la existencia del producto en la base de datos.");
                }
        
                $queryCheck .= ")";
                
                // Asignar los parámetros y ejecutar la consulta
                $stmt = mysqli_prepare($conn, $queryCheck);
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
        
                // Verificar si existe un producto con el ID, nombre o código de barras ingresado
                if ($row = mysqli_fetch_assoc($result)) {
                    // Verificar si está inactivo (bit de estado en 0)
                    $isInactive = $row[PRODUCTO_ESTADO] == 0;
                    return ["success" => true, "exists" => true, "inactive" => $isInactive, "productoID" => $row[PRODUCTO_ID]];
                }
        
                // Retorna false si no se encontraron resultados
                $messageParams = [];
                if ($productoID) { $messageParams[] = "'ID [$productoID]'"; }
                if ($productoNombre) { $messageParams[] = "'Nombre [$productoNombre]'"; }
                if ($productoCodigoBarrasID) { $messageParams[] = "'Código de Barras [$productoCodigoBarrasID]'"; }
                $params = implode(', ', $messageParams);
        
                $message = "No se encontró ningún producto ($params) en la base de datos.";
                return ["success" => true, "exists" => false, "message" => $message];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al verificar la existencia del producto en la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }        
        
        public function insertProducto($producto, $conn = null) {
            $createdConnection = false;
            $stmt = null;

            try {
                // Establece una conexión con la base de datos
                if ($conn === null) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConnection = true;

                    // Inicia una transacción
                    mysqli_begin_transaction($conn);
                }

                // Insertar el Código de Barras en la base de datos
                $productoCodigoBarras = $producto->getProductoCodigoBarras();
                $codigoBarrasData = new CodigoBarrasData();
                $result = $codigoBarrasData->insertCodigoBarras($productoCodigoBarras, $conn);
                if (!$result["success"]) { throw new Exception($result["message"]); }

                // Verifica si el código de barras está inactivo
                $productoCodigoBarras->setCodigoBarrasID($result["id"]);
                if ($result["inactive"]) {
                    // Reactiva el código de barras
                    $result = $codigoBarrasData->updateCodigoBarras($productoCodigoBarras, $conn);
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                }

                // Obtener los valores de las propiedades del objeto
                $codigoBarrasID = $productoCodigoBarras->getCodigoBarrasID();
                $productoID = $producto->getProductoID();
                $productoNombre = $producto->getProductoNombre();
        
                // Verifica si el producto ya existe
                $check = $this->productoExiste(null, $productoNombre, $codigoBarrasID, false, true);
                if (!$check["success"]) { throw new Exception($check["message"]); }

                // En caso de ya existir pero estar inactivo
                if ($check["exists"] && $check["inactive"]) {
                    $message = "Ya existe un producto con el mismo nombre ($productoNombre) en la base de datos, pero está inactivo. Desea reactivarlo?";
                    return ["success" => true, "message" => $message, "inactive" => $check["inactive"], "id" => $check["productoID"]];
                }

                // En caso de ya existir y estar activo
                if ($check["exists"]) {
                    $codigoBarrasNumero = $productoCodigoBarras->getCodigoBarrasNumero();
                    $message = "El producto 'Nombre [$productoNombre]' y 'CodigoBarrasID [$codigoBarrasID]' ya existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return [
                        "success" => false, 
                        "message" => "Ya existe un producto con el mismo nombre ($productoNombre) o código de barras ($codigoBarrasNumero) en la base de datos."
                    ];
                }
        
                // Obtenemos el último ID de la tabla tbproducto
                $queryGetLastId = "SELECT MAX(" . PRODUCTO_ID . ") FROM " . TB_PRODUCTO;
                $idCont = mysqli_query($conn, $queryGetLastId);
                $nextId = 1;
        
                // Calcula el siguiente ID para la nueva entrada
                if ($row = mysqli_fetch_row($idCont)) {
                    $nextId = (int) trim($row[0]) + 1;
                }

                // Procesa la imagen del producto
                $producto->setProductoID($nextId);
                $checkImagen = $this->procesarImagen($producto);
                if (!$checkImagen["success"]) { throw new Exception($checkImagen["message"]); }
        
                // Crea una consulta y un statement SQL para insertar el nuevo registro
                $queryInsert = 
                    "INSERT INTO " . TB_PRODUCTO . " ("
                        . PRODUCTO_ID . ", "
                        . PRODUCTO_CODIGO_BARRAS_ID . ", "
                        . PRODUCTO_NOMBRE . ", "
                        . PRODUCTO_PRECIO_COMPRA . ", "
                        . PRODUCTO_PORCENTAJE_GANANCIA . ", "
                        . PRODUCTO_DESCRIPCION . ", "
                        . PRODUCTO_CATEGORIA_ID . ", "
                        . PRODUCTO_SUBCATEGORIA_ID . ", "
                        . PRODUCTO_MARCA_ID . ", "
                        . PRODUCTO_PRESENTACION_ID . ", "
                        . PRODUCTO_IMAGEN
                    . ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $queryInsert);
        
                // Obtenemos los valores faltantes de las propiedades del objeto
                $productoPrecioCompra = $producto->getProductoPrecioCompra();
                $productoPorcentajeGanancia = $producto->getProductoPorcentajeGanancia();
                $productoDescripcion = $producto->getProductoDescripcion();
                $productoCategoriaID = $producto->getProductoCategoria()->getCategoriaID();
                $productoSubcategoriaID = $producto->getProductoSubcategoria()->getSubCategoriaID();
                $productoMarcaID = $producto->getProductoMarca()->getMarcaID();
                $productoPresentacionID = $producto->getProductoPresentacion()->getPresentacionID();
                $productoImagen = $producto->getProductoImagen();

                // Asigna los valores a cada '?' y ejecuta la consulta
                mysqli_stmt_bind_param(
                    $stmt,
                    'iisddsiiiis', // i: entero, s: Cadena, d: Decimal
                    $nextId,
                    $codigoBarrasID,
                    $productoNombre,
                    $productoPrecioCompra,
                    $productoPorcentajeGanancia,
                    $productoDescripcion,
                    $productoCategoriaID,
                    $productoSubcategoriaID,
                    $productoMarcaID,
                    $productoPresentacionID,
                    $productoImagen
                );
                $result = mysqli_stmt_execute($stmt);

                // Confirmar la transacción
                if ($createdConnection) { mysqli_commit($conn); }

                return ["success" => true, "message" => "Producto insertado exitosamente", "id" => $nextId];
            } catch (Exception $e) {
                if (isset($conn) && $createdConnection) { mysqli_rollback($conn); }

                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al insertar el producto en la base de datos',
                    $this->className
                );

                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn) && $createdConnection) { mysqli_close($conn); }
            }
        }
        
        public function updateProducto($producto, $conn = null) {
            $createdConnection = false;
            $stmt = null;

            try {
                // Obtener los valores de las propiedades del objeto
                $productoCodigoBarras = $producto->getProductoCodigoBarras();
                $codigoBarrasID = $productoCodigoBarras->getCodigoBarrasID();
                $productoNombre = $producto->getProductoNombre();
                $productoID = $producto->getProductoID();
        
                // Verifica si el producto ya existe en la base de datos
                $check = $this->productoExiste($productoID);
                if (!$check["success"]) { throw new Exception($check["message"]); }

                // En caso de no existir
                if (!$check["exists"]) {
                    $message = "El producto con 'ID [$productoID]' no existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => true, "message" => "El producto seleccionado no existe en la base de datos."];
                }

                // Verifica que no exista otro producto con la misma información
                $check = $this->productoExiste($productoID, $productoNombre, $codigoBarrasID, true);
                if (!$check["success"]) { throw new Exception($check["message"]); }

                // En caso de ya existir
                if ($check["exists"]) {
                    $codigoBarrasNumero = $productoCodigoBarras->getCodigoBarrasNumero();
                    $message = "El producto 'Nombre [$productoNombre]' y 'CodigoBarrasID [$codigoBarrasID]' ya existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return [
                        "success" => false, 
                        "message" => "Ya existe un producto con el mismo nombre ($productoNombre) o código de barras ($codigoBarrasNumero) en la base de datos."
                    ];
                }
        
                // Se procesa la imagen del producto
                $image = $this->procesarImagen($producto);
                if (!$image["success"]) { throw new Exception($image["message"]); }

                // Eliminala imagen anterior si se ha actualizado
                $this->eliminarImagenAntigua($producto);
        
                // Establece una conexión con la base de datos
                if ($conn === null) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConnection = true;

                    // Inicia una transacción
                    mysqli_begin_transaction($conn);
                }

                // Actualiza el Código de Barras asociado al producto
                $codigoBarrasData = new CodigoBarrasData();
                $result = $codigoBarrasData->updateCodigoBarras($productoCodigoBarras, $conn);
                if (!$result["success"]) { throw new Exception($result["message"]); }

                // Crea una consulta y un statement SQL para actualizar el registro
                $queryUpdate = 
                    "UPDATE " . TB_PRODUCTO . 
                    " SET " . 
                        PRODUCTO_CODIGO_BARRAS_ID . " = ?, " .
                        PRODUCTO_NOMBRE . " = ?, " .
                        PRODUCTO_PRECIO_COMPRA . " = ?, " .
                        PRODUCTO_PORCENTAJE_GANANCIA . " = ?, " .
                        PRODUCTO_DESCRIPCION . " = ?, " .
                        PRODUCTO_CATEGORIA_ID . " = ?, " .
                        PRODUCTO_SUBCATEGORIA_ID . " = ?, " .
                        PRODUCTO_MARCA_ID . " = ?, " .
                        PRODUCTO_PRESENTACION_ID . " = ?, " .
                        PRODUCTO_IMAGEN . " = ?, " .
                        PRODUCTO_ESTADO . " = TRUE " .
                    "WHERE " . PRODUCTO_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryUpdate);
        
                // Obtiene los valores faltantes de las propiedades del objeto
                $productoPrecioCompra = $producto->getProductoPrecioCompra();
                $productoPorcentajeGanancia = $producto->getProductoPorcentajeGanancia();
                $productoDescripcion = $producto->getProductoDescripcion();
                $productoCategoriaID = $producto->getProductoCategoria()->getCategoriaID();
                $productoSubcategoriaID = $producto->getProductoSubcategoria()->getSubCategoriaID();
                $productoMarcaID = $producto->getProductoMarca()->getMarcaID();
                $productoPresentacionID = $producto->getProductoPresentacion()->getPresentacionID();
                $productoImagen = $producto->getProductoImagen();

                // Asigna los valores a cada '?' de la consulta
                mysqli_stmt_bind_param(
                    $stmt,
                    'isddsiiiisi', // s: Cadena, i: Entero
                    $codigoBarrasID,
                    $productoNombre,
                    $productoPrecioCompra,
                    $productoPorcentajeGanancia,
                    $productoDescripcion,
                    $productoCategoriaID,
                    $productoSubcategoriaID,
                    $productoMarcaID,
                    $productoPresentacionID,
                    $productoImagen,
                    $productoID
                );
        
                // Ejecuta la consulta de actualización
                $result = mysqli_stmt_execute($stmt);

                // Confirmar la transacción
                if ($createdConnection) { mysqli_commit($conn); }

                return ["success" => true, "message" => "Producto actualizado exitosamente"];
            } catch (Exception $e) {
                if (isset($conn) && $createdConnection) { mysqli_rollback($conn); }

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
                if (isset($conn) && $createdConnection) { mysqli_close($conn); }
            }
        }
        
        public function deleteProducto($productoID, $conn = null) {
            $createdConnection = false;
            $stmt = null;

            try {
                // Verifica si el producto existe en la base de datos
                $check = $this->productoExiste($productoID);
                if (!$check["success"]) { throw new Exception($check["message"]); }

                // En caso de no existir
                if (!$check["exists"]) {
                    $message = "El producto con 'ID [$productoID]' no existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => true, "message" => "El producto seleccionado no existe en la base de datos."];
                }
        
                // Obtiene la información del producto
                $producto = $this->getProductoByID($productoID);
                if (!$producto["success"]) { throw new Exception($producto["message"]); }
                $producto = $producto["producto"];

                // Obtiene los valores de las propiedades del producto
                $codigoBarrasID = $producto->getProductoCodigoBarras()->getCodigoBarrasID();
                $rutaImagen = $producto->getProductoImagen();
        
                // Establece una conexión con la base de datos
                if ($conn === null) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConnection = true;

                    // Inicia una transacción
                    mysqli_begin_transaction($conn);
                }

                // Elimina el Código de Barras asociado al producto
                $codigoBarrasData = new CodigoBarrasData();
                $result = $codigoBarrasData->deleteCodigoBarras($codigoBarrasID, $conn);
                if (!$result["success"]) { throw new Exception($result["message"]); }
        
                // Crea una consulta y un statement SQL para eliminar el registro (borrado lógico)
                $queryDelete = "UPDATE " . TB_PRODUCTO . " SET " . PRODUCTO_ESTADO . " = FALSE WHERE " . PRODUCTO_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryDelete);
                mysqli_stmt_bind_param($stmt, 'i', $productoID);
        
                // Ejecuta la consulta de eliminación
                $result = mysqli_stmt_execute($stmt);

                // Eliminar la imagen actual del producto
                $rutaImagen = dirname(__DIR__) . $rutaImagen;
                if ($rutaImagen != DEFAULT_PRODUCT_IMAGE && file_exists($rutaImagen)) {
                    unlink($rutaImagen);
                }

                // Eliminar carpetas vacías
                $this->limpiarCarpetasVacias(dirname($rutaImagen), 'productos');
        
                // Confirmar la transacción
                if ($createdConnection) { mysqli_commit($conn); }

                // Devuelve el resultado de la operación
                return ["success" => true, "message" => "Producto eliminado exitosamente."];
            } catch (Exception $e) {
                // Revertir la transacción en caso de error
                if (isset($conn) && $createdConnection) { mysqli_rollback($conn); }

                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al eliminar el producto de la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn) && $createdConnection) { mysqli_close($conn); }
            }
        }

        public function getAllTBProductos($onlyActive = false, $deleted = false) {
            $conn = null;
            
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Construir la consulta SQL para obtener todos los productos activos
                $querySelect = "SELECT * FROM " . TB_PRODUCTO;
                if ($onlyActive) { $querySelect .= " WHERE " . PRODUCTO_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE'); }
                $result = mysqli_query($conn, $querySelect);

                // Crear la lista con los datos obtenidos
                $productos = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    // Obtiene el Codigo de Barras asociado al producto
                    $codigoBarrasData = new CodigoBarrasData();
                    $codigoBarras = $codigoBarrasData->getCodigoBarrasByID($row[PRODUCTO_CODIGO_BARRAS_ID], false);
                    if (!$codigoBarras["success"]) { throw new Exception($codigoBarras["message"]); }

                    // Obtiene la Categoría asociada al producto
                    $categoriaData = new CategoriaData();
                    $categoria = $categoriaData->getCategoriaByID($row[PRODUCTO_CATEGORIA_ID], false);
                    if (!$categoria["success"]) { throw new Exception($categoria["message"]); }

                    // Obtiene la Subcategoría asociada al producto
                    $subcategoriaData = new SubcategoriaData();
                    $subcategoria = $subcategoriaData->getSubcategoriaByID($row[PRODUCTO_SUBCATEGORIA_ID], false);
                    if (!$subcategoria["success"]) { throw new Exception($subcategoria["message"]); }

                    // Obtiene la Marca asociada al producto
                    $marcaData = new MarcaData();
                    $marca = $marcaData->getMarcaByID($row[PRODUCTO_MARCA_ID], false);
                    if (!$marca["success"]) { throw new Exception($marca["message"]); }

                    // Obtiene la Presentación asociada al producto
                    $presentacionData = new PresentacionData();
                    $presentacion = $presentacionData->getPresentacionByID($row[PRODUCTO_PRESENTACION_ID], false);
                    if (!$presentacion["success"]) { throw new Exception($presentacion["message"]); }

                    $producto = new Producto(
                        $row[PRODUCTO_ID],
                        $codigoBarras["codigoBarras"],
                        $row[PRODUCTO_NOMBRE],
                        $row[PRODUCTO_PRECIO_COMPRA],
                        $row[PRODUCTO_PORCENTAJE_GANANCIA],
                        $categoria["categoria"],
                        $subcategoria["subcategoria"],
                        $marca["marca"],
                        $presentacion["presentacion"],
                        $row[PRODUCTO_DESCRIPCION],
                        $row[PRODUCTO_IMAGEN],
                        $row[PRODUCTO_ESTADO]
                    );
                    $productos[] = $producto;
                }

                // Devolver la lista de productos
                return ["success" => true, "productos" => $productos];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener la lista de productos desde la base de datos',
                    $this->className
                );

                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cerramos la conexión
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function getPaginatedProductos($search, $page, $size, $sort = null, $onlyActive = false, $deleted = false) {
            $conn = null; $stmt = null;
            
            try {
                // Calcular el offset y el total de páginas
                $offset = ($page - 1) * $size;
        
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
        
                // Consultar el total de registros
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_PRODUCTO;
                if ($onlyActive) { $queryTotalCount .= " WHERE " . PRODUCTO_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE'); }

                // Obtener el total de registros y calcular el total de páginas
                $totalResult = mysqli_query($conn, $queryTotalCount);
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = (int) $totalRow['total'];
                $totalPages = ceil($totalRecords / $size);
        
                // Construir la consulta SQL para paginación
                $querySelect = "
                    SELECT 
                        P.*, C." . CODIGO_BARRAS_NUMERO . " 
                    FROM " . 
                        TB_PRODUCTO. " P 
                    INNER JOIN " . 
                        TB_CODIGO_BARRAS . " C ON P." . PRODUCTO_CODIGO_BARRAS_ID . " = C." . CODIGO_BARRAS_ID
                ;
                
                // Agregar filtro de búsqueda a la consulta
                $params = [];
                $types = "";
                if ($search) {
                    $querySelect .= " WHERE (" . PRODUCTO_NOMBRE . " LIKE ?";
                    $querySelect .= " OR " . CODIGO_BARRAS_NUMERO . " LIKE ?)";
                    $searchParam = "%" . $search . "%";
                    $params = [$searchParam, $searchParam];
                    $types .= "ss";
                }

                // Agregar filtro de estado a la consulta
                if ($onlyActive) { 
                    $querySelect .= $search ? " AND " : " WHERE ";
                    $querySelect .= PRODUCTO_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE"); 
                }

                // Agregar ordenamiento a la consulta
                if ($sort) { 
                    if ($sort === 'codigo') {
                        $querySelect .= " ORDER BY C." . CODIGO_BARRAS_NUMERO . " ";
                    } else {
                        $querySelect .= " ORDER BY P.producto" . $sort . " ";
                    }
                } else { 
                    $querySelect .= " ORDER BY " . PRODUCTO_ID . " DESC"; 
                }

                // Agregar límites a la consulta
                $querySelect .= " LIMIT ? OFFSET ?";
                $params = array_merge($params, [$size, $offset]);
                $types .= "ii";
        
                // Preparar la consulta y vincular los parámetros
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);
        
                // Ejecutar la consulta y obtener los resultados
                $result = mysqli_stmt_get_result($stmt);
        
                $productos = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    // Obtiene el Codigo de Barras asociado al producto
                    $codigoBarrasData = new CodigoBarrasData();
                    $codigoBarras = $codigoBarrasData->getCodigoBarrasByID($row[PRODUCTO_CODIGO_BARRAS_ID], false);
                    if (!$codigoBarras["success"]) { throw new Exception($codigoBarras["message"]); }

                    // Obtiene la Categoría asociada al producto
                    $categoriaData = new CategoriaData();
                    $categoria = $categoriaData->getCategoriaByID($row[PRODUCTO_CATEGORIA_ID], false);
                    if (!$categoria["success"]) { throw new Exception($categoria["message"]); }

                    // Obtiene la Subcategoría asociada al producto
                    $subcategoriaData = new SubcategoriaData();
                    $subcategoria = $subcategoriaData->getSubcategoriaByID($row[PRODUCTO_SUBCATEGORIA_ID], false);
                    if (!$subcategoria["success"]) { throw new Exception($subcategoria["message"]); }

                    // Obtiene la Marca asociada al producto
                    $marcaData = new MarcaData();
                    $marca = $marcaData->getMarcaByID($row[PRODUCTO_MARCA_ID], false);
                    if (!$marca["success"]) { throw new Exception($marca["message"]); }

                    // Obtiene la Presentación asociada al producto
                    $presentacionData = new PresentacionData();
                    $presentacion = $presentacionData->getPresentacionByID($row[PRODUCTO_PRESENTACION_ID], false);
                    if (!$presentacion["success"]) { throw new Exception($presentacion["message"]); }

                    $producto = new Producto(
                        $row[PRODUCTO_ID],
                        $codigoBarras["codigoBarras"],
                        $row[PRODUCTO_NOMBRE],
                        $row[PRODUCTO_PRECIO_COMPRA],
                        $row[PRODUCTO_PORCENTAJE_GANANCIA],
                        $categoria["categoria"],
                        $subcategoria["subcategoria"],
                        $marca["marca"],
                        $presentacion["presentacion"],
                        $row[PRODUCTO_DESCRIPCION],
                        $row[PRODUCTO_IMAGEN],
                        $row[PRODUCTO_ESTADO]
                    );
                    $productos[] = $producto;
                }
        
                return [
                    "success" => true,
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "productos" => $productos
                ];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener la lista de productos desde la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cerrar la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }
        
        public function getProductoByID($productoID, $onlyActive = true, $deleted = false) {
            $conn = null; $stmt = null;

            try {
                // Verificar si el producto existe en la base de datos
                $check = $this->productoExiste($productoID);
                if (!$check["success"]) { throw new Exception($check["message"]); }

                // En caso de no existir
                if (!$check["exists"]) {
                    $message = "El producto con 'ID [$productoID]' no existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                    return ["success" => true, "message" => "El producto seleccionado no existe en la base de datos."];
                }
        
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
        
                // Obtenemos la información del producto
                $querySelect = "
                    SELECT 
                        P.*
                    FROM " . 
                        TB_PRODUCTO . " P 
                    WHERE 
                        P." . PRODUCTO_ID . " = ?" . ($onlyActive ? " AND 
                        P." . PRODUCTO_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE') : '');
                $stmt = mysqli_prepare($conn, $querySelect);
        
                // Asigna los parámetros y ejecuta la consulta
                mysqli_stmt_bind_param($stmt, 'i', $productoID);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
        
                // Verifica si existe algún registro con los criterios dados
                if ($row = mysqli_fetch_assoc($result)) {
                    // Obtiene el Codigo de Barras asociado al producto
                    $codigoBarrasData = new CodigoBarrasData();
                    $codigoBarras = $codigoBarrasData->getCodigoBarrasByID($row[PRODUCTO_CODIGO_BARRAS_ID], false);
                    if (!$codigoBarras["success"]) { throw new Exception($codigoBarras["message"]); }

                    // Obtiene la Categoría asociada al producto
                    $categoriaData = new CategoriaData();
                    $categoria = $categoriaData->getCategoriaByID($row[PRODUCTO_CATEGORIA_ID], false);
                    if (!$categoria["success"]) { throw new Exception($categoria["message"]); }

                    // Obtiene la Subcategoría asociada al producto
                    $subcategoriaData = new SubcategoriaData();
                    $subcategoria = $subcategoriaData->getSubcategoriaByID($row[PRODUCTO_SUBCATEGORIA_ID], false);
                    if (!$subcategoria["success"]) { throw new Exception($subcategoria["message"]); }

                    // Obtiene la Marca asociada al producto
                    $marcaData = new MarcaData();
                    $marca = $marcaData->getMarcaByID($row[PRODUCTO_MARCA_ID], false);
                    if (!$marca["success"]) { throw new Exception($marca["message"]); }

                    // Obtiene la Presentación asociada al producto
                    $presentacionData = new PresentacionData();
                    $presentacion = $presentacionData->getPresentacionByID($row[PRODUCTO_PRESENTACION_ID], false);
                    if (!$presentacion["success"]) { throw new Exception($presentacion["message"]); }

                    $producto = new Producto(
                        $row[PRODUCTO_ID],
                        $codigoBarras["codigoBarras"],
                        $row[PRODUCTO_NOMBRE],
                        $row[PRODUCTO_PRECIO_COMPRA],
                        $row[PRODUCTO_PORCENTAJE_GANANCIA],
                        $categoria["categoria"],
                        $subcategoria["subcategoria"],
                        $marca["marca"],
                        $presentacion["presentacion"],
                        $row[PRODUCTO_DESCRIPCION],
                        $row[PRODUCTO_IMAGEN],
                        $row[PRODUCTO_ESTADO]
                    );
                    return ["success" => true, "producto" => $producto];
                }

                // En caso de que no se haya encontrado el producto
                $message = "No se encontró el producto con 'ID [$productoID]' en la base de datos.";
                Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ["success" => false, "message" => "No se encontró el producto seleccionado en la base de datos."];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener el producto de la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra la conexión y el statement
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        private function procesarImagen($producto, $formatosPermitidos = ['jpg', 'jpeg', 'png', 'webp']) {
            // Verificar si se ha cargado un archivo
            $imagen = $producto->getProductoImagen();
            if ($imagen['error'] !== UPLOAD_ERR_OK) {
                $this->asignarImagenPorDefecto($producto, DEFAULT_PRODUCT_IMAGE);
                return ['success' => true];
            }
        
            // Verificar extensión del archivo
            $extension = strtolower(pathinfo($imagen['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $formatosPermitidos)) {
                $formatos = implode(', ', $formatosPermitidos);
                return ['success' => false, 'message' => "Formato no permitido. Permitidos: $formatos."];
            }
        
            // Verificar si el archivo está correctamente cargado
            if (!is_uploaded_file($imagen['tmp_name'])) {
                return ['success' => false, 'message' => 'Error en la carga del archivo.'];
            }
        
            // Obtener la ruta de la imagen y el nombre del archivo
            $rutaImagen = $this->obtenerRutaImagen($producto, $extension);
            $rutaCompleta = dirname(__DIR__) . $rutaImagen;
        
            // Mover la imagen al destino
            if (!move_uploaded_file($imagen['tmp_name'], $rutaCompleta)) {
                return ['success' => false, 'message' => 'Error al mover la imagen.'];
            }
        
            // Convertir a WebP y redimensionar
            $rutaConvertida = $this->convertToWebP($rutaCompleta, 512, 512, 100);
            $producto->setProductoImagen(str_replace(dirname(__DIR__), '', $rutaConvertida));
        
            return ['success' => true, 'message' => 'Imagen guardada correctamente.'];
        }
        
        private function asignarImagenPorDefecto($producto, $rutaPorDefecto) {
            $producto->setProductoImagen($rutaPorDefecto);
            Utils::writeLog("Imagen por defecto asignada.", BUSINESS_LOG_FILE, INFO_MESSAGE, $this->className);
        }
        
        private function obtenerRutaImagen($producto, $extension) {
            $categoriaID = $producto->getProductoCategoria()->getCategoriaID();
            $subcategoriaID = $producto->getProductoSubcategoria()->getSubcategoriaID();
            $productoID = $producto->getProductoID();
            $nombreProducto = preg_replace('/[^a-zA-Z0-9]/', '_', $producto->getProductoNombre());
            $carpeta = 'productos/' . Utils::generarURLCarpetaImagen($categoriaID, $subcategoriaID);
            
            return Utils::crearRutaImagen($carpeta, "{$productoID}_{$nombreProducto}.{$extension}");
        }

        private function eliminarImagenAntigua($productoActualizar) {
            // Obtener la imagen actual del producto
            $productoEnBD = $this->getProductoByID($productoActualizar->getProductoID());
            if (!$productoEnBD["success"]) { return $productoEnBD; } // Error al obtener el producto de la BD

            // Verificar si la imagen actual es la misma que la nueva
            $rutaImagenEnBD = dirname(__DIR__) . $productoEnBD["producto"]->getProductoImagen();
            $rutaImagenNueva = dirname(__DIR__) . $productoActualizar->getProductoImagen();
            $imagenPorDefecto = dirname(__DIR__) . DEFAULT_PRODUCT_IMAGE;
            if ($rutaImagenNueva === $rutaImagenEnBD || $rutaImagenEnBD === $imagenPorDefecto) {
                return ["success" => true];
            }

            // Eliminar la imagen actual del producto
            if (file_exists($rutaImagenEnBD)) {
                unlink($rutaImagenEnBD);
            }

            // Eliminar carpetas vacías
            $this->limpiarCarpetasVacias(dirname($rutaImagenEnBD), 'productos');
            return ["success" => true];
        }

        private function limpiarCarpetasVacias($directorio, $limite) {
            // Limpiar carpetas recursivamente hasta el límite especificado
            while (is_dir($directorio) && $directorio !== $limite && count(glob("$directorio/*")) === 0) {
                rmdir($directorio); // Eliminar la carpeta si está vacía
                $directorio = dirname($directorio); // Subir un nivel en la jerarquía de carpetas
            }
        }
        
        private function convertToWebP($sourcePath, $newWidth = null, $newHeight = null, $quality = 80, $removeOriginal = true) {
            // Obtiene la información de la imagen original
            list($originalWidth, $originalHeight, $imageType) = getimagesize($sourcePath);
            $dir = pathinfo($sourcePath, PATHINFO_DIRNAME);
            $filename = pathinfo($sourcePath, PATHINFO_FILENAME);
            $destinationPath = $dir . DIRECTORY_SEPARATOR . $filename . '.webp';
        
            // Crea la imagen original según el tipo de imagen
            switch ($imageType) {
                case IMAGETYPE_JPEG:
                    $sourceImage = imagecreatefromjpeg($sourcePath);
                    break;
                case IMAGETYPE_PNG:
                    $sourceImage = imagecreatefrompng($sourcePath);
                    break;
                default:
                    return $sourcePath;
            }
        
            // Si no se especifican nuevas dimensiones, se mantienen las originales
            $newWidth = $newWidth ?? $originalWidth;
            $newHeight = $newHeight ?? $originalHeight;
        
            // Crea una nueva imagen redimensionada
            $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        
            // Mantiene la transparencia si es PNG
            if ($imageType == IMAGETYPE_PNG) {
                imagecolortransparent($resizedImage, imagecolorallocatealpha($resizedImage, 0, 0, 0, 127));
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
            }
        
            // Redimensiona la imagen original
            imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
        
            // Guarda la imagen en formato WebP
            imagewebp($resizedImage, $destinationPath, $quality);
        
            // Libera la memoria
            imagedestroy($sourceImage);
            imagedestroy($resizedImage);

            // Elimina la imagen original si se especifica
            if ($removeOriginal) { unlink($sourcePath); }
        
            // Devuelve la ruta de la imagen convertida eliminando la ruta base
            return $destinationPath;
        }
        
    }

?>