<?php

    require_once dirname(__DIR__, 1) . '/domain/Categoria.php';

    class Subcategoria implements JsonSerializable {

        private $subcategoriaID;
        private $subcategoriaCategoria;
        private $subcategoriaNombre;
        private $subcategoriaDescripcion;
        private $subcategoriaEstado;

        // Constructor
        public function __construct(int $subcategoriaID = -1, string $subcategoriaNombre = "", string $subcategoriaDescripcion = "", 
            Categoria $subcategoriaCategoria = null, bool $subcategoriaEstado = true) 
        {
            $this->subcategoriaID = $subcategoriaID;
            $this->subcategoriaNombre = strtoupper($subcategoriaNombre);
            $this->subcategoriaDescripcion = ucfirst($subcategoriaDescripcion);
            $this->subcategoriaCategoria = $subcategoriaCategoria;
            $this->subcategoriaEstado = $subcategoriaEstado;
        }

        // Getters
        public function getSubcategoriaID(): int { return $this->subcategoriaID; }
        public function getSubcategoriaNombre(): string { return $this->subcategoriaNombre; }
        public function getSubcategoriaDescripcion(): string { return $this->subcategoriaDescripcion; }
        public function getSubcategoriaCategoria(): ?Categoria {return $this->subcategoriaCategoria; }
        public function getSubcategoriaEstado(): bool { return $this->subcategoriaEstado; }

        // Setters
        public function setSubcategoriaID(int $subcategoriaID) { $this->subcategoriaID = $subcategoriaID; }
        public function setSubcategoriaNombre(string $subcategoriaNombre) { $this->subcategoriaNombre = strtoupper($subcategoriaNombre); }
        public function setSubcategoriaDescripcion(string $subcategoriaDescripcion) { $this->subcategoriaDescripcion = ucfirst($subcategoriaDescripcion); }
        public function setSubcategoriaCategoriaId(Categoria $subcategoriaCategoria){$this->subcategoriaCategoria = $subcategoriaCategoria;}
        public function setSubcategoriaEstado(bool $subcategoriaEstado) { $this->subcategoriaEstado = $subcategoriaEstado; }

        public static function fromArray(array $subcategoria): Subcategoria {
            return new Subcategoria(
                $subcategoria['ID'] ?? -1,
                $subcategoria['Nombre'] ?? "",
                $subcategoria['Descripcion'] ?? "",
                $subcategoria['Categoria'] ? Categoria::fromArray(get_object_vars($subcategoria['Categoria'])) : null,
                $subcategoria['Estado'] ?? true
            );
        }

        // JsonSerializable
        public function jsonSerialize() {
            return [
                'ID' => $this->subcategoriaID,
                'Nombre' => $this->subcategoriaNombre,
                'Descripcion' => $this->subcategoriaDescripcion,
                'Categoria' => $this->subcategoriaCategoria,
                'Estado' => $this->subcategoriaEstado
            ];
        }

    }

?>