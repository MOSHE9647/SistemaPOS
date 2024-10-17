<?php

	require_once dirname(__DIR__, 1) . '/domain/Producto.php';
	require_once dirname(__DIR__, 1) . '/domain/Compra.php';
	require_once dirname(__DIR__, 1) . '/utils/Utils.php';

	class CompraDetalle implements JsonSerializable {

		private $compraDetalleID;
		private $compraDetalleCompra;
		private $compraDetalleProducto;
		private $compraDetalleFechaCreacion;
		private $compraDetalleFechaModificacion;
		private $compraDetalleEstado;

		public function __construct(int $compraDetalleID = -1, Compra $compraDetalleCompra = null, Producto $compraDetalleProducto = null, 
			$compraDetalleFechaCreacion = "", $compraDetalleFechaModificacion = "", bool $compraDetalleEstado = true) 
		{
			$this->compraDetalleID = $compraDetalleID;
			$this->compraDetalleCompra = $compraDetalleCompra;
			$this->compraDetalleProducto = $compraDetalleProducto;
			$this->compraDetalleFechaCreacion = $compraDetalleFechaCreacion;
			$this->compraDetalleFechaModificacion = $compraDetalleFechaModificacion;
			$this->compraDetalleEstado = $compraDetalleEstado;
		}

		public function getCompraDetalleID(): int { return $this->compraDetalleID; }
		public function getCompraDetalleCompra(): Compra { return $this->compraDetalleCompra; }
		public function getCompraDetalleProducto(): Producto { return $this->compraDetalleProducto; }
		public function getCompraDetalleFechaCreacion() { return $this->compraDetalleFechaCreacion; }
		public function getCompraDetalleFechaModificacion() { return $this->compraDetalleFechaModificacion; }
		public function getCompraDetalleEstado(): bool { return $this->compraDetalleEstado; }

		public function setCompraDetalleID(int $compraDetalleID) { $this->compraDetalleID = $compraDetalleID; }
		public function setCompraDetalleCompra(Compra $compraDetalleCompra) { $this->compraDetalleCompra = $compraDetalleCompra; }
		public function setCompraDetalleProducto(Producto $compraDetalleProducto) { $this->compraDetalleProducto = $compraDetalleProducto; }
		public function setCompraDetalleFechaCreacion($compraDetalleFechaCreacion) { $this->compraDetalleFechaCreacion = $compraDetalleFechaCreacion; }
		public function setCompraDetalleFechaModificacion($compraDetalleFechaModificacion) { $this->compraDetalleFechaModificacion = $compraDetalleFechaModificacion; }
		public function setCompraDetalleEstado(bool $compraDetalleEstado) { $this->compraDetalleEstado = $compraDetalleEstado; }

		public function jsonSerialize() {
			return [
				'ID' => $this->compraDetalleID,
				'Compra' => $this->compraDetalleCompra,
				'Producto' => $this->compraDetalleProducto,
				'Creacion' => $this->compraDetalleFechaCreacion ? Utils::formatearFecha($this->compraDetalleFechaCreacion) : '',
                'Modificacion' => $this->compraDetalleFechaModificacion ? Utils::formatearFecha($this->compraDetalleFechaModificacion) : '',
                'CreacionISO' => $this->compraDetalleFechaCreacion ? Utils::formatearFecha($this->compraDetalleFechaCreacion, 'Y-MM-dd') : '',
                'ModificacionISO' => $this->compraDetalleFechaModificacion ? Utils::formatearFecha($this->compraDetalleFechaModificacion, 'Y-MM-dd') : '',
                'Estado' => $this->compraDetalleEstado
			];
		}

	}

?>