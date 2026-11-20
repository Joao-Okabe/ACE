class AppBar extends HTMLElement {
    connectedCallback() {
        this.atualizarUsuario();
        }
    atualizarUsuario() {
        const usuario = window.usuarioLogado || {};
        const nome = usuario.nome || usuario.nm_usuario || "Usuário";
        const email = usuario.email || "—";
        const foto = usuario.foto_perfil || usuario.foto || "/assets/img/no-prof-pic.png";
        const papelUsuario = Array.isArray(usuario.papeis)
            ? usuario.papeis[0] || "Usuário"
            : usuario.papel || usuario.nm_papel || usuario.cd_papel || usuario.role || "Usuário";
        const papeis = {
            ADM: "Administrador",
            DIR: "Diretor",
            CRD: "Coordenador",
            PRF: "Professor",
            ARB: "Árbitro",
            ALU: "Aluno",
            VIS: "Visitante"
        };
        const cargo = papeis[String(papelUsuario).toUpperCase()] || papelUsuario;

        //exemplo
        this.notificacoes = [
            {
                id: 1,
                titulo: "Nova competição",
                mensagem: "Uma nova competição foi cadastrada.",
                data: "Há 10 minutos",
                lida: false
            },
            {
                id: 2,
                titulo: "Atualização de partida",
                mensagem: "O resultado de uma partida foi atualizado.",
                data: "Há 1 hora",
                lida: false
            },
            {
                id: 3,
                titulo: "Bem-vindo ao ACE!",
                mensagem: "Acesse o sistema e confira suas funcionalidades.",
                data: "Ontem",
                lida: true
            }
        ];

        this.innerHTML = `
            <nav class="navbar" aria-label="Barra superior">
                <button type="button" class="menu-btn" aria-label="Abrir menu" aria-expanded="true" aria-controls="app-sidebar">
                    <img src="/assets/icons/sidebar.svg" alt="" class="icon">
                </button>
                <div class="logo">
                    <img src="/assets/img/logo-ace-laranja.png" alt="Logo ACE">
                </div>
                <div class="perfil">
                    <div class="notificacao-wrapper">
                        <button type="button" class="notificacao" aria-label="Notificações" aria-expanded="false" aria-controls="painel-notificacoes">
                            <img src="/assets/icons/notifications.svg" alt="" class="icon">
                            <span class="notificacao-badge" hidden></span>
                        </button>
                        <div class="painel-notificacoes" id="painel-notificacoes" hidden>
                            <div class="notificacao-header">
                                <h3>Notificações</h3>
                                <button type="button" class="marcar-lidas">Marcar todas como lidas</button>
                            </div>
                            <div class="notificacao-lista"></div>
                        </div>
                    </div>
                    
                    <div class="perfil-wrapper">
                        <button type="button" class="perfil-botao" aria-label="Abrir opções do perfil" aria-expanded="false" aria-controls="painel-perfil">
                            <img src="${foto}" alt="">
                            <div class="usuario">
                                <span></span>
                                <small></small>
                            </div>
                        </button>
                        <div class="painel-perfil" id="painel-perfil" hidden>
                            <div class="perfil-info">
                                <strong class="perfil-nome"></strong>
                                <span class="perfil-cargo"></span>
                                <small class="perfil-email"></small>
                            </div>
                            <div class="perfil-opcoes">
                                <button type="button" class="perfil-configuracoes">
                                    <img src="/assets/icons/settings.svg" alt="">
                                    Configurações
                                </button>
                                <button type="button" class="perfil-sair">
                                    <img src="/assets/icons/exit.svg" alt="">
                                    Sair da conta
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
        `;

        this.querySelector(".usuario span").textContent = nome;
        this.querySelector(".usuario small").textContent = cargo;
        this.querySelector(".perfil-nome").textContent = nome;
        this.querySelector(".perfil-cargo").textContent = cargo;
        this.querySelector(".perfil-email").textContent = email;
        this.configurarMenu();
        this.configurarNotificacoes();
        this.configurarPerfil();
        this.configurarFechamentoPaineis();
    }

    configurarMenu() {
        const botao = this.querySelector(".menu-btn");
        botao?.addEventListener("click", () => {
            document.dispatchEvent(new CustomEvent("ace:toggle-sidebar"));
        });
    }

    configurarNotificacoes() {
        const botao = this.querySelector(".notificacao");
        const painel = this.querySelector(".painel-notificacoes");
        const lista = this.querySelector(".notificacao-lista");
        const marcarLidas = this.querySelector(".marcar-lidas");
        const badge = this.querySelector(".notificacao-badge");
        if (!botao || !painel || !lista) return;

        const atualizarNotificacoes = () => {
            const naoLidas = this.notificacoes.filter(notificacao => !notificacao.lida);
            badge.textContent = naoLidas.length;
            badge.hidden = naoLidas.length === 0;
            botao.setAttribute("aria-label", naoLidas.length ? `Notificações, ${naoLidas.length} não lidas` : "Notificações");
            lista.replaceChildren();

            if (this.notificacoes.length === 0) {
                const vazio = document.createElement("div");
                vazio.className = "notificacao-vazia";
                vazio.textContent = "Você não possui notificações.";
                lista.appendChild(vazio);
                return;
            }

            this.notificacoes.forEach(notificacao => {
                const item = document.createElement("div");
                item.className = `notificacao-item ${notificacao.lida ? "lida" : "nao-lida"}`;
                item.innerHTML = `
                    <span class="notificacao-ponto"></span>
                    <div class="notificacao-conteudo">
                        <strong></strong>
                        <p></p>
                        <small></small>
                    </div>
                `;
                item.querySelector("strong").textContent = notificacao.titulo;
                item.querySelector("p").textContent = notificacao.mensagem;
                item.querySelector("small").textContent = notificacao.data;
                item.addEventListener("click", () => {
                    notificacao.lida = true;
                    atualizarNotificacoes();
                });
                lista.appendChild(item);
            });
        };

        botao.addEventListener("click", () => {
            const aberto = !painel.hidden;
            const painelPerfil = this.querySelector(".painel-perfil");
            const botaoPerfil = this.querySelector(".perfil-botao");
            painel.hidden = aberto;
            botao.setAttribute("aria-expanded", String(!aberto));
            painelPerfil.hidden = true;
            botaoPerfil.setAttribute("aria-expanded", "false");
        });

        marcarLidas?.addEventListener("click", () => {
            this.notificacoes.forEach(notificacao => {
                notificacao.lida = true;
            });
            atualizarNotificacoes();
        });

        atualizarNotificacoes();
    }

    configurarPerfil() {
        const botao = this.querySelector(".perfil-botao");
        const painel = this.querySelector(".painel-perfil");
        const configuracoes = this.querySelector(".perfil-configuracoes");
        const sair = this.querySelector(".perfil-sair");
        if (!botao || !painel) return;

        botao.addEventListener("click", () => {
            const aberto = !painel.hidden;
            const painelNotificacoes = this.querySelector(".painel-notificacoes");
            const botaoNotificacoes = this.querySelector(".notificacao");
            painel.hidden = aberto;
            botao.setAttribute("aria-expanded", String(!aberto));
            painelNotificacoes.hidden = true;
            botaoNotificacoes.setAttribute("aria-expanded", "false");
        });

        configuracoes?.addEventListener("click", () => {
            painel.hidden = true;
            botao.setAttribute("aria-expanded", "false");
            document.dispatchEvent(new CustomEvent("ace:open-settings"));
        });

        sair?.addEventListener("click", () => {
            document.dispatchEvent(new CustomEvent("ace:logout"));
        });
    }

    configurarFechamentoPaineis() {
        const painelNotificacoes = this.querySelector(".painel-notificacoes");
        const painelPerfil = this.querySelector(".painel-perfil");
        const botaoNotificacoes = this.querySelector(".notificacao");
        const botaoPerfil = this.querySelector(".perfil-botao");

        document.addEventListener("click", event => {
            if (!this.contains(event.target)) {
                painelNotificacoes.hidden = true;
                painelPerfil.hidden = true;
                botaoNotificacoes.setAttribute("aria-expanded", "false");
                botaoPerfil.setAttribute("aria-expanded", "false");
            }
        });

        this.addEventListener("keydown", event => {
            if (event.key === "Escape") {
                const notificacoesAbertas = !painelNotificacoes.hidden;
                const perfilAberto = !painelPerfil.hidden;

                if (notificacoesAbertas || perfilAberto) {
                    painelNotificacoes.hidden = true;
                    painelPerfil.hidden = true;
                    botaoNotificacoes.setAttribute("aria-expanded", "false");
                    botaoPerfil.setAttribute("aria-expanded", "false");

                    if (perfilAberto) {
                        botaoPerfil.focus();
                    } else {
                        botaoNotificacoes.focus();
                    }
                }
            }
        });
    }
}

if (!customElements.get("app-bar")) {
    customElements.define("app-bar", AppBar);
}
