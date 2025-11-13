// Clase Menu para gestionar el menú desplegable de navegación en dispositivos móviles
class Menu {

    constructor() {
        document.addEventListener("DOMContentLoaded", () => {
            const boton = document.querySelector("header button");
            const menu = document.querySelector("header nav");

            boton.addEventListener("click", () => { 
                menu.hidden = !menu.hidden; // Alterna la visibilidad del menú
                boton.setAttribute("aria-expanded", String(!menu.hidden));
            });

            // Reset automático al cambiar tamaño de ventana para "trackear" cuando se debe mostrar siempre el menú
            window.addEventListener("resize", () => {
                if (window.innerWidth >= 769) {
                    menu.hidden = false;
                    boton.setAttribute("aria-expanded", "true");
                }
            });
        });
    }
}

new Menu(); // Necesario instanciar la clase Menu para que funcione el desplegable correctamente