<?php

    class Utils {

        public static function writeLog($message, $logFile = 'SistemaPOSErrors.log') {
            $date = date('Y-m-d H:i:s');
            $formattedMessage = "[$date] $message" . PHP_EOL;
            file_put_contents(__DIR__ . '/../logs/' . $logFile, $formattedMessage, FILE_APPEND | LOCK_EX);
        }

        // Método estático para validar fecha
        public static function validar_fecha($fecha) {
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

    }

?>