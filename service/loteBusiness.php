<?php

include __DIR__ . "/../data/loteData.php";

class LoteBusiness {

    private $loteData;

    public function __construct() {
        $this->loteData = new LoteData();
    }

    public function insertLote($lote) {
        return $this->loteData->insertLote($lote);
    }

    public function getAllLote() {
        return $this->loteData->getAllLotes();
    }

    public function getPaginatedLotes($page, $size, $sort = null) {
        return $this->loteData->getPaginatedLotes($page, $size, $sort);
    }

    public function updateLote($lote) {
        return $this->loteData->updateLote($lote);
    }

    public function deleteLote($loteID) {
        return $this->loteData->deleteLote($loteID);
    }
}

?>
