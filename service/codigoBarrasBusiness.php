<?php

    include __DIR__ . '/../data/codigoBarrasData.php';
    include __DIR__ . '/../service/ProductoBusiness.php';
    include __DIR__ . '/../service/loteBusiness.php';

    define('NUM_CEROS', 4);

    class CodigoBarrasBusiness {

        private $codigoBarrasData;
    
        public function __construct() {
            $this->codigoBarrasData = new CodigoBarrasData();
        }

        public function validarCodigoBarras($codigoBarras, $validarCamposAdicionales = true, $insertar = true) {
            try {
                // Obtener los valores de las propiedades del objeto
                $codigoBarrasID = $codigoBarras->getCodigoBarrasID();
                $codigoBarras = $codigoBarras->getCodigoBarrasNumero();

                // Verifica que el ID del Código de Barras sea válido
                if (!$insertar && ($codigoBarrasID === null || !is_numeric($codigoBarrasID) || $codigoBarrasID < 0)) {
                    $errors[] = "El ID del código de barras está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                    Utils::writeLog("El ID [$codigoBarrasID] del código de barras no es válido.", BUSINESS_LOG_FILE);
                }

                if ($validarCamposAdicionales) {
                    if (!is_string($codigoBarras) || strlen($codigoBarras) != 13 || !ctype_digit($codigoBarras)) {
                        $errors[] = "El Código de Barras introducido no es válido: El código debe tener 13 dígitos";
                        Utils::writeLog("El 'Código de Barras [$codigoBarras]' introducido no es válido: El código debe tener 13 dígitos", BUSINESS_LOG_FILE);
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

        public function insertTBCodigoBarras($codigoBarras) {
            // Verifica que los datos del Código de Barras sean validos
            $check = $this->validarCodigoBarras($codigoBarras);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->codigoBarrasData->insertCodigoBarras($codigoBarras);
        }

        public function deleteTBCodigoBarras($codigoBarras) {
            // Verifica que los datos del Código de Barras sean validos
            $check = $this->validarCodigoBarras($codigoBarras, false, false);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            $codigoBarrasID = $codigoBarras->getCodigoBarrasID();
            unset($codigoBarras);
            return $this->codigoBarrasData->deleteCodigoBarras($codigoBarrasID);
        }

        /**
         * Genera un código de barras para un lote dado.
         *
         * @param int $loteID El ID del lote para generar el código de barras.
         *
         * @return array Un arreglo que contiene el estado de éxito, la ruta de la imagen generada y el código EAN13.
         *
         * @throws Exception Si el ID del lote es inválido o si ocurre un error durante el proceso de generación.
         *
         * @example
         * $resultado = $this->generarCodigoDeBarras(123);
         * if ($resultado['success']) {
         *     echo "Código de barras generado con éxito. Ruta: " . $resultado['path'] . ", Código EAN13: " . $resultado['code'];
         * } else {
         *     echo "Error al generar el código de barras: " . $resultado['message'];
         * }
         */
        public function generarCodigoDeBarras($loteID) {
            try {
                // Validación de entradas
                if (!is_numeric($loteID) || $loteID <= 0) {
                    throw new Exception("El ID del lote proporcionado no es válido.");
                }
        
                // Obtener la información del Lote desde la BD
                $loteBusiness = new LoteBusiness();
                $result = $loteBusiness->getLoteByID($loteID);
                if (!$result['success']) { 
                    throw new Exception($result['message']);                    
                }
        
                $lote = $result['lote'];
        
                // Generar el código de barras
                $codigoBarrasData = $this->generarCodigoBarrasData($lote);
                $ean13 = Utils::generateEAN13Barcode($codigoBarrasData['codigo']);
        
                // Generar la ruta donde se va a guardar la imagen
                $path = $this->crearRutaImagen($codigoBarrasData['codigoURL'], $ean13);
        
                // Insertar el código de barras en la BD
                $codigoBarras = new CodigoBarras($ean13);
                $result = $this->insertTBCodigoBarras($codigoBarras);
                if (!$result['success']) {
                    throw new Exception($result['message']);
                }
                $codigoBarrasID = $result['codigoID'];
        
                // Obtener el Producto desde la BD y actualizar con el código de barras generado
                $productoBusiness = new ProductoBusiness();
                $result = $productoBusiness->getProductoByID($lote->getProductoID());
                if (!$result['success']) {
                    $delete = $this->deleteTBCodigoBarras($codigoBarras);
                    $message = $result['message'] . ". ";
                    if (!$delete['success']) {
                        $message .= $delete['message'];
                    }
                    throw new Exception($message);
                }
                $producto = $result['producto'];
                $producto->setProductoCodigoBarras($codigoBarrasID);
        
                $result = $productoBusiness->updateTBProducto($producto);
                if (!$result['success']) {
                    throw new Exception($result['message']);
                }
        
                return ['success' => true, 'path' => $path, 'code' => $ean13];
            } catch (Exception $e) {
                // Manejo de errores y logging
                $message = 'Ocurrió un error al generar el código de barras. ' . $e->getMessage();
                Utils::writeLog("[CodigoBarras] $message", BUSINESS_LOG_FILE);
                return ["success" => false, "message" => $message];
            }
        }
        
        /**
         * Genera los datos necesarios para crear un código de barras.
         *
         * @param Lote $lote El objeto lote para generar los datos.
         *
         * @return array Un arreglo que contiene el código URL y el código EAN-13 generado.
         *
         * @example
         * $lote = new Lote();
         * $lote->setLoteID(123);
         * $lote->setProveedorID(456);
         * $lote->setProductoID(789);
         * $datos = $this->generarCodigoBarrasData($lote);
         * echo "Código URL: " . $datos['codigoURL'] . ", Código EAN-13: " . $datos['codigo'];
         */
        private function generarCodigoBarrasData($lote) {
            $loteID = str_pad($lote->getLoteID(), NUM_CEROS, '0', STR_PAD_LEFT);
            $proveedorID = str_pad($lote->getProveedorID(), NUM_CEROS, '0', STR_PAD_LEFT);
            $productoID = str_pad($lote->getProductoID(), NUM_CEROS, '0', STR_PAD_LEFT);
        
            $codigoURL = "$loteID/$proveedorID/$productoID";
            $codigo = str_replace('/', '', $codigoURL);
        
            return ['codigoURL' => $codigoURL, 'codigo' => $codigo];
        }
        
        /**
         * Crea la ruta donde se guardará la imagen del código de barras.
         *
         * @param string $codigoURL La URL para usar en la ruta.
         * @param string $ean13 El código EAN13 para usar en la ruta.
         *
         * @return string La ruta completa de la imagen.
         *
         * @example
         * $ruta = $this->crearRutaImagen("123/456/789", "1234567890123");
         * echo "Ruta de la imagen: " . $ruta;
         */
        private function crearRutaImagen($codigoURL, $ean13) {
            $basePath = __DIR__ . "/../view/img/productos/";
            $fullPath = $basePath . "$codigoURL/";
        
            // Verificar y crear el directorio si no existe
            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0777, true);
            }
        
            return $fullPath . "$ean13.png";
        }

    }

    // $codigoBarrasBusiness = new CodigoBarrasBusiness();
    // $result = $codigoBarrasBusiness->generarCodigoDeBarras(1);
    
    // if (!$result['success']) {
    //     echo $result['message'];
    // }
    // else {
    //     echo 'Ruta: ' . $result['path'] . ', Código: ' . $result['code'];
    // }

?>