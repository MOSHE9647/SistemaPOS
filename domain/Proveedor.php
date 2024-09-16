<?php

    class Proveedor {
        
        private $proveedorID;
        private $proveedorNombre;
        private $proveedorEmail;
        private $proveedorEstado;
        private $proveedorFechaRegistro;
        private $proveedorCategoria;



        private $proveedorTelefono; // Nuevo campo para el teléfono id
        private $direccionid; // id direccion

        function __construct($proveedorNombre, $proveedorEmail, $proveedorID = 0,$categoriaid = 0,$direccionid =0, $proveedorEstado = true,  $proveedorTelefono = "") {
            $this->proveedorID = $proveedorID;
            $this->proveedorNombre = $proveedorNombre;
            $this->proveedorEmail = strtolower($proveedorEmail); // Convertir a minúsculas para consistencia
            $this->proveedorEstado = $proveedorEstado; 
            $this->proveedorTelefono = $proveedorTelefono; // Inicializar el nuevo campo
            $this->direccionid = $direccionid;
            $this->proveedorCategoria = $categoriaid;
        }

        // Getters
        function getProveedorID() { return $this->proveedorID; }
        function getProveedorNombre() { return $this->proveedorNombre; }
        function getProveedorEmail() { return $this->proveedorEmail; }
        function getProveedorEstado() { return $this->proveedorEstado; }
        function getProveedorFechaRegistro() { return $this->proveedorFechaRegistro; }
        function getProveedorTelefono() { return $this->proveedorTelefono; } // Nuevo getter
        function getProveedorDireccionId(){return $this->direccionid; }
        function getProveedorCategoria(){return $this->proveedorCategoria; }

        // Setters
        function setProveedorID($proveedorID) { $this->proveedorID = $proveedorID; }
        function setProveedorNombre($proveedorNombre) { $this->proveedorNombre = $proveedorNombre; }
        function setProveedorEmail($proveedorEmail) { $this->proveedorEmail = $proveedorEmail; }
        function setProveedorEstado($proveedorEstado) { $this->proveedorEstado = $proveedorEstado; }
        function setProveedorFechaRegistro($proveedorFechaRegistro) { $this->proveedorFechaRegistro = $proveedorFechaRegistro; }
        function setProveedorTelefono($proveedorTelefono) { $this->proveedorTelefono = $proveedorTelefono; } // Nuevo setter
        function setProveedorDireccionId($direccionid){ $this->direccionid = $direccionid; }
        function setProveedorCategoria($categoriaid){$this->proveedorCategoria = $categoriaid;}
        
        // Implementación del método __toString
        public function __toString() {
            return "ID: " . $this->proveedorID . "\n" .
                "Nombre: " . $this->proveedorNombre . "\n" .
                "Email: " . $this->proveedorEmail . "\n" .
                "Estado: " . ($this->proveedorEstado ? "Activo" : "Inactivo") . "\n" .
                "Fecha de Registro: " . $this->proveedorFechaRegistro . "\n" .
                "Teléfono: " . $this->proveedorTelefono . "\n"; // Mostrar teléfono en __toString
        }
    }
    
?>
