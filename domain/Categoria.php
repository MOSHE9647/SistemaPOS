<?php

    class Categoria implements JsonSerializable {

        private $categoriaID;
        private $categoriaNombre;
        private $categoriaDescripcion;
        private $categoriaEstado;

        function __construct(int $categoriaID = -1, string $categoriaNombre = "", string $categoriaDescripcion = "", bool $categoriaEstado = true) {
            $this->categoriaID = $categoriaID;
            $this->categoriaNombre = strtoupper($categoriaNombre);
            $this->categoriaDescripcion = $categoriaDescripcion;
            $this->categoriaEstado = $categoriaEstado;
        }

        function getCategoriaID(): int { return $this->categoriaID; }
        function getCategoriaNombre(): string { return $this->categoriaNombre; }
        function getCategoriaDescripcion(): string { return $this->categoriaDescripcion; }
        function getCategoriaEstado(): bool { return $this->categoriaEstado; }

        function setCategoriaID(int $categoriaID) { $this->categoriaID = $categoriaID; }
        function setCategoriaNombre(string $categoriaNombre) { $this->categoriaNombre = strtoupper($categoriaNombre); }
        function setCategoriaDescripcion(string $categoriaDescripcion) { $this->categoriaDescripcion = $categoriaDescripcion; }
        function setCategoriaEstado(bool $categoriaEstado) { $this->categoriaEstado = $categoriaEstado; }
        
        function jsonSerialize() {
            return [
                'ID' => $this->categoriaID,
                'Nombre' => $this->categoriaNombre,
                'Descripcion' => $this->categoriaDescripcion,
                'Estado' => $this->categoriaEstado
            ];
        }

    }
    
?>
