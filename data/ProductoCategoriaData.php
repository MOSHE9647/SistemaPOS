<?php

include_once 'data.php';
include __DIR__ . '/../domain/ProductoCategoria.php';
require_once __DIR__ . '/../utils/Variables.php';

class ProductoCategoriaData extends Data
{
    // Constructor
    public function __construct()
    {
        parent::__construct();
    }

    private function obtenerNuevoId() {
        try {
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];

            $query = "SELECT MAX(" . CATEGORIA_ID . ") AS max_id FROM " . TB_CATEGORIA;
            $stmt = mysqli_prepare($conn, $query);
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
            }

            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if (!$result) {
                throw new Exception("Error al obtener el resultado: " . mysqli_error($conn));
            }

            $row = mysqli_fetch_assoc($result);
            if ($row['max_id'] === null) {
                return 1;
            }
            return $row['max_id'] + 1;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        } finally {
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }

    public function insertarCategoria($categoria) {
        try {
            $nuevoId = $this->obtenerNuevoId();
            if ($nuevoId === false) {
                return ["success" => false, "message" => "Error al obtener un nuevo ID."];
            }

            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];

            $query = "INSERT INTO " . TB_CATEGORIA . " (" . CATEGORIA_ID . ", " . CATEGORIA_NOMBRE . ", " . CATEGORIA_ESTADO . ") VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($stmt, 'isi', $nuevoId, $categoria->getNombre(), $categoria->getEstado());
            $result = mysqli_stmt_execute($stmt);
            if (!$result) {
                throw new Exception("Error al insertar la categoría: " . mysqli_error($conn));
            }

            return ["success" => true, "message" => "Categoría insertada exitosamente."];
        } catch (Exception $e) {
            error_log($e->getMessage());
            return ["success" => false, "message" => $e->getMessage()];
        } finally {
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }

    public function actualizarCategoria($categoria) {
        try {
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];

            $query = "UPDATE " . TB_CATEGORIA . " SET " . CATEGORIA_NOMBRE . " = ?, " . CATEGORIA_ESTADO . " = ? WHERE " . CATEGORIA_ID . " = ?";
            $stmt = mysqli_prepare($conn, $query);
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($stmt, 'sii', $categoria->getNombre(), $categoria->getEstado(), $categoria->getId());
            $result = mysqli_stmt_execute($stmt);
            if (!$result) {
                throw new Exception("Error al actualizar la categoría: " . mysqli_error($conn));
            }

            return ["success" => true, "message" => "Categoría actualizada exitosamente."];
        } catch (Exception $e) {
            error_log($e->getMessage());
            return ["success" => false, "message" => $e->getMessage()];
        } finally {
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }

    public function eliminarCategoria($categoriaid) {
        try {
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];

            $query = "UPDATE " . TB_CATEGORIA . " SET " . CATEGORIA_ESTADO . " = 0 WHERE " . CATEGORIA_ID . " = ?";
            $stmt = mysqli_prepare($conn, $query);
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($stmt, 'i', $categoriaid);
            $result = mysqli_stmt_execute($stmt);
            if (!$result) {
                throw new Exception("Error al eliminar la categoría: " . mysqli_error($conn));
            }

            return ["success" => true, "message" => "Categoría eliminada exitosamente."];
        } catch (Exception $e) {
            error_log($e->getMessage());
            return ["success" => false, "message" => $e->getMessage()];
        } finally {
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }

    public function obtenerCategorias() {
        try {
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];

            $query = "SELECT * FROM " . TB_CATEGORIA . " WHERE " . CATEGORIA_ESTADO . " = 1";
            $stmt = mysqli_prepare($conn, $query);
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
            }

            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if (!$result) {
                throw new Exception("Error al obtener el resultado: " . mysqli_error($conn));
            }

            $categorias = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $categorias[] = new ProductoCategoria($row[CATEGORIA_ID], $row[CATEGORIA_NOMBRE], $row[CATEGORIA_ESTADO]);
            }

            return ["success" => true, "data" => $categorias];
        } catch (Exception $e) {
            error_log($e->getMessage());
            return ["success" => false, "message" => $e->getMessage()];
        } finally {
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }

    // Método para obtener la paginación de categorías
    public function getPaginationCategorias($page, $size, $sort = null) {
        try {
            // Validar los parámetros de paginación
            if (!is_numeric($page) || $page < 1) {
                throw new Exception("El número de página debe ser un entero positivo.");
            }
            if (!is_numeric($size) || $size < 1) {
                throw new Exception("El tamaño de la página debe ser un entero positivo.");
            }
            $offset = ($page - 1) * $size;
    
            // Establece una conexión con la base de datos
            $result = $this->getConnection();
            if (!$result["success"]) {
                throw new Exception($result["message"]);
            }
            $conn = $result["connection"];

            // Consultar el total de registros
            $queryTotalCount = "SELECT COUNT(*) AS total FROM " . TB_CATEGORIA . " WHERE " . CATEGORIA_ESTADO . " = 1";
            $totalResult = mysqli_query($conn, $queryTotalCount);
            if (!$totalResult) {
                throw new Exception("Error al obtener el conteo total de registros: " . mysqli_error($conn));
            }
            $totalRow = mysqli_fetch_assoc($totalResult);
            $totalRecords = (int)$totalRow['total'];
            $totalPages = ceil($totalRecords / $size);

            // Construir la consulta SQL para paginación
            $querySelect = "SELECT * FROM " . TB_CATEGORIA . " WHERE " . CATEGORIA_ESTADO . " = 1 ";

            // Añadir la cláusula de ordenamiento si se proporciona
            if ($sort) {
                $querySelect .= "ORDER BY " . CATEGORIA_NOMBRE . " " . $sort . " ";
            }

            // Añadir la cláusula de limitación y offset
            $querySelect .= "LIMIT ? OFFSET ?";

            // Preparar la consulta
            $stmt = mysqli_prepare($conn, $querySelect);
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
            }
    
            // Vincular los parámetros
            mysqli_stmt_bind_param($stmt, "ii", $size, $offset);

            // Ejecutar la consulta
            mysqli_stmt_execute($stmt);
            $resultSet = mysqli_stmt_get_result($stmt);
            if (!$resultSet) {
                throw new Exception("Error al ejecutar la consulta: " . mysqli_error($conn));
            }
    
            // Procesar los resultados
            $categorias = [];
            while ($row = mysqli_fetch_assoc($resultSet)) {
                $categorias[] = new ProductoCategoria($row[CATEGORIA_ID], $row[CATEGORIA_NOMBRE], $row[CATEGORIA_ESTADO]);
            }
    
            return [
                "success" => true,
                "data" => [
                    "totalPages" => $totalPages,
                    "totalRecords" => $totalRecords,
                    "categorias" => $categorias
                ]
            ];
        } catch (Exception $e) {
            error_log($e->getMessage());
            return ["success" => false, "message" => $e->getMessage()];
        } finally {
            if (isset($stmt)) { mysqli_stmt_close($stmt); }
            if (isset($conn)) { mysqli_close($conn); }
        }
    }
    
}

?>
