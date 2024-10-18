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
        
        // Nombre de la clase
        private $className;

        /**
         * Inicializa una nueva instancia de la clase ProductoData.
         */
		public function __construct() {
			parent::__construct();
            $this->className = get_class($this);
        }

        /**
         * Verifica la existencia de un producto en la base de datos.
         *
         * Este método permite verificar si un producto existe en la base de datos
         * utilizando diferentes criterios como el ID del producto, el nombre del producto
         * o el código de barras del producto. Dependiendo de los parámetros proporcionados,
         * la función puede verificar la existencia para operaciones de consulta, inserción o actualización.
         *
         * @param int|null $productoID El ID del producto (opcional).
         * @param string|null $productoNombre El nombre del producto (opcional).
         * @param int|null $productoCodigoBarrasID El ID del código de barras del producto (opcional).
         * @param bool $update Indica si se está realizando una operación de actualización (opcional).
         * @param bool $insert Indica si se está realizando una operación de inserción (opcional).
         * @return array Un arreglo asociativo con el resultado de la verificación:
         *               - "success" (bool): Indica si la operación fue exitosa.
         *               - "exists" (bool): Indica si el producto existe en la base de datos.
         *               - "inactive" (bool, opcional): Indica si el producto está inactivo (solo si existe).
         *               - "productoID" (int, opcional): El ID del producto (solo si existe).
         *               - "message" (string, opcional): Mensaje de error o información adicional.
         * @throws Exception Si ocurre un error durante la verificación.
         */
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
        
        /**
         * Inserta un nuevo producto en la base de datos.
         *
         * Este método permite insertar un nuevo producto en la base de datos. 
         * Verifica si el producto ya existe y maneja la reactivación de productos inactivos.
         * También procesa la imagen del producto y maneja la transacción de la inserción.
         *
         * @param Producto $producto El objeto Producto a insertar.
         * @param mysqli|null $conn La conexión a la base de datos (opcional).
         * @return array Un arreglo asociativo con el resultado de la inserción:
         *               - "success" (bool): Indica si la operación fue exitosa.
         *               - "message" (string): Mensaje de error o información adicional.
         *               - "id" (int, opcional): El ID del producto insertado (solo si la operación fue exitosa).
         *               - "inactive" (bool, opcional): Indica si el producto estaba inactivo (solo si ya existía).
         * @throws Exception Si ocurre un error durante la inserción.
         */
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
                        . PRODUCTO_CANTIDAD . ", "
                        . PRODUCTO_PRECIO_COMPRA . ", "
                        . PRODUCTO_PORCENTAJE_GANANCIA . ", "
                        . PRODUCTO_DESCRIPCION . ", "
                        . PRODUCTO_CATEGORIA_ID . ", "
                        . PRODUCTO_SUBCATEGORIA_ID . ", "
                        . PRODUCTO_MARCA_ID . ", "
                        . PRODUCTO_PRESENTACION_ID . ", "
                        . PRODUCTO_IMAGEN . ', '
                        . PRODUCTO_FECHA_VENCIMIENTO
                    . ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $queryInsert);
        
                // Obtenemos los valores faltantes de las propiedades del objeto
                $productoCantidad = $producto->getProductoCantidad();
                $productoPrecioCompra = $producto->getProductoPrecioCompra();
                $productoPorcentajeGanancia = $producto->getProductoPorcentajeGanancia();
                $productoDescripcion = $producto->getProductoDescripcion();
                $productoCategoriaID = $producto->getProductoCategoria()->getCategoriaID();
                $productoSubcategoriaID = $producto->getProductoSubcategoria()->getSubCategoriaID();
                $productoMarcaID = $producto->getProductoMarca()->getMarcaID();
                $productoPresentacionID = $producto->getProductoPresentacion()->getPresentacionID();
                $productoImagen = $producto->getProductoImagen();
                $productoFechaVencimiento = $producto->getProductoFechaVencimiento();

                // Asigna los valores a cada '?' y ejecuta la consulta
                mysqli_stmt_bind_param(
                    $stmt,
                    'iisiddsiiiiss', // i: entero, s: Cadena, d: Decimal
                    $nextId,
                    $codigoBarrasID,
                    $productoNombre,
                    $productoCantidad,
                    $productoPrecioCompra,
                    $productoPorcentajeGanancia,
                    $productoDescripcion,
                    $productoCategoriaID,
                    $productoSubcategoriaID,
                    $productoMarcaID,
                    $productoPresentacionID,
                    $productoImagen,
                    $productoFechaVencimiento
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
        
        /**
         * Actualiza la información de un producto en la base de datos.
         *
         * @param Producto $producto El objeto Producto que contiene la información actualizada.
         * @param mysqli|null $conn La conexión a la base de datos. Si es null, se creará una nueva conexión.
         * @return array Un array asociativo con las claves 'success' y 'message'. 'success' es un booleano 
         *               que indica si la operación fue exitosa, y 'message' es un mensaje descriptivo.
         * @throws Exception Si ocurre un error durante la actualización del producto.
         */
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
                        PRODUCTO_CANTIDAD . " = ?, " .
                        PRODUCTO_PRECIO_COMPRA . " = ?, " .
                        PRODUCTO_PORCENTAJE_GANANCIA . " = ?, " .
                        PRODUCTO_DESCRIPCION . " = ?, " .
                        PRODUCTO_CATEGORIA_ID . " = ?, " .
                        PRODUCTO_SUBCATEGORIA_ID . " = ?, " .
                        PRODUCTO_MARCA_ID . " = ?, " .
                        PRODUCTO_PRESENTACION_ID . " = ?, " .
                        PRODUCTO_IMAGEN . " = ?, " .
                        PRODUCTO_FECHA_VENCIMIENTO . " = ? " .
                        PRODUCTO_ESTADO . " = TRUE " .
                    "WHERE " . PRODUCTO_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryUpdate);
        
                // Obtiene los valores faltantes de las propiedades del objeto
                $productoCantidad = $producto->getProductoCantidad();
                $productoPrecioCompra = $producto->getProductoPrecioCompra();
                $productoPorcentajeGanancia = $producto->getProductoPorcentajeGanancia();
                $productoDescripcion = $producto->getProductoDescripcion();
                $productoCategoriaID = $producto->getProductoCategoria()->getCategoriaID();
                $productoSubcategoriaID = $producto->getProductoSubcategoria()->getSubCategoriaID();
                $productoMarcaID = $producto->getProductoMarca()->getMarcaID();
                $productoPresentacionID = $producto->getProductoPresentacion()->getPresentacionID();
                $productoImagen = $producto->getProductoImagen();
                $productoFechaVencimiento = $producto->getProductoFechaVencimiento();

                // Asigna los valores a cada '?' de la consulta
                mysqli_stmt_bind_param(
                    $stmt,
                    'isiddsiiiissi', // s: Cadena, i: Entero
                    $codigoBarrasID,
                    $productoNombre,
                    $productoCantidad,
                    $productoPrecioCompra,
                    $productoPorcentajeGanancia,
                    $productoDescripcion,
                    $productoCategoriaID,
                    $productoSubcategoriaID,
                    $productoMarcaID,
                    $productoPresentacionID,
                    $productoImagen,
                    $productoFechaVencimiento,
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
        
        /**
         * Elimina un producto de la base de datos.
         *
         * Este método realiza un borrado lógico del producto en la base de datos, 
         * actualizando su estado a FALSE. Además, elimina el código de barras asociado 
         * y la imagen del producto, si existe.
         *
         * @param int $productoID El ID del producto a eliminar.
         * @param mysqli|null $conn (Opcional) Conexión a la base de datos. Si no se proporciona, 
         *                          se creará una nueva conexión.
         * @return array Un array asociativo con las claves 'success' y 'message'. 
         *               'success' indica si la operación fue exitosa, y 'message' 
         *               proporciona información adicional.
         * @throws Exception Si ocurre un error durante la operación.
         */
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

        /**
         * Obtiene todos los productos de la tabla TB_PRODUCTO.
         *
         * @param bool $onlyActive Indica si solo se deben obtener los productos activos.
         * @param bool $deleted Indica si se deben incluir los productos eliminados.
         * @return array Un array asociativo que contiene:
         *               - "success" (bool): Indica si la operación fue exitosa.
         *               - "productos" (array): Una lista de objetos Producto si la operación fue exitosa.
         *               - "message" (string): Un mensaje de error en caso de que la operación falle.
         *
         * @throws Exception Si ocurre un error al establecer la conexión con la base de datos.
         * @throws Exception Si ocurre un error al obtener el código de barras del producto.
         * @throws Exception Si ocurre un error al obtener la categoría del producto.
         * @throws Exception Si ocurre un error al obtener la subcategoría del producto.
         * @throws Exception Si ocurre un error al obtener la marca del producto.
         * @throws Exception Si ocurre un error al obtener la presentación del producto.
         */
        public function getAllTBProducto($onlyActive = false, $deleted = false) {
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
                        $row[PRODUCTO_CANTIDAD],
                        $row[PRODUCTO_PRECIO_COMPRA],
                        $row[PRODUCTO_PORCENTAJE_GANANCIA],
                        $row[PRODUCTO_DESCRIPCION],
                        $categoria["categoria"],
                        $subcategoria["subcategoria"],
                        $marca["marca"],
                        $presentacion["presentacion"],
                        $row[PRODUCTO_IMAGEN],
                        $row[PRODUCTO_FECHA_VENCIMIENTO],
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

        /**
         * Obtiene una lista paginada de productos desde la base de datos.
         *
         * @param string $search Término de búsqueda para filtrar productos por nombre o código de barras.
         * @param int $page Número de página actual para la paginación.
         * @param int $size Cantidad de registros por página.
         * @param string|null $sort Campo por el cual ordenar los resultados (opcional).
         * @param bool $onlyActive Indica si solo se deben incluir productos activos (opcional).
         * @param bool $deleted Indica si se deben incluir productos eliminados (opcional).
         * 
         * @return array Un array asociativo con los siguientes elementos:
         *               - "success" (bool): Indica si la operación fue exitosa.
         *               - "page" (int): Número de página actual.
         *               - "size" (int): Cantidad de registros por página.
         *               - "totalPages" (int): Número total de páginas.
         *               - "totalRecords" (int): Número total de registros.
         *               - "productos" (array): Lista de objetos Producto.
         *               - "message" (string): Mensaje de error en caso de fallo.
         * 
         * @throws Exception Si ocurre un error al obtener la conexión a la base de datos o al ejecutar las consultas.
         */
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
                        $row[PRODUCTO_CANTIDAD],
                        $row[PRODUCTO_PRECIO_COMPRA],
                        $row[PRODUCTO_PORCENTAJE_GANANCIA],
                        $row[PRODUCTO_DESCRIPCION],
                        $categoria["categoria"],
                        $subcategoria["subcategoria"],
                        $marca["marca"],
                        $presentacion["presentacion"],
                        $row[PRODUCTO_IMAGEN],
                        $row[PRODUCTO_FECHA_VENCIMIENTO],
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
        
        /**
         * Obtiene la información de un producto por su ID.
         *
         * @param int $productoID El ID del producto a buscar.
         * @param bool $onlyActive (Opcional) Indica si solo se deben considerar productos activos. Por defecto es true.
         * @param bool $deleted (Opcional) Indica si se deben considerar productos marcados como eliminados. Por defecto es false.
         * @return array Un arreglo asociativo que contiene:
         *               - "success" (bool): Indica si la operación fue exitosa.
         *               - "message" (string): Un mensaje descriptivo del resultado.
         *               - "producto" (Producto|null): Un objeto Producto con la información del producto, si se encontró.
         * @throws Exception Si ocurre un error durante la ejecución.
         */
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
                        $row[PRODUCTO_CANTIDAD],
                        $row[PRODUCTO_PRECIO_COMPRA],
                        $row[PRODUCTO_PORCENTAJE_GANANCIA],
                        $row[PRODUCTO_DESCRIPCION],
                        $categoria["categoria"],
                        $subcategoria["subcategoria"],
                        $marca["marca"],
                        $presentacion["presentacion"],
                        $row[PRODUCTO_IMAGEN],
                        $row[PRODUCTO_FECHA_VENCIMIENTO],
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

        /**
         * Procesa la imagen de un producto, verificando su formato, cargándola y convirtiéndola a WebP.
         *
         * @param Producto $producto El objeto del producto que contiene la imagen a procesar.
         * @param array $formatosPermitidos (Opcional) Array de formatos de imagen permitidos. Por defecto: ['jpg', 'jpeg', 'png', 'webp'].
         * @return array Resultado del procesamiento de la imagen. Contiene:
         *               - 'success' (bool): Indica si el procesamiento fue exitoso.
         *               - 'message' (string, opcional): Mensaje descriptivo en caso de error o éxito.
         *
         * El método realiza las siguientes acciones:
         * 1. Verifica si se ha cargado un archivo de imagen.
         * 2. Comprueba que la extensión del archivo esté permitida.
         * 3. Verifica que el archivo haya sido cargado correctamente.
         * 4. Obtiene la ruta de destino para la imagen.
         * 5. Mueve la imagen cargada a la ruta de destino.
         * 6. Convierte la imagen a formato WebP y la redimensiona a 512x512 píxeles.
         * 7. Asigna la nueva ruta de la imagen al producto.
         *
         * Si ocurre algún error durante el proceso, se asigna una imagen por defecto al producto y se retorna un mensaje descriptivo del error.
         */
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
        
        /**
         * Asigna una imagen por defecto a un producto.
         *
         * Este método establece una imagen por defecto para un producto dado y 
         * registra esta acción en el archivo de log correspondiente.
         *
         * @param Producto $producto El objeto producto al cual se le asignará la imagen por defecto.
         * @param string $rutaPorDefecto La ruta de la imagen por defecto que se asignará al producto.
         * 
         * @return void
         */
        private function asignarImagenPorDefecto($producto, $rutaPorDefecto) {
            $producto->setProductoImagen($rutaPorDefecto);
            Utils::writeLog("Imagen por defecto asignada.", BUSINESS_LOG_FILE, INFO_MESSAGE, $this->className);
        }
        
        /**
         * Obtiene la ruta de la imagen de un producto basado en su categoría, subcategoría y nombre.
         *
         * @param Producto $producto El objeto del producto del cual se obtendrá la ruta de la imagen.
         * @param string $extension La extensión del archivo de imagen (por ejemplo, 'jpg', 'png').
         * @return string La ruta completa de la imagen del producto.
         *
         * Este método genera una ruta de imagen única para un producto específico. 
         * Primero, obtiene los IDs de la categoría y subcategoría del producto, así como el ID y nombre del producto.
         * Luego, reemplaza cualquier carácter no alfanumérico en el nombre del producto con un guion bajo.
         * Finalmente, construye la ruta de la carpeta utilizando la categoría y subcategoría, y crea la ruta completa de la imagen 
         * utilizando una función auxiliar.
         */
        private function obtenerRutaImagen($producto, $extension) {
            $categoriaID = $producto->getProductoCategoria()->getCategoriaID();
            $subcategoriaID = $producto->getProductoSubcategoria()->getSubcategoriaID();
            $productoID = $producto->getProductoID();
            $nombreProducto = preg_replace('/[^a-zA-Z0-9]/', '_', $producto->getProductoNombre());
            $carpeta = 'productos/' . Utils::generarURLCarpetaImagen($categoriaID, $subcategoriaID);
            
            return Utils::crearRutaImagen($carpeta, "{$productoID}_{$nombreProducto}.{$extension}");
        }

        /**
         * Elimina la imagen antigua de un producto si es diferente a la nueva imagen proporcionada.
         *
         * Este método realiza los siguientes pasos:
         * 1. Obtiene la imagen actual del producto desde la base de datos.
         * 2. Verifica si la imagen actual es la misma que la nueva imagen proporcionada.
         * 3. Si las imágenes son diferentes y la imagen actual no es la imagen por defecto, elimina la imagen actual.
         * 4. Elimina carpetas vacías en el directorio de imágenes de productos.
         *
         * @param object $productoActualizar El objeto del producto que contiene la nueva imagen.
         * @return array Un arreglo asociativo con la clave "success" que indica si la operación fue exitosa.
         */
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

        /**
         * Elimina carpetas vacías de manera recursiva hasta un límite especificado.
         *
         * @param string $directorio La ruta del directorio inicial desde donde comenzar la limpieza.
         * @param string $limite La ruta del directorio límite hasta donde se permitirá la limpieza.
         *
         * La función revisa si el directorio dado está vacío y, si es así, lo elimina.
         * Luego, sube un nivel en la jerarquía de carpetas y repite el proceso hasta
         * que se alcance el directorio límite o se encuentre un directorio no vacío.
         */
        private function limpiarCarpetasVacias($directorio, $limite) {
            // Limpiar carpetas recursivamente hasta el límite especificado
            while (is_dir($directorio) && $directorio !== $limite && count(glob("$directorio/*")) === 0) {
                rmdir($directorio); // Eliminar la carpeta si está vacía
                $directorio = dirname($directorio); // Subir un nivel en la jerarquía de carpetas
            }
        }
        
        /**
         * Convierte una imagen a formato WebP, con la opción de redimensionarla y eliminar la imagen original.
         *
         * @param string $sourcePath La ruta de la imagen original.
         * @param int|null $newWidth (Opcional) El nuevo ancho de la imagen. Si no se especifica, se mantiene el ancho original.
         * @param int|null $newHeight (Opcional) La nueva altura de la imagen. Si no se especifica, se mantiene la altura original.
         * @param int $quality (Opcional) La calidad de la imagen WebP (0-100). Por defecto es 80.
         * @param bool $removeOriginal (Opcional) Si se debe eliminar la imagen original después de la conversión. Por defecto es true.
         * @return string La ruta de la imagen convertida en formato WebP.
         */
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