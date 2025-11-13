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
        const contenedor = document.querySelector("main article:nth-of-type(1)");
        const inputFile = contenedor.querySelector("input");
        contenedor.innerHTML = "";
        contenedor.appendChild(inputFile);
        
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
                        contenedor.appendChild(element);
                    }
                });
            }      
            lector.readAsText(fichero);
        } else {
            const p = document.createElement("p");
            p.textContent = "El archivo no es del tipo HTML.";
            contenedor.appendChild(p);
        }   
    }
}

class CargadorSVG {

    constructor() {

    }

    leerArchivoSVG(fichero) {
        if (fichero && fichero.type === 'image/svg+xml') {
            const lector = new FileReader();
            lector.onload = (e) => {
                this.insertarSVG(e.target.result);
            };
            lector.readAsText(fichero);
        } else {
            const p = document.createElement("p");
            p.textContent = "No se puede cargar el archivo. Asegúrate de que es un archivo SVG.";
            document.querySelector("main article:nth-of-type(2)").appendChild(p);
        }
    }

    insertarSVG(svg) {
        const parser = new DOMParser();
        const documentoSVG = parser.parseFromString(svg, 'image/svg+xml');
        const elementoSVG = documentoSVG.documentElement;
        const contenedor = document.querySelector("main article:nth-of-type(2) svg");
        contenedor.replaceWith(elementoSVG);
    }
}