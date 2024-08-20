<?php

include_once 'data.php';
include __DIR__ . '/../domain/Lote.php';
include_once __DIR__ . '/../utils/Variables.php';

class LoteData extends Data {

    // Constructor
    public function __construct() {
        parent::__construct();
    }

    public function insertLote($lote) {
        try {
            // Establece una conexión con la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];
            if ($this->loteExists($lote->getLoteCodigo())) {
                return ["success" => false, "message" => "Ya existe un lote con el mismo código."];
            }
            // Obtiene el último ID de la tabla tblote
            $queryGetLastId = "SELECT MAX(" . LOTE_ID . ") FROM " . TB_LOTE;
            $idCont = mysqli_query($conn, $queryGetLastId);
            $nextId = 1;

            // Calcula el siguiente ID para la nueva entrada
            if ($row = mysqli_fetch_row($idCont)) {
                $nextId = (int) trim($row[0]) + 1;
            }
  
            // Crea una consulta y un statement SQL para insertar el registro
            $queryInsert = "INSERT INTO " . TB_LOTE . " ("
                . LOTE_ID . ", "
                . LOTE_CODIGO . ", "
                . PRODUCTOLOTE_ID . ", "
                . LOTE_CANTIDAD . ", "
                . LOTE_PRECIO . ", "
                . PROVEEDORLOTE_ID . ", "
                . LOTE_FECHA_INGRESO . ", "
                . LOTE_FECHA_VENCIMIENTO . ", "
                . LOTE_ESTADO
                . ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, true)";
            $stmt = mysqli_prepare($conn, $queryInsert);

            // Obtener los valores de las propiedades del objeto $lote
            $loteCodigo = $lote->getLoteCodigo();
            $productoID = $lote->getProductoID();
            $loteCantidad = $lote->getLoteCantidad();
            $lotePrecio = $lote->getLotePrecio();
            $proveedorID = $lote->getProveedorID();
            $loteFechaIngreso = $lote->getLoteFechaIngreso();
            $loteFechaVencimiento = $lote->getLoteFechaVencimiento();

            // Asigna los valores a cada '?' de la consulta
            mysqli_stmt_bind_param(
                $stmt,
                'isiidiss', // i: Entero, d: Doble, s: Cadena
                $nextId,
                $loteCodigo,
                $productoID,
                $loteCantidad,
                $lotePrecio,
                $proveedorID,
                $loteFechaIngreso,
                $loteFechaVencimiento
            );

            // Ejecuta la consulta de inserción
            $result = mysqli_stmt_execute($stmt);
            return ["success" => true, "message" => "Lote insertado exitosamente"];
        } catch (Exception $e) {
            // Manejo del error dentro del bloque catch
            $userMessage = $this->handleMysqlError(
                $e->getCode(), 
                $e->getMessage(),
                'Error al insertar el lote en la base de datos'
            );

            // Devolver mensaje amigable para el usuario
            return ["success" => false, "message" => $userMessage];
        } finally {
            // Cierra el statement y la conexión si están definidos
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }

