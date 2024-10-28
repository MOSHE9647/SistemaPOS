<?php

    require_once dirname(__DIR__, 1) . "/data/clienteData.php";
    require_once dirname(__DIR__, 1) . "/utils/Utils.php";
    require_once "telefonoBusiness.php";

    class ClienteBusiness {

        private $clienteData;
        private $className;

        public function __construct() {
            $this->clienteData = new ClienteData();
            $this->className = get_class($this);
        }

        public function validarClienteID($clienteID) {
            if ($clienteID === null || !is_numeric($clienteID) || $clienteID < 0) {
                Utils::writeLog("El 'ID [$clienteID]' del cliente no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ["is_valid" => false, "message" => "El ID del cliente está vacío o no es válido. Revise que este sea un número y que sea mayor a 0"];
            }

            return ["is_valid" => true];
        }

        public function validarClienteTelefonoID($clienteTelefonoID) {
            if ($clienteTelefonoID === null || !is_numeric($clienteTelefonoID) || $clienteTelefonoID < 0) {
                Utils::writeLog("El 'ID [$clienteTelefonoID]' del teléfono del cliente no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ["is_valid" => false, "message" => "El ID del teléfono del cliente está vacío o no es válido. Revise que este sea un número y que sea mayor a 0"];
            }

            return ["is_valid" => true];
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

        public function validarCliente($cliente, $validarCamposAdicionales = true, $insert = false) {
            try {
                // Obtener los valores de las propiedades del objeto
                $clienteID = $cliente->getClienteID();
                $telefono = $cliente->getClienteTelefono();
                $errors = [];

                // Verifica que el ID del cliente sea válido
                if (!$insert) {
                    $checkID = $this->validarClienteID($clienteID);
                    if (!$checkID['is_valid']) { $errors[] = $checkID['message']; }
                }

                // Si la validación de campos adicionales está activada, valida los otros campos
                if ($validarCamposAdicionales) {
                    // Crea el service de telefono y verifica que los datos sean correctos
                    $telefonoBusiness = new TelefonoBusiness();
                    $checkTelefono = $telefonoBusiness->validarTelefono($telefono, true, $insert);
                    if (!$checkTelefono['is_valid']) { $errors[] = $checkTelefono['message']; }
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

        public function insertTBCliente($cliente) {
            // Verifica que los datos del cliente sean validos
            $check = $this->validarCliente($cliente, true, true);
            if (!$check['is_valid']) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->clienteData->insertCliente($cliente);
        }

        public function updateTBCliente($cliente) {
            // Verifica que los datos del cliente sean validos
            $check = $this->validarCliente($cliente);
            if (!$check['is_valid']) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->clienteData->updateCliente($cliente);
        }

        public function deleteTBCliente($clienteID) {
            // Verifica que el ID del cliente sea válido
            $check = $this->validarClienteID($clienteID);
            if (!$check['is_valid']) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->clienteData->deleteCliente($clienteID);
        }

        public function getAllTBCliente($onlyActive = true, $deleted = false) {
            return $this->clienteData->getAllTBCliente($onlyActive, $deleted);
        }

        public function getPaginatedClientes($search, $page, $size, $sort = null, $onlyActive = true, $deleted = false) {
            // Verifica que los datos de paginación sean válidos
            $check = $this->validarDatosPaginacion($page, $size);
            if (!$check['is_valid']) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }
            
            return $this->clienteData->getPaginatedClientes($search, $page, $size, $sort, $onlyActive, $deleted);
        }

        public function getClienteByID($clienteID, $onlyActive = true, $deleted = false) {
            // Verifica que el ID del cliente sea válido
            $check = $this->validarClienteID($clienteID);
            if (!$check['is_valid']) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->clienteData->getClienteByID($clienteID, $onlyActive, $deleted);
        }

        public function getClienteByTelefonoID($telefonoID) {
            // Verifica que el ID del teléfono del cliente sea válido
            $check = $this->validarClienteTelefonoID($telefonoID);
            if (!$check['is_valid']) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->clienteData->getClienteByTelefonoID($telefonoID);
        }

        public function getVentaClienteByID($clienteID, $onlyActive = false, $deleted = false) {
            // Verifica que los datos del proveedor sean validos
            $check = $this->validarClienteID($clienteID);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->clienteData->getVentaClienteByID($clienteID, $onlyActive, $deleted);
        }

    }

?>