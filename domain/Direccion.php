<?php

    require_once dirname(__DIR__, 1) . '/utils/Utils.php';

    class Direccion implements JsonSerializable {

        private $direccionID;
        private $direccionProvincia;
        private $direccionCanton;
        private $direccionDistrito;
        private $direccionBarrio;
        private $direccionSennas;
        private $direccionDistancia;
        private $direccionEstado;

        function __construct(int $direccionID = -1, string $direccionProvincia = "", string $direccionCanton = "", string $direccionDistrito = "", 
                string $direccionBarrio = "", string $direccionSennas = "", float $direccionDistancia = 0.0, bool $direccionEstado = true)
        {
            $this->direccionID = $direccionID;
            $this->direccionProvincia = strtoupper($direccionProvincia);
            $this->direccionCanton = strtoupper($direccionCanton);
            $this->direccionDistrito = strtoupper($direccionDistrito);
            $this->direccionBarrio = strtoupper($direccionBarrio);
            $this->direccionSennas = strtoupper($direccionSennas);
            $this->direccionDistancia = Utils::formatearDecimal($direccionDistancia);
            $this->direccionEstado = $direccionEstado;
        }

        function getDireccionID(): int { return $this->direccionID; }
        function getDireccionProvincia(): string { return $this->direccionProvincia; }
        function getDireccionCanton():string { return $this->direccionCanton; }
        function getDireccionDistrito(): string { return $this->direccionDistrito; }
        function getDireccionBarrio(): string { return $this->direccionBarrio; }
        function getDireccionSennas(): string { return $this->direccionSennas; }
        function getDireccionDistancia(): float { return $this->direccionDistancia; }
        function getDireccionEstado(): bool { return $this->direccionEstado; }

        function setDireccionID(int $direccionID) { $this->direccionID = $direccionID; }
        function setDireccionProvincia(string $direccionProvincia) { $this->direccionProvincia = $direccionProvincia; }
        function setDireccionCanton(string $direccionCanton) { $this->direccionCanton = $direccionCanton; }
        function setDireccionDistrito(string $direccionDistrito) { $this->direccionDistrito = $direccionDistrito; }
        function setDireccionBarrio(string $direccionBarrio) { $this->direccionBarrio = $direccionBarrio; }
        function setDireccionSennas(string $direccionSennas) { $this->direccionSennas = $direccionSennas; }
        function setDireccionDistancia(float $direccionDistancia) { $this->direccionDistancia = $direccionDistancia; }
        function setDireccionEstado(bool $direccionEstado) { $this->direccionEstado = $direccionEstado; }

        public function getDireccionCompleta(): string {
            $direccionCompleta = "{$this->direccionProvincia}, {$this->direccionCanton}, {$this->direccionDistrito}";
            if (!empty($this->direccionBarrio)) { $direccionCompleta .= ", {$this->direccionBarrio}"; }
            if (!empty($this->direccionSennas)) { $direccionCompleta .= ", {$this->direccionSennas}"; }
            return $direccionCompleta;
        }

        public static function fromArray(array $direccion): Direccion {
            return new Direccion(
                intval($direccion['ID'] ?? -1), 
                $direccion['Provincia'] ?? "", 
                $direccion['Canton'] ?? "", 
                $direccion['Distrito'] ?? "", 
                $direccion['Barrio'] ?? "", 
                $direccion['Sennas'] ?? "", 
                floatval($direccion['Distancia'] ?? 0.0), 
                $direccion['Estado'] ?? true
            );
        }
        
        public function jsonSerialize() {
            return [
                'ID' => $this->direccionID,
                'Provincia' => $this->direccionProvincia,
                'Canton' => $this->direccionCanton,
                'Distrito' => $this->direccionDistrito,
                'Barrio' => $this->direccionBarrio,
                'Sennas' => $this->direccionSennas,
                'Distancia' => $this->direccionDistancia,
                'Estado' => $this->direccionEstado
            ];
        }

    }

?>