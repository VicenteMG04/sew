<?php
    session_start();
    class Configuracion {
        protected $server;
        protected $user;
        protected $pass;
        protected $dbname;
        protected $conn;

        public function __construct(){
            $this->server = "localhost";
            $this->user = "DBUSER2025";
            $this->pass = "DBPSWD2025";
            $this->dbname = "uo294013_db";
            // Inicialización de la conexión
            $this->conn = new mysqli(
                $this->server,
                $this->user,
                $this->pass,
                $this->dbname
            );
            $this->conn->set_charset('utf8mb4');
            if ($this->conn->connect_error) {
                die('Error de conexión: ' . $this->conn->connect_error);
            }   
        }

        // Función para crear la base de datos y las tablas leyendo linea por linea del archivo uo294013_db.sql
        public function crearBD() {
            $tempConn = new mysqli($this->server, $this->user, $this->pass);
            $path = __DIR__ . DIRECTORY_SEPARATOR . 'uo294013_db.sql';
            if (!is_file($path) || !is_readable($path)) { // El archivo no existe o no es accesible
                echo "<p>No se encontró uo294013_db.sql en $path</p>";
                return;
            }
            if (($file = fopen($path, 'r')) !== false) {
                $sql = "";
                while (($line = fgets($file)) !== false) {
                    $line = trim($line);
                    if (empty($line) || strpos($line, "--") === 0 || strpos($line, "/*") === 0) {
                        continue;
                    }
                    $sql .= $line;
                    if (substr($line, -1) === ";") {
                        $tempConn->query($sql);
                        $sql = "";
                    }
                }
                fclose($file);
            } else {  // El archivo no se pudo abrir
                echo "<p>Error al abrir el archivo que inicializa la base de datos</p>";
            }
            $tempConn->close();
        }
    
        // Función para importar datos de un archivo CSV a la base de datos
        public function importarCsv() {
            $path = __DIR__ . DIRECTORY_SEPARATOR .   'uo294013_db.csv';
            if (!is_file($path) || !is_readable($path)) { // El archivo no existe o no es accesible
                echo "<p>No se encontró uo294013_db.csv en $path</p>";
                return;
            }
            $showErrors = false;
            if (($handle = fopen($path, "r")) !== false) {
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    try {
                        switch ($data[0]) {
                            /*
                            case "estado_reserva": // estado
                                $stmt = $this->conn->prepare("INSERT INTO estado_reserva (estado) VALUES (?)");
                                $stmt->bind_param("s", $data[1]);
                                break;
                            case "tipo_recurso": // nombre
                                $stmt = $this->conn->prepare("INSERT INTO tipo_recurso (nombre) VALUES (?)");
                                $stmt->bind_param("s", $data[1]);
                                break;
                            case "recurso": // tipo_recurso_id, nombre, limite_ocupacion, precio, descripcion
                                $stmt = $this->conn->prepare("INSERT INTO recurso (tipo_recurso_id, nombre, limite_ocupacion, precio, descripcion) VALUES (?, ?, ?, ?, ?)");
                                $stmt->bind_param("isids", $data[1], $data[2], $data[3], $data[4], $data[5]);
                                break;
                            case "usuario": // nombre, apellidos, password, email, fecha_alta
                                $hashedPassword = password_hash($data[3], PASSWORD_DEFAULT);
                                $stmt = $this->conn->prepare("INSERT INTO usuario (nombre, apellidos, password, email, fecha_alta) VALUES (?, ?, ?, ?, ?)");
                                $stmt->bind_param("sssss", $data[1], $data[2], $hashedPassword, $data[4], $data[5]);
                                break;
                            case "reserva": // usuario_id, recurso_id, estado_id, presupuesto, fecha_hora_inicio, fecha_hora_fin
                                $stmt = $this->conn->prepare("INSERT INTO reserva (usuario_id, recurso_id, estado_id, presupuesto, fecha_hora_inicio, fecha_hora_fin) VALUES (?, ?, ?, ?, ?, ?)");
                                $stmt->bind_param("iiidss", $data[1], $data[2], $data[3], $data[4], $data[5], $data[6]);
                                break;
                            */
                        }
                        $stmt->execute();
                        $stmt->close();
                    } catch (Exception $e) {
                        $showErrors = true;
                    }
                }
                fclose($handle);
                if ($showErrors) { // La estructura del CSV no es correcta o hay errores al insertar en la base de datos
                    echo "<p>Algunos datos no se han podido importar correctamente debido a inconsistencias con la base de datos</p>";
                }
            } else { // No see pudo abrir el archivo CSV
                echo "<p>Error al importar los datos a la base de datos</p>";
            }
        }

        // Función para añadir información a la web encodeada correctamente
        public function imprimirOut($s) { 
            return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); 
        }

        /*
        // Función que devuelve todas las reservas activas del usuario en sesión
        public function getReservasUsuario(int $usuarioId): ?array {
            $stmt = $this->conn->prepare("
                SELECT
                    res.id,
                    r.nombre AS recurso_nombre,
                    er.estado AS estado,
                    res.presupuesto,
                    res.fecha_hora_inicio,
                    res.fecha_hora_fin
                FROM reserva res
                JOIN recurso r 
                    ON res.recurso_id = r.id
                JOIN estado_reserva er
                    ON res.estado_id = er.id
                WHERE res.usuario_id = ?
                ORDER BY res.fecha_hora_inicio DESC
            ");
            $stmt->bind_param('i', $usuarioId);
            $stmt->execute();
            $result = $stmt->get_result();
            $reservas = [];
            while ($row = $result->fetch_assoc()) {
                $reservas[] = $row;
            }
            $stmt->close();
            return $reservas;
        }
        */
    }

    $configuracion = new Configuracion();
    $action = $_REQUEST['action'] ?? 'home';
    // Inicialización de la base de datos (con datos de los ficheros uo294013_db.sql y uo294013_db.csv)
    if ($action === 'init') {
        $configuracion->crearBD();
        $configuracion->importarCsv();
        header('Location: uo294013_db.php?action=home');
        exit;
    }
?>