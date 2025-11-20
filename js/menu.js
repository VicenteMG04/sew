// Clase Menu para gestionar el menú desplegable de navegación en dispositivos móviles
class Menu {

    constructor() {
        document.addEventListener("DOMContentLoaded", () => {
            const boton = document.querySelector("header button");
            const menu = document.querySelector("header nav");

            // Acción al hacer clic en el botón del menú (contraer/expandir)
            boton.addEventListener("click", () => { 
                menu.hidden = !menu.hidden; // Alterna la visibilidad del menú
                boton.setAttribute("aria-expanded", String(!menu.hidden));
            });

            // Mostrar el menú automáticamente en pantallas grandes (en móviles, oculto por defecto)
            if (window.innerWidth >= 769) {
                menu.hidden = false;
                boton.setAttribute("aria-expanded", "true");
            } 
            
            // Reset automático al cambiar tamaño de ventana para "trackear" cuando se debe mostrar el menú
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