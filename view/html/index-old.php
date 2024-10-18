<!-- Titulo de Bienvenida -->
<div class="page-header">
    <div class="page-title">
        <h1>Administraci&oacute;n de <span>CRUD&apos;s</span>:</h1>
        <small>Inicio <span>/</span> CRUD&apos;s</small>
    </div>
</div>
<hr>

<!-- Contenido de la pagina -->
<div class="page-content cruds-container">
<?php
    // Array de enlaces principales
    $mainTables = [
        'roles.php' => 'Roles de Usuario',
        'impuestos.php' => 'Impuestos',
        'proveedores.php' => 'Proveedores',
        'direcciones.php' => 'Direcciones',
        'telefonos.php' => 'Teléfonos',
        'categorias.php' => 'Categorías',
        'subcategorias.php' => 'Subcategorías',
        'lotes.php' => 'Lotes',
        'compras.php' => 'Compras',
        'cuentasporpagar.php' => 'Cuentas por Pagar',
        'compradetalle.php' => 'Detalle de Compras',
        'presentaciones.php'=> 'Presentaciones',
        'marcas.php'=> 'Marcas',
    ];

    // Array de enlaces intermedios
    $intermediateTables = [
        'productoSubcategoria.php' => 'Producto-Subcategorías',
        'proveedorDireccion.php' => 'Proveedor-Direcciones',
        'proveedorTelefono.php' => 'Proveedor-Teléfonos',
        'proveedorProducto.php' => 'Proveedor-Productos',
        'proveedorCategoria.php' => 'Proveedor-Categoría',
        'usuarioTelefono.php' => 'Usuario-Teléfonos',
    ];

    // Función para generar la lista de enlaces
    function generateLinks($links) {
        echo "<div class=\"links-container\">";
                echo "<ul class=\"links-list\">";
                    foreach ($links as $file => $label) {
                        echo "<li><a class=\"link\" href=\"./view/html/config/$file\">$label</a></li>";
                    }
                echo "</ul>";
        echo "</div>";
    }

    // Imprimir secciones de enlaces
    echo "
        <div class=\"title-container\">
            <div class=\"title\">
                <h2>Tablas Principales</h2>
            </div>
        </div>
    ";
    generateLinks($mainTables);

    echo "
        <div class=\"title-container\">
            <div class=\"title\">
                <h2>Tablas Intermedias</h2>
            </div>
        </div>
    ";
    generateLinks($intermediateTables);
?>
</div>