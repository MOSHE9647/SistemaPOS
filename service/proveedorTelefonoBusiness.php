<?php

	require_once __DIR__ . "/../data/proveedorTelefonoData.php";
    require_once __DIR__ . '/../service/proveedorBusiness.php';

    class ProveedorTelefonoBusiness {

        private $proveedorTelefonoData;
        private $className;

        public function __construct() {
            $this->proveedorTelefonoData = new ProveedorTelefonoData();
            $this->className = get_class($this);
        }

        public function validarProveedorTelefono($proveedorID = null, $telefonoID = null, $both = true) {
            try {
                $errors = [];
        
                // Validar proveedorID si es necesario
                if ($both || $proveedorID !== null) {
                    if (!is_numeric($proveedorID) || $proveedorID <= 0) {
                        $errors[] = "El ID del proveedor está vacío o no es válido. Debe ser un número mayor a 0.";
                        Utils::writeLog("El ID [$proveedorID] del proveedor no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                    }
                }
        
                // Validar telefonoID si es necesario
                if ($both || $telefonoID !== null) {
                    if (!is_numeric($telefonoID) || $telefonoID <= 0) {
                        $errors[] = "El ID del teléfono está vacío o no es válido. Debe ser un número mayor a 0.";
                        Utils::writeLog("El ID [$telefonoID] del teléfono no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                    }
                }
        
                // Si no se proporcionó ningún ID
                if (empty($errors) && !$proveedorID && !$telefonoID) {
                    // Registrar parámetros faltantes y lanzar excepción
                    $missingParamsLog = "Faltan parámetros para validar:";
                    $missingParamsLog .= " proveedorID [" . ($proveedorID ?? 'null') . "]";
                    $missingParamsLog .= " telefonoID [" . ($telefonoID ?? 'null') . "]";
                    Utils::writeLog(trim($missingParamsLog), BUSINESS_LOG_FILE, WARN_MESSAGE, $this->className);
                    throw new Exception("No se proporcionaron los parámetros necesarios para realizar la validación.");
                }
        
                // Lanza una excepción si hay errores
                if (!empty($errors)) {
                    throw new Exception(implode('<br>', $errors));
                }
        
                // Si no hay errores, devuelve un arreglo con el resultado de la validación
                return ["is_valid" => true];
        
            } catch (Exception $e) {
                return ["is_valid" => false, "message" => $e->getMessage()];
            }
        }        

        public function addTelefonoToProveedor($proveedorID, $telefonoID) {
            // Verifica que los ID del telefono y del proveedor sean validos
            $check = $this->validarProveedorTelefono($proveedorID, $telefonoID);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->proveedorTelefonoData->addTelefonoToProveedor($proveedorID, $telefonoID);
        }

        public function updateTelefonosProveedor($proveedor) {
            // Valida los datos del Proveedor
            $proveedorBusiness = new ProveedorBusiness();
            $check = $proveedorBusiness->validarProveedor($proveedor);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->proveedorTelefonoData->updateTelefonosProveedor($proveedor);
        }

        public function removeTelefonoFromProveedor($proveedorID, $telefonoID) {
            // Verifica que los ID del telefono y del proveedor sean validos
            $check = $this->validarProveedorTelefono($proveedorID, $telefonoID);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->proveedorTelefonoData->removeTelefonoFromProveedor($proveedorID, $telefonoID);
        }

        public function getTelefonosByProveedor($proveedorID, $json = false) {
            // Verifica que el ID del proveedor sea válido
            $check = $this->validarProveedorTelefono($proveedorID, null, false);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->proveedorTelefonoData->getTelefonosByProveedor($proveedorID, $json);
        }

        public function getPaginatedTelefonosByProveedor($proveedorID, $page, $size, $sort = null, $onlyActiveOrInactive = true, $deleted = false) {
            // Verifica que el ID del proveedor sea válido
            $check = $this->validarProveedorTelefono($proveedorID, null, false);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->proveedorTelefonoData->getPaginatedTelefonosByProveedor($proveedorID, $page, $size, $sort, $onlyActiveOrInactive, $deleted);
        }

    }

?>