<?php
require_once __DIR__ . "/../data/compraPorPagarData.php";
require_once dirname(__DIR__, 1) . '/utils/Utils.php';
require_once __DIR__ . "/../domain/CompraPorPagar.php";

class CompraPorPagarBussines{
    private $compraPagarData;

    public function __construct(){
        $this->compraPagarData = new CompraPorPagarData();
    }
    function validarCamposFecha($compraPagar,$update = false,$validarCamposAdicionales=false){
        try {
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
            $errors = [];
         
            if ($validarCamposAdicionales) {
                if(($fechaVence === null || empty($fechaVence)) && !$update){
                    $errors[] = "La fecha de vencimiento no puede estar vacia";
                }
                if($fechaPago === null || empty($fechaPago)){
                    $errors[] = "La fecha de pago no puede estar vacia";
                }
                if(!Utils::fechaMayorOIgualAHoy($fechaVence) && $update){
                    $errors[] = "La fecha de vencimiento debe ser mayor o igual a la actual";
                }
                if(empty($estadoCompra)){
                    $errors[] = "El estado de la compra no puede estar vacia";
                }
            }
            if (!empty($errors)) {
                throw new Exception(implode('<br>', $errors));
            }

            return ["is_valid" => true];
        } catch (Exception $e) {
            return ["is_valid" => false, "message" => $e->getMessage()];
        }
    }
    function insertCompraPorPagar($compraPagar){
        return $this->compraPagarData->InsertarCompraPorPagar($compraPagar);
    }
    function updateCompraPorPagar($compraPagar){
        return  $this->compraPagarData->ActualizarCompraPorPagar($compraPagar);
    }
    function deleteCompraPorPagar($id){
        return $this->compraPagarData->deleteCopraPorPagar($id);
    }
    function getAllCompraPorPagar($onlyActive = false, $delete = false){
        return $this->compraPagarData->getALLCompraPorPagar($onlyActive, $delete);
    }
    function paginaCompraPorPagar($search,$page,$size,$sort=null,$onlyActive= false,$deleted=false){
        return $this->compraPagarData-> getPaginaCompraPorPagar($search,$page,$size,$sort,$onlyActive,$deleted);
    }
    function getCompraPorPagarID($id,$onlyActive = true, $delete = false){
        return $this->compraPagarData-> getCompraPorPagarById($id, $onlyActive, $delete);
    }
}


?>