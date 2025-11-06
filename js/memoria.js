class Memoria {
    // Atributos privados
    #tablero_bloqueado;
    #primera_carta;
    #segunda_carta;
    #cronometro;

    constructor() {
        this.#tablero_bloqueado = true;
        this.#primera_carta = null;
        this.#segunda_carta = null;
        this.#barajarCartas();
        this.#tablero_bloqueado = false;
        this.#cronometro = new Cronometro();
        this.#cronometro.arrancar();
        this.#añadirListeners(); // Refactorización para eliminar los onclick en el HTML
    }

    #voltearCarta(carta) {
        if (carta.getAttribute("data-estado") !== "volteada" && carta.getAttribute("data-estado") !== "revelada" && !this.#tablero_bloqueado) {
            carta.setAttribute("data-estado", "volteada");
            if (this.#primera_carta === null) {
                this.#primera_carta = carta;
            } else {
                this.#segunda_carta = carta;
                this.#comprobarPareja();
            }
        }
    }

    #barajarCartas() {
        const contenedor = document.querySelector('main');
        let cartas = Array.from(contenedor.querySelectorAll('article'));
        for (let i = cartas.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [cartas[i], cartas[j]] = [cartas[j], cartas[i]];
        }
        cartas.forEach(carta => contenedor.appendChild(carta));
    }

    #reiniciarAtributos() {
        this.#tablero_bloqueado = true;
        this.#primera_carta = null;
        this.#segunda_carta = null;
    }

    #deshabilitarCartas() {
        this.#primera_carta.setAttribute("data-estado", "revelada");
        this.#segunda_carta.setAttribute("data-estado", "revelada");
        this.#reiniciarAtributos();
        if (this.#comprobarJuego()) {
            this.#cronometro.parar();
        } else {
            this.#tablero_bloqueado = false;
        }
    }

    #comprobarJuego() {
        let todas_reveladas = true;
        const contenedor = document.querySelector('main');
        let cartas = Array.from(contenedor.querySelectorAll('article'));
        cartas.forEach(carta => {
            if (carta.getAttribute("data-estado") !== "revelada") {
                todas_reveladas = false;
            } 
        });
        return todas_reveladas;
    }

    #cubrirCartas() {
        this.#tablero_bloqueado = true;
        setTimeout(() => {
            this.#primera_carta.setAttribute("data-estado", "");
            this.#segunda_carta.setAttribute("data-estado", "");
            this.#reiniciarAtributos();
            this.#tablero_bloqueado = false;
        }, 1500); // Delay de 1.5 segundos antes de cubrir las cartas de nuevo
    }

    #comprobarPareja() {
        let iguales = this.#primera_carta.querySelector('img').getAttribute("alt") == this.#segunda_carta.querySelector('img').getAttribute("alt");
        iguales ? this.#deshabilitarCartas() : this.#cubrirCartas();
    }

    // Método auxiliar para la refactorización
    #añadirListeners() {
        const cartas = document.querySelectorAll('main article');
        cartas.forEach(carta => {
            carta.addEventListener('click', () => this.#voltearCarta(carta));
        });
    }
}