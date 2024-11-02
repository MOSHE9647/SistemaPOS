<?php
    require_once dirname(__DIR__, 1) . '/data/data.php';
    require_once dirname(__DIR__, 1) . '/data/proveedorData.php'; 
    require_once dirname(__DIR__, 1) . '/data/clienteData.php'; 
    require_once dirname(__DIR__, 1) . '/domain/Compra.php';
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';
    require_once dirname(__DIR__, 1) . '/utils/Variables.php';

    Class CompraData extends Data{

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
        private function compraExiste($compraID, $update = false, $insert = false) {
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {throw new Exception($result["message"]);}
                $conn = $result["connection"];

                // Crea una consulta y un statement SQL para buscar el registro
                $queryCheck = "SELECT  " . COMPRA_ID . ", " . COMPRA_ESTADO . " FROM " . TB_COMPRA . " WHERE ";
                $params = [];
                $types = "";

                // Consulta para verificar si existe un producto con el ID ingresado
                if ($compraID && (!$update && !$insert)) {
                    // Consultar: Verificar existencia por ID
                    $queryCheck .= COMPRA_ID . " = ?";
                    $params = [$compraID];
                    $types .= 'i';
                }
                    // Asignar los parámetros y ejecutar la consulta
                    $stmt = mysqli_prepare($conn, $queryCheck);
                    mysqli_stmt_bind_param($stmt, $types, ...$params);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);


                    // Verificar si existe un producto con el ID, nombre o código de barras ingresado
                if ($row = mysqli_fetch_assoc($result)) {
                    // Verificar si está inactivo (bit de estado en 0)
                    $isInactive = $row[COMPRA_ESTADO] == 0;
                    return ["success" => true, "exists" => true, "inactive" => $isInactive, "compraID" => $row[COMPRA_ID]];
                }
                // Retorna false si no se encontraron resultados
                $messageParams = [];
                $params = implode(', ', $messageParams);
        
                $message = "No se encontró ninguna compra ($params) en la base de datos.";
                return ["success" => true, "exists" => false, "message" => $message];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al verificar la existencia de la compra en la base de datos',
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
         * @param Compra $producto El objeto Producto a insertar.
         * @param mysqli|null $conn La conexión a la base de datos (opcional).
         * @return array Un arreglo asociativo con el resultado de la inserción:
         *               - "success" (bool): Indica si la operación fue exitosa.
         *               - "message" (string): Mensaje de error o información adicional.
         *               - "id" (int, opcional): El ID del producto insertado (solo si la operación fue exitosa).
         *               - "inactive" (bool, opcional): Indica si el producto estaba inactivo (solo si ya existía).
         * @throws Exception Si ocurre un error durante la inserción.
         */

        public function insertCompra($compra, $conn = null) {
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

                $checkFactura = $this->existeNumeroFactura($compra->getCompraNumeroFactura());
                if (!$checkFactura["success"]) {
                    return $checkFactura;
                }
                if ($checkFactura["exists"]) {
                    return ["success" => false, "message" => "Error: El número de factura ya existe."];
                }
        
                // Obtiene el último ID de la tabla tblote
                $queryGetLastId = "SELECT MAX(" . COMPRA_ID . ") FROM " . TB_COMPRA;
                $idCont = mysqli_query($conn, $queryGetLastId);
                $nextId = 1;
        
                // Calcula el siguiente ID para la nueva entrada
                if ($row = mysqli_fetch_row($idCont)) {
                    $nextId = (int) trim($row[0]) + 1;
                }
                
                // Si la fecha de creación está vacía, asignar la fecha actual
                if (empty($compraFechaCreacion)) {
                    $compraFechaCreacion = date('Y-m-d H:i:s'); // Formato de fecha y hora actual
                }

                    // Si la fecha de modificación está vacía, asignar la fecha actual
                if (empty($compraFechaModificacion)) {
                    $compraFechaModificacion = date('Y-m-d H:i:s'); // Formato de fecha y hora actual
                }

                // Crea una consulta SQL para insertar el registro
                $queryInsert = "INSERT INTO " . TB_COMPRA . " ("
                    . COMPRA_ID . ", "
                    . CLIENTE_ID . ", "
                    . PROVEEDOR_ID . ", "
                    . COMPRA_NUMERO_FACTURA . ", " 
                    . COMPRA_MONEDA . ", "
                    . COMPRA_MONTO_BRUTO . ", "
                    . COMPRA_MONTO_NETO . ", "
                    . COMPRA_MONTO_IMPUESTO . ", "
                    . COMPRA_CONDICION_COMPRA . ", "
                    . COMPRA_TIPO_PAGO . ", "
                    . COMPRA_CREACION . ", "
                    . COMPRA_MODIFICACION . ", "
                    . COMPRA_ESTADO
                    . ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, true)";
                $stmt = mysqli_prepare($conn, $queryInsert);
        
               // Obtener los valores del objeto $compra
        $clienteID = $compra->getClienteID(); // Asegúrate de que este método devuelva el ID del cliente
        $proveedorID = $compra->getProveedorID();
        $compraNumeroFactura = $compra->getCompraNumeroFactura();
        $compraMoneda = $compra->getCompraMoneda();
        $compraMontoBruto = $compra->getCompraMontoBruto();
        $compraMontoNeto = $compra->getCompraMontoNeto();
        $compraMontoImpuesto = $compra->getCompraMontoImpuesto();
        $compraCondicionCompra = $compra->getCompraCondicionCompra();
        $compraTipoPago = $compra->getCompraTipoPago();
        $compraFechaCreacion = $compra->getCompraFechaCreacion();
        $compraFechaModificacion = $compra->getCompraFechaModificacion();

        // Asigna los valores a cada '?' de la consulta
        mysqli_stmt_bind_param(
            $stmt,
            'iiissdddssss', // Define el tipo de datos correcto para cada parámetro
            $nextId,
            $clienteID,
            $proveedorID,
            $compraNumeroFactura, 
            $compraMoneda,
            $compraMontoBruto,
            $compraMontoNeto,
            $compraMontoImpuesto,
            $compraCondicionCompra,
            $compraTipoPago,
            $compraFechaCreacion,
            $compraFechaModificacion
        );
                $result = mysqli_stmt_execute($stmt);

                    // Confirmar la transacción
            if ($createdConnection) { mysqli_commit($conn); }
        
                return ["success" => true, "message" => "Compra insertada exitosamente"];
            } catch (Exception $e) {
                if (isset($conn) && $createdConnection) { mysqli_rollback($conn); }

                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al insertar la compra en la base de datos',
                    $this->className
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra el statement y la conexión si están definidos
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn) && $createdConnection) { mysqli_close($conn); }
            }
        }
            
        /**
         * Actualiza la información de un producto en la base de datos.
         *
         * @param Compra $producto El objeto Producto que contiene la información actualizada.
         * @param mysqli|null $conn La conexión a la base de datos. Si es null, se creará una nueva conexión.
         * @return array Un array asociativo con las claves 'success' y 'message'. 'success' es un booleano 
         *               que indica si la operación fue exitosa, y 'message' es un mensaje descriptivo.
         * @throws Exception Si ocurre un error durante la actualización del producto.
         */
        public function updateCompra($compra, $conn = null) {
            $createdConnection = false;
            $stmt = null;
            try {
                if ($conn === null) {
                    $result = $this->getConnection();
                    if (!$result["success"]) { throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                    $createdConnection = true;
        
                    // Inicia una transacción
                    mysqli_begin_transaction($conn);
                }
        
                // Obtener el ID de la compra
                $compraID = $compra->getCompraID();
        
                // Verificar si la compra existe
                $check = $this->compraExiste($compraID);
                if (!$check["success"]) { throw new Exception($check["message"]); }
        
                if (!$check["exists"]) {
                    $message = "La compra con 'ID [$compraID]' no existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    return ["success" => false, "message" => "La compra seleccionada no existe en la base de datos."];
                }
        
                // Crea una consulta y un statement SQL para actualizar el registro
                $queryUpdate = 
                    "UPDATE " . TB_COMPRA . 
                    " SET " . 
                    CLIENTE_ID . " = ?, " .
                    PROVEEDOR_ID . " = ?, " .
                    COMPRA_NUMERO_FACTURA . " = ?, " .
                    COMPRA_MONEDA . " = ?, " .
                    COMPRA_MONTO_BRUTO . " = ?, " .
                    COMPRA_MONTO_NETO . " = ?, " .
                    COMPRA_MONTO_IMPUESTO . " = ?, " .
                    COMPRA_CONDICION_COMPRA . " = ?, " .
                    COMPRA_TIPO_PAGO . " = ?, " .
                    COMPRA_CREACION . " = ?, " .
                    COMPRA_MODIFICACION . " = ?, " .
                    COMPRA_ESTADO . " = true " .
                    "WHERE " . COMPRA_ID . " = ?";
                
                $stmt = mysqli_prepare($conn, $queryUpdate);
                if (!$stmt) {
                    throw new Exception("Error en la preparación de la consulta: " . mysqli_error($conn));
                }
        
                $clienteID = $compra->getClienteID(); // Asegúrate de que este método devuelva el ID del cliente
                $proveedorID = $compra->getProveedorID();
                $compraNumeroFactura = $compra->getCompraNumeroFactura();
                $compraMoneda = $compra->getCompraMoneda();
                $compraMontoBruto = $compra->getCompraMontoBruto();
                $compraMontoNeto = $compra->getCompraMontoNeto();
                $compraMontoImpuesto = $compra->getCompraMontoImpuesto();
                $compraCondicionCompra = $compra->getCompraCondicionCompra();
                $compraTipoPago = $compra->getCompraTipoPago();
                $compraFechaCreacion = $compra->getCompraFechaCreacion();
                $compraFechaModificacion = $compra->getCompraFechaModificacion();
        
                // Asigna los valores a cada '?' de la consulta
                mysqli_stmt_bind_param(
                    $stmt,
                    'iissdddssssi', // s: String, d: Double (decimal), i: Integer
                    $clienteID ,
                    $proveedorID,
                    $compraNumeroFactura,
                    $compraMoneda, 
                    $compraMontoBruto,
                    $compraMontoNeto,
                    $compraMontoImpuesto,
                    $compraCondicionCompra,
                    $compraTipoPago,
                    $compraFechaCreacion,
                    $compraFechaModificacion,
                    $compraID
                );
        
                // Ejecuta la consulta de actualización
                $result = mysqli_stmt_execute($stmt);
                if (!$result) {
                    throw new Exception("Error al ejecutar la consulta: " . mysqli_stmt_error($stmt));
                }
        
                // Confirma la transacción
                mysqli_commit($conn);
        
                return ["success" => true, "message" => "Compra actualizada exitosamente"];
            } catch (Exception $e) {
                if (isset($conn) && $createdConnection) { 
                    mysqli_rollback($conn); 
                }
        
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al actualizar la compra en la base de datos'
                );
        
                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            } finally {
                // Cierra el statement y la conexión si están definidos
                if (isset($stmt)) { 
                    mysqli_stmt_close($stmt); 
                }
                if (isset($conn) && $createdConnection) { 
                    mysqli_close($conn); 
                }
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
        public function deleteCompra($compraID, $conn = null) {
            $createdConnection = false;
            $stmt = null;
           
            try {
                // Verifica que el ID no esté vacío y sea numérico
                if (empty($compraID) || !is_numeric($compraID) || $compraID <= 0) {
                    throw new Exception("El ID no puede estar vacío o ser menor a 0.");
                }
        
                // Verificar si existe el ID y que el Estado no sea false
                $check = $this->compraExiste($compraID);
                if (!$check["success"]) {
                    return $check; // Error al verificar la existencia
                }
                if (!$check["exists"]) {
                    throw new Exception("No se encontró una compra con el ID [" . $compraID . "]");
                }
        
                // Establece una conexión con la base de datos
                if ($conn === null) {
                    $result = $this->getConnection();
                    if (!$result["success"]) {
                        throw new Exception($result["message"]);
                    }
                    $conn = $result["connection"];
                    $createdConnection = true;
        
                    // Inicia una transacción
                    mysqli_begin_transaction($conn);
                }
        
                // Crea una consulta y un statement SQL para eliminar lógicamente el registro
                $queryDelete = "UPDATE " . TB_COMPRA . " SET " . COMPRA_ESTADO . " = false WHERE " . COMPRA_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryDelete);
                mysqli_stmt_bind_param($stmt, 'i', $compraID);
        
                // Ejecuta la consulta de eliminación lógica
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Error al ejecutar la consulta de eliminación: " . mysqli_error($conn));
                }
        
                // Si todo sale bien, devuelve éxito
                mysqli_commit($conn);
                return ["success" => true, "message" => "Compra eliminada exitosamente"];
                
            } catch (Exception $e) {
                // Revertir la transacción en caso de error
                if (isset($conn) && $createdConnection) {
                    mysqli_rollback($conn);
                }
        
                // Manejo del error
                error_log($e->getMessage()); // Agrega log de error para depuración
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al eliminar la compra de la base de datos',
                    $this->className
                );
                return ["success" => false, "message" => $userMessage];
                
            } finally {
                // Cierra el statement y la conexión si fueron creados
                if (isset($stmt)) {
                    mysqli_stmt_close($stmt);
                }
                if (isset($conn) && $createdConnection) {
                    mysqli_close($conn);
                }
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
        public function getAllTBCompra($onlyActive = false, $deleted = false) {
            $conn = null;
            try {
                // Establecer una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
        
                   // Construir la consulta SQL
                $querySelect = "SELECT c.*, cl.clientenombre, p.proveedornombre 
                FROM " . TB_COMPRA . " c
                 INNER JOIN " . TB_CLIENTE . " cl ON c." . CLIENTE_ID . " = cl.clienteid
                 INNER JOIN " . TB_PROVEEDOR . " p ON c." . PROVEEDOR_ID . " = p.proveedorid";

                if ($onlyActive) {  
                $querySelect .= " WHERE c." . COMPRA_ESTADO . " = 1"; // Asegúrate de que 1 represente estado activo
                }
        
                // Ejecutar la consulta
                $result = mysqli_query($conn, $querySelect);
                if (!$result) {
                    throw new Exception("Error en la consulta: " . mysqli_error($conn));
                }
        
                $compras = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    // Crear objeto Proveedor
                    $proveedor = new Proveedor(
                        $row[PROVEEDOR_ID], // ID del proveedor
                        $row['proveedornombre'] // Nombre del proveedor
                        // Agrega otros parámetros necesarios aquí...
                    );

                        // Crear objeto Proveedor
                        $cliente = new Cliente(
                            $row[CLIENTE_ID], // ID del proveedor
                            $row['clientenombre'] // Nombre del proveedor
                            // Agrega otros parámetros necesarios aquí...
                        );
        
                    // Crear objeto Compra
                    $compra = new Compra(
                        $row[COMPRA_ID],
                        $cliente, // Pasa el objeto Proveedor aquí
                        $proveedor, // Pasa el objeto Proveedor aquí
                        $row[COMPRA_NUMERO_FACTURA],
                        $row[COMPRA_MONEDA],
                        $row[COMPRA_MONTO_BRUTO],
                        $row[COMPRA_MONTO_NETO],
                        $row[COMPRA_MONTO_IMPUESTO],
                        $row[COMPRA_CONDICION_COMPRA],
                        $row[COMPRA_TIPO_PAGO],
                        $row[COMPRA_CREACION],
                        $row[COMPRA_MODIFICACION],
                        $row[COMPRA_ESTADO]
                    );
                    $compras[] = $compra;
                }
        
                // Devolver la lista de compras
                return ["success" => true, "compras" => $compras];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener la lista de compras desde la base de datos',
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

        public function getPaginatedCompras($search, $page, $size, $sort = null, $onlyActive = false, $deleted = false) {
            $conn = null; $stmt = null;
            try {

                    // Calcular el offset para la paginación
                    $offset = ($page - 1) * $size;

                    // Establece una conexión con la base de datos
                    $result = $this->getConnection();
                    if (!$result["success"]) {
                        throw new Exception($result["message"]); }
                    $conn = $result["connection"];
                        
                // Consultar el total de registros en la tabla
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_COMPRA;
                if ($onlyActive) { $queryTotalCount .= " WHERE " . COMPRA_ESTADO . " != false" . ($deleted ? 'TRUE' : 'FALSE'); }

                $totalResult = mysqli_query($conn, $queryTotalCount);
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = (int) $totalRow['total'];
                $totalPages = ceil($totalRecords / $size);

                // Construir la consulta SQL con joins para obtener nombres en lugar de IDs
                $querySelect = "
                    SELECT 
                        c." . COMPRA_ID . ", 
                        cl.clientenombre AS clienteNombre,
                        pr.proveedornombre AS proveedorNombre,
                        c." . COMPRA_NUMERO_FACTURA . ",
                        c." . COMPRA_MONEDA . ", 
                        c." . COMPRA_MONTO_BRUTO . ", 
                        c." . COMPRA_MONTO_NETO . ", 
                        c." . COMPRA_MONTO_IMPUESTO . ", 
                        c." . COMPRA_CONDICION_COMPRA . ", 
                        c." . COMPRA_TIPO_PAGO . ", 
                        c." . COMPRA_CREACION . ", 
                        c." . COMPRA_MODIFICACION . ", 
                        c." . COMPRA_ESTADO . "
                    FROM " . TB_COMPRA . " c
                    JOIN tbproveedor pr ON c." . PROVEEDOR_ID .  " = pr.proveedorid
                    JOIN tbcliente cl ON c." . CLIENTE_ID .  " = cl.clienteid
                    WHERE c." . COMPRA_ESTADO . " != false
                    
                ";
                    // Filtro de búsqueda
                    $params = [];
                    $types = "";
                    if ($search) {
                        $querySelect .= " WHERE (c." . COMPRA_NUMERO_FACTURA . " LIKE ? OR pr.proveedornombre LIKE ?)";
                        $searchParam = "%" . $search . "%";
                        $params = [$searchParam, $searchParam];
                        $types .= "ss";
                    }

                    // Ordenamiento
                if ($sort) {
                    $querySelect .= " ORDER BY c." . $sort . " ";
                } else {
                    $querySelect .= " ORDER BY c." . COMPRA_ID . " DESC";
                }

                // Agregar límites a la consulta
                $querySelect .= " LIMIT ? OFFSET ?";
                $params = array_merge($params, [$size, $offset]);
                $types .= "ii";

                // Preparar la consulta y vincular los parámetros
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);

                // Obtener el resultado
                $result = mysqli_stmt_get_result($stmt);

                // Crear la lista de lotes
                $listaCompras = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $listaCompras[] = [
                        'ID' => $row[COMPRA_ID],          
                        'Cliente' => $row['clienteNombre'],
                        'Proveedor' => $row['proveedorNombre'],          
                        'NumeroFactura' => $row[COMPRA_NUMERO_FACTURA], 
                        'Moneda' => $row[COMPRA_MONEDA], // Agregando moneda
                        'MontoBruto' => $row[COMPRA_MONTO_BRUTO],   
                        'MontoNeto' => $row[COMPRA_MONTO_NETO],   
                        'MontoImpuesto' => $row[COMPRA_MONTO_IMPUESTO], // Agregando monto de impuesto  
                        'CondicionCompra' => $row[COMPRA_CONDICION_COMPRA], // Agregando condición de compra
                        'TipoPago' => $row[COMPRA_TIPO_PAGO],       
                        'FechaCreacion' => $row[COMPRA_CREACION], 
                        'FechaModificacion' => $row[COMPRA_MODIFICACION], 
                        'Estado' => $row[COMPRA_ESTADO]             
                    ];
                }

                // Devolver el resultado con la lista de direcciones y metadatos de paginación
                return [
                    "success" => true,
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "listaCompras" => $listaCompras
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
        public function getCompraByID($compraID, $onlyActive = true, $deleted = false) {
            $conn = null; $stmt = null;
            try {
                // Verificar si el producto existe en la base de datos
                $check = $this->compraExiste($compraID);
                if (!$check["success"]) { throw new Exception($check["message"]); }

                // En caso de no existir
                if (!$check["exists"]) {
                    $message = "La compra con 'ID [$compraID]' no existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    return ["success" => true, "message" => "La compra seleccionado no existe en la base de datos."];
                }
        
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Obtenemos la información de la compra
                $querySelect = "
                SELECT * FROM " . TB_COMPRA . " WHERE " . COMPRA_ID . " = ? AND " . COMPRA_ESTADO . " != false";
                $stmt = mysqli_prepare($conn, $querySelect);

                // Asignar los parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, 'i', $compraID);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                // Verifica si existe algún registro con los criterios dados
                $compra = null;
                if ($row = mysqli_fetch_assoc($result)) {
                    $compra = new Compra(
                        $row[COMPRA_ID],
                        $row[CLIENTE_ID],
                        $row[PROVEEDOR_ID],
                        $row[COMPRA_NUMERO_FACTURA],  
                        $row[COMPRA_MONEDA], // Moneda
                        $row[COMPRA_MONTO_BRUTO],
                        $row[COMPRA_MONTO_NETO],
                        $row[COMPRA_MONTO_IMPUESTO], // Monto de impuesto
                        $row[COMPRA_CONDICION_COMPRA], // Condición de compra
                        $row[COMPRA_TIPO_PAGO],  
                        $row[COMPRA_CREACION],
                        $row[COMPRA_MODIFICACION],  
                        $row[COMPRA_ESTADO]
                    );
                    return ["success" => true, "compra" => $compra];
                }
        
                // En caso de que no se haya encontrado el producto
                $message = "No se encontró la compra con 'ID [$compraID]' en la base de datos.";
                Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                return ["success" => false, "message" => "No se encontró la compra seleccionado en la base de datos."];
            } catch (Exception $e) {
                    // Manejo del error dentro del bloque catch
                    $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener el compra de la base de datos',
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

        public function getAllTBCompraDetalleCompra() {
            $response = [];
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];

                // Construir la consulta SQL con joins para obtener nombres en lugar de IDs
                $querySelect = "SELECT " . COMPRA_ID . ", " . COMPRA_NUMERO_FACTURA . " FROM " . TB_COMPRA . " WHERE " . COMPRA_ESTADO . " !=false"; 
                $result = mysqli_query($conn, $querySelect);

                // Crear la lista con los datos obtenidos
                $listaCompraDetalleCompra = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $listaCompraDetalleCompra [] = [
                        "ID" =>  $row[COMPRA_ID],                  
                    "NumeroFactura" => $row[COMPRA_NUMERO_FACTURA],         
                    ];
                }
                return ["success" => true, "listaCompraDetalleCompra" => $listaCompraDetalleCompra];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al obtener la lista de compras desde la base de datos'
                );

                // Devolver mensaje amigable para el usuario
                $response = ["success" => false, "message" => $userMessage];
            } finally {
                // Cerramos la conexión
                if (isset($conn)) { mysqli_close($conn); }
            }
            return $response;
        }

        private function existeNumeroFactura($numeroFactura) {
            $conn = $this->getConnection();
            if (!$conn["success"]) {
                return $conn;
            }
        
            $query = "SELECT 1 FROM " . TB_COMPRA . " WHERE " . COMPRA_NUMERO_FACTURA . " = ?";
            $stmt = mysqli_prepare($conn['connection'], $query);
            mysqli_stmt_bind_param($stmt, 's', $numeroFactura);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $exists = mysqli_num_rows($result) > 0;
        
            mysqli_stmt_close($stmt);
            mysqli_close($conn['connection']);
        
            return ["success" => true, "exists" => $exists];
        }
            
    }

?>