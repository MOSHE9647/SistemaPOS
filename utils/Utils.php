<?php

    //include_once "libs/barcode/BarcodeGenerator.php";
    //include_once "libs/barcode/BarcodeGeneratorPNG.php";

    class Utils {

        public static function writeLog($message, $logFile = 'utils-error.log') {
            $date = date('Y-m-d H:i:s');
            $formattedMessage = "[$date] $message" . PHP_EOL;
            file_put_contents(__DIR__ . '/../logs/' . $logFile, $formattedMessage, FILE_APPEND | LOCK_EX);
        }

        // Método estático para validar fecha
        public static function validarFecha($fecha) {
            $formato = 'Y-m-d';
            $date = DateTime::createFromFormat($formato, $fecha);
            return $date && $date->format($formato) === $fecha;
        }

        // Metodo que convierte una fecha al formato '2 ago. 2024'
        public static function formatearFecha($fecha, $formato = 'd MMMM yyyy') {
            // Crear un objeto DateTime a partir de la fecha
            $date = new DateTime($fecha);
            
            // Seleccionar el formato adecuado basado en el formato proporcionado
            switch ($formato) {
                case 'Y-MM-dd':
                    // Formatear al formato 'Y-MM-dd'
                    return $date->format('Y-m-d');

                case 'd MMMM yyyy':        
                    // Definir los nombres completos y abreviados de los meses
                    $meses = [
                        'ene.', 'feb.', 'mar.', 'abr.', 'may.', 'jun.',
                        'jul.', 'ago.', 'sep.', 'oct.', 'nov.', 'dic.'
                    ];

                    // Formatear al formato 'd MMMM yyyy'
                    $dia = $date->format('d');
                    $mesNombre = $meses[(int)$date->format('n') - 1];
                    $anio = $date->format('Y');
                    return "{$dia} {$mesNombre} {$anio}";

                default:
                    // Manejar formatos no reconocidos
                    throw new InvalidArgumentException("Formato no soportado: $formato");
            }
        }

        public static function fechaMenorOIgualAHoy($fecha) {
            $fechaHoy = date("Y-m-d");
            return $fecha <= $fechaHoy;
        }        

        public static function generateEAN13Barcode($code) {
            // Verificar que el código tenga 12 dígitos
            if (!is_string($code) || strlen($code) != 12 || !ctype_digit($code)) {
                Utils::writeLog("Error al generar el código de barras para [$code]: El código debe tener 12 dígitos");
                throw new InvalidArgumentException("No se pudo generar el código de barras: El código debe tener 12 dígitos");
            }
        
            // Calcular el dígito de control
            $digits = str_split($code);
            $weights = array(1, 3);
            $sum_weights = 0;
            foreach ($digits as $i => $digit) {
                $sum_weights += $digit * $weights[$i % 2];
            }
            $check_digit = (10 - ($sum_weights % 10)) % 10;
        
            // Construir el código EAN-13 completo
            $ean13 = $code . $check_digit;
        
            // Generar la representación gráfica del código de barras utilizando php-barcode
            $barcodePNG = new BarcodeGeneratorPNG();
            $barPNG = $barcodePNG->getBarcode($ean13, $barcodePNG::TYPE_EAN_13, 2, 60);

            return ['barcode' => $ean13, 'png' => $barPNG];
        }

    }

?>