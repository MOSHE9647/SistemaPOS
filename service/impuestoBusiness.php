<?php

    include __DIR__ . "/../data/impuestoData.php";

    class ImpuestoBusiness {

        private $impuestoData;

        public function __construct() {
            $this->impuestoData = new ImpuestoData();
        }

        public function insertTBImpuesto($impuesto) {
            return $this->impuestoData->insertImpuesto($impuesto);
        }

        public function getAllTBImpuesto() {
            return $this->impuestoData->getAllTBImpuesto();
        }

        public function updateTBImpuesto($impuesto) {
            return $this->impuestoData->updateImpuesto($impuesto);
        }

        public function deleteTBImpuesto($impuestoID) {
            return $this->impuestoData->deleteImpuesto($impuestoID);
        }

    }

?>