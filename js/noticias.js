class Noticias {
    // Atributos privados
    #busqueda;
    #url;
    #apiKey;
    
    constructor() {
        this.#busqueda = "MotoGP";
        this.#url = "https://api.thenewsapi.com/v1/news/all";
        this.#apiKey = "udb6f7ArWQFjLudHAh4MXdtIX7EJPCB2KDOmxcoS";
    }

    async buscar() {
        const peticion = `${this.#url}?api_token=${this.#apiKey}&search=${this.#busqueda}&language=es&limit=5`;

        try {
            const respuesta = await fetch(peticion);
            if (!respuesta.ok) {
                throw new Error("Error en la respuesta de la API");
            }
            const datos = await respuesta.json();
            this.procesarInformacion(datos);
        } catch (error) {
            const section = document.createElement("section");
            const errorMsg = document.createElement("p");
            errorMsg.textContent = "No se han podido cargar las noticias.";
            section.appendChild(errorMsg);
            document.querySelector("main").appendChild(section);
        }
    }

    procesarInformacion(datosJSON) {
        const noticias = datosJSON.data;
        const section = document.createElement("section");
        const titulo = document.createElement("h3");
        titulo.textContent = "Noticias MotoGP";
        section.appendChild(titulo);

        noticias.forEach(noticia => {
            const articulo = document.createElement("article");

            const titular = document.createElement("h4");
            titular.textContent = noticia.title;
            articulo.appendChild(titular);

            const descripcion = document.createElement("p");
            descripcion.textContent = noticia.description || "Sin descripción disponible.";
            articulo.appendChild(descripcion);

            const fuente = document.createElement("p");
            fuente.textContent = "Fuente: " + (noticia.source || "Desconocida");
            articulo.appendChild(fuente);

            const enlace = document.createElement("a");
            enlace.textContent = "Leer noticia completa";
            enlace.href = noticia.url;
            enlace.target = "_blank";
            articulo.appendChild(enlace);
            section.appendChild(articulo);
        });
        document.querySelector("main").appendChild(section);
    }
}