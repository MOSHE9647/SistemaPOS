<?php

require_once dirname(__DIR__, 1) . "/domain/Proveedor.php";
require_once dirname(__DIR__, 1) . '/utils/Utils.php';

class Compra implements JsonSerializable {

    private $compraID;
    private $compraNumeroFactura;
    private $compraMontoBruto;
    private $compraMontoNeto;
    private $compraTipoPago;
    private $compraProveedor;
    private $compraFechaCreacion;
    private $compraFechaModificacion;
    private $compraEstado;

    public function __construct(int $compraID = -1, string $compraNumeroFactura = "", float $compraMontoBruto = 0.0, 
        float $compraMontoNeto = 0.0, string $compraTipoPago = "", Proveedor $compraProveedor = null, 
        $compraFechaCreacion = "", $compraFechaModificacion = "", bool $compraEstado = true) 
    {
        $this->compraID = $compraID;
        $this->compraNumeroFactura = strtoupper($compraNumeroFactura);
        $this->compraMontoBruto = Utils::formatearDecimal($compraMontoBruto);
        $this->compraMontoNeto = Utils::formatearDecimal($compraMontoNeto);
        $this->compraTipoPago = strtoupper($compraTipoPago);
        $this->compraProveedor = $compraProveedor;
        $this->compraFechaCreacion = $compraFechaCreacion;
        $this->compraFechaModificacion = $compraFechaModificacion;
        $this->compraEstado = $compraEstado;
    }

    public function getCompraID(): int { return $this->compraID; }
    public function getCompraNumeroFactura(): string { return $this->compraNumeroFactura; }
    public function getCompraMontoBruto(): float { return $this->compraMontoBruto; }
    public function getCompraMontoNeto(): float { return $this->compraMontoNeto; }
    public function getCompraTipoPago(): string { return $this->compraTipoPago; }
    public function getCompraProveedor(): ?Proveedor { return $this->compraProveedor; }
    public function getCompraFechaCreacion() { return $this->compraFechaCreacion; }
    public function getCompraFechaModificacion() { return $this->compraFechaModificacion; }
    public function getCompraEstado(): bool { return $this->compraEstado; }

    public function setCompraID(int $compraID) { $this->compraID = $compraID; }
    public function setCompraNumeroFactura(string $compraNumeroFactura) { $this->compraNumeroFactura = $compraNumeroFactura; }
    public function setCompraMontoBruto(float $compraMontoBruto) { $this->compraMontoBruto = $compraMontoBruto; }
    public function setCompraMontoNeto(float $compraMontoNeto) { $this->compraMontoNeto = $compraMontoNeto; }
    public function setCompraTipoPago(string $compraTipoPago) { $this->compraTipoPago = $compraTipoPago; }
    public function setCompraProveedor(Proveedor $compraProveedor) { $this->compraProveedor = $compraProveedor; }
    public function setCompraFechaCreacion($compraFechaCreacion) { $this->compraFechaCreacion = $compraFechaCreacion; }
    public function setCompraFechaModificacion($compraFechaModificacion) { $this->compraFechaModificacion = $compraFechaModificacion; }
    public function setCompraEstado(bool $compraEstado) { $this->compraEstado = $compraEstado; }

    public function getProveedorID(): ?int {
        return $this->compraProveedor ? $this->compraProveedor->getProveedorID() : null;
    }

    public function jsonSerialize() {
        return [
            'ID' => $this->compraID,
            'NumeroFactura' => $this->compraNumeroFactura,
            'MontoBruto' => $this->compraMontoBruto,
            'MontoNeto' => $this->compraMontoNeto,
            'TipoPago' => $this->compraTipoPago,
            'Proveedor' => [
                'ID' => $this->compraProveedor ? $this->compraProveedor->getProveedorID() : null,
                'Nombre' => $this->compraProveedor ? $this->compraProveedor->getProveedorNombre() : null,
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