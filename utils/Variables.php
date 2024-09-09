<?php

	// ARCHIVO QUE CONTIENE EL NOMBRE DE LAS DISTINTAS VARIABLES
	// ESTÁTICAS QUE SE VAN A ESTAR UTILIZANDO DENTRO DEL PROGRAMA

	// VARIABLES PARA LOG:
	define('UTILS_LOG_FILE', 'utils-log.log');
	define('DATA_LOG_FILE', 'database-log.log');
	define('BUSINESS_LOG_FILE', 'business-log.log');
	define('ERROR_MESSAGE', 'ERROR');
	define('INFO_MESSAGE', 'INFO');
	define('WARN_MESSAGE', 'WARN');

	// VARIABLES CLEVER-CLOUD (BD EN LA NUBE)
	define('CLOUD_DB_HOST', 'bdpbhgi0jbzwpoftwisg-mysql.services.clever-cloud.com');
	define('CLOUD_DB_NAME', 'bdpbhgi0jbzwpoftwisg');
	define('CLOUD_DB_USER', 'ucbd34lboyoad3gt');
	define('CLOUD_DB_PASS', 'EleYfGcbzdD9q1DJ11GQ');

	// VARIABLES DE MYSQL (LOCAL):
	define('DB_HOST', 'localhost');
	define('DB_NAME', 'bdpuntoventa');
	define('DB_USER', 'root');
	define('DB_PASS', '#SistemaPOS1234');

	// TABLA 'tbCategoria'
	define('TB_CATEGORIA', 'tbcategoria'); //<- Nombre de la Tabla
	define('CATEGORIA_ID', 'categoriaid');
	define('CATEGORIA_NOMBRE', 'categorianombre');
	define('CATEGORIA_DESCRIPCION', 'categoriadescripcion');
	define('CATEGORIA_ESTADO', 'categoriaestado');

	// TABLA 'tbCodigoBarras'
	define('TB_CODIGO_BARRAS', 'tbcodigobarras'); //<- Nombre de la Tabla
	define('CODIGO_BARRAS_ID', 'codigobarrasid');
	define('CODIGO_BARRAS_NUMERO', 'codigobarrasnumero');
	define('CODIGO_BARRAS_FECHA_CREACION', 'codigobarrasfechacreacion');
	define('CODIGO_BARRAS_FECHA_MODIFICACION', 'codigobarrasfechamodificacion');
	define('CODIGO_BARRAS_ESTADO', 'codigobarrasestado');

	// TABLA 'tbCompra'
	define('TB_COMPRA', 'tbcompra'); //<- Nombre de la Tabla
	define('COMPRA_ID', 'compraid');
	define('COMPRA_NUMERO_FACTURA', 'compranumerofactura');
	define('COMPRA_MONTO_BRUTO', 'compramontobruto');
	define('COMPRA_MONTO_NETO', 'compramontoneto');
	define('COMPRA_TIPO_PAGO', 'compratipopago');
	define('COMPRA_PROVEEDOR_ID', 'compraproveedorid');
	define('COMPRA_FECHA_CREACION', 'comprafechacreacion');
	define('COMPRA_FECHA_MODIFICACION', 'comprafechamodificacion');
	define('COMPRA_ESTADO', 'compraestado');

	// TABLA 'tbCompraDetalle'
	define('TB_COMPRA_DETALLE', 'tbcompradetalle'); //<- Nombre de la Tabla
	define('COMPRA_DETALLE_ID', 'compradetalleid');
	define('COMPRA_DETALLE_COMPRA_ID', 'compradetallecompraid');
	define('COMPRA_DETALLE_LOTE_ID', 'compradetalleloteid');
	define('COMPRA_DETALLE_PRODUCTO_ID', 'compradetalleproductoid');
	define('COMPRA_DETALLE_PRECIO_PRODUCTO', 'compradetalleprecioproducto');
	define('COMPRA_DETALLE_CANTIDAD', 'compradetallecantidad');
	define('COMPRA_DETALLE_FECHA_CREACION', 'compradetallefechacreacion');
	define('COMPRA_DETALLE_FECHA_MODIFICACION', 'compradetallefechamodificacion');
	define('COMPRA_DETALLE_ESTADO', 'compradetalleestado');

	// TABLA 'tbCuentaPorPagar'
	define('TB_CUENTA_POR_PAGAR', 'tbcuentaporpagar');
	define('CUENTA_POR_PAGAR_ID', 'cuentaporpagarid');
	define('CUENTA_POR_PAGAR_COMPRA_DETALLE_ID', 'cuentaporpagarcompradetalleid');
	define('CUENTA_POR_PAGAR_FECHA_VENCIMIENTO', 'cuentaporpagarfechavencimiento');
	define('CUENTA_POR_PAGAR_MONTO_TOTAL', 'cuentaporpagarmontototal');
	define('CUENTA_POR_PAGAR_MONTO_PAGADO', 'cuentaporpagarmontopagado');
	define('CUENTA_POR_PAGAR_FECHA_PAGO', 'cuentaporpagarfechapago');
	define('CUENTA_POR_PAGAR_NOTAS', 'cuentaporpagarnotas');
	define('CUENTA_POR_PAGAR_ESTADO_CUENTA', 'cuentaporpagarestadocuenta'); //<- Pendiente, Pagada, Vencida
	define('CUENTA_POR_PAGAR_ESTADO', 'cuentaporpagarestado');

	// TABLA 'tbDireccion'
	define('TB_DIRECCION', 'tbdireccion'); //<- Nombre de la Tabla
	define('DIRECCION_ID', 'direccionid');
	define('DIRECCION_PROVINCIA', 'direccionprovincia');
	define('DIRECCION_CANTON', 'direccioncanton');
	define('DIRECCION_DISTRITO', 'direcciondistrito');
	define('DIRECCION_BARRIO', 'direccionbarrio');
	define('DIRECCION_SENNAS', 'direccionsennas');
	define('DIRECCION_DISTANCIA', 'direcciondistancia');
	define('DIRECCION_ESTADO', 'direccionestado');

	// TABLA 'tbImpuesto'
	define('TB_IMPUESTO', 'tbimpuesto'); //<- Nombre de la Tabla
	define('IMPUESTO_ID', 'impuestoid');
	define('IMPUESTO_NOMBRE', 'impuestonombre');
	define('IMPUESTO_VALOR', 'impuestovalor');
	define('IMPUESTO_DESCRIPCION', 'impuestodescripcion');
	define('IMPUESTO_FECHA_VIGENCIA', 'impuestofechavigencia');
	define('IMPUESTO_ESTADO', 'impuestoestado');

	// Tabla 'tbLote'
	define('TB_LOTE','tblote'); //<- Nombre de la Tabla
	define('LOTE_ID','loteid');
	define('LOTE_CODIGO','lotecodigo');
	define('LOTE_FECHA_VENCIMIENTO','lotefechavencimiento');
	define('LOTE_ESTADO','loteestado');

	// Tabla 'tbProducto'
	define('TB_PRODUCTO','tbproducto'); //<- Nombre de la Tabla
	define('PRODUCTO_ID','productoid');
	define('PRODUCTO_NOMBRE','productonombre');
	define('PRODUCTO_PRECIO_COMPRA','productopreciocompra');
	define('PRODUCTO_PORCENTAJE_GANANCIA','productoporcentajeganancia');
	define('PRODUCTO_DESCRIPCION','productodescripcion');
	define('PRODUCTO_CODIGO_BARRAS_ID','productocodigobarrasid');
	define('PRODUCTO_IMAGEN', 'productoimagen');
	define('PRODUCTO_ESTADO','productoestado');

	// TABLA 'tbProveedor'
	define('TB_PROVEEDOR', 'tbproveedor'); //<- Nombre de la Tabla
	define('PROVEEDOR_ID', 'proveedorid');
	define('PROVEEDOR_NOMBRE', 'proveedornombre'); 
	define('PROVEEDOR_EMAIL', 'proveedoremail');
	define('PROVEEDOR_FECHA_REGISTRO', 'proveedorfecharegistro');
	define('PROVEEDOR_ESTADO', 'proveedorestado');

	// TABLA 'tbRol'
	define('TB_ROL', 'tbrol'); //<- Nombre de la Tabla
	define('ROL_ID', 'rolid');
	define('ROL_NOMBRE', 'rolnombre');
	define('ROL_DESCRIPCION', 'roldescripcion');
	define('ROL_ESTADO', 'rolestado');

	//Tabla 'tbSubCategoria'
	define('TB_SUBCATEGORIA','tbsubcategoria');
	define('SUBCATEGORIA_ID','subcategoriaid');
	define('SUBCATEGORIA_NOMBRE','subcategorianombre');
	define('SUBCATEGORIA_DESCRIPCION','subcategoriadescripcion');
	define('SUBCATEGORIA_ESTADO','subcategoriaestado');

	// TABLA 'tbTelefono'
	define('TB_TELEFONO', 'tbtelefono'); //<- Nombre de la Tabla
	define('TELEFONO_ID', 'telefonoid');
	define('TELEFONO_PROVEEDOR_ID', 'telefonoproveedorid');
	define('TELEFONO_TIPO', 'telefonotipo');
	define('TELEFONO_CODIGO_PAIS', 'telefonocodigopais');
	define('TELEFONO_NUMERO', 'telefononumero');
	define('TELEFONO_EXTENSION', 'telefonoextension');
	define('TELEFONO_FECHA_CREACION', 'telefonofechacreacion');
	define('TELEFONO_FECHA_MODIFICACION', 'telefonofechamodificacion');
	define('TELEFONO_ESTADO', 'telefonoestado');

	// TABLA 'tbUsuario'
	define('TB_USUARIO', 'tbusuario'); //<- Nombre de la Tabla
	define('USUARIO_ID', 'usuarioid');
	define('USUARIO_NOMBRE', 'usuarionombre');
	define('USUARIO_PRIMER_APELLIDO', 'usuarioprimerapellido');
	define('USUARIO_SEGUNDO_APELLIDO', 'usuariosegundoapellido');
	define('USUARIO_ROL_ID', 'usuariorolid');
	define('USUARIO_EMAIL', 'usuarioemail');
	define('USUARIO_PASSWORD', 'usuariopassword');
	define('USUARIO_NICKNAME', 'usuarionickname'); //<- 3 letras Nombre + 3 letras 1er Apellido + 3 letras 2do Apellido
	define('USUARIO_FECHA_CREACION', 'usuariofechacreacion');
	define('USUARIO_FECHA_MODIFICACION', 'usuariofechamodificacion');
	define('USUARIO_ESTADO', 'usuarioestado');
		
	/*************** TABLAS INTERMEDIAS ***************/

	// TABLA INTERMEDIA PARA Producto y Categoria 'tbProductoCategoria'
	define('TB_PRODUCTO_CATEGORIA', 'tbproductocategoria'); //<- Nombre de la Tabla
	define('PRODUCTO_CATEGORIA_ID', 'productocategoriaid'); //<- ID de la tabla intermedia
	define('PRODUCTO_CATEGORIA_ESTADO', 'productocategoriaestado');

	// TABLA INTERMEDIA PARA Producto y Subcategoria 'tbproductosubcategoria'
	define('TB_PRODUCTO_SUBCATEGORIA','tbproductosubcategoria');
	define('PRODUCTO_SUBCATEGORIA_ID','productosubcategoriaid');
	define('PRODUCTO_SUBCATEGORIA_ESTADO','productosubcategoriaestado');

	// TABLA INTERMEDIA PARA Prveedor y Categoria 'tbproveedorcategoria'
	define('TB_PROVEEDOR_SUBCATEGORIA','tbproveedorcategoria');
	define('PROVEEDOR_SUBCATEGORIA_ID','proveedorcategoriaid');
	define('PROVEEDOR_SUBCATEGORIA_ESTADO','proveedorcategoriaestado');

	// TABLA INTERMEDIA PARA Proveedor Y Direccion 'tbProveedorDireccion'
	define('TB_PROVEEDOR_DIRECCION', 'tbproveedordireccion'); //<- Nombre de la Tabla
	define('PROVEEDOR_DIRECCION_ID', 'proveedordireccionid');
	define('PROVEEDOR_DIRECCION_ESTADO', 'proveedordireccionestado');

	//TABLA INTERMEDIA PROVEEDOR-PRODUCTO
    define('TB_PROVEEDOR_PRODUCTO', 'tbproveedorproducto'); // Nombre de la Tabla
    define('PROVEEDOR_PRODUCTO_ID', 'proveedorproductoid'); // ID de la tabla proveedor-producto
	define('PROVEEDOR_PRODUCTO_ESTADO', 'proveedorproductoestado');

	//TABLA INTERMEDIA PARA Proveedor Y Telefono 'tbProveedorTelefono'
    define('TB_PROVEEDOR_TELEFONO', 'tbproveedortelefono'); // Nombre de la Tabla
    define('PROVEEDOR_TELEFONO_ID', 'proveedortelefonoid'); // ID de la tabla proveedor-telefono
	define('PROVEEDOR_TELEFONO_ESTADO', 'proveedortelefonoestado');

?>