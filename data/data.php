<?php

	require_once dirname(__DIR__, 1) . '/utils/Variables.php';
	require_once dirname(__DIR__, 1) . '/utils/Utils.php';

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
                // PC de Isaac (Ubuntu WSL2 - Win11)
                case "Moshe9647-PC":
                    // $this->servername = CLOUD_DB_HOST;
                    // $this->username = CLOUD_DB_USER;
                    // $this->password = CLOUD_DB_PASS;
                    // $this->dbname = CLOUD_DB_NAME;
                    $this->servername = DB_HOST;
                    $this->username = DB_USER;
                    $this->password = DB_PASS;
                    $this->dbname = DB_NAME;
                    break;
                // PC de Gonzalo (Ubuntu WSL2 - Win10)
                case "DESKTOP-G544DN0":
                    $this->servername = CLOUD_DB_HOST;
                    $this->username = CLOUD_DB_USER;
                    $this->password = CLOUD_DB_PASS;
                    $this->dbname = CLOUD_DB_NAME;
                    break;
                // PC de Jason (Ubuntu 24.04)
                case "jasonmadrigalo-123":
                    $this->servername = CLOUD_DB_HOST;
                    $this->username = CLOUD_DB_USER;
                    $this->password = CLOUD_DB_PASS;
                    $this->dbname = CLOUD_DB_NAME;
                    break;
                // PC de Javier (Windows)
                case "Javier":
                    $this->servername = CLOUD_DB_HOST;
                    $this->username = CLOUD_DB_USER;
                    $this->password = CLOUD_DB_PASS;
                    $this->dbname = CLOUD_DB_NAME;
                    break;
                // Otras PC's
                default:
                    $this->servername = DB_HOST;
                    $this->username = DB_USER;
                    $this->password = "";
                    $this->dbname = DB_NAME;
                    break;
            }
            
        }
        
        /**
         * Establishes a connection to the MySQL database.
         *
         * @return array An array containing the connection status and the connection object.
         *               The array has the following structure:
         *               [
         *                   "success" => bool, // Whether the connection was successful
         *                   "connection" => mysqli, // The MySQL connection object
         *                   "message" => string // An error message if the connection failed
         *               ]
         *
         * @throws Exception If the MySQL server is not running or if there is an error connecting to the database.
         *
         * @example
         * $db = new Database();
         * $result = $db->getConnection();
         * if ($result["success"]) {
         *     $conn = $result["connection"];
         *     // Use the connection object to perform queries
         * } else {
         *     echo $result["message"]; // Display the error message
         * }
         */
        public function getConnection() {
            try {
                // Intentar establecer una conexión con la base de datos
                $conn = mysqli_connect($this->servername, $this->username, $this->password, $this->dbname);

                // Verificar si la conexión fue exitosa
                if (!$conn) {
                    // Lanzar una excepción con el código y mensaje de error
                    throw new Exception(mysqli_connect_error(), mysqli_connect_errno());
                }

                // Establecer el conjunto de caracteres para la conexión
                if (!mysqli_set_charset($conn, 'utf8')) {
                    $userMessage = "No se pudo establecer el formato de texto adecuado para la base de datos.";
                    $logMessage = "Error al establecer el conjunto de caracteres UTF-8: " . mysqli_error($conn);
                    Utils::writeLog($logMessage, DATA_LOG_FILE);
                    throw new Exception($userMessage);
                }

                return ["success" => true, "connection" => $conn];
            } catch (Exception $e) {
                // Manejo del error dentro del bloque catch
                $userMessage = $this->handleMysqlError(
                    $e->getCode(), 
                    $e->getMessage(), 
                    'Error de conexión a la base de datos' //<- Mensaje para específicar el origen del error
                );

                // Devolver mensaje amigable para el usuario
                return ["success" => false, "message" => $userMessage];
            }
        }
        
        /**
         * Handles MySQL errors and returns a user-friendly error message.
         *
         * This function takes three parameters: the MySQL error code, the error message, and a log message.
         * It uses a switch statement to map the error code to a user-friendly error message.
         * The error message is also logged to a file using the Utils::writeLog function.
         *
         * @param int $errorCode The MySQL error code
         * @param string $errorMessage The error message returned by MySQL
         * @param string $logMessage The log message to write to the log file
         * @return string A user-friendly error message
         *
         * Example:
         * ```
         * $errorCode = 1045;
         * $errorMessage = "Access denied for user 'username'@'localhost' (using password: YES)";
         * $logMessage = "Error connecting to database";
         * $userMessage = handleMysqlError($errorCode, $errorMessage, $logMessage);
         * echo $userMessage; // Output: "Error en la autenticación del usuario en la base de datos. Verifique su nombre de usuario y contraseña."
         * ```
         */
        public function handleMysqlError($errorCode, $errorMessage, $logMessage, $className = null, $line = null) {
            switch ($errorCode) {
                case 1044:
                    $userMessage = "Acceso denegado para el usuario de la base de datos. Verifique sus credenciales.";
                    break;
                case 1045:
                    $userMessage = "Error en la autenticación del usuario en la base de datos. Verifique su nombre de usuario y contraseña.";
                    break;
                case 1049:
                    $userMessage = "La base de datos solicitada no existe. Verifique el nombre de la base de datos.";
                    break;
                case 1064:
                    $userMessage = "Error de sintaxis en la consulta SQL. Revise la consulta SQL que se está ejecutando.";
                    break;
                case 2002:
                    $userMessage = "No se puede conectar al servidor de la base de datos. Verifique la dirección del servidor.";
                    break;
                case 0:
                    $userMessage = $errorMessage;
                    break;
                default:
                    $userMessage = "Error al acceder a los datos. Por favor, inténtelo de nuevo más tarde. Si el problema persiste, póngase en contacto con nosotros.";
            }
            
            $message = "$logMessage (Código: $errorCode): $errorMessage";
            Utils::writeLog($message, DATA_LOG_FILE, ERROR_MESSAGE, $className ?? self::class, $line);
            return $userMessage;
        }

    }
