<?php
    
    require_once __DIR__ . "/../data/productoCategoriaData.php";
    
    class ProductoCategoriaBusiness {
        
        private $productoCategoriaData;
        private $className;

        function __construct(){
            $this->productoCategoriaData = new ProductoCategoriaData();
            $this->className = get_class($this);
        }
        
        public function validarProductoCategoria($productoID = null, $categoriaID = null, $both = true) {
            try {
                $errors = [];

                // Validar productoID si es necesario
                if ($both || $productoID !== null) {
                    if (!is_numeric($productoID) || $productoID <= 0) {
                        $errors[] = "El ID del producto está vacío o no es válido. Debe ser un número mayor a 0.";
                        Utils::writeLog("El ID [$productoID] del producto no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                    }
                }

                // Validar categoriaID si es necesario
                if ($both || $categoriaID !== null) {
                    if (!is_numeric($categoriaID) || $categoriaID <= 0) {
                        $errors[] = "El ID de la categoría está vacío o no es válido. Debe ser un número mayor a 0.";
                        Utils::writeLog("El ID [$categoriaID] de la categoría no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                    }
                }

                // Si no se proporcionó ningún ID
                if (empty($errors) && !$productoID && !$categoriaID) {
                    // Registrar parámetros faltantes y lanzar excepción
                    $missingParamsLog = "Faltan parámetros para validar:";
                    $missingParamsLog .= " productoID [" . ($productoID ?? 'null') . "]";
                    $missingParamsLog .= " categoriaID [" . ($categoriaID ?? 'null') . "]";
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

        public function addCategoriaToProducto($productoID, $categoriaID) {
            // Verifica que los ID de la categoria y del producto sean validos
            $check = $this->validarProductoCategoria($productoID, $categoriaID);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->productoCategoriaData->addCategoriaToProducto($productoID, $categoriaID);
        }

        public function removeCategoriaFromProducto($productoID, $categoriaID) {
            // Verifica que los ID de la categoria y del producto sean validos
            $check = $this->validarProductoCategoria($productoID, $categoriaID);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->productoCategoriaData->removeCategoriaFromProducto($productoID, $categoriaID);
        }

        public function getCategoriasByProducto($productoID, $json = false) {
            // Verifica que el ID del producto sea valido
            $check = $this->validarProductoCategoria($productoID, null, false);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->productoCategoriaData->getCategoriasByProducto($productoID, $json);
        }

        public function getPaginatedCategoriasByProducto($productoID, $page, $size, $sort = null, $onlyActiveOrInactive = true, $deleted = false) {
            // Verifica que el ID del producto sea valido
            $check = $this->validarProductoCategoria($productoID, null, false);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->productoCategoriaData->getPaginatedCategoriasByProducto($productoID, $page, $size, $sort, $onlyActiveOrInactive, $deleted);
        }
        
        public function getAllTBProductoCategoria() {
            return $this->productoCategoriaData->getAllTBProductoCategoria();
        }
    }

?>