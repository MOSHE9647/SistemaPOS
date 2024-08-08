<?php

    // ARCHIVO QUE CONTIENE EL NOMBRE DE LAS DISTINTAS VARIABLES
    // ESTÁTICAS QUE SE VAN A ESTAR UTILIZANDO DENTRO DEL PROGRAMA

    // NOMBRE DE LA BD:
    define('DB_NAME', 'bdpuntoventa');

    // TABLA 'tbImpuesto'
    define('TB_IMPUESTO', 'tbimpuesto'); //<- Nombre de la Tabla
    define('IMPUESTO_ID', 'impuestoid');
    define('IMPUESTO_NOMBRE', 'impuestonombre');
    define('IMPUESTO_VALOR', 'impuestovalor');
    define('IMPUESTO_ESTADO', 'impuestoestado');
    define('IMPUESTO_DESCRIPCION', 'impuestodescripcion');
    define('IMPUESTO_FECHA_VIGENCIA', 'impuestofechavigencia');

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

    // TABLA 'tbProveedor'
    define('TB_PROVEEDOR', 'tbproveedor');
    define('PROVEEDOR_ID', 'proveedorid');
    define('PROVEEDOR_ESTADO', 'proveedorestado');

    /*************** TABLAS INTERMEDIAS ***************/

    // TABLA INTERMEDIA PARA Proveedor Y Direccion 'tbProveedorDireccion'
    define('TB_PROVEEDOR_DIRECCION', 'tbproveedordireccion');
    define('PROVEEDOR_DIRECCION_ESTADO', 'proveedordireccionestado');
    define('PROVEEDOR_DIRECCION_ID', 'proveedordireccionid');

?>