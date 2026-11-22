
class AppBar extends HTMLElement {
    connectedCallback() {
        if (!this.querySelector(".navbar")) {
            this.renderizar();
        }

        this.atualizarUsuario();

        if (this._eventosConfigurados) return;
        this._eventosConfigurados = true;
        this.configurarMenu();
    }

    renderizar() {
        this.innerHTML = `
            <nav class="navbar" aria-label="Barra superior">
                <button type="button" class="menu-btn" aria-label="Abrir menu" aria-expanded="true" aria-controls="app-sidebar">
                    <img src="/assets/icons/sidebar.svg" alt="" class="icon">
                </button>

                <div class="logo">
                    <img src="/assets/img/logo-ace-laranja.png" alt="Logo ACE">
                </div>

                <div class="perfil">
                    <div class="perfil-wrapper">
                        <div class="perfil-botao" style="cursor: default;">
                            <img src="/assets/img/no-prof-pic.png" alt="" class="perfil-foto">
                            <div class="usuario">
                                <span></span>
                                <small></small>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
        `;

        const imagem = this.querySelector(".perfil-foto");

        imagem?.addEventListener("error", () => {
            imagem.src = "/assets/img/no-prof-pic.png";
        }, { once: true });
    }

    atualizarUsuario() {
        const usuario = window.usuarioLogado || {};

        const nome =
            usuario.nome ||
            usuario.nm_usuario ||
            "Usuário";

        const fotoInformada =
            usuario.foto_perfil ||
            usuario.foto ||
            "/assets/img/no-prof-pic.png";

        const foto =
            typeof fotoInformada === "string" &&
            fotoInformada.trim() &&
            !/^file:/i.test(fotoInformada.trim())
                ? fotoInformada.trim()
                : "/assets/img/no-prof-pic.png";

        const papelUsuario = Array.isArray(usuario.papeis)
            ? usuario.papeis[0] || "Usuário"
            : usuario.papel ||
              usuario.nm_papel ||
              usuario.cd_papel ||
              usuario.role ||
              "Usuário";

        const papeis = {
            ADM: "Administrador",
            DIR: "Diretor",
            CRD: "Coordenador",
            PRF: "Professor",
            ARB: "Árbitro",
            ALU: "Aluno",
            VIS: "Visitante"
        };

        const cargo =
            papeis[String(papelUsuario).toUpperCase()] ||
            papelUsuario;

        const elementos = {
            nome: this.querySelector(".usuario span"),
            cargo: this.querySelector(".usuario small"),
            foto: this.querySelector(".perfil-foto")
        };

        if (!elementos.nome) return;

        elementos.nome.textContent = nome;
        elementos.cargo.textContent = cargo;

        if (
            elementos.foto &&
            elementos.foto.getAttribute("src") !== foto
        ) {
            elementos.foto.onerror = () => {
                elementos.foto.onerror = null;
                elementos.foto.src = "/assets/img/no-prof-pic.png";
            };

            elementos.foto.src = foto;
        }
    }

    configurarMenu() {
        const botao = this.querySelector(".menu-btn");

        botao?.addEventListener("click", () => {
            document.dispatchEvent(
                new CustomEvent("ace:toggle-sidebar")
            );
        });
    }
}

if (!customElements.get("app-bar")) {
    customElements.define("app-bar", AppBar);
}
