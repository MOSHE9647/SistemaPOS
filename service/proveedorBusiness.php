<?php

    include __DIR__ . "/../data/proveedorData.php";

    class ProveedorBusiness {

        private $proveedorData;

        public function __construct() {
            $this->proveedorData = new ProveedorData();
        }

        public function validarProveedor($proveedor, $validarCamposAdicionales = true) {
            try {
                // Obtener los valores de las propiedades del objeto
                $proveedorID = $proveedor->getProveedorID();
                $nombre = $proveedor->getProveedorNombre();
                $email = $proveedor->getProveedorEmail();
                $fechaRegistro = $proveedor->getProveedorFechaRegistro();
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
                    if (empty($fechaRegistro) || !Utils::validarFecha($fechaRegistro)) {
                        $errors[] = "El campo 'Fecha Registro' está vacío o no es válido.";
                        Utils::writeLog("[Proveedor] El campo 'Fecha Registro [$fechaRegistro]' está vacío o no es válido.", BUSINESS_LOG_FILE);
                    }
                    if (!Utils::fechaMenorOIgualAHoy($fechaRegistro)) {
                        $errors[] = "El campo 'Fecha Registro' no puede ser una fecha mayor a la de hoy. Revise que la fecha sea menor o igual a la de hoy.";
                        Utils::writeLog("[Proveedor] El campo 'Fecha Registro [$fechaRegistro]' es mayor a la de hoy.", BUSINESS_LOG_FILE);
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

        public function getPaginatedProveedores($page, $size, $sort = null) {
            return $this->proveedorData->getPaginatedProveedores($page, $size, $sort);
        }

    }

?>