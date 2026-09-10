class Header extends HTMLElement {

    connectedCallback() {
        // Recupera o estado da sidebar antes de renderizar
        const sidebarFechada =
        localStorage.getItem('sidebarFechada') === 'true';

        if (sidebarFechada) {
                this.classList.add('sidebar-fechada-inicial');
        }

        const usuario = window.usuarioLogado || {};

        const nome = usuario.nome || usuario.nm_usuario || 'Usuário';
        const email = usuario.email || '—';
        const foto = usuario.foto || '/img/no-prof-pic.png';

        this.innerHTML = `

            <nav class="navbar">

                <button
                    type="button"
                    id="menu-btn"
                    aria-label="Abrir ou fechar menu"
                    aria-expanded="true"
                >
                    <i class="bi bi-layout-sidebar"></i>
                </button>

                <div class="logo">
                    <img
                        src="/img/logo-ace-laranja.png"
                        alt="Logo ACE"
                    >
                </div>

                <div class="perfil">

                    <i
                        class="bi bi-bell notificacao"
                        aria-label="Notificações"
                        role="button"
                        tabindex="0"
                    ></i>

                    <img
                        src="${foto}"
                        alt="Perfil de ${nome}"
                    >

                    <div class="usuario">
                        <span>${nome}</span>
                        <small>${email}</small>
                    </div>

                </div>

            </nav>


            <!-- Sidebar -->

            <div class="sidebar">

                <div class="menu">

                    <a href="/dashboard" class="menu-item">
                        <i class="bi bi-house-door-fill"></i>
                        <span>Painel</span>
                    </a>

                    <a href="/escolas/listar" class="menu-item">
                        <i class="bi bi-bank2"></i>
                        <span>Escolas</span>
                    </a>

                    <a href="/alunos/listar" class="menu-item">
                        <i class="bi bi-person-fill"></i>
                        <span>Alunos</span>
                    </a>

                    <a href="#" class="menu-item">
                        <i class="bi bi-people-fill"></i>
                        <span>Times</span>
                    </a>

                    <a href="#" class="menu-item">
                        <i class="bi bi-trophy-fill"></i>
                        <span>Competições</span>
                    </a>

                    <a href="#" class="menu-item">
                        <i class="bi bi-dribbble"></i>
                        <span>Partidas</span>
                    </a>

                    <a href="#" class="menu-item">
                        <i class="bi bi-bar-chart-line-fill"></i>
                        <span>Rankings</span>
                    </a>

                    <a href="#" class="menu-item">
                        <i class="bi bi-gear-fill"></i>
                        <span>Configurações</span>
                    </a>

                    <a href="/logout" class="menu-item">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Sair da conta</span>
                    </a>

                </div>

            </div>
        `;

        this.configurarMenu();
        this.configurarPaginaAtual();
        
        this.classList.remove('sidebar-fechada-inicial');
    }



    configurarMenu() {

    const menuBtn = this.querySelector('#menu-btn');
    const sidebar = this.querySelector('.sidebar');
    const content = document.querySelector('.content');
    const navbar = this.querySelector('.navbar');

    if (!menuBtn || !sidebar) {
        return;
    }

    // Recupera o estado salvo da sidebar
    const sidebarFechada = localStorage.getItem('sidebarFechada') === 'true';

    if (sidebarFechada) {
        sidebar.classList.add('close');

        if (content) {
            content.classList.add('close');
        }

        if (navbar) {
            navbar.classList.add('close')
        }
    }

    // Estado inicial do botão
    const menuAberto = !sidebar.classList.contains('close');

    menuBtn.setAttribute(
        'aria-expanded',
        menuAberto.toString()
    );

    menuBtn.setAttribute(
        'aria-label',
        menuAberto
            ? 'Fechar menu'
            : 'Abrir menu'
    );

    menuBtn.addEventListener('click', () => {

        sidebar.classList.toggle('close');

        if (content) {
            content.classList.toggle('close');
        }

        if (navbar) {
            navbar.classList.toggle('close')
        }

        // Verifica o novo estado
        const menuAberto = !sidebar.classList.contains('close');

        // Salva o estado no navegador
        localStorage.setItem(
            'sidebarFechada',
            (!menuAberto).toString()
        );

        // Atualiza acessibilidade do botão
        menuBtn.setAttribute(
            'aria-expanded',
            menuAberto.toString()
        );

        menuBtn.setAttribute(
            'aria-label',
            menuAberto
                ? 'Fechar menu'
                : 'Abrir menu'
        );
    });
}


    configurarPaginaAtual() {

        const paginaAtual = window.location.pathname;

        const links = this.querySelectorAll('.menu-item');

        links.forEach(link => {

            const href = link.getAttribute('href');

            // Remove qualquer active definido anteriormente
            link.classList.remove('active');

            // Ignora links sem endereço
            if (!href || href === '#') {
                return;
            }

            // Verifica se é a página atual
            if (
                paginaAtual === href ||
                paginaAtual.startsWith(href + '/')
            ) {
                link.classList.add('active');
            }

        });
    }
}


// Registra o componente apenas uma vez
if (!customElements.get('app-header')) {
    customElements.define('app-header', Header);
}