    public function updateLote($lote) {
        try {
            // Establece una conexión con la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];

            // Crea una consulta y un statement SQL para actualizar el registro
            $queryUpdate = 
                "UPDATE " . TB_LOTE . 
                " SET " . 
                    LOTE_CODIGO . " = ?, " . 
                    PRODUCTOLOTE_ID . " = ?, " .
                    LOTE_CANTIDAD . " = ?, " .
                    LOTE_PRECIO . " = ?, " .
                    PROVEEDORLOTE_ID . " = ?, " .
                    LOTE_FECHA_INGRESO . " = ?, " .
                    LOTE_FECHA_VENCIMIENTO . " = ?, " .
                    LOTE_ESTADO . " = true " .
                "WHERE " . LOTE_ID . " = ?";
            $stmt = mysqli_prepare($conn, $queryUpdate);

            // Obtener los valores de las propiedades del objeto $lote
            $loteID = $lote->getLoteID();
            $loteCodigo = $lote->getLoteCodigo();
            $productoID = $lote->getProductoID();
            $loteCantidad = $lote->getLoteCantidad();
            $lotePrecio = $lote->getLotePrecio();
            $proveedorID = $lote->getProveedorID();
            $loteFechaIngreso = $lote->getLoteFechaIngreso();
            $loteFechaVencimiento = $lote->getLoteFechaVencimiento();

            // Asigna los valores a cada '?' de la consulta
            mysqli_stmt_bind_param(
                $stmt,
                'siidissi', // i: Entero, d: Decimal, s: Cadena
                $loteCodigo,
                $productoID,
                $loteCantidad,
                $lotePrecio,
                $proveedorID,
                $loteFechaIngreso,
                $loteFechaVencimiento,
                $loteID
            );

            // Ejecuta la consulta de actualización
            $result = mysqli_stmt_execute($stmt);

            // Devuelve el resultado de la consulta
            return ["success" => true, "message" => "Lote actualizado exitosamente"];
        } catch (Exception $e) {
            // Manejo del error dentro del bloque catch
            $userMessage = $this->handleMysqlError(
                $e->getCode(), 
                $e->getMessage(),
                'Error al actualizar el lote en la base de datos'
            );

            // Devolver mensaje amigable para el usuario
            return ["success" => false, "message" => $userMessage];
        } finally {
            // Cierra la conexión y el statement si están definidos
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }

    public function getAllTBLote() {
        try {
            // Establece una conexion con la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];

            // Construir la consulta SQL con joins para obtener nombres en lugar de IDs
        $querySelect = "
        SELECT 
            l." . LOTE_ID . ", 
            l." . LOTE_CODIGO . ", 
            p.productonombre AS productoNombre,
            l." . LOTE_CANTIDAD . ", 
            l." . LOTE_PRECIO . ", 
            pr.proveedornombre AS proveedorNombre,
            l." . LOTE_FECHA_INGRESO . ", 
            l." . LOTE_FECHA_VENCIMIENTO . ", 
            l." . LOTE_ESTADO . "
        FROM " . TB_LOTE . " l
        JOIN tbproducto p ON l." . PRODUCTOLOTE_ID . " = p.productoid
        JOIN tbproveedor pr ON l." . PROVEEDORLOTE_ID . " = pr.proveedorid
        WHERE l." . LOTE_ESTADO . " != false
        ";

        $result = mysqli_query($conn, $querySelect);

           // Crear la lista con los datos obtenidos
        $listaLotes = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $currentLote = new Lote(
                $row[LOTE_ID],
                $row[LOTE_CODIGO],
                $row['productoNombre'],  // Usar el nombre del producto
                $row[LOTE_CANTIDAD],
                $row[LOTE_PRECIO],
                $row['proveedorNombre'], // Usar el nombre del proveedor
                $row[LOTE_FECHA_INGRESO],
                $row[LOTE_FECHA_VENCIMIENTO],
                $row[LOTE_ESTADO]
            );
            array_push($listaLotes, $currentLote);
        }

            return ["success" => true, "listaLotes" => $listaLotes];
        } catch (Exception $e) {
            // Manejo del error dentro del bloque catch
            $userMessage = $this->handleMysqlError(
                $e->getCode(), 
                $e->getMessage(),
                'Error al obtener la lista de lotes desde la base de datos'
            );

            // Devolver mensaje amigable para el usuario
            return ["success" => false, "message" => $userMessage];
        } finally {
            // Cerramos la conexion
            if (isset($conn)) { mysqli_close($conn); }
        }
    }

    
    public function getPaginatedLotes($page, $size, $sort = null) {
        try {
            // Verificar que la página y el tamaño sean números enteros positivos
            if (!is_numeric($page) || $page < 1) {
                throw new Exception("El número de página debe ser un entero positivo.");
            }
            if (!is_numeric($size) || $size < 1) {
                throw new Exception("El tamaño de la página debe ser un entero positivo.");
            }
            
            // Calcular el offset para la paginación
            $offset = ($page - 1) * $size;

            // Establece una conexión con la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];

            // Consultar el total de registros en la tabla
            $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_LOTE . " WHERE " . LOTE_ESTADO . " != false ";
			$totalResult = mysqli_query($conn, $queryTotalCount);
            $totalRow = mysqli_fetch_assoc($totalResult);
            $totalRecords = (int) $totalRow['total'];
            $totalPages = ceil($totalRecords / $size);

			  // Construir la consulta SQL para paginación con joins
              $querySelect = "
              SELECT 
                  l." . LOTE_ID . ", 
                  l." . LOTE_CODIGO . ", 
                  p.productonombre AS productoNombre,
                  l." . LOTE_CANTIDAD . ", 
                  l." . LOTE_PRECIO . ", 
                  pr.proveedornombre AS proveedorNombre,
                  l." . LOTE_FECHA_INGRESO . ", 
                  l." . LOTE_FECHA_VENCIMIENTO . ", 
                  l." . LOTE_ESTADO . "
              FROM " . TB_LOTE . " l
              JOIN tbproducto p ON l." . PRODUCTOLOTE_ID . " = p.productoid
              JOIN tbproveedor pr ON l." . PROVEEDORLOTE_ID . " = pr.proveedorid
              WHERE l." . LOTE_ESTADO . " != false 
          ";
			   
        
                // Añadir la cláusula de limitación y offset
                $querySelect .= "LIMIT ? OFFSET ?";
        
                // Preparar la consulta y vincular los parámetros
                $stmt = mysqli_prepare($conn, $querySelect);
                mysqli_stmt_bind_param($stmt, "ii", $size, $offset);
        
                // Ejecutar la consulta
                $result = mysqli_stmt_execute($stmt);
        
                // Obtener el resultado
                $result = mysqli_stmt_get_result($stmt);

            // Crear la lista de lotes
            $listaLotes = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $listaLotes [] = [
					'ID' => $row[LOTE_ID],
                    'Codigo' => $row[LOTE_CODIGO],
                    'Producto' => $row['productoNombre'],
					'Cantidad' => $row[LOTE_CANTIDAD],
                    'Precio' =>  $row[LOTE_PRECIO],
					'Proveedor' => $row['proveedorNombre'],
					'FechaIngreso' => $row[LOTE_FECHA_INGRESO],
					'FechaVencimiento' => $row[LOTE_FECHA_VENCIMIENTO],
					'Estado' => $row[LOTE_ESTADO]
				];
            }

             // Devolver el resultado con la lista de direcciones y metadatos de paginación
			 return [
				"success" => true,
				"page" => $page,
				"size" => $size,
				"totalPages" => $totalPages,
				"totalRecords" => $totalRecords,
				"listaLotes" => $listaLotes
			];
		} catch (Exception $e) {
			// Manejo del error dentro del bloque catch
			$userMessage = $this->handleMysqlError(
				$e->getCode(), 
				$e->getMessage(),
				'Error al obtener la lista de direcciones desde la base de datos'
			);
	
			// Devolver mensaje amigable para el usuario
			return ["success" => false, "message" => $userMessage];
		} finally {
			// Cerrar la conexión y el statement
			if (isset($stmt)) { mysqli_stmt_close($stmt); }
			if (isset($conn)) { mysqli_close($conn); }
		}
	}

	private function loteExiste($loteID) {
		try {
			// Establece una conexión con la base de datos
			$result = $this->getConnection();
			if (!$result["success"]) {
				throw new Exception($result["message"]);
			}
			$conn = $result["connection"];

			// Crea una consulta y un statement SQL para buscar el registro
			$queryCheck = "SELECT * FROM " . TB_LOTE . " WHERE " . LOTE_ID . " = ? AND " . LOTE_ESTADO . " != false";
			$stmt = mysqli_prepare($conn, $queryCheck);

			// Asignar los parámetros y ejecutar la consulta
			mysqli_stmt_bind_param($stmt, "i", $loteID);
			mysqli_stmt_execute($stmt);
			$result = mysqli_stmt_get_result($stmt);

			// Verifica si existe algún registro con los criterios dados
			if (mysqli_num_rows($result) > 0) {
				return ["success" => true, "exists" => true];
			}
	
			return ["success" => true, "exists" => false];
		} catch (Exception $e) {
			// Manejo del error dentro del bloque catch
			$userMessage = $this->handleMysqlError(
				$e->getCode(), 
				$e->getMessage(),
				'Error al obtener la lista de direcciones desde la base de datos'
			);

			// Devolver mensaje amigable para el usuario
			return ["success" => false, "message" => $userMessage];
		} finally {
			// Cierra la conexión y el statement
			if (isset($stmt)) { mysqli_stmt_close($stmt); }
			if (isset($conn)) { mysqli_close($conn); }
		}
	}
	
    public function deleteLote($loteID) {
        try {

			 // Verifica que el ID de la dirección no esté vacío y sea numérico
			 if (empty($loteID) || !is_numeric($loteID) || $loteID <= 0) {
				throw new Exception("El ID no puede estar vacío o ser menor a 0.");
			}

			 // Verificar si existe el ID y que el Estado no sea false
			 $check = $this->loteExiste($loteID);
			 if (!$check["success"]) {
				 return $check; // Error al verificar la existencia
			 }
			 if (!$check["exists"]) {
				 throw new Exception("No se encontró una dirección con el ID [" . $loteID . "]");
			 }


            // Establece una conexión con la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];

            // Crea una consulta y un statement SQL para eliminar el registro
            $queryDelete = "UPDATE " . TB_LOTE . " SET ". LOTE_ESTADO . " = false WHERE " . LOTE_ID . " = ?";
            $stmt = mysqli_prepare($conn, $queryDelete);
			mysqli_stmt_bind_param($stmt, 'i', $loteID);

            // Ejecuta la consulta de eliminación
            $result = mysqli_stmt_execute($stmt);

            // Devuelve el resultado de la consulta
            return ["success" => true, "message" => "Lote eliminado exitosamente"];
        } catch (Exception $e) {
            // Devolver mensaje amigable para el usuario
            return ["success" => false, "message" => $e->getMessage()];
        } finally {
            // Cierra la conexión y el statement si están definidos
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }

    public function loteExists($codigo) {
    try {
        // Establece una conexión con la base de datos
        $result = $this->getConnection();
        if (!$result["success"]) {
            throw new Exception($result["message"]);
        }
        $conn = $result["connection"];

        // Consulta SQL para verificar si existe un lote con el mismo código
        $querySelect = "SELECT COUNT(*) FROM " . TB_LOTE . " WHERE " . LOTE_CODIGO . " = ? AND " . LOTE_ESTADO . " = true";
        $stmt = mysqli_prepare($conn, $querySelect);

        // Asigna el valor al '?' de la consulta
        mysqli_stmt_bind_param($stmt, 's', $codigo);

        // Ejecuta la consulta
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        // Verifica el número de lotes encontrados
        $count = mysqli_fetch_row($result)[0];
        return $count > 0;
    } catch (Exception $e) {
        // Manejo del error dentro del bloque catch
        $userMessage = $this->handleMysqlError(
            $e->getCode(), 
            $e->getMessage(),
            'Error al verificar la existencia del lote'
        );

        // Devolver mensaje amigable para el usuario
        return ["success" => false, "message" => $userMessage];
    } finally {
        // Cierra el statement y la conexión si están definidos
        if (isset($stmt)) { mysqli_stmt_close($stmt); }
        if (isset($conn)) { mysqli_close($conn); }
    }
}

}
?>