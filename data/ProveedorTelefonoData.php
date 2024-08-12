<?php
include 'Data.php'; 
include 'ProveedorTelefono.php'; 
include 'Variables.php';

class ProveedorTelefonoData extends Data {

    public function __construct() {
        parent::__construct();
    }

    // Método para obtener el próximo ID
    private function obtenerNuevoId() {
        $query = $this->db->prepare("SELECT MAX(" . Variables::$proveedortelefonoid . ") AS max_id FROM " . Variables::$tablaProveedorTelefono);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        
        // Si la tabla está vacía, devuelve 1 como el primer ID
        if ($result['max_id'] === null) {
            return 1;
        }
        
        return $result['max_id'] + 1;
    }

    // Método para validar si un teléfono ya está asociado a un proveedor
    private function telefonoExiste($telefono, $proveedorid) {
        $query = $this->db->prepare("SELECT COUNT(*) AS count FROM " . Variables::$tablaProveedorTelefono . " WHERE " . Variables::$telefono . " = ? AND " . Variables::$proveedorid . " = ? AND " . Variables::$activo . " = 1");
        $query->execute([$telefono, $proveedorid]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    // Método para validar que el proveedor existe en la base de datos
    private function proveedorExiste($proveedorid) {
        $query = $this->db->prepare("SELECT COUNT(*) AS count FROM " . TB_PROVEEDOR . " WHERE " . PROVEEDOR_ID . " = ?");
        $query->execute([$proveedorid]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    // Método para validar los datos de un ProveedorTelefono(que no hayan datos vacios al insertar)
    private function validarProveedorTelefono($proveedorTelefono) {
        return !empty($proveedorTelefono->getTelefono()) && !empty($proveedorTelefono->getProveedorId());
    }

    // Método para insertar un nuevo registro en ProveedorTelefono
    public function insertarProveedorTelefono($proveedorTelefono) {
     try{ 
        // Valida campos obligatorios y existencia del proveedor
        if (!$this->validarProveedorTelefono($proveedorTelefono) || !$this->proveedorExiste($proveedorTelefono->getProveedorId())) {
            return false; // O lanza una excepción dependiendo de cómo quieras manejarlo
        }

        // Verifica si el teléfono ya está asociado al proveedor
        if ($this->telefonoExiste($proveedorTelefono->getTelefono(), $proveedorTelefono->getProveedorId())) {
            return false;
        }

        // Obtiene un nuevo ID
        $nuevoId = $this->obtenerNuevoId();

        // Ejecutar la inserción en la base de datos
        $query = $this->db->prepare("INSERT INTO " . Variables::$tablaProveedorTelefono . " (" . Variables::$proveedortelefonoid . ", " . Variables::$proveedorid . ", " . Variables::$telefono . ", " . Variables::$activo . ") VALUES (?, ?, ?, ?)");
        return $query->execute([$nuevoId, $proveedorTelefono->getProveedorId(), $proveedorTelefono->getTelefono(), $proveedorTelefono->getActivo()]);

        }catch(){
            error_log($e->getMessage());
            return false;
        }
    }


    // Método para actualizar un registro de ProveedorTelefono existente
    public function actualizarProveedorTelefono($proveedorTelefono) {
     try{// Valida campos obligatorios y existencia del proveedor
        if (!$this->validarProveedorTelefono($proveedorTelefono) || !$this->proveedorExiste($proveedorTelefono->getProveedorId())) {
            return false;
        }

        // Ejecuta la actualización en la base de datos
        $query = $this->db->prepare("UPDATE " . Variables::$tablaProveedorTelefono . " SET " . Variables::$proveedorid . " = ?, " . Variables::$telefono . " = ?, " . Variables::$activo . " = ? WHERE " . Variables::$proveedortelefonoid . " = ?");
        return $query->execute([$proveedorTelefono->getProveedorId(), $proveedorTelefono->getTelefono(), $proveedorTelefono->getActivo(), $proveedorTelefono->getProveedorTelefonoId()]);
      } catch(Exception e){
        error_log($e->getMessage());
        return false;
      }
    }

    // Método para eliminar lógicamente un ProveedorTelefono (marcar como inactivo)
    public function eliminarProveedorTelefono($proveedortelefonoid) {
        try{
            $query = $this->db->prepare("UPDATE " . Variables::$tablaProveedorTelefono . " SET " . Variables::$activo . " = 0 WHERE " . Variables::$proveedortelefonoid . " = ?");
           return $query->execute([$proveedortelefonoid]);
        }catch(Exception e){
            error_log($e->getMessage());
        return false;
        }
    }

    // Método para obtener un ProveedorTelefono por su ID
    public function obtenerProveedorTelefonoPorId($proveedortelefonoid) {
        $query = $this->db->prepare("SELECT * FROM " . Variables::$tablaProveedorTelefono . " WHERE " . Variables::$proveedortelefonoid . " = ?");
        $query->execute([$proveedortelefonoid]);
        $row = $query->fetch(PDO::FETCH_ASSOC);
        return new ProveedorTelefono($row[Variables::$proveedortelefonoid], $row[Variables::$proveedorid], $row[Variables::$telefono], $row[Variables::$activo]);
    }

    // Método para obtener todos los ProveedorTelefonos activos
    public function obtenerProveedoresTelefonosActivos() {
        $query = $this->db->prepare("SELECT * FROM " . Variables::$tablaProveedorTelefono . " WHERE " . Variables::$activo . " = 1");
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $proveedoresTelefonos = [];
        foreach ($results as $row) {
            $proveedoresTelefonos[] = new ProveedorTelefono($row[Variables::$proveedortelefonoid], $row[Variables::$proveedorid], $row[Variables::$telefono], $row[Variables::$activo]);
        }
        return $proveedoresTelefonos;
    }
}
?>
