const ESTADO_SIDEBAR = "sidebarFechada";

class SideBar extends HTMLElement {
    connectedCallback() {
        const fechada =
            localStorage.getItem(ESTADO_SIDEBAR) === "true";

        this.innerHTML = `
            <aside id="app-sidebar" class="sidebar ${fechada ? "close" : ""}" aria-label="Menu principal">
                <nav class="menu">

                    <a href="/pages/dashboard.html" class="menu-item">
                        <img src="/assets/icons/dashboard.svg" alt="" class="icon">
                        <span>Painel</span>
                    </a>

                    <a href="/pages/escola/listar.html" class="menu-item">
                        <img src="/assets/icons/school.svg" alt="" class="icon">
                        <span>Escolas</span>
                    </a>

                    <a href="/pages/aluno/listar.html" class="menu-item">
                        <img src="/assets/icons/group.svg" alt="" class="icon">
                        <span>Alunos</span>
                    </a>

                    <a href="/pages/competicao/listar.html" class="menu-item">
                        <img src="/assets/icons/trophy.svg" alt="" class="icon">
                        <span>Competições</span>
                    </a>

                    <a href="/pages/time/listar.html" class="menu-item">
                        <img src="/assets/icons/shield.svg" alt="" class="icon">
                        <span>Times</span>
                    </a>

                    <a href="/pages/ranking/listar.html" class="menu-item">
                        <img src="/assets/icons/barChart.svg" alt="" class="icon">
                        <span>Rankings</span>
                    </a>

                    <a href="/pages/usuario/editar.html" class="menu-item">
                        <img src="/assets/icons/settings.svg" alt="" class="icon">
                        <span>Configurações</span>
                    </a>

                    <a href="/pages/usuario/login.html" class="menu-item">
                        <img src="/assets/icons/exit.svg" alt="" class="icon">
                        <span>Logout</span>
                    </a>

                </nav>
            </aside>
        `;

        this.configurarMenu();
        this.configurarPaginaAtual();
        this.atualizarLayout(fechada);

        document.addEventListener(
            "ace:toggle-sidebar",
            this.alternarMenu
        );
    }

    disconnectedCallback() {
        document.removeEventListener(
            "ace:toggle-sidebar",
            this.alternarMenu
        );
    }

    alternarMenu = () => {
        const sidebar = this.querySelector(".sidebar");
        const fechada = !sidebar?.classList.contains("close");

        this.atualizarLayout(fechada);

        localStorage.setItem(
            ESTADO_SIDEBAR,
            String(fechada)
        );
    };

    configurarMenu() {
        // A barra superior controla a abertura do menu.
        // A sidebar apenas recebe o evento correspondente.
    }

    atualizarLayout(fechada) {
        const sidebar = this.querySelector(".sidebar");
        const navbar = document.querySelector("app-bar .navbar");
        const content = document.querySelector(".content");
        const botao = document.querySelector("app-bar .menu-btn");

        sidebar?.classList.toggle("close", fechada);
        navbar?.classList.toggle("close", fechada);
        content?.classList.toggle("close", fechada);

        botao?.setAttribute("aria-expanded", String(!fechada));
        botao?.setAttribute(
            "aria-label",
            fechada ? "Abrir menu" : "Fechar menu"
        );
    }

    configurarPaginaAtual() {
        const paginaAtual = window.location.pathname;

        this.querySelectorAll(".menu-item").forEach(link => {
            const href = link.getAttribute("href");

            if (!href || href === "#") return;

            const ativo = paginaAtual === href;

            link.classList.toggle("active", ativo);
        });
    }
}

if (!customElements.get("app-sidebar")) {
    customElements.define("app-sidebar", SideBar);
}
