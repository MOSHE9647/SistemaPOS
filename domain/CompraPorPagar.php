<?php

    require_once dirname(__DIR__, 1) . "/domain/Compra.php";
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';

    class CompraPorPagar implements JsonSerializable {

        private $compraPorPagarID;
        private $compraPorPagarCompra;
        private $compraPorPagarFechaVencimiento;
        private $compraPorPagarCancelado;
        private $compraPorPagarNotas;
        private $compraPorPagarEstado;

        public function __construct(int $compraPorPagarID = -1, Compra $compraPorPagarCompra = null, $compraPorPagarFechaVencimiento = "", 
            bool $compraPorPagarCancelado = false, string $compraPorPagarNotas = "", bool $compraPorPagarEstado = true) 
        {
            $this->compraPorPagarID = $compraPorPagarID;
            $this->compraPorPagarCompra = $compraPorPagarCompra;
            $this->compraPorPagarFechaVencimiento = $compraPorPagarFechaVencimiento;
            $this->compraPorPagarCancelado = $compraPorPagarCancelado;
            $this->compraPorPagarNotas = $compraPorPagarNotas;
            $this->compraPorPagarEstado = $compraPorPagarEstado;
        }

        public function getCompraPorPagarID(): int { return $this->compraPorPagarID; }
        public function getCompraPorPagarCompra(): ?Compra { return $this->compraPorPagarCompra; }
        public function getCompraPorPagarFechaVencimiento() { return $this->compraPorPagarFechaVencimiento; }
        public function getCompraPorPagarCancelado(): bool { return $this->compraPorPagarCancelado; }
        public function getCompraPorPagarNotas(): string { return $this->compraPorPagarNotas; }
        public function getCompraPorPagarEstado(): bool { return $this->compraPorPagarEstado; }

        public function setCompraPorPagarID(int $compraPorPagarID) { $this->compraPorPagarID = $compraPorPagarID; }
        public function setCompraPorPagarCompra(Compra $compraPorPagarCompra) { $this->compraPorPagarCompra = $compraPorPagarCompra; }
        public function setCompraPorPagarFechaVencimiento($compraPorPagarFechaVencimiento) { $this->compraPorPagarFechaVencimiento = $compraPorPagarFechaVencimiento; }
        public function setCompraPorPagarCancelado(bool $compraPorPagarCancelado) { $this->compraPorPagarCancelado = $compraPorPagarCancelado; }
        public function setCompraPorPagarNotas(string $compraPorPagarNotas) { $this->compraPorPagarNotas = $compraPorPagarNotas; }
        public function setCompraPorPagarEstado(bool $compraPorPagarEstado) { $this->compraPorPagarEstado = $compraPorPagarEstado; }

        public static function fromArray(array $compraPorPagar): CompraPorPagar {
            return new CompraPorPagar(
                intval($compraPorPagar['ID'] ?? -1), 
                Utils::convertToObject($compraPorPagar['Compra'] ?? null, Compra::class),
                $compraPorPagar['Vencimiento'] ?? '', 
                $compraPorPagar['Cancelado'] ?? false, 
                $compraPorPagar['Notas'] ?? '', 
                $compraPorPagar['Estado'] ?? true
            );
        }

        public function jsonSerialize() {
            return [
                'ID' => $this->compraPorPagarID,
                'Compra' => $this->compraPorPagarCompra,
                'Vencimiento' => $this->compraPorPagarFechaVencimiento ? Utils::formatearFecha($this->compraPorPagarFechaVencimiento) : '',
                'VencimientoISO' => $this->compraPorPagarFechaVencimiento ? Utils::formatearFecha($this->compraPorPagarFechaVencimiento, 'Y-MM-dd') : '',
                'Cancelado' => $this->compraPorPagarCancelado,
                'Notas' => $this->compraPorPagarNotas,
                'Estado' => $this->compraPorPagarEstado
            ];
        }

    }

?>