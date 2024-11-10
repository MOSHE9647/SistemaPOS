<?php

    class Presentacion implements JsonSerializable {

        private $presentacionID;
        private $presentacionNombre;
        private $presentacionDescripcion;
        private $presentacionEstado;

        public function __construct(int $presentacionID = -1, string $presentacionNombre = "", 
            string $presentacionDescripcion = "", bool $presentacionEstado = true) 
        {
            $this->presentacionID = $presentacionID;
            $this->presentacionNombre = strtoupper($presentacionNombre);
            $this->presentacionDescripcion = ucfirst($presentacionDescripcion);
            $this->presentacionEstado = $presentacionEstado;
        }

        // Getters y Setters
        public function getPresentacionID(): int { return $this->presentacionID; }
        public function getPresentacionNombre(): string { return $this->presentacionNombre; }
        public function getPresentacionDescripcion(): string { return $this->presentacionDescripcion; }
        public function getPresentacionEstado(): bool { return $this->presentacionEstado; }

        public function setPresentacionID(int $presentacionID) { $this->presentacionID = $presentacionID; }
        public function setPresentacionNombre(string $presentacionNombre) { $this->presentacionNombre = strtoupper($presentacionNombre); }
        public function setPresentacionDescripcion(string $presentacionDescripcion) { $this->presentacionDescripcion = ucfirst($presentacionDescripcion); }
        public function setPresentacionEstado(bool $presentacionEstado) { $this->presentacionEstado = $presentacionEstado; }

        public static function fromArray(array $presentacion): Presentacion {
            return new Presentacion(
                intval($presentacion['ID'] ?? -1),
                $presentacion['Nombre'] ?? "",
                $presentacion['Descripcion'] ?? "",
                $presentacion['Estado'] ?? true
            );
        }

        // JsonSerializable
        public function jsonSerialize() {
            return [
                'ID' => $this->presentacionID,
                'Nombre' => $this->presentacionNombre,
                'Descripcion' => $this->presentacionDescripcion,
                'Estado' => $this->presentacionEstado
            ];
        }
        
    }

?>