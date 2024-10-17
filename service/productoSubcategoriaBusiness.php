<?php
    
    require_once __DIR__ . "/../data/productoSubcategoriaData.php";
    
    class ProductoSubcategoriaBusiness {
        
        private $productoSubcategoriaData;
        private $className;

        function __construct(){
            $this->productoSubcategoriaData = new ProductoSubcategoriaData();
            $this->className = get_class($this);
        }
        
        public function validarProductoSubcategoria($productoID = null, $subcategoriaID = null, $both = true) {
            try {
                $errors = [];

                // Validar productoID si es necesario
                if ($both || $productoID !== null) {
                    if (!is_numeric($productoID) || $productoID <= 0) {
                        $errors[] = "El ID del producto está vacío o no es válido. Debe ser un número mayor a 0.";
                        Utils::writeLog("El ID [$productoID] del producto no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                    }
                }

                // Validar subcategoriaID si es necesario
                if ($both || $subcategoriaID !== null) {
                    if (!is_numeric($subcategoriaID) || $subcategoriaID <= 0) {
                        $errors[] = "El ID de la subcategoría está vacío o no es válido. Debe ser un número mayor a 0.";
                        Utils::writeLog("El ID [$subcategoriaID] de la subcategoría no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                    }
                }

                // Si no se proporcionó ningún ID
                if (empty($errors) && !$productoID && !$subcategoriaID) {
                    // Registrar parámetros faltantes y lanzar excepción
                    $missingParamsLog = "Faltan parámetros para validar:";
                    $missingParamsLog .= " productoID [" . ($productoID ?? 'null') . "]";
                    $missingParamsLog .= " subcategoriaID [" . ($subcategoriaID ?? 'null') . "]";
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

        public function addSubcategoriaToProducto($productoID, $subcategoriaID) {
            // Verifica que los ID de la subcategoria y del producto sean validos
            $check = $this->validarProductoSubcategoria($productoID, $subcategoriaID);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->productoSubcategoriaData->addSubcategoriaToProducto($productoID, $subcategoriaID);
        }

        public function removeSubcategoriaFromProducto($productoID, $subcategoriaID) {
            // Verifica que los ID de la subcategoria y del producto sean validos
            $check = $this->validarProductoSubcategoria($productoID, $subcategoriaID);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->productoSubcategoriaData->removeSubcategoriaFromProducto($productoID, $subcategoriaID);
        }

        public function getSubcategoriasByProducto($productoID, $json = false) {
            // Verifica que el ID del producto sea valido
            $check = $this->validarProductoSubcategoria($productoID, null, false);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->productoSubcategoriaData->getSubcategoriasByProducto($productoID, $json);
        }

        public function getPaginatedSubcategoriasByProducto($productoID, $page, $size, $sort = null, $onlyActive = true, $deleted = false) {
            // Verifica que el ID del producto sea valido
            $check = $this->validarProductoSubcategoria($productoID, null, false);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->productoSubcategoriaData->getPaginatedSubcategoriasByProducto($productoID, $page, $size, $sort, $onlyActive, $deleted);
        }

    }

?>