<?php

  require_once __DIR__ . "/../data/presentacionData.php";

    class PresentacionBusiness {

        private $presentacionData;

        public function __construct() {
        $this->presentacionData = new PresentacionData();
       }

       public function verificacionDeDatos($presentacion, $verificarcampos = false, $verificarid = false) {
        try {
            $id = $presentacion->getPresentacionId();
            $nombre = $presentacion->getPresentacionNombre();
            $errors = [];
            
            // Verificar el ID
            if ($verificarid && (empty($id) || $id <= 0 || !is_numeric($id))) {
                $errors[] = "El ID de la presentación está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                Utils::writeLog("El ID '[$id]' de la presentación no es válido.", BUSINESS_LOG_FILE);
            }

            // Verificar los campos requeridos
            if ($verificarcampos) {
                if (empty($nombre)) {
                    $errors[] = "El Nombre de la presentación está vacío. Revisa que esté ingresando correctamente el nombre.";
                    Utils::writeLog("El Nombre '>>[$nombre]' de la presentación no es válido.", BUSINESS_LOG_FILE);
                }
            }

            if (!empty($errors)) {
                throw new Exception(implode('<br>', $errors));
            }

            return ["is_valid" => true];
        } catch (Exception $e) {
            return ["is_valid" => false, "message" => $e->getMessage()];
        }
      }

       public function insertTBPresentacion($presentacion) {
        $check = $this->verificacionDeDatos($presentacion, true);
        if (!$check['is_valid']) { 
            return $check; 
        }
        return $this->presentacionData->insertPresentacion($presentacion);
        }

       public function updateTBPresentacion($presentacion) {
        $check = $this->verificacionDeDatos($presentacion, true, true);
        if (!$check['is_valid']) { 
            return $check; 
        }
        return $this->presentacionData->actualizarPresentacion($presentacion);
        }

        public function deleteTBPresentacion($presentacion) {
        $check = $this->verificacionDeDatos($presentacion, false, true);
        if (!$check['is_valid']) { 
            return $check; 
        }
        return $this->presentacionData->eliminarPresentacion($presentacion->getPresentacionId());
        }

       public function getAllTBPresentaciones() {
        return $this->presentacionData->obtenerListaPresentaciones();
        }

        public function getAllTBProductoPresentacion() {
            return $this->presentacionData->getAllTBProductoPresentacion();
            }

       public function getPaginatedPresentaciones($page, $size, $sort = null) {
        return $this->presentacionData->getPaginatedPresentaciones($page, $size, $sort);
       }
       
    


    }

?>
