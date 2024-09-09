<?php

    require_once __DIR__ . '/../data/codigoBarrasData.php';
    require_once __DIR__ . '/../service/compraDetalleBusiness.php';
    require_once __DIR__ . '/../service/proveedorBusiness.php';
    require_once __DIR__ . '/../service/productoBusiness.php';
    require_once __DIR__ . '/../service/loteBusiness.php';

    define('NUM_CEROS', 4);

    class CodigoBarrasBusiness {

        private $className;
        private $codigoBarrasData;
    
        public function __construct() {
            $this->className = get_class($this);
            $this->codigoBarrasData = new CodigoBarrasData();
        }

        public function validarCodigoBarrasID($codigoBarrasID) {
            if ($codigoBarrasID === null || !is_numeric($codigoBarrasID) || $codigoBarrasID < 0) {
                Utils::writeLog("El ID [$codigoBarrasID] del código de barras no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ["is_valid" => false, "message" => "El ID del código de barras está vacío o no es válido. Revise que este sea un número y que sea mayor a 0"];
            }

            return ["is_valid" => true];
        }

        public function validarCodigoBarras($codigoBarras, $validarCamposAdicionales = true, $insert = true) {
            try {
                // Obtener los valores de las propiedades del objeto
                $codigoBarrasID = $codigoBarras->getCodigoBarrasID();
                $codigoBarras = $codigoBarras->getCodigoBarrasNumero();

                // Verifica que el ID del Código de Barras sea válido
                if (!$insert) {
                    $checkID = $this->validarCodigoBarrasID($codigoBarrasID);
                    if (!$checkID['is_valid']) { $errors[] = $checkID['message']; }
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

        public function updateTBCodigoBarras($codigoBarras) {
            // Verifica que los datos del Código de Barras sean validos
            $check = $this->validarCodigoBarras($codigoBarras, true, false);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->codigoBarrasData->updateCodigoBarras($codigoBarras);
        }

        public function deleteTBCodigoBarras($codigoBarrasID) {
            // Verifica que los datos del Código de Barras sean validos
            $check = $this->validarCodigoBarrasID($codigoBarrasID);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->codigoBarrasData->deleteCodigoBarras($codigoBarrasID);
        }

        public function getAllTBCodigoBarras($onlyActiveOrInactive = false, $deleted = false) {
            return $this->telefonoData->getAllTBCodigoBarras($onlyActiveOrInactive, $deleted);
        }

        public function getPaginatedCodigosBarras($page, $size, $sort = null, $onlyActiveOrInactive = true, $deleted = false) {
            return $this->telefonoData->getPaginatedCodigosBarras($page, $size, $sort, $onlyActiveOrInactive, $deleted);
        }

        public function getCodigoBarrasByID($codigoBarrasID) {
            // Verifica que los datos del Código de Barras sean validos
            $check = $this->validarCodigoBarrasID($codigoBarrasID);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->codigoBarrasData->getCodigoBarrasByID($codigoBarraID);
        }

        public function generarCodigoDeBarras($compraDetalle) {
            try {
                // Generamos los servicios para manejar los datos
                $compraDetalleBusiness = new CompraDetalleBusiness();
                $proveedorBusiness = new ProveedorBusiness();
                $productoBusiness = new ProductoBusiness();
                $loteBusiness = new LoteBusiness();
                
                // Validamos que el objeto CompraDetalle sea válido
                $check = $compraDetalleBusiness->validarCompraDetalle($compraDetalle);
                if (!$check["is_valid"]) { return ["success" => $check["is_valid"], "message" => $check["message"]]; }
        
                

                // Obtener la información del Lote desde la BD
                $loteBusiness = new LoteBusiness();
                $result = $loteBusiness->getLoteByID($loteID);
                if (!$result['success']) { 
                    throw new Exception($result['message']);                    
                }
        
                $lote = $result['lote'];
        
                // Generar el código de barras
                $codigoBarrasData = $this->generarCodigoBarrasData($lote);
                $ean13 = Utils::calculateEAN13Checksum($codigoBarrasData['codigo']);
        
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

        public function calculateEAN13Checksum($code) {
            // Verificar que el código tenga 12 dígitos
            if (!is_string($code) || strlen($code) != 12 || !ctype_digit($code)) {
                $message = "Error al generar el checksum del código de barras para [$code]: El código debe tener 12 dígitos";
                Utils::writeLog($message, BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                throw new InvalidArgumentException("El código para generar el checksum debe tener 12 dígitos");
            }
        
            // Calcular el dígito de control
            $digits = str_split($code);
            $weights = array(1, 3);
            $sum_weights = 0;
            foreach ($digits as $i => $digit) {
                $sum_weights += $digit * $weights[$i % 2];
            }
            $checksum = (10 - ($sum_weights % 10)) % 10;
        
            // Construir el código EAN-13 completo
            $ean13 = $code . $checksum;

            return $ean13;
        }

        public function generateBarcodeFromHash($loteID, $proveedorID, $productoID) {
            $stringToHash = $loteID . $proveedorID . $productoID;               //<- Concatenar los IDs
            $hash = sha1($stringToHash);                                        //<- Generar un hash (usando SHA-1)
            $numericHash = substr(preg_replace('/[^0-9]/', '', $hash), 0, 12);  //<- Tomar los primeros 12 dígitos numéricos del hash
            $checksum = $this->calculateEAN13Checksum($numericHash);              //<- Calcular el dígito de verificación para EAN-13
            return $checksum;                                                   //<- Retornar el código de barras completo (12 dígitos + checksum)
        }

    }

?>