<?php

    require_once dirname(__DIR__, 1) . "/data/usuarioData.php";
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';

    class UsuarioBusiness {

        private $usuarioData;
        private $className;

        public function __construct() {
            $this->usuarioData = new UsuarioData();
            $this->className = get_class($this);
        }

        public function validarUsuarioID($usuarioID) {
            if ($usuarioID === null || !is_numeric($usuarioID) || $usuarioID < 0) {
                Utils::writeLog("El 'ID [$usuarioID]' del usuario no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                return ["is_valid" => false, "message" => "El ID del usuario está vacío o no es válido. Revise que este sea un número y que sea mayor a 0"];
            }

            return ["is_valid" => true];
        }

        public function validarUsuarioEmail($usuarioEmail) {
            if ($usuarioEmail === null || empty($usuarioEmail) || !filter_var($usuarioEmail, FILTER_VALIDATE_EMAIL)) {
                Utils::writeLog("El 'Correo [$usuarioEmail]' del usuario no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                return ["is_valid" => false, "message" => "El campo 'Correo' está vacío o no es válido. Revise que este sea un correo electrónico válido."];
            }

            return ["is_valid" => true];
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

        public function validarUsuario($usuario, $validarCamposAdicionales = true, $insert = false) {
            try {
                // Obtener los valores de las propiedades del objeto
                $usuarioID = $usuario->getUsuarioID();
                $nombre = $usuario->getUsuarioNombre();
                $apellido1 = $usuario->getUsuarioApellido1();
                $apellido2 = $usuario->getUsuarioApellido2();
                $correo = $usuario->getUsuarioEmail();
                $password = $usuario->getUsuarioPassword();
                $rolID = $usuario->getUsuarioRolUsuario()->getRolID();
                $errors = [];

                // Verifica que el ID del usuario sea válido
                if (!$insert) {
                    $checkID = $this->validarUsuarioID($usuarioID);
                    if (!$checkID['is_valid']) { $errors[] = $checkID['message']; }
                }

                // Si la validación de campos adicionales está activada, valida los otros campos
                if ($validarCamposAdicionales) {
                    if ($nombre === null || empty($nombre) || is_numeric($nombre)) {
                        $errors[] = "El campo 'Nombre' está vacío o no es válido.";
                        Utils::writeLog("El campo 'Nombre [$nombre]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    }
                    if ($apellido1 === null || empty($apellido1) || is_numeric($apellido1)) {
                        $errors[] = "El campo 'Primer Apellido' está vacío o no es válido.";
                        Utils::writeLog("El campo 'Primer Apellido [$apellido1]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    }
                    if ($apellido2 === null || empty($apellido2) || is_numeric($apellido2)) {
                        $errors[] = "El campo 'Segundo Apellido' está vacío o no es válido.";
                        Utils::writeLog("El campo 'Segundo Apellido [$apellido2]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    }
                    if ($rolID === null || !is_numeric($rolID) || $rolID < 0) {
                        $errors[] = "El campo 'Rol' está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                        Utils::writeLog("El campo 'Rol [$rolID]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    }
                    $checkEmail = $this->validarUsuarioEmail($correo);
                    if (!$checkEmail['is_valid']) { $errors[] = $checkEmail['message']; }
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

        public function insertTBUsuario($usuario) {
            // Verifica que los datos del usuario sean validos
            $check = $this->validarUsuario($usuario, true, true);
            if (!$check['is_valid']) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->usuarioData->insertUsuario($usuario);
        }

        public function updateTBUsuario($usuario) {
            // Verifica que los datos del usuario sean validos
            $check = $this->validarUsuario($usuario);
            if (!$check['is_valid']) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->usuarioData->updateUsuario($usuario);
        }

        public function deleteTBUsuario($usuarioID) {
            // Verifica que el ID del usuario sea válido
            $checkID = $this->validarUsuarioID($usuarioID);
            if (!$checkID['is_valid']) {
                return ["success" => $checkID["is_valid"], "message" => $checkID["message"]];
            }

            return $this->usuarioData->deleteUsuario($usuarioID);
        }

        public function getAllTBUsuario($onlyActive = true, $deleted = false) {
            return $this->usuarioData->getAllTBUsuario($onlyActive, $deleted);
        }

        public function getPaginatedUsuarios($search, $page, $size, $sort, $onlyActive = true, $deleted = false) {
            // Verifica que los datos de paginación sean válidos
            $check = $this->validarDatosPaginacion($page, $size);
            if (!$check['is_valid']) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->usuarioData->getPaginatedUsuarios($search, $page, $size, $sort, $onlyActive, $deleted);
        }

        public function getUsuarioByID($usuarioID, $onlyActive = true, $deleted = false) {
            // Verifica que el ID del usuario sea válido
            $checkID = $this->validarUsuarioID($usuarioID);
            if (!$checkID['is_valid']) {
                return ["success" => $checkID["is_valid"], "message" => $checkID["message"]];
            }

            return $this->usuarioData->getUsuarioByID($usuarioID, $onlyActive, $deleted);
        }

        public function getUsuarioByEmail($usuarioEmail) {
            // Verifica que el correo del usuario sea válido
            $checkEmail = $this->validarUsuarioEmail($usuarioEmail);
            if (!$checkEmail['is_valid']) {
                return ["success" => $checkEmail["is_valid"], "message" => $checkEmail["message"]];
            }

            return $this->usuarioData->getUsuarioByEmail($usuarioEmail);
        }

        public function autenticarUsuario(string $email, string $password): array {
            // Consulta a la base de datos para obtener el usuario por email
            $result = $this->getUsuarioByEmail($email);
        
            // Verifica si la consulta fue exitosa y existe el usuario
            if (!$result["success"]) {
                return ["success" => false, "message" => $result["message"]];
            }
        
            // Verifica si la contraseña es correcta
            $usuarioPassword = $result["password"];
            if (!password_verify($password, $usuarioPassword)) {
                return ["success" => false, "message" => "La contraseña ingresada no es correcta."];
            }
        
            // Si las credenciales son válidas, retorna el objeto Usuario
            return ["success" => true, "usuario" => $result["usuario"]];
        }
    }

?>