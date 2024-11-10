<?php
	// Incluye los archivos de configuración y constantes
	require_once './domain/Usuario.php';
	require_once './utils/Variables.php';
	require_once './auth/auth.php'; // Incluye el archivo de autenticación

	// Verifica si la sesión ha caducado
	if (verificarSesionCaducada()) {
		$LOGIN_URL = './view/auth/login.php';
		header("Location: $LOGIN_URL");
		exit();
	}

	// Verifica si el usuario está autenticado
	$usuario = $_SESSION[SESSION_AUTHENTICATED_USER];

	// Obtiene el nombre completo y el rol del usuario
	$isAdmin = $usuario->isAdmin();
	$correoUsuario = $usuario->getUsuarioEmail();
	$nombreUsuario = $usuario->getUsuarioNombreCompleto();
	$nombreRol = $usuario->getUsuarioRolUsuario()->getRolNombre();

	// Rutas de la página
	$indexScript = './view/static/js/index.js';
	$indexStylesheet = './view/static/css/index.css';
	$userImage = './view/static/img/user.webp';
	$productImage = '.' . DEFAULT_PRODUCT_IMAGE;
	$logutURL = './view/auth/logout.php';

	// Determina qué vista cargar
	$view = $_GET['view'] ?? 'home'; // Por defecto carga home
	$ajax = $_GET['ajax'] ?? false;
	$urlBase = 'index.php?view=';

	// Si la petición es vía AJAX, solo devuelve la vista sin toda la estructura    
	if ($_SERVER['REQUEST_METHOD'] === 'GET' && $ajax) {
		$url = "./view/html";
		$views = [
			'ventas' => "{$url}/links/ventas.php",
			'productos' => "{$url}/links/productos.php",
			'clientes' => "{$url}/links/clientes.php",
			'proveedores' => "{$url}/links/proveedores.php",
			'reportes' => "{$url}/links/reportes.php",
			'usuarios' => "{$url}/links/usuarios.php",
			'config' => "{$url}/config.php",
			'home' => "{$url}/home.php"
		];

		$file = $views[$view] ?? $views['home'];

		if (!file_exists($file)) {
			http_response_code(404);
			$response = ['success' => false, 'message' => '404 Not Found'];
			header('Content-Type: application/json');
			echo json_encode($response);
			exit;
		}

		include $file;
		exit;
	}

?>

<!DOCTYPE html>
<html lang="es-cr">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Inicio | POSFusion</title>
		<link rel="stylesheet" href="<?= $indexStylesheet ?>">
		<script>
			var isAdmin = <?php echo json_encode($isAdmin); ?>;
			var correoUsuario = <?php echo json_encode($correoUsuario); ?>;
			var defaultProductImage = <?php echo json_encode(DEFAULT_PRODUCT_IMAGE); ?>;
			var usuarioActual = <?php echo json_encode($nombreUsuario); ?>;
		</script>
	</head>
	<body>
		<!-- Input para mostrar u ocultar el menu lateral -->
		<input type="checkbox" id="menu-toggle">

		<!-- Barra lateral de la pagina -->
		<nav class="sidebar">
			<!-- Nombre (marca) de la pagina -->
			<div class="brand">
				<span class="bi bi-shop"></span>
				<h2>POSFusion</h2>
			</div>
			
			<!-- Menu de navegacion -->
			<div class="sidemenu">
				<!-- Información del Usuario -->
				<div class="side-user">
					<!-- Imagen del Usuario -->
					<div class="side-img" style="background-image: url(<?= $userImage ?>);"></div>

					<!-- Nombre y Rol del Usuario -->
					<?php if ($usuario !== null): ?>
						<div class="user">
							<small><?= $nombreUsuario ?></small>
							<p><?= $nombreRol ?></p>
						</div>
					<?php endif; ?>
				</div>

				<!-- Enlaces del Menu Lateral -->
				<ul>
					<!-- Inicio -->
					<li>
						<!-- La clase 'active' indica que esta seleccionado  -->
						<a href="<?= $urlBase ?>home" class="active">
							<span class="las la-home"></span>
							<span>Inicio</span>
						</a>
					</li><hr>

					<!-- Array con los enlaces del menu lateral -->
					<?php
						$links = [
							['name' => 'Ventas', 'icon' => 'las la-shopping-cart', 'url' => "{$urlBase}ventas"],
							['name' => 'Productos', 'icon' => 'las la-boxes', 'url' => "{$urlBase}productos"],
							['name' => 'Clientes', 'icon' => 'las la-users', 'url' => "{$urlBase}clientes"],
							['name' => 'Proveedores', 'icon' => 'las la-truck', 'url' => "{$urlBase}proveedores"],
							['name' => 'Reportes', 'icon' => 'las la-chart-bar', 'url' => "{$urlBase}reportes"],
						];
					?>

					<!-- Recorre los enlaces del menu lateral -->
					<?php foreach ($links as $link): ?>
						<li>
							<a href="<?= $link['url'] ?>">
								<span class="<?= $link['icon'] ?>"></span>
								<span><?= $link['name'] ?></span>
							</a>
						</li>
					<?php endforeach; ?>

					<hr>
					<?php if ($isAdmin): ?>
						<!-- Usuarios -->
						<li>
							<a href="<?= $urlBase ?>usuarios">
								<span class="las la-user-friends"></span>
								<span>Usuarios</span>
							</a>
						</li>
					<?php endif; ?>
				</ul>
			</div>
		</nav>

		<!-- Contenido de la pagina -->
		<div class="main-content">
			<!-- Barra superior de la pagina -->
			<header>
				<!-- Boton para mostrar el menu lateral -->
				<label for="menu-toggle" class="menu-toggler">
					<span class="las la-bars"></span>
				</label>

				<!-- Iconos de la barra -->
				<div class="head-icons">
					<!-- Array con los iconos de la barra superior -->
					<?php
						$links = [
							['name' => 'Config', 'icon' => 'las la-cog', 'url' => "{$urlBase}config"],
							['name' => 'Cerrar Sesión', 'icon' => 'las la-sign-out-alt', 'url' => $logutURL],
						];
					?>
					<!-- Recorre los iconos de la barra superior -->
					<?php foreach ($links as $link): ?>
						<div class="head-icon">
							<a id="<?= $link['name'] === 'Config' ? 'config-link' : '' ?>" href="<?= $link['url'] ?>">
								<span class="<?= $link['icon'] ?>"></span>
								<span><?= $link['name'] ?></span>
							</a>
						</div>
					<?php endforeach;?>
				</div>
			</header>

			<!-- Contenido principal de la pagina (se reemplaza dinámicamente con AJAX) -->
			<main>
				<?php
					include './view/html/home.php'; // Incluye la vista home por defecto
				?>
			</main>
		</div>

		<!-- Boton menu en caso de ser vista movil -->
		<label class="close-mobile-menu" for="menu-toggle"></label>

		<!-- Loader -->
		<div class="loader-container">
			<div class="lds-ring loader" id="loader"><div></div><div></div><div></div><div></div></div>
		</div>

		<!-- Scripts -->
		<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
		<script type="module" src="<?= $indexScript ?>"></script>
	</body>
</html>