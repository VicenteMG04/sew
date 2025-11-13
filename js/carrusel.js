class Carrusel {
    // Atributos privados
    #busqueda;
    #actual;
    #maximo;
    #imagenes;

    constructor() {
        this.#busqueda = "Le Mans, MotoGP"; // Término(s) de búsqueda en la llamada a la API de Flickr
        this.#actual = 0; // Número actual de elementos del carrusel
        this.#maximo = 5; // Número máximo de elementos a mostrar en el carrusel
        // Para almacenar las URLs de las imágenes obtenidas
        this.#imagenes = [];
    }

    getFotografias() {
        return new Promise((resolve, reject) => {
            $.ajax({
                dataType: "jsonp", // Utilizar JSONP para evitar problemas de CORS
                jsonp: "jsoncallback",
                jsonpCallback: "jsonFlickrFeed", // Nombre de la función de callback esperada por Flickr
                url: "https://api.flickr.com/services/feeds/photos_public.gne",
                data: {
                    tags: this.#busqueda,
                    tagmode: "all",
                    format: "json",
                },
                method: "GET",
                success: (data) => {
                    this.#procesarJSONFotografias(data);
                    this.#mostrarFotografias();
                    resolve();
                },
                error: () => {
                    $("h2").html("Error al cargar las imágenes desde Flickr");
                }
            });
        });
    }

    #procesarJSONFotografias(data) {
        // Reinicio del array de imágenes para no duplicar ni exceder el máximo
        this.#imagenes = [];

        if (!data || !data.items || data.items.length === 0) {
            $("h2").html("No se han podido encontrar fotografías en el objeto JSON devuelto por Flickr");
            return;
        }

        // Si no se encuentran 5 fotografías, ajustar el máximo
        let max = Math.min(this.#maximo, data.items.length);

        for (let i = 0; i < max; i++) {
            let foto = data.items[i];
            let url = foto.media.m.replace('_m', '_z'); // z -> 640px lado largo
            this.#imagenes.push(url);
        }
    }

    #mostrarFotografias() {
        if (this.#imagenes.length === 0) {
            $("h2").html("No se han podido recuperar imágenes disponibles desde Flickr");
            return;
        }

        let articulo = $("<article></article>");
        // Encabezado <h2> con el nombre del circuito
        let encabezado = $("<h2></h2>").text("Imágenes del circuito de Le Mans");
        let imagen = $("<img>").attr("src", this.#imagenes[0]).attr("alt", "Fotografía del circuito de Le Mans");

        articulo.append(encabezado);
        articulo.append(imagen);
        $("main").append(articulo);
        // Cambio de fotografía cada 3 segundos
        setInterval(this.#cambiarFotografia.bind(this), 3000);
    }

    #cambiarFotografia() {
        this.#actual++;
        // Si se llega al final del carrusel, vuelve al principio
        if (this.#actual >= this.#maximo) {
            this.#actual = 0;
        }
        $("main img").attr("src", this.#imagenes[this.#actual]);
    }
}
