<?php
require_once dirname(__DIR__, 1) . '/domain/CompraPorPagar.php';
require_once dirname(__DIR__, 1) . "/domain/Compra.php";
require_once dirname(__DIR__, 1) . '/utils/Utils.php';
require_once dirname(__DIR__, 1) . '/utils/Variables.php';
require_once dirname(__DIR__, 1) . '/data/data.php';
require_once dirname(__DIR__, 1) . '/data/compraData.php';

class CompraPorPagarData extends Data{
    private $className;
    private $compradata;
    public function __construct(){
        parent::__construct();
        $this->className = get_class($this);
        $this->compradata = new CompraData();
    }

    //Verificar si la compraPoragarExiste
    function compraPorPagarIdExiste($id){
        try{
            $result = $this->getConnection();
            if (!$result['success']) {
                throw new Exception($result['message']);
            }
            $conn = $result['connection'];
    
            if ($id <= 0 || !is_numeric($id)) {
                Utils::writeLog("LINEA[".__LINE__."]Error el id de compra por pagar no es valido. ID [$id]",DATA_LOG_FILE);
                throw new Exception("El id de la compra por pagar es inválido.");
            }
    
            $query = "SELECT " . COMPRA_POR_PAGAR_ID . " FROM " . TB_COMPRA_POR_PAGAR . " WHERE " . COMPRA_POR_PAGAR_ID . " = ?";
            
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
                'Ocurrió un error al verificar la existencia de la cuenta por pagar',
                $this->className
            );
            return ["success"=>false, "message"=>$userMessage];
        }finally{
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
        }
    }

    function verificarComprar($compraid, $update = false, $verificarEnCompraPorPagar = true,$compraPagarId = 0){
        try{
            $result = $this->getConnection();
            if (!$result["success"]) { throw new Exception($result["message"]); }
            $conn = $result["connection"];
            

            if(empty($compraid) || !is_numeric($compraid) || $compraid <= 0){
                $mensaje = "En compra por pagar la compra id no es valida. compraID [$compraid]";
                Utils::writeLog($mensaje,DATA_LOG_FILE,ERROR_MESSAGE,$this->className,__LINE__);
                throw new Exception("La compra detalle no valida");
            }
            
            $tablaVerificacion = ($verificarEnCompraPorPagar) ? TB_COMPRA_POR_PAGAR : TB_COMPRA;
            $columEstado = ($verificarEnCompraPorPagar)? COMPRA_POR_PAGAR_ESTADO : COMPRA_ESTADO;

            $query="SELECT " . COMPRA_ID. " FROM " . $tablaVerificacion ." WHERE " . COMPRA_ID. " = ? AND ".$columEstado. " != FALSE ";

            $params=[$compraid];
            $types="i";

            if($update && $verificarEnCompraPorPagar){
                $query .= " AND ". COMPRA_POR_PAGAR_ID . " <> ? ";
                $params = array_merge($params,[$compraPagarId]);
                $types .="i";
            }

            $stmt = mysqli_prepare($conn,$query);
            mysqli_stmt_bind_param($stmt,$types,...$params);
            mysqli_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);

            if($row = mysqli_fetch_all($resultado)){
                return ["success"=>true,"exists"=>True];
                
            }
            return ["success"=>true,"exists"=>false];
        }catch(Exception $e){
            $message = $this->handleMysqlError(
                $e->getCode(),$e->getMessage(),
                "Error al verificar la compra detalle",
                $this->className
            );
            return ["success"=>false, "message"=>$message];
        }finally{
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }

    }

    function InsertarCompraPorPagar($compraPagar, $conn = null ){
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
            $id = $compraPagar->getCompraPorPagarID();
            $compraId = $compraPagar->getCompraPorPagarCompra()->getCompraID();
            $fechaVence = $compraPagar->getCompraPorPagarFechaVencimiento();
            $estadoCompra =$compraPagar->getCompraPorPagarCancelado();
            $notas = $compraPagar->getCompraPorPagarNotas();
            $estado = $compraPagar->getCompraPorPagarEstado();


            if(!Utils::fechaMayorOIgualAHoy($fechaVence)){ 
                $mensaje = "Verificacion fecha vencimiento no valida . fecha vencimiento [$fechaVence]";
                Utils::writeLog($mensaje,DATA_LOG_FILE,ERROR_MESSAGE,$this->className,__LINE__);
                throw new  Exception("La fecha de vencimiento no es valida"); 
            }
            // verifica la compra detalle en la tabla TB_COMPRA_DETALLE
            $result = $this->verificarComprar($compraId,false,false);
            if(!$result["success"]){ return $result; }
            if(!$result["exists"]){ 
                $mensaje = "Verificacion de la compra por pagar compra id no existe o esta inactiva. compraID [$compraId]";
                Utils::writeLog($mensaje,DATA_LOG_FILE,ERROR_MESSAGE,$this->className,__LINE__);
                throw new Exception("La compra no se ha creado o no es valida."); 
            }

            //verifica compra detalle en la tabla TB_COMPRA_POR_PAGAR
            $result = $this->verificarComprar($compraId);
            if(!$result["success"]){ return $result; }
            if($result["exists"]){ 
                $mensaje = "Verificacion de la compra por pagar compra id ya esta relacionada con otra compra. compraID [$compraId]";
                Utils::writeLog($mensaje,DATA_LOG_FILE,ERROR_MESSAGE,$this->className,__LINE__);
                throw new Exception("La compra ya esta añadida a otra compra por pagar."); 
            }
            

            // Obtenemos el último ID de la tabla tbproducto
            $queryGetLastId = "SELECT MAX(" . COMPRA_POR_PAGAR_ID . ") FROM " . TB_COMPRA_POR_PAGAR;
            $idCont = mysqli_query($conn, $queryGetLastId);
            $nextId = 1;

            // Calcula el siguiente ID para la nueva entrada
            if ($row = mysqli_fetch_row($idCont)) {
                $nextId = (int) trim($row[0]) + 1;
            }

            //consulta de insert
            $queryInsert=
            "INSERT INTO " . TB_COMPRA_POR_PAGAR . " ( " 
            . COMPRA_POR_PAGAR_ID . ", "
            . COMPRA_ID . ","
            . COMPRA_POR_PAGAR_VENCIMIENTO . ","
            . COMPRA_POR_PAGAR_CANCELADO . ","
            . COMPRA_POR_PAGAR_NOTAS
            ." ) VALUES (?,?,?,?,?) "; 

            $stmt = mysqli_prepare($conn,$queryInsert);

            mysqli_stmt_bind_param(
                $stmt,
                "iisss",
                $nextId,
                $compraId,
                $fechaVence,
                $estadoCompra,
                $notas
            );

            $result = mysqli_stmt_execute($stmt);
            if($nuevaConexion){
                mysqli_commit($conn);
            }
            return ["success"=>true, "message"=>"Compra por pagar insertado correctamente", "id"=>$nextId];
        }catch(Exception $e){
            if(isset($conn) && $nuevaConexion){
                mysqli_rollback($conn);
            }
            $message = $this->handleMysqlError(
                $e->getCode(), $e->getMessage(),
                'Ocurrio un error al insertar la compra a la base de datos',
                $this->className
            );
            return ["succes"=>false, "message"=>$message];
        }finally{
            // Cierra la conexión y el statement
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn) && $nuevaConexion) { mysqli_close($conn); }
        }

    }

    function ActualizarCompraPorPagar($compraPagar, $conn = null){
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
            $id = $compraPagar->getCompraPorPagarID();
            $compraId = $compraPagar->getCompraPorPagarCompra()->getCompraID();
            $fechaVence = $compraPagar->getCompraPorPagarFechaVencimiento();
            $estadoCompra =$compraPagar->getCompraPorPagarCancelado();
            $notas = $compraPagar->getCompraPorPagarNotas();
            $estado = $compraPagar->getCompraPorPagarEstado();
            
            //verificamos la existencia del id
            $result = $this->compraPorPagarIdExiste($id);
            if(!$result["success"]){ throw new Exception($result["message"]); }
            if(!$result["exists"]){ 
                $mensaje = "Verificacion de la compra por pagar compra id no existe. compraID [$compraId]";
                Utils::writeLog($mensaje,DATA_LOG_FILE,ERROR_MESSAGE,$this->className,__LINE__);
                throw new Exception("No existe la compra por por pagar"); 
            }
            // if(!Utils::fechaMayorOIgualAHoy($fechaVence)){ 
            //     Utils::writeLog("Verificacion fecha vencimiento no valida . fecha vencimiento [$fechaVence]",DATA_LOG_FILE);
            //     throw new  Exception("La fecha de vencimiento no es valida"); 
            // }
            // verifica la compra detalle en la tabla TB_COMPRA_DETALLE
            $result = $this->verificarComprar($compraId,false,false);
            if(!$result["success"]){ return $result; }
            if(!$result["exists"]){ 
                $mensaje = "Verificacion de la compra por pagar compra id no existe. compraID [$id]";
                Utils::writeLog($mensaje,DATA_LOG_FILE,ERROR_MESSAGE,$this->className,__LINE__);
                throw new Exception("La compra no se ha creado."); 
            }

            //verifica compra detalle en la tabla TB_COMPRA_POR_PAGAR
            $result = $this->verificarComprar($compraId,true,true,$id);
            if(!$result["success"]){ return $result; }
            if($result["exists"]){ 
                $mensaje = "Verificacion de la compra por pagar compra id esta relacionada con otra compra por pagar. compraID [$id]";
                Utils::writeLog($mensaje,DATA_LOG_FILE,ERROR_MESSAGE,$this->className,__LINE__);
                throw new Exception("La compra detalle ya esta añadida a otra compra por pagar."); 
            }

            $queryUdate = 
            "UPDATE " . TB_COMPRA_POR_PAGAR .
            " SET " .
                COMPRA_ID . " = ?, ".
                COMPRA_POR_PAGAR_VENCIMIENTO . " = ?, ". 
                COMPRA_POR_PAGAR_CANCELADO . " = ?,".
                COMPRA_POR_PAGAR_NOTAS . " = ?,".
                COMPRA_POR_PAGAR_ESTADO . " = TRUE " .
            " WHERE " . COMPRA_POR_PAGAR_ID . " = ? ";
            
            $stmt = mysqli_prepare($conn,$queryUdate);

            mysqli_stmt_bind_param(
                $stmt,
                "isssi",
                $compraId,
                $fechaVence,
                $estadoCompra,
                $notas,
                $id
            );

            $result = mysqli_stmt_execute($stmt);

            if($nuevaConexion){
                mysqli_commit($conn);
            }
            return ["success"=>true, "message"=>"Compra por pagar Actualizado correctamente."];
        }catch (Exception $e){
            if(isset($conn) && $nuevaConexion){
                mysqli_rollback($conn);
            }
            $message = $this->handleMysqlError(
                $e->getCode(),$e->getMessage(),
                "Error al actualizar la compra por pagar",
                $this->className
            );
            return ["success" => false, "message"=>$message];
        }finally{
             // Cierra la conexión y el statement
             if (isset($stmt)) { mysqli_stmt_close($stmt); }
             if (isset($conn) && $nuevaConexion) { mysqli_close($conn); }
        }
    }

    function deleteCopraPorPagar($compraPagarId,$conn= null){
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
            $result = $this->compraPorPagarIdExiste($id);
            if(!$result["success"]){
                throw new Exception($result["message"]);
            }
            if(!$result["exists"]){
                $mensaje = "La compra por pagar no existe . ID [$id]";
                Utils::writeLog($mensaje,DATA_LOG_FILE,ERROR_MESSAGE,$this->className,__LINE__);
                throw new Exception("No existe la compra por por pagar");
            }
            //Creacion del query
            $queryDelete="UPDATE " . TB_COMPRA_POR_PAGAR . " SET " . COMPRA_POR_PAGAR_ESTADO . " = FALSE " .
                            " WHERE " . COMPRA_POR_PAGAR_ID . " = ? ";
                            
            $stmt = mysqli_prepare($conn, $queryDelete);
            mysqli_stmt_bind_param($stmt,"i",$id);
            $result = mysqli_stmt_execute($stmt);

            if($nuevaConexion){
                mysqli_commit($conn);
            }

            return ["success"=>true, "message"=>"Compra por pagar eliminada correctamente."];
        }catch(Exception $e){
            if(isset($conn) && $nuevaConexion){
                mysqli_rollback($conn);
            }
            $message = $this->handleMysqlError(
                $e->getCode(),$e->getMessage(),
                "Error al eliminar la compra por pagar",
                $this->className
            );
            return ["success"=> false, "message" => $message];
        }finally{
            // Cierra la conexión y el statement
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn) && $nuevaConexion) { mysqli_close($conn); }
        }

    }

    function getALLCompraPorPagar($onlyActive = false, $delete = false){
        
        try{
            $result = $this->getConnection();
            if (!$result["success"]) { throw new Exception($result["message"]); }
            $conn = $result["connection"];

            $query = "SELECT * FROM " . TB_COMPRA_POR_PAGAR 
            .( $onlyActive ? " WHERE ". COMPRA_POR_PAGAR_ESTADO . " != ".($delete? 'TRUE': 'FALSE'):" ");
            $result = mysqli_query($conn,$query);


            $ComprasPorPagar =[];

            while($row = mysqli_fetch_assoc($result)){
                $compra = NULL;
                $result = $this->compradata->getCompraByID($row[COMPRA_ID]);
                if ($result["success"]){
                    $compra = $result["compra"];
                }
                $compraPorPagarObj = new CompraPorPagar(
                    $row[COMPRA_POR_PAGAR_ID],
                    $compra,
                    $row[COMPRA_POR_PAGAR_VENCIMIENTO],
                    $row[COMPRA_POR_PAGAR_CANCELADO],
                    $row[COMPRA_POR_PAGAR_NOTAS],
                    $row[COMPRA_POR_PAGAR_ESTADO]
                );
                $ComprasPorPagar[] = $compraPorPagarObj ;
            }
            return["success"=>true, "ListaComprasPorPagar"=>$ComprasPorPagar];
        }catch(Exception $e){
            $message = $this->handleMysqlError(
                $e->getCode(),$e->getMessage(),
                "Error al obtener las compras por pagar",
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

            $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_COMPRA_POR_PAGAR;
            if ($onlyActive) { $queryTotalCount .= " WHERE " . COMPRA_POR_PAGAR_ESTADO . " != " . ($deleted ? 'TRUE' : 'FALSE'); }

            // Obtener el total de registros y calcular el total de páginas
            $totalResult = mysqli_query($conn, $queryTotalCount);
            $totalRow = mysqli_fetch_assoc($totalResult);
            $totalRecords = (int) $totalRow['total'];
            $totalPages = ceil($totalRecords / $size);


            $querySelect = "SELECT * FROM " . TB_COMPRA_POR_PAGAR;

            $params=[];
            $types="";

            if($search){
                $querySelect.= " WHERE (" . COMPRA_POR_PAGAR_ESTADO_COMPRA . " LIKE ? ";
                $querySelect.=" OR " .COMPRA_POR_PAGAR_NOTAS . " LIKE ? )";
                $seParam = "%".$search."%";
                $params =[$seParam,$seParam];
                $types.="ss";
            }

            if($onlyActive){
                $querySelect .= $search ? " AND " : " WHERE ";
                $querySelect .= COMPRA_POR_PAGAR_ESTADO . " != " . ($deleted ? "TRUE" : "FALSE"); 
            }

            if($sort){
                $querySelect.=" ORDER BY comprarporpagar".$sort." ";
            }else{
                $querySelect.=" ORDER BY ".  COMPRA_POR_PAGAR_ID." ";
            }

            $querySelect.=" LIMIT ? OFFSET ? ";
            $params= array_merge($params,[$size,$offset]);
            $types.="ii";

            $stmt = mysqli_prepare($conn, $querySelect);
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
    
            // Ejecutar la consulta y obtener los resultados
            $result = mysqli_stmt_get_result($stmt);

            $listaComprasPorPagar=[];
            while($row = mysqli_fetch_assoc($result)){
                $compra = NULL;
                $result = $this->compradata->getCompraByID($row[COMPRA_ID]);
                if ($result["success"]){
                    $compra = $result["compra"];
                }
                $compraPorPagarObj = new CompraPorPagar(
                    $row[COMPRA_POR_PAGAR_ID],
                    $compra,
                    $row[COMPRA_POR_PAGAR_VENCIMIENTO],
                    $row[COMPRA_POR_PAGAR_CANCELADO],
                    $row[COMPRA_POR_PAGAR_NOTAS],
                    $row[COMPRA_POR_PAGAR_ESTADO]
                );
                $listaComprasPorPagar = $compraPorPagarObj ;
            }

            return [
                    "success" => true,
                    "page" => $page,
                    "size" => $size,
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "listaComprasPorPagar" => $listaComprasPorPagar
            ];
        }catch(Exception $e){
            $message =$this->handleMysqlError(
                $e->getCode(),$e->getMessage(),
                "Error al listar la pagina de compras por pagar",
                $this->className
            );
            return ["success"=>false,"message"=>$message];
        }finally{
            // Cerrar la conexión y el statement
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }

    function getCompraPorPagarById($id, $onlyActive = true, $delete = false){
        try{
            //Verificar si existe o no
            $result = $this->getConnection();
            if (!$result["success"]) { throw new Exception($result["message"]); }
            $conn = $result["connection"]; 


            $result = $this->compraPorPagarIdExiste($id);
            if(!$result["success"]){
                throw new Exception($result["message"]);
            }
            if(!$result["exists"]){
                $mensaje = "La compra por pagar no existe . ID [$id]";
                Utils::writeLog($mensaje,DATA_LOG_FILE,ERROR_MESSAGE,$this->className,__LINE__);
                throw new Exception("No existe la compra por por pagar");
            }

            //inicion del query
            $querySelect = "SELECT * FROM " . TB_COMPRA_POR_PAGAR . 
            " WHERE " . COMPRA_POR_PAGAR_ID . " = ? ". 
            ($onlyActive ? " AND " . COMPRA_POR_PAGAR_ESTADO . " != " ($delete ? 'TRUE': 'FALSE'): " " );
            
            $stmt = mysqli_prepare($conn,$querySelect);
            mysqli_stmt_bind_param($stmt,"i",$id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if($row = mysqli_fetch_assoc($result)){
                $compra = NULL;
                $result = $this->compradata->getCompraByID($row[COMPRA_ID]);
                if ($result["success"]){
                    $compra = $result["compra"];
                }
                $compraPorPagarObj = new CompraPorPagar(
                    $row[COMPRA_POR_PAGAR_ID],
                    $compra,
                    $row[COMPRA_POR_PAGAR_VENCIMIENTO],
                    $row[COMPRA_POR_PAGAR_CANCELADO],
                    $row[COMPRA_POR_PAGAR_NOTAS],
                    $row[COMPRA_POR_PAGAR_ESTADO]
                );
                return ["success"=>true, "CompraPorPagar"=>$compraPorPagarObj];
            }
            return ["success"=> false, "message"=>"No se encontro la compra por pagar."];
        }catch(Exception $e){
            $message = $this->handleMysqlError(
                $e->getCode(),$e->getMessage(),
                "Error al obtener la compra por pagar de la base de datos",
                $this->className
            );
            return ["success" => false, "message"=>$message];
        }finally{
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }

}


?>