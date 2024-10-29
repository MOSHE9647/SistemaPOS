<?php
require_once __DIR__ . "/../data/ventaData.php";
require_once dirname(__DIR__, 1) . '/utils/Utils.php';

Class VentaBusiness {
    private $className;     //<- Variable para almacenar el nombre de la clase
    private $ventaData;

    public function __construct() {
        $this->ventaData = new VentaData();
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
        public function validarVentaID($ventaID) {
            if ($ventaID === null || !is_numeric($ventaID) || $ventaID < 0) {
                Utils::writeLog("El ID [$ventaID] de la venta no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ["is_valid" => false, "message" => "El ID de la venta está vacío o no es válido. Revise que este sea un número y que sea mayor a 0"];
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
                Utils::writeLog("El 'page [$page]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ["is_valid" => false, "message" => "El número de página está vacío o no es válido. Revise que este sea un número y que sea mayor o igual a 0"];
            }

            if ($size === null || !is_numeric($size) || $size < 0) {
                Utils::writeLog("El 'size [$size]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE, $this->className);
                return ["is_valid" => false, "message" => "El tamaño de la página está vacío o no es válido. Revise que este sea un número y que sea mayor o igual a 0"];
            }

            return ["is_valid" => true];
        }

         /**
         * Valida los datos de un producto.
         *
         * @param Venta $producto El producto a validar.
         * @param bool $validarCamposAdicionales Indica si se deben validar los campos adicionales del producto.
         * @param bool $insert Indica si se está insertando un nuevo producto.
         * 
         * @return array Un arreglo asociativo que indica si los datos del producto son válidos y un mensaje en caso de error.
         *               - "is_valid" (bool): Indica si los datos del producto son válidos.
         *               - "message" (string): Mensaje de error si los datos del producto no son válidos.
         */

         public function validarVenta($venta, $validarCamposAdicionales = true, $insert = false) {
            try {
                // Obtener los valores de las propiedades del objeto
                $ventaID = $venta->getVentaID();
                $ventaCliente = $venta->getVentaCliente();
                $ventaNumeroFactura = $venta->getVentaNumeroFactura();
                $ventaMoneda = $venta->getVentaMoneda();
                $ventaMontoBruto = $venta->getVentaMontoBruto();
                $ventaMontoNeto = $venta->getVentaMontoNeto();
                $ventaMontoImpuesto = $venta->getVentaMontoImpuesto();
                $ventaCondicionVenta =  $venta->getVentaCondicionVenta();
                $ventaTipoPago =  $venta->getVentaTipoPago();
                $errors = [];
                
    
                 // Verifica que el ID del producto sea válido
                 if (!$insert) {
                    $checkID = $this->validarVentaID($ventaID);
                    if (!$checkID['is_valid']) { $errors[] = $checkID['message']; }
                }
    
                // Si la validación de campos adicionales está activada, valida los otros campos
                if ($validarCamposAdicionales) {
                    
                    
                   // if ($ventaCliente === null || !is_numeric($ventaCliente) || $ventaCliente < 0) {
                     //   $errors[] = "El campo 'Cliente' está vacío.";
                       // Utils::writeLog("El campo 'Cliente ID [$ventaCliente]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE,  $this->className);
                  // }

                    if (empty($ventaNumeroFactura)) {
                        $errors[] = "El campo 'Número de factura' está vacío.";
                        Utils::writeLog("El campo 'Número de factura [$ventaNumeroFactura]' está vacío.", BUSINESS_LOG_FILE, ERROR_MESSAGE,  $this->className);
                    }
    
                    if ($ventaMoneda === null ) {
                        $errors[] = "El campo 'Modena' no puede ser negativo vacío.";
                        Utils::writeLog("El campo 'Modena [$ventaMoneda]' no es valido.", BUSINESS_LOG_FILE, ERROR_MESSAGE,  $this->className);
                    }
    
                    if ($ventaMontoBruto === null) {
                        $errors[] = "El campo 'Monto bruto' está vacío.";
                        Utils::writeLog("El campo 'Monto bruto [$ventaMontoBruto]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE,  $this->className);
                    }
                    if ($ventaMontoBruto < 0) {
                        $errors[] = "El campo 'Monto bruto' tiene que ser positivo.";
                        Utils::writeLog("El campo 'Monto bruto [$ventaMontoBruto]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE,  $this->className);
                    }
    
                    if ($ventaMontoNeto === null) {
                        $errors[] = "El campo 'Monto neto' está vacío.";
                        Utils::writeLog("El campo 'Monto neto [$ventaMontoNeto]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE,  $this->className);
                    }
    
                    if ($ventaMontoNeto < 0) {
                        $errors[] = "El campo 'Monto neto' tiene que ser positivo.";
                        Utils::writeLog("El campo 'Monto neto [$ventaMontoNeto]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE,  $this->className);
                    }
    
                    if (empty($ventaMontoImpuesto)) {
                        $errors[] = "El campo 'Tipo de pago' está vacío.";
                        Utils::writeLog("El campo 'Tipo de pago [$ventaMontoImpuesto]' está vacío.", BUSINESS_LOG_FILE, ERROR_MESSAGE,  $this->className);
                    }
                    
                    if ($ventaCondicionVenta < 0) {
                        $errors[] = "El campo 'Monto neto' tiene que ser positivo.";
                        Utils::writeLog("El campo 'Monto neto [$ventaMontoImpuesto]' no es válido.", BUSINESS_LOG_FILE, ERROR_MESSAGE,  $this->className);
                    }
    
                    if (empty($ventaTipoPago || !is_numeric ($ventaTipoPago))) {
                        $errors[] = "El campo 'Tipo de pago' está vacío.";
                        Utils::writeLog("El campo 'Tipo de pago [$ventaTipoPago]' está vacío.", BUSINESS_LOG_FILE, ERROR_MESSAGE,  $this->className);
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

         public function insertTBVenta($venta) {
            // Verifica que los datos de la compra sean válidos
            $check = $this->validarVenta($venta, true, true);
            if (!$check["is_valid"]) {
                return ["success" => false, "message" => $check["message"]];
            }
            return $this->ventaData->insertVenta($venta);
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
            public function updateTBVenta($venta) {
                // Verifica que los datos de la compra sean válidos
                $check = $this->validarVenta($venta);
                if (!$check["is_valid"]) {
                    return ["success" => false, "message" => $check["message"]];
                }
                return $this->ventaData->updateVenta($venta);
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
        public function deleteTBVenta($ventaID) {
            $checkID = $this->validarVentaID($ventaID);
            if (!$checkID["is_valid"]) {
                return ["success" => $checkID["is_valid"], "message" => $checkID["message"]];
            }
            return $this->ventaData->deleteVenta($ventaID);
        }
    
    
         /**
             * Obtiene todos los productos de la base de datos.
             *
             * @param bool $onlyActive Indica si se deben obtener solo los productos activos. Por defecto es true.
             * @param bool $deleted Indica si se deben incluir los productos eliminados. Por defecto es false.
             * @return array Retorna un arreglo con todos los productos obtenidos.
             */
        public function getAllTBVenta($onlyActive = true, $deleted = false) {
            return $this->ventaData->getAllTBVenta($onlyActive, $deleted);
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
        public function getPaginatedVentas($search, $page, $size, $sort = null, $onlyActive = true, $deleted = false) {
            $check = $this->validarDatosPaginacion($page, $size);
            if (!$check["is_valid"]) {
                return ["success" => $check["is_valid"], "message" => $check["message"]];
            }
            return $this->ventaData->getPaginatedVentas($search, $page, $size, $sort, $onlyActive, $deleted);
        }
    
        /**
             * Obtiene un producto por su ID.
             *
             * @param int $productoID El ID del producto a obtener.
             * @param bool $onlyActive (Opcional) Indica si solo se deben incluir productos activos. Por defecto es true.
             * @param bool $deleted (Opcional) Indica si se deben incluir productos eliminados. Por defecto es false.
             * @return array Resultado de la operación con éxito y mensaje o el producto obtenido.
             */
        public function getVentaByID($ventaID, $onlyActive = true, $deleted = false) {
            // Verifica que el ID del producto sea válido
            $checkID = $this->validarVentaID($ventaID);
            if (!$checkID["is_valid"]) {
                return ["success" => $checkID["is_valid"], "message" => $checkID["message"]];
            }
            return $this->ventaData->getVentaByID($ventaID, $onlyActive, $deleted);
        }
    
        
    }
    ?>
    