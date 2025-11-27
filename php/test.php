<?php
    include_once("../cronometro.php");
    
    class Test {
        protected $server = 'localhost';
        protected $user = 'DBUSER2025';
        protected $pass = 'DBPSWD2025';
        protected $dbname = 'uo294013_db';

        protected $conn = null;

        protected $respuestasCorrectas = [
            1 => ["20", "veinte"],
            2 => ["54", "cincuenta y cuatro", "cincuenta y cuatro"],
            3 => ["27", "veintisiete"],
            4 => ["johann zarco", "zarco"],
            5 => ["francia"],
            6 => ["marc márquez", "marc marquez"],
            7 => ["sí", "si"],
            8 => ["22", "veintidós", "veintidos"],
            9 => ["long lap", "longlap"],
            10 => ["5º", "5", "quinto", "5o"]
        ];

        // Constructor de la clase PHP test, encargado de conectar con la BD e inicializar el estado del test
        public function __construct() {
            // Conectar a BD (si falla, lo informamos pero no morimos)
            $this->conn = @new mysqli($this->server, $this->user, $this->pass, $this->dbname);
            if ($this->conn && $this->conn->connect_error) {
                echo "<p>Error de conexión con la base de datos: ".htmlspecialchars($this->conn->connect_error)."</p>";
                $this->conn = null;
            }

            if (!isset($_SESSION['estadoTest'])) $_SESSION['estadoTest'] = 'inicio';
        }

        // Función para iniciar la prueba, creando el cronómetro (no se mostrará) y cambiando el estado del test
        public function iniciarPrueba() {
            $_SESSION["cronometro"] = new Cronometro();
            $_SESSION["cronometro"]->arrancar();
            $_SESSION["estadoTest"] = "preguntas";
        }

        // Función para finalizar el test, guardando las respuestas del usuario y el tiempo empleado
        public function terminarPreguntas(array $post) {
            $resp = [];
            foreach ($post as $k => $v) {
                if (str_starts_with($k, 'pregunta')) {
                    $resp[$k] = $v;
                }
            }
            $_SESSION['respuestasUsuario'] = $resp;

            if (isset($_SESSION['cronometro'])) {
                $_SESSION['cronometro']->parar();
                $_SESSION['tiempoSegundos'] = (int) $_SESSION['cronometro']->obtenerDuracion();
            } else {
                $_SESSION['tiempoSegundos'] = 0;
            }

            $_SESSION['estadoTest'] = 'formularioFinal';
        }

        // Función para evaluar las respuestas del usuario y devolver un array con los resultados
        public function evaluar() : array {
            $resultado = [];

            foreach ($this->respuestasCorrectas as $numPregunta => $opciones) {
                $clave = "pregunta".$numPregunta;

                if (!isset($_SESSION["respuestasUsuario"][$clave])) {
                    $resultado[$numPregunta] = false;
                    continue;
                }

                $respuestaUsuario = strtolower(trim($_SESSION["respuestasUsuario"][$clave]));
                $resultado[$numPregunta] = in_array($respuestaUsuario, array_map("strtolower", $opciones));
            }

            return $resultado;
        }

        // Función para guardar el resultado del test en la base de datos
        public function guardarResultado($codigo, $valoracion, $observaciones) {
            // Aquí insertarás en tus tablas:
            // - usuario
            // - resultado
            // - observaciones

            $_SESSION["estadoTest"] = "fin";
            session_destroy();
        }

        // Función para mostrar el formulario correspondiente según el estado del test
        public function mostrarFormulario() {
            $estado = $_SESSION["estadoTest"];

            // ESTADO 1: Inicio de la prueba
            if ($estado === "inicio") {
                echo '
                <form method="post">
                    <p>Cuando estés preparado, pulsa el siguiente botón para iniciar la prueba.</p>
                    <button name="iniciar" type="submit">Iniciar prueba</button>
                </form>';
                return;
            }

            // ESTADO 2: Preguntas
            if ($estado === "preguntas") {
                echo '
                <form method="post">
                    <label>Pregunta 1: <input type="text" name="pregunta1" required></label>
                    <label>Pregunta 2: <input type="text" name="pregunta2" required></label>
                    <label>Pregunta 3: <input type="text" name="pregunta3" required></label>
                    <label>Pregunta 4: <input type="text" name="pregunta4" required></label>
                    <label>Pregunta 5: <input type="text" name="pregunta5" required></label>
                    <label>Pregunta 6: <input type="text" name="pregunta6" required></label>
                    <label>Pregunta 7: <input type="text" name="pregunta7" required></label>
                    <label>Pregunta 8: <input type="text" name="pregunta8" required></label>
                    <label>Pregunta 9: <input type="text" name="pregunta9" required></label>
                    <label>Pregunta 10: <input type="text" name="pregunta10" required></label>

                    <button name="terminar" type="submit">Terminar prueba</button>
                </form>';
                return;
            }

            // ESTADO 3: Formulario final
            if ($estado === "formularioFinal") {
                echo '
                <form method="post">
                    <fieldset>
                        <legend>Identificación del usuario</legend>
                        <label>Código: <input type="text" name="codigo" required></label>
                    </fieldset>

                    <fieldset>
                        <legend>Valoración del usuario</legend>
                        <label>Valoración: <input type="text" name="valoracion" required></label>
                    </fieldset>

                    <fieldset>
                        <legend>Observaciones del evaluador</legend>
                        <textarea name="obs" rows="5" cols="40" required></textarea>
                    </fieldset>

                    <button name="guardar" type="submit">Guardar prueba</button>
                </form>';
                return;
            }

            // ESTADO 4: FIN
            if ($estado === "fin") {
                echo "<p>La prueba ha sido registrada correctamente.</p>";
            }
        }
    }

    // Creación de la instancia de la clase Test y manejo de las solicitudes POST
    $test = new Test();

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        if (isset($_POST["iniciar"])) {
            $test->iniciarPrueba();
        }

        if (isset($_POST["terminar"])) {
            $test->terminarPreguntas($_POST);
        }

        if (isset($_POST["guardar"])) {
            $test->guardarResultado($_POST["codigo"], $_POST["valoracion"], $_POST["obs"]);
        }
    }
