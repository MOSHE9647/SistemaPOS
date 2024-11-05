<?php
    
    require_once dirname(__DIR__, 1) . '/domain/VentaDetalle.php';
    require_once dirname(__DIR__, 1) . '/domain/Venta.php';
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';

    // Datos de VentaDetalle recibidos en la solicitud
    $listaDetalles  = isset($_GET['detalles'])  ? json_decode($_GET['detalles'], true)  : null;
    $datosExtra     = isset($_GET['extra'])     ? json_decode($_GET['extra'])     : null;
    if (empty($listaDetalles) || empty($datosExtra)) {
        Utils::enviarRespuesta(400, false, "No se han recibido los datos de la venta.");
    }

    // Crear un arreglo de detalles de venta
    $detalles = [];
    foreach ($listaDetalles as $detalle) {
        // Crear un objeto VentaDetalle con los datos recibidos
        $ventaDetalle = VentaDetalle::fromArray($detalle);
        array_push($detalles, $ventaDetalle);
    }

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura</title>
    <style>
        :root {
            --print-width: 100mm;
            --print-height: 297mm;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 10px;
            width: var(--print-width);
        }
        .factura {
            padding: 10px;
        }
        .encabezado {
            text-align: center;
            margin-bottom: 10px;
            align-self: center;
        }
        .detalle {
            display: flex;
            flex-direction: column;
            align-self: flex-start;
            margin-bottom: 10px;
            padding: 5px;
            width: 100%;
            gap: 10px;
        }
        .detalle-cliente,
        .detalle-extra {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            width: 100%;
        }
        .bottom {
            margin-top: 50px;
            padding-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th {
            border-bottom: 1px solid #000;
        }
        th, td {
            padding: 5px;
            text-align: left;
            font-size: .8rem;
        }
        .factura {
            padding: 10px;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        .totales {
            width: auto;
            margin-top: 10px;
            margin-right: 10px;
        }
        .totales-text {
            display: flex;
            justify-content: space-between;
            width: 100%;
            padding: 5px;
            font-size: .9rem;
        }
        .bold span {
            font-weight: bold;
            font-size: 1rem;
        }
        .totales-text span:last-child {
            text-align: right;
            width: 140px;
        }

        @media print {
            body {
                width: var(--print-width);
            }
        }
        @page {
            size: var(--print-width) var(--print-height);
            margin: 0;
        }

        @media print {
            html, body {
                width: var(--print-width);
                height: var(--print-height);
            }
            .factura {
                page-break-after: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="factura">
        <div class="encabezado">
            <h2>Factura</h2>
            <p>N° Factura: <span id="numeroFactura"></span></p>
            <p>Fecha: <span id="fechaHora"></span></p>
        </div>
        <div class="detalle">
            <div class="detalle-cliente">
                <span>Cliente:</span>
                <span id="nombreCliente"></span>
            </div>
            <div class="detalle-cliente">
                <span>Tipo de Venta:</span>
                <span id="tipoVenta"></span>
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cant.</th>
                    <th>Código</th>
                    <th>Total</th>
                    <th>IVA</th>
                </tr>
            </thead>
            <tbody id="listaProductos">
                <!-- Los productos se insertarán aquí -->
            </tbody>
        </table>
        <div class="totales">
            <div class="totales-text">
                <span>Subtotal: </span>
                <span id="subtotal">¢0.00</span>
            </div>
            <div class="totales-text">
                <span>IVA: </span>
                <span id="ivaTotal">¢0.00</span>
            </div>
            <div class="totales-text bold">
                <span>Total: </span>
                <span id="total">¢0.00</span>
            </div>
            <hr>
            <div class="totales-text">
                <span>Pago: </span>
                <span id="pago">¢0.00</span>
            </div>
            <div class="totales-text bold">
                <span>Vuelto: </span>
                <span id="vuelto">¢0.00</span>
            </div>
        </div>
        <div class="detalle bottom">
            <div class="detalle-extra">
                <span>Cajero:</span>
                <span id="nombreCajero"></span>
            </div>
            <div class="detalle-extra">
                <span>Moneda:</span>
                <span id="moneda"></span>
            </div>
            <div class="detalle-extra">
                <span>Tipo de Pago:</span>
                <span id="tipoPago"></span>
            </div>
            <div class="detalle-extra" style="display: none;">
                <span>Tipo de Cambio:</span>
                <span id="tipoCambio"></span>
            </div>
        </div>
    </div>

    <script>
        const detalles = <?php echo json_encode($detalles); ?>;
        const datosExtra = <?php echo json_encode($datosExtra); ?>;
        const venta = detalles[0].Venta;

        const productos = [];
        detalles.forEach(detalle => {
            producto = {
                cantidad: detalle.Cantidad,
                producto: detalle.Producto,
                precio: detalle.Precio.toFixed(2),
            }
            productos.push(producto);
        });

        // Datos de ejemplo
        const factura = {
            numero: venta.NumeroFactura,
            fecha: new Date().toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: 'numeric',
                hour12: true
            }),
            cliente: datosExtra.cliente?.Nombre + ' - ' + datosExtra.cliente?.Alias,
            cajero: datosExtra.usuario,
            moneda: venta.Moneda,
            tipoCambio: venta.TipoCambio ? parseFloat(venta.TipoCambio).toFixed(2) : null,
            tipoVenta: venta.CondicionVenta,
            tipoPago: venta.TipoPago,
            productos: productos,
            subtotal: parseFloat(venta.MontoBruto).toFixed(2),
            impuesto: parseFloat(venta.MontoImpuesto).toFixed(2),
            total: parseFloat(venta.MontoNeto).toFixed(2),
            pago: parseFloat(venta.MontoPago).toFixed(2),
            vuelto: parseFloat(venta.MontoVuelto).toFixed(2)
        };

        // Función para llenar la factura
        function llenarFactura() {
            document.getElementById('numeroFactura').textContent = factura.numero;
            document.getElementById('fechaHora').textContent = factura.fecha.toLocaleString();
            document.getElementById('nombreCliente').textContent = factura.cliente;
            document.getElementById('nombreCajero').textContent = factura.cajero;
            document.getElementById('moneda').textContent = factura.moneda;
            document.getElementById('tipoVenta').textContent = factura.tipoVenta;
            document.getElementById('tipoPago').textContent = factura.tipoPago;

            if (factura.tipoCambio) {
                document.getElementById('tipoCambio').parentElement.style.display = 'flex';
                document.getElementById('tipoCambio').textContent = `¢${factura.tipoCambio}`;
            }

            const listaProductos = document.getElementById('listaProductos');
            factura.productos.forEach(data => {
                const producto = data.producto;
                const nombreProducto = `${producto.Nombre || ''} ${producto.Presentacion?.Nombre + ', ' || ''} ${producto.Marca?.Nombre || ''}`;
                const impuesto = (data.precio * datosExtra.impuesto).toFixed(2);
                const fila = `
                    <tr>
                        <td>${nombreProducto}</td>
                        <td>${data.cantidad}</td>
                        <td>${producto.CodigoBarras.Numero}</td>
                        <td>¢${data.precio}</td>
                        <td>¢${impuesto}</td>
                    </tr>
                `;
                listaProductos.innerHTML += fila;
            });

            const currencySymbols = { USD: '$', EUR: '€', CRC: '¢' };
            document.getElementById('subtotal').textContent = `¢${factura.subtotal}`;
            document.getElementById('ivaTotal').textContent = `¢${factura.impuesto}`;
            document.getElementById('total').textContent = `¢${factura.total}`;
            document.getElementById('pago').textContent = `${currencySymbols[factura.moneda]}${factura.pago}`;
            document.getElementById('vuelto').textContent = `¢${factura.vuelto}`;
        }

        // Llenar la factura y abrir el diálogo de impresión al cargar la página
        window.onload = function() {
            llenarFactura();
            
            // Configurar las opciones de impresión
            const mediaQueryList = window.matchMedia('print');
            mediaQueryList.addListener(function(mql) {
                if (mql.matches) {
                    document.title = "Factura N°" + factura.numero;
                }
            });

            // Abrir el diálogo de impresión
            window.print();
        };
    </script>
</body>
</html>