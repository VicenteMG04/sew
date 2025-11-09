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

    getMeteorologiaCarrera() {
        // Promesas para preservar el orden de ejecución
        return $.ajax({
            dataType: "json",
            url: "https://archive-api.open-meteo.com/v1/archive?latitude=48.007755&longitude=0.19986&start_date=2025-05-12&end_date=2025-05-12&daily=sunrise,sunset&hourly=temperature_2m,apparent_temperature,rain,relative_humidity_2m,wind_speed_10m,wind_direction_10m&timezone=Europe%2FBerlin",
            method: "GET"
        }).done((data) => this.#procesarJSONCarrera(data))
        .fail(() => $("h2").html("Error al cargar la información meteorológica de la carrera"));
    }

    #procesarJSONCarrera(data) {
        const main = $("main");
        main.append("<h3>Datos meteorológicos del día de la carrera (12 de mayo de 2025)</h3>");

        // --> Datos diarios
        const sunrise = data.daily.sunrise[0].split("T")[1];
        const sunset = data.daily.sunset[0].split("T")[1];
        main.append("<p>Hora de salida del sol (amanecer): " + sunrise + "</p>");
        main.append("<p>Hora de puesta del sol (anochecer): " + sunset + "</p>");

        // --> Datos horarios
        const horas = data.hourly.time;
        const temperaturas = data.hourly.temperature_2m;
        const sensaciones_termicas = data.hourly.apparent_temperature;
        const lluvias = data.hourly.rain;
        const humedades = data.hourly.relative_humidity_2m;
        const velocidades_viento = data.hourly.wind_speed_10m;
        const direcciones_viento = data.hourly.wind_direction_10m;

        main.append(`<h4>Hora de la carrera: ${horas[14].split("T")[1]} CEST (GMT+2)</h4>`);
        const bloque = `
            <ul>
                <li>Temperatura: ${temperaturas[14]} °C</li>
                <li>Sensación Térmica: ${sensaciones_termicas[14]} °C</li>
                <li>Lluvia: ${lluvias[14]} mm</li>
                <li>Humedad: ${humedades[14]} %</li>
                <li>Velocidad del Viento: ${velocidades_viento[14]} km/h</li>
                <li>Dirección del Viento: ${direcciones_viento[14]} °</li>
            </ul>
        `;
        main.append(bloque);
    }

    getMeteorologiaEntrenos() {
        return $.ajax({
            dataType: "json",
            url: "https://archive-api.open-meteo.com/v1/archive?latitude=48.007755&longitude=0.19986&start_date=2025-05-09&end_date=2025-05-11&hourly=temperature_2m,rain,relative_humidity_2m,wind_speed_10m&timezone=Europe%2FBerlin",
            method: "GET"
        }).done((data) => this.#procesarJSONEntrenos(data))
        .fail(() => $("h2").html("Error al cargar la información meteorológica de los entrenamientos"));
    }

    #procesarJSONEntrenos(data) {
        const main = $("main");
        main.append("<h3>Datos meteorológicos de los días de entrenamiento (9-11 de mayo de 2025)</h3>");

        // Datos horarios
        const horas = data.hourly.time;
        const temperaturas = data.hourly.temperature_2m;
        const humedades = data.hourly.relative_humidity_2m;
        const lluvias = data.hourly.rain;
        const velocidades_viento = data.hourly.wind_speed_10m;

        // Días de entrenamiento
        const dias = [];
        for (let i = 0; i < horas.length; i++) {
            const dia = horas[i].split("T")[0];
            if (!dias.includes(dia)) {
                dias.push(dia);
            }
        }

        // Cálculo de datos promedio para cada día
        dias.forEach(dia => {
            // Índices de las horas correspondientes al día actual
            const indicesDia = [];
            for (let i = 0; i < horas.length; i++) {
                if (horas[i].startsWith(dia)) {
                    indicesDia.push(i);
                }
            }
                
            const mediaTemp = (indicesDia.reduce((c, i) => c + temperaturas[i], 0) / indicesDia.length).toFixed(2);
            const mediaHum = (indicesDia.reduce((c, i) => c + humedades[i], 0) / indicesDia.length).toFixed(2);
            const mediaLluvia = (indicesDia.reduce((c, i) => c + lluvias[i], 0) / indicesDia.length).toFixed(2);
            const mediaViento = (indicesDia.reduce((c, i) => c + velocidades_viento[i], 0) / indicesDia.length).toFixed(2);

            const bloque = `
                <h4>Día ${dia}</h4>
                <ul>
                    <li>Temperatura media: ${mediaTemp} °C</li>
                    <li>Humedad media: ${mediaHum} %</li>
                    <li>Lluvia media: ${mediaLluvia} mm</li>
                    <li>Velocidad media del viento: ${mediaViento} km/h</li>
                </ul>
            `;
            main.append(bloque);
        });
    }
}