<?php

	include_once 'data.php';
	include __DIR__ . '/../domain/Impuesto.php';

	class ImpuestoData extends Data {

		// Constructor
		public function __construct() {
			parent::__construct();
		}

		// Función para verificar si un impuesto con el mismo nombre ya existe en la bd
		public function impuestoExiste($impuestoNombre) {
			// Establece una conexión con la base de datos
			$conn = mysqli_connect($this->servername, $this->username, $this->password, $this->dbname);
			if (!$conn) {
				return ["success" => false, "message" => "Error al conectar con la base de datos"];
			}
			$conn->set_charset('utf8');
	
			// Consulta para verificar si el nombre del impuesto ya existe
			$queryCheck = "SELECT * FROM tbimpuesto WHERE ImpuestoNombre = ?";
			$stmt = mysqli_prepare($conn, $queryCheck);
			if (!$stmt) {
				mysqli_close($conn);
				return ["success" => false, "message" => "Error al preparar la consulta"];
			}
	
			mysqli_stmt_bind_param($stmt, 's', $impuestoNombre);
			mysqli_stmt_execute($stmt);
			$result = mysqli_stmt_get_result($stmt);
	
			// Verifica si existe algún registro con el mismo nombre
			if (mysqli_num_rows($result) > 0) {
				mysqli_stmt_close($stmt);
				mysqli_close($conn);
				return ["success" => true, "exists" => true];
			}
	
			// Cierra el statement y la conexión a la base de datos
			mysqli_stmt_close($stmt);
			mysqli_close($conn);
	
			return ["success" => true, "exists" => false];
		}

		// Funcion para validar fecha
		public function validar_fecha($fecha) {
			$formato = 'Y-m-d';
			$date = DateTime::createFromFormat($formato, $fecha);
			return $date && $date->format($formato) === $fecha;
		}

		public function insertImpuesto($impuesto) {
			// Obtener los valores de las propiedades del objeto
			$impuestoNombre = $impuesto->getImpuestoNombre();
			$impuestoValor = $impuesto->getImpuestoValor();
			$impuestoDescripcion = $impuesto->getImpuestoDescripcion();
			$impuestoFechaVigencia = $impuesto->getImpuestoFechaVigencia();
	
			// Verifica que las propiedades no estén vacías
			if (empty($impuestoNombre)) {
				return ["success" => false, "message" => "El nombre del impuesto está vacío"];
			}
			if (empty($impuestoValor)) {
				return ["success" => false, "message" => "El valor del impuesto está vacío"];
			}
			if (empty($impuestoDescripcion)) {
				return ["success" => false, "message" => "La descripción del impuesto está vacía"];
			}
			if (empty($impuestoFechaVigencia) || !validar_fecha($impuestoFechaVigencia)) {
				return ["success" => false, "message" => "La fecha de vigencia del impuesto está vacía o no es válida"];
			}
	
			// Verifica si el impuesto ya existe
			$check = $this->impuestoExiste($impuestoNombre);
			if (!$check["success"]) {
				return $check; // Error al verificar la existencia
			}
			if ($check["exists"]) {
				return ["success" => false, "message" => "Ya existe un impuesto con el mismo nombre"];
			}

			// Establece una conexión con la base de datos
			$conn = mysqli_connect($this->servername, $this->username, $this->password, $this->dbname);
			if (!$conn) {
				return ["success" => false, "message" => "Error al conectar con la base de datos"];
			}
			$conn->set_charset('utf8');
	
			// Obtenemos el último ID de la tabla tbimpuesto
			$queryGetLastId = "SELECT MAX(ImpuestoID) AS impuestoID FROM tbimpuesto";
			$idCont = mysqli_query($conn, $queryGetLastId);
			if (!$idCont) {
				mysqli_close($conn);
				return ["success" => false, "message" => "Error al ejecutar la consulta"];
			}
			$nextId = 1;
	
			// Calcula el siguiente ID para la nueva entrada
			if ($row = mysqli_fetch_row($idCont)) {
				$nextId = (int) trim($row[0]) + 1;
			}
	
			// Crea una consulta y un statement SQL para insertar el nuevo registro
			$queryInsert = "INSERT INTO tbimpuesto VALUES (?, ?, ?, ?, ?)";
			$stmt = mysqli_prepare($conn, $queryInsert);
			if (!$stmt) {
				mysqli_close($conn);
				return ["success" => false, "message" => "Error al preparar la consulta"];
			}
	
			mysqli_stmt_bind_param(
				$stmt,
				'issss', // i: Entero, s: Cadena
				$nextId,
				$impuestoNombre,
				$impuestoValor,
				$impuestoDescripcion,
				$impuestoFechaVigencia
			);
	
			// Ejecuta la consulta de inserción
			$result = mysqli_stmt_execute($stmt);
			if (!$result) {
				mysqli_stmt_close($stmt);
				mysqli_close($conn);
				return ["success" => false, "message" => "Error al insertar el impuesto"];
			}
	
			// Cierra el statement y la conexión a la base de datos
			mysqli_stmt_close($stmt);
			mysqli_close($conn);
	
			// Devuelve el resultado de la consulta
			return ["success" => true, "message" => "Impuesto insertado exitosamente"];
		}

		public function getAllTBImpuesto() {
			// Establece una conexion con la base de datos
			$conn = mysqli_connect($this->servername, $this->username, $this->password, $this->dbname);
			if (!$conn) {
				return ["success" => false, "message" => "Error al conectar con la base de datos"];
			}
			$conn->set_charset('utf8');

			// Obtenemos la lista de Impuestos
			$querySelect = "SELECT * FROM tbimpuesto";
			$result = mysqli_query($conn, $querySelect);

			// Verificamos si ocurrio un error
			mysqli_close($conn); //<- Cierra la conexion
			if (!$result) {
				return ["success" => false, "message" => "Ocurrió un error al ejecutar la consulta"];
			}

			// Creamos la lista con los datos obtenidos
			$listaImpuestos = [];
			while ($row = mysqli_fetch_array($result)) {
				$currentImpuesto = new Impuesto(
					$row['ImpuestoID'],
					$row['ImpuestoNombre'],
					$row['ImpuestoValor'],
					$row['ImpuestoDescripcion'],
					$row['ImpuestoFechaVigencia']
				);
				array_push($listaImpuestos, $currentImpuesto);
			}

			return ["success" => true, "listaImpuestos" => $listaImpuestos];
		}

		public function updateImpuesto($impuesto) {
			// Obtener los valores de las propiedades del objeto
			$impuestoID = $impuesto->getImpuestoID();
			$impuestoNombre = $impuesto->getImpuestoNombre();
			$impuestoValor = $impuesto->getImpuestoValor();
			$impuestoDescripcion = $impuesto->getImpuestoDescripcion();
			$impuestoFechaVigencia = $impuesto->getImpuestoFechaVigencia();
	
			// Verifica que las propiedades no estén vacías
			if (empty($impuestoID) || !is_numeric($impuestoID)) {
				return ["success" => false, "message" => "No se encontró el ID del Impuesto o este no es válido"];
			}
			if (empty($impuestoNombre)) {
				return ["success" => false, "message" => "El nombre del impuesto está vacío"];
			}
			if (empty($impuestoValor)) {
				return ["success" => false, "message" => "El valor del impuesto está vacío"];
			}
			if (empty($impuestoDescripcion)) {
				return ["success" => false, "message" => "La descripción del impuesto está vacía"];
			}
			if (empty($impuestoFechaVigencia) || !validar_fecha($impuestoFechaVigencia)) {
				return ["success" => false, "message" => "La fecha de vigencia del impuesto está vacía o no es válida"];
			}

			// Establece una conexión con la base de datos
			$conn = mysqli_connect($this->servername, $this->username, $this->password, $this->dbname);
			if (!$conn) {
				return ["success" => false, "message" => "Error al conectar con la base de datos"];
			}
			$conn->set_charset('utf8');

			// Crea una consulta y un statement SQL para actualizar el registro
			$queryUpdate = "UPDATE tbimpuesto SET impuestoNombre = ?, impuestoValor = ?, impuestoDescripcion = ?, impuestoFechaVigencia = ? WHERE impuestoID = ?";
			$stmt = mysqli_prepare($conn, $queryUpdate);
			if (!$stmt) {
				mysqli_close($conn);
				return ["success" => false, "message" => "Error al preparar la consulta"];
			}

			mysqli_stmt_bind_param(
				$stmt,
				'ssssi', // s: Cadena, i: Entero
				$impuestoNombre,
				$impuestoValor,
				$impuestoDescripcion,
				$impuestoFechaVigencia,
				$impuestoID
			);

			// Ejecuta la consulta de actualización
			$result = mysqli_stmt_execute($stmt);
			if (!$result) {
				mysqli_stmt_close($stmt);
				mysqli_close($conn);
				return ["success" => false, "message" => "Error al actualizar el impuesto"];
			}

			// Cierra el statement y la conexión a la base de datos
			mysqli_stmt_close($stmt);
			mysqli_close($conn);

			// Devuelve el resultado de la consulta
			return ["success" => true, "message" => "Impuesto actualizado exitosamente"];
		}

		public function deleteImpuesto($impuestoID) {
			// Verifica que las propiedades no estén vacías
			if (empty($impuestoID) || !is_numeric($impuestoID)) {
				return ["success" => false, "message" => "No se encontró el ID del Impuesto o este no es válido"];
			}

			// Establece una conexión con la base de datos
			$conn = mysqli_connect($this->servername, $this->username, $this->password, $this->dbname);
			if (!$conn) {
				return ["success" => false, "message" => "Error al conectar con la base de datos"];
			}
			$conn->set_charset('utf8');

			// Crea una consulta y un statement SQL para eliminar el registro
			$queryDelete = "DELETE FROM tbimpuesto WHERE impuestoID = ?";
			$stmt = mysqli_prepare($conn, $queryDelete);
			if (!$stmt) {
				mysqli_close($conn);
				return ["success" => false, "message" => "Error al preparar la consulta"];
			}

			mysqli_stmt_bind_param($stmt, 'i', $impuestoID);

			// Ejecuta la consulta de eliminación
			$result = mysqli_stmt_execute($stmt);
			if (!$result) {
				mysqli_stmt_close($stmt);
				mysqli_close($conn);
				return ["success" => false, "message" => "Error al eliminar el impuesto"];
			}

			// Cierra el statement y la conexión a la base de datos
			mysqli_stmt_close($stmt);
			mysqli_close($conn);

			// Devuelve el resultado de la consulta
			return ["success" => true, "message" => "Impuesto eliminado exitosamente"];
		}

	}

?>