<!DOCTYPE html>
<html lang="es-cr">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Listar Impuestos | SistemaPOS</title>
		<?php include __DIR__ . '/../../service/impuestoBusiness.php'; ?>
		<style>
			h2 {
                margin: auto;
                padding: 10px;
                text-align: center;
                margin-bottom: 10px;
            }
			table {
				width: 100%;
				border-collapse: collapse;
			}
			th, td {
				border: 1px solid black;
				text-align: center;
				padding: 8px;
			}
			th {
				background-color: #f2f2f2;
			}
			.menu-button {
                display: block; 
                width: max-content; 
                margin: 20px auto; 
                text-align: center; 
                padding: 10px; 
                border: 1px solid #ccc; 
                border-radius: 5px; 
                text-decoration: none; 
                color: black;
            }
		</style>
	</head>
	<body>
		<h2>Lista de Impuestos</h2>
		<table>
			<thead>
				<tr>
					<th>ID</th>
					<th>Nombre</th>
					<th>Valor</th>
					<th>Descripción</th>
					<th>Fecha Vigencia</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$impuestoBusiness = new ImpuestoBusiness();
					$result = $impuestoBusiness->getAllTBImpuesto();

					if ($result["success"]) {
						$listaImpuestos = $result["listaImpuestos"];

						foreach ($listaImpuestos as $current) {
							echo '<tr>';
							echo '<td>' . $current->getImpuestoID() . '</td>';
							echo '<td>' . $current->getImpuestoNombre() . '</td>';
							echo '<td>' . $current->getImpuestoValor() . '% </td>';
							echo '<td>' . $current->getImpuestoDescripcion() . '</td>';
							echo '<td>' . $current->getImpuestoFechaVigencia() . '</td>';
							echo '</tr>';
						}
					}
					else {
						echo '<tr> <td colspan="5"> <p style="color: red; text-align: center;">' . $result["message"] . '</p> </td> </tr>';
					}
				?>
			</tbody>
		</table>
		<a href="../index.php" class="menu-button">Regresar al Menú</a>
	</body>
</html>