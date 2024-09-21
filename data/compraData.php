<?php
        include_once 'data.php';
        include __DIR__ . '/../domain/Compra.php';
        include_once __DIR__ . '/../utils/Variables.php';

        Class CompraData extends Data{

             // Constructor
             public function __construct() {
                parent::__construct();
            }


            public function insertCompra($compra) {
                try {

                    $checkFactura = $this->existeNumeroFactura($compra->getCompraNumeroFactura());
                    if (!$checkFactura["success"]) {
                        return $checkFactura;
                    }
                    if ($checkFactura["exists"]) {
                        return ["success" => false, "message" => "Error: El número de factura ya existe."];
                    }
                    
                    // Establece una conexión con la base de datos
                    $result = $this->getConnection();
                    if (!$result["success"]) {
                        throw new Exception($result["message"]);
                    }
                    $conn = $result["connection"];
            
                    // Obtiene el último ID de la tabla tblote
                    $queryGetLastId = "SELECT MAX(" . COMPRA_ID . ") FROM " . TB_COMPRA;
                    $idCont = mysqli_query($conn, $queryGetLastId);
                    $nextId = 1;
            
                    // Calcula el siguiente ID para la nueva entrada
                    if ($row = mysqli_fetch_row($idCont)) {
                        $nextId = (int) trim($row[0]) + 1;
                    }
            
                    // Obtener los valores de las propiedades del objeto $compra
                    $compraNumeroFactura = $compra->getCompraNumeroFactura();
                    $compraMontoBruto = $compra->getCompraMontoBruto();
                    $compraMontoNeto = $compra->getCompraMontoNeto();
                    $compraTipoPago = $compra->getCompraTipoPago();
                    $proveedorID = $compra->getProveedorID();
                    $compraFechaCreacion = $compra->getCompraFechaCreacion();
                    $compraFechaModificacion = $compra->getCompraFechaModificacion();
            
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
                        . COMPRA_NUMERO_FACTURA . ", "
                        . COMPRA_PROVEEDOR_ID . ", "
                        . COMPRA_MONTO_BRUTO . ", "
                        . COMPRA_MONTO_NETO . ", "
                        . COMPRA_TIPO_PAGO . ", "
                        . COMPRA_FECHA_CREACION . ", "
                        . COMPRA_FECHA_MODIFICACION . ", "
                        . COMPRA_ESTADO
                        . ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, true)";
                    $stmt = mysqli_prepare($conn, $queryInsert);
            
                    // Asigna los valores a cada '?' de la consulta
                    mysqli_stmt_bind_param(
                        $stmt,
                        'isiddsss', // i: Entero, s: Cadena, d: Decimal
                        $nextId,
                        $compraNumeroFactura, 
                        $proveedorID,
                        $compraMontoBruto,
                        $compraMontoNeto,
                        $compraTipoPago,
                        $compraFechaCreacion,
                        $compraFechaModificacion
                    );
            
                    // Ejecuta la consulta de inserción
                    $result = mysqli_stmt_execute($stmt);
                    if (!$result) {
                        throw new Exception("Error al insertar la compra: " . mysqli_error($conn));
                    }
            
                    return ["success" => true, "message" => "Compra insertada exitosamente"];
                } catch (Exception $e) {
                    // Manejo del error dentro del bloque catch
                    $userMessage = $this->handleMysqlError(
                        $e->getCode(), 
                        $e->getMessage(),
                        'Error al insertar la compra en la base de datos'
                    );
            
                    // Devolver mensaje amigable para el usuario
                    return ["success" => false, "message" => $userMessage];
                } finally {
                    // Cierra el statement y la conexión si están definidos
                    if (isset($stmt)) { mysqli_stmt_close($stmt); }
                    if (isset($conn)) { mysqli_close($conn); }
                }
            }


            public function updateCompra($compra) {
                try {
                    // Establece una conexión con la base de datos
                    $result = $this->getConnection();
                    if (!$result["success"]) {
                        throw new Exception($result["message"]);
                    }
                    $conn = $result["connection"];
        
                    // Crea una consulta y un statement SQL para actualizar el registro
                    $queryUpdate = 
                        "UPDATE " . TB_COMPRA . 
                        " SET " . 
                        COMPRA_NUMERO_FACTURA . " = ?, " .
                        COMPRA_PROVEEDOR_ID . " = ?, " .
                        COMPRA_MONTO_BRUTO . " = ?, " .
                        COMPRA_MONTO_NETO . " = ?, " .
                        COMPRA_TIPO_PAGO . " = ?, " .
                       
                        COMPRA_FECHA_CREACION . " = ?, " .
                        COMPRA_FECHA_MODIFICACION . " = ?, " .
                        COMPRA_ESTADO . " = true " .
                        "WHERE " . COMPRA_ID . " = ?";
                    $stmt = mysqli_prepare($conn, $queryUpdate);
        
                    // Obtener los valores de las propiedades del objeto $lote
                    $compraID = $compra->getCompraID();
                    $compraNumeroFactura = $compra->getCompraNumeroFactura();   
                    $compraMontoBruto = $compra->getCompraMontoBruto();
                    $compraMontoNeto = $compra->getCompraMontoNeto();
                    $compraTipoPago = $compra->getCompraTipoPago();
                  //  $compraProveedorId = $compra->getCompraProveedorId();
                  $proveedorID = $compra->getProveedorID();
                    $compraFechaCreacion = $compra->getCompraFechaCreacion();
                    $compraFechaModificacion = $compra->getCompraFechaModificacion();
        
                    // Asigna los valores a cada '?' de la consulta
                    mysqli_stmt_bind_param(
                        $stmt,
                        'siddsssi', // s: String, d: Double (decimal), i: Integer
                        $compraNumeroFactura, 
                       // $compraProveedorId,
                        $proveedorID,
                        $compraMontoBruto,
                        $compraMontoNeto,
                        $compraTipoPago,
                        $compraFechaCreacion,
                        $compraFechaModificacion,
                        $compraID
                    );
        
                    // Ejecuta la consulta de actualización
                    $result = mysqli_stmt_execute($stmt);
        
                    // Devuelve el resultado de la consulta
                    return ["success" => true, "message" => "Compra actualizada exitosamente"];
                } catch (Exception $e) {
                    // Manejo del error dentro del bloque catch
                    $userMessage = $this->handleMysqlError(
                        $e->getCode(), 
                        $e->getMessage(),
                        'Error al actualizar el lote en la base de datos'
                    );
        
                    // Devolver mensaje amigable para el usuario
                    return ["success" => false, "message" => $userMessage];
                } finally {
                    // Cierra la conexión y el statement si están definidos
                    if (isset($stmt)) { mysqli_stmt_close($stmt); }
                    if (isset($conn)) { mysqli_close($conn); }
                }
            }


          /*  public function getAllTBCompra() {
                try {
                    // Establece una conexion con la base de datos
                    $result = $this->getConnection();
                    if (!$result["success"]) {
                        throw new Exception($result["message"]);
                    }
                    $conn = $result["connection"];
        
                    // Construir la consulta SQL con joins para obtener nombres en lugar de IDs
                $querySelect = "
                   SELECT 
             c." . COMPRA_ID . ", 
                c." . COMPRA_NUMERO_FACTURA . ",
                c." . COMPRA_PROVEEDOR_ID . ",
                c." . COMPRA_MONTO_BRUTO . ", 
                c." . COMPRA_MONTO_NETO . ", 
                c." . COMPRA_TIPO_PAGO . ", 
                c." . COMPRA_FECHA_CREACION . ", 
                c." . COMPRA_FECHA_MODIFICACION . ", 
                c." . COMPRA_ESTADO . "
            FROM " . TB_COMPRA . " c
            WHERE c." . COMPRA_ESTADO . " != false
        ";
        
                $result = mysqli_query($conn, $querySelect);
        
                   // Crear la lista con los datos obtenidos
                $listaCompras = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $currentCompra = new Compra(
                        $row[COMPRA_ID],              // compraID
                        $row[COMPRA_NUMERO_FACTURA], 
                        $row[COMPRA_PROVEEDOR_ID],      // compraProveedorNombre en lugar de compraProveedorId // compraNumeroFactura
                        $row[COMPRA_MONTO_BRUTO],     // compraMontoBruto
                        $row[COMPRA_MONTO_NETO],      // compraMontoNeto
                        $row[COMPRA_TIPO_PAGO],       // compraTipoPago
                        
                        $row[COMPRA_FECHA_CREACION],  // compraFechaCreacion
                        $row[COMPRA_FECHA_MODIFICACION], // compraFechaModificacion
                        $row[COMPRA_ESTADO]        
                    );
                    array_push($listaCompras, $currentCompra);
                }
        
                    return ["success" => true, "listaCompras" => $listaCompras];
                } catch (Exception $e) {
                    // Manejo del error dentro del bloque catch
                    $userMessage = $this->handleMysqlError(
                        $e->getCode(), 
                        $e->getMessage(),
                        'Error al obtener la lista de compras desde la base de datos'
                    );
        
                    // Devolver mensaje amigable para el usuario
                    return ["success" => false, "message" => $userMessage];
                } finally {
                    // Cerramos la conexion
                    if (isset($conn)) { mysqli_close($conn); }
                }
            }

            public function getPaginatedCompras($page, $size, $sort = null) {
                try {
                    // Verificar que la página y el tamaño sean números enteros positivos
                    if (!is_numeric($page) || $page < 1) {
                        throw new Exception("El número de página debe ser un entero positivo.");
                    }
                    if (!is_numeric($size) || $size < 1) {
                        throw new Exception("El tamaño de la página debe ser un entero positivo.");
                    }
                    
                    // Calcular el offset para la paginación
                    $offset = ($page - 1) * $size;
        
                    // Establece una conexión con la base de datos
                    $result = $this->getConnection();
                    if (!$result["success"]) {
                        throw new Exception($result["message"]);
                    }
                    $conn = $result["connection"];
        
                    // Consultar el total de registros en la tabla
                    $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_COMPRA . " WHERE " . COMPRA_ESTADO . " != false ";
                    $totalResult = mysqli_query($conn, $queryTotalCount);
                    $totalRow = mysqli_fetch_assoc($totalResult);
                    $totalRecords = (int) $totalRow['total'];
                    $totalPages = ceil($totalRecords / $size);
        
                     
                      $querySelect = "
                       SELECT 
           c." . COMPRA_ID . ", 
                c." . COMPRA_NUMERO_FACTURA . ",
                 c." . COMPRA_PROVEEDOR_ID . ", 
                c." . COMPRA_MONTO_BRUTO . ", 
                c." . COMPRA_MONTO_NETO . ", 
                c." . COMPRA_TIPO_PAGO . ", 
                c." . COMPRA_FECHA_CREACION . ", 
                c." . COMPRA_FECHA_MODIFICACION . ", 
                c." . COMPRA_ESTADO . "
            FROM " . TB_COMPRA . " c
            WHERE c." . COMPRA_ESTADO . " != false
            LIMIT ? OFFSET ?
        ";
                
                        // Preparar la consulta y vincular los parámetros
                        $stmt = mysqli_prepare($conn, $querySelect);
                        mysqli_stmt_bind_param($stmt, "ii", $size, $offset);
                
                        // Ejecutar la consulta
                        $result = mysqli_stmt_execute($stmt);
                
                        // Obtener el resultado
                        $result = mysqli_stmt_get_result($stmt);
        
                    // Crear la lista de lotes
                    $listaCompras = [];
                    while ($row = mysqli_fetch_assoc($result)) {
                        $listaCompras [] = [
                            'ID' => $row[COMPRA_ID],                    // compraID
                            'NumeroFactura' => $row[COMPRA_NUMERO_FACTURA], // compraNumeroFactura
                            'ProveedorID' => $row[COMPRA_PROVEEDOR_ID], // Nombre del proveedor
                            'MontoBruto' => $row[COMPRA_MONTO_BRUTO],   // compraMontoBruto
                            'MontoNeto' => $row[COMPRA_MONTO_NETO],     // compraMontoNeto
                            'TipoPago' => $row[COMPRA_TIPO_PAGO],       // compraTipoPago
                          
                            'FechaCreacion' => $row[COMPRA_FECHA_CREACION], // compraFechaCreacion
                            'FechaModificacion' => $row[COMPRA_FECHA_MODIFICACION], // compraFechaModificacion
                            'Estado' => $row[COMPRA_ESTADO]             // compraEstado
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
                        $e->getCode(), 
                        $e->getMessage(),
                        'Error al obtener la lista de compras desde la base de dat'
                    );
            
                    // Devolver mensaje amigable para el usuario
                    return ["success" => false, "message" => $userMessage];
                } finally {
                    // Cerrar la conexión y el statement
                    if (isset($stmt)) { mysqli_stmt_close($stmt); }
                    if (isset($conn)) { mysqli_close($conn); }
                }
            }
*/
public function getAllTBCompra() {
    try {
        // Establece una conexión con la base de datos
        $result = $this->getConnection();
        if (!$result["success"]) {
            throw new Exception($result["message"]);
        }
        $conn = $result["connection"];

        // Construir la consulta SQL con joins para obtener nombres en lugar de IDs
        $querySelect = "
            SELECT 
                c." . COMPRA_ID . ", 
                c." . COMPRA_NUMERO_FACTURA . ",
                pr.proveedornombre AS proveedorNombre,
                c." . COMPRA_MONTO_BRUTO . ", 
                c." . COMPRA_MONTO_NETO . ", 
                c." . COMPRA_TIPO_PAGO . ", 
                c." . COMPRA_FECHA_CREACION . ", 
                c." . COMPRA_FECHA_MODIFICACION . ", 
                c." . COMPRA_ESTADO . "
            FROM " . TB_COMPRA . " c
            JOIN  tbproveedor  pr ON c." . COMPRA_PROVEEDOR_ID . " = pr.proveedorid
            WHERE c." . COMPRA_ESTADO . " != false
        ";

        $result = mysqli_query($conn, $querySelect);
        if (!$result) {
            throw new Exception("Error en la consulta de compras: " . mysqli_error($conn));
        }

        // Crear la lista con los datos obtenidos
        $listaCompras = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $currentCompra = new Compra(
                $row[COMPRA_ID],                  
                $row[COMPRA_NUMERO_FACTURA], 
                $row['proveedorNombre'], // Usar el nombre del proveedor   
                $row[COMPRA_MONTO_BRUTO],     
                $row[COMPRA_MONTO_NETO],      
                $row[COMPRA_TIPO_PAGO],       
                $row[COMPRA_FECHA_CREACION],  
                $row[COMPRA_FECHA_MODIFICACION], 
                $row[COMPRA_ESTADO]        
            );
            array_push($listaCompras, $currentCompra);
        }

        return ["success" => true, "listaCompras" => $listaCompras];
    } catch (Exception $e) {
        // Manejo del error dentro del bloque catch
        $userMessage = $this->handleMysqlError(
            $e->getCode(), 
            $e->getMessage(),
            'Error al obtener la lista de compras desde la base de datos'
        );

        // Devolver mensaje amigable para el usuario
        return ["success" => false, "message" => $userMessage];
    } finally {
        // Cerramos la conexión
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

public function getPaginatedCompras($page, $size, $sort = null) {
    try {
        // Verificar que la página y el tamaño sean números enteros positivos
        if (!is_numeric($page) || $page < 1) {
            throw new Exception("El número de página debe ser un entero positivo.");
        }
        if (!is_numeric($size) || $size < 1) {
            throw new Exception("El tamaño de la página debe ser un entero positivo.");
        }
        
        // Calcular el offset para la paginación
        $offset = ($page - 1) * $size;

        // Establece una conexión con la base de datos
        $result = $this->getConnection();
        if (!$result["success"]) {
            throw new Exception($result["message"]);
        }
        $conn = $result["connection"];

        // Consultar el total de registros en la tabla
        $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_COMPRA . " WHERE " . COMPRA_ESTADO . " != false";
        $totalResult = mysqli_query($conn, $queryTotalCount);
        $totalRow = mysqli_fetch_assoc($totalResult);
        $totalRecords = (int) $totalRow['total'];
        $totalPages = ceil($totalRecords / $size);

        // Construir la consulta SQL con joins para obtener nombres en lugar de IDs
        $querySelect = "
            SELECT 
                c." . COMPRA_ID . ", 
                c." . COMPRA_NUMERO_FACTURA . ",
                pr.proveedornombre AS proveedorNombre,
                c." . COMPRA_MONTO_BRUTO . ", 
                c." . COMPRA_MONTO_NETO . ", 
                c." . COMPRA_TIPO_PAGO . ", 
                c." . COMPRA_FECHA_CREACION . ", 
                c." . COMPRA_FECHA_MODIFICACION . ", 
                c." . COMPRA_ESTADO . "
            FROM " . TB_COMPRA . " c
            JOIN tbproveedor pr ON c." . COMPRA_PROVEEDOR_ID .  " = pr.proveedorid
            WHERE c." . COMPRA_ESTADO . " != false
           
        ";
        $querySelect .=  "LIMIT ? OFFSET ?";
        // Preparar la consulta y vincular los parámetros
        $stmt = mysqli_prepare($conn, $querySelect);
        mysqli_stmt_bind_param($stmt, "ii", $size, $offset);

        // Ejecutar la consulta
        $result = mysqli_stmt_execute($stmt);
        if (!$result) {
            throw new Exception("Error en la ejecución de la consulta de compras: " . mysqli_error($conn));
        }

        // Obtener el resultado
        $result = mysqli_stmt_get_result($stmt);

        // Crear la lista de lotes
        $listaCompras = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $listaCompras[] = [
                'ID' => $row[COMPRA_ID],                    
                'NumeroFactura' => $row[COMPRA_NUMERO_FACTURA], 
                'Proveedor' => $row['proveedorNombre'],
                'MontoBruto' => $row[COMPRA_MONTO_BRUTO],   
                'MontoNeto' => $row[COMPRA_MONTO_NETO],     
                'TipoPago' => $row[COMPRA_TIPO_PAGO],       
                'FechaCreacion' => $row[COMPRA_FECHA_CREACION], 
                'FechaModificacion' => $row[COMPRA_FECHA_MODIFICACION], 
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
            $e->getCode(), 
            $e->getMessage(),
            'Error al obtener la lista de compras desde la base de datos'
        );

        // Devolver mensaje amigable para el usuario
        return ["success" => false, "message" => $userMessage];
    } finally {
        // Cerrar la conexión y el statement
        if (isset($stmt)) { mysqli_stmt_close($stmt); }
        if (isset($conn)) { mysqli_close($conn); }
    }
}

            private function compraExiste($compraID) {
                try {
                    // Establece una conexión con la base de datos
                    $result = $this->getConnection();
                    if (!$result["success"]) {
                        throw new Exception($result["message"]);
                    }
                    $conn = $result["connection"];
        
                    // Crea una consulta y un statement SQL para buscar el registro
                    $queryCheck = "SELECT * FROM " . TB_COMPRA . " WHERE " . COMPRA_ID . " = ? AND " . COMPRA_ID . " != false";
                    $stmt = mysqli_prepare($conn, $queryCheck);
        
                    // Asignar los parámetros y ejecutar la consulta
                    mysqli_stmt_bind_param($stmt, "i", $compraID);
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
                        'Error al obtener la lista de compras desde la base de datosssss'
                    );
        
                    // Devolver mensaje amigable para el usuario
                    return ["success" => false, "message" => $userMessage];
                } finally {
                    // Cierra la conexión y el statement
                    if (isset($stmt)) { mysqli_stmt_close($stmt); }
                    if (isset($conn)) { mysqli_close($conn); }
                }
            }

                public function deleteCompra($compraID) {
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
                             throw new Exception("No se encontró una dirección con el ID [" . $compraID . "]");
                         }
            
            
                        // Establece una conexión con la base de datos
                        $result = $this->getConnection();
                        if (!$result["success"]) {
                            throw new Exception($result["message"]);
                        }
                        $conn = $result["connection"];
            
                        // Crea una consulta y un statement SQL para eliminar el registro
                        $queryDelete = "UPDATE " . TB_COMPRA . " SET ". COMPRA_ESTADO . " = false WHERE " . COMPRA_ID . " = ?";
                        $stmt = mysqli_prepare($conn, $queryDelete);
                        mysqli_stmt_bind_param($stmt, 'i', $compraID);
            
                        // Ejecuta la consulta de eliminación
                        $result = mysqli_stmt_execute($stmt);
            
                        // Devuelve el resultado de la consulta
                        return ["success" => true, "message" => "Compra eliminado exitosamente"];
                    } catch (Exception $e) {
                        // Devolver mensaje amigable para el usuario
                        return ["success" => false, "message" => $e->getMessage()];
                    } finally {
                        // Cierra la conexión y el statement si están definidos
                        if (isset($stmt)) { mysqli_stmt_close($stmt); }
                        if (isset($conn)) { mysqli_close($conn); }
                    }
                }

                public function getCompraByID($compraID) {
                    try {
                        $check = $this->compraExiste($compraID);
                        if (!$check['success']) {
                            return $check;
                        }
                        if (!$check['exists']) {
                            Utils::writeLog("La compra con 'ID [$compraID]' no existe en la base de datos.", DATA_LOG_FILE);
                            throw new Exception("No existe ninguna compra en la base de datos que coincida con la información proporcionada.");
                        }
            
                        // Establece una conexion con la base de datos
                        $result = $this->getConnection();
                        if (!$result["success"]) {
                            throw new Exception($result["message"]);
                        }
                        $conn = $result["connection"];
            
                        // Obtenemos la información de la compra
                        $querySelect = "SELECT * FROM " . TB_COMPRA . " WHERE " . COMPRA_ID . " = ? AND " . COMPRA_ESTADO . " != false";
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
                                $row[COMPRA_NUMERO_FACTURA],  
                                $row[COMPRA_MONTO_BRUTO],
                                $row[COMPRA_MONTO_NETO],
                                $row[COMPRA_TIPO_PAGO],  
                                $row[COMPRA_PROVEEDOR_ID],
                                $row[COMPRA_FECHA_CREACION],
                                $row[COMPRA_FECHA_MODIFICACION],  
                                $row[COMPRA_ESTADO]
                            );
                        }
                
                        return ["success" => true, "compra" => $compra];
                    } catch (Exception $e) {
                        // Manejo del error dentro del bloque catch
                        $userMessage = $this->handleMysqlError(
                            $e->getCode(), 
                            $e->getMessage(),
                            'Error al obtener la compra desde la base de datos'
                        );
                
                        // Devolver mensaje amigable para el usuario
                        return ["success" => false, "message" => $userMessage];
                    } finally {
                        // Cerrar la conexión y el statement
                        if (isset($stmt)) { mysqli_stmt_close($stmt); }
                        if (isset($conn)) { mysqli_close($conn); }
                    }
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