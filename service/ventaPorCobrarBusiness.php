<?php
require_once dirname(__DIR__, 1) . '/data/ventaPorCobrarData.php';
require_once dirname(__DIR__, 1) . '/utils/Utils.php';
require_once dirname(__DIR__, 1) . '/domain/VentaPorCobrar.php';

class VentaPorCobrarBusiness{
    private $ventaCobrarData;

    public function __construct(){
        $this->ventaCobrarData = new VentaPorCobrarData();
    }
    function validarCamposFecha($ventaCobrar,$update = false,$validarCamposAdicionales=false){
        try {
            $id = $ventaCobrar->getVentaPorCobrarID();
            $venta = $ventaCobrar->getVentaPorCobrarVenta()->getVentaID();
            $fechaVence = $ventaCobrar->getVentaPorCobrarFechaVencimiento();
            $estadoCompra =$ventaCobrar->getVentaPorCobrarCancelado();
            $notas = $ventaCobrar->getVentaPorCobrarNotas();
            $estado = $ventaCobrar->getVentaPorCobrarEstado();
            $errors = [];
         
            if ($validarCamposAdicionales) {
                if(!Utils::fechaMayorOIgualAHoy($fechaVence) && $update){
                    $errors[] = "La fecha de vencimiento debe ser mayor o igual a la actual";
                }
                if($estadoCompra === null){
                    $errors[] = "El estado de la venta no puede estar vacia";
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
    function insertVentaCobrar($ventaCobrar){
        return $this->ventaCobrarData->InsertaVentaPorCobrar($ventaCobrar);
    }
    function updateVentaCobrar($ventaCobrar){
        return  $this->ventaCobrarData->ActualizarCompraPorPagar($ventaCobrar);
    }
    function deleteVentaCobrar($id){
        return $this->ventaCobrarData->deleteVentaPorCobrar($id);
    }
    function getAllVentaCobrar($onlyActive = false, $delete = false){
        return $this->ventaCobrarData->getALLVentePorCobrar($onlyActive, $delete);
    }
    function paginaVentaCobrar($search,$page,$size,$sort=null,$onlyActive= false,$deleted=false){
        return $this->ventaCobrarData->getPaginaCompraPorPagar($search,$page,$size,$sort,$onlyActive,$deleted);
    }
    function getVentaCobrarID($id,$onlyActive = true, $delete = false){
        return $this->ventaCobrarData->getVentaPorCobrarById($id, $onlyActive, $delete);
    }
    function ventaPorCobrarClienteExiste($idCliente,$onlyActive = false, $delete = false){
        return $this->ventaCobrarData->ventaPorCobrarClienteExiste($idCliente,$onlyActive, $delete);
    }
}


?>