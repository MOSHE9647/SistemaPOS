<?php

require_once __DIR__ . "/../data/compraData.php";
require_once dirname(__DIR__, 1) . '/utils/Utils.php';

class CompraBusiness {

    private $className;     //<- Variable para almacenar el nombre de la clase
    private $compraData;

    public function __construct() {
        $this->compraData = new CompraData();
        $this->className = get_class($this);      //<- Se obtiene el nombre de la clase
    }

      /**
         * Valida el ID de un producto.
         *
         * @param mixed $productoID El ID del producto a validar.
         * 
         * @return array Un arreglo asociativo que indica si el ID es válido y un mensaje en caso de error.
         *               - "is_valid" (bool): Indica si el ID es válido.
         *               - "message" (string): Mensaje de error si el ID no es válido.
         */
        public function validarCompraID($compraID) {
            if ($compraID === null || !is_numeric($compraID) || $compraID < 0) {
                Utils::writeLog("El ID [$compraID] de la compra no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                return ["is_valid" => false, "message" => "El ID de la compra está vacío o no es válido. Revise que este sea un número y que sea mayor a 0"];
            }
            return ["is_valid" => true];
        }

         /**
         * Valida los datos de paginación.
         *
         * @param mixed $page El número de página a validar.
         * @param mixed $size El tamaño de la página a validar.
         * 
         * @return array Un arreglo asociativo que indica si los datos de paginación son válidos y un mensaje en caso de error.
         *               - "is_valid" (bool): Indica si los datos de paginación son válidos.
         *               - "message" (string): Mensaje de error si los datos de paginación no son válidos.
         */

         public function validarDatosPaginacion($page, $size) {
            if ($page === null || !is_numeric($page) || $page < 0) {
                Utils::writeLog("El 'page [$page]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                return ["is_valid" => false, "message" => "El número de página está vacío o no es válido. Revise que este sea un número y que sea mayor o igual a 0"];
            }

            if ($size === null || !is_numeric($size) || $size < 0) {
                Utils::writeLog("El 'size [$size]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className, __LINE__);
                return ["is_valid" => false, "message" => "El tamaño de la página está vacío o no es válido. Revise que este sea un número y que sea mayor o igual a 0"];
            }

            return ["is_valid" => true];
        }

         /**
         * Valida los datos de un producto.
         *
         * @param Compra $producto El producto a validar.
         * @param bool $validarCamposAdicionales Indica si se deben validar los campos adicionales del producto.
         * @param bool $insert Indica si se está insertando un nuevo producto.
         * 
         * @return array Un arreglo asociativo que indica si los datos del producto son válidos y un mensaje en caso de error.
         *               - "is_valid" (bool): Indica si los datos del producto son válidos.
         *               - "message" (string): Mensaje de error si los datos del producto no son válidos.
         */

    public function validarCompra($compra, $validarCamposAdicionales = true, $insert = false) {
        try {
            // Obtener los valores de las propiedades del objeto
            $compraID = $compra->getCompraID();
            $compraNumeroFactura = $compra->getCompraNumeroFactura();
            $compraMontoBruto = $compra->getCompraMontoBruto();
            $compraMontoNeto = $compra->getCompraMontoNeto();
            $compraTipoPago = $compra->getCompraTipoPago();
            $proveedorid = $compra->getProveedorID();
            $clienteid = $compra->getClienteID();
            $errors = [];
            

             // Verifica que el ID del producto sea válido
             if (!$insert) {
                $checkID = $this->validarCompraID($compraID);
                if (!$checkID['is_valid']) { $errors[] = $checkID['message']; }
            }

            // Si la validación de campos adicionales está activada, valida los otros campos
            if ($validarCamposAdicionales) {

                if (empty($compraNumeroFactura)) {
                    $errors[] = "El campo 'Número de factura' está vacío.";
                    Utils::writeLog("El campo 'Número de factura [$compraNumeroFactura]' está vacío.", BUSINESS_LOG_FILE, ERROR_MESSAGE,  $this->className, __LINE__);
                }

                if ($compraNumeroFactura < 0) {
                    $errors[] = "El campo 'Número de factura' no puede ser negativo vacío.";
                    Utils::writeLog("El campo 'Número de factura [$compraNumeroFactura]' no es valido.", BUSINESS_LOG_FILE, ERROR_MESSAGE,  $this->className, __LINE__);
                }

                if ($compraMontoBruto === null) {
                    $errors[] = "El campo 'Monto bruto' está vacío.";
                    Utils::writeLog("El campo 'Monto bruto [$compraMontoBruto]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE,  $this->className, __LINE__);
                }
                if ($compraMontoBruto < 0) {
                    $errors[] = "El campo 'Monto bruto' tiene que ser positivo.";
                    Utils::writeLog("El campo 'Monto bruto [$compraMontoBruto]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE,  $this->className, __LINE__);
                }

                if ($compraMontoNeto === null) {
                    $errors[] = "El campo 'Monto neto' está vacío.";
                    Utils::writeLog("El campo 'Monto neto [$compraMontoNeto]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE,  $this->className, __LINE__);
                }

                if ($compraMontoNeto < 0) {
                    $errors[] = "El campo 'Monto neto' tiene que ser positivo.";
                    Utils::writeLog("El campo 'Monto neto [$compraMontoNeto]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE,  $this->className, __LINE__);
                }

                if (empty($compraTipoPago)) {
                    $errors[] = "El campo 'Tipo de pago' está vacío.";
                    Utils::writeLog("El campo 'Tipo de pago [$compraTipoPago]' está vacío.", BUSINESS_LOG_FILE, ERROR_MESSAGE,  $this->className, __LINE__);
                }

                if ($proveedorid === null || !is_numeric($proveedorid) || $proveedorid < 0) {
                    $errors[] = "El campo 'Proveedor' está vacío.";
                    Utils::writeLog("El campo 'Proveedor ID [$proveedorid]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE,  $this->className, __LINE__);
                }

                if ($clienteid === null || !is_numeric($clienteid) || $clienteid < 0) {
                    $errors[] = "El campo 'Cliente' está vacío.";
                    Utils::writeLog("El campo 'Cliente ID [$clienteid]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE,  $this->className, __LINE__);
                }
            }

            // Lanza una excepción si hay errores
            if (!empty($errors)) {
                throw new Exception(implode('<br>', $errors));
            }

            return ["is_valid" => true];
        } catch (Exception $e) {
            return ["is_valid" => false, "message" => $e->getMessage()];
        }
    }

    /**
         * Inserta un nuevo producto en la base de datos.
         *
         * @param array $producto Los datos del producto a insertar.
         * @return array Un arreglo asociativo con el resultado de la operación.
         *               - "success" (bool): Indica si la inserción fue exitosa.
         *               - "message" (string): Mensaje de error en caso de que la validación falle.
         */

    public function insertTBCompra($compra) {
        // Verifica que los datos de la compra sean válidos
        $check = $this->validarCompra($compra, true, true);
        if (!$check["is_valid"]) {
            return ["success" => false, "message" => $check["message"]];
        }
        return $this->compraData->insertCompra($compra);
    }

     /**
         * Actualiza la información de un producto en la base de datos.
         *
         * @param array $producto Arreglo asociativo que contiene los datos del producto a actualizar.
         * 
         * @return array Arreglo asociativo con el resultado de la operación. Contiene:
         *               - "success" (bool): Indica si la actualización fue exitosa.
         *               - "message" (string): Mensaje descriptivo del resultado de la operación.
         */
        public function updateTBCompra($compra) {
            // Verifica que los datos de la compra sean válidos
            $check = $this->validarCompra($compra);
            if (!$check["is_valid"]) {
                return ["success" => false, "message" => $check["message"]];
            }
            return $this->compraData->updateCompra($compra);
        }
        
    
    /**
         * Elimina un producto de la base de datos.
         *
         * @param int $productoID El ID del producto a eliminar.
         * @return array Un arreglo asociativo que indica si la operación fue exitosa y un mensaje.
         *
         * El arreglo de retorno tiene la siguiente estructura:
         * - "success" (bool): Indica si la operación de eliminación fue exitosa.
         * - "message" (string): Un mensaje descriptivo del resultado de la operación.
         */
    public function deleteTBCompra($compraID) {
        $checkID = $this->validarCompraID($compraID);
        if (!$checkID["is_valid"]) {
            return ["success" => $checkID["is_valid"], "message" => $checkID["message"]];
        }
        return $this->compraData->deleteCompra($compraID);
    }


     /**
         * Obtiene todos los productos de la base de datos.
         *
         * @param bool $onlyActive Indica si se deben obtener solo los productos activos. Por defecto es true.
         * @param bool $deleted Indica si se deben incluir los productos eliminados. Por defecto es false.
         * @return array Retorna un arreglo con todos los productos obtenidos.
         */
    public function getAllTBCompra($onlyActive = true, $deleted = false) {
        return $this->compraData->getAllTBCompra($onlyActive, $deleted);
    }

    public function getAllTBCompraDetalleCompra() {
        return $this->compraData->getAllTBCompraDetalleCompra();
    }

    /**
         * Obtiene una lista paginada de productos.
         *
         * @param string $search Término de búsqueda para filtrar productos.
         * @param int $page Número de la página actual.
         * @param int $size Cantidad de productos por página.
         * @param string|null $sort (Opcional) Criterio de ordenación (nombre, precio, cantidad, fechavencimiento, etc...).
         * @param bool $onlyActive (Opcional) Indica si solo se deben incluir productos activos. Por defecto es true.
         * @param bool $deleted (Opcional) Indica si se deben incluir productos eliminados. Por defecto es false.
         * @return array Resultado de la operación con éxito y mensaje o lista de productos paginados.
         */
    public function getPaginatedCompras($search, $page, $size, $sort = null, $onlyActive = true, $deleted = false) {
        $check = $this->validarDatosPaginacion($page, $size);
        if (!$check["is_valid"]) {
            return ["success" => $check["is_valid"], "message" => $check["message"]];
        }
        return $this->compraData->getPaginatedCompras($search, $page, $size, $sort, $onlyActive, $deleted);
    }

    /**
         * Obtiene un producto por su ID.
         *
         * @param int $productoID El ID del producto a obtener.
         * @param bool $onlyActive (Opcional) Indica si solo se deben incluir productos activos. Por defecto es true.
         * @param bool $deleted (Opcional) Indica si se deben incluir productos eliminados. Por defecto es false.
         * @return array Resultado de la operación con éxito y mensaje o el producto obtenido.
         */
    public function getCompraByID($compraID, $onlyActive = true, $deleted = false) {
        // Verifica que el ID del producto sea válido
        $checkID = $this->validarCompraID($compraID);
        if (!$checkID["is_valid"]) {
            return ["success" => $checkID["is_valid"], "message" => $checkID["message"]];
        }
        return $this->compraData->getCompraByID($compraID, $onlyActive, $deleted);
    }

    
}
?>
