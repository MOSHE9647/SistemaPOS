<?php
    require_once __DIR__ . "/../data/productoData.php";
    require_once __DIR__ . '/../utils/Utils.php';

    class ProductoBusiness{

        private $className;
        private $productoData;

        public function __construct(){
            $this->productoData = new ProductoData();
        }

        public function validarProductoID($productoID) {
            if ($productoID === null || !is_numeric($productoID) || $productoID < 0) {
                Utils::writeLog("El ID [$productoID] del producto no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ["is_valid" => false, "message" => "El ID del producto está vacío o no es válido. Revise que este sea un número y que sea mayor a 0"];
            }

            return ["is_valid" => true];
        }

        public function validarProducto($producto, $validarCamposAdicionales = true) {
            try {
                // Obtener los valores de las propiedades del objeto
                $productoID = $producto->getProductoID();
                $nombre = $producto->getProductoNombre();
                $precioCompra = $producto->getProductoPrecioCompra();
                $codigoBarras = $producto->getProductoCodigoBarrasID();
                $ganancia = $producto->getPorcentajeGanancia();
                $errors = [];

                // Verifica que el ID del producto sea válido
                $checkID = $this->validarProductoID($productoID);
                if (!$checkID['is_valid']) { $errors[] = $checkID['message']; }

                // Si la validación de campos adicionales está activada, valida los otros campos
                if ($validarCamposAdicionales) {
                    if ($nombre === null || empty($nombre) || is_numeric($nombre)) {
                        $errors[] = "El campo 'Nombre' está vacío o no es válido.";
                        Utils::writeLog("[Producto] El campo 'Nombre [$nombre]' no es válido.", BUSINESS_LOG_FILE);
                    }
                    if ($precioCompra === null || empty($precioCompra) || !is_numeric($precioCompra) || $precioCompra <= 0) {
                        $errors[] = "El campo 'Precio Compra' está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                        Utils::writeLog("[Producto] El campo 'Precio Compra [$precioCompra]' no es válido.", BUSINESS_LOG_FILE);
                    }
                    if ($codigoBarras === null || empty($codigoBarras) || !is_numeric($codigoBarras)) {
                        $errors[] = "El campo 'Código de Barras' está vacío o no es válido.";
                        Utils::writeLog("[Producto] El campo 'Código de Barras [$codigoBarras]' no es válido.", BUSINESS_LOG_FILE);
                    }
                    if ($ganancia === null || empty($ganancia) || !is_numeric($ganancia) || $ganancia <= 0) {
                        $errors[] = "El campo 'Ganancia' está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                        Utils::writeLog("[Producto] El campo 'Porcentaje de ganancia [$ganancia]' no es válido.", BUSINESS_LOG_FILE);
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

        public function insertTBProducto($producto, $imagen) {
            $check = $this->validarProducto($producto);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            // Generar la ruta de la imagen utilizando los métodos de Utils
            $nombreProducto = $producto->getProductoNombre();
            $loteID = $producto->getLoteID(); // Asegúrate de tener este método en tu clase Producto
            $proveedorID = $producto->getProveedorID(); // Asegúrate de tener este método en tu clase Producto
            $productoID = $producto->getProductoID();

            $path = "productos/" . Utils::generarURLCarpetaImagen($loteID, $proveedorID, $productoID);
            $nombreArchivo = Utils::generateCodeFromUUID(1) . "_" . $nombreProducto . ".jpg"; // Asume que la imagen es JPG

            // Guardar la imagen en la ruta generada
            $rutaImagen = Utils::crearRutaImagen($path, $nombreArchivo);

            // Mover la imagen subida a la ruta generada
            if (!move_uploaded_file($imagen['tmp_name'], $rutaImagen)) {
                return ["success" => false, "message" => "Error al guardar la imagen en el servidor"];
            }

            // Asignar la ruta de la imagen al objeto Producto
            $producto->setProductoImagen($rutaImagen);

            return $this->productoData->insertProducto($producto);
        }

        public function updateTBProducto($producto, $imagen = null) {
            $check = $this->validarProducto($producto);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            // Si se proporciona una nueva imagen, se guarda en la ruta correspondiente
            if ($imagen !== null) {
                $nombreProducto = $producto->getProductoNombre();
                $loteID = $producto->getLoteID();
                $proveedorID = $producto->getProveedorID();
                $productoID = $producto->getProductoID();
            
                $path = "productos/" . Utils::generarURLCarpetaImagen($loteID, $proveedorID, $productoID);
                $nombreArchivo = Utils::generateCodeFromUUID(1) . "_" . $nombreProducto . ".jpg";
            
                $rutaImagen = Utils::crearRutaImagen($path, $nombreArchivo);
            
                if (!move_uploaded_file($imagen['tmp_name'], $rutaImagen)) {
                    return ["success" => false, "message" => "Error al guardar la nueva imagen en el servidor"];
                }
            
                $producto->setProductoImagen($rutaImagen);
            }
            

            return $this->productoData->updateProducto($producto);
        }

        
        public function deleteTBProducto($producto) {
            $check = $this->validarProducto($producto, false);
            if (!$check["is_valid"]) {  
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            $productoID = $producto->getProductoID();
            unset($producto);

            return $this->productoData->deleteProducto($productoID);
        }

        public function getProductoByID($productoID) {
            return $this->productoData->getProductoByID($productoID);
        }

        public function getAllTBProducto() {
            return $this->productoData->getAllProductos();
        }

        public function getAllTBCompraDetalleProducto() {
            return $this->productoData->getAllTBCompraDetalleProducto();
        }

        public function getPaginatedProductos($page, $size, $sort = null) {
            return $this->productoData->getPaginatedProductos($page, $size, $sort);
        }
    }
?>
