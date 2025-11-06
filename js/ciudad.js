class Ciudad {
    // Atributos privados
    #nombre;
    #pais;
    #gentilicio;
    #poblacion;
    #coordenadas;

    constructor(nombre, pais, gentilicio) {
        this.#nombre = nombre; /* "Le Mans"; */
        this.#pais = pais; /* "Francia"; */
        this.#gentilicio = gentilicio; /* "Manceaux"; */
        this.#poblacion = 146000; /* 146000; */
        this.#coordenadas = { latitud: 48.007755, longitud: 0.199860 }; /* { latitud: 48.007755, longitud: 0.199860 }; */
    }

    completarInfo() {
        this.#poblacion = 146000;
        this.#coordenadas = {
            latitud: 48.007755,
            longitud: 0.199860
        };
    }

    #getNombre() {
        return this.#nombre;
    }

    #getPais() {
        return this.#pais;
    }

    #getInfoSecundaria() {
        return "<ul><li>Gentilicio: " + this.#gentilicio + "</li><li>Población: " + this.#poblacion + " habitantes</li></ul>";
    }

    getCoordenadas() {
        const main = document.querySelector("main");
        const coordenadas = document.createElement("p");
        coordenadas.textContent = "Coordenadas: { Latitud: " + this.#coordenadas.latitud + ", Longitud: " + this.#coordenadas.longitud + " }";
        main.appendChild(coordenadas);
    }

    mostrarInfo() {
        const main = document.querySelector("main");
        const ciudad = document.createElement("p");
        ciudad.textContent = "Ciudad: " + this.#getNombre();
        main.appendChild(ciudad);
        const pais = document.createElement("p");
        pais.textContent = "País: " + this.#getPais();
        main.appendChild(pais);
        main.innerHTML += this.#getInfoSecundaria();
    }
}