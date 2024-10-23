<?php

    require_once dirname(__DIR__, 1) . '/domain/Cliente.php';
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';

    class Venta implements JsonSerializable {

        private $ventaID;
        private $ventaCliente;
        private $ventaNumeroFactura;
        private $ventaMoneda;
        private $ventaMontoBruto;
        private $ventaMontoNeto;
        private $ventaMontoImpuesto;
        private $ventaCondicionVenta;
        private $ventaTipoPago;
        private $ventaFechaCreacion;
        private $ventaFechaModificacion;
        private $ventaEstado;

        public function __construct(int $ventaID = -1, string $ventaNumeroFactura = "", float $ventaMontoBruto = 0.0, float $ventaMontoNeto = 0.0, 
            float $ventaMontoImpuesto = 0.0, string $ventaMoneda = "CRC", string $ventaCondicionVenta = "Contado", string $ventaTipoPago = "Efectivo", 
            Cliente $ventaCliente = null, $ventaFechaCreacion = "", $ventaFechaModificacion = "", bool $ventaEstado = true)
        {
            $this->ventaID = $ventaID;
            $this->ventaCliente = $ventaCliente;
            $this->ventaNumeroFactura = strtoupper($ventaNumeroFactura);
            $this->ventaMoneda = strtoupper($ventaMoneda);
            $this->ventaMontoBruto = Utils::formatearDecimal($ventaMontoBruto);
            $this->ventaMontoNeto = Utils::formatearDecimal($ventaMontoNeto);
            $this->ventaMontoImpuesto = Utils::formatearDecimal($ventaMontoImpuesto);
            $this->ventaCondicionVenta = strtoupper($ventaCondicionVenta);
            $this->ventaTipoPago = strtoupper($ventaTipoPago);
            $this->ventaFechaCreacion = $ventaFechaCreacion;
            $this->ventaFechaModificacion = $ventaFechaModificacion;
            $this->ventaEstado = $ventaEstado;
        }

        public function getVentaID(): int { return $this->ventaID; }
        public function getVentaCliente(): ?Cliente { return $this->ventaCliente; }
        public function getVentaNumeroFactura(): string { return $this->ventaNumeroFactura; }
        public function getVentaMoneda(): string { return $this->ventaMoneda; }
        public function getVentaMontoBruto(): float { return $this->ventaMontoBruto; }
        public function getVentaMontoNeto(): float { return $this->ventaMontoNeto; }
        public function getVentaMontoImpuesto(): float { return $this->ventaMontoImpuesto; }
        public function getVentaCondicionVenta(): string { return $this->ventaCondicionVenta; }
        public function getVentaTipoPago(): string { return $this->ventaTipoPago; }
        public function getVentaFechaCreacion() { return $this->ventaFechaCreacion; }
        public function getVentaFechaModificacion() { return $this->ventaFechaModificacion; }
        public function getVentaEstado(): bool { return $this->ventaEstado; }

        public function setVentaID(int $ventaID) { $this->ventaID = $ventaID; }
        public function setVentaCliente(Cliente $ventaCliente) { $this->ventaCliente = $ventaCliente; }
        public function setVentaNumeroFactura(string $ventaNumeroFactura) { $this->ventaNumeroFactura = strtoupper($ventaNumeroFactura); }
        public function setVentaMoneda(string $ventaMoneda) { $this->ventaMoneda = strtoupper($ventaMoneda); }
        public function setVentaMontoBruto(float $ventaMontoBruto) { $this->ventaMontoBruto = Utils::formatearDecimal($ventaMontoBruto); }
        public function setVentaMontoNeto(float $ventaMontoNeto) { $this->ventaMontoNeto = Utils::formatearDecimal($ventaMontoNeto); }
        public function setVentaMontoImpuesto(float $ventaMontoImpuesto) { $this->ventaMontoImpuesto = Utils::formatearDecimal($ventaMontoImpuesto); }
        public function setVentaCondicionVenta(string $ventaCondicionVenta) { $this->ventaCondicionVenta = strtoupper($ventaCondicionVenta); }
        public function setVentaTipoPago(string $ventaTipoPago) { $this->ventaTipoPago = strtoupper($ventaTipoPago); }
        public function setVentaFechaCreacion($ventaFechaCreacion) { $this->ventaFechaCreacion = $ventaFechaCreacion; }
        public function setVentaFechaModificacion($ventaFechaModificacion) { $this->ventaFechaModificacion = $ventaFechaModificacion; }
        public function setVentaEstado(bool $ventaEstado) { $this->ventaEstado = $ventaEstado; }

        public function jsonSerialize() {
            return [
                'ID' => $this->ventaID,
                'NumeroFactura' => $this->ventaNumeroFactura,
                'Moneda' => $this->ventaMoneda,
                'MontoBruto' => $this->ventaMontoBruto,
                'MontoNeto' => $this->ventaMontoNeto,
                'MontoImpuesto' => $this->ventaMontoImpuesto,
                'CondicionVenta' => $this->ventaCondicionVenta,
                'TipoPago' => $this->ventaTipoPago,
                'Cliente' => [
                    'ID' => $this->ventaCliente ? $this->ventaCliente->getClienteID() : null,
                    'Nombre' => $this->ventaCliente ? $this->ventaCliente->getClienteNombre() : null,
                    'Alias' => $this->ventaCliente ? $this->ventaCliente->getClienteAlias() : null
                ],
                'Creacion' => $this->ventaFechaCreacion ? Utils::formatearFecha($this->ventaFechaCreacion) : '',
                'Modificacion' => $this->ventaFechaModificacion ? Utils::formatearFecha($this->ventaFechaModificacion) : '',
                'CreacionISO' => $this->ventaFechaCreacion ? Utils::formatearFecha($this->ventaFechaCreacion, 'Y-MM-dd') : '',
                'ModificacionISO' => $this->ventaFechaModificacion ? Utils::formatearFecha($this->ventaFechaModificacion, 'Y-MM-dd') : '',
                'Estado' => $this->ventaEstado
            ];
        }

    }

?>