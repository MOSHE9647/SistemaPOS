<?php

	/* 
	 * ARCHIVO QUE CONTIENE EL NOMBRE DE LAS DISTINTAS VARIABLES
	 * ESTÁTICAS QUE SE VAN A ESTAR UTILIZANDO DENTRO DEL PROGRAMA
	*/

	// VARIABLES PARA EL MANEJO DE SESIONES:
	define('SESSION_AUTHENTICATED_USER', 'usuario_autenticado'); //<- Usuario Autenticado
	define('SESSION_AUTHENTICATED', 'autenticado'); //<- Sesión Autenticada
	define('SESSION_ACCESS_DENIED', 'acceso_denegado'); //<- Acceso Denegado
	define('SESSION_LOGGED_OUT', 'logout'); //<- Sesión Cerrada
	define('SESSION_ORIGIN_URL', 'url_origen'); //<- URL de Origen

	// VARIABLES PARA ROLES DE USUARIO:
	define('ROL_ADMIN', 1); //<- Rol de Administrador
	define('ROL_DEPENDIENTE', 2); //<- Rol de Dependiente
	define('ROL_CLIENTE', 3); //<- Rol de Cliente

	// VARIABLES PARA ENLACES DE PÁGINAS:
	define('DEFAULT_PRODUCT_IMAGE', '/view/static/img/product.webp'); //<- Imagen por defecto de los productos

	// VARIABLES PARA LOG:
	define('UTILS_LOG_FILE', 'utils-log.log'); //<- Nombre del Archivo de Log
	define('DATA_LOG_FILE', 'database-log.log'); //<- Nombre del Archivo de Log
	define('BUSINESS_LOG_FILE', 'business-log.log'); //<- Nombre del Archivo de Log
	define('CONTROLLER_LOG_FILE', 'controller-log.log'); //<- Nombre del Archivo de Log
	define('ERROR_MESSAGE', 'ERROR'); //<- Tipo de Mensaje de Error
	define('INFO_MESSAGE', 'INFO'); //<- Tipo de Mensaje de Información
	define('WARN_MESSAGE', 'WARN'); //<- Tipo de Mensaje de Advertencia

	// VARIABLES CLEVER-CLOUD (BD EN LA NUBE)
	define('CLOUD_DB_HOST', 'bdpbhgi0jbzwpoftwisg-mysql.services.clever-cloud.com'); //<- Host de la Base de Datos
	define('CLOUD_DB_NAME', 'bdpbhgi0jbzwpoftwisg'); //<- Nombre de la Base de Datos
	define('CLOUD_DB_USER', 'ucbd34lboyoad3gt'); //<- Usuario de la Base de Datos
	define('CLOUD_DB_PASS', 'EleYfGcbzdD9q1DJ11GQ'); //<- Contraseña de la Base de Datos

	// VARIABLES DE MYSQL (LOCAL):
	define('DB_HOST', 'localhost'); //<- Host de la Base de Datos
	define('DB_NAME', 'bdpuntoventa'); //<- Nombre de la Base de Datos
	define('DB_USER', 'root'); //<- Usuario de la Base de Datos
	define('DB_PASS', '#SistemaPOS1234'); //<- Contraseña de la Base de Datos

	// TABLA 'tbCategoria'
	define('TB_CATEGORIA', 'tbcategoria'); //<- Nombre de la Tabla
	define('CATEGORIA_ID', 'categoriaid'); //<- ID de la Categoría
	define('CATEGORIA_NOMBRE', 'categorianombre'); //<- Nombre de la Categoría
	define('CATEGORIA_DESCRIPCION', 'categoriadescripcion'); //<- Descripción de la Categoría
	define('CATEGORIA_ESTADO', 'categoriaestado'); //<- Estado de la Categoría

	// TABLA 'tbCliente'
	define('TB_CLIENTE', 'tbcliente'); //<- Nombre de la Tabla
	define('CLIENTE_ID', 'clienteid'); //<- ID del Cliente
	define('CLIENTE_NOMBRE', 'clientenombre'); //<- Nombre del Cliente
	define('CLIENTE_ALIAS', 'clientealias'); //<- Alias del Cliente
	define('CLIENTE_USUARIO_ID', 'clienteusuarioid'); //<- ID del Usuario del Cliente
	define('CLIENTE_TELEFONO_ID', 'clientetelefonoid'); //<- ID del Teléfono del Cliente
	define('CLIENTE_FECHA_CREACION', 'clientefechacreacion'); //<- Fecha de Creación del Cliente
	define('CLIENTE_FECHA_MODIFICACION', 'clientefechamodificacion'); //<- Fecha de Modificación del Cliente
	define('CLIENTE_ESTADO', 'clienteestado'); //<- Estado del Cliente

	// TABLA 'tbCodigoBarras'
	define('TB_CODIGO_BARRAS', 'tbcodigobarras'); //<- Nombre de la Tabla
	define('CODIGO_BARRAS_ID', 'codigobarrasid'); //<- ID del Código de Barras
	define('CODIGO_BARRAS_NUMERO', 'codigobarrasnumero'); //<- Número del Código de Barras
	define('CODIGO_BARRAS_FECHA_CREACION', 'codigobarrasfechacreacion'); //<- Fecha de Creación del Código de Barras
	define('CODIGO_BARRAS_FECHA_MODIFICACION', 'codigobarrasfechamodificacion'); //<- Fecha de Modificación del Código de Barras
	define('CODIGO_BARRAS_ESTADO', 'codigobarrasestado'); //<- Estado del Código de Barras

	// TABLA 'tbCompra'
	define('TB_COMPRA', 'tbcompra'); //<- Nombre de la Tabla
	define('COMPRA_ID', 'compraid'); //<- ID de la Compra
	define('COMPRA_NUMERO_FACTURA', 'compranumerofactura'); //<- Número de Factura de la Compra
	define('COMPRA_MONTO_BRUTO', 'compramontobruto'); //<- Monto Bruto de la Compra
	define('COMPRA_MONTO_NETO', 'compramontoneto'); //<- Monto Neto de la Compra
	define('COMPRA_TIPO_PAGO', 'compratipopago'); //<- Tipo de Pago de la Compra
	define('COMPRA_PROVEEDOR_ID', 'compraproveedorid'); //<- ID del Proveedor de la Compra
	define('COMPRA_FECHA_CREACION', 'comprafechacreacion'); //<- Fecha de Creación de la Compra
	define('COMPRA_FECHA_MODIFICACION', 'comprafechamodificacion'); //<- Fecha de Modificación de la Compra
	define('COMPRA_ESTADO', 'compraestado'); //<- Estado de la Compra

	// TABLA 'tbCompraDetalle'
	define('TB_COMPRA_DETALLE', 'tbcompradetalle'); //<- Nombre de la Tabla
	define('COMPRA_DETALLE_ID', 'compradetalleid'); //<- ID del Detalle de la Compra
	define('COMPRA_DETALLE_COMPRA_ID', 'compradetallecompraid'); //<- ID de la Compra del Detalle
	define('COMPRA_DETALLE_PRODUCTO_ID', 'compradetalleproductoid'); //<- ID del Producto del Detalle
	define('COMPRA_DETALLE_FECHA_CREACION', 'compradetallefechacreacion'); //<- Fecha de Creación del Detalle
	define('COMPRA_DETALLE_FECHA_MODIFICACION', 'compradetallefechamodificacion'); //<- Fecha de Modificación del Detalle
	define('COMPRA_DETALLE_ESTADO', 'compradetalleestado'); //<- Estado del Detalle
	// ** Eliminado de la tabla de forma temporal **/
	// Estos datos ya los tiene Producto
	define('COMPRA_DETALLE_PRECIO_PRODUCTO', 'compradetalleprecioproducto'); //<- Precio del Producto del Detalle
	define('COMPRA_DETALLE_CANTIDAD', 'compradetallecantidad'); //<- Cantidad del Producto del Detalle

	// TABLA 'tbCompraPorPagar'
	define('TB_COMPRA_POR_PAGAR', 'tbcompraporpagar'); //<- Nombre de la Tabla
	define('COMPRA_POR_PAGAR_ID', 'compraporpagarid'); //<- ID de la Compra por Pagar
	define('COMPRA_POR_PAGAR_COMPRA_DETALLE_ID', 'compraporpagarcompradetalleid'); //<- ID del Detalle de la Compra por Pagar
	define('COMPRA_POR_PAGAR_FECHA_VENCIMIENTO', 'compraporpagarfechavencimiento'); //<- Fecha de Vencimiento de la Compra por Pagar
	define('COMPRA_POR_PAGAR_MONTO_TOTAL', 'compraporpagarmontototal'); //<- Monto Total de la Compra por Pagar
	define('COMPRA_POR_PAGAR_MONTO_PAGADO', 'compraporpagarmontopagado'); //<- Monto Pagado de la Compra por Pagar
	define('COMPRA_POR_PAGAR_FECHA_PAGO', 'compraporpagarfechapago'); //<- Fecha de Pago de la Compra por Pagar
	define('COMPRA_POR_PAGAR_ESTADO_COMPRA', 'compraporpagarestadocompra'); //<- Pendiente, Pagada, Vencida
	define('COMPRA_POR_PAGAR_NOTAS', 'compraporpagarnotas'); //<- Notas de la Compra por Pagar
	define('COMPRA_POR_PAGAR_ESTADO', 'compraporpagarestado'); //<- Estado de la Compra por Pagar

	// TABLA 'tbDireccion'
	define('TB_DIRECCION', 'tbdireccion'); //<- Nombre de la Tabla
	define('DIRECCION_ID', 'direccionid'); //<- ID de la Dirección
	define('DIRECCION_PROVINCIA', 'direccionprovincia'); //<- Provincia de la Dirección
	define('DIRECCION_CANTON', 'direccioncanton'); //<- Cantón de la Dirección
	define('DIRECCION_DISTRITO', 'direcciondistrito'); //<- Distrito de la Dirección
	define('DIRECCION_BARRIO', 'direccionbarrio'); //<- Barrio de la Dirección
	define('DIRECCION_SENNAS', 'direccionsennas'); //<- Señas de la Dirección
	define('DIRECCION_DISTANCIA', 'direcciondistancia'); //<- Distancia de la Dirección
	define('DIRECCION_ESTADO', 'direccionestado'); //<- Estado de la Dirección

	// TABLA 'tbImpuesto'
	define('TB_IMPUESTO', 'tbimpuesto'); //<- Nombre de la Tabla
	define('IMPUESTO_ID', 'impuestoid'); //<- ID del Impuesto
	define('IMPUESTO_NOMBRE', 'impuestonombre'); //<- Nombre del Impuesto (IVA, ISV, etc.)
	define('IMPUESTO_VALOR', 'impuestovalor'); //<- Valor del Impuesto
	define('IMPUESTO_DESCRIPCION', 'impuestodescripcion'); //<- Descripción del Impuesto
	define('IMPUESTO_FECHA_INICIO_VIGENCIA', 'impuestofechainiciovigencia'); //<- Fecha de Inicio de Vigencia del Impuesto
	define('IMPUESTO_FECHA_FIN_VIGENCIA', 'impuestofechafinvigencia'); //<- Fecha de Fin de Vigencia del Impuesto
	define('IMPUESTO_ESTADO', 'impuestoestado'); //<- Estado del Impuesto

	// TABLA 'tbMarca'
	define('TB_MARCA', 'tbmarca'); //<- Nombre de la Tabla
	define('MARCA_ID', 'marcaid'); //<- ID de la Marca (DosPinos, CocaCola, etc.)
	define('MARCA_NOMBRE', 'marcanombre'); //<- Nombre de la Marca
	define('MARCA_DESCRIPCION', 'marcadescripcion'); //<- Descripción de la Marca
	define('MARCA_ESTADO', 'marcaestado'); //<- Estado de la Marca

	// TABLA 'tbPresentacion'
	define('TB_PRESENTACION', 'tbpresentacion'); //<- Nombre de la Tabla
	define('PRESENTACION_ID', 'presentacionid'); //<- ID de la Presentación
	define('PRESENTACION_NOMBRE', 'presentacionnombre'); //<- Nombre de la Presentación (Litros, Mililitros, etc.)
	define('PRESENTACION_DESCRIPCION', 'presentaciondescripcion'); //<- Descripción de la Presentación
	define('PRESENTACION_ESTADO', 'presentacionestado'); //<- Estado de la Presentación

	// Tabla 'tbProducto'
	define('TB_PRODUCTO','tbproducto'); //<- Nombre de la Tabla
	define('PRODUCTO_ID','productoid'); //<- ID del Producto
	define('PRODUCTO_CODIGO_BARRAS_ID','productocodigobarrasid'); //<- ID del Código de Barras del Producto
	define('PRODUCTO_NOMBRE','productonombre'); //<- Nombre del Producto
	define('PRODUCTO_CANTIDAD','productocantidad'); //<- Cantidad del Producto
	define('PRODUCTO_PRECIO_COMPRA','productopreciocompra'); //<- Precio de Compra del Producto
	define('PRODUCTO_PORCENTAJE_GANANCIA','productoporcentajeganancia'); //<- Porcentaje de Ganancia del Producto
	define('PRODUCTO_DESCRIPCION','productodescripcion'); //<- Descripción del Producto
	define('PRODUCTO_CATEGORIA_ID','productocategoriaid'); //<- ID de la Categoría del Producto
	define('PRODUCTO_SUBCATEGORIA_ID','productosubcategoriaid'); //<- ID de la Subcategoría del Producto
	define('PRODUCTO_MARCA_ID','productomarcaid'); //<- ID de la Marca del Producto
	define('PRODUCTO_PRESENTACION_ID','productopresentacionid'); //<- ID de la Presentación del Producto
	define('PRODUCTO_IMAGEN', 'productoimagen'); //<- Imagen del Producto
	define('PRODUCTO_FECHA_VENCIMIENTO','productofechavencimiento'); //<- Fecha de Vencimiento del Producto
	define('PRODUCTO_ESTADO','productoestado'); //<- Estado del Producto

	// TABLA 'tbProveedor'
	define('TB_PROVEEDOR', 'tbproveedor'); //<- Nombre de la Tabla
	define('PROVEEDOR_ID', 'proveedorid'); //<- ID del Proveedor
	define('PROVEEDOR_NOMBRE', 'proveedornombre'); //<- Nombre del Proveedor
	define('PROVEEDOR_EMAIL', 'proveedoremail'); //<- Email del Proveedor
	define('PROVEEDOR_CATEGORIA_ID', 'proveedorcategoriaid'); //<- ID de la Categoría del Proveedor
	define('PROVEEDOR_FECHA_CREACION', 'proveedorfechacreacion'); //<- Fecha de Creación del Proveedor
	define('PROVEEDOR_FECHA_MODIFICACION', 'proveedorfechamodificacion'); //<- Fecha de Modificación del Proveedor
	define('PROVEEDOR_ESTADO', 'proveedorestado'); //<- Estado del Proveedor

	// TABLA 'tbRolUsuario'
	define('TB_ROL', 'tbrolusuario'); //<- Nombre de la Tabla
	define('ROL_ID', 'rolusuarioid'); //<- ID del Rol de Usuario
	define('ROL_NOMBRE', 'rolusuarionombre'); //<- Nombre del Rol de Usuario
	define('ROL_DESCRIPCION', 'rolusuariodescripcion'); //<- Descripción del Rol de Usuario
	define('ROL_ESTADO', 'rolusuarioestado'); //<- Estado del Rol de Usuario

	//Tabla 'tbSubCategoria'
	define('TB_SUBCATEGORIA','tbsubcategoria'); //<- Nombre de la Tabla
	define('SUBCATEGORIA_ID','subcategoriaid'); //<- ID de la Subcategoría
	define('SUBCATEGORIA_CATEGORIA_ID','subcategoriacategoriaid'); //<- ID de la Categoría de la Subcategoría
	define('SUBCATEGORIA_NOMBRE','subcategorianombre'); //<- Nombre de la Subcategoría
	define('SUBCATEGORIA_DESCRIPCION','subcategoriadescripcion'); //<- Descripción de la Subcategoría
	define('SUBCATEGORIA_ESTADO','subcategoriaestado'); //<- Estado de la Subcategoría

	// TABLA 'tbTelefono'
	define('TB_TELEFONO', 'tbtelefono'); //<- Nombre de la Tabla
	define('TELEFONO_ID', 'telefonoid'); //<- ID del Teléfono
	define('TELEFONO_TIPO', 'telefonotipo'); //<- Tipo de Teléfono (Móvil, Fijo, Fax, etc.)
	define('TELEFONO_CODIGO_PAIS', 'telefonocodigopais'); //<- Código de País del Teléfono
	define('TELEFONO_NUMERO', 'telefononumero'); //<- Número del Teléfono
	define('TELEFONO_EXTENSION', 'telefonoextension'); //<- Extensión del Teléfono
	define('TELEFONO_FECHA_CREACION', 'telefonofechacreacion'); //<- Fecha de Creación del Teléfono
	define('TELEFONO_FECHA_MODIFICACION', 'telefonofechamodificacion'); //<- Fecha de Modificación del Teléfono
	define('TELEFONO_ESTADO', 'telefonoestado'); //<- Estado del Teléfono

	// TABLA 'tbUsuario'
	define('TB_USUARIO', 'tbusuario'); //<- Nombre de la Tabla
	define('USUARIO_ID', 'usuarioid'); //<- ID del Usuario
	define('USUARIO_NOMBRE', 'usuarionombre'); //<- Nombre del Usuario
	define('USUARIO_APELLIDO_1', 'usuarioapellido1'); //<- Primer Apellido del Usuario
	define('USUARIO_APELLIDO_2', 'usuarioapellido2'); //<- Segundo Apellido del Usuario
	define('USUARIO_ROL_ID', 'usuariorolusuarioid'); //<- ID del Rol del Usuario
	define('USUARIO_EMAIL', 'usuarioemail'); //<- Email del Usuario
	define('USUARIO_PASSWORD', 'usuariopassword'); //<- Contraseña del Usuario
	define('USUARIO_FECHA_CREACION', 'usuariofechacreacion'); //<- Fecha de Creación del Usuario
	define('USUARIO_FECHA_MODIFICACION', 'usuariofechamodificacion'); //<- Fecha de Modificación del Usuario
	define('USUARIO_ESTADO', 'usuarioestado'); //<- Estado del Usuario

	// ** FALTA DE AGREGAR LAS TABLAS RELACIONADAS A LA VENTA ** //
	// TABLA 'tbVenta'
	// TABLA 'tbVentaDetalle'
	// TABLA 'tbFacturaTemporal'
	// TABLA 'tbCuentaPorCobrar'
		
	/*************** TABLAS INTERMEDIAS ***************/

	// TABLA INTERMEDIA PARA Proveedor Y Direccion 'tbProveedorDireccion'
	define('TB_PROVEEDOR_DIRECCION', 'tbproveedordireccion'); //<- Nombre de la Tabla
	define('PROVEEDOR_DIRECCION_ID', 'proveedordireccionid'); //<- ID de la tabla proveedor-direccion
	define('PROVEEDOR_DIRECCION_ESTADO', 'proveedordireccionestado'); //<- Estado de la tabla proveedor-direccion

	//TABLA INTERMEDIA PARA Proveedor Y Producto 'tbProveedorProducto
    define('TB_PROVEEDOR_PRODUCTO', 'tbproveedorproducto'); // Nombre de la Tabla
    define('PROVEEDOR_PRODUCTO_ID', 'proveedorproductoid'); // ID de la tabla proveedor-producto
	define('PROVEEDOR_PRODUCTO_ESTADO', 'proveedorproductoestado'); // Estado de la tabla proveedor-producto

	//TABLA INTERMEDIA PARA Proveedor Y Telefono 'tbProveedorTelefono'
    define('TB_PROVEEDOR_TELEFONO', 'tbproveedortelefono'); // Nombre de la Tabla
    define('PROVEEDOR_TELEFONO_ID', 'proveedortelefonoid'); // ID de la tabla proveedor-telefono
	define('PROVEEDOR_TELEFONO_ESTADO', 'proveedortelefonoestado'); // Estado de la tabla proveedor-telefono

?>