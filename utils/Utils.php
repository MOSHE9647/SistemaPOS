<?php

    require_once 'Variables.php';

    define('NUM_CEROS', 4);

    class Utils {

        private static $className = 'Utils';

        /**
         * Escribe un mensaje en el archivo de log especificado.
         *
         * @param string $message El mensaje a escribir en el log.
         * @param string $logFile El nombre del archivo de log (por defecto UTILS_LOG_FILE).
         * @param string $type El tipo de mensaje (por defecto ERROR_MESSAGE).
         * @param string|null $class La clase desde donde se llama el log (opcional).
         */
        public static function writeLog($message, $logFile = UTILS_LOG_FILE, $type = ERROR_MESSAGE, $class = null) {
            $logDir = __DIR__ . '/../logs/';
            
            // Verifica si la carpeta de logs existe; si no, la crea
            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true); // Crea la carpeta con permisos 0777 y recursivamente
            }
        
            // Formatear el mensaje de log
            $date = date('Y-m-d H:i:s');
            $class = $class ? "['$class']" : '';
            $type = $type == INFO_MESSAGE || $type == WARN_MESSAGE ? "[$type] " : "[$type]";

            // Formatear el mensaje con la fecha, tipo, clase y mensaje
            $formattedMessage = "[$date] $type $class $message" . PHP_EOL;
            file_put_contents($logDir . $logFile, $formattedMessage, FILE_APPEND | LOCK_EX);
        }        

        /**
         * Valida si una fecha está en el formato 'Y-m-d'.
         *
         * @param string $fecha La fecha a validar.
         * @return bool True si la fecha es válida, False en caso contrario.
         */
        public static function validarFecha($fecha) {
            $formato = 'Y-m-d';
            $date = DateTime::createFromFormat($formato, $fecha);
            return $date && $date->format($formato) === $fecha;
        }

        /**
         * Convierte una fecha al formato especificado.
         *
         * @param string $fecha La fecha a formatear.
         * @param string $formato El formato deseado para la fecha (por defecto 'd MMMM yyyy').
         * @return string La fecha formateada.
         */
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

        /**
         * Verifica si una fecha es menor o igual a la fecha actual.
         *
         * @param string $fecha La fecha a comparar.
         * @return bool True si la fecha es menor o igual a hoy, False en caso contrario.
         */
        public static function fechaMenorOIgualAHoy($fecha) {
            $fechaHoy = date("Y-m-d");
            return $fecha <= $fechaHoy;
        }

        /**
         * Verifica si una fecha es mayor o igual a la fecha actual.
         *
         * @param string $fecha La fecha a comparar.
         * @return bool True si la fecha es mayor o igual a hoy, False en caso contrario.
         */
        public static function fechaMayorOIgualAHoy($fecha) {
            $fechaHoy = date("Y-m-d");
            return $fecha >= $fechaHoy;
        }
        
        /**
         * Genera un código a partir de un UUID.
         *
         * @return string El código generado.
         */
        public static function generateCodeFromUUID($size = 5) {
            // Generar un UUID
            $uuid = bin2hex(random_bytes(16));                                      //<- 128 bits (32 caracteres hexadecimales)
            $numericUUID = substr(preg_replace('/[^0-9]/', '', $uuid), 0, $size);   //<- Tomar los primeros dígitos numéricos del UUID según $size
            return $numericUUID;                                                    //<- Retornar el código completo
        }
        
        /**
         * Crea la ruta donde se guardará la imagen.
         *
         * @param string $path La URL para usar en la ruta.
         * @param string $fileName El nombre de la imagen para usar en la ruta.
         * @return string La ruta completa de la imagen.
         *
         * @example
         * $path = "productos/" . Utils::generarURLCarpetaImagen(123, 456, 789);
         * $nombreArchivo = Utils::generateCodeFromUUID(1) . "_" . $nombreProducto;
         * $ruta = $this->crearRutaImagen($path, $nombreArchivo);
         * echo "Ruta de la imagen: " . $ruta; --> "/../view/img/productos/123/456/789/8_lapicero.jpg"
         */
        public static function crearRutaImagen($path, $fileName) {
            $basePath = "/../view/img/";
            $fullPath = $basePath . "$path/";
        
            // Verificar y crear el directorio si no existe
            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0777, true);
            }
        
            return $fullPath . $fileName;
        }

        /**
         * Genera los datos necesarios para crear una URL para generar una carpeta.
         *
         * @param int $loteID El ID del Lote con el cual generar los datos.
         * @param int $productoID El ID del Producto con el cual generar los datos.
         * @param int $proveedorID El ID del Proveedor con el cual generar los datos.
         * @return string Un string que contiene la URL de la carpeta a generar.
         *
         * @example
         * $loteID = 123;
         * $proveedorID = 456;
         * $productoID = 789;
         * $urlCarpeta = $this->generarURLCarpetaImagen($loteID, $proveedorID, $productoID);
         * echo "URL Carpeta: " . $urlCarpeta; --> "123/456/789"
         */
        public static function generarURLCarpetaImagen($loteID, $proveedorID, $productoID) {
            // Se formatean los IDs con ceros a la izquierda
            $loteID = str_pad($loteID, NUM_CEROS, '0', STR_PAD_LEFT);
            $proveedorID = str_pad($proveedorID, NUM_CEROS, '0', STR_PAD_LEFT);
            $productoID = str_pad($productoID, NUM_CEROS, '0', STR_PAD_LEFT);
            // Se crea la URL de la carpeta
            $urlCarpeta = "$loteID/$proveedorID/$productoID";
            return $urlCarpeta;
        }

    }

?>