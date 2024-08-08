<?php

    class Data {
        
        public $servername;
        public $username;
        public $password; 
        public $dbname;

        public $isActive;

        public function __construct() {
            // Obtiene el nombre del equipo
            $hostName = gethostname();
            
            // Configuracion del acceso a la bd
            $this->isActive = false;

            switch ($hostName) {
                // PC de Isaac (Ubuntu WSL2)
                case "Moshe9647-PC":
                    $this->servername = "localhost";
                    $this->username = "root";
                    $this->password = "#SistemaPOS1234";
                    $this->dbname = "bdpuntoventa";
                    break;
                default:
                    $this->servername = "localhost";
                    $this->username = "root";
                    $this->password = "";
                    $this->dbname = "bdpuntoventa";
                    break;
            }
        }

    }

?>