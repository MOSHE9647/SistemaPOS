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
         * @param int|null $line El número de línea desde donde se llama el log (opcional).
         */
        public static function writeLog($message, $logFile = UTILS_LOG_FILE, $type = ERROR_MESSAGE, $class = null, $line = null) {
            $logDir = __DIR__ . '/../logs/';
            
            // Verifica si la carpeta de logs existe; si no, la crea
            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true); // Crea la carpeta con permisos 0777 y recursivamente
            }
        
            // Formatear el mensaje de log
            $date = date('Y-m-d H:i:s');
            $class = $class ? "['$class']" : '';
            $line = $line ? "[linea $line]" : '';
            $type = $type == INFO_MESSAGE || $type == WARN_MESSAGE ? "[$type] " : "[$type]";
            
            // Formatear el mensaje con la fecha, tipo, clase, línea y mensaje
            $formattedMessage = "[$date] $type $class $line $message" . PHP_EOL;
            file_put_contents($logDir . $logFile, $formattedMessage, FILE_APPEND | LOCK_EX);
        }

        /**
         * Envia una respuesta JSON con el código de estado, el estado de la operación y un mensaje.
         *
         * @param int $statusCode El código de estado HTTP.
         * @param bool $success El estado de la operación.
         * @param string $message El mensaje de la operación.
         */
        public static function enviarRespuesta($statusCode, $success, $message) {
            http_response_code($statusCode);
            header('Content-Type: application/json');
            echo json_encode(['success' => $success, 'message' => $message]);
            exit();
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
         * Formatea un número decimal a dos decimales.
         *
         * @param float $valor El valor decimal a formatear.
         * @return float El valor formateado a dos decimales.
         */
        public static function formatearDecimal(float $valor): float {
            return number_format((float)$valor, 2, '.', ''); //<- Formatea a dos decimales
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
         * echo "Ruta de la imagen: " . $ruta; --> "view/img/productos/123/456/789/8_lapicero.jpg"
         */
        public static function crearRutaImagen($path, $fileName) {
            $basePath = "/view/static/img/";
            $fullPath = $basePath . "$path/";
        
            // Verificar y crear el directorio si no existe
            $tempPath = __DIR__ . "/..$fullPath";
            if (!is_dir($tempPath)) {
                if (!mkdir($tempPath, 0755, true)) {
                    Utils::writeLog("No se pudo crear el directorio '$fullPath'.", BUSINESS_LOG_FILE, ERROR_MESSAGE, self::$className);
                }
            }

            // Verificar los permisos de la carpeta
            if (!is_writable($tempPath)) {
                // Cambiar permisos si es necesario
                chmod($tempPath, 0755);
            }
        
            return $fullPath . $fileName;
        }

        /**
         * Genera los datos necesarios para crear una URL para generar una carpeta.
         *
         * @param int $categoriaID El ID de la Categoría con el cual generar los datos.
         * @param int $subcategoriaID El ID de la Subcategoría con el cual generar los datos.
         * @param int $productoID El ID del Producto con el cual generar los datos.
         * @return string Un string que contiene la URL de la carpeta a generar.
         *
         * @example
         * $categoriaID = 123;
         * $subcategoriaID = 456;
         * $productoID = 789;
         * $urlCarpeta = $this->generarURLCarpetaImagen($categoriaID, $subcategoriaID, $productoID);
         * echo "URL Carpeta: " . $urlCarpeta; --> "0123/0456/0789"
         */
        public static function generarURLCarpetaImagen($categoriaID, $subcategoriaID) {
            // Se formatean los IDs con ceros a la izquierda
            $categoriaID = str_pad($categoriaID, max(NUM_CEROS, strlen($categoriaID)), '0', STR_PAD_LEFT);
            $subcategoriaID = str_pad($subcategoriaID, max(NUM_CEROS, strlen($subcategoriaID)), '0', STR_PAD_LEFT);
            // Se crea la URL de la carpeta
            $urlCarpeta = "$categoriaID/$subcategoriaID";
            return $urlCarpeta;
        }

        public static function normalizarTexto(string $texto, bool $senna = false): string {
            // Eliminar acentos usando iconv
            $textoNormalizado = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);
    
            // Convertir a mayúsculas y eliminar espacios adicionales
            $textoNormalizado = strtoupper(trim($textoNormalizado));
            
            // Eliminar caracteres especiales y dejar solo letras, números y espacios
            $textoNormalizado = preg_replace('/[^A-Z0-9\s]/', '', $textoNormalizado);
            $textoNormalizado = preg_replace('/\s+/', ' ', $textoNormalizado);
    
            // Reemplazar palabras y abreviaturas comunes
            $reemplazos = [
                'URBANIZACION' => 'URB', 'URB.' => 'URB',
                'AVENIDA' => 'AV', 'AV.' => 'AV',
                'CALLE' => 'CL', 'CL.' => 'CL',
                'CARRERA' => 'CRA', 'CRA.' => 'CRA',
                'BARRIO' => 'BR', 'BR.' => 'BR',
                'PLAZA' => 'PLZ', 'PLZ.' => 'PLZ',
                'PARQUE' => 'PQ', 'PQ.' => 'PQ',
                'EDIFICIO' => 'ED', 'ED.' => 'ED',
                'PASEO' => 'PS', 'PS.' => 'PS'
            ];
            $textoNormalizado = str_replace(array_keys($reemplazos), array_values($reemplazos), $textoNormalizado);
    
            // Eliminar palabras irrelevantes (artículos, preposiciones, etc.)
            if ($senna) {
                $irrelevantes = ['COSTADO', 'FRENTE A', 'DETRAS DE', 'EL', 'LA', 'DE', 'Y', 'A', 'EN', 'CON', 'POR', 'DEL'];
                $textoNormalizado = str_replace($irrelevantes, '', $textoNormalizado);
            }
    
            // Eliminar espacios adicionales de nuevo tras los reemplazos
            $textoNormalizado = trim(preg_replace('/\s+/', ' ', $textoNormalizado));
            
            return $textoNormalizado;
        }

        /**
         * Normaliza un texto eliminando acentos, convirtiendo a mayúsculas y eliminando caracteres especiales.
         *
         * @param string $texto El texto a normalizar.
         * @param bool $senna Indica si se deben eliminar palabras irrelevantes (por defecto true).
         * @return string El texto normalizado.
         */
        public static function esSimiliar(string $texto1, string $texto2, int $porcentajeSimilitud = 80, bool $senna = true): bool {
            $similitud = 0;
            similar_text(
                Utils::normalizarTexto($texto1, $senna), 
                Utils::normalizarTexto($texto2, $senna), 
                $similitud
            );
            return $similitud >= $porcentajeSimilitud;
        }

    }

?>