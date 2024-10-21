<?php
require_once dirname(__DIR__, 1) . '/domain/CompraPorPagar.php';
require_once dirname(__DIR__, 1) . '/domain/CompraDetalle.php';
require_once dirname(__DIR__, 1) . '/data/compradetalleData';
require_once dirname(__DIR__, 1) . '/utils/Utils.php';
require_once dirname(__DIR__, 1) . '/utils/Variables.php';
require_once dirname(__DIR__, 1) . '/data/data.php';

class CompraPorPagarData extends Data{
    private $className;
    public function __construct(){
        parent::__construct();
        $this->className = get_class($this);
    }

    //Verificar si la compraPoragarExiste
    function compraPorPagarIdExiste($id){
        try{

            $result = $this->getConnection();
            if(!$result['success']){
                throw new Exception($result['message']);
            }
            $conn = $result['connection'];

            if($id <= 0 || is_numeric($id)){
                throw new Exception("El id de la compra por pagar es invalido");
            }

            $query = "SELECT " . COMPRA_POR_PAGAR_ID . " FROM ". TB_COMPRA_POR_PAGAR . " WHERE " . COMPRA_POR_PAGAR_ID . " = ? " ;
            $types = "i";
            $param = [$id];

          
            $stmt = mysqli_prepare($conn,$query);
            mysqli_stmt_bind_param($stmt,"i",$param);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);

            if($row = mysqli_fetch_all($resultado)){
                return ["success"=>true,"exists"=>True];
            }
            return ["success"=>true,"exists"=>false];
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
            //id
            $detalleId = $compraPagar->getCompraPorPagarCompraDetalle()->getCompraDetalleID();
            //objeto
            $detalleCompra = $compraPagar->getCompraPorPagarCompraDetalle();
         
            $fechaVence = $compraPagar->getCompraPorPagarFechaVencimiento();
            $montoTotal = $compraPagar->getCompraPorPagarMontoTotal();
            $montoPagado = $compraPagar->getCompraPorPagarMontoPagado();
            $fechaPago = $compraPagar->getCompraPorPagarFechaPago();
            $estadoCompra =$compraPagar->getCompraPorPagarEstadoCompra();
            $notas = $compraPagar->getCompraPorPagarNotas();
            $estado = $compraPagar->getCompraPorPagarEstado();

            $result = $this->compraPorPagarIdExiste($id);
            if(!$result["success"]){
                throw new Exception($result["message"]);
            }
            if(!$result["exists"]){
                throw new Exception("No existe la compra por por pagar");
            }

            if(Utils::fechaMayorOIgualAHoy($fechaVence)){
                throw new  Exception("La fecha de vencimiento no es valida");
            }
            //Guardamos el objeto detalle
            $ref_detalleID_final = 0;

            if($detalleId <= 0){
                $detalleData = new CompraDetalleData();
                $result = $detalleData->insertCompraDetalle($detalleCompra);
                if($result["success"]){
                    $ref_detalleID_final = $result["Id"];
                }else{
                    throw new Exception($result["message"]);
                }
            }else{
                $ref_detalleID_final = $detalleId;
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
            . COMPRA_POR_PAGAR_COMPRA_DETALLE_ID . ","
            . COMPRA_POR_PAGAR_FECHA_VENCIMIENTO . ","
            . COMPRA_POR_PAGAR_MONTO_TOTAL . ","
            . COMPRA_POR_PAGAR_MONTO_PAGADO . ","
            . COMPRA_POR_PAGAR_FECHA_PAGO . ","
            . COMPRA_POR_PAGAR_ESTADO_COMPRA . ","
            . COMPRA_POR_PAGAR_NOTAS
            ." ) VALUES (?,?,?,?,?,?,?,?,?) "; 

            $stmt = mysqli_prepare($conn,$queryInsert);

            mysqli_stmt_bind_param(
                $stmt,
                "iisffsss",
                $id,
                $$ref_detalleID_final,
                $fechaVence,
                $montoTotal,
                $montoPagado,
                $fechaPago,
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
                'Ocurrio un error al insertar la compra a la base de datos'
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
            //id
            $detalleId = $compraPagar->getCompraPorPagarCompraDetalle()->getCompraDetalleID();
            //objeto
            //$detalleCompra = $compraPagar->getCompraPorPagarCompraDetalle();
         
            $fechaVence = $compraPagar->getCompraPorPagarFechaVencimiento();
            $montoTotal = $compraPagar->getCompraPorPagarMontoTotal();
            $montoPagado = $compraPagar->getCompraPorPagarMontoPagado();
            $fechaPago = $compraPagar->getCompraPorPagarFechaPago();
            $estadoCompra =$compraPagar->getCompraPorPagarEstadoCompra();
            $notas = $compraPagar->getCompraPorPagarNotas();
            $estado = $compraPagar->getCompraPorPagarEstado();
            
            //verificamos la existencia del id
            $result = $this->compraPorPagarIdExiste($id);
            if(!$result["success"]){
                throw new Exception($result["message"]);
            }
            if(!$result["exists"]){
                throw new Exception("No existe la compra por por pagar");
            }

            if(Utils::fechaMayorOIgualAHoy($fechaVence)){
                throw new  Exception("La fecha de vencimiento no es valida");
            }

            $queryUdate = 
            "UPDATE " . TB_COMPRA_POR_PAGAR .
            " SET " .
                COMPRA_POR_PAGAR_COMPRA_DETALLE_ID . " = ?, ".
                COMPRA_POR_PAGAR_FECHA_VENCIMIENTO . " = ?, ". 
                COMPRA_POR_PAGAR_MONTO_TOTAL . " = ?, ".
                COMPRA_POR_PAGAR_MONTO_PAGADO . " = ?, " . 
                COMPRA_POR_PAGAR_FECHA_PAGO . " = ?, " .
                COMPRA_POR_PAGAR_ESTADO_COMPRA . " = ?,".
                COMPRA_POR_PAGAR_NOTAS . " = ?,".
                COMPRA_POR_PAGAR_ESTADO . " = TRUE " .
            " WHERE " . COMPRA_POR_PAGAR_ID . " = ? ";
            
            $stmt = mysqli_prepare($conn,$queryUdate);

            mysqli_stmt_bind_param(
                $stmt,
                "isddsssi",
                $detalleId,
                $fechaVence,
                $montoTotal,
                $montoPagado,
                $fechaPago,
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
                "Error al actualizar la compra por pagar"
            );
            return ["succes" => true, "message"=>$message];
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
                "Error al eliminar la compra por pagar"
            );
            return ["success"=> false, "message" => $message];
        }finally{
            // Cierra la conexión y el statement
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn) && $nuevaConexion) { mysqli_close($conn); }
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
                $compraPorPagar = new CompraPorPagar(
                        $row[COMPRA_POR_PAGAR_ID],
                        new CompraDetalle ($row[COMPRA_POR_PAGAR_COMPRA_DETALLE_ID]),
                        $row[COMPRA_POR_PAGAR_FECHA_VENCIMIENTO],
                        $row[COMPRA_POR_PAGAR_MONTO_TOTAL],
                        $row[COMPRA_POR_PAGAR_MONTO_PAGADO],
                        $row[COMPRA_POR_PAGAR_FECHA_PAGO],
                        $row[COMPRA_POR_PAGAR_ESTADO_COMPRA],
                        $row[COMPRA_POR_PAGAR_NOTAS],
                        $row[COMPRA_POR_PAGAR_ESTADO]
                );
                return ["success"=>true, "CompraPorPagar"=>$compraPorPagar];
            }
            return ["success"=> false, "message"=>"No se encontro la compra por pagar."];
        }catch(Exception $e){
            $message = $this->handleMysqlError(
                $e->getCode(),$e->getMessage(),
                "Error al obtener la compra por pagar de la base de datos"
            );
            return ["success" => true, "message"=>$message];
        }finally{
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }

}


?>