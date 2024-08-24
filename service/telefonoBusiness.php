<?php

    include __DIR__ . '/../data/telefonoData.php';
    include __DIR__ . '/../service/proveedorBusiness.php';

    class TelefonoBusiness {

        private $telefonoData;

        public function __construct() {
            $this->telefonoData = new TelefonoData();
        }

        public function validarTelefono($telefono, $validarCamposAdicionales = true, $insertar = true) {
            try {
                // Obtener los valores de las propiedades del objeto
                $telefonoID = $telefono->getTelefonoID();
                $proveedorID = $telefono->getTelefonoProveedorID();
                $tipo = $telefono->getTelefonoTipo();
                $codigoPais = $telefono->getTelefonoCodigoPais();
                $numero = $telefono->getTelefonoNumero();
                $errors = [];

                // Verifica que el ID del Telefono sea válido
                if (!$insertar && ($telefonoID === null || !is_numeric($telefonoID) || $telefonoID < 0)) {
                    $errors[] = "El ID del teléfono está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                    Utils::writeLog("El ID [$telefonoID] del teléfono no es válido.", BUSINESS_LOG_FILE);
                }

                // Si la validación de campos adicionales está activada, valida los otros campos
                if ($validarCamposAdicionales) {
                    if ($proveedorID === null || !is_numeric($proveedorID) || $proveedorID < 0) {
                        $errors[] = "El ID del proveedor está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                        Utils::writeLog("[Telefono] El ID [$proveedorID] del proveedor no es válido.", BUSINESS_LOG_FILE);
                    }
                    $proveedorBusiness = new ProveedorBusiness();
                    $check = $proveedorBusiness->existeProveedor($proveedorID);
                    if (!$check["success"]) { 
                        $errors[] = $check['message'];
                        Utils::writeLog("[Telefono] No se pudo verificar la existencia del Proveedor.", BUSINESS_LOG_FILE);
                    }
                    if (!$check['exists']) {
                        $errors[] = "El proveedor seleccionado no existe en la base de datos.";
                        Utils::writeLog("[Telefono] El proveedor con ID [$proveedorID] no existe en la base de datos.", BUSINESS_LOG_FILE);
                    }
                    if ($tipo === null || empty($tipo) || is_numeric($tipo)) {
                        $errors[] = "El campo 'Tipo' está vacío o no es válido.";
                        Utils::writeLog("[Telefono] El campo 'Tipo [$tipo]' no es válido.", BUSINESS_LOG_FILE);
                    }
                    if ($codigoPais === null || empty($codigoPais) || !is_numeric($codigoPais)) {
                        $errors[] = "El campo 'Código de País' está vacío o no es válido.";
                        Utils::writeLog("[Telefono] El campo 'Código de País [$codigoPais]' no es válido.", BUSINESS_LOG_FILE);
                    }
                    if ($numero === null || empty($numero) || is_numeric($numero)) {
                        $errors[] = "El campo 'Número de Teléfono' está vacío o no es válido.";
                        Utils::writeLog("[Telefono] El campo 'Número de Teléfono [$numero]' no es válido.", BUSINESS_LOG_FILE);
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

        public function insertTBTelefono($telefono) {
            // Verifica que los datos del telefono sean validos
            $check = $this->validarTelefono($telefono);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->telefonoData->insertTelefono($telefono);
        }

        public function updateTBTelefono($telefono) {
            // Verifica que los datos del telefono sean validos
            $check = $this->validarTelefono($telefono);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->telefonoData->updateTelefono($telefono);
        }

        public function deleteTBTelefono($telefono) {
            // Verifica que los datos del telefono sean validos
            $check = $this->validarTelefono($telefono, false);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            $telefonoID = $telefono->getTelefonoID();
            unset($telefono);
            return $this->telefonoData->deleteTelefono($telefonoID);
        }

        public function getPaginatedTelefonos($page, $size, $sort = null) {
            return $this->telefonoData->getPaginatedTelefonos($page, $size, $sort);
        }

    }

?>