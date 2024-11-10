<?php

    require_once dirname(__DIR__, 1) . "/domain/Proveedor.php";
    require_once dirname(__DIR__, 1) . '/domain/Cliente.php';
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';

    class Compra implements JsonSerializable {

        private $compraID;
        private $compraCliente;
        private $compraProveedor;
        private $compraNumeroFactura;
        private $compraMoneda;
        private $compraMontoBruto;
        private $compraMontoNeto;
        private $compraMontoImpuesto;
        private $compraCondicionCompra;
        private $compraTipoPago;
        private $compraFechaCreacion;
        private $compraFechaModificacion;
        private $compraEstado;

        public function __construct(
            int $compraID = -1, 
            Cliente $compraCliente = null, 
            Proveedor $compraProveedor = null, 
            string $compraNumeroFactura = "", 
            string $compraMoneda = "CRC", 
            float $compraMontoBruto = 0.0, 
            float $compraMontoNeto = 0.0, 
            float $compraMontoImpuesto = 0.0, 
            string $compraCondicionCompra = "CONTADO", 
            string $compraTipoPago = "EFECTIVO",
            $compraFechaCreacion = "", 
            $compraFechaModificacion = "", 
            bool $compraEstado = true
        )
        {
            $this->compraID = $compraID;
            $this->compraCliente = $compraCliente;
            $this->compraProveedor = $compraProveedor;
            $this->compraNumeroFactura = strtoupper($compraNumeroFactura);
            $this->compraMoneda = strtoupper($compraMoneda);
            $this->compraMontoBruto = Utils::formatearDecimal($compraMontoBruto);
            $this->compraMontoNeto = Utils::formatearDecimal($compraMontoNeto);
            $this->compraMontoImpuesto = Utils::formatearDecimal($compraMontoImpuesto);
            $this->compraCondicionCompra = strtoupper($compraCondicionCompra);
            $this->compraTipoPago = strtoupper($compraTipoPago);
            $this->compraFechaCreacion = $compraFechaCreacion;
            $this->compraFechaModificacion = $compraFechaModificacion;
            $this->compraEstado = $compraEstado;
        }

        public function getCompraID(): int { return $this->compraID; }
        public function getCompraCliente(): ?Cliente { return $this->compraCliente; }
        public function getCompraProveedor(): ?Proveedor { return $this->compraProveedor; }
        public function getCompraNumeroFactura(): string { return $this->compraNumeroFactura; }
        public function getCompraMoneda(): string { return $this->compraMoneda; }
        public function getCompraMontoBruto(): float { return $this->compraMontoBruto; }
        public function getCompraMontoNeto(): float { return $this->compraMontoNeto; }
        public function getCompraMontoImpuesto(): float { return $this->compraMontoImpuesto; }
        public function getCompraCondicionCompra(): string { return $this->compraCondicionCompra; }
        public function getCompraTipoPago(): string { return $this->compraTipoPago; }
        public function getCompraFechaCreacion() { return $this->compraFechaCreacion; }
        public function getCompraFechaModificacion() { return $this->compraFechaModificacion; }
        public function getCompraEstado(): bool { return $this->compraEstado; }

        public function setCompraID(int $compraID) { $this->compraID = $compraID; }
        public function setCompraCliente(Cliente $compraCliente) { $this->compraCliente = $compraCliente; }
        public function setCompraProveedor(Proveedor $compraProveedor) { $this->compraProveedor = $compraProveedor; }
        public function setCompraNumeroFactura(string $compraNumeroFactura) { $this->compraNumeroFactura = strtoupper($compraNumeroFactura); }
        public function setCompraMoneda(string $compraMoneda) { $this->compraMoneda = strtoupper($compraMoneda); }
        public function setCompraMontoBruto(float $compraMontoBruto) { $this->compraMontoBruto = Utils::formatearDecimal($compraMontoBruto); }
        public function setCompraMontoNeto(float $compraMontoNeto) { $this->compraMontoNeto = Utils::formatearDecimal($compraMontoNeto); }
        public function setCompraMontoImpuesto(float $compraMontoImpuesto) { $this->compraMontoImpuesto = Utils::formatearDecimal($compraMontoImpuesto); }
        public function setCompraCondicionCompra(string $compraCondicionCompra) { $this->compraCondicionCompra = strtoupper($compraCondicionCompra); }
        public function setCompraTipoPago(string $compraTipoPago) { $this->compraTipoPago = strtoupper($compraTipoPago); }
        public function setCompraFechaCreacion($compraFechaCreacion) { $this->compraFechaCreacion = $compraFechaCreacion; }
        public function setCompraFechaModificacion($compraFechaModificacion) { $this->compraFechaModificacion = $compraFechaModificacion; }
        public function setCompraEstado(bool $compraEstado) { $this->compraEstado = $compraEstado; }

        public function getProveedorID(): ?int {
            return $this->compraProveedor ? $this->compraProveedor->getProveedorID() : null;
        }

        public function getClienteID(): ?int {
            return $this->compraCliente ? $this->compraCliente->getClienteID() : null;
        }

        public function fromArray(array $compra): Compra {
            return new Compra(
                intval($compra['ID'] ?? -1),
                Utils::convertToObject($compra['Cliente'] ?? null, Cliente::class),
                Utils::convertToObject($compra['Proveedor'] ?? null, Proveedor::class),
                $compra['NumeroFactura'] ?? '',
                $compra['Moneda'] ?? 'CRC',
                floatval($compra['MontoBruto'] ?? 0.0),
                floatval($compra['MontoNeto'] ?? 0.0),
                floatval($compra['MontoImpuesto'] ?? 0.0),
                $compra['Condicion'] ?? 'CONTADO',
                $compra['TipoPago'] ?? 'EFECTIVO',
                $compra['Creacion'] ?? '',
                $compra['Modificacion'] ?? '',
                $compra['Estado'] ?? true
            );
        }

        public function jsonSerialize() {
            return [
                'ID' => $this->compraID,
                'NumeroFactura' => $this->compraNumeroFactura,
                'Moneda' => $this->compraMoneda,
                'MontoBruto' => $this->compraMontoBruto,
                'MontoNeto' => $this->compraMontoNeto,
                'MontoImpuesto' => $this->compraMontoImpuesto,
                'CondicionCompra' => $this->compraCondicionCompra,
                'TipoPago' => $this->compraTipoPago,
                'Proveedor' => [
                    'ID' => $this->compraProveedor ? $this->compraProveedor->getProveedorID() : null,
                    'Nombre' => $this->compraProveedor ? $this->compraProveedor->getProveedorNombre() : null,
                    'Categoria' => $this->compraProveedor ? $this->compraProveedor->getProveedorCategoria() : null
                ],
                'Cliente' => [
                    'ID' => $this->compraCliente ? $this->compraCliente->getClienteID() : null,
                    'Nombre' => $this->compraCliente ? $this->compraCliente->getClienteNombre() : null,
                    'Alias' => $this->compraCliente ? $this->compraCliente->getClienteAlias() : null
                ],
                'Creacion' => $this->compraFechaCreacion ? Utils::formatearFecha($this->compraFechaCreacion) : '',
                'Modificacion' => $this->compraFechaModificacion ? Utils::formatearFecha($this->compraFechaModificacion) : '',
                'CreacionISO' => $this->compraFechaCreacion ? Utils::formatearFecha($this->compraFechaCreacion, 'Y-MM-dd') : '',
                'ModificacionISO' => $this->compraFechaModificacion ? Utils::formatearFecha($this->compraFechaModificacion, 'Y-MM-dd') : '',
                'Estado' => $this->compraEstado
            ];
        }

    }

?>