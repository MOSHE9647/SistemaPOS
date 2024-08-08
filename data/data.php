<?php

	require_once __DIR__ . '/../utils/Variables.php';

    class Data {
        
        public $servername;
        public $username;
        public $password; 
        public $dbname;

        public function __construct() {
            // Obtiene el nombre del equipo
            $hostName = gethostname();
            
            // Configuracion del acceso a la bd
            switch ($hostName) {
                // PC de Isaac (Ubuntu WSL2)
                case "Moshe9647-PC":
                    $this->servername = "localhost";
                    $this->username = "root";
                    $this->password = "#SistemaPOS1234";
                    $this->dbname = DB_NAME;
                    break;
                // PC de Maikel
                case "2":
                    $this->servername = "localhost";
                    $this->username = "root";
                    $this->password = "";
                    $this->dbname = DB_NAME;
                    break;
                // PC de Javier
                case "3":
                    $this->servername = "localhost";
                    $this->username = "root";
                    $this->password = "";
                    $this->dbname = DB_NAME;
                    break;
                // PC de Gonzalo (Ubuntu WSL2)
                case "4":
                    $this->servername = "localhost";
                    $this->username = "root";
                    $this->password = "";
                    $this->dbname = DB_NAME;
                    break;
                // PC de Jason (Ubuntu 24.04)
                default:
                    $this->servername = "localhost";
                    $this->username = "root";
                    $this->password = "";
                    $this->dbname = DB_NAME;
                    break;
            }
        }

        // Verifica que se esté ejecutando MySQL
        public function isMysqlRunning() {
            $os = PHP_OS_FAMILY;
        
            if ($os === 'Windows') {
                // Comando para Windows
                $output = [];
                exec('tasklist /FI "IMAGENAME eq mysqld.exe"', $output);
                foreach ($output as $line) {
                    if (strpos($line, 'mysqld.exe') !== false) {
                        return true;
                    }
                }
                return false;
            } else {
                // Comando para Linux/Unix
                $output = [];
                exec('systemctl is-active mysql', $output);
                $status = implode("\n", $output);
                return trim($status) === 'active';
            }
        }
        
        // Realiza la conexion a la BD
        public function getConnection() {
            try {
                if (!$this->isMysqlRunning()) {
                    throw new Exception("El servidor de la base de datos no está disponible en este momento. Por favor, inténtelo más tarde.");
                }
        
                $conn = mysqli_connect($this->servername, $this->username, $this->password, $this->dbname);
                if (!$conn) {
                    throw new Exception("No se puede conectar al servidor de la base de datos: " . mysqli_error($conn));
                }
                $conn->set_charset('utf8');
                return ["success" => true, "connection" => $conn, "message" => "Conexión a la base de datos establecida exitosamente."];
            } catch (Exception $e) {
                return ["success" => false, "message" => $e->getMessage()];
            }
        }        

    }

?>