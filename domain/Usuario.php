<?php

    class Usuario {

        private $usuarioID;
        private $usuarioNombre;
        private $usuarioApellido1;
        private $usuarioApellido2;
        private $usuarioEmail;
        private $usuarioPassword;
        private $usuarioRolID;
        private $usuarioFechaCreacion;
        private $usuarioFechaModificacion;
        private $usuarioEstado;

        public function __construct(int $usuarioID = -1, string $usuarioNombre = "", string $usuarioApellido1 = "", string $usuarioApellido2 = "", 
            string $usuarioEmail = "", string $usuarioPassword = "", int $usuarioRolID = -1, $usuarioFechaCreacion = null, 
            $usuarioFechaModificacion = null, bool $usuarioEstado = true
        ) {
            $this->usuarioID = $usuarioID;
            $this->usuarioNombre = $usuarioNombre;
            $this->usuarioApellido1 = $usuarioApellido1;
            $this->usuarioApellido2 = $usuarioApellido2;
            $this->usuarioEmail = $usuarioEmail;
            $this->usuarioPassword = $usuarioPassword;
            $this->usuarioRolID = $usuarioRolID;
            $this->usuarioFechaCreacion = $usuarioFechaCreacion;
            $this->usuarioFechaModificacion = $usuarioFechaModificacion;
            $this->usuarioEstado = $usuarioEstado;
        }

        public function setUsuarioID(int $usuarioID) { $this->usuarioID = $usuarioID; }
        public function setUsuarioNombre(string $usuarioNombre) { $this->usuarioNombre = $usuarioNombre; }
        public function setUsuarioApellido1(string $usuarioApellido1) { $this->usuarioApellido1 = $usuarioApellido1; }
        public function setUsuarioApellido2(string $usuarioApellido2) { $this->usuarioApellido2 = $usuarioApellido2; }
        public function setUsuarioEmail(string $usuarioEmail) { $this->usuarioEmail = $usuarioEmail; }
        public function setUsuarioPassword(string $usuarioPassword) { $this->usuarioPassword = $usuarioPassword; }
        public function setUsuarioRolID(int $usuarioRolID) { $this->usuarioRolID = $usuarioRolID; }
        public function setUsuarioFechaCreacion($usuarioFechaCreacion) { $this->usuarioFechaCreacion = $usuarioFechaCreacion; }
        public function setUsuarioFechaModificacion($usuarioFechaModificacion) { $this->usuarioFechaModificacion = $usuarioFechaModificacion; }
        public function setUsuarioEstado(bool $usuarioEstado) { $this->usuarioEstado = $usuarioEstado; }

        public function getUsuarioID(): int { return $this->usuarioID; }
        public function getUsuarioNombre(): string { return $this->usuarioNombre; }
        public function getUsuarioApellido1(): string { return $this->usuarioApellido1; }
        public function getUsuarioApellido2(): string { return $this->usuarioApellido2; }
        public function getUsuarioEmail(): string { return $this->usuarioEmail; }
        public function getUsuarioPassword(): string { return $this->usuarioPassword; }
        public function getUsuarioRolID(): int { return $this->usuarioRolID; }
        public function getUsuarioFechaCreacion() { return $this->usuarioFechaCreacion; }
        public function getUsuarioFechaModificacion() { return $this->usuarioFechaModificacion; }
        public function getUsuarioEstado(): bool { return $this->usuarioEstado; }

        // Retorna el nombre completo del usuario
        public function getUsuarioNombreCompleto(): string {
            return $this->usuarioNombre . " " . $this->usuarioApellido1 . " " . $this->usuarioApellido2;
        }

        // Retorna la información del usuario en formato de cadena
        public function __toString(): string {
            return "Usuario ID: " . $this->usuarioID . "\n" .
                "Nombre: " . $this->usuarioNombre . "\n" .
                "Apellido 1: " . $this->usuarioApellido1 . "\n" .
                "Apellido 2: " . $this->usuarioApellido2 . "\n" .
                "Email: " . $this->usuarioEmail . "\n" .
                "Rol: " . $this->usuarioRolID . "\n" .
                "Fecha de Creación: " . $this->usuarioFechaCreacion->format('Y-m-d H:i:s') . "\n" .
                "Fecha de Modificación: " . $this->usuarioFechaModificacion->format('Y-m-d H:i:s') . "\n" .
                "Estado: " . ($this->usuarioEstado ? "Activo" : "Inactivo");
        }
    }

?>