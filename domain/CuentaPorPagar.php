<?php

    require_once dirname(__DIR__, 1) . "/domain/CompraDetalle.php";
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';

    class CuentaPorPagar implements JsonSerializable {

        private $cuentaPorPagarID;
        private $cuentaPorPagarCompraDetalle;
        private $cuentaPorPagarFechaVencimiento;
        private $cuentaPorPagarMontoTotal;
        private $cuentaPorPagarMontoPagado;
        private $cuentaPorPagarFechaPago;
        private $cuentaPorPagarEstadoCuenta;
        private $cuentaPorPagarNotas;
        private $cuentaPorPagarEstado;

        public function __construct(int $cuentaPorPagarID = -1, CompraDetalle $cuentaPorPagarCompraDetalle = null, 
            $cuentaPorPagarFechaVencimiento = "", float $cuentaPorPagarMontoTotal = 0.0, float $cuentaPorPagarMontoPagado = 0.0, 
            $cuentaPorPagarFechaPago = "", string $cuentaPorPagarEstadoCuenta = "", string $cuentaPorPagarNotas = "", 
            bool $cuentaPorPagarEstado = true) 
        {
            $this->cuentaPorPagarID = $cuentaPorPagarID;
            $this->cuentaPorPagarCompraDetalle = $cuentaPorPagarCompraDetalle;
            $this->cuentaPorPagarFechaVencimiento = $cuentaPorPagarFechaVencimiento;
            $this->cuentaPorPagarMontoTotal = Utils::formatearDecimal($cuentaPorPagarMontoTotal);
            $this->cuentaPorPagarMontoPagado = Utils::formatearDecimal($cuentaPorPagarMontoPagado);
            $this->cuentaPorPagarFechaPago = $cuentaPorPagarFechaPago;
            $this->cuentaPorPagarEstadoCuenta = strtoupper($cuentaPorPagarEstadoCuenta);
            $this->cuentaPorPagarNotas = $cuentaPorPagarNotas;
            $this->cuentaPorPagarEstado = $cuentaPorPagarEstado;
        }

        public function getCuentaPorPagarID(): int { return $this->cuentaPorPagarID; }
        public function getCuentaPorPagarCompraDetalle(): CompraDetalle { return $this->cuentaPorPagarCompraDetalle; }
        public function getCuentaPorPagarFechaVencimiento() { return $this->cuentaPorPagarFechaVencimiento; }
        public function getCuentaPorPagarMontoTotal(): float { return $this->cuentaPorPagarMontoTotal; }
        public function getCuentaPorPagarMontoPagado(): float { return $this->cuentaPorPagarMontoPagado; }
        public function getCuentaPorPagarFechaPago() { return $this->cuentaPorPagarFechaPago; }
        public function getCuentaPorPagarEstadoCuenta(): string { return $this->cuentaPorPagarEstadoCuenta; }
        public function getCuentaPorPagarNotas(): string { return $this->cuentaPorPagarNotas; }
        public function getCuentaPorPagarEstado(): bool { return $this->cuentaPorPagarEstado; }

        public function setCuentaPorPagarID(int $cuentaPorPagarID) { $this->cuentaPorPagarID = $cuentaPorPagarID; }
        public function setCuentaPorPagarCompraDetalle(CompraDetalle $cuentaPorPagarCompraDetalle) { $this->cuentaPorPagarCompraDetalle = $cuentaPorPagarCompraDetalle; }
        public function setCuentaPorPagarFechaVencimiento($cuentaPorPagarFechaVencimiento) { $this->cuentaPorPagarFechaVencimiento = $cuentaPorPagarFechaVencimiento; }
        public function setCuentaPorPagarMontoTotal(float $cuentaPorPagarMontoTotal) { $this->cuentaPorPagarMontoTotal = $cuentaPorPagarMontoTotal; }
        public function setCuentaPorPagarMontoPagado(float $cuentaPorPagarMontoPagado) { $this->cuentaPorPagarMontoPagado = $cuentaPorPagarMontoPagado; }
        public function setCuentaPorPagarFechaPago($cuentaPorPagarFechaPago) { $this->cuentaPorPagarFechaPago = $cuentaPorPagarFechaPago; }
        public function setCuentaPorPagarEstadoCuenta(string $cuentaPorPagarEstadoCuenta) { $this->cuentaPorPagarEstadoCuenta = $cuentaPorPagarEstadoCuenta; }
        public function setCuentaPorPagarNotas(string $cuentaPorPagarNotas) { $this->cuentaPorPagarNotas = $cuentaPorPagarNotas; }
        public function setCuentaPorPagarEstado(bool $cuentaPorPagarEstado) { $this->cuentaPorPagarEstado = $cuentaPorPagarEstado; }

        public function jsonSerialize() {
            return [
                'ID' => $this->cuentaPorPagarID,
                'CompraDetalle' => $this->cuentaPorPagarCompraDetalle,
                'MontoTotal' => $this->cuentaPorPagarMontoTotal,
                'MontoPagado' => $this->cuentaPorPagarMontoPagado,
                'EstadoCuenta' => $this->cuentaPorPagarEstadoCuenta,
                'FechaPago' => $this->cuentaPorPagarFechaPago ? Utils::formatearFecha($this->cuentaPorPagarFechaPago) : '',
                'FechaPagoISO' => $this->cuentaPorPagarFechaPago ? Utils::formatearFecha($this->cuentaPorPagarFechaPago, 'Y-MM-dd') : '',
                'Vencimiento' => $this->cuentaPorPagarFechaVencimiento ? Utils::formatearFecha($this->cuentaPorPagarFechaVencimiento) : '',
                'VencimientoISO' => $this->cuentaPorPagarFechaVencimiento ? Utils::formatearFecha($this->cuentaPorPagarFechaVencimiento, 'Y-MM-dd') : '',
                'Notas' => $this->cuentaPorPagarNotas,
                'Estado' => $this->cuentaPorPagarEstado
            ];
        }

    }

?>