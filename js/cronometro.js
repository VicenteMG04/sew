class Cronometro {
    // Atributos privados
    #tiempo;
    #corriendo;
    #inicio;
    #minutos;
    #segundos;
    #decimas;

    constructor() {
        this.#tiempo = 0;
    }

    arrancar() {
        if (this.#corriendo == null && this.#tiempo == 0) {
            try {
                this.#inicio = Temporal.now();
            } catch (e) {
                this.#inicio = Date.now(); // Si Temporal no está disponible
            }
            // Utilizar bind() para asegurar que 'this' se refiere a la instancia de Cronometro
            this.#corriendo = setInterval(this.#actualizar.bind(this), 100); // 100 ms -> actualizar cada 0.1 segundos
        }
    }

    #actualizar() {
        try {
            this.#tiempo = Number(Temporal.now().instant().since(this.#inicio, { smallestUnit: 'milliseconds' }).total('milliseconds'));
        } catch (e) {
            this.#tiempo = Date.now() - this.#inicio; // Si Temporal no está disponible
        }
        this.#mostrar();
    }

    #mostrar() {
        this.#minutos = parseInt(this.#tiempo / 60000);
        this.#segundos = parseInt((this.#tiempo % 60000) / 1000);
        this.#decimas = parseInt((this.#tiempo % 1000) / 100);

        const mm = String(this.#minutos).padStart(2, '0');
        const ss = String(this.#segundos).padStart(2, '0');
        const d = this.#decimas;
        const p = document.querySelector('main p:nth-of-type(1)');
        p.textContent = `${mm}:${ss}.${d}`;
    }

    parar() {
        clearInterval(this.#corriendo);
        this.#corriendo = null;
    }

    #reiniciar() {
        clearInterval(this.#corriendo);
        this.#corriendo = null;
        this.#tiempo = 0;
        this.#mostrar();
    }

    // Método auxiliar para la refactorización
    añadirListeners() {
        const botonArrancar = document.querySelector("main button:nth-of-type(1)");
        const botonParar = document.querySelector("main button:nth-of-type(2)");
        const botonReiniciar = document.querySelector("main button:nth-of-type(3)");
        botonArrancar.addEventListener("click", () => this.arrancar());
        botonParar.addEventListener("click", () => this.parar());
        botonReiniciar.addEventListener("click", () => this.#reiniciar());
    }
}
