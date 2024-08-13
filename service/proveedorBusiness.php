<?php

    include __DIR__ . "/../data/proveedorData.php";

    class ProveedorBusiness {

        private $proveedorData;

        public function __construct() {
            $this->proveedorData = new ProveedorData();
        }

        public function insertTBProveedor($proveedor) {
            return $this->proveedorData->insertProveedor($proveedor);
        }

        public function getAllTBProveedor() {
            return $this->proveedorData->getAllTBProveedor();
        }

        public function getPaginatedProveedores($page, $size, $sort = null) {
            return $this->proveedorData->getPaginatedProveedores($page, $size, $sort);
        }

        public function updateTBProveedor($proveedor) {
            return $this->proveedorData->updateProveedor($proveedor);
        }

        public function deleteTBProveedor($proveedorID) {
            return $this->proveedorData->deleteProveedor($proveedorID);
        }

    }

?>