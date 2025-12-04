<?php
    class Configuracion {
        protected $server = "localhost";
        protected $user = "DBUSER2025";
        protected $pass = "DBPSWD2025";
        protected $dbname = "uo294013_db";

        protected $conn;

        // Constructor: establece la conexión y crea la base de datos si no existe
        public function __construct() {
            $this->conn = @new mysqli($this->server, $this->user, $this->pass);
            if ($this->conn->connect_error) {
                echo "<p>Conexión fallida: " . htmlspecialchars($this->conn->connect_error) . "</p>";
                return;
            }

            // Intentamos seleccionar la BD; si no existe, la creamos a partir del script (.sql)
            if (!$this->safeSelectDB()) {
                if ($this->crearBD()) {
                    // Intentamos seleccionar la BD de nuevo
                    if (!$this->safeSelectDB()) {
                        echo "<p>Error: la base de datos se creó pero no se pudo seleccionar.</p>";
                        return;
                    }
                } else {
                    echo "<p>No se pudo crear la base de datos desde el fichero SQL.</p>";
                    return;
                }
            }
            $this->conn->set_charset("utf8mb4");
        }

        // Función auxiliar para comprobar si se puede seleccionar la base de datos de forma segura
        private function safeSelectDB() : bool {
            try {
                return @$this->conn->select_db($this->dbname);
            } catch (mysqli_sql_exception $e) {
                return false;
            }
        }

        // Función para crear la base de datos y las tablas leyendo el archivo uo294013_db.sql. Devuelve true si se creó correctamente
        public function crearBD() : bool {
            // Conexión temporal para crear la base de datos (evitamos errores de "database in use")
            $tempConn = @new mysqli($this->server, $this->user, $this->pass);
            if ($tempConn->connect_error) {
                echo "<p>Error conexión temporal: " . htmlspecialchars($tempConn->connect_error) . "</p>";
                return false;
            }

            // Búsqueda del archivo .sql
            $path = __DIR__ . DIRECTORY_SEPARATOR . "uo294013_db.sql";
            if (!is_file($path) || !is_readable($path)) { // El archivo no existe o no es accesible
                echo "<p>No se encontró uo294013_db.sql en $path</p>";
                $tempConn->close();
                return false;
            }

            // Lectura y ejecución del archivo .sql
            $sql = file_get_contents($path);
            if ($sql === false) {
                echo "<p>Error leyendo el archivo SQL</p>";
                $tempConn->close();
                return false;
            }

            // Uso de multi_query para ejecutar el script completo
            if (!$tempConn->multi_query($sql)) {
                echo "<p>Error al ejecutar la sentencia SQL: " . htmlspecialchars($tempConn->error) . "</p>";
                $tempConn->close();
                return false;
            }

            // Consumo de los resultados para vaciar el buffer de la conexión
            do {
                if ($res = $tempConn->store_result()) {
                    $res->free();
                }
            } while ($tempConn->more_results() && $tempConn->next_result());

            $tempConn->close();
            return true;
        }

        // Función para borrar todos los registros de las tablas de la base de datos. Devuelve true si se vaciaron correctamente todas las tablas
        public function reiniciarBD() : bool{
            $tables = ["observaciones", "resultado", "usuario"];

            // Comprobación de conexión
            if (!($this->conn instanceof mysqli)) {
                echo "<p>Error: sin conexión activa.</p>";
                return false;
            }
            
            // Ejecución de las sentencias de borrado en la misma transacción segura
            $this->conn->begin_transaction();
            try {
                // Desactivación de FKs para evitar errores de integridad referencial
                $this->conn->query("SET FOREIGN_KEY_CHECKS=0");
                foreach ($tables as $t) {
                    // Escape del nombre de la tabla
                    $t_esc = $this->conn->real_escape_string($t);
                    $sql = "TRUNCATE TABLE `$t_esc`";
                    if (!$this->conn->query($sql)) {
                        throw new Exception("Error truncando $t: " . $this->conn->error);
                    }
                }
                // Reactivación de FKs
                $this->conn->query("SET FOREIGN_KEY_CHECKS=1");
                $this->conn->commit();
                return true;
            } catch (Exception $e) {
                $this->conn->rollback();
                echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
                return false;
            }
        }

        // Función para eliminar la base de datos por completo
        public function eliminarBD() : bool {
            // Primero, comprobación de conexión (en caso de que exista, cerrarla para evitar que la DB esté "in use")
            if ($this->conn instanceof mysqli) {
                @$this->conn->close();
                $this->conn = null;
            }
            
            // Conexión temporal para ejecutar el DROP DATABASE
            $tempConn = @new mysqli($this->server, $this->user, $this->pass);
            if ($tempConn->connect_error) {
                return false; // No se pudo conectar, no se puede eliminar la BD
            }

            $res = $tempConn->query("DROP DATABASE IF EXISTS `$this->dbname`");
            if (!$res) {
                echo "<p>Error al eliminar la base de datos: " . htmlspecialchars($tempConn->error) . "</p>";
            }
            // Se cierra la conexión y se devuelve true/false en función del resultado
            $tempConn->close();
            return (bool)$res;
        }

        // Función para exportar a CSV los datos de la base de datos
        public function exportarCSV() : bool {
            // Si ya se ha enviado salida, no se pueden enviar cabeceras
            if (headers_sent()) {
                echo "<p>No se pueden enviar cabeceras CSV: ya se envió contenido.</p>";
                return false;
            }

            // Limpieza de buffers de salida
            if (ob_get_level()) {
                ob_end_clean();
            }

            // Cabeceras para descargar las tablas como CSV
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="uo294013_db.csv"');

            $output = fopen('php://output', 'w');
            if ($output === false) {
                echo "<p>No se pudo abrir el stream de salida.</p>";
                return false;
            }

            // Lista de tablas a exportar
            $tablas = ["usuario", "resultado", "observaciones"];

            foreach ($tablas as $tabla) {
                // Consulta
                $query = "SELECT * FROM `$tabla`";
                $result = $this->conn->query($query);
                if (!$result) {
                    fputcsv($output, ["<error en la consulta de $tabla: " . $this->conn->error . ">"]);
                    continue;
                }

                // Filas
                while ($row = $result->fetch_assoc()) {
                    fputcsv($output, array_merge([$tabla], array_values($row)), ",", '"');
                }

                $result->free();
            }
            fclose($output);
            return true;
        }

        // Función para importar desde un CSV los datos de las pruebas a la base de datos fila por fila
        public function importarCSV($file): bool {
            if (!$file || $file["error"] !== UPLOAD_ERR_OK) {
                echo "<p>Error subiendo el archivo CSV.</p>";
                return false;
            }

            $handle = fopen($file["tmp_name"], "r");
            if (!$handle) {
                echo "<p>No se pudo abrir el archivo CSV.</p>";
                return false;
            }

            // Eliminar BOM si existe
            $bom = pack('H*','EFBBBF');
            $firstLine = fgets($handle);
            if (substr($firstLine, 0, 3) === $bom) {
                $firstLine = substr($firstLine, 3);
            } 
            rewind($handle); // Volver al inicio del archivo

            // Vaciar tablas antes de importar
            if (!$this->reiniciarBD()) {
                fclose($handle);
                return false;
            }

            while (($data = fgetcsv($handle, 0, ",", '"')) !== false) {
                if (empty($data[0])) {
                    continue; // Saltar líneas vacías
                }
                $stmt = null;
                try {
                    switch ($data[0]) {
                        case "usuario":
                            if (count($data) < 6) continue 2;
                            $stmt = $this->conn->prepare("INSERT INTO usuario (id, profesion, edad, genero, pericia) VALUES (?, ?, ?, ?, ?)");
                            $stmt->bind_param("isisi", $data[1], $data[2], $data[3], $data[4], $data[5]);
                            break;

                        case "resultado":
                            if (count($data) < 9) continue 2;
                            $stmt = $this->conn->prepare("INSERT INTO resultado (id_usuario, dispositivo, tiempo, completada, respuestas, comentarios, propuestas, valoracion) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param("ississsi", $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8]);
                            break;

                        case "observaciones":
                            if (count($data) < 3) continue 2;
                            $stmt = $this->conn->prepare("INSERT INTO observaciones (id_usuario, comentarios) VALUES (?, ?)");
                            $stmt->bind_param("is", $data[1], $data[2]);
                            break;

                        default:
                            continue 2;
                    }

                    if ($stmt) {
                        $stmt->execute();
                        $stmt->close();
                    }
                } catch (Exception $e) {
                    echo "<p>Error al insertar datos: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
            }

            fclose($handle);
            return true;
        }

        // Destructor: cierra la conexión si está abierta
        public function __destruct() {
            if ($this->conn instanceof mysqli) {
                @$this->conn->close();
                $this->conn = null;
            }
        }
    }

    $config = new Configuracion();

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_POST["reiniciar"])) { // Reinicio de la base de datos
            $msg = $config->reiniciarBD() ? "Base de datos reiniciada" : "Error al reiniciar";
        } elseif (isset($_POST["eliminar"])) { // Eliminación de la base de datos
            $msg = $config->eliminarBD() ? "Base de datos eliminada" : "Error al eliminar";
        } elseif (isset($_POST["exportar"])) { // Exportación de resultados a .csv
            $config->exportarCSV();
            exit;
        } elseif (isset($_POST["importar"])) {
            $msg = $config->importarCSV($_FILES["csvfile"] ?? null)
                ? "Importación completada con éxito."
                : "Error al importar los datos del archivo CSV. Asegúrate de adjuntar un archivo válido.";
        }
    }
?>
<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="author" content="Vicente Megido García (UO294013)" />
    <meta name="description" content="Página de configuración de la base de datos para las pruebas de usabilidad de la web MotoGP - Desktop" />
    <meta name="keywords" content="MotoGP, aplicación, carreras, motos" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MotoGP - Configuración Test</title>
    <link rel="icon" href="../multimedia/favicon.ico" sizes="48x48">
    <link rel="stylesheet" type="text/css" href="../estilo/estilo.css" />
    <link rel="stylesheet" type="text/css" href="../estilo/layout.css" />
</head>

<body>
    <h1>Configuración Test</h1>
    <main>
        <?php if (isset($msg)) echo "<p>$msg</p>"; ?>
        <form method="post" enctype="multipart/form-data">
            <button name="reiniciar" type="submit">Reiniciar base de datos (vaciar tablas)</button>
            <button name="eliminar" type="submit">Eliminar base de datos</button>
            <button name="exportar" type="submit">Exportar resultados (.csv)</button>
            
            <label for="csvfile">Importar datos desde CSV:</label>
            <input type="file" id="csvfile" name="csvfile" accept=".csv" />
            <button name="importar" type="submit">Importar CSV</button>
        </form>
    </main>
    <footer>
        <p>© MotoGP - Desktop | Software y Estándares para la Web (SEW), Curso 2025-2026 | Vicente Megido García (UO294013) - Todos los derechos reservados</p>
    </footer>
</body>
</html>