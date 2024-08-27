<?php

    class Proveedor {
        
        private $proveedorID;
        private $proveedorNombre;
        private $proveedorEmail;
        private $proveedorTipo;
        private $proveedorEstado;
        private $proveedorFechaRegistro;
        private $proveedorTelefono; // Nuevo campo para el teléfono
    

        function __construct($proveedorNombre, $proveedorEmail, $proveedorFechaRegistro, $proveedorID = 0, $proveedorTipo = "", $proveedorEstado = true,  $proveedorTelefono = "") {
            $this->proveedorID = $proveedorID;
            $this->proveedorNombre = $proveedorNombre;
            $this->proveedorEmail = strtolower($proveedorEmail); // Convertir a minúsculas para consistencia
            $this->proveedorEstado = $proveedorEstado; 
            $this->proveedorTipo = $proveedorTipo;
            $this->proveedorFechaRegistro = $proveedorFechaRegistro;
            $this->proveedorTelefono = $proveedorTelefono; // Inicializar el nuevo campo
        }

        // Getters
        function getProveedorID() { return $this->proveedorID; }
        function getProveedorNombre() { return $this->proveedorNombre; }
        function getProveedorEmail() { return $this->proveedorEmail; }
        function getProveedorEstado() { return $this->proveedorEstado; }
        function getProveedorTipo() { return $this->proveedorTipo; }
        function getProveedorFechaRegistro() { return $this->proveedorFechaRegistro; }
        function getProveedorTelefono() { return $this->proveedorTelefono; } // Nuevo getter
        

        // Setters
        function setProveedorID($proveedorID) { $this->proveedorID = $proveedorID; }
        function setProveedorNombre($proveedorNombre) { $this->proveedorNombre = $proveedorNombre; }
        function setProveedorEmail($proveedorEmail) { $this->proveedorEmail = $proveedorEmail; }
        function setProveedorEstado($proveedorEstado) { $this->proveedorEstado = $proveedorEstado; }
        function setProveedorTipo($proveedorTipo) { $this->proveedorTipo = $proveedorTipo; }
        function setProveedorFechaRegistro($proveedorFechaRegistro) { $this->proveedorFechaRegistro = $proveedorFechaRegistro; }
        function setProveedorTelefono($proveedorTelefono) { $this->proveedorTelefono = $proveedorTelefono; } // Nuevo setter

        // Implementación del método __toString
        public function __toString() {
            return "ID: " . $this->proveedorID . "\n" .
                "Nombre: " . $this->proveedorNombre . "\n" .
                "Email: " . $this->proveedorEmail . "\n" .
                "Tipo: " . $this->proveedorTipo . "\n" .
                "Estado: " . ($this->proveedorEstado ? "Activo" : "Inactivo") . "\n" .
                "Fecha de Registro: " . $this->proveedorFechaRegistro . "\n" .
                "Teléfono: " . $this->proveedorTelefono . "\n"; // Mostrar teléfono en __toString
        }
    }
    
?>
