<?php

    include_once 'data.php';
    include __DIR__ . '/../domain/ProveedorTelefono.php';
    require_once __DIR__ . '/../utils/Variables.php';

    class ProveedorTelefonoData extends Data
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

                $query = "SELECT MAX(" . PROVEEDOR_TELEFONO_ID . ") AS max_id FROM " . TB_PROVEEDOR_TELEFONO;
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

        private function telefonoExiste($telefono, $proveedorid) {
            try {
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];

                $query = "SELECT COUNT(*) AS count FROM " . TB_PROVEEDOR_TELEFONO . " WHERE " . PROVEEDOR_TELEFONO . " = ? AND " . PROVEEDOR_TELEFONO_ESTADO . " = 1";
                $stmt = mysqli_prepare($conn, $query);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }

                mysqli_stmt_bind_param($stmt, 's', $telefono);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if (!$result) {
                    throw new Exception("Error al obtener el resultado: " . mysqli_error($conn));
                }

                $row = mysqli_fetch_assoc($result);
                return $row['count'] > 0;
            } catch (Exception $e) {
                error_log($e->getMessage());
                return false;
            } finally {
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        private function proveedorExiste($proveedorid) {
            try {
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];

                $query = "SELECT COUNT(*) AS count FROM " . TB_PROVEEDOR . " WHERE " . PROVEEDOR_ID . " = ?";
                $stmt = mysqli_prepare($conn, $query);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }

                mysqli_stmt_bind_param($stmt, 'i', $proveedorid);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if (!$result) {
                    throw new Exception("Error al obtener el resultado: " . mysqli_error($conn));
                }

                $row = mysqli_fetch_assoc($result);
                return $row['count'] > 0;
            } catch (Exception $e) {
                error_log($e->getMessage());
                return false;
            } finally {
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        private function validarFormatoTelefono($telefono) {
            // Validar formato de teléfono (ejemplo: +506 1234 5678)
            $patron = '/^\+506 \d{4} \d{4}$/';
            return preg_match($patron, $telefono);
        }

        private function validarProveedorTelefono($proveedorTelefono) {
            return !empty($proveedorTelefono->getTelefono()) && 
                   !empty($proveedorTelefono->getProveedorId()) &&
                   $this->validarFormatoTelefono($proveedorTelefono->getTelefono());
        }

        public function insertarProveedorTelefono($proveedorTelefono) {
            try {
                if (!$this->validarProveedorTelefono($proveedorTelefono) || !$this->proveedorExiste($proveedorTelefono->getProveedorId())) {
                    return ["success" => false, "message" => "Datos del proveedor o teléfono inválidos."];
                }

                if ($this->telefonoExiste($proveedorTelefono->getTelefono(), $proveedorTelefono->getProveedorId())) {
                    return ["success" => false, "message" => "El teléfono ya está asociado a un proveedor."];
                }

                $nuevoId = $this->obtenerNuevoId();
                if ($nuevoId === false) {
                    return ["success" => false, "message" => "Error al obtener un nuevo ID."];
                }

                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];

                $query = "INSERT INTO " . TB_PROVEEDOR_TELEFONO . " (" . PROVEEDOR_TELEFONO_ID . ", " . PROVEEDOR_ID . ", " . PROVEEDOR_TELEFONO . ", " . PROVEEDOR_TELEFONO_ESTADO . ") VALUES (?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $query);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }

                mysqli_stmt_bind_param($stmt, 'iisi', $nuevoId, $proveedorTelefono->getProveedorId(), $proveedorTelefono->getTelefono(), $proveedorTelefono->getActivo());
                $result = mysqli_stmt_execute($stmt);
                if (!$result) {
                    throw new Exception("Error al insertar el teléfono del proveedor: " . mysqli_error($conn));
                }

                return ["success" => true, "message" => "Teléfono del proveedor insertado exitosamente."];
            } catch (Exception $e) {
                error_log($e->getMessage());
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function actualizarProveedorTelefono($proveedorTelefono) {
            try {
                if (!$this->validarProveedorTelefono($proveedorTelefono) || !$this->proveedorExiste($proveedorTelefono->getProveedorId())) {
                    return ["success" => false, "message" => "Datos del proveedor o teléfono inválidos."];
                }

                if ($this->telefonoExiste($proveedorTelefono->getTelefono(), $proveedorTelefono->getProveedorId())) {
                    return ["success" => false, "message" => "El teléfono ya está asociado a un proveedor."];
                }

                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];

                $query = "UPDATE " . TB_PROVEEDOR_TELEFONO . " SET " . PROVEEDOR_ID . " = ?, " . PROVEEDOR_TELEFONO . " = ?, " . PROVEEDOR_TELEFONO_ESTADO . " = ? WHERE " . PROVEEDOR_TELEFONO_ID . " = ?";
                $stmt = mysqli_prepare($conn, $query);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }

                mysqli_stmt_bind_param($stmt, 'issi', $proveedorTelefono->getProveedorId(), $proveedorTelefono->getTelefono(), $proveedorTelefono->getActivo(), $proveedorTelefono->getProveedorTelefonoId());
                $result = mysqli_stmt_execute($stmt);
                if (!$result) {
                    throw new Exception("Error al actualizar el teléfono del proveedor: " . mysqli_error($conn));
                }

                return ["success" => true, "message" => "Teléfono del proveedor actualizado exitosamente."];
            } catch (Exception $e) {
                error_log($e->getMessage());
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function eliminarProveedorTelefono($proveedortelefonoid) {
            try {
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];

                $query = "UPDATE " . TB_PROVEEDOR_TELEFONO . " SET " . PROVEEDOR_TELEFONO_ESTADO . " = 0 WHERE " . PROVEEDOR_TELEFONO_ID . " = ?";
                $stmt = mysqli_prepare($conn, $query);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }

                mysqli_stmt_bind_param($stmt, 'i', $proveedortelefonoid);
                $result = mysqli_stmt_execute($stmt);
                if (!$result) {
                    throw new Exception("Error al eliminar el teléfono del proveedor: " . mysqli_error($conn));
                }

                return ["success" => true, "message" => "Teléfono del proveedor eliminado exitosamente."];
            } catch (Exception $e) {
                error_log($e->getMessage());
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        public function obtenerTelefonoProveedor($proveedorid) {
            try {
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];

                $query = "SELECT * FROM " . TB_PROVEEDOR_TELEFONO . " WHERE " . PROVEEDOR_ID . " = ? AND " . PROVEEDOR_TELEFONO_ESTADO . " = 1";
                $stmt = mysqli_prepare($conn, $query);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }

                mysqli_stmt_bind_param($stmt, 'i', $proveedorid);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if (!$result) {
                    throw new Exception("Error al obtener el resultado: " . mysqli_error($conn));
                }

                $telefonos = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $telefonos[] = new ProveedorTelefono($row[PROVEEDOR_TELEFONO_ID], $row[PROVEEDOR_ID], $row[PROVEEDOR_TELEFONO], $row[PROVEEDOR_TELEFONO_ESTADO]);
                }

                return ["success" => true, "data" => $telefonos];
            } catch (Exception $e) {
                error_log($e->getMessage());
                return ["success" => false, "message" => $e->getMessage()];
            } finally {
                if (isset($stmt)) { mysqli_stmt_close($stmt); }
                if (isset($conn)) { mysqli_close($conn); }
            }
        }

        // Método para obtener todos los ProveedorTelefonos activos
        public function obtenerProveedoresTelefonosActivos() {
            try {
                $result = $this->getConnection();
                if (!$result["success"]) {
                    throw new Exception($result["message"]);
                }
                $conn = $result["connection"];
        
                $query = "SELECT * FROM " . TB_PROVEEDOR_TELEFONO . " WHERE " . PROVEEDOR_TELEFONO_ESTADO . " = 1";
                $stmt = mysqli_prepare($conn, $query);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
                }
        
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if (!$result) {
                    throw new Exception("Error al obtener el resultado: " . mysqli_error($conn));
                }
        
                $proveedoresTelefonos = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $proveedoresTelefonos[] = new ProveedorTelefono(
                        $row[PROVEEDOR_TELEFONO_ID], 
                        $row[PROVEEDOR_ID], 
                        $row[PROVEEDOR_TELEFONO], 
                        $row[PROVEEDOR_TELEFONO_ESTADO]
                    );
                }
        
                return ["success" => true, "data" => $proveedoresTelefonos];
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