<?php

    require_once "codigoBarrasBusiness.php";
    require_once __DIR__ . "/../data/ProductoData.php";
    require_once __DIR__ . '/../utils/Utils.php';

    class ProductoBusiness{

        private $className;
        private $productoData;

        public function __construct(){
            $this->productoData = new ProductoData();
            $this->className = get_class($this);
        }

        public function validarProductoID($productoID) {
            if ($productoID === null || !is_numeric($productoID) || $productoID < 0) {
                Utils::writeLog("El ID [$productoID] del producto no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ["is_valid" => false, "message" => "El ID del producto está vacío o no es válido. Revise que este sea un número y que sea mayor a 0"];
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

        public function validarProducto($producto, $validarCamposAdicionales = true, $insert = false) {
            try {
                // Obtener los valores de las propiedades del objeto
                $productoID = $producto->getProductoID();
                $codigoBarras = $producto->getProductoCodigoBarras();
                $nombre = $producto->getProductoNombre();
                $precioCompra = $producto->getProductoPrecioCompra();
                $ganancia = $producto->getProductoPorcentajeGanancia();
                $categoriaID = $producto->getProductoCategoria()->getCategoriaID();
                $subcategoriaID = $producto->getProductoSubcategoria()->getSubcategoriaID();
                $marcaID = $producto->getProductoMarca()->getMarcaID();
                $presentacionID = $producto->getProductoPresentacion()->getPresentacionID();
                $errors = [];

                // Verifica que el ID del producto sea válido
                if (!$insert) {
                    $checkID = $this->validarProductoID($productoID);
                    if (!$checkID['is_valid']) { $errors[] = $checkID['message']; }
                }

                // Si la validación de campos adicionales está activada, valida los otros campos
                if ($validarCamposAdicionales) {
                    // Verifica que el código de barras sea válido
                    $codigoBarrasBusiness = new CodigoBarrasBusiness();
                    $checkCodigoBarras = $codigoBarrasBusiness->validarCodigoBarras($codigoBarras, true, $insert);
                    if (!$checkCodigoBarras['is_valid']) { $errors[] = $checkCodigoBarras['message']; }

                    // Verifica que los demás campos sean válidos
                    if ($nombre === null || empty($nombre) || is_numeric($nombre)) {
                        $errors[] = "El campo 'Nombre' está vacío o no es válido.";
                        Utils::writeLog("El campo 'Nombre [$nombre]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                    }
                    if ($precioCompra === null || empty($precioCompra) || !is_numeric($precioCompra) || $precioCompra <= 0) {
                        $errors[] = "El campo 'Precio Compra' está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                        Utils::writeLog("El campo 'Precio Compra [$precioCompra]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                    }
                    if ($ganancia === null || empty($ganancia) || !is_numeric($ganancia) || $ganancia <= 0) {
                        $errors[] = "El campo 'Ganancia' está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                        Utils::writeLog("El campo 'Porcentaje de Ganancia [$ganancia]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                    }
                    if ($categoriaID === null || !is_numeric($categoriaID) || $categoriaID < 0) {
                        $errors[] = "El campo 'Categoría' está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                        Utils::writeLog("El campo 'Categoría [$categoriaID]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                    }
                    if ($subcategoriaID === null || !is_numeric($subcategoriaID) || $subcategoriaID < 0) {
                        $errors[] = "El campo 'Subcategoría' está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                        Utils::writeLog("El campo 'Subcategoría [$subcategoriaID]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                    }
                    if ($marcaID === null || !is_numeric($marcaID) || $marcaID < 0) {
                        $errors[] = "El campo 'Marca' está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                        Utils::writeLog("El campo 'Marca [$marcaID]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                    }
                    if ($presentacionID === null || !is_numeric($presentacionID) || $presentacionID < 0) {
                        $errors[] = "El campo 'Presentación' está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                        Utils::writeLog("El campo 'Presentación [$presentacionID]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
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

        public function insertTBProducto($producto) {
            // Verifica que los datos del producto sean correctos
            $check = $this->validarProducto($producto, true, true);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->productoData->insertProducto($producto);
        }

        public function updateTBProducto($producto) {
            // Verifica que los datos del producto sean correctos
            $check = $this->validarProducto($producto);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->productoData->updateProducto($producto);
        }

        
        public function deleteTBProducto($productoID) {
            // Verifica que el ID del producto sea válido
            $checkID = $this->validarProductoID($productoID);
            if (!$checkID["is_valid"]) {
                return ["success" => $checkID["is_valid"], "message" => $checkID["message"]];
            }

            return $this->productoData->deleteProducto($productoID);
        }

        public function getAllTBProducto($onlyActive = true, $deleted = false) {
            return $this->productoData->getAllTBProductos($onlyActive, $deleted);
        }

        public function getPaginatedProductos($search, $page, $size, $sort = null, $onlyActive = true, $deleted = false) {
            // Verifica que los datos de paginación sean correctos
            $check = $this->validarDatosPaginacion($page, $size);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->productoData->getPaginatedProductos($search, $page, $size, $sort, $onlyActive, $deleted);
        }

        public function getProductoByID($productoID, $onlyActive = true, $deleted = false) {
            // Verifica que el ID del producto sea válido
            $checkID = $this->validarProductoID($productoID);
            if (!$checkID["is_valid"]) {
                return ["success" => $checkID["is_valid"], "message" => $checkID["message"]];
            }

            return $this->productoData->getProductoByID($productoID, $onlyActive, $deleted);
        }      

    }
?>
