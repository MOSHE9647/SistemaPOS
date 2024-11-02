<?php

	require_once __DIR__ . "/../data/proveedorDireccionData.php";
    require_once __DIR__ . '/../service/proveedorBusiness.php';

	class ProveedorDireccionBusiness {
		
		private $proveedorDireccionData;
        private $className;

		public function __construct() {
			$this->proveedorDireccionData = new ProveedorDireccionData();
			$this->className = get_class($this);
		}

		public function validarProveedorDireccion($proveedorID = null, $direccionID = null, $both = true) {
            try {
                $errors = [];
        
                // Validar proveedorID si es necesario
                if ($both || $proveedorID !== null) {
                    if (!is_numeric($proveedorID) || $proveedorID <= 0) {
                        $errors[] = "El ID del proveedor está vacío o no es válido. Debe ser un número mayor a 0.";
                        Utils::writeLog("El ID [$proveedorID] del proveedor no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    }
                }
        
                // Validar direccionID si es necesario
                if ($both || $direccionID !== null) {
                    if (!is_numeric($direccionID) || $direccionID <= 0) {
                        $errors[] = "El ID del teléfono está vacío o no es válido. Debe ser un número mayor a 0.";
                        Utils::writeLog("El ID [$direccionID] del teléfono no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                    }
                }
        
                // Si no se proporcionó ningún ID
                if (empty($errors) && !$proveedorID && !$direccionID) {
                    // Registrar parámetros faltantes y lanzar excepción
                    $missingParamsLog = "Faltan parámetros para validar:";
                    $missingParamsLog .= " proveedorID [" . ($proveedorID ?? 'null') . "]";
                    $missingParamsLog .= " direccionID [" . ($direccionID ?? 'null') . "]";
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

		public function addDireccionToProveedor($proveedorID, $direccionID) {
			// Verifica que los ID de la dirección y del proveedor sean válidos
			$check = $this->validarProveedorDireccion($proveedorID, $direccionID);
			if (!$check['is_valid']) {
				return ["success" => $check["is_valid"], "message" => $check["message"]];
			}

			return $this->proveedorDireccionData->addDireccionToProveedor($proveedorID, $direccionID);
		}

		public function updateDireccionesProveedor($proveedor) {
			// Valida los datos del Proveedor
            $proveedorBusiness = new ProveedorBusiness();
            $check = $proveedorBusiness->validarProveedor($proveedor);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

			return $this->proveedorDireccionData->updateDireccionesProveedor($proveedor);
		}

		public function removeDireccionFromProveedor($proveedorID, $direccionID) {
			// Verifica que los ID de la dirección y del proveedor sean válidos
			$check = $this->validarProveedorDireccion($proveedorID, $direccionID);
			if (!$check['is_valid']) {
				return ["success" => $check["is_valid"], "message" => $check["message"]];
			}

			return $this->proveedorDireccionData->removeDireccionFromProveedor($proveedorID, $direccionID);
		}

		public function getDireccionesByProveedor($proveedorID, $json = false) {
			// Verifica que el ID del proveedor sea válido
			$check = $this->validarProveedorDireccion($proveedorID, null, false);
			if (!$check['is_valid']) {
				return ["success" => $check["is_valid"], "message" => $check["message"]];
			}

			return $this->proveedorDireccionData->getDireccionesByProveedor($proveedorID, $json);
		}

		public function getPaginatedDireccionesByProveedor($proveedorID, $page, $size, $sort = null, $onlyActive = true, $deleted = false) {
			// Verifica que el ID del proveedor sea válido
			$check = $this->validarProveedorDireccion($proveedorID, null, false);
			if (!$check['is_valid']) {
				return ["success" => $check["is_valid"], "message" => $check["message"]];
			}

			return $this->proveedorDireccionData->getPaginatedDireccionesByProveedor($proveedorID, $page, $size, $sort, $onlyActive, $deleted);
		}

	}

?>