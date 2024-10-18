<?php

    require_once dirname(__DIR__, 1) . "/service/codigoBarrasBusiness.php";
    require_once dirname(__DIR__, 1) . "/data/ProductoData.php";
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';

    class ProductoBusiness{

        private $className;     //<- Variable para almacenar el nombre de la clase
        private $productoData;  //<- Variable para almacenar la clase ProductoData

        /**
         * Constructor de la clase ProductoBusiness.
         */
        public function __construct(){
            $this->productoData = new ProductoData(); //<- Se crea un objeto de la clase ProductoData
            $this->className = get_class($this);      //<- Se obtiene el nombre de la clase
        }

        /**
         * Valida el ID de un producto.
         *
         * @param mixed $productoID El ID del producto a validar.
         * 
         * @return array Un arreglo asociativo que indica si el ID es válido y un mensaje en caso de error.
         *               - "is_valid" (bool): Indica si el ID es válido.
         *               - "message" (string): Mensaje de error si el ID no es válido.
         */
        public function validarProductoID($productoID) {
            if ($productoID === null || !is_numeric($productoID) || $productoID < 0) {
                Utils::writeLog("El ID [$productoID] del producto no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ["is_valid" => false, "message" => "El ID del producto está vacío o no es válido. Revise que este sea un número y que sea mayor a 0"];
            }
            return ["is_valid" => true];
        }

        /**
         * Valida los datos de paginación.
         *
         * @param mixed $page El número de página a validar.
         * @param mixed $size El tamaño de la página a validar.
         * 
         * @return array Un arreglo asociativo que indica si los datos de paginación son válidos y un mensaje en caso de error.
         *               - "is_valid" (bool): Indica si los datos de paginación son válidos.
         *               - "message" (string): Mensaje de error si los datos de paginación no son válidos.
         */
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

        /**
         * Valida los datos de un producto.
         *
         * @param Producto $producto El producto a validar.
         * @param bool $validarCamposAdicionales Indica si se deben validar los campos adicionales del producto.
         * @param bool $insert Indica si se está insertando un nuevo producto.
         * 
         * @return array Un arreglo asociativo que indica si los datos del producto son válidos y un mensaje en caso de error.
         *               - "is_valid" (bool): Indica si los datos del producto son válidos.
         *               - "message" (string): Mensaje de error si los datos del producto no son válidos.
         */
        public function validarProducto($producto, $validarCamposAdicionales = true, $insert = false) {
            try {
                // Obtener los valores de las propiedades del objeto
                $productoID = $producto->getProductoID();
                $codigoBarras = $producto->getProductoCodigoBarras();
                $nombre = $producto->getProductoNombre();
                $cantidad = $producto->getProductoCantidad();
                $precioCompra = $producto->getProductoPrecioCompra();
                $ganancia = $producto->getProductoPorcentajeGanancia();
                $categoriaID = $producto->getProductoCategoria()->getCategoriaID();
                $subcategoriaID = $producto->getProductoSubcategoria()->getSubcategoriaID();
                $marcaID = $producto->getProductoMarca()->getMarcaID();
                $presentacionID = $producto->getProductoPresentacion()->getPresentacionID();
                $fechaVencimiento = $producto->getProductoFechaVencimiento();
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
                    if ($cantidad === null || empty($cantidad) || !is_numeric($cantidad) || $cantidad <= 0) {
                        $errors[] = "El campo 'Cantidad' está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                        Utils::writeLog("El campo 'Cantidad [$cantidad]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
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
                    if ($fechaVencimiento === null || empty($fechaVencimiento) || !Utils::validarFecha($fechaVencimiento) || Utils::fechaMenorOIgualAHoy($fechaVencimiento)) {
                        $errors[] = "El campo 'Fecha de Vencimiento' está vacío o no es válido. Revise que este sea una fecha válida y que sea mayor a la fecha actual";
                        Utils::writeLog("El campo 'Fecha de Vencimiento [$fechaVencimiento]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
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

        /**
         * Inserta un nuevo producto en la base de datos.
         *
         * @param array $producto Los datos del producto a insertar.
         * @return array Un arreglo asociativo con el resultado de la operación.
         *               - "success" (bool): Indica si la inserción fue exitosa.
         *               - "message" (string): Mensaje de error en caso de que la validación falle.
         */
        public function insertTBProducto($producto) {
            // Verifica que los datos del producto sean correctos
            $check = $this->validarProducto($producto, true, true);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }
            return $this->productoData->insertProducto($producto);
        }

        /**
         * Actualiza la información de un producto en la base de datos.
         *
         * @param array $producto Arreglo asociativo que contiene los datos del producto a actualizar.
         * 
         * @return array Arreglo asociativo con el resultado de la operación. Contiene:
         *               - "success" (bool): Indica si la actualización fue exitosa.
         *               - "message" (string): Mensaje descriptivo del resultado de la operación.
         */
        public function updateTBProducto($producto) {
            // Verifica que los datos del producto sean correctos
            $check = $this->validarProducto($producto);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }
            return $this->productoData->updateProducto($producto);
        }

        /**
         * Elimina un producto de la base de datos.
         *
         * @param int $productoID El ID del producto a eliminar.
         * @return array Un arreglo asociativo que indica si la operación fue exitosa y un mensaje.
         *
         * El arreglo de retorno tiene la siguiente estructura:
         * - "success" (bool): Indica si la operación de eliminación fue exitosa.
         * - "message" (string): Un mensaje descriptivo del resultado de la operación.
         */
        public function deleteTBProducto($productoID) {
            // Verifica que el ID del producto sea válido
            $checkID = $this->validarProductoID($productoID);
            if (!$checkID["is_valid"]) {
                return ["success" => $checkID["is_valid"], "message" => $checkID["message"]];
            }
            return $this->productoData->deleteProducto($productoID);
        }

        /**
         * Obtiene todos los productos de la base de datos.
         *
         * @param bool $onlyActive Indica si se deben obtener solo los productos activos. Por defecto es true.
         * @param bool $deleted Indica si se deben incluir los productos eliminados. Por defecto es false.
         * @return array Retorna un arreglo con todos los productos obtenidos.
         */
        public function getAllTBProducto($onlyActive = true, $deleted = false) {
            return $this->productoData->getAllTBProducto($onlyActive, $deleted);
        }

        /**
         * Obtiene una lista paginada de productos.
         *
         * @param string $search Término de búsqueda para filtrar productos.
         * @param int $page Número de la página actual.
         * @param int $size Cantidad de productos por página.
         * @param string|null $sort (Opcional) Criterio de ordenación (nombre, precio, cantidad, fechavencimiento, etc...).
         * @param bool $onlyActive (Opcional) Indica si solo se deben incluir productos activos. Por defecto es true.
         * @param bool $deleted (Opcional) Indica si se deben incluir productos eliminados. Por defecto es false.
         * @return array Resultado de la operación con éxito y mensaje o lista de productos paginados.
         */
        public function getPaginatedProductos($search, $page, $size, $sort = null, $onlyActive = true, $deleted = false) {
            // Verifica que los datos de paginación sean correctos
            $check = $this->validarDatosPaginacion($page, $size);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }
            return $this->productoData->getPaginatedProductos($search, $page, $size, $sort, $onlyActive, $deleted);
        }

        /**
         * Obtiene un producto por su ID.
         *
         * @param int $productoID El ID del producto a obtener.
         * @param bool $onlyActive (Opcional) Indica si solo se deben incluir productos activos. Por defecto es true.
         * @param bool $deleted (Opcional) Indica si se deben incluir productos eliminados. Por defecto es false.
         * @return array Resultado de la operación con éxito y mensaje o el producto obtenido.
         */
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
