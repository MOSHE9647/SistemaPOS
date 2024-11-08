<?php

    require_once dirname(__DIR__, 1) . '/domain/Cliente.php';
    require_once dirname(__DIR__, 1) . '/domain/Usuario.php';
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';

    class Venta implements JsonSerializable {

        private $ventaID;
        private $ventaCliente;
        private $ventaUsuario;
        private $ventaNumeroFactura;
        private $ventaMoneda;
        private $ventaMontoBruto;
        private $ventaMontoNeto;
        private $ventaMontoImpuesto;
        private $ventaCondicionVenta;
        private $ventaTipoPago;
        private $ventaTipoCambio;
        private $ventaMontoPago;
        private $ventaMontoVuelto;
        private $ventaReferenciaTarjeta;
        private $ventaComprobanteSinpe;
        private $ventaFechaCreacion;
        private $ventaFechaModificacion;
        private $ventaEstado;

        public function __construct(
            int $ventaID = -1,
            Cliente $ventaCliente = null,
            Usuario $ventaUsuario = null,
            string $ventaNumeroFactura = "",
            string $ventaMoneda = "CRC",
            float $ventaMontoBruto = 0.0,
            float $ventaMontoNeto = 0.0,
            float $ventaMontoImpuesto = 0.0,
            string $ventaCondicionVenta = "CONTADO",
            string $ventaTipoPago = "EFECTIVO",
            float $ventaTipoCambio = 0.0,
            float $ventaMontoPago = 0.0,
            float $ventaMontoVuelto = 0.0,
            $ventaReferenciaTarjeta = "",
            $ventaComprobanteSinpe = "",
            $ventaFechaCreacion = "",
            $ventaFechaModificacion = "",
            bool $ventaEstado = true
        ) {
            $this->ventaID = $ventaID;
            $this->ventaCliente = $ventaCliente;
            $this->ventaUsuario = $ventaUsuario;
            $this->ventaNumeroFactura = $ventaNumeroFactura;
            $this->ventaMoneda = strtoupper($ventaMoneda);
            $this->ventaMontoBruto = Utils::formatearDecimal($ventaMontoBruto);
            $this->ventaMontoNeto = Utils::formatearDecimal($ventaMontoNeto);
            $this->ventaMontoImpuesto = Utils::formatearDecimal($ventaMontoImpuesto);
            $this->ventaCondicionVenta = strtoupper($ventaCondicionVenta);
            $this->ventaTipoPago = strtoupper($ventaTipoPago);
            $this->ventaTipoCambio = Utils::formatearDecimal($ventaTipoCambio);
            $this->ventaMontoPago = Utils::formatearDecimal($ventaMontoPago);
            $this->ventaMontoVuelto = Utils::formatearDecimal($ventaMontoVuelto);
            $this->ventaReferenciaTarjeta = strtoupper($ventaReferenciaTarjeta);
            $this->ventaComprobanteSinpe = strtoupper($ventaComprobanteSinpe);
            $this->ventaFechaCreacion = $ventaFechaCreacion;
            $this->ventaFechaModificacion = $ventaFechaModificacion;
            $this->ventaEstado = $ventaEstado;
        }
        
        public function getVentaID(): int { return $this->ventaID; }
        public function getVentaCliente(): ?Cliente { return $this->ventaCliente; }
        public function getVentaUsuario(): ?Usuario { return $this->ventaUsuario; }
        public function getVentaNumeroFactura(): string { return $this->ventaNumeroFactura; }
        public function getVentaMoneda(): string { return $this->ventaMoneda; }
        public function getVentaMontoBruto(): float { return $this->ventaMontoBruto; }
        public function getVentaMontoNeto(): float { return $this->ventaMontoNeto; }
        public function getVentaMontoImpuesto(): float { return $this->ventaMontoImpuesto; }
        public function getVentaCondicionVenta(): string { return $this->ventaCondicionVenta; }
        public function getVentaTipoPago(): string { return $this->ventaTipoPago; }
        public function getVentaTipoCambio(): float { return $this->ventaTipoCambio; }
        public function getVentaMontoPago(): float { return $this->ventaMontoPago; }
        public function getVentaMontoVuelto(): float { return $this->ventaMontoVuelto; }
        public function getVentaReferenciaTarjeta() { return $this->ventaReferenciaTarjeta; }
        public function getVentaComprobanteSinpe() { return $this->ventaComprobanteSinpe; }
        public function getVentaFechaCreacion() { return $this->ventaFechaCreacion; }
        public function getVentaFechaModificacion() { return $this->ventaFechaModificacion; }
        public function getVentaEstado(): bool { return $this->ventaEstado; }

        public function setVentaID(int $ventaID) { $this->ventaID = $ventaID; }
        public function setVentaCliente(Cliente $ventaCliente) { $this->ventaCliente = $ventaCliente; }
        public function setVentaUsuario(Usuario $ventaUsuario) { $this->ventaUsuario = $ventaUsuario; }
        public function setVentaNumeroFactura(string $ventaNumeroFactura) { $this->ventaNumeroFactura = $ventaNumeroFactura; }
        public function setVentaMoneda(string $ventaMoneda) { $this->ventaMoneda = strtoupper($ventaMoneda); }
        public function setVentaMontoBruto(float $ventaMontoBruto) { $this->ventaMontoBruto = Utils::formatearDecimal($ventaMontoBruto); }
        public function setVentaMontoNeto(float $ventaMontoNeto) { $this->ventaMontoNeto = Utils::formatearDecimal($ventaMontoNeto); }
        public function setVentaMontoImpuesto(float $ventaMontoImpuesto) { $this->ventaMontoImpuesto = Utils::formatearDecimal($ventaMontoImpuesto); }
        public function setVentaCondicionVenta(string $ventaCondicionVenta) { $this->ventaCondicionVenta = strtoupper($ventaCondicionVenta); }
        public function setVentaTipoPago(string $ventaTipoPago) { $this->ventaTipoPago = strtoupper($ventaTipoPago); }
        public function setVentaTipoCambio(float $ventaTipoCambio) { $this->ventaTipoCambio = Utils::formatearDecimal($ventaTipoCambio); }
        public function setVentaMontoPago(float $ventaMontoPago) { $this->ventaMontoPago = Utils::formatearDecimal($ventaMontoPago); }
        public function setVentaMontoVuelto(float $ventaMontoVuelto) {  $this->ventaMontoVuelto = Utils::formatearDecimal($ventaMontoVuelto); }
        public function setVentaReferenciaTarjeta($ventaReferenciaTarjeta) {  $this->ventaReferenciaTarjeta = strtoupper($ventaReferenciaTarjeta); }
        public function setVentaComprobanteSinpe($ventaComprobanteSinpe) {  $this->ventaComprobanteSinpe = strtoupper($ventaComprobanteSinpe); }
        public function setVentaFechaCreacion($ventaFechaCreacion) { $this->ventaFechaCreacion = $ventaFechaCreacion; }
        public function setVentaFechaModificacion($ventaFechaModificacion) { $this->ventaFechaModificacion = $ventaFechaModificacion; }
        public function setVentaEstado(bool $ventaEstado) { $this->ventaEstado = $ventaEstado; }

        public function getClienteID(): ?int {
            return $this->ventaCliente ? $this->ventaCliente->getClienteID() : null;
        }

        public function getUsuarioID(): ?int {
            return $this->ventaUsuario ? $this->ventaUsuario->getUsuarioID() : null;
        }

        public static function fromArray(array $venta): Venta {
            return new Venta(
                intval($venta['ID']) ?? -1,
                Utils::convertToObject($venta['Cliente'] ?? null, Cliente::class),
                Utils::convertToObject($venta['Usuario'] ?? null, Usuario::class),
                $venta['NumeroFactura'] ?? "",
                $venta['Moneda'] ?? "CRC",
                floatval($venta['MontoBruto']) ?? 0.0,
                floatval($venta['MontoNeto']) ?? 0.0,
                floatval($venta['MontoImpuesto']) ?? 0.0,
                $venta['Condicion'] ?? "CONTADO",
                $venta['TipoPago'] ?? "EFECTIVO",
                floatval($venta['TipoCambio']) ?? 0.0,
                floatval($venta['MontoPago']) ?? 0.0,
                floatval($venta['MontoVuelto']) ?? 0.0,
                $venta['ReferenciaTarjeta'] ?? "",
                $venta['ComprobanteSINPE'] ?? "",
                $venta['Creacion'] ?? "",
                $venta['Modificacion'] ?? "",
                $venta['Estado'] ?? true
            );
        }

        public function jsonSerialize() {
            return [
                'ID' => $this->ventaID,
                'NumeroFactura' => $this->ventaNumeroFactura,
                'Moneda' => $this->ventaMoneda,
                'MontoBruto' => $this->ventaMontoBruto,
                'MontoNeto' => $this->ventaMontoNeto,
                'MontoImpuesto' => $this->ventaMontoImpuesto,
                'Condicion' => $this->ventaCondicionVenta,
                'TipoPago' => $this->ventaTipoPago,
                'TipoCambio' => $this->ventaTipoCambio,
                'MontoPago' => $this->ventaMontoPago,
                'MontoVuelto' => $this->ventaMontoVuelto,
                'ReferenciaTarjeta' => $this->ventaReferenciaTarjeta,
                'ComprobanteSinpe' => $this->ventaComprobanteSinpe,
                'Cliente' => [
                    'ID' => $this->ventaCliente ? $this->ventaCliente->getClienteID() : null,
                    'Nombre' => $this->ventaCliente ? $this->ventaCliente->getClienteNombre() : null,
                    'Alias' => $this->ventaCliente ? $this->ventaCliente->getClienteAlias() : null
                ],
                'Usuario' => [
                    'ID' => $this->ventaUsuario ? $this->ventaUsuario->getUsuarioID() : null,
                    'Nombre' => $this->ventaUsuario ? $this->ventaUsuario->getUsuarioNombre() : null,
                    'Apellido1' => $this->ventaUsuario ? $this->ventaUsuario->getUsuarioApellido1() : null,
                    'Rol' => $this->ventaUsuario ? $this->ventaUsuario->getUsuarioRolUsuario()->getRolNombre() : null
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