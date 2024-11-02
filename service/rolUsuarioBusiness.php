<?php

    require_once __DIR__ .  '/../data/rolUsuarioData.php';
    require_once __DIR__ . '/../utils/Utils.php';

    class RolBusiness {

        private $rolData;
        private $className;

        public function __construct() {
            $this->rolData = new RolData();
            $this->className = get_class($this);
        }

        public function validarRolID($rolID) {
            if ($rolID === null || !is_numeric($rolID) || $rolID < 0) {
                Utils::writeLog("El 'ID [$rolID]' del rol no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                return ["is_valid" => false, "message" => "El ID del rol está vacío o no es válido. Revise que este sea un número y que sea mayor a 0"];
            }

            return ["is_valid" => true];
        }

        public function validarRol($rol, $validarCamposAdicionales = true, $insert = false) {
            try {
                // Obtener los valores de las propiedades del objeto
                $rolID = $rol->getRolID();
                $nombre = $rol->getRolNombre();
                $errors = [];

                // Verifica que el ID del rol sea válido
                if (!$insert) {
                    $checkID = $this->validarRolID($rolID);
                    if (!$checkID['is_valid']) { $errors[] = $checkID['message']; }
                }

                // Si la validación de campos adicionales está activada, valida los otros campos
                if ($validarCamposAdicionales) {
                    if ($nombre === null || empty($nombre) || is_numeric($nombre)) {
                        $errors[] = "El campo 'Nombre' está vacío o no es válido.";
                        Utils::writeLog("El campo 'Nombre [$nombre]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    }
                }

                // Si hay errores, los retorna
                if (!empty($errors)) {
                    throw new Exception(implode('<br>', $errors));
                }

                return ["is_valid" => true];
            } catch (Exception $e) {
                return ["is_valid" => false, "message" => $e->getMessage()];
            }
        }

        public function insertTBRolUsuario($rol) {
            // Verificar que los datos del rol sean válidos
            $checkRol = $this->validarRol($rol, true, true);
            if (!$checkRol['is_valid']) {
                return ['success' => $checkRol['is_valid'], 'message' => $checkRol['message']];
            }

            // Insertar el rol en la base de datos
            return $this->rolData->insertRolUsuario($rol);
        }

        public function updateTBRolUsuario($rol) {
            // Verificar que los datos del rol sean válidos
            $checkRol = $this->validarRol($rol);
            if (!$checkRol['is_valid']) {
                return ['success' => $checkRol['is_valid'], 'message' => $checkRol['message']];
            }

            // Actualizar el rol en la base de datos
            return $this->rolData->updateRolUsuario($rol);
        }

        public function deleteTBRolUsuario($rolID) {
            // Verificar que el ID del rol sea válido
            $checkID = $this->validarRolID($rolID);
            if (!$checkID['is_valid']) {
                return ['success' => $checkID['is_valid'], 'message' => $checkID['message']];
            }

            // Eliminar el rol de la base de datos
            return $this->rolData->deleteRolUsuario($rolID);
        }

        public function getAllTBRolUsuario($onlyActive = false, $deleted = false) {
            return $this->rolData->getAllTBRolUsuario($onlyActive, $deleted);
        }

        public function getPaginatedRoles($page, $size, $sort = null, $onlyActive = true, $deleted = false) {
            return $this->rolData->getPaginatedRoles($page, $size, $sort, $onlyActive, $deleted);
        }

        public function getRolUsuarioByID($rolID) {
            // Verificar que el ID del rol sea válido
            $checkID = $this->validarRolID($rolID);
            if (!$checkID['is_valid']) {
                return ['success' => $checkID['is_valid'], 'message' => $checkID['message']];
            }

            // Obtener el rol de la base de datos
            return $this->rolData->getRolByID($rolID);
        }

    }

?>