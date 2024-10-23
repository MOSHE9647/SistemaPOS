<?php

	require_once dirname(__DIR__, 1) . '/domain/Producto.php';
	require_once dirname(__DIR__, 1) . '/domain/Compra.php';
	require_once dirname(__DIR__, 1) . '/utils/Utils.php';

	class CompraDetalle implements JsonSerializable {

		private $compraDetalleID;
		private $compraDetalleCompra;
		private $compraDetalleProducto;
		private $compraDetallePrecio;
		private $compraDetalleCantidad;
		private $compraDetalleEstado;

		public function __construct(int $compraDetalleID = -1, float $compraDetallePrecio = 0.0, int $compraDetalleCantidad = 0,
			Compra $compraDetalleCompra = null, Producto $compraDetalleProducto = null, bool $compraDetalleEstado = true) 
		{
			$this->compraDetalleID = $compraDetalleID;
			$this->compraDetalleCompra = $compraDetalleCompra;
			$this->compraDetalleProducto = $compraDetalleProducto;
			$this->compraDetallePrecio = Utils::formatearDecimal($compraDetallePrecio);
			$this->compraDetalleCantidad = $compraDetalleCantidad;
			$this->compraDetalleEstado = $compraDetalleEstado;
		}

		public function getCompraDetalleID(): int { return $this->compraDetalleID; }
		public function getCompraDetalleCompra(): ?Compra { return $this->compraDetalleCompra; }
		public function getCompraDetalleProducto(): ?Producto { return $this->compraDetalleProducto; }
		public function getCompraDetallePrecio(): float { return $this->compraDetallePrecio; }
		public function getCompraDetalleCantidad(): int { return $this->compraDetalleCantidad; }
		public function getCompraDetalleEstado(): bool { return $this->compraDetalleEstado; }

		public function setCompraDetalleID(int $compraDetalleID) { $this->compraDetalleID = $compraDetalleID; }
		public function setCompraDetalleCompra(Compra $compraDetalleCompra) { $this->compraDetalleCompra = $compraDetalleCompra; }
		public function setCompraDetalleProducto(Producto $compraDetalleProducto) { $this->compraDetalleProducto = $compraDetalleProducto; }
		public function setCompraDetallePrecio(float $compraDetallePrecio) { $this->compraDetallePrecio = Utils::formatearDecimal($compraDetallePrecio); }
		public function setCompraDetalleCantidad(int $compraDetalleCantidad) { $this->compraDetalleCantidad = $compraDetalleCantidad; }
		public function setCompraDetalleEstado(bool $compraDetalleEstado) { $this->compraDetalleEstado = $compraDetalleEstado; }

		public function jsonSerialize() {
			return [
				'ID' => $this->compraDetalleID,
				'Compra' => $this->compraDetalleCompra ? $this->compraDetalleCompra : null,
				'Producto' => $this->compraDetalleProducto ? $this->compraDetalleProducto : null,
				'Precio' => $this->compraDetallePrecio,
				'Cantidad' => $this->compraDetalleCantidad,
                'Estado' => $this->compraDetalleEstado
			];
		}

	}

?>