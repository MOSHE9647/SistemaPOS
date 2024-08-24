<?php

	include __DIR__ . "/../data/proveedorDireccionData.php";

	class ProveedorDireccionBusiness {
		
		private $proveedorDireccionData;

		public function __construct() {
			$this->proveedorDireccionData = new ProveedorDireccionData();
		}

		public function addDireccionToProveedor($proveedorID, $direccionID) {
			return $this->proveedorDireccionData->addDireccionToProveedor($proveedorID, $direccionID);
		}

		public function removeDireccionFromProveedor($proveedorID, $direccionID) {
			return $this->proveedorDireccionData->removeDireccionFromProveedor($proveedorID, $direccionID);
		}

		public function getDireccionesByProveedor($proveedorID) {
			return $this->proveedorDireccionData->getDireccionesByProveedor($proveedorID);
		}

		public function getProveedoresByDireccion($direccionID) {
			return $this->proveedorDireccionData->getProveedoresByDireccion($direccionID);
		}

		public function updateDireccionOfProveedor($proveedorID, $direccionID) {
			try {
				// Primero, se 'elimina' la dirección que tenía anteriormente
				$remove = $this->removeDireccionFromProveedor($proveedorID, $direccionID);
				if (!$remove['success']) {
					throw new Exception($remove['message']);
				}

				// Luego, se le agrega la nueva dirección al proveedor
				$insert = $this->addDireccionToProveedor($proveedorID, $direccionID);
				if (!$insert['success']) {
					throw new Exception($insert['message']);
				}

				// Y, si todo salió bien, se devuelve el resultado de la operación
				return ["success" => true, "message" => "La dirección del proveedor se actualizó exitosamente."];
			} catch (Exception $e) {
				// Devuelve el mensaje de error
				return ["success" => false, "message" => $e->getMessage()];
			}
		}

	}

?>