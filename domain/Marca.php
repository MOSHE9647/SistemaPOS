<?php

    class Marca implements JsonSerializable {
        
        // Atributos privados
        private $marcaID;
        private $marcaNombre;
        private $marcaDescripcion;
        private $marcaEstado;

        // Constructor
        public function __construct(int $marcaID = -1, string $marcaNombre = "", string $marcaDescripcion = "", bool $marcaEstado = true) {
            $this->marcaID = $marcaID;
            $this->marcaNombre = strtoupper($marcaNombre);
            $this->marcaDescripcion = ucfirst($marcaDescripcion);
            $this->marcaEstado = $marcaEstado;
        }

        // Getters y Setters
        public function getMarcaID(): int { return $this->marcaID; }
        public function getMarcaNombre(): string { return $this->marcaNombre; }
        public function getMarcaDescripcion(): string { return $this->marcaDescripcion; }
        public function getMarcaEstado(): bool { return $this->marcaEstado; }

        public function setMarcaID(int $marcaID) { $this->marcaID = $marcaID; }
        public function setMarcaNombre(string $marcaNombre) { $this->marcaNombre = strtoupper($marcaNombre); }
        public function setMarcaDescripcion(string $marcaDescripcion) { $this->marcaDescripcion = ucfirst($marcaDescripcion); }
        public function setMarcaEstado(bool $marcaEstado) { $this->marcaEstado = $marcaEstado; }

        public static function fromArray(array $array): Marca {
            return new Marca(
                $array['ID'] ?? -1,
                $array['Nombre'] ?? "",
                $array['Descripcion'] ?? "",
                $array['Estado'] ?? true
            );
        }

        // JsonSerializable
        public function jsonSerialize() {
            return [
                'ID' => $this->marcaID,
                'Nombre' => $this->marcaNombre,
                'Descripcion' => $this->marcaDescripcion,
                'Estado' => $this->marcaEstado
            ];
        }

    }

?>
