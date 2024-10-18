<?php

    require_once dirname(__DIR__, 1) . "/domain/CompraDetalle.php";
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';

    class CompraPorPagar implements JsonSerializable {

        private $compraPorPagarID;
        private $compraPorPagarCompraDetalle;
        private $compraPorPagarFechaVencimiento;
        private $compraPorPagarMontoTotal;
        private $compraPorPagarMontoPagado;
        private $compraPorPagarFechaPago;
        private $compraPorPagarEstadoCompra;
        private $compraPorPagarNotas;
        private $compraPorPagarEstado;

        public function __construct(int $compraPorPagarID = -1, CompraDetalle $compraPorPagarCompraDetalle = null, 
            $compraPorPagarFechaVencimiento = "", float $compraPorPagarMontoTotal = 0.0, float $compraPorPagarMontoPagado = 0.0, 
            $compraPorPagarFechaPago = "", string $compraPorPagarEstadoCompra = "", string $compraPorPagarNotas = "", 
            bool $compraPorPagarEstado = true) 
        {
            $this->compraPorPagarID = $compraPorPagarID;
            $this->compraPorPagarCompraDetalle = $compraPorPagarCompraDetalle;
            $this->compraPorPagarFechaVencimiento = $compraPorPagarFechaVencimiento;
            $this->compraPorPagarMontoTotal = Utils::formatearDecimal($compraPorPagarMontoTotal);
            $this->compraPorPagarMontoPagado = Utils::formatearDecimal($compraPorPagarMontoPagado);
            $this->compraPorPagarFechaPago = $compraPorPagarFechaPago;
            $this->compraPorPagarEstadoCompra = strtoupper($compraPorPagarEstadoCompra);
            $this->compraPorPagarNotas = $compraPorPagarNotas;
            $this->compraPorPagarEstado = $compraPorPagarEstado;
        }

        public function getCompraPorPagarID(): int { return $this->compraPorPagarID; }
        public function getCompraPorPagarCompraDetalle(): CompraDetalle { return $this->compraPorPagarCompraDetalle; }
        public function getCompraPorPagarFechaVencimiento() { return $this->compraPorPagarFechaVencimiento; }
        public function getCompraPorPagarMontoTotal(): float { return $this->compraPorPagarMontoTotal; }
        public function getCompraPorPagarMontoPagado(): float { return $this->compraPorPagarMontoPagado; }
        public function getCompraPorPagarFechaPago() { return $this->compraPorPagarFechaPago; }
        public function getCompraPorPagarEstadoCompra(): string { return $this->compraPorPagarEstadoCompra; }
        public function getCompraPorPagarNotas(): string { return $this->compraPorPagarNotas; }
        public function getCompraPorPagarEstado(): bool { return $this->compraPorPagarEstado; }

        public function setCompraPorPagarID(int $compraPorPagarID) { $this->compraPorPagarID = $compraPorPagarID; }
        public function setCompraPorPagarCompraDetalle(CompraDetalle $compraPorPagarCompraDetalle) { $this->compraPorPagarCompraDetalle = $compraPorPagarCompraDetalle; }
        public function setCompraPorPagarFechaVencimiento($compraPorPagarFechaVencimiento) { $this->compraPorPagarFechaVencimiento = $compraPorPagarFechaVencimiento; }
        public function setCompraPorPagarMontoTotal(float $compraPorPagarMontoTotal) { $this->compraPorPagarMontoTotal = $compraPorPagarMontoTotal; }
        public function setCompraPorPagarMontoPagado(float $compraPorPagarMontoPagado) { $this->compraPorPagarMontoPagado = $compraPorPagarMontoPagado; }
        public function setCompraPorPagarFechaPago($compraPorPagarFechaPago) { $this->compraPorPagarFechaPago = $compraPorPagarFechaPago; }
        public function setCompraPorPagarEstadoCompra(string $compraPorPagarEstadoCompra) { $this->compraPorPagarEstadoCompra = $compraPorPagarEstadoCompra; }
        public function setCompraPorPagarNotas(string $compraPorPagarNotas) { $this->compraPorPagarNotas = $compraPorPagarNotas; }
        public function setCompraPorPagarEstado(bool $compraPorPagarEstado) { $this->compraPorPagarEstado = $compraPorPagarEstado; }

        public function jsonSerialize() {
            return [
                'ID' => $this->compraPorPagarID,
                'CompraDetalle' => $this->compraPorPagarCompraDetalle,
                'MontoTotal' => $this->compraPorPagarMontoTotal,
                'MontoPagado' => $this->compraPorPagarMontoPagado,
                'EstadoCompra' => $this->compraPorPagarEstadoCompra,
                'FechaPago' => $this->compraPorPagarFechaPago ? Utils::formatearFecha($this->compraPorPagarFechaPago) : '',
                'FechaPagoISO' => $this->compraPorPagarFechaPago ? Utils::formatearFecha($this->compraPorPagarFechaPago, 'Y-MM-dd') : '',
                'Vencimiento' => $this->compraPorPagarFechaVencimiento ? Utils::formatearFecha($this->compraPorPagarFechaVencimiento) : '',
                'VencimientoISO' => $this->compraPorPagarFechaVencimiento ? Utils::formatearFecha($this->compraPorPagarFechaVencimiento, 'Y-MM-dd') : '',
                'Notas' => $this->compraPorPagarNotas,
                'Estado' => $this->compraPorPagarEstado
            ];
        }

    }

?>