<?php

    require_once __DIR__ . '/../data/codigoBarrasData.php';

    class CodigoBarrasBusiness {

        private $className;
        private $codigoBarrasData;
    
        public function __construct() {
            $this->className = get_class($this);
            $this->codigoBarrasData = new CodigoBarrasData();
        }

        public function validarCodigoBarrasID($codigoBarrasID) {
            if ($codigoBarrasID === null || !is_numeric($codigoBarrasID) || $codigoBarrasID < 0) {
                Utils::writeLog("El ID [$codigoBarrasID] del código de barras no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                return ["is_valid" => false, "message" => "El ID del código de barras está vacío o no es válido. Revise que este sea un número y que sea mayor a 0"];
            }

            return ["is_valid" => true];
        }

        public function validarDatosPaginacion($page, $size) {
            if ($page === null || !is_numeric($page) || $page < 0) {
                Utils::writeLog("El 'page [$page]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                return ["is_valid" => false, "message" => "El número de página está vacío o no es válido. Revise que este sea un número y que sea mayor o igual a 0"];
            }

            if ($size === null || !is_numeric($size) || $size < 0) {
                Utils::writeLog("El 'size [$size]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                return ["is_valid" => false, "message" => "El tamaño de la página está vacío o no es válido. Revise que este sea un número y que sea mayor o igual a 0"];
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

        public function insertTBCodigoBarras($codigoBarras, $conn = null) {
            // Verifica que los datos del Código de Barras sean validos
            $check = $this->validarCodigoBarras($codigoBarras);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->codigoBarrasData->insertCodigoBarras($codigoBarras, $conn);
        }

        public function updateTBCodigoBarras($codigoBarras) {
            // Verifica que los datos del Código de Barras sean validos
            $check = $this->validarCodigoBarras($codigoBarras, true, false);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->codigoBarrasData->updateCodigoBarras($codigoBarras);
        }

        public function deleteTBCodigoBarras($codigoBarrasID, $conn = null) {
            // Verifica que los datos del Código de Barras sean validos
            $check = $this->validarCodigoBarrasID($codigoBarrasID);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->codigoBarrasData->deleteCodigoBarras($codigoBarrasID, $conn);
        }

        public function getAllTBCodigoBarras($onlyActive = false, $deleted = false) {
            return $this->codigoBarrasData->getAllTBCodigoBarras($onlyActive, $deleted);
        }

        public function getPaginatedCodigosBarras($page, $size, $sort = null, $onlyActive = true, $deleted = false) {
            // Verifica que los datos de paginación sean válidos
            $check = $this->validarDatosPaginacion($page, $size);
            if (!$check['is_valid']) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->codigoBarrasData->getPaginatedCodigosBarras($page, $size, $sort, $onlyActive, $deleted);
        }

        public function getCodigoBarrasByID($codigoBarrasID, $onlyActive = true, $deleted = false) {
            // Verifica que los datos del Código de Barras sean validos
            $check = $this->validarCodigoBarrasID($codigoBarrasID);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }

            return $this->codigoBarrasData->getCodigoBarrasByID($codigoBarrasID, $onlyActive, $deleted);
        }

        public function generarCodigoDeBarras() {
            try {
                // Generar el código de barras a partir de un ID único
                $ean13 = $this->generateBarcodeFromHash(Utils::generateCodeFromUUID());
                return ['success' => true, 'code' => $ean13];
            } catch (Exception $e) {
                // Manejo de errores y logging
                $message = 'Ocurrió un error al intentar generar el código de barras: ' . $e->getMessage();
                return ["success" => false, "message" => $message];
            }
        }

        /**
         * Genera un código de barras a partir de un hash.
         *
         * @param string $stringToHash El string desde el cual se quiere generar el hash.
         * @return string El código de barras generado (12 dígitos + checksum).
         */
        private function generateBarcodeFromHash($stringToHash = '') {
            $uniqueString = $stringToHash . date('YmdHis');                 //<- Agregar la fecha y hora actual al string proporcionado
            $hash = md5($uniqueString);                                     //<- Generar un hash (usando MD5)
            $barcode = substr(preg_replace('/[^0-9]/', '', $hash), 0, 12);  //<- Tomar los primeros 12 dígitos numéricos del hash
            $ean13code = $this->calculateEAN13Checksum($barcode);           //<- Calcular el dígito de verificación para EAN-13
            return $ean13code;                                              //<- Retornar el código de barras completo (12 dígitos + checksum)
        }

        /**
         * Calcula el dígito de control para un código EAN-13.
         *
         * @param string $code El código de 12 dígitos al cual calcular el dígito de control.
         * @return string El código EAN-13 completo con el dígito de control.
         * @throws InvalidArgumentException Si el código no tiene 12 dígitos.
         */
        private function calculateEAN13Checksum($code): string {
            // Verificar que el código tenga 12 dígitos
            if (!is_string($code) || strlen($code) != 12 || !ctype_digit($code)) {
                $message = "Error al generar el checksum del código de barras para [$code]: El código debe tener 12 dígitos";
                Utils::writeLog($message, BUSINESS_LOG_FILE, ERROR_MESSAGE, $className);
                throw new InvalidArgumentException("El código para generar el checksum debe tener 12 dígitos");
            }
        
            // Calcular el dígito de control (checksum) para el código EAN-13
            $digits = str_split($code);                     //<- Convertir el código en un array de dígitos
            $weights = array(1, 3);                         //<- Pesos para los dígitos (1, 3)
            $sum_weights = 0;                               //<- Suma de todos los pesos
            foreach ($digits as $i => $digit) {
                $sum_weights += $digit * $weights[$i % 2];  //<- Multiplicar el dígito por el peso correspondiente
            }
            $checksum = (10 - ($sum_weights % 10)) % 10;    //<- Calcular el checksum (10 - (suma % 10))
            return ($code . $checksum);                     //<- Retornar el código EAN-13 completo (12 dígitos + checksum)
        }

    }

?>