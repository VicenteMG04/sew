<?php
    session_start();
    class Clasificacion {
        protected $documento;

        public function __construct() {
            $this->documento = "xml/circuitoEsquema.xml";
        }

        public function consultar() {
            $datos = file_get_contents($this->documento);
            $xml = new SimpleXMLElement($datos);
            // Necesitamos registrar el namespace para poder incorporar la información sin errores (encontraba null)
            $xml->registerXPathNamespace('ns', 'http://www.uniovi.es');
            return $xml;
        }
    }

    // Instancia de la clasificación
    $clasificacion = new Clasificacion();
    $xml = $clasificacion->consultar();
?>
<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="author" content="Vicente Megido García (UO294013)" />
    <meta name="description" content="Menú con información relativa a las clasificaciones para la web MotoGP - Desktop" />
    <meta name="keywords" content="MotoGP, aplicación, carreras, motos, clasificaciones" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MotoGP - Clasificaciones</title>
    <link rel="stylesheet" type="text/css" href="estilo/estilo.css" />
    <link rel="stylesheet" type="text/css" href="estilo/layout.css" />
    <script src="js/menu.js"></script>
</head>

<body>
    <header>
        <h1><a href="index.html" title="Página principal">MotoGP Desktop</a></h1>
        <!-- Ejercicio opcional: Plegar el menú en dispositivos móviles -->
        <button aria-expanded="false" aria-label="Mostrar u ocultar menú">☰ Menú</button>
        <nav hidden>
            <a href="index.html" title="Página de inicio">Inicio</a>
            <a href="piloto.html" title="Información del piloto Fermín Aldeguer">Piloto</a>
            <a href="circuito.html" title="Información del circuito de Le Mans">Circuito</a>
            <a href="meteorologia.html" title="Información meteorológica">Meteorología</a>
            <a href="clasificaciones.php" title="Página de clasificaciones" class="active">Clasificaciones</a>
            <a href="juegos.html" title="Página de juegos">Juegos</a>
            <a href="ayuda.html" title="Página de ayuda">Ayuda</a>
        </nav>
    </header>
    <p>Estás en: <a href="index.html" title="Página de inicio">Inicio</a> >> <strong>Clasificaciones</strong></p>
    <main>
        <h2>Clasificaciones</h2>
        <?php if ($xml == null): ?>
            <p>No se ha podido leer el archivo XML.</p>
        <?php else: ?>
            <p>Ganador de la carrera: <?php echo $xml->xpath('/ns:circuito/ns:vencedor/ns:piloto')[0]; ?></p>
            <!-- Si no se usase un espacio de nombres, se podrían acceder las propiedades con $xml->circuito->vencedor->piloto -->
            <p>Tiempo empleado: <?php echo $xml->xpath('/ns:circuito/ns:vencedor/ns:tiempo')[0]; ?></p>
            <h3>Clasificación del mundial de pilotos (TOP 3) tras la carrera: </h3>
            <ol>
            <?php foreach ($xml->xpath('/ns:circuito/ns:clasificacion_mundial/ns:piloto') as $piloto): ?>
                <li><?php echo $piloto; ?></li>
            <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </main>
    <footer>
        <p>© MotoGP - Desktop | Software y Estándares para la Web (SEW), Curso 2025-2026 | Vicente Megido García (UO294013) - Todos los derechos reservados</p>
    </footer>
</body>
</html>