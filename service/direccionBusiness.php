<?php

    include __DIR__ . "/../data/direccionData.php";

    class DireccionBusiness {

        private $direccionData;

        public function __construct() {
            $this->direccionData = new DireccionData();
        }

        public function insertTBDireccion($direccion) {
            return $this->direccionData->insertDireccion($direccion);
        }

        public function updateTBDireccion($direccion) {
            return $this->direccionData->updateDireccion($direccion);
        }

        public function getAllTBDireccion() {
            return $this->direccionData->getAllTBDireccion();
        }

        public function deleteTBDireccion($direccionID) {
            return $this->direccionData->deleteDireccion($direccionID);
        }

    }

?>