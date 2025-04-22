<?php
// Configuraciones de seguridad
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
// header("Content-Security-Policy: default-src 'self'; script-src 'self' https://maps.googleapis.com https://maps.gstatic.com; style-src 'self' 'https://fonts.googleapis.com'; img-src 'self' data:; font-src 'https://fonts.gstatic.com';");
header("Content-Security-Policy: default-src 'self' https: 'unsafe-inline' 'unsafe-eval'; img-src 'self' data:;");
// header("Content-Security-Policy: default-src 'self' https: 'unsafe-inline' 'unsafe-eval'; img-src 'self' data:; script-src 'self' https://maps.googleapis.com https://maps.gstatic.com; frame-src https://www.google.com;");


// Configuración de sesión segura
session_start([
	'cookie_lifetime' => 86400,
	'cookie_secure' => true,
	'cookie_httponly' => true,
	'cookie_samesite' => 'Strict',
	'use_strict_mode' => true
]);

require __DIR__ . '/controllers/sorteocontroller.php';
require __DIR__ . '/controllers/locationcontroller.php';
require __DIR__ . '/controllers/cartoncontroller.php';
require __DIR__ . '/controllers/premioscontroller.php';
require __DIR__ . '/controllers/entidadescontroller.php';
require __DIR__ . '/controllers/personacontroller.php';
require __DIR__ . '/controllers/compracontroller.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Limpiar buffer de salida
    if (ob_get_length()) ob_end_clean();
    
    // Leer input JSON
    $json = file_get_contents('php://input');
    $input = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos JSON inválidos']);
        exit;
    }

    if (!empty($input['action']) && $input['action'] === 'registrarCompra') {
        $compraController = new CompraController();
		$response = $compraController->registrarCompra($input); // Pasar $input directamente
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

// Crear instancia del controlador
$sorteoController = new SorteoController();
$sorteosActivos = $sorteoController->getActiveSorteos();

$countryController = new LocationController();
$countries = $countryController->getAllCountries();


$ticketsController = new CartonController();
$tickets = $ticketsController->getCartonSellBySorteo($sorteosActivos['id']);

$premiosController = new PremiosController();
$premios = $premiosController->getPremiosSorteo($sorteosActivos['id']);

$entidadesController = new EntidadesController();
$entidades = $entidadesController->getEntidades();

$personaController = new PersonaController();
// $persona = $personaController->getAll(); //PRUEBA FUNCIONAL PARA LUEGO CARGAR LOS GANADORES

// Protección contra session fixation
session_regenerate_id(true);

// Configuración de zona horaria
date_default_timezone_set('America/Bogota');


// Configuración de manejo de errores
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');


?>
<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Participa en emocionantes sorteos con Los Audaces y gana premios increíbles">
	<title>Los Audaces - Sorteos y Rifas</title>
	<link href="css/style.css?v=<?php echo filemtime('css/style.css'); ?>" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
	<link rel="icon" href="resources/logo.png" type="image/png">
	<!-- Preload de imágenes de premios -->
	<!-- Preload de imágenes de premios -->
	<?php foreach ($premios as $premio): ?>
		<link rel="preload" href="resources/premios/<?php echo htmlspecialchars($premio['foto']); ?>" as="image" imagesrcset="resources/premios/<?php echo htmlspecialchars($premio['foto']); ?> 1x, resources/premios/<?php echo htmlspecialchars($premio['foto']); ?> 2x">
	<?php endforeach; ?>
	<meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">
</head>

<body>
	<div class="page-wrapper">
		<!-- Header -->
		<header class="main-header">
			<div class="logo-container">
				<a id="inicio" href="index.php"><img src="resources/logo.png" alt="Los Audaces" class="logo"></a>
			</div>
			<div class="menu-container">
				<button class="menu-toggle">
					<span></span>
					<span></span>
					<span></span>
				</button>
				<nav class="main-nav">
					<ul>
						<li><a href="#" id="rifas">Rifas</a></li>
						<li><a href="#" id="premios">Premios</a></li>
						<li><a href="#" id="contact">Contacto</a></li>
						<li><a href="#" id="login">Login</a></li>
					</ul>
				</nav>
			</div>
		</header>

		<!-- Contenido principal -->
		<main class="main-content">
			<section id="home-section" style="display: block;"> <!-- MOSTRAR POR DEFECTO -->
				<div class="hero-section">
					<div class="hero-background">
						<div class="purple-bar"></div>
						<div class="hero-text">
							<h1>¡Cambia tu vida con un solo boleto! ¡Participa ya!</h1>
						</div>
					</div>
					<div class="lottery-promo">
						<div><strong>HOY PUEDE SER TU DÍA DE SUERTE</strong></div>
						<div>Participa en nuestros fascinantes sorteos</div>
						<div>¡La suerte está de tu lado!</div>
						<button id="comprar_numprin" class="btn_comprarprin">Comprar Boleto</button>
					</div>
				</div>

				<!-- Slider de Premios -->
				<section class="premios-section">
					<h2 class="section-title">NUESTROS PREMIOS</h2>
					<div class="slider-container">
						<div class="slider">
							<!-- Las imágenes se cargarán dinámicamente con JavaScript -->
						</div>
						<button class="slider-btn prev-btn">&lt;</button>
						<button class="slider-btn next-btn">&gt;</button>
						<div class="slider-dots"></div>
					</div>
				</section>
			</section>

			<section id="rifas-section" style="display: none; "> <!-- OCULTA POR DEFECTO -->
				<section class="sorteos-container">
					<div class="sorteos">
						<img src="resources/premios/<?php echo htmlspecialchars($sorteosActivos['FOTO'] ?? 'default.jpg'); ?>" alt="Premios de Sorteo" class="sorteo-image">
					</div>
					<div class="sorteo-info">
						<h2><?php echo htmlspecialchars($sorteosActivos['titulo'] ?? 'Sorteo'); ?></h2>
						<p class="sorteo-precio">$<?php echo number_format($sorteosActivos['precio'] ?? 0, 2); ?></p>
						<button id="comprar_num" class="btn-comprar" data-sorteo="<?php echo htmlspecialchars($sorteosActivos['id'] ?? ''); ?>">Comprar Boleto</button>
					</div>
				</section>
			</section>

			<section id="carton-num" class="carton-numeros" style="display: none;">
				<div class="rango-num">
					<?php
					$totalPages = ceil($sorteosActivos['qtynumeros'] / 100);
					if ($totalPages > 1) {
						for ($i = 0; $i < $totalPages; $i++) {
							$startNum = $i * 100;
							$endNum = ($i + 1) * 100 - 1;
							echo '<a href="#" class="rango-btn" data-page="' . $i . '" data-start="' . $startNum . '" data-end="' . $endNum . '">';
							echo '<div class="numrango">' . str_pad($startNum, 3, '0', STR_PAD_LEFT) . ' - ' . str_pad($endNum, 3, '0', STR_PAD_LEFT) . '</div>';
							echo '</a>';
						}
					}
					?>
				</div>
				<!-- Esta tabla será llenada dinámicamente por JavaScript -->
				<table class="numeros-tabla"></table>
			</section>

			<section id="contacto-section" style="display: none;">
				<!-- MAPA DE GOOGLE -->
				<div id="mapa" style="width: 100%; height: 300px; margin-top: 20px;"></div>

				<form id="formContacto"  action="sendMail.php">
					<h2>Contáctanos</h2>

					<div class="form-group">
						<label for="nombre">Nombre:</label>
						<input type="text" id="nombrec" name="nombrec" required>
					</div>

					<div class="form-group">
						<label for="email">Correo Electrónico:</label>
						<input type="email" id="emailc" name="emailc" required>
					</div>

					<div class="form-group">
						<label for="mensaje">Mensaje:</label>
						<textarea id="mensaje" name="mensaje" rows="5" required></textarea>
					</div>

					<!-- RECAPTCHA -->
					<div class="g-recaptcha" data-sitekey="TU_SITE_KEY_RECAPTCHA"></div>

					<button type="submit">Enviar</button>
				</form>


			</section>

			<section id="registroCliente" style="display: none;">
				<div class="metodos-pago-horizontal">
					<?php foreach ($entidades as $entidad): ?>
						<div class="metodo-pago-item">
							<div class="logo-entidad">
								<img src="<?php echo 'resources/entidades/' . $entidad['logo']; ?>" alt="<?php echo $entidad['nombreentidad']; ?>">

							</div>
							<div class="detalles-entidad">
								<h4><?php echo $entidad['nombreentidad']; ?></h4>
								<div class="datos-cuenta">
									<?php if (!empty($entidad['tipocta'])): ?>
										<span class="dato"><strong>Tipo:</strong> <?php echo $entidad['tipocta']; ?></span>
									<?php endif; ?>

									<?php if (!empty($entidad['numcta'])): ?>
										<span class="dato"><strong>Cuenta:</strong> <?php echo $entidad['numcta']; ?></span>
									<?php endif; ?>

									<?php if (!empty($entidad['nombretitular'])): ?>
										<span class="dato"><strong>Titular:</strong> <?php echo $entidad['nombretitular']; ?></span>
									<?php endif; ?>

									<?php if (!empty($entidad['cedulatitular'])): ?>
										<span class="dato"><strong>ID:</strong> <?php echo $entidad['cedulatitular']; ?></span>
									<?php endif; ?>

									<?php if (!empty($entidad['emailcta'])): ?>
										<span class="dato"><strong>Email:</strong> <?php echo $entidad['emailcta']; ?></span>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="modal-content">
					<span class="close">&times;</span>
					<h2>Registro de Cliente</h2>
					<form id="registroClienteForm" onsubmit="return false;" > 
						<label for="nombre">Nombre:</label>
						<input type="text" id="nombre" class="input" name="nombre" required>

						<label for="apellido">Apellido:</label>
						<input type="text" id="apellido" name="apellido" class="input" required>

						<label for="numIdentificacion">Número de Identificación:</label>
						<input type="text" id="numIdentificacion" name="numIdentificacion" class="input" required>

						<label for="pais">País:</label>
						<select id="pais" name="pais" required>
							<option value="">Seleccione un país</option>
							<?php foreach ($countries as $country): ?>
								<option value="<?php echo $country['id']; ?>"><?php echo $country['name']; ?></option>
							<?php endforeach; ?>
						</select>


						<label for="email">Email:</label>
						<input type="email" id="email" name="email" class="input" required>

						<label for="telefono">Número de Teléfono:</label>
						<input type="tel" id="telefono" name="telefono" class="input" required>
						
						<div class="checkbox-container" style="display: flex; align-items: center; margin: 10px 0;">
							<input type="checkbox" id="is_adult" name="is_adult" class="input" required style="margin-right: 10px;">
							<label for="is_adult" style="margin-bottom: 0;">Soy Mayor de Edad</label>
						</div>						
						<button id="regCliente" type="submit">Registrar</button>
					</form>
				</div>
			</section>

			<section id="showpremios" style="display: none;">
				<div class="premios-container">
					<div class="thumbs-container">
						<?php
						require __DIR__ . '/thumbnail.php';
						foreach ($premios as $k => $v) {
							$thumbPath = getThumbnail('resources/premios/' . htmlspecialchars($v['foto']));
							echo '<div class="thumb-item" data-premio-id="' . $k . '">';
							echo '<img src="' . $thumbPath . '" alt="' . htmlspecialchars($v['name'] ?? 'Premio') . '" class="thumb-img">';
							echo '<div class="thumb-title">' . htmlspecialchars($v['name'] ?? 'Premio') . '</div>';
							echo '</div>';
						}
						?>
					</div>

					<div class="premio-detalle">
						<?php if (!empty($premios)): ?>
							<div class="premio-imagen">
								<img src="resources/premios/<?php echo htmlspecialchars($premios[0]['foto']); ?>" alt="<?php echo htmlspecialchars($premios[0]['name']); ?>" id="detalle-imagen">
							</div>
							<div class="premio-info" id="detalle-info">
								<h2><?php echo htmlspecialchars($premios[0]['name']); ?></h2>
								<p><?php echo htmlspecialchars($premios[0]['descripcion']); ?></p>
								<div class="premio-caracteristicas">
									<?php if (!empty($premios[0]['valor'])): ?>
										<p><strong>Valor:</strong> $<?php echo number_format($premios[0]['valor'], 2, ',', '.'); ?></p>
									<?php endif; ?>
									<p><strong>Posición:</strong> <?php echo htmlspecialchars($premios[0]['posicion']); ?></p>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</section>

			<!-- Modal de Login -->
			<div id="loginModal" class="modal" style="display: none;">
				<div class="modal-content">
					<span class="close" id="closeLoginModal">&times;</span>
					<h2>Iniciar Sesión</h2>
					<form id="loginForm">
						<label for="login">Usuario o Email:</label>
						<input type="text" name="login" id="login" required>

						<label for="password">Contraseña:</label>
						<input type="password" name="password" id="password" required>

						<button type="submit">Ingresar</button>
					</form>
					<div id="loginMessage"></div>
				</div>
			</div>

			<div id="shopcart" style="display: none;">
				<h2 class="">Carrito de Compras</h2>
				<div class="body">
					<div class="container">
						<h4 class="heading">Numeros Seleccionados</h4>
						<div class="result"></div>
					</div>
					<div class="cart">
						<div class="cart-header">
							<h4>Cant. Selec. </h4>
							<p class="noOfItems">0 items</p>
						</div>
						<!-- <hr noshade="true" size="1px" /> -->
						<div class="cart-items"></div>
						<hr noshade="true" size="1px" />
						<div class="cart-footer cart-header">
							<h4>Total</h4>
							<p class="total">$0</p>
						</div>
						<button id="regcompra" class="buy-btn">Comprar Seleccionados</button>
					</div>
				</div>
			</div>

			<!-- Modal para compra -->
			<div id="compraModal" class="modal" style="display:none;">
				<div class="modal-content">
					<span class="close-modal">&times;</span>
					<h3 id="modalSorteoTitulo"></h3>
					<div id="modalSorteoContent"></div>
				</div>
			</div>
		</main>

		<!-- Footer -->
		<footer class="main-footer" role="contentinfo">
			<div class="footer-container">
				<div class="footer-brand">
					<img src="resources/logo.png" alt="Los Audaces" class="footer-logo" width="150" height="auto">
				</div>

				<div class="footer-content">
					<div class="footer-section">
						<h3 class="footer-heading">Rifas</h3>
						<ul class="footer-list">
							<li><a href="#" class="footer-link">Accessorios</a></li>
							<li><a href="#" class="footer-link">Números Ganadores</a></li>
							<li><a href="#" class="footer-link">Próximos Sorteos</a></li>
						</ul>
					</div>
					<div class="footer-section">
						<h3 class="footer-heading">Tienda</h3>
						<ul class="footer-list">
							<li>Dirección: Av. 6 # 9-76 Centro Cucuta - Colombia</li>
							<li>Email: <a href="mailto:admin@losaudaces.com" class="footer-link">admin@losaudaces.com</a></li>
							<li>Teléfono: <a href="tel:+573204563721" class="footer-link">+57-3204563721</a></li>
						</ul>
					</div>

					<div class="footer-section">
						<h3 class="footer-heading">Política</h3>
						<ul class="footer-list">
							<li><a href="#" class="footer-link">Términos y condiciones</a></li>
							<li><a href="#" class="footer-link">Política de reembolso</a></li>
							<li><a href="#" class="footer-link">Política de privacidad</a></li>
							<li><a href="#" class="footer-link">Política de envío</a></li>
							<li><a href="#" class="footer-link">Política de cookies</a></li>
							<li><a href="#" class="footer-link">FAQ</a></li>
						</ul>
					</div>
				</div>
			</div>

			<div class="footer-bottom">
				<p class="copyright">&copy; 2023 Los Audaces. Todos los derechos reservados.</p>
			</div>
		</footer>
	</div>
	<!-- Pasar datos PHP a JavaScript -->
	<script>
		window.appData = {
			totalNumeros: <?php echo intval($sorteosActivos['qtynumeros']); ?>,
			numerosVendidos: <?php echo json_encode(array_map(function ($t) {
									return str_pad($t['numero'], 3, '0', STR_PAD_LEFT);
								}, $tickets)); ?>,
			precioNumero: <?php echo floatval(str_replace(',', '.', $sorteosActivos['precio'])); ?>,
			premiosData: <?php echo json_encode($premios); ?>,
			premiosImages: [
				'resources/premios/celular.jpeg',
				'resources/premios/Moto.jpeg',
				'resources/premios/Viaje.jpeg'
			]
		};
	</script>

	<!-- Cargar el módulo principal -->
	<script type="module" src="js/main.js"></script>

	<!-- Google Maps (mantener este script al final) -->
	<script async defer src="https://maps.googleapis.com/maps/api/js?key=TU_API_KEY_MAPS&callback=initMap"></script>
</body>

</html>