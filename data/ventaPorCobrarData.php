<?php

require_once dirname(__DIR__, 1) . '/data/data.php';
require_once dirname(__DIR__, 1) . '/domain/VentaPorCobrar.php';
require_once dirname(__DIR__, 1) . '/utils/Variables.php';
require_once dirname(__DIR__, 1) . '/data/ventaData.php';
require_once dirname(__DIR__, 1) . '/data/ventaDetalleData.php';

class VentaPorCobrarData extends Data {

    private $className;
    private $ventadata;
    private $ventaDetalleData;

    public function __construct(){
        parent::__construct();
        $this->className = get_class($this);
        $this->ventadata = new VentaData();
        $this->ventaDetalleData = new VentaDetalleData();
    }

    function ventaPorCobrarIdExiste($id){
        try{
            $result = $this->getConnection();
            if (!$result['success']) {
                throw new Exception($result['message']);
            }
            $conn = $result['connection'];
    
            if ($id <= 0 || !is_numeric($id)) {
                Utils::writeLog("LINEA[".__LINE__."]Error el id de venta por cobrar no es valido. ID [$id]",DATA_LOG_FILE);
                throw new Exception("El id de la venta por combrar es inválido.");
            }
    
            $query = "SELECT " . VENTA_POR_COBRAR_ID . " FROM " . TB_VENTA_POR_COBRAR. " WHERE " . VENTA_POR_COBRAR_ID . " = ?";
            
            $stmt = mysqli_prepare($conn, $query);
            if (!$stmt) { throw new Exception("Error en la preparación de la consulta."); }
    
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);
    
            if (mysqli_num_rows($resultado) > 0) {
                return ["success" => true, "exists" => true];
            }
            return ["success" => true, "exists" => false];
        }catch(Exception $e){
            $userMessage = $this->handleMysqlError(
                $e->getCode(), $e->getMessage(),
                'Ocurrió un error al verificar la existencia de la venta por combrar',
                $this->className
            );
            return ["success"=>false, "message"=>$userMessage];
        }finally{
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
        }
    }

    function ventaPorCobrarClienteExiste($idCliente,$onlyActive = true, $delete = false){
        try{
            $result = $this->getConnection();
            if (!$result["success"]) { throw new Exception($result["message"]); }
            $conn = $result["connection"];

            $query = "SELECT * FROM " . TB_VENTA_POR_COBRAR 
            .( $onlyActive ? " WHERE ". VENTA_POR_COBRAR_ESTADO . " != ".($delete? 'TRUE': 'FALSE'):" ");
            $result = mysqli_query($conn,$query);
            $listaVentasCobrar =[];
            $mensaje ="";
            while($row = mysqli_fetch_assoc($result)){
                $resultado = $this->ventadata->getVentaByID($row[VENTA_ID]);
                if ($resultado["success"]){
                    $venta = $resultado["venta"];
                    $mensaje = $row[VENTA_POR_COBRAR_CANCELADO];
                    if ($venta->getVentaCliente()->getClienteID() ===intval($idCliente) && boolval($row[VENTA_POR_COBRAR_CANCELADO]) === false){
                        $ventaCobrarObj = new VentaPorCobrar(
                            $row[VENTA_POR_COBRAR_ID],
                            $venta,
                            $row[VENTA_POR_COBRAR_VENCIMIENTO],
                            $row[VENTA_POR_COBRAR_CANCELADO],
                            $row[VENTA_POR_COBRAR_NOTAS],
                            $row[VENTA_POR_COBRAR_ESTADO]
                        );
                        $listaVentasCobrar[] = $ventaCobrarObj;
                    }
                }
            }
            if (!empty($listaVentasCobrar)){
                return["success"=>true, "exists"=>true,"listaVentasPorCobrar"=>$listaVentasCobrar];
            }else{
                return["success"=>true, "exists"=>false];
            }
        }catch(Exception $e){
            $message = $this->handleMysqlError(
                $e->getCode(),$e->getMessage(),
                "Error al obtener las venta por cobrar",
                $this->className
            );
            return ["success"=>false, "message"=>$message];
        }finally{
             // Cierra la conexión y el statement
             if (isset($stmt)) { mysqli_stmt_close($stmt); }
             if (isset($conn)) { mysqli_close($conn); }
        }
    }

    function verificarVenta($ventaid, $update = false, $verificarVentaPorCobrar = true,$ventaPorCobrarID = 0){

        try{
            $result = $this->getConnection();
            if (!$result["success"]) { throw new Exception($result["message"]); }
            $conn = $result["connection"];
            

            if(empty($ventaid) || !is_numeric($ventaid) || $ventaid <= 0){
                $mensaje = "En vetna por cobrar la venta id no es valida. ventaID [$ventaid]";
                Utils::writeLog($mensaje,DATA_LOG_FILE,ERROR_MESSAGE,$this->className,__LINE__);
                throw new Exception("La venta no es valida");
            }
            
            $tablaVerificacion = ($verificarVentaPorCobrar) ? TB_VENTA_POR_COBRAR : TB_VENTA;
            $columEstado = ($verificarVentaPorCobrar) ? VENTA_POR_COBRAR_ESTADO : VENTA_ESTADO;

            $query="SELECT " . VENTA_ID. " FROM " . $tablaVerificacion ." WHERE " . VENTA_ID. " = ? AND ".$columEstado. " != FALSE ";

            $params=[$ventaid];
            $types="i";

            if($update && $verificarVentaPorCobrar){
                $query .= " AND ". VENTA_POR_COBRAR_ID . " <> ? ";
                $params = array_merge($params,[$ventaid]);
                $types .="i";
            }

            $stmt = mysqli_prepare($conn,$query);
            mysqli_stmt_bind_param($stmt,$types,...$params);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);

            if($row = mysqli_fetch_all($resultado)){
                return ["success"=>true,"exists"=>True];
                
            }
            return ["success"=>true,"exists"=>false];
        }catch(Exception $e){
            $message = $this->handleMysqlError(
                $e->getCode(),$e->getMessage(),
                "Error al verificar la venta por cobrar",
                $this->className
            );
            return ["success"=>false, "message"=>$message];
        }finally{
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }

    }

    function insertarListaVentaPorCobrar($ventaCobrar, $listaDetalles, $conn = null) {
        $createdConn = false;
        $consecutivo = null;

        try {
            if(is_null($conn)) {
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
                $createdConn = true;
                mysqli_begin_transaction($conn);
            }

            // Obtiene la venta asociada a la venta por cobrar
            $venta = $ventaCobrar->getVentaPorCobrarVenta();
            if (!$venta) { throw new Exception("No se encontró la venta asociada al detalle de venta."); }

            // Inserta la venta en la tabla TB_VENTA
            $insertVenta = $this->ventadata->insertVenta($venta, $conn);
            if (!$insertVenta["success"]) { throw new Exception($insertVenta["message"]); }
            $datosVenta = ['id' => $insertVenta["id"], 'consecutivo' => $insertVenta["consecutivo"]];

            // Obtiene el consecutivo de la venta
            $consecutivo = $datosVenta['consecutivo'];

            // Inserta la cada venta detalle de la venta en la tabla TB_VENTA_DETALLE
            foreach ($listaDetalles as $detalle) {
                $detalle->getVentaDetalleVenta()->setVentaID($datosVenta['id']);
                $insertDetalle = $this->ventaDetalleData->insertVentaDetalle($detalle, $conn);
                if (!$insertDetalle["success"]) { throw new Exception($insertDetalle["message"]); }
            }

            // Inserta la venta por cobrar en la tabla TB_VENTA_POR_COBRAR
            $ventaCobrar->getVentaPorCobrarVenta()->setVentaID($datosVenta['id']);
            $insertVentaCobrar = $this->InsertaVentaPorCobrar($ventaCobrar, $conn);
            if (!$insertVentaCobrar["success"]) { throw new Exception($insertVentaCobrar["message"]); }

            // Commit de la transacción
            if ($createdConn) {
                mysqli_commit($conn);
            }

            // Retorna el mensaje de éxito
            $message = "Venta creada correctamente. Consecutivo: " . $consecutivo;
            return ["success" => true, "message" => $message, "consecutivo" => $consecutivo, "id" => $insertVentaCobrar['id']];
        } catch (Exception $e) {
            // Rollback de la transacción y deshace el consecutivo
            if (isset($conn) && $createdConn) { mysqli_rollback($conn); }
            if ($consecutivo) { Utils::deshacerConsecutivo($consecutivo); }

            // Log y manejo de errores
            $logMessage = "Error al crear la venta en la base de datos: " . $e->getMessage();
            $userMessage = $this->handleMysqlError($e->getCode(), $e->getMessage(), $logMessage, $this->className, __LINE__);
            return ["success" => false, "message" => $userMessage];
        } finally {
            // Cierra la conexión
            if (isset($conn) && !$createdConn) { mysqli_close($conn); }
        }
    }

    function InsertaVentaPorCobrar($ventaCobrar, $conn = null ){
        $nuevaConexion = false;
        $stmt = null;
        
        try{
            if($conn === null){
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
                $nuevaConexion = true;
                mysqli_begin_transaction($conn);
            }
            
            $id = $ventaCobrar->getVentaPorCobrarID();
            $ventaid = $ventaCobrar->getVentaPorCobrarVenta()->getVentaID();
            $fechaVence = $ventaCobrar->getVentaPorCobrarFechaVencimiento();
            $estadoCancelado =$ventaCobrar->getVentaPorCobrarCancelado();
            $notas = $ventaCobrar->getVentaPorCobrarNotas();
            $estado = $ventaCobrar->getVentaPorCobrarEstado();


            if(!Utils::fechaMayorOIgualAHoy($fechaVence)){ 
                $mensaje = "Verificacion fecha vencimiento no valida . fecha vencimiento [$fechaVence]";
                Utils::writeLog($mensaje,DATA_LOG_FILE,ERROR_MESSAGE,$this->className,__LINE__);
                throw new  Exception("La fecha de vencimiento no es valida"); 
            }

            //verifica compra detalle en la tabla TB_VENTA_POR_COBRAR
            $result = $this->verificarVenta($ventaid);
            if(!$result["success"]){ return $result; }
            if($result["exists"]){ 
                $mensaje = "Verificacion de la venta por cobrar venta id ya esta relacionada con otra venta por cobrar. ventaID [$ventaid]";
                Utils::writeLog($mensaje,DATA_LOG_FILE,ERROR_MESSAGE,$this->className,__LINE__);
                throw new Exception("La venta ya esta añadida a otra venta por cobrar"); 
            }

            // Obtenemos el último ID
            $queryGetLastId = "SELECT MAX(" . VENTA_POR_COBRAR_ID . ") FROM " . TB_VENTA_POR_COBRAR;
            $idCont = mysqli_query($conn, $queryGetLastId);
            $nextId = 1;

            // Calcula el siguiente ID para la nueva entrada
            if ($row = mysqli_fetch_row($idCont)) {
                $nextId = (int) trim($row[0]) + 1;
            }

            //consulta de insert
            $queryInsert=
            "INSERT INTO " . TB_VENTA_POR_COBRAR. " ( " 
            . VENTA_POR_COBRAR_ID. ", "
            . VENTA_ID . ","
            . VENTA_POR_COBRAR_VENCIMIENTO. ","
            . VENTA_POR_COBRAR_CANCELADO. ","
            . VENTA_POR_COBRAR_NOTAS
            ." ) VALUES (?,?,?,?,?) "; 

            $stmt = mysqli_prepare($conn,$queryInsert);

            mysqli_stmt_bind_param(
                $stmt,
                "iisis",
                $nextId,
                $ventaid,
                $fechaVence,
                $estadoCancelado,
                $notas
            );

            $result = mysqli_stmt_execute($stmt);
            if($nuevaConexion){
                mysqli_commit($conn);
            }
            return ["success"=>true, "message"=>"venta por cobrar insertado correctamente", "id"=>$nextId];
        }catch(Exception $e){
            if(isset($conn) && $nuevaConexion){
                mysqli_rollback($conn);
            }
            $message = $this->handleMysqlError(
                $e->getCode(), $e->getMessage(),
                'Ocurrio un error al insertar la venta por cobrar a la base de datos',
                $this->className
            );
            return ["succes"=>false, "message"=>$message];
        }finally{
            // Cierra la conexión y el statement
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn) && $nuevaConexion) { mysqli_close($conn); }
        }

    }

    function UpdateVentaPorCobrar($ventaCobrar, $conn = null){
        $nuevaConexion = false;
        $stmt = null;
        try{
            if($conn === null){
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
                $nuevaConexion = true;
                mysqli_begin_transaction($conn);
            }
            $id = $ventaCobrar->getVentaPorCobrarID();
            $ventaid = $ventaCobrar->getVentaPorCobrarVenta()->getVentaID();
            $fechaVence = $ventaCobrar->getVentaPorCobrarFechaVencimiento();
            $estadoCancelado =$ventaCobrar->getVentaPorCobrarCancelado();
            $notas = $ventaCobrar->getVentaPorCobrarNotas();
            $estado = $ventaCobrar->getVentaPorCobrarEstado();
            
            //verificamos la existencia del id
            $result = $this->ventaPorCobrarIdExiste($id);
            if(!$result["success"]){ throw new Exception($result["message"]); }
            if(!$result["exists"]){ 
                $mensaje = "Verificacion de la venta por cobrar venta id no existe. ventaID [$id]";
                Utils::writeLog($mensaje,DATA_LOG_FILE,ERROR_MESSAGE,$this->className,__LINE__);
                throw new Exception("No existe la venta por cobrar"); 
            }
   
            $result = $this->verificarVenta($ventaid,false,false);
            if(!$result["success"]){ return $result; }
            if(!$result["exists"]){ 
                $mensaje = "Verificacion de la venta por cobrar venta id no existe. ventaID [$ventaid]";
                Utils::writeLog($mensaje,DATA_LOG_FILE,ERROR_MESSAGE,$this->className,__LINE__);
                throw new Exception("La venta no se ha creado o esta inactivo."); 
            }

            $result = $this->verificarVenta($ventaid,true,true,$id);
            if(!$result["success"]){ return $result; }
            if($result["exists"]){ 
                $mensaje = "Verificacion de la venta por cobrar venta id esta relacionada con otra venta por cobrar. ventaID [$ventaid]";
                Utils::writeLog($mensaje,DATA_LOG_FILE,ERROR_MESSAGE,$this->className,__LINE__);
                throw new Exception("La venta ya esta añadida a otra venta por cobrar."); 
            }

            $queryUdate = 
            "UPDATE " . TB_VENTA_POR_COBRAR .
            " SET " .
                VENTA_ID . " = ?, ".
                VENTA_POR_COBRAR_VENCIMIENTO . " = ?, ". 
                VENTA_POR_COBRAR_CANCELADO . " = ?,".
                VENTA_POR_COBRAR_NOTAS . " = ?,".
                VENTA_POR_COBRAR_ESTADO. " = TRUE " .
            " WHERE " . VENTA_POR_COBRAR_ID . " = ? ";
            
            $stmt = mysqli_prepare($conn,$queryUdate);

            mysqli_stmt_bind_param(
                $stmt,
                "isisi",
                $ventaid,
                $fechaVence,
                $estadoCancelado,
                $notas,
                $id
            );

            $result = mysqli_stmt_execute($stmt);

            if($nuevaConexion){
                mysqli_commit($conn);
            }
            return ["success"=>true, "message"=>"venta por cobrar Actualizado correctamente."];
        }catch (Exception $e){
            if(isset($conn) && $nuevaConexion){
                mysqli_rollback($conn);
            }
            $message = $this->handleMysqlError(
                $e->getCode(),$e->getMessage(),
                "Error al actualizar la venta por cobrar",
                $this->className
            );
            return ["success" => false, "message"=>$message];
        }finally{
             // Cierra la conexión y el statement
             if (isset($stmt)) { mysqli_stmt_close($stmt); }
             if (isset($conn) && $nuevaConexion) { mysqli_close($conn); }
        }
    }

    function deleteVentaPorCobrar($ventaCobrarId,$conn= null){
        $nuevaConexion = false;
        $stmt = null;
        try{
            if($conn === null){
                $result = $this->getConnection();
                if (!$result["success"]) { throw new Exception($result["message"]); }
                $conn = $result["connection"];
                $nuevaConexion = true;
                mysqli_begin_transaction($conn);
            }
            
            //Verificamos el id
            $result = $this->ventaPorCobrarIdExiste($ventaCobrarId);
            if(!$result["success"]){
                throw new Exception($result["message"]);
            }
            if(!$result["exists"]){
                $mensaje = "La venta por cobrar pagar no existe . ID [$ventaCobrarId]";
                Utils::writeLog($mensaje,DATA_LOG_FILE,ERROR_MESSAGE,$this->className,__LINE__);
                throw new Exception("No existe la venta por cobrar");
            }
            //Creacion del query
            $queryDelete="UPDATE " . TB_VENTA_POR_COBRAR . " SET " . VENTA_POR_COBRAR_ESTADO . " = FALSE " .
                            " WHERE " . VENTA_POR_COBRAR_ID . " = ? ";
                            
            $stmt = mysqli_prepare($conn, $queryDelete);
            mysqli_stmt_bind_param($stmt,"i",$ventaCobrarId);
            $result = mysqli_stmt_execute($stmt);

            if($nuevaConexion){
                mysqli_commit($conn);
            }

            return ["success"=>true, "message"=>"venta por cobrar eliminada correctamente."];
        }catch(Exception $e){
            if(isset($conn) && $nuevaConexion){
                mysqli_rollback($conn);
            }
            $message = $this->handleMysqlError(
                $e->getCode(),$e->getMessage(),
                "Error al eliminar la venta por cobrar",
                $this->className
            );
            return ["success"=> false, "message" => $message];
        }finally{
            // Cierra la conexión y el statement
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn) && $nuevaConexion) { mysqli_close($conn); }
        }

    }

    function getALLVentePorCobrar($onlyActive = false, $delete = false){
        try{
            $result = $this->getConnection();
            if (!$result["success"]) { throw new Exception($result["message"]); }
            $conn = $result["connection"];

            $query = "SELECT * FROM " . TB_VENTA_POR_COBRAR 
            .( $onlyActive ? " WHERE ". VENTA_POR_COBRAR_ESTADO . " != ".($delete? 'TRUE': 'FALSE'):" ");
            $result = mysqli_query($conn,$query);


            $listaVentasCobrar =[];

            while($row = mysqli_fetch_assoc($result)){
                $venta = NULL;
                $resultado = $this->ventadata->getVentaByID($row[VENTA_ID]);
                if ($resultado["success"]){
                    $venta = $resultado["venta"];
                }
                $ventaCobrarObj = new VentaPorCobrar(
                    $row[VENTA_POR_COBRAR_ID],
                    $venta,
                    $row[VENTA_POR_COBRAR_VENCIMIENTO],
                    $row[VENTA_POR_COBRAR_CANCELADO],
                    $row[VENTA_POR_COBRAR_NOTAS],
                    $row[VENTA_POR_COBRAR_ESTADO]
                );
                $listaVentasCobrar[] = $ventaCobrarObj;
            }
            return["success"=>true, "ListaVentaPorCobrar"=>$listaVentasCobrar];
        }catch(Exception $e){
            $message = $this->handleMysqlError(
                $e->getCode(),$e->getMessage(),
                "Error al obtener las venta por cobrar",
                $this->className
            );
            return ["success"=>false, "message"=>$message];
        }finally{
             // Cierra la conexión y el statement
             if (isset($stmt)) { mysqli_stmt_close($stmt); }
             if (isset($conn)) { mysqli_close($conn); }
        }
    }

    function getPaginaCompraPorPagar($search,$page,$size,$sort=null,$onlyActive= false,$deleted=false){
        try{
            $offset = ($page-1)*$size;

            $result = $this->getConnection();
            if (!$result["success"]) { throw new Exception($result["message"]); }
            $conn = $result["connection"];

            $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_VENTA_POR_COBRAR;
            if ($onlyActive) { $queryTotalCount .= " WHERE " . VENTA_POR_COBRAR_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE'); }

            // Obtener el total de registros y calcular el total de páginas
            $totalResult = mysqli_query($conn, $queryTotalCount);
            $totalRow = mysqli_fetch_assoc($totalResult);
            $totalRecords = (int) $totalRow['total'];
            $totalPages = ceil($totalRecords / $size);


            $querySelect = "SELECT * FROM " . TB_VENTA_POR_COBRAR;

            $params=[];
            $types="";

            if($search){
                $querySelect.= " WHERE (" . VENTA_POR_COBRAR_CANCELADO . " LIKE ? ";
                $querySelect.=" OR " .VENTA_POR_COBRAR_NOTAS . " LIKE ? )";
                $seParam = "%".$search."%";
                $params =[$seParam,$seParam];
                $types.="ss";
            }

            if($onlyActive){
                $querySelect .= $search ? " AND " : " WHERE ";
                $querySelect .= VENTA_POR_COBRAR_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE"); 
            }

            if($sort){
                $querySelect.=" ORDER BY ventaporcobrar".$sort." ";
            }else{
                $querySelect.=" ORDER BY ".  VENTA_POR_COBRAR_ID." ";
            }

            $querySelect.=" LIMIT ? OFFSET ? ";
            $params= array_merge($params,[$size,$offset]);
            $types.="ii";

            $stmt = mysqli_prepare($conn, $querySelect);
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
    
            // Ejecutar la consulta y obtener los resultados
            $result = mysqli_stmt_get_result($stmt);

            $listaVentasCobrar =[];

            while($row = mysqli_fetch_assoc($result)){
                $venta = NULL;
                $resultado= $this->ventadata->getVentaByID($row[VENTA_ID]);
                if ($resultado["success"]){
                    $venta = $resultado["venta"];
                }
                $ventaCobrarObj = new VentaPorCobrar(
                    $row[VENTA_POR_COBRAR_ID],
                    $venta,
                    $row[VENTA_POR_COBRAR_VENCIMIENTO],
                    $row[VENTA_POR_COBRAR_CANCELADO],
                    $row[VENTA_POR_COBRAR_NOTAS],
                    $row[VENTA_POR_COBRAR_ESTADO]
                );
                $listaVentasCobrar[] = $ventaCobrarObj;
            }

            return [
                    "success" => true,
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "listaVentasPorCobrar" => $listaVentasCobrar
            ];
        }catch(Exception $e){
            $message =$this->handleMysqlError(
                $e->getCode(),$e->getMessage(),
                "Error al listar la pagina de venta por cobrar",
                $this->className
            );
            return ["success"=>false,"message"=>$message];
        }finally{
            // Cerrar la conexión y el statement
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }

    function getVentaPorCobrarById($id, $onlyActive = true, $delete = false){
        try{
            //Verificar si existe o no
            $result = $this->getConnection();
            if (!$result["success"]) { throw new Exception($result["message"]); }
            $conn = $result["connection"]; 


            $result = $this->ventaPorCobrarIdExiste($id);
            if(!$result["success"]){
                throw new Exception($result["message"]);
            }
            if(!$result["exists"]){
                $mensaje = "La venta por cobrar no existe . ID [$id]";
                Utils::writeLog($mensaje,DATA_LOG_FILE,ERROR_MESSAGE,$this->className,__LINE__);
                throw new Exception("No existe la venta por cobrar");
            }

            //inicion del query
            $querySelect = "SELECT * FROM " . TB_VENTA_POR_COBRAR. 
            " WHERE " . VENTA_POR_COBRAR_ID. " = ? ". 
            ($onlyActive ? " AND " . VENTA_POR_COBRAR_ESTADO . " != " ($delete ? 'TRUE': 'FALSE'): " " );
            
            $stmt = mysqli_prepare($conn,$querySelect);
            mysqli_stmt_bind_param($stmt,"i",$id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if($row = mysqli_fetch_assoc($result)){
                $venta = NULL;
                $resultado = $this->ventadata->getVentaByID($row[VENTA_ID]);
                if ($resultado["success"]){
                    $venta = $resultado["venta"];
                }
                $ventaCobrarObj = new VentaPorCobrar(
                    $row[VENTA_POR_COBRAR_ID],
                    $venta,
                    $row[VENTA_POR_COBRAR_VENCIMIENTO],
                    $row[VENTA_POR_COBRAR_CANCELADO],
                    $row[VENTA_POR_COBRAR_NOTAS],
                    $row[VENTA_POR_COBRAR_ESTADO]
                );
                return ["success"=>true, "VentaPorCobrar"=>$ventaCobrarObj];
            }
            return ["success"=> false, "message"=>"No se encontro la VentaPorCobrar."];
        }catch(Exception $e){
            $message = $this->handleMysqlError(
                $e->getCode(),$e->getMessage(),
                "Error al obtener la venta por cobrar de la base de datos",
                $this->className
            );
            return ["success" => false, "message"=>$message];
        }finally{
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }
    
}