/* ACE — interações da landing page */
const menuToggle = document.querySelector(".menu-toggle");
const menu = document.getElementById("menuPrincipal");

if (menuToggle && menu) {
    menuToggle.addEventListener("click", () => {
        const aberto = menu.classList.toggle("is-open");
        menuToggle.setAttribute("aria-expanded", String(aberto));
        menuToggle.setAttribute("aria-label", aberto ? "Fechar menu" : "Abrir menu");
    });

    menu.querySelectorAll("a").forEach(link => {
        link.addEventListener("click", () => {
            menu.classList.remove("is-open");
            menuToggle.setAttribute("aria-expanded", "false");
            menuToggle.setAttribute("aria-label", "Abrir menu");
        });
    });

    document.addEventListener("click", evento => {
        if (!menu.contains(evento.target) && !menuToggle.contains(evento.target)) {
            menu.classList.remove("is-open");
            menuToggle.setAttribute("aria-expanded", "false");
            menuToggle.setAttribute("aria-label", "Abrir menu");
        }
    });
}

const anoAtual = document.getElementById("anoAtual");
if (anoAtual) anoAtual.textContent = new Date().getFullYear();