?>
<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="author" content="Vicente Megido García (UO294013)" />
    <meta name="description" content="Página dedicada a la realización de las pruebas de usabilidad de la web MotoGP - Desktop" />
    <meta name="keywords" content="MotoGP, aplicación, carreras, motos, test, preguntas" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MotoGP - Test</title>
    <link rel="icon" href="../multimedia/favicon.ico" sizes="48x48">
    <link rel="stylesheet" type="text/css" href="../estilo/estilo.css" />
    <link rel="stylesheet" type="text/css" href="../estilo/layout.css" />
</head>

<body>
    <h1>Test</h1>
    <form method="post">
        <label>¿Cuántos años tiene el piloto murciano de MotoGP Fermín Aldeguer? (en diciembre de 2025): <input type="text" name="pregunta1" placeholder="Respuesta a la pregunta 1" required /></label>
        
        <label>¿Qué dorsal utiliza Fermín Aldeguer en su primera temporada en MotoGP?: <input type="text" name="pregunta2" placeholder="Respuesta a la pregunta 2" required /></label>

        <label>¿Cuántas vueltas duró la carrera del Gran Premio de Le Mans de la temporada de 2025?: <input type="text" name="pregunta3" placeholder="Respuesta a la pregunta 3" required /></label>

        <label>¿Quién fue el vencedor de la carrera del Gran Premio de Le Mans de la temporada de 2025?: <input type="text" name="pregunta4" placeholder="Respuesta a la pregunta 4" required /></label>

        <label>¿En qué país europeo se encuentra el circuito de Le Mans?: <input type="text" name="pregunta5" placeholder="Respuesta a la pregunta 5" required /></label>
    
        <label>¿Qué piloto era el líder del mundial de MotoGP tras la celebración del Gran Premio de Le Mans de la temporada de 2025?: <input type="text" name="pregunta6" placeholder="Respuesta a la pregunta 6" required /></label>

        <label>¿Llovió durante el fin de semana del Gran Premio de Le Mans de la temporada de 2025?: <input type="text" name="pregunta7" placeholder="Respuesta a la pregunta 7" required /></label>

        <label>¿Con qué término inglés se conoce a la penalización que obliga a un piloto a realizar un recorrido más largo durante una carrera de MotoGP?: <input type="text" name="pregunta8" placeholder="Respuesta a la pregunta 8" required /></label>

        <label>¿Cuántos puntos de diferencia había entre el líder del mundial de MotoGP y el segundo clasificado tras la celebración del Gran Premio de Le Mans de la temporada de 2025?: <input type="text" name="pregunta9" placeholder="Respuesta a la pregunta 9" required /></label>
        
        <label>¿En qué posición finalizó Fermín Aldeguer la temporada 2024 en el mundial de pilotos de Moto2?: <input type="text" name="pregunta10" placeholder="Respuesta a la pregunta 10" required /></label>

        <button type="submit" name="terminarPrueba">Terminar prueba</button>
    </form>
</body>
</html>