<?php
    class Cronometro {
        protected $tiempo;
        protected $inicio;
        protected $corriendo; // Parámetro creado de forma adicional para controlar el estado del cronómetro y evitar comportamientos inesperados

        public function __construct() {
            $this->tiempo = 0;
            $this->corriendo = false;
        }

        public function arrancar() {
            if (!$this->corriendo) {
                $this->tiempo = 0; // Reiniciamos el tiempo cada vez que se arranca
                $this->inicio = microtime(true); // Con microtime(true) podemos obtener el tiempo en segundos con microsegundos como fracción decimal
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
            $tiempoActual = $this->getTiempo();
            $minutos = floor($tiempoActual / 60);
            $segundos = floor($tiempoActual % 60);
            $decimas = floor(($tiempoActual - floor($tiempoActual)) * 10);
            return sprintf("%02d:%02d.%d", $minutos, $segundos, $decimas); // Formato MM:SS.d
        }

        // Función auxiliar para obtener el tiempo transcurrido en segundos (float)
        public function getTiempo() {
            $tiempoActual = $this->tiempo;
            if ($this->corriendo) {
                $tiempoActual += microtime(true) - $this->inicio;
            }
            return $tiempoActual;
        }

        // Práctica 2 de PHP: Función para obtener el tiempo en el formato requerido para la base de datos de MySQL (HH:MM:SS.mmm)
        public function getTiempoBD() {
            $totalSegundos = $this->getTiempo();
            $horas = floor($totalSegundos / 3600);
            $minutos = floor(($totalSegundos % 3600) / 60);
            $segundos = floor($totalSegundos % 60);
            $milisegundos = round(($totalSegundos - floor($totalSegundos)) * 1000);
            return sprintf("%02d:%02d:%02d.%03d", $horas, $minutos, $segundos, $milisegundos);
        }
    }
?>