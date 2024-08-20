<?php
   include __DIR__ . "/../data/productoData.php";
   require_once __DIR__ . '/../utils/Utils.php';

   class ProductoBusiness{
        private $productoData;

        public function __construct(){
            $this->productoData = new ProductoData();
        }

        public function validarProducto($producto, $validarCamposAdicionales = true) {
            try {
                // Obtener los valores de las propiedades del objeto
                $productoID = $producto->getProductoID();
                $nombre = $producto->getProductoNombre();
                $precio = $producto->getProductoPrecio();
                $cantidad = $producto->getProductoCantidad();
                $fechaAdquisicion = $producto->getProductoFechaAdquisicion();
                $codigo = $producto->getProductoCodigoBarras();
                $errors = [];

                // Verifica que el ID del producto sea válido
                if ($productoID === null || !is_numeric($productoID) || $productoID < 0) {
                    $errors[] = "El ID del producto está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                    Utils::writeLog("El ID [$productoID] del producto no es válido.", BUSINESS_LOG_FILE);
                }

                // Si la validación de campos adicionales está activada, valida los otros campos
                if ($validarCamposAdicionales) {
                    if ($nombre === null || empty($nombre) || is_numeric($nombre)) {
                        $errors[] = "El campo 'Nombre' está vacío o no es válido.";
                        Utils::writeLog("[Producto] El campo 'Nombre [$nombre]' no es válido.", BUSINESS_LOG_FILE);
                    }
                    if ($precio === null || empty($precio) || !is_numeric($precio) || $precio <= 0) {
                        $errors[] = "El campo 'Precio' está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                        Utils::writeLog("[Producto] El campo 'Precio [$precio]' no es válido.", BUSINESS_LOG_FILE);
                    }
                    if ($cantidad === null || empty($cantidad) || !is_numeric($cantidad) || $cantidad < 0) {
                        $errors[] = "El campo 'Cantidad' está vacío o no es válido. Revise que este sea un número y que sea mayor o igual a 0";
                        Utils::writeLog("[Producto] El campo 'Cantidad [$cantidad]' no es válido.", BUSINESS_LOG_FILE);
                    }
                    if ($codigo === null || empty($codigo) || !is_numeric($codigo)) {
                        $errors[] = "El campo 'Código de Barras' está vacío o no es válido.";
                        Utils::writeLog("[Producto] El campo 'Código de Barras [$codigo]' no es válido.", BUSINESS_LOG_FILE);
                    }
                    if (empty($fechaAdquisicion) || !Utils::validarFecha($fechaAdquisicion)) {
                        $errors[] = "El campo 'Fecha Adquisición' está vacío o no es válido.";
                        Utils::writeLog("[Producto] El campo 'Fecha Adquisición [$fechaAdquisicion]' está vacío o no es válido.", BUSINESS_LOG_FILE);
                    }
                    if (!Utils::fechaMenorOIgualAHoy($fechaAdquisicion)) {
                        $errors[] = "El campo 'Fecha Adquisición' no puede ser una fecha mayor a la de hoy. Revise que la fecha sea menor o igual a la de hoy.";
                        Utils::writeLog("[Impuesto] El campo 'Fecha Adquisición [$fechaAdquisicion]' es mayor a la de hoy.", BUSINESS_LOG_FILE);
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

        public function insertTBProducto($producto){
            $check = $this->validarProducto($producto);
            if(!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->productoData->insertProducto($producto);
        }

        public function updateTBProducto($producto){
            $check = $this->validarProducto($producto);
            if(!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }
            
            return $this->productoData->updateProducto($producto);
        }

        public function deleteTBProducto($producto){
            $check = $this->validarProducto($producto, false);
            if(!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            $productoID = $producto->getProductoID(); //<- Obtenemos el ID verificado del Producto
            unset($producto); //<- Eliminamos el objeto para no ocupar espacio en memoria (en caso de ser necesario)
            return $this->productoData->deleteProducto($productoID);
        }
        
        public function getProductoByID($productoID){
            return $this->productoData->getProductoByID($productoID);
        }

        public function getAllTBProducto(){
            return $this->productoData->getAllProductos();
        }

        public function getPaginatedProductos($page, $size, $sort = null) {
            return $this->productoData->getPaginatedProductos($page, $size, $sort);
        }

   }
?>