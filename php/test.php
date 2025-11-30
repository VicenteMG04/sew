<?php
    define('CRONOMETRO_SIN_HTML', true); // cronometro.php no genererará HTML
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
                echo "<p>Error de conexión con la base de datos: " . htmlspecialchars($this->conn->connect_error)."</p>";
                $this->conn = null;
            }

            if (!isset($_SESSION['estadoTest'])) {
                $_SESSION['estadoTest'] = 'inicio';
            }
        }

        // Función para iniciar la prueba, creando el cronómetro (no se mostrará) y cambiando el estado del test
        public function iniciarPrueba() {
            $_SESSION["cronometro"] = new Cronometro();
            $_SESSION["cronometro"]->arrancar();
            $_SESSION["estadoTest"] = "preguntas";
        }

        // Función para finalizar el test, guardando las respuestas del usuario y el tiempo empleado
        public function terminarPreguntas(array $post) : void {
            $resp = [];
            foreach ($post as $k => $v) {
                if (str_starts_with($k, 'pregunta')) {
                    $resp[$k] = trim((string)$v);
                }
            }
            $_SESSION['respuestasUsuario'] = $resp;

            if (isset($_SESSION['cronometro'])) {
                $_SESSION['cronometro']->parar();
                // Método obtenerDuracion creado para esta sesión específica (devuelve HH:MM:SS.mmm)
                $_SESSION['tiempo'] = $_SESSION['cronometro']->getTiempoBD();
            } else {
                $_SESSION['tiempo'] = "00:00:00.000";
            }

            $_SESSION['estadoTest'] = 'formularioFinal';
        }

        // Función para evaluar las respuestas del usuario y devolver un array con los resultados
        public function evaluar() : array {
            $resultado = [];
            $respuestasUsuario = $_SESSION['respuestasUsuario'] ?? [];
            
            foreach ($this->respuestasCorrectas as $numPregunta => $opciones) {
                $clave = "pregunta".$numPregunta;

                if (!isset($respuestasUsuario[$clave])) {
                    $resultado[$numPregunta] = false;
                    continue;
                }

                $respuestaUsuario = strtolower(trim($_SESSION["respuestasUsuario"][$clave]));
                $resultado[$numPregunta] = in_array($respuestaUsuario, array_map("strtolower", $opciones));
            }

            return $resultado;
        }

        // Función para guardar el resultado del test en la base de datos
        public function guardarResultado(array $post): bool {
            if (!$this->conn) {
                echo "<p>No hay conexión a la base de datos.</p>";
                return false;
            }

            // Extraer y validar
            $codigo = isset($post['codigo']) ? intval($post['codigo']) : 0;
            if ($codigo <= 0 || $codigo > 12) {
                echo "<p>ID de usuario inválido. Introduce un valor entre 1 y 12.</p>";
                return false;
            }

            $profesion = trim($post['profesion'] ?? '');
            $edad = isset($post['edad']) ? intval($post['edad']) : null;
            $genero = trim($post['genero'] ?? '');
            $pericia = isset($post['pericia']) ? intval($post['pericia']) : null;
            if ($pericia === null || $pericia < 0) {
                $pericia = 0;
            }
            if ($pericia > 10) {
                $pericia = 10;
            }

            $dispositivo = trim($post['dispositivo'] ?? '');
            $tiempoSQL = $_SESSION['tiempo'] ?? "00:00:00.000";
            $comentarios = trim($post['comentarios'] ?? '');
            $propuestas = trim($post['propuestas'] ?? '');
            $valoracion = isset($post['valoracion']) ? intval($post['valoracion']) : 0;
            if ($valoracion < 0) {
                $valoracion = 0;
            }
            if ($valoracion > 10) {
                $valoracion = 10;
            }

            $obs_facilitador = trim($post['obs_facilitador'] ?? '');

            // Iniciar transacción
            $this->conn->begin_transaction();
            try {
                // 1) Tabla usuario: update si existe, insert si no existe
                $stmt = $this->conn->prepare("SELECT id FROM usuario WHERE id = ?");
                if (!$stmt) {
                    throw new Exception($this->conn->error);
                }
                $stmt->bind_param("i", $codigo);
                $stmt->execute();
                $res = $stmt->get_result();
                $exists = (bool)$res->fetch_assoc();
                $stmt->close();

                if ($exists) {
                    $stmt = $this->conn->prepare("UPDATE usuario SET profesion = ?, edad = ?, genero = ?, pericia = ? WHERE id = ?");
                    if (!$stmt) {
                        throw new Exception($this->conn->error);
                    }
                    $stmt->bind_param("sisii", $profesion, $edad, $genero, $pericia, $codigo);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    $stmt = $this->conn->prepare("INSERT INTO usuario (id, profesion, edad, genero, pericia) VALUES (?, ?, ?, ?, ?)");
                    if (!$stmt) {
                        throw new Exception($this->conn->error);
                    }
                    $stmt->bind_param("isisi", $codigo, $profesion, $edad, $genero, $pericia);
                    $stmt->execute();
                    $stmt->close();
                }

                // 2) Tabla resultado: insertar (id_usuario, dispositivo, tiempo, completada, comentarios, propuestas, valoracion)
                $completada = 1;
                $stmt = $this->conn->prepare("INSERT INTO resultado (id_usuario, dispositivo, tiempo, completada, comentarios, propuestas, valoracion) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if (!$stmt) {
                    throw new Exception($this->conn->error);
                }
                // Tipos: i s s i s s i => "ississi"
                $stmt->bind_param("ississi", $codigo, $dispositivo, $tiempoSQL, $completada, $comentarios, $propuestas, $valoracion);
                $stmt->execute();
                $stmt->close();

                // 3) Tabla observaciones: insertar
                $stmt = $this->conn->prepare("INSERT INTO observaciones (id_usuario, comentarios) VALUES (?, ?)");
                if (!$stmt) {
                    throw new Exception($this->conn->error);
                }
                $stmt->bind_param("is", $codigo, $obs_facilitador);
                $stmt->execute();
                $stmt->close();

                $this->conn->commit();

                unset($_SESSION['respuestasUsuario'], $_SESSION['cronometro'], $_SESSION['tiempoSegundos']);
                $_SESSION['estadoTest'] = 'fin';

                return true;
            } catch (Exception $e) {
                $this->conn->rollback();
                echo "<p>Error al guardar en la BD: " . htmlspecialchars($e->getMessage())."</p>";
                return false;
            }
        }

        // Función para mostrar el formulario con las preguntas según el estado del test
        public function mostrarFormulario() {
            $estado = $_SESSION["estadoTest"];

            // ESTADO 1: Inicio de la prueba
            if ($estado === "inicio") {
                echo '
                    <form method="post">
                        <p>Cuando estés preparado, pulsa el siguiente botón para iniciar la prueba:</p>
                        <button name="iniciar" type="submit">Iniciar prueba</button>
                    </form>';
                return;
            }

            // ESTADO 2: Preguntas
            if ($estado === "preguntas") {
                echo '
                    <form method="post">
                        <label for="pregunta1">¿Cuántos años tiene el piloto murciano de MotoGP Fermín Aldeguer? (en diciembre de 2025): </label>
                        <input type="text" id="pregunta1" name="pregunta1" placeholder="Respuesta a la pregunta 1" required />
                        
                        <label for="pregunta2">¿Qué dorsal utiliza Fermín Aldeguer en su primera temporada en MotoGP?: </label>
                        <input type="text" id="pregunta2" name="pregunta2" placeholder="Respuesta a la pregunta 2" required />

                        <label for="pregunta3">¿Cuántas vueltas duró la carrera del Gran Premio de Le Mans de la temporada de 2025?: </label>
                        <input type="text" id="pregunta3" name="pregunta3" placeholder="Respuesta a la pregunta 3" required />

                        <label for="pregunta4">¿Quién fue el vencedor de la carrera del Gran Premio de Le Mans de la temporada de 2025?: </label>
                        <input type="text" id="pregunta4" name="pregunta4" placeholder="Respuesta a la pregunta 4" required />

                        <label for="pregunta5">¿En qué país europeo se encuentra el circuito de Le Mans?: </label>
                        <input type="text" id="pregunta5" name="pregunta5" placeholder="Respuesta a la pregunta 5" required />
                    
                        <label for="pregunta6">¿Qué piloto era el líder del mundial de MotoGP tras la celebración del Gran Premio de Le Mans de la temporada de 2025?: </label>
                        <input type="text" id="pregunta6" name="pregunta6" placeholder="Respuesta a la pregunta 6" required />

                        <label for="pregunta7">¿Llovió durante el fin de semana del Gran Premio de Le Mans de la temporada de 2025?: </label>
                        <input type="text" id="pregunta7" name="pregunta7" placeholder="Respuesta a la pregunta 7" required />

                        <label for="pregunta8">¿Con qué término inglés se conoce a la penalización que obliga a un piloto a realizar un recorrido más largo durante una carrera de MotoGP?: </label>
                        <input type="text" id="pregunta8" name="pregunta8" placeholder="Respuesta a la pregunta 8" required />

                        <label for="pregunta9">¿Cuántos puntos de diferencia había entre el líder del mundial de MotoGP y el segundo clasificado tras la celebración del Gran Premio de Le Mans de la temporada de 2025?: </label>
                        <input type="text" id="pregunta9" name="pregunta9" placeholder="Respuesta a la pregunta 9" required />
                        
                        <label for="pregunta10">¿En qué posición finalizó Fermín Aldeguer la temporada 2024 en el mundial de pilotos de Moto2?: </label>
                        <input type="text" id="pregunta10" name="pregunta10" placeholder="Respuesta a la pregunta 10" required />   

                        <button type="submit" name="terminar">Terminar prueba</button>
                    </form>';
                return;
            }

            // ESTADO 3: Formulario final
            if ($estado === "formularioFinal") {
                echo '
                    <form method="post">
                        <fieldset>
                            <legend>Identificación del usuario</legend>

                            <label for="codigo">Código del usuario evaluado:</label>
                            <input type="text" id="codigo" name="codigo" required />

                            <label for="profesion">Profesión:</label>
                            <input type="text" id="profesion" name="profesion" required />

                            <label for="edad">Edad:</label>
                            <input type="number" id="edad" name="edad" required />

                            <label for="genero">Género:</label>
                            <input type="text" id="genero" name="genero" required />

                            <label for="pericia">Nivel de pericia informática (0-10):</label>
                            <input type="number" id="pericia" name="pericia" min="0" max="10" required />
                        </fieldset>

                        <fieldset>
                            <legend>Información de la prueba</legend>

                            <label for="dispositivo">Dispositivo utilizado:</label>
                            <input type="text" id="dispositivo" name="dispositivo" required />
      
                            <label for="valoracion">Valoración de la aplicación (0-10):</label>
                            <input type="number" id="valoracion" name="valoracion" min="0" max="10" required />

                            <label for="comentarios">Comentarios del usuario:</label>
                            <textarea id="comentarios" name="comentarios" rows="4"></textarea>

                            <label for="propuestas">Propuestas de mejora:</label>
                            <textarea id="propuestas" name="propuestas" rows="4"></textarea>
                        </fieldset>

                        <fieldset>
                            <legend>Observaciones del facilitador</legend>

                            <label for="obs_facilitador">Observaciones del facilitador:</label>
                            <textarea id="obs_facilitador" name="obs_facilitador" rows="5"></textarea>
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
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }

        if (isset($_POST["terminar"])) {
            $test->terminarPreguntas($_POST);
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }

        if (isset($_POST["guardar"])) {
            $ok = $test->guardarResultado($_POST);
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
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
    <?php $test->mostrarFormulario(); ?>
</body>
</html>