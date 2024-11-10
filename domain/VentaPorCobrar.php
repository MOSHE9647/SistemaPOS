<?php

    require_once dirname(__DIR__, 1) . "/domain/Venta.php";
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';

    class VentaPorCobrar implements JsonSerializable {

        private $ventaPorCobrarID;
        private $ventaPorCobrarVenta;
        private $ventaPorCobrarFechaVencimiento;
        private $ventaPorCobrarCancelado;
        private $ventaPorCobrarNotas;
        private $ventaPorCobrarEstado;

        public function __construct(int $ventaPorCobrarID = -1, Venta $ventaPorCobrarVenta = null, $ventaPorCobrarFechaVencimiento = "", 
            bool $ventaPorCobrarCancelado = false, string $ventaPorCobrarNotas = "", bool $ventaPorCobrarEstado = true) 
        {
            $this->ventaPorCobrarID = $ventaPorCobrarID;
            $this->ventaPorCobrarVenta = $ventaPorCobrarVenta;
            $this->ventaPorCobrarFechaVencimiento = $ventaPorCobrarFechaVencimiento;
            $this->ventaPorCobrarCancelado = $ventaPorCobrarCancelado;
            $this->ventaPorCobrarNotas = $ventaPorCobrarNotas;
            $this->ventaPorCobrarEstado = $ventaPorCobrarEstado;
        }

        public function getVentaPorCobrarID(): int { return $this->ventaPorCobrarID; }
        public function getVentaPorCobrarVenta(): ?Venta { return $this->ventaPorCobrarVenta; }
        public function getVentaPorCobrarFechaVencimiento() { return $this->ventaPorCobrarFechaVencimiento; }
        public function getVentaPorCobrarCancelado(): bool { return $this->ventaPorCobrarCancelado; }
        public function getVentaPorCobrarNotas(): string { return $this->ventaPorCobrarNotas; }
        public function getVentaPorCobrarEstado(): bool { return $this->ventaPorCobrarEstado; }

        public function setVentaPorCobrarID(int $ventaPorCobrarID) { $this->ventaPorCobrarID = $ventaPorCobrarID; }
        public function setVentaPorCobrarVenta(Venta $ventaPorCobrarVenta) { $this->ventaPorCobrarVenta = $ventaPorCobrarVenta; }
        public function setVentaPorCobrarFechaVencimiento($ventaPorCobrarFechaVencimiento) { $this->ventaPorCobrarFechaVencimiento = $ventaPorCobrarFechaVencimiento; }
        public function setVentaPorCobrarCancelado(bool $ventaPorCobrarCancelado) { $this->ventaPorCobrarCancelado = $ventaPorCobrarCancelado; }
        public function setVentaPorCobrarNotas(string $ventaPorCobrarNotas) { $this->ventaPorCobrarNotas = $ventaPorCobrarNotas; }
        public function setVentaPorCobrarEstado(bool $ventaPorCobrarEstado) { $this->ventaPorCobrarEstado = $ventaPorCobrarEstado; }

        public static function fromArray(array $ventaPorCobrar): VentaPorCobrar {
            return new VentaPorCobrar(
                intval($ventaPorCobrar['ID'] ?? -1),
                Utils::convertToObject($ventaPorCobrar['Venta'] ?? null, Venta::class),
                $ventaPorCobrar['Vencimiento'] ?? "",
                $ventaPorCobrar['Cancelado'] ?? false,
                $ventaPorCobrar['Notas'] ?? "",
                $ventaPorCobrar['Estado'] ?? true
            );
        }

        public function jsonSerialize() {
            return [
                'ID' => $this->ventaPorCobrarID,
                'Venta' => $this->ventaPorCobrarVenta,
                'Vencimiento' => $this->ventaPorCobrarFechaVencimiento ? Utils::formatearFecha($this->ventaPorCobrarFechaVencimiento) : '',
                'VencimientoISO' => $this->ventaPorCobrarFechaVencimiento ? Utils::formatearFecha($this->ventaPorCobrarFechaVencimiento, 'Y-MM-dd') : '',
                'Cancelado' => $this->ventaPorCobrarCancelado,
                'Notas' => $this->ventaPorCobrarNotas,
                'Estado' => $this->ventaPorCobrarEstado
            ];
        }

    }

?>