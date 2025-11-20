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

    leerArchivoSVG(fichero) {
        if (fichero && fichero.type.match(/svg.*/)) {
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

    insertarSVG(svgTexto) {
        const parser = new DOMParser();
        const documentoSVG = parser.parseFromString(svgTexto, 'image/svg+xml');
        const elementoSVG = documentoSVG.documentElement;
        // Atributos necesarios para que el SVG sea "responsive"
        elementoSVG.setAttribute("viewBox", "0 0 800 400");
        elementoSVG.setAttribute("preserveAspectRatio", "xMidYMid meet");
        const padre = document.querySelector("main article:nth-of-type(2)");
        padre.appendChild(elementoSVG);
    }
}

class CargadorKML {

    leerArchivoKML(fichero) {
        if (fichero && (fichero.type.match(/kml.*/) || fichero.name.toLowerCase().endsWith(".kml"))) { // En algunos navegadores el tipo MIME de KML puede no estar definido
            const lector = new FileReader();
            lector.onload = (e) => {
                this.insertarCapaKML(e.target.result);
            };
            lector.readAsText(fichero);
        } else {
            const p = document.createElement("p");
            p.textContent = "No se puede cargar el archivo. Asegúrate de que es un archivo KML.";
            document.querySelector("main article:nth-of-type(3)").appendChild(p);
        }
    }

    async insertarCapaKML(kmlTexto) {
        const parser = new DOMParser();
        const xmlDoc = parser.parseFromString(kmlTexto, "application/xml");

        // Todas las coordenadas guardadas en el archivo KML
        const coords = xmlDoc.getElementsByTagName("coordinates");
        const puntos = [];
        for (let i = 0; i < coords.length; i++) {
            const conjunto = coords[i].textContent.trim().split(/\s+/);
            for (let j = 0; j < conjunto.length; j++) {
                const [lng, lat] = conjunto[j].split(",").map(Number);
                if (!isNaN(lat) && !isNaN(lng)) {
                    puntos.push({ lat: lat, lng: lng });
                }
            }
        }

        if (puntos.length === 0) {
            const p = document.createElement("p");
            p.textContent = "No se encontraron coordenadas válidas en el archivo KML.";
            document.querySelector("main article:nth-of-type(3)").appendChild(p);
            return;
        }

        const padre = document.querySelector("main article:nth-of-type(3)");
        const contenedor = document.createElement("div");
        padre.appendChild(contenedor);

        const mapa = new google.maps.Map(contenedor, {
            center: { lat: 47.953839, lng: 0.210905 }, // Mapa centrado en el centro del circuito
            zoom: 15,
            mapId: 'DEMO_MAP_ID' // Requiere habilitar mapas personalizados en Google Cloud Console
        });

        const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");

        new AdvancedMarkerElement({ position: puntos[0], map: mapa, title: "Inicio (línea de salida)" });

        new google.maps.Polyline({
            path: puntos,
            map: mapa,
            strokeColor: "#FF0000",
            strokeOpacity: 0.8,
            strokeWeight: 3
        });
    }
}