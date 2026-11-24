
import { authService } from "../services/authService.js";

const FOTO_PADRAO = "/assets/img/no-prof-pic.png";

class AppBar extends HTMLElement {
    connectedCallback() {
        if (!this.querySelector(".navbar")) {
            this.renderizar();
        }

        this.atualizarUsuario();

        if (this._eventosConfigurados) return;
        this._eventosConfigurados = true;

        this.configurarMenu();
        this.configurarPerfil();
    }

    disconnectedCallback() {
        document.removeEventListener("click", this._fecharAoClicarFora);
        document.removeEventListener("keydown", this._fecharComEscape);
        this._eventosConfigurados = false;
    }

    renderizar() {
        this.innerHTML = `
            <nav class="navbar" aria-label="Barra superior">
                <button type="button" class="menu-btn" aria-label="Abrir menu" aria-expanded="true" aria-controls="app-sidebar">
                    <img src="/assets/icons/sidebar.svg" alt="" class="icon">
                </button>

                <a href="/pages/dashboard.html" class="logo" aria-label="Ir para o Dashboard">
                    <img src="/assets/img/logo-ace-laranja.png" alt="Logo ACE">
                </a>

                <div class="perfil">
                    <div class="perfil-wrapper">
                        <button type="button" class="perfil-botao" id="abrirMenuPerfil" aria-label="Abrir menu do perfil" aria-expanded="false" aria-controls="menuPerfilFlutuante">
                            <img src="${FOTO_PADRAO}" alt="" class="perfil-foto">
                            <div class="usuario">
                                <span></span>
                                <small></small>
                            </div>
                        </button>
                      
                        <div class="perfil-menu-flutuante" id="menuPerfilFlutuante" role="region" aria-label="Informações do perfil" hidden>
                            <div class="perfil-menu-cabecalho">
                                <img src="${FOTO_PADRAO}" alt="" class="perfil-menu-foto">

                                <div class="perfil-menu-usuario">
                                    <strong id="perfilMenuNome">Usuário</strong>
                                    <span id="perfilMenuEmail">Não informado</span>
                                    <span id="perfilMenuCargo">Usuário</span>
                                </div>
                            </div>

                            <div class="perfil-menu-acoes">
                                <a href="/pages/usuario/configuracoes.html" class="perfil-menu-link">
                                <img src="/assets/icons/config.svg" alt="" class="icon" onerror="this.style.display='none'">
                                    <span>Configurações da conta</span>
                                </a>

                                <button type="button" class="perfil-menu-logout" id="botaoLogout">
                                <img src="/assets/icons/exit1.svg" alt="" class="icon" onerror="this.style.display='none'">
                                    <span>Sair da conta</span>
                                    
                                </button>
                            </div>

                            <p class="perfil-menu-mensagem" id="mensagemLogout" role="alert" hidden></p>
                        </div>

                    </div>
                </div>
            </nav>
        `;

        const imagens = this.querySelectorAll(".perfil-foto, .perfil-menu-foto");

        imagens.forEach(imagem => {
            imagem.onerror = () => {
                imagem.onerror = null;
                imagem.src = FOTO_PADRAO;
            };
        });
    }

    atualizarUsuario() {
        const dados = window.usuarioLogado || {};
        const usuario = dados.usuario || dados;

        const nome =
            usuario.nome ||
            usuario.nm_usuario ||
            dados.nome ||
            dados.nm_usuario ||
            "Usuário";

        const email =
            usuario.email ||
            usuario.ds_email ||
            dados.email ||
            dados.ds_email ||
            "Não informado";

        const fotoInformada =
            usuario.foto_perfil ||
            usuario.foto ||
            dados.foto_perfil ||
            dados.foto ||
            FOTO_PADRAO;

        const foto =
            typeof fotoInformada === "string" &&
            fotoInformada.trim() &&
            !/^file:/i.test(fotoInformada.trim())
                ? fotoInformada.trim()
                : FOTO_PADRAO;

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

        const codigoPapel = typeof papelUsuario === "object"
            ? papelUsuario.sg_papel ||
              papelUsuario.nm_papel ||
              papelUsuario.codigo ||
              "Usuário"
            : papelUsuario;

        const cargo =
            papeis[String(codigoPapel).toUpperCase()] ||
            String(codigoPapel);

        const preencherTexto = (seletor, valor) => {
            const elemento = this.querySelector(seletor);
            if (elemento) elemento.textContent = valor;
        };

        preencherTexto(".usuario span", nome);
        preencherTexto(".usuario small", cargo);

        preencherTexto("#perfilMenuNome", nome);
        preencherTexto("#perfilMenuNomeCompleto", nome);
        preencherTexto("#perfilMenuEmail", email);
        preencherTexto("#perfilMenuCargo", cargo);
        preencherTexto("#perfilMenuCargoCompleto", cargo);

        this.querySelectorAll(".perfil-foto, .perfil-menu-foto").forEach(imagem => {
            if (imagem.getAttribute("src") === foto) return;

            imagem.onerror = () => {
                imagem.onerror = null;
                imagem.src = FOTO_PADRAO;
            };

            imagem.src = foto;
        });
    }

    configurarMenu() {
        const botao = this.querySelector(".menu-btn");

        botao?.addEventListener("click", () => {
            document.dispatchEvent(
                new CustomEvent("ace:toggle-sidebar")
            );
        });
    }

    configurarPerfil() {
        const botao = this.querySelector("#abrirMenuPerfil");
        const menu = this.querySelector("#menuPerfilFlutuante");
        const botaoLogout = this.querySelector("#botaoLogout");

        if (!botao || !menu) return;

        const fecharMenu = () => {
            menu.hidden = true;
            botao.setAttribute("aria-expanded", "false");
        };

        botao.addEventListener("click", () => {
            this.atualizarUsuario();

            const abrir = menu.hidden;

            menu.hidden = !abrir;
            botao.setAttribute("aria-expanded", String(abrir));
        });

        this._fecharAoClicarFora = evento => {
            if (!this.querySelector(".perfil-wrapper")?.contains(evento.target)) {
                fecharMenu();
            }
        };

        this._fecharComEscape = evento => {
            if (evento.key === "Escape" && !menu.hidden) {
                fecharMenu();
                botao.focus();
            }
        };

        document.addEventListener("click", this._fecharAoClicarFora);
        document.addEventListener("keydown", this._fecharComEscape);

        botaoLogout?.addEventListener("click", async () => {
            const mensagem = this.querySelector("#mensagemLogout");

            botaoLogout.disabled = true;
            if (mensagem) mensagem.hidden = true;

            try {
                await authService.logout();
                window.location.replace("/pages/usuario/login.html");
            } catch (erro) {
                console.error("Erro ao sair da conta:", erro);

                if (mensagem) {
                    mensagem.textContent = "Não foi possível sair da conta. Tente novamente.";
                    mensagem.hidden = false;
                }

                botaoLogout.disabled = false;
            }
        });
    }
}

if (!customElements.get("app-bar")) {
    customElements.define("app-bar", AppBar);
}
