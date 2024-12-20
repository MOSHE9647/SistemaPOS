<?php

	require_once dirname(__DIR__, 1) . '/domain/Venta.php';
	require_once dirname(__DIR__, 1) . '/domain/Producto.php';
	require_once dirname(__DIR__, 1) . '/utils/Utils.php';

	class VentaDetalle implements JsonSerializable {

		private $ventaDetalleID;
		private $ventaDetalleVenta;
		private $ventaDetalleProducto;
		private $ventaDetallePrecio;
		private $ventaDetalleCantidad;
		private $ventaDetalleEstado;

		public function __construct(int $ventaDetalleID = -1, float $ventaDetallePrecio = 0.0, int $ventaDetalleCantidad = 0,
			Venta $ventaDetalleVenta = null, Producto $ventaDetalleProducto = null, bool $ventaDetalleEstado = true) 
		{
			$this->ventaDetalleID = $ventaDetalleID;
			$this->ventaDetalleVenta = $ventaDetalleVenta;
			$this->ventaDetalleProducto = $ventaDetalleProducto;
			$this->ventaDetallePrecio = Utils::formatearDecimal($ventaDetallePrecio);
			$this->ventaDetalleCantidad = $ventaDetalleCantidad;
			$this->ventaDetalleEstado = $ventaDetalleEstado;
		}

		public function getVentaDetalleID(): int { return $this->ventaDetalleID; }
		public function getVentaDetalleVenta(): ?Venta { return $this->ventaDetalleVenta; }
		public function getVentaDetalleProducto(): ?Producto { return $this->ventaDetalleProducto; }
		public function getVentaDetallePrecio(): float { return $this->ventaDetallePrecio; }
		public function getVentaDetalleCantidad(): int { return $this->ventaDetalleCantidad; }
		public function getVentaDetalleEstado(): bool { return $this->ventaDetalleEstado; }

		public function setVentaDetalleID(int $ventaDetalleID) { $this->ventaDetalleID = $ventaDetalleID; }
		public function setVentaDetalleVenta(Venta $ventaDetalleVenta) { $this->ventaDetalleVenta = $ventaDetalleVenta; }
		public function setVentaDetalleProducto(Producto $ventaDetalleProducto) { $this->ventaDetalleProducto = $ventaDetalleProducto; }
		public function setVentaDetallePrecio(float $ventaDetallePrecio) { $this->ventaDetallePrecio = Utils::formatearDecimal($ventaDetallePrecio); }
		public function setVentaDetalleCantidad(int $ventaDetalleCantidad) { $this->ventaDetalleCantidad = $ventaDetalleCantidad; }
		public function setVentaDetalleEstado(bool $ventaDetalleEstado) { $this->ventaDetalleEstado = $ventaDetalleEstado; }

		public static function fromArray(array $ventaDetalle): VentaDetalle {
			return new VentaDetalle(
				intval($ventaDetalle['ID'] ?? -1),
				floatval($ventaDetalle['Precio'] ?? 0.0),
				intval($ventaDetalle['Cantidad'] ?? 0),
				Utils::convertToObject($ventaDetalle['Venta'] ?? null, Venta::class),
				Utils::convertToObject($ventaDetalle['Producto'] ?? null, Producto::class),
				$ventaDetalle['Estado'] ?? true
			);
		}

		public function jsonSerialize() {
			return [
				'ID' => $this->ventaDetalleID,
				'Venta' => $this->ventaDetalleVenta ? $this->ventaDetalleVenta : null,
				'Producto' => $this->ventaDetalleProducto ? $this->ventaDetalleProducto : null,
				'Precio' => $this->ventaDetallePrecio,
				'Cantidad' => $this->ventaDetalleCantidad,
				'Estado' => $this->ventaDetalleEstado
			];
		}

	}

?>