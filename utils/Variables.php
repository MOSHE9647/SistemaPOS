<?php

	// ARCHIVO QUE CONTIENE EL NOMBRE DE LAS DISTINTAS VARIABLES
	// ESTÁTICAS QUE SE VAN A ESTAR UTILIZANDO DENTRO DEL PROGRAMA

	// VARIABLES DE LA BD:
	define('DB_NAME', 'bdpuntoventa');
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
	define('TB_DIRECCION', 'tbdireccion');
	define('DIRECCION_ID', 'direccionid');
	define('DIRECCION_PROVINCIA', 'direccionprovincia');
	define('DIRECCION_CANTON', 'direccioncanton');
	define('DIRECCION_DISTRITO', 'direcciondistrito');
	define('DIRECCION_BARRIO', 'direccionbarrio');
	define('DIRECCION_SENNAS', 'direccionsennas');
	define('DIRECCION_DISTANCIA', 'direcciondistancia');
	define('DIRECCION_ESTADO', 'direccionestado');

	// Tabla 'tbproducto'
	define('TB_PRODUCTO','tbproducto');
	define('PRODUCTO_ID','productoid');
	define('PRODUCTO_NOMBRE','productonombre');
	define('PRODUCTO_PRECIO_U','productopreciounitario');
	define('PRODUCTO_CANTIDAD','productocantidad');
	define('PRODUCTO_FECHA_ADQ', 'productofechaadquisicion');
	define('PRODUCTO_DESCRIPCION','productodescripcion');
	define('PRODUCTO_ESTADO','productoestado');


	/*************** TABLAS INTERMEDIAS ***************/

	// TABLA INTERMEDIA PARA Proveedor Y Direccion 'tbProveedorDireccion'
	define('TB_PROVEEDOR_DIRECCION', 'tbproveedordireccion');
	define('PROVEEDOR_DIRECCION_ESTADO', 'proveedordireccionestado');
	define('PROVEEDOR_DIRECCION_ID', 'proveedordireccionid');

	//TABLA PROVEEDORTELEFONO
	// Nombre de la tabla
    public static $tablaProveedorTelefono = "tbproveedortelefono";

    //Columnas
    public static $proveedortelefonoid = "proveedortelefonoid";
    public static $proveedorid = "proveedorid";
    public static $telefono = "telefono";
    public static $activo = "activo";

?>