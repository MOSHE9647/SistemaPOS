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
                Utils::writeLog("El 'page [$page]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ["is_valid" => false, "message" => "El número de página está vacío o no es válido. Revise que este sea un número y que sea mayor o igual a 0"];
            }

            if ($size === null || !is_numeric($size) || $size < 0) {
                Utils::writeLog("El 'size [$size]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ["is_valid" => false, "message" => "El tamaño de la página está vacío o no es válido. Revise que este sea un número y que sea mayor o igual a 0"];
            }

            return ["is_valid" => true];
        }

        public function validarProveedor($proveedor, $validarCamposAdicionales = true) {
            try {
                // Obtener los valores de las propiedades del objeto
                $proveedorID = $proveedor->getProveedorID();
                $nombre = $proveedor->getProveedorNombre();
                $email = $proveedor->getProveedorEmail();
                $categoriaID = $proveedor->getProveedorCategoria();
                $errors = [];

                // Verifica que el ID del proveedor sea válido
                if ($proveedorID === null || !is_numeric($proveedorID) || $proveedorID < 0) {
                    $errors[] = "El ID del proveedor está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                    Utils::writeLog("El ID '[$proveedorID]' del proveedor no es válido.", BUSINESS_LOG_FILE);
                }

                // Si la validación de campos adicionales está activada, valida los otros campos
                if ($validarCamposAdicionales) {
                    if ($nombre === null || empty($nombre) || is_numeric($nombre)) {
                        $errors[] = "El campo 'Nombre' está vacío o no es válido.";
                        Utils::writeLog("[Proveedor] El campo 'Nombre [$nombre]' no es válido.", BUSINESS_LOG_FILE);
                    }
                    if ($email === null || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = "El campo 'Correo' no es válido. Debe digitar un correo electrónico válido (Ej: ejemplo@correo.com).";
                        Utils::writeLog("[Proveedor] El campo 'Email [$email]' no es válido.", BUSINESS_LOG_FILE);
                    }
                    if ($categoriaID=== null || empty($categoriaID)) {
                        $errors[] = "El campo 'Categoria' no es válido.";
                        Utils::writeLog("[Proveedor] El campo 'Categoriaid [$categoriaID]' no es válido.", BUSINESS_LOG_FILE);
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

        public function existeProveedor($proveedorID) {
            return $this->proveedorData->proveedorExiste($proveedorID);
        }

        public function insertTBProveedor($proveedor) {
            // Verifica que los datos del proveedor sean validos
            $check = $this->validarProveedor($proveedor);
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

        public function deleteTBProveedor($proveedor) {
            // Verifica que los datos del proveedor sean validos
            $check = $this->validarProveedor($proveedor, false);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            $proveedorID = $proveedor->getProveedorID(); //<- Obtenemos el ID verificado del Proveedor
            unset($proveedor); //<- Eliminamos el objeto para no ocupar espacio en memoria (en caso de ser necesario)
            return $this->proveedorData->deleteProveedor($proveedorID);
        }

        public function getAllTBProveedor() {
            return $this->proveedorData->getAllTBProveedor();
        }

        public function getAllTBCompraProveedor() {
            return $this->proveedorData->getAllTBCompraProveedor();
        }

        // NO TOCAR, YA ESTÁ IMPLEMENTADO
        public function getPaginatedProveedores($search, $page, $size, $sort, $onlyActive = true, $deleted = false) {
            // Verifica que los datos de paginación sean válidos
            $check = $this->validarDatosPaginacion($page, $size);
            if (!$check['is_valid']) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->proveedorData->getPaginatedProveedores($search, $page, $size, $sort, $onlyActive, $deleted);
        }

    }

?>