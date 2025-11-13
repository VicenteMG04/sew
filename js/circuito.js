class Circuito {

    constructor() {
        this.#comprobarAPIFile();
    }

    #comprobarAPIFile() {
        // Si NO se cumple alguna condición, el navegador no soporta la API File
        if (!window.File || !window.FileReader || !window.FileList || !window.Blob) {
            const p = document.createElement("p");
            p.textContent = "La API File no es soportada en este navegador.";
            document.querySelector("main").appendChild(p);
        }
    }

    leerArchivoHTML(fichero) {
        // Limpiar primero el contenido previo sin borrar el encabezado h2 ni el selector de archivos
        const h2 = document.querySelector("main h2");
        const inputFile = document.querySelector("input[type='file']");
        document.querySelector("main").innerHTML = "";
        document.querySelector("main").appendChild(h2);
        document.querySelector("main").appendChild(inputFile);

        var tipoFichero = /html.*/;
        if (fichero.type.match(tipoFichero)) {
            var lector = new FileReader();
            lector.onload = () => {
                // El evento "onload" se lleva a cabo cada vez que se completa con éxito una operación de lectura
                // La propiedad "result" es donde se almacena el contenido del archivo
                // Esta propiedad solamente es válida cuando se termina la operación de lectura
                var parser = new DOMParser();
                var doc = parser.parseFromString(lector.result, "text/html");
                const contenido = doc.body.querySelectorAll("main > *");
                contenido.forEach(element => {
                    if (element.tagName != "H2") { // Evitar duplicar el encabezado h2
                        document.querySelector("main").appendChild(element);
                    }
                });
            }      
            lector.readAsText(fichero);
        } else {
            document.createElement("p").textContent = "El archivo no es del tipo HTML.";
            document.querySelector("main").appendChild(p);
        }   
    }
}