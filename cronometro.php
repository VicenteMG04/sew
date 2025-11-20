<?php
    session_start();
    class Cronometro {
        protected $tiempo;
        protected $inicio;
        protected $corriendo;

        public function __construct() {
            $this->tiempo = 0;
            $this->corriendo = false;
        }

        public function arrancar() {
            if (!$this->corriendo){
                $this->inicio = microtime(true);
                $this->corriendo = true;
            }
        }

        public function parar() {
            if ($this->corriendo) {
                $this->tiempo += microtime(true) - $this->inicio;
                $this->corriendo = false;
            }
        }

        public function mostrar() {
            $tiempoActual = $this->tiempo;
            if ($this->corriendo) {
                $tiempoActual += microtime(true) - $this->inicio;
            }

            $minutos = floor($tiempoActual / 60);
            $segundos = floor($tiempoActual % 60);
            $decimas = floor(($tiempoActual - floor($tiempoActual)) * 10);
            echo sprintf("%02d:%02d.%d", $minutos, $segundos, $decimas); // Formato mm:ss.s
        }
    }

    // Instancia del cronómetro
    if (!isset($_SESSION["crono"])) {
        $_SESSION["crono"] = new Cronometro();
    }
    $cronometro = $_SESSION["crono"];
?>
<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="author" content="Vicente Megido García (UO294013)" />
    <meta name="description" content="Documento PHP con la vista del cronómetro implementado en PHP para la web MotoGP - Desktop" />
    <meta name="keywords" content="MotoGP, aplicación, carreras, motos, juego, cronómetro, tiempo" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MotoGP - Cronómetro PHP</title>
    <link rel="stylesheet" type="text/css" href="estilo/estilo.css" />
    <link rel="stylesheet" type="text/css" href="estilo/layout.css" />
</head>

<body>
    <header>
        <h1><a href="index.html" title="Página principal">MotoGP Desktop</a></h1>
        <!-- Ejercicio opcional: Plegar el menú en dispositivos móviles -->
        <button aria-expanded="false" aria-label="Mostrar u ocultar menú">☰ Menú</button>
        <nav>
            <a href="index.html" title="Página de inicio">Inicio</a>
            <a href="piloto.html" title="Información del piloto Fermín Aldeguer">Piloto</a>
            <a href="circuito.html" title="Información del circuito de Le Mans">Circuito</a>
            <a href="meteorologia.html" title="Información meteorológica">Meteorología</a>
            <a href="clasificaciones.html" title="Página de clasificaciones">Clasificaciones</a>
            <a href="juegos.html" title="Página de juegos" class="active">Juegos</a>
            <a href="ayuda.html" title="Página de ayuda">Ayuda</a>
        </nav>
    </header>
    <p>Estás en: <a href="index.html" title="Página de inicio">Inicio</a> >> <a href="juegos.html" title="Página de juegos">Juegos</a> >> <strong>Cronómetro PHP</strong></p>
    <main>
        <h2>Cronómetro PHP</h2>
        <form action="#" method="post" name="botones">
            <input type="submit" name="arrancar" value="Arrancar" />
            <input type="submit" name="parar" value="Parar" />
            <input type="submit" name="mostrar" value="Mostrar" />
        </form>
        <?php
            if (isset($_POST['arrancar'])) {
                $cronometro->arrancar();
            }

            if (isset($_POST['parar'])) {
                $cronometro->parar();
            }

            if (isset($_POST['mostrar'])) {
                echo "<p>Tiempo transcurrido: " . $cronometro->mostrar() . "</p>";
            }
            $_SESSION["crono"] = $cronometro;
        ?>
    </main>
    <footer>
        <p>© MotoGP - Desktop | Software y Estándares para la Web (SEW), Curso 2025-2026 | Vicente Megido García (UO294013) - Todos los derechos reservados</p>
    </footer>
</body>
</html>