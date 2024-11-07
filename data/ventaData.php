<?php
    require_once dirname(__DIR__, 1) . '/data/data.php';
    require_once dirname(__DIR__, 1) . '/data/clienteData.php'; 
    require_once dirname(__DIR__, 1) . '/domain/Venta.php';
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';
    require_once dirname(__DIR__, 1) . '/utils/Variables.php';

    class VentaData extends Data{

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

        private function ventaExiste($ventaID, $update = false, $insert = false) {
            try {
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) {throw new Exception($result["message"]);}
                $conn = $result["connection"];

                // Crea una consulta y un statement SQL para buscar el registro
                $queryCheck = "SELECT  " . VENTA_ID . ", " . VENTA_ESTADO . " FROM " . TB_VENTA . " WHERE ";
                $params = [];
                $types = "";

                // Consulta para verificar si existe un producto con el ID ingresado
                if ($ventaID && (!$update && !$insert)) {
                    // Consultar: Verificar existencia por ID
                    $queryCheck .= VENTA_ID . " = ?";
                    $params = [$ventaID];
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
                    $isInactive = $row[VENTA_ESTADO] == 0;
                    return ["success" => true, "exists" => true, "inactive" => $isInactive, "ventaID" => $row[VENTA_ID]];
                }
                // Retorna false si no se encontraron resultados
                $messageParams = [];
                $params = implode(', ', $messageParams);
        
                $message = "No se encontró ninguna venta ($params) en la base de datos.";
                return ["success" => true, "exists" => false, "message" => $message];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al verificar la existencia de la venta en la base de datos',
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
         * @param Venta $producto El objeto Producto a insertar.
         * @param mysqli|null $conn La conexión a la base de datos (opcional).
         * @return array Un arreglo asociativo con el resultado de la inserción:
         *               - "success" (bool): Indica si la operación fue exitosa.
         *               - "message" (string): Mensaje de error o información adicional.
         *               - "id" (int, opcional): El ID del producto insertado (solo si la operación fue exitosa).
         *               - "inactive" (bool, opcional): Indica si el producto estaba inactivo (solo si ya existía).
         * @throws Exception Si ocurre un error durante la inserción.
         */

         public function insertVenta($venta, $conn = null) {
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

                $checkFactura = $this->existeVentaNumeroFactura($venta->getVentaNumeroFactura());
                if (!$checkFactura["success"]) {
                    return $checkFactura;
                }
                if ($checkFactura["exists"]) {
                    return ["success" => false, "message" => "Error: El número de factura ya existe."];
                }
        
                // Obtiene el último ID de la tabla tblote
                $queryGetLastId = "SELECT MAX(" . VENTA_ID . ") FROM " . TB_VENTA;
                $idCont = mysqli_query($conn, $queryGetLastId);
                $nextId = 1;
        
                // Calcula el siguiente ID para la nueva entrada
                if ($row = mysqli_fetch_row($idCont)) {
                    $nextId = (int) trim($row[0]) + 1;
                }
                
                // Si la fecha de creación está vacía, asignar la fecha actual
                if (empty($ventaFechaCreacion)) {
                    $ventaFechaCreacion = date('Y-m-d H:i:s'); // Formato de fecha y hora actual
                }

                    // Si la fecha de modificación está vacía, asignar la fecha actual
                if (empty($ventaFechaModificacion)) {
                    $ventaFechaModificacion = date('Y-m-d H:i:s'); // Formato de fecha y hora actual
                }

                // Crea una consulta SQL para insertar el registro
                $queryInsert = 
                    "INSERT INTO " . TB_VENTA . " ("
                        . VENTA_ID . ", "
                        . CLIENTE_ID . ", "
                        . USUARIO_ID . ", "
                        . VENTA_NUMERO_FACTURA . ", "
                        . VENTA_MONEDA . ", "
                        . VENTA_MONTO_BRUTO . ", "
                        . VENTA_MONTO_NETO . ", "
                        . VENTA_MONTO_IMPUESTO . ", "
                        . VENTA_CONDICION_VENTA . ", "
                        . VENTA_TIPO_PAGO . ", "
                        . VENTA_TIPO_CAMBIO . ", "
                        . VENTA_MONTO_PAGO . ", "
                        . VENTA_MONTO_VUELTO . ", "
                        . VENTA_REFERENCIA_TARJETA . ", "
                        . VENTA_COMPROBANTE_SINPE . ", "
                        . VENTA_CREACION . ", "
                        . VENTA_MODIFICACION . ", "
                        . VENTA_ESTADO
                    . ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, true)"
                ;
                $stmt = mysqli_prepare($conn, $queryInsert);
        
                // Obtener los valores de las propiedades del objeto $venta
                $ventaCliente = $venta->getClienteID();
                $ventaUsuario = $venta->getUsuarioID();
                $ventaNumeroFactura = $venta->getVentaNumeroFactura();
                $ventaMoneda = $venta->getVentaMoneda();
                $ventaMontoBruto = $venta->getVentaMontoBruto();
                $ventaMontoNeto = $venta->getVentaMontoNeto();
                $ventaMontoImpuesto = $venta->getVentaMontoImpuesto();
                $ventaCondicionVenta = $venta->getVentaCondicionVenta();
                $ventaTipoPago = $venta->getVentaTipoPago();
                $ventaTipoCambio = $venta->getVentaTipoCambio();
                $ventaMontoPago = $venta->getVentaMontoPago();
                $ventaMontoVuelto = $venta->getVentaMontoVuelto();
                $ventaReferenciaTarjeta = $venta->getVentaReferenciaTarjeta();
                $ventaComprobanteSinpe = $venta->getVentaComprobanteSinpe();

                // Asigna los valores a cada '?' de la consulta
                mysqli_stmt_bind_param(
                    $stmt,
                    'iiissdddssdddssss', // Define los tipos: i = entero, s = cadena, d = decimal
                    $nextId,
                    $ventaCliente,
                    $ventaUsuario,
                    $ventaNumeroFactura,
                    $ventaMoneda,
                    $ventaMontoBruto,
                    $ventaMontoNeto,
                    $ventaMontoImpuesto,
                    $ventaCondicionVenta,
                    $ventaTipoPago,
                    $ventaTipoCambio,
                    $ventaMontoPago,
                    $ventaMontoVuelto,
                    $ventaReferenciaTarjeta,
                    $ventaComprobanteSinpe,
                    $ventaFechaCreacion,
                    $ventaFechaModificacion
                );
                $result = mysqli_stmt_execute($stmt);

                    // Confirmar la transacción
            if ($createdConnection) { mysqli_commit($conn); }
        
                return ["success" => true, "message" => "Venta insertada exitosamente","id"=>$nextId];
            } catch (Exception $e) {
                if (isset($conn) && $createdConnection) { mysqli_rollback($conn); }

                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Ocurrió un error al insertar la venta en la base de datos',
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
         * @param Venta $producto El objeto Producto que contiene la información actualizada.
         * @param mysqli|null $conn La conexión a la base de datos. Si es null, se creará una nueva conexión.
         * @return array Un array asociativo con las claves 'success' y 'message'. 'success' es un booleano 
         *               que indica si la operación fue exitosa, y 'message' es un mensaje descriptivo.
         * @throws Exception Si ocurre un error durante la actualización del producto.
         */
        public function updateVenta($venta, $conn = null) {
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
                $ventaID = $venta->getVentaID();
        
                // Verificar si la compra existe
                $check = $this->ventaExiste($ventaID);
                if (!$check["success"]) { throw new Exception($check["message"]); }
        
                if (!$check["exists"]) {
                    $message = "La venta con 'ID [$ventaID]' no existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    return ["success" => false, "message" => "La venta seleccionada no existe en la base de datos."];
                }
        
                // Crea una consulta y un statement SQL para actualizar el registro
                $queryUpdate = 
                    "UPDATE " . TB_VENTA . 
                    " SET " . 
                        CLIENTE_ID . " = ?, " .
                        USUARIO_ID . " = ?, " .
                        VENTA_NUMERO_FACTURA . " = ?, " . 
                        VENTA_MONEDA . " = ?, " . 
                        VENTA_MONTO_BRUTO . " = ?, " .
                        VENTA_MONTO_NETO . " = ?, " .
                        VENTA_MONTO_IMPUESTO . " = ?, " .
                        VENTA_CONDICION_VENTA . " = ?, " .  
                        VENTA_TIPO_PAGO . " = ?, " .
                        VENTA_TIPO_CAMBIO . " = ?, " .
                        VENTA_MONTO_PAGO . " = ?, " .  
                        VENTA_MONTO_VUELTO . " = ?, " .
                        VENTA_REFERENCIA_TARJETA . " = ?, " .
                        VENTA_COMPROBANTE_SINPE . " = ?, " .
                        VENTA_CREACION . " = ?, " .
                        VENTA_MODIFICACION . " = ?, " .
                        VENTA_ESTADO . " = true " .
                    "WHERE " . VENTA_ID . " = ?";
                
                $stmt = mysqli_prepare($conn, $queryUpdate);
                if (!$stmt) {
                    throw new Exception("Error en la preparación de la consulta: " . mysqli_error($conn));
                }
        
                // Obtener los valores del objeto $venta  
                $clienteID = $venta->getClienteID();
                $usuarioID = $venta->getUsuarioID();
                $ventaNumeroFactura = $venta->getVentaNumeroFactura(); 
                $ventaMoneda = $venta->getVentaMoneda();
                $ventaMontoBruto = $venta->getVentaMontoBruto();
                $ventaMontoNeto = $venta->getVentaMontoNeto();
                $ventaMontoImpuesto = $venta->getVentaMontoImpuesto();
                $ventaCondicionVenta = $venta->getVentaCondicionVenta();
                $ventaTipoPago = $venta->getVentaTipoPago();
                $ventaTipoCambio = $venta->getVentaTipoCambio();
                $ventaFechaCreacion = $venta->getVentaFechaCreacion();
                $ventaFechaModificacion = $venta->getVentaFechaModificacion();
                $ventaMontoPago = $venta->getVentaMontoPago();
                $ventaMontoVuelto = $venta->getVentaMontoVuelto();
                $ventaReferenciaTarjeta = $venta->getVentaReferenciaTarjeta();
                $ventaComprobanteSinpe = $venta->getVentaComprobanteSinpe();
        
                // Asigna los valores a cada '?' de la consulta
                mysqli_stmt_bind_param(
                    $stmt,
                    'iissdddssdddssssi', // Ajusta los tipos según el tipo de dato de cada columna en la base de datos
                    $clienteID,
                    $usuarioID,
                    $ventaNumeroFactura, 
                    $ventaMoneda,
                    $ventaMontoBruto,
                    $ventaMontoNeto,
                    $ventaMontoImpuesto,
                    $ventaCondicionVenta,
                    $ventaTipoPago,
                    $ventaTipoCambio,
                    $ventaMontoPago,
                    $ventaMontoVuelto,
                    $ventaReferenciaTarjeta,
                    $ventaComprobanteSinpe,
                    $ventaFechaCreacion,
                    $ventaFechaModificacion,
                    $ventaID
                );
        
                // Ejecuta la consulta de actualización
                $result = mysqli_stmt_execute($stmt);
                if (!$result) {
                    throw new Exception("Error al ejecutar la consulta: " . mysqli_stmt_error($stmt));
                }
        
                // Confirma la transacción
                mysqli_commit($conn);
        
                return ["success" => true, "message" => "Venta actualizada exitosamente"];
            } catch (Exception $e) {
                if (isset($conn) && $createdConnection) { 
                    mysqli_rollback($conn); 
                }
        
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(),
                    'Error al actualizar la venta en la base de datos'
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
         * Elimina una venta de la base de datos.
         *
         * Este método realiza un borrado lógico de la venta en la base de datos, 
         * actualizando su estado a FALSE.
         *
         * @param int $ventaID El ID de la venta a eliminar.
         * @param mysqli|null $conn (Opcional) Conexión a la base de datos. Si no se proporciona, 
         *                          se creará una nueva conexión.
         * @return array Un array asociativo con las claves 'success' y 'message'. 
         *               'success' indica si la operación fue exitosa, y 'message' 
         *               proporciona información adicional.
         * @throws Exception Si ocurre un error durante la operación.
         */
        public function deleteVenta($ventaID, $conn = null) {
            $createdConnection = false;
            $stmt = null;
        
            try {
                // Verifica que el ID no esté vacío y sea numérico
                if (empty($ventaID) || !is_numeric($ventaID) || $ventaID <= 0) {
                    throw new Exception("El ID no puede estar vacío o ser menor a 0.");
                }

                // Verificar si existe el ID y que el Estado no sea false
                $check = $this->ventaExiste($ventaID);
                if (!$check["success"]) {
                    return $check; // Error al verificar la existencia
                }
                if (!$check["exists"]) {
                    throw new Exception("No se encontró una venta con el ID [" . $ventaID . "]");
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
                $queryDelete = "UPDATE " . TB_VENTA . " SET " . VENTA_ESTADO . " = false WHERE " . VENTA_ID . " = ?";
                $stmt = mysqli_prepare($conn, $queryDelete);
                mysqli_stmt_bind_param($stmt, 'i', $ventaID);

                // Ejecuta la consulta de eliminación lógica
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Error al ejecutar la consulta de eliminación: " . mysqli_error($conn));
                }

                // Si todo sale bien, devuelve éxito
                mysqli_commit($conn);
                return ["success" => true, "message" => "Venta eliminada exitosamente"];
                
            } catch (Exception $e) {
                // Revertir la transacción en caso de error
                if (isset($conn) && $createdConnection) {
                    mysqli_rollback($conn);
                }

                // Manejo del error
                error_log($e->getMessage()); // Agrega log de error para depuración
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al eliminar la venta de la base de datos',
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
         * Obtiene todas las ventas de la tabla TB_VENTA.
         *
         * @param bool $onlyActive Indica si solo se deben obtener las ventas activas.
         * @param bool $deleted Indica si se deben incluir las ventas eliminadas.
         * @return array Un array asociativo que contiene:
         *               - "success" (bool): Indica si la operación fue exitosa.
         *               - "ventas" (array): Una lista de objetos Venta si la operación fue exitosa.
         *               - "message" (string): Un mensaje de error en caso de que la operación falle.
         *
         * @throws Exception Si ocurre un error al establecer la conexión con la base de datos.
            * @throws Exception Si ocurre un error al obtener el código de barras del producto.
                * @throws Exception Si ocurre un error al obtener la categoría del producto.
                * @throws Exception Si ocurre un error al obtener la subcategoría del producto.
                * @throws Exception Si ocurre un error al obtener la marca del producto.
                * @throws Exception Si ocurre un error al obtener la presentación del producto.
        */
        public function getAllTBVenta($onlyActive = false, $deleted = false) {
            $conn = null;
            try {
                // Establecer una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

            
                // Construir la consulta SQL
                $querySelect = "
                    SELECT 
                        v.*, c.*, t.*, u.*, r.*
                    FROM " . TB_VENTA . " v
                    INNER JOIN " . TB_CLIENTE . " c 
                        ON v." . CLIENTE_ID . " = c." . CLIENTE_ID . "
                    INNER JOIN " . TB_USUARIO . " u
                        ON v." . USUARIO_ID . " = u." . USUARIO_ID . "
                    INNER JOIN " . TB_TELEFONO . " t
                        ON c." . TELEFONO_ID . " = t." . TELEFONO_ID . "
                    INNER JOIN " . TB_ROL . " r
                        ON u." . ROL_ID . " = r." . ROL_ID . "
                ";
                if ($onlyActive) {  
                    $querySelect .= " WHERE v." . VENTA_ESTADO . " = TRUE"; // Asegúrate de que 1 represente estado activo
                }

                // Ejecutar la consulta
                $result = mysqli_query($conn, $querySelect);
                if (!$result) {
                    throw new Exception("Error en la consulta: " . mysqli_error($conn));
                }

                $ventas = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    // Crear objeto Venta
                    $venta = new Venta(
                        $row[VENTA_ID],
                        new Cliente ( // Esto debe ser un objeto de tipo Cliente
                            $row[CLIENTE_ID], // ID del cliente
                            $row[CLIENTE_NOMBRE], // Nombre del cliente
                            $row[CLIENTE_ALIAS], // Alias del cliente
                            new Telefono (
                                $row[TELEFONO_ID], // ID del teléfono
                                $row[TELEFONO_TIPO], // Tipo de teléfono
                                $row[TELEFONO_CODIGO_PAIS], // Código de país
                                $row[TELEFONO_NUMERO], // Número de teléfono
                                $row[TELEFONO_EXTENSION], // Extensión del teléfono
                            ),
                            $row[CLIENTE_CREACION], // Fecha de creación del cliente
                            $row[CLIENTE_MODIFICACION], // Fecha de modificación del cliente
                        ), 
                        new Usuario ( // Esto debe ser un objeto de tipo Usuario
                            $row[USUARIO_ID], // ID del usuario
                            $row[USUARIO_NOMBRE], // Nombre del usuario
                            $row[USUARIO_APELLIDO_1], // Primer apellido del usuario
                            $row[USUARIO_APELLIDO_2], // Segundo apellido del usuario
                            $row[USUARIO_EMAIL], // Correo electrónico del usuario
                            $row[USUARIO_PASSWORD], // Contraseña del usuario
                            new RolUsuario (
                                $row[ROL_ID], // ID del rol
                                $row[ROL_NOMBRE], // Nombre del rol
                                $row[ROL_DESCRIPCION], // Descripción del rol
                            ),
                            $row[USUARIO_CREACION], // Fecha de creación del usuario
                            $row[USUARIO_MODIFICACION], // Fecha de modificación del usuario
                        ),
                        $row[VENTA_NUMERO_FACTURA],
                        $row[VENTA_MONEDA],
                        $row[VENTA_MONTO_BRUTO],
                        $row[VENTA_MONTO_NETO],
                        $row[VENTA_MONTO_IMPUESTO],
                        $row[VENTA_CONDICION_VENTA],
                        $row[VENTA_TIPO_PAGO],
                        $row[VENTA_TIPO_CAMBIO],
                        $row[VENTA_MONTO_PAGO],
                        $row[VENTA_MONTO_VUELTO],
                        $row[VENTA_REFERENCIA_TARJETA],
                        $row[VENTA_COMPROBANTE_SINPE],
                        $row[VENTA_CREACION],
                        $row[VENTA_MODIFICACION],
                        $row[VENTA_ESTADO]
                    );
                    $ventas[] = $venta;
                }

                // Devolver la lista de ventas
                return ["success" => true, "ventas" => $ventas];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener la lista de ventas desde la base de datos',
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

        public function getPaginatedVentas($search, $page, $size, $sort = null, $onlyActive = false, $deleted = false) {
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
                $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_VENTA;
                if ($onlyActive) { $queryTotalCount .= " WHERE " . VENTA_ESTADO . " != false" . ($deleted ? 'TRUE' : 'FALSE'); }

                $totalResult = mysqli_query($conn, $queryTotalCount);
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = (int) $totalRow['total'];
                $totalPages = ceil($totalRecords / $size);

                // Construir la consulta SQL con joins para obtener nombres en lugar de IDs
                $querySelect = "
                    SELECT 
                        v.*, c.*, t.*, u.*, r.*
                    FROM " . TB_VENTA . " v
                    INNER JOIN " . TB_CLIENTE . " c 
                        ON v." . CLIENTE_ID . " = c." . CLIENTE_ID . "
                    INNER JOIN " . TB_USUARIO . " u
                        ON v." . USUARIO_ID . " = u." . USUARIO_ID . "
                    INNER JOIN " . TB_TELEFONO . " t
                        ON c." . TELEFONO_ID . " = t." . TELEFONO_ID . "
                    INNER JOIN " . TB_ROL . " r
                        ON u." . ROL_ID . " = r." . ROL_ID . "
                    WHERE v." . VENTA_ESTADO . " != false
                ";
                $params = [];
                $types = "";
                if ($search) {
                    $querySelect .= " AND (v." . VENTA_NUMERO_FACTURA . " LIKE ? OR c.clientenombre LIKE ?)";
                    $searchParam = "%" . $search . "%";
                    $params = [$searchParam, $searchParam];
                    $types .= "ss";
                }

                    // Ordenamiento
                if ($sort) {
                    $querySelect .= " ORDER BY c." . $sort . " ";
                } else {
                    $querySelect .= " ORDER BY c." . VENTA_ID . " DESC";
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
                $listaVentas = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    // Crear objeto Venta
                    $venta = new Venta(
                        $row[VENTA_ID],
                        new Cliente ( // Esto debe ser un objeto de tipo Cliente
                            $row[CLIENTE_ID], // ID del cliente
                            $row[CLIENTE_NOMBRE], // Nombre del cliente
                            $row[CLIENTE_ALIAS], // Alias del cliente
                            new Telefono (
                                $row[TELEFONO_ID], // ID del teléfono
                                $row[TELEFONO_TIPO], // Tipo de teléfono
                                $row[TELEFONO_CODIGO_PAIS], // Código de país
                                $row[TELEFONO_NUMERO], // Número de teléfono
                                $row[TELEFONO_EXTENSION], // Extensión del teléfono
                            ),
                            $row[CLIENTE_CREACION], // Fecha de creación del cliente
                            $row[CLIENTE_MODIFICACION], // Fecha de modificación del cliente
                        ), 
                        new Usuario ( // Esto debe ser un objeto de tipo Usuario
                            $row[USUARIO_ID], // ID del usuario
                            $row[USUARIO_NOMBRE], // Nombre del usuario
                            $row[USUARIO_APELLIDO_1], // Primer apellido del usuario
                            $row[USUARIO_APELLIDO_2], // Segundo apellido del usuario
                            $row[USUARIO_EMAIL], // Correo electrónico del usuario
                            $row[USUARIO_PASSWORD], // Contraseña del usuario
                            new RolUsuario (
                                $row[ROL_ID], // ID del rol
                                $row[ROL_NOMBRE], // Nombre del rol
                                $row[ROL_DESCRIPCION], // Descripción del rol
                            ),
                            $row[USUARIO_CREACION], // Fecha de creación del usuario
                            $row[USUARIO_MODIFICACION], // Fecha de modificación del usuario
                        ),
                        $row[VENTA_NUMERO_FACTURA],
                        $row[VENTA_MONEDA],
                        $row[VENTA_MONTO_BRUTO],
                        $row[VENTA_MONTO_NETO],
                        $row[VENTA_MONTO_IMPUESTO],
                        $row[VENTA_CONDICION_VENTA],
                        $row[VENTA_TIPO_PAGO],
                        $row[VENTA_TIPO_CAMBIO],
                        $row[VENTA_MONTO_PAGO],
                        $row[VENTA_MONTO_VUELTO],
                        $row[VENTA_REFERENCIA_TARJETA],
                        $row[VENTA_COMPROBANTE_SINPE],
                        $row[VENTA_CREACION],
                        $row[VENTA_MODIFICACION],
                        $row[VENTA_ESTADO]
                    );
                    $listaVentas[] = $venta;
                }

                // Devolver el resultado con la lista de direcciones y metadatos de paginación
                return [
                    "success" => true,
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "listaVentas" => $listaVentas
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
        public function getVentaByID($ventaID, $onlyActive = true, $deleted = false) {
            $conn = null; $stmt = null;
            try {
                // Verificar si el producto existe en la base de datos
                $check = $this->ventaExiste($ventaID);
                if (!$check["success"]) { throw new Exception($check["message"]); }

                // En caso de no existir
                if (!$check["exists"]) {
                    $message = "La venta con 'ID [$ventaID]' no existe en la base de datos.";
                    Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    return ["success" => true, "message" => "La venta seleccionado no existe en la base de datos."];
                }
        
                // Establece una conexión con la base de datos
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];

                // Obtenemos la información de la compra
                $querySelect = "
                    SELECT 
                        v.*, c.*, t.*, u.*, r.*
                    FROM " . TB_VENTA . " v 
                    INNER JOIN " . TB_CLIENTE . " c 
                        ON v." . CLIENTE_ID . " = c." . CLIENTE_ID . "
                    INNER JOIN " . TB_USUARIO . " u
                        ON v." . USUARIO_ID . " = u." . USUARIO_ID . "
                    INNER JOIN " . TB_TELEFONO . " t
                        ON c." . TELEFONO_ID . " = t." . TELEFONO_ID . "
                    INNER JOIN " . TB_ROL . " r
                        ON u." . ROL_ID . " = r." . ROL_ID . "
                    WHERE v." . VENTA_ID . " = ? AND v." . VENTA_ESTADO . " != false
                ";
                $stmt = mysqli_prepare($conn, $querySelect);

                // Asignar los parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, 'i', $ventaID);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                // Verifica si existe algún registro con los criterios dados
                $venta = null;
                if ($row = mysqli_fetch_assoc($result)) {
                    // Crear objeto Venta
                    $venta = new Venta(
                        $row[VENTA_ID],
                        new Cliente ( // Esto debe ser un objeto de tipo Cliente
                            $row[CLIENTE_ID], // ID del cliente
                            $row[CLIENTE_NOMBRE], // Nombre del cliente
                            $row[CLIENTE_ALIAS], // Alias del cliente
                            new Telefono (
                                $row[TELEFONO_ID], // ID del teléfono
                                $row[TELEFONO_TIPO], // Tipo de teléfono
                                $row[TELEFONO_CODIGO_PAIS], // Código de país
                                $row[TELEFONO_NUMERO], // Número de teléfono
                                $row[TELEFONO_EXTENSION], // Extensión del teléfono
                            ),
                            $row[CLIENTE_CREACION], // Fecha de creación del cliente
                            $row[CLIENTE_MODIFICACION], // Fecha de modificación del cliente
                        ), 
                        new Usuario ( // Esto debe ser un objeto de tipo Usuario
                            $row[USUARIO_ID], // ID del usuario
                            $row[USUARIO_NOMBRE], // Nombre del usuario
                            $row[USUARIO_APELLIDO_1], // Primer apellido del usuario
                            $row[USUARIO_APELLIDO_2], // Segundo apellido del usuario
                            $row[USUARIO_EMAIL], // Correo electrónico del usuario
                            $row[USUARIO_PASSWORD], // Contraseña del usuario
                            new RolUsuario (
                                $row[ROL_ID], // ID del rol
                                $row[ROL_NOMBRE], // Nombre del rol
                                $row[ROL_DESCRIPCION], // Descripción del rol
                            ),
                            $row[USUARIO_CREACION], // Fecha de creación del usuario
                            $row[USUARIO_MODIFICACION], // Fecha de modificación del usuario
                        ),
                        $row[VENTA_NUMERO_FACTURA],
                        $row[VENTA_MONEDA],
                        $row[VENTA_MONTO_BRUTO],
                        $row[VENTA_MONTO_NETO],
                        $row[VENTA_MONTO_IMPUESTO],
                        $row[VENTA_CONDICION_VENTA],
                        $row[VENTA_TIPO_PAGO],
                        $row[VENTA_TIPO_CAMBIO],
                        $row[VENTA_MONTO_PAGO],
                        $row[VENTA_MONTO_VUELTO],
                        $row[VENTA_REFERENCIA_TARJETA],
                        $row[VENTA_COMPROBANTE_SINPE],
                        $row[VENTA_CREACION],
                        $row[VENTA_MODIFICACION],
                        $row[VENTA_ESTADO]
                    );
                    return ["success" => true, "venta" => $venta];
                }
        
                // En caso de que no se haya encontrado el producto
                $message = "No se encontró la venta con 'ID [$ventaID]' en la base de datos.";
                Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                return ["success" => false, "message" => "No se encontró la venta seleccionado en la base de datos."];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), $e->getMessage(),
                    'Error al obtener la venta de la base de datos',
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

        private function existeVentaNumeroFactura($numeroFactura) {
            $conn = $this->getConnection();
            if (!$conn["success"]) {
                return $conn;
            }
        
            $query = "SELECT 1 FROM " . TB_VENTA . " WHERE " . VENTA_NUMERO_FACTURA . " = ?";
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