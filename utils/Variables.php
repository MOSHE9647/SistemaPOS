<?php

	// ARCHIVO QUE CONTIENE EL NOMBRE DE LAS DISTINTAS VARIABLES
	// ESTÁTICAS QUE SE VAN A ESTAR UTILIZANDO DENTRO DEL PROGRAMA

	// VARIABLES PARA LOG:
	define('DATA_LOG_FILE', 'database-error.log');
	define('BUSINESS_LOG_FILE', 'business-error.log');

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

	// TABLA 'tbImpuesto'
	define('TB_IMPUESTO', 'tbimpuesto'); //<- Nombre de la Tabla
	define('IMPUESTO_ID', 'impuestoid');
	define('IMPUESTO_NOMBRE', 'impuestonombre');
	define('IMPUESTO_VALOR', 'impuestovalor');
	define('IMPUESTO_ESTADO', 'impuestoestado');
	define('IMPUESTO_DESCRIPCION', 'impuestodescripcion');
	define('IMPUESTO_FECHA_VIGENCIA', 'impuestofechavigencia');

	// TABLA 'tbProveedor'
	define('TB_PROVEEDOR', 'tbproveedor'); //<- Nombre de la Tabla
	define('PROVEEDOR_ID', 'proveedorid');
	define('PROVEEDOR_NOMBRE', 'proveedornombre'); 
	define('PROVEEDOR_EMAIL', 'proveedoremail');
	define('PROVEEDOR_TIPO', 'proveedortipo');
	define('PROVEEDOR_ESTADO', 'proveedorestado');
	define('PROVEEDOR_FECHA_REGISTRO', 'proveedorfecharegistro');

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

	// Tabla 'tbproducto'
	define('TB_PRODUCTO','tbproducto'); //<- Nombre de la Tabla
	define('PRODUCTO_ID','productoid');
	define('PRODUCTO_NOMBRE','productonombre');
	define('PRODUCTO_PRECIO_U','productopreciounitario');
	define('PRODUCTO_CANTIDAD','productocantidad');
	define('PRODUCTO_FECHA_ADQ', 'productofechaadquisicion');
	define('PRODUCTO_DESCRIPCION','productodescripcion');
	define('PRODUCTO_CODIGO_BARRAS','productocodigobarras');
	define('PRODUCTO_ESTADO','productoestado');

	// TABLA 'tbCategoria'
	define('TB_CATEGORIA', 'tbcategoria'); //<- Nombre de la Tabla
	define('CATEGORIA_ID', 'categoriaid');
	define('CATEGORIA_NOMBRE', 'categorianombre');
	define('CATEGORIA_ESTADO', 'categoriaestado');

	//Tabla 'tbsubcategoria'
	define('TB_SUBCATEGORIA','tbsubcategoria');
	define('SUBCATEGORIA_ID','tbsubcategoriaid');
	define('SUBCATEGORIA_NOMBRE','tbsubcategorianombre');
	define('SUBCATEGORIA_ESTADO','tbsubcategoriaestado');

	//Tabla 'tbproductosubcategoria'
	define('TB_PRODUCTO_SUBCATEGORIA','tbproductosubcategoria');
	define('PRODUCTO_SUBCATEGORIA_ID','productosubcategoriaid');
	define('PRODUCTO_SUBCATEGORIA_PRODUCTO_ID','productoid');
	define('PRODUCTO_SUBCATEGORIA_SUBCATEGORIA_ID','subcategoriaid');
	define('PRODUCTO_SUBCATEGORIA_ESTADO','productocategoriaestado');
		
	/*************** TABLAS INTERMEDIAS ***************/

	// TABLA INTERMEDIA PARA Producto y Categoria 'tbProductoCategoria'
	define('TB_PRODUCTO_CATEGORIA', 'tbproductocategoria'); //<- Nombre de la Tabla
	define('PRODUCTO_ID_FK', 'productoid'); //<- ID del producto
	define('CATEGORIA_ID_FK', 'categoriaid'); //<- ID de la categoría
	define('PRODUCTO_CATEGORIA_ESTADO', 'productocategoriaestado');

	// TABLA INTERMEDIA PARA Proveedor Y Direccion 'tbProveedorDireccion'
	define('TB_PROVEEDOR_DIRECCION', 'tbproveedordireccion'); //<- Nombre de la Tabla
	define('PROVEEDOR_DIRECCION_ESTADO', 'proveedordireccionestado');
	define('PROVEEDOR_DIRECCION_ID', 'proveedordireccionid');

	// TABLA INTERMEDIA PARA Proveedor Y Telefono 'tbProveedorTelefono'
    define('TB_PROVEEDOR_TELEFONO', 'tbproveedortelefono'); //<- Nombre de la Tabla
    define('PROVEEDOR_TELEFONO_ESTADO', 'proveedortelefonoestado');
    define('PROVEEDOR_TELEFONO_ID', 'proveedortelefonoid');
    define('PROVEEDOR_TELEFONO', 'proveedortelefono');


	// Tabla 'tblote'
	define('TB_LOTE','tblote'); //<- Nombre de la Tabla
	define('LOTE_ID','loteid');
	define('LOTE_CODIGO','lotecodigo');
	define('PRODUCTOLOTE_ID', 'productoid');
	define('LOTE_CANTIDAD','lotecantidad');
	define('LOTE_PRECIO','loteprecio');
	define('PROVEEDORLOTE_ID', 'proveedorid');
	define('LOTE_FECHA_INGRESO','lotefechaingreso');
	define('LOTE_FECHA_VENCIMIENTO','lotefechavencimiento');
	define('LOTE_ESTADO','loteestado');


	//TABLA INTERMEDIA PROVEEDOR-PRODUCTO
    define('TB_PROVEEDOR_PRODUCTO', 'tbproveedorproducto'); // Nombre de la Tabla
    define('PROVEEDOR_PRODUCTO_ID', 'proveedorproductoid'); // ID de la tabla proveedor-producto
    //define('PROVEEDOR_ID', 'proveedorid'); // ID del proveedor
    //define('PRODUCTO_ID', 'productoid'); // ID del producto



?>