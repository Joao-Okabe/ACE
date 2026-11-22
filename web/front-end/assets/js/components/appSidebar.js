const ESTADO_SIDEBAR = "sidebarFechada";

class SideBar extends HTMLElement {
    connectedCallback() {
        const fechada = this.obterEstadoInicial();

        if (!this.querySelector(".sidebar")) {
            this.renderizar(fechada);
        }

        this.configurarPaginaAtual();
        this.atualizarLayout(fechada);

        if (!this._ouvindoToggle) {
            document.addEventListener("ace:toggle-sidebar", this.alternarMenu);
            this._ouvindoToggle = true;
        }
    }

    disconnectedCallback() {
        if (this._ouvindoToggle) {
            document.removeEventListener("ace:toggle-sidebar", this.alternarMenu);
            this._ouvindoToggle = false;
        }
    }

    obterEstadoInicial() {
        const estadoAntecipado = document.documentElement.classList.contains(
            "sidebar-fechada"
        );

        try {
            const salvo = localStorage.getItem(ESTADO_SIDEBAR);
            return salvo === null ? estadoAntecipado : salvo === "true";
        } catch {
            return estadoAntecipado;
        }
    }

    renderizar(fechada) {
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
                    <a href="/pages/ranking.html" class="menu-item">
                        <img src="/assets/icons/barChart.svg" alt="" class="icon">
                        <span>Rankings</span>
                    </a>
                    <a href="/pages/usuario/configuracoes.html" class="menu-item">
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
    }

    alternarMenu = () => {
        const sidebar = this.querySelector(".sidebar");
        if (!sidebar) return;

        const fechada = !sidebar.classList.contains("close");
        this.atualizarLayout(fechada);

        try {
            localStorage.setItem(ESTADO_SIDEBAR, String(fechada));
        } catch {
            // O menu continua funcionando mesmo se o armazenamento estiver bloqueado.
        }
    };

    atualizarLayout(fechada) {
        const sidebar = this.querySelector(".sidebar");
        const navbar = document.querySelector("app-bar .navbar");
        const content = document.querySelector(".content");
        const botao = document.querySelector("app-bar .menu-btn");

        sidebar?.classList.toggle("close", fechada);
        navbar?.classList.toggle("close", fechada);
        content?.classList.toggle("close", fechada);

        document.documentElement.classList.toggle("sidebar-fechada", fechada);

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
            link.classList.toggle("active", paginaAtual === href);
        });
    }
}

if (!customElements.get("app-sidebar")) {
    customElements.define("app-sidebar", SideBar);
}
