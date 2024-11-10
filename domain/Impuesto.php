<?php

    require_once dirname(__DIR__, 1) . '/utils/Utils.php';

    class Impuesto implements JsonSerializable {
        
        private $impuestoID;
        private $impuestoNombre;
        private $impuestoValor;
        private $impuestoDescripcion;
        private $impuestoFechaInicioVigencia;
        private $impuestoFechaFinVigencia;
        private $impuestoEstado;

        function __construct(int $impuestoID = -1, string $impuestoNombre = "", float $impuestoValor = 0.0, string $impuestoDescripcion = "", 
                $impuestoFechaInicioVigencia = null, $impuestoFechaFinVigencia = null, bool $impuestoEstado = true)
        {
            $this->impuestoID = $impuestoID;
            $this->impuestoNombre = strtoupper($impuestoNombre);
            $this->impuestoValor = Utils::formatearDecimal($impuestoValor);
            $this->impuestoDescripcion = ucfirst($impuestoDescripcion);
            $this->impuestoFechaInicioVigencia = $impuestoFechaInicioVigencia;
            $this->impuestoFechaFinVigencia = $impuestoFechaFinVigencia;
            $this->impuestoEstado = $impuestoEstado;
        }

        function getImpuestoID(): int { return $this->impuestoID; }
        function getImpuestoNombre(): string { return $this->impuestoNombre; }
        function getImpuestoValor(): float { return $this->impuestoValor; }
        function getImpuestoDescripcion(): string { return $this->impuestoDescripcion; }
        function getImpuestoFechaInicioVigencia() { return $this->impuestoFechaInicioVigencia; }
        function getImpuestoFechaFinVigencia() { return $this->impuestoFechaFinVigencia; }
        function getImpuestoEstado(): bool { return $this->impuestoEstado; }

        function setImpuestoID(int $impuestoID) { $this->impuestoID = $impuestoID; }
        function setImpuestoNombre(string $impuestoNombre) { $this->impuestoNombre = strtoupper($impuestoNombre); }
        function setImpuestoValor(float $impuestoValor) { $this->impuestoValor = Utils::formatearDecimal($impuestoValor); }
        function setImpuestoDescripcion(string $impuestoDescripcion) { $this->impuestoDescripcion = ucfirst($impuestoDescripcion); }
        function setImpuestoFechaInicioVigencia($impuestoFechaInicioVigencia) { $this->impuestoFechaInicioVigencia = $impuestoFechaInicioVigencia; }
        function setImpuestoFechaFinVigencia($impuestoFechaFinVigencia) { $this->impuestoFechaFinVigencia = $impuestoFechaFinVigencia; }
        function setImpuestoEstado(bool $impuestoEstado) { $this->impuestoEstado = $impuestoEstado; }
        
        public static function fromArray(array $impuesto): Impuesto {
            return new Impuesto(
                intval($impuesto['ID'] ?? -1), 
                $impuesto['Nombre'] ?? "", 
                floatval($impuesto['Valor'] ?? 0.0), 
                $impuesto['Descripcion'] ?? "", 
                $impuesto['InicioVigencia'] ?? "", 
                $impuesto['FinVigencia'] ?? "", 
                $impuesto['Estado'] ?? true
            );
        }

        public function jsonSerialize() {
            return [
                'ID' => $this->impuestoID,
                'Nombre' => $this->impuestoNombre,
                'Valor' => $this->impuestoValor,
                'Descripcion' => $this->impuestoDescripcion,
                'InicioVigenciaISO' => Utils::formatearFecha($this->impuestoFechaInicioVigencia, 'Y-MM-dd'),
                'InicioVigencia' => Utils::formatearFecha($this->impuestoFechaInicioVigencia),
                'FinVigenciaISO' => Utils::formatearFecha($this->impuestoFechaFinVigencia, 'Y-MM-dd'),
                'FinVigencia' => Utils::formatearFecha($this->impuestoFechaFinVigencia),
                'Estado' => $this->impuestoEstado
            ];
        }

    }

?>