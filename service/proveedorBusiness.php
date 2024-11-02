<?php

    require_once dirname(__DIR__, 1) . "/data/proveedorData.php";
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';

    class ProveedorBusiness {

        private $proveedorData;
        private $className;

        public function __construct() {
            $this->proveedorData = new ProveedorData();
            $this->className = get_class($this);
        }

        public function validarDatosPaginacion($page, $size) {
            if ($page === null || !is_numeric($page) || $page < 0) {
                Utils::writeLog("El 'page [$page]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                return ["is_valid" => false, "message" => "El número de página está vacío o no es válido. Revise que este sea un número y que sea mayor o igual a 0"];
            }

            if ($size === null || !is_numeric($size) || $size < 0) {
                Utils::writeLog("El 'size [$size]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                return ["is_valid" => false, "message" => "El tamaño de la página está vacío o no es válido. Revise que este sea un número y que sea mayor o igual a 0"];
            }

            return ["is_valid" => true];
        }

        public function validarProveedorID($proveedorID) {
            if ($proveedorID === null || !is_numeric($proveedorID) || $proveedorID < 0) {
                Utils::writeLog("El 'proveedorID [$proveedorID]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                return ["is_valid" => false, "message" => "El ID del proveedor está vacío o no es válido. Revise que este sea un número y que sea mayor a 0"];
            }

            return ["is_valid" => true];
        }

        public function validarProveedor($proveedor, $validarCamposAdicionales = true, $insert = false) {
            try {
                // Obtener los valores de las propiedades del objeto
                $proveedorID = $proveedor->getProveedorID();
                $nombre = $proveedor->getProveedorNombre();
                $email = $proveedor->getProveedorEmail();
                $categoriaID = $proveedor->getProveedorCategoria();
                $errors = [];

                // Verifica que el ID del proveedor sea válido
                if (!$insert) {
                    $checkID = $this->validarProveedorID($proveedorID);
                    if (!$checkID['is_valid']) { $errors[] = $checkID['message']; }
                }

                // Si la validación de campos adicionales está activada, valida los otros campos
                if ($validarCamposAdicionales) {
                    if ($nombre === null || empty($nombre) || is_numeric($nombre)) {
                        $errors[] = "El campo 'Nombre' está vacío o no es válido.";
                        Utils::writeLog("El campo 'Nombre [$nombre]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    }
                    if ($email === null || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = "El campo 'Correo' no es válido. Debe digitar un correo electrónico válido (Ej: ejemplo@correo.com).";
                        Utils::writeLog("El campo 'Email [$email]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    }
                    if ($categoriaID=== null || empty($categoriaID)) {
                        $errors[] = "El campo 'Categoria' no es válido.";
                        Utils::writeLog("El campo 'Categoriaid [$categoriaID]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    }
                }

                // Lanza una excepción si hay errores
                if (!empty($errors)) {
                    throw new Exception(implode('<br>', $errors));
                }

                return ["is_valid" => true];
            } catch (Exception $e) {
                return ["is_valid" => false, "message" => $e->getMessage()];
            }
        }

        public function insertTBProveedor($proveedor) {
            // Verifica que los datos del proveedor sean validos
            $check = $this->validarProveedor($proveedor, true, true);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }
            return $this->proveedorData->insertProveedor($proveedor);
        }

        public function updateTBProveedor($proveedor) {
            // Verifica que los datos del proveedor sean validos
            $check = $this->validarProveedor($proveedor);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->proveedorData->updateProveedor($proveedor);
        }

        public function deleteTBProveedor($proveedorID) {
            // Verifica que los datos del proveedor sean validos
            $check = $this->validarProveedorID($proveedorID);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->proveedorData->deleteProveedor($proveedorID);
        }

        public function getAllTBProveedor($onlyActive = true, $deleted = false) {
            return $this->proveedorData->getAllTBProveedor($onlyActive, $deleted);
        }

        public function getPaginatedProveedores($search, $page, $size, $sort, $onlyActive = true, $deleted = false) {
            // Verifica que los datos de paginación sean válidos
            $check = $this->validarDatosPaginacion($page, $size);
            if (!$check['is_valid']) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->proveedorData->getPaginatedProveedores($search, $page, $size, $sort, $onlyActive, $deleted);
        }

        public function getProveedorByID($proveedorID, $onlyActive = false, $deleted = false) {
            // Verifica que los datos del proveedor sean validos
            $check = $this->validarProveedorID($proveedorID);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->proveedorData->getProveedorByID($proveedorID, $onlyActive, $deleted);
        }

        public function getCompraProveedorByID($proveedorID, $onlyActive = false, $deleted = false) {
            // Verifica que los datos del proveedor sean validos
            $check = $this->validarProveedorID($proveedorID);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->proveedorData->getCompraProveedorByID($proveedorID, $onlyActive, $deleted);
        }

    }

?>