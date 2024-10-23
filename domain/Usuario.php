<?php

    require_once dirname(__DIR__, 1) . "/domain/RolUsuario.php";
    require_once dirname(__DIR__, 1) . "/utils/Utils.php";

    class Usuario implements JsonSerializable {

        private $usuarioID;
        private $usuarioRolUsuario;
        private $usuarioNombre;
        private $usuarioApellido1;
        private $usuarioApellido2;
        private $usuarioEmail;
        private $usuarioPassword;
        private $usuarioFechaCreacion;
        private $usuarioFechaModificacion;
        private $usuarioEstado;

        public function __construct(int $usuarioID = -1, string $usuarioNombre = "", string $usuarioApellido1 = "", string $usuarioApellido2 = "", 
            string $usuarioEmail = "", string $usuarioPassword = "", RolUsuario $usuarioRolUsuario = null, $usuarioFechaCreacion = null, 
            $usuarioFechaModificacion = null, bool $usuarioEstado = true
        ) {
            $this->usuarioID = $usuarioID;
            $this->usuarioRolUsuario = $usuarioRolUsuario;
            $this->usuarioNombre = ucwords(strtolower($usuarioNombre));
            $this->usuarioApellido1 = ucwords(strtolower($usuarioApellido1));
            $this->usuarioApellido2 = ucwords(strtolower($usuarioApellido2));
            $this->usuarioEmail = strtolower($usuarioEmail);
            $this->usuarioPassword = $usuarioPassword;
            $this->usuarioFechaCreacion = $usuarioFechaCreacion;
            $this->usuarioFechaModificacion = $usuarioFechaModificacion;
            $this->usuarioEstado = $usuarioEstado;
        }

        public function getUsuarioID(): int { return $this->usuarioID; }
        public function getUsuarioNombre(): string { return $this->usuarioNombre; }
        public function getUsuarioApellido1(): string { return $this->usuarioApellido1; }
        public function getUsuarioApellido2(): string { return $this->usuarioApellido2; }
        public function getUsuarioEmail(): string { return $this->usuarioEmail; }
        public function getUsuarioPassword(): string { return $this->usuarioPassword; }
        public function getUsuarioRolUsuario(): ?RolUsuario { return $this->usuarioRolUsuario; }
        public function getUsuarioFechaCreacion() { return $this->usuarioFechaCreacion; }
        public function getUsuarioFechaModificacion() { return $this->usuarioFechaModificacion; }
        public function getUsuarioEstado(): bool { return $this->usuarioEstado; }

        public function setUsuarioID(int $usuarioID) { $this->usuarioID = $usuarioID; }
        public function setUsuarioRolUsuario(RolUsuario $usuarioRolUsuario) { $this->usuarioRolUsuario = $usuarioRolUsuario; }
        public function setUsuarioNombre(string $usuarioNombre) { $this->usuarioNombre = ucwords(strtolower($usuarioNombre)); }
        public function setUsuarioApellido1(string $usuarioApellido1) { $this->usuarioApellido1 = ucwords(strtolower($usuarioApellido1)); }
        public function setUsuarioApellido2(string $usuarioApellido2) { $this->usuarioApellido2 = ucwords(strtolower($usuarioApellido2)); }
        public function setUsuarioEmail(string $usuarioEmail) { $this->usuarioEmail = strtolower($usuarioEmail); }
        public function setUsuarioPassword(string $usuarioPassword) { $this->usuarioPassword = $usuarioPassword; }
        public function setUsuarioFechaCreacion($usuarioFechaCreacion) { $this->usuarioFechaCreacion = $usuarioFechaCreacion; }
        public function setUsuarioFechaModificacion($usuarioFechaModificacion) { $this->usuarioFechaModificacion = $usuarioFechaModificacion; }
        public function setUsuarioEstado(bool $usuarioEstado) { $this->usuarioEstado = $usuarioEstado; }

        // Retorna si el usuario es administrador
        public function isAdmin(): bool {
            return $this->usuarioRolUsuario ? $this->usuarioRolUsuario->getRolID() === ROL_ADMIN : false;
        }

        // Retorna el nombre completo del usuario
        public function getUsuarioNombreCompleto(): string {
            $nombreCompleto = $this->usuarioNombre;
            if (!empty($this->usuarioApellido1)) { $nombreCompleto .= " " . $this->usuarioApellido1; }
            if (!empty($this->usuarioApellido2)) { $nombreCompleto .= " " . $this->usuarioApellido2; }
            return $nombreCompleto;
        }

        public function jsonSerialize() {
            return [
                'ID' => $this->usuarioID,
                'Nombre' => $this->usuarioNombre,
                'Apellido1' => $this->usuarioApellido1,
                'Apellido2' => $this->usuarioApellido2,
                'Email' => $this->usuarioEmail,
                'RolUsuario' => $this->usuarioRolUsuario ?? null,
                'Creacion' => $this->usuarioFechaCreacion ? Utils::formatearFecha($this->usuarioFechaCreacion) : '',
                'Modificacion' => $this->usuarioFechaModificacion ? Utils::formatearFecha($this->usuarioFechaModificacion) : '',
                'CreacionISO' => $this->usuarioFechaCreacion ? Utils::formatearFecha($this->usuarioFechaCreacion, 'Y-MM-dd') : '',
                'ModificacionISO' => $this->usuarioFechaModificacion ? Utils::formatearFecha($this->usuarioFechaModificacion, 'Y-MM-dd') : '',
                'Estado' => $this->usuarioEstado
            ];
        }

    }

?>