<?php

	require_once __DIR__ . "/../data/usuarioTelefonoData.php";
    require_once __DIR__ . '/../service/usuarioBusiness.php';

    class UsuarioTelefonoBusiness {

        private $usuarioTelefonoData;
        private $className;

        public function __construct() {
            $this->usuarioTelefonoData = new UsuarioTelefonoData();
            $this->className = get_class($this);
        }

        public function validarUsuarioTelefono($usuarioID = null, $telefonoID = null, $both = true) {
            try {
                $errors = [];
        
                // Validar usuarioID si es necesario
                if ($both || $usuarioID !== null) {
                    if (!is_numeric($usuarioID) || $usuarioID <= 0) {
                        $errors[] = "El ID del usuario está vacío o no es válido. Debe ser un número mayor a 0.";
                        Utils::writeLog("El ID [$usuarioID] del usuario no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    }
                }
        
                // Validar telefonoID si es necesario
                if ($both || $telefonoID !== null) {
                    if (!is_numeric($telefonoID) || $telefonoID <= 0) {
                        $errors[] = "El ID del teléfono está vacío o no es válido. Debe ser un número mayor a 0.";
                        Utils::writeLog("El ID [$telefonoID] del teléfono no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    }
                }
        
                // Si no se proporcionó ningún ID
                if (empty($errors) && !$usuarioID && !$telefonoID) {
                    // Registrar parámetros faltantes y lanzar excepción
                    $missingParamsLog = "Faltan parámetros para validar:";
                    $missingParamsLog .= " usuarioID [" . ($usuarioID ?? 'null') . "]";
                    $missingParamsLog .= " telefonoID [" . ($telefonoID ?? 'null') . "]";
                    Utils::writeLog(trim($missingParamsLog), BUSINESS_LOG_FILE, WARN_MESSAGE, $this->className, __LINE__);
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

        public function addTelefonoToUsuario($usuarioID, $telefonoID) {
            // Verifica que los ID del telefono y del usuario sean validos
            $check = $this->validarUsuarioTelefono($usuarioID, $telefonoID);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->usuarioTelefonoData->addTelefonoToUsuario($usuarioID, $telefonoID);
        }

        public function updateTelefonosUsuario($usuario) {
            // Valida los datos del Usuario
            $usuarioBusiness = new UsuarioBusiness();
            $check = $usuarioBusiness->validarUsuario($usuario);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->usuarioTelefonoData->updateTelefonosUsuario($usuario);
        }

        public function removeTelefonoFromUsuario($usuarioID, $telefonoID) {
            // Verifica que los ID del telefono y del usuario sean validos
            $check = $this->validarUsuarioTelefono($usuarioID, $telefonoID);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->usuarioTelefonoData->removeTelefonoFromUsuario($usuarioID, $telefonoID);
        }

        public function getTelefonosByUsuario($usuarioID, $json = false) {
            // Verifica que el ID del usuario sea válido
            $check = $this->validarUsuarioTelefono($usuarioID, null, false);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->usuarioTelefonoData->getTelefonosByUsuario($usuarioID, $json);
        }

        public function getPaginatedTelefonosByUsuario($usuarioID, $page, $size, $sort = null, $onlyActive = true, $deleted = false) {
            // Verifica que el ID del usuario sea válido
            $check = $this->validarUsuarioTelefono($usuarioID, null, false);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->usuarioTelefonoData->getPaginatedTelefonosByUsuario($usuarioID, $page, $size, $sort, $onlyActive, $deleted);
        }

    }

?>