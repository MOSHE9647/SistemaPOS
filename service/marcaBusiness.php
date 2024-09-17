<?php

require_once __DIR__ . "/../data/marcaData.php";

class MarcaBusiness {

    private $marcaData;

    public function __construct() {
        $this->marcaData = new MarcaData();
    }

    public function verificacionDeDatos($marca, $verificarcampos = false, $verificarid = false) {
        try {
            $id = $marca->getMarcaId();
            $nombre = $marca->getMarcaNombre();
            $errors = [];
            
            // Verificar el ID
            if ($verificarid && (empty($id) || $id <= 0 || !is_numeric($id))) {
                $errors[] = "El ID de la marca está vacío o no es válido. Revise que este sea un número y que sea mayor a 0";
                Utils::writeLog("El ID '[$id]' de la marca no es válido.", BUSINESS_LOG_FILE);
            }

            // Verificar los campos requeridos
            if ($verificarcampos) {
                if (empty($nombre)) {
                    $errors[] = "El Nombre de la marca está vacío. Revisa que esté ingresando correctamente el nombre.";
                    Utils::writeLog("El Nombre '>>[$nombre]' de la marca no es válido.", BUSINESS_LOG_FILE);
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

    public function insertTBMarca($marca) {
        $check = $this->verificacionDeDatos($marca, true);
        if (!$check['is_valid']) { 
            return $check; 
        }
        return $this->marcaData->insertMarca($marca);
    }

    public function updateTBMarca($marca) {
        $check = $this->verificacionDeDatos($marca, true, true);
        if (!$check['is_valid']) { 
            return $check; 
        }
        return $this->marcaData->actualizarMarca($marca);
    }

    public function deleteTBMarca($marca) {
        $check = $this->verificacionDeDatos($marca, false, true);
        if (!$check['is_valid']) { 
            return $check; 
        }
        return $this->marcaData->eliminarMarca($marca->getMarcaId());
    }

    public function getAllTBMarcas() {
        return $this->marcaData->obtenerListaMarcas();
    }

    public function getPaginatedMarcas($page, $size, $sort = null) {
        return $this->marcaData->getPaginatedMarcas($page, $size, $sort);
    }
}

?>
