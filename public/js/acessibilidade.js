class Acessibilidade {
    constructor() {
        this.criarPainel();
        this.configurarEventos();
    }
    criarPainel() {
        const container = document.createElement('div');
        container.innerHTML = `
            <button
                type="button"
                id="acessibilidade-btn"
                class="acessibilidade-btn"
                aria-label="Abrir opções de acessibilidade"
                aria-expanded="false"
                aria-controls="acessibilidade-painel"
                title="acessibilidade"
            >
                <i class="bi bi-universal-access" aria-hidden="true"></i>
            </button>
            <div
                id="acessibilidade-painel"
                class="acessibilidade-painel"
                aria-hidden="true"
            >
                <h2>Acessibilidade</h2>
                <!-- DALTONISMO -->
                <div class="acessibilidade-section">
                    <h3>Filtros para daltonismo</h3>
                    <div class="daltonismo-opcoes">
                        <button
                            type="button"
                            data-filtro="padrao"
                        >
                            Padrão
                        </button>
                        <button
                            type="button"
                            data-filtro="protanopia"
                        >
                            Protanopia
                        </button>
                        <button
                            type="button"
                            data-filtro="deuteranopia"
                        >
                            Deuteranopia
                        </button>
                        <button
                            type="button"
                            data-filtro="tritanopia"
                        >
                            Tritanopia
                        </button>
                    </div>
                </div>
                <!-- TAMANHO DA FONTE -->
                <div class="acessibilidade-section">
                    <h3>Tamanho da fonte</h3>
                    <div class="tamanho-fonte">
                        <button
                            type="button"
                            id="fonte-diminuir"
                            aria-label="Diminuir tamanho da fonte"
                        >
                            A-
                        </button>
                        <button
                            type="button"
                            id="fonte-normal"
                            aria-label="Restaurar tamanho da fonte padrão"
                        >
                            A
                        </button>
                        <button
                            type="button"
                            id="fonte-aumentar"
                            aria-label="Aumentar tamanho da fonte"
                        >
                            A+
                        </button>
                    </div>
                </div>
                <!-- TEMA -->
                <div class="acessibilidade-section">
                    <h3>Tema</h3>
                    <div class="tema-opcoes">
                        <button
                            type="button"
                            data-tema="claro"
                        >
                            Claro
                        </button>
                        <button
                            type="button"
                            data-tema="escuro"
                        >
                            Escuro
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(container);
    }
    configurarEventos() {
        const botao = document.querySelector('#acessibilidade-btn');
        const painel = document.querySelector('#acessibilidade-painel');
        const diminuir = document.querySelector('#fonte-diminuir');
        const normal = document.querySelector('#fonte-normal');
        const aumentar = document.querySelector('#fonte-aumentar');
        const filtros = document.querySelectorAll('[data-filtro]');
        const temas = document.querySelectorAll('[data-tema]');

        //Abrir/fechar painel
        botao.addEventListener('click', () => {
            const aberto = painel.classList.toggle('show');
            botao.classList.toggle('ativo', aberto);
            botao.setAttribute(
                'aria-expanded',
                aberto ? 'true' : 'false'
            );
            painel.setAttribute(
                'aria-hidden',
                aberto ? 'false' : 'true'
            );
        });

        //Diminuir fonte
        diminuir.addEventListener('click', () => {
            this.definirFonte('pequena');
        });

        //Fonte normal
        normal.addEventListener('click', () => {
            this.definirFonte('normal');
        });

        //Aumentar fonte
        aumentar.addEventListener('click', () => {
            this.definirFonte('grande');
        });

        //Daltonismo
        filtros.forEach(filtro => {
            filtro.addEventListener('click', () => {
                const tipo = filtro.dataset.filtro;
                this.definirDaltonismo(tipo);
            });
        });

        //Tema
        temas.forEach(tema => {
            tema.addEventListener('click', () => {
                const tipoTema = tema.dataset.tema;
                this.definirTema(tipoTema);
            });
        });

        //Restaurar configs
        this.restaurarConfiguracoes();
    }

    //Fonte
    definirFonte(tipo) {
        const diminuir = document.querySelector('#fonte-diminuir');
        const normal = document.querySelector('#fonte-normal');
        const aumentar = document.querySelector('#fonte-aumentar');
        document.body.classList.remove(
            'fonte-pequena',
            'fonte-normal',
            'fonte-grande'
        );
        diminuir.classList.remove('selecionado');
        normal.classList.remove('selecionado');
        aumentar.classList.remove('selecionado');
        if (tipo === 'pequena') {
            document.body.classList.add('fonte-pequena');
            diminuir.classList.add('selecionado');
        }
        else if (tipo === 'grande') {
            document.body.classList.add('fonte-grande');
            aumentar.classList.add('selecionado');
        }
        else {
            document.body.classList.add('fonte-normal');
            normal.classList.add('selecionado');
            tipo = 'normal';
        }
        localStorage.setItem('fonte', tipo);
        salvarConfiguracoes();
    }

    //Daltonismo
    definirDaltonismo(tipo) {
        const filtros = document.querySelectorAll('[data-filtro]');
        document.body.classList.remove(
            'filtro-protanopia',
            'filtro-deuteranopia',
            'filtro-tritanopia'
        );
        filtros.forEach(botao => {
            botao.classList.remove('selecionado');
        });
        if (tipo === 'protanopia') {
            document.body.classList.add(
                'filtro-protanopia'
            );
        }
        else if (tipo === 'deuteranopia') {
            document.body.classList.add(
                'filtro-deuteranopia'
            );
        }
        else if (tipo === 'tritanopia') {
            document.body.classList.add(
                'filtro-tritanopia'
            );
        }
        else {
            tipo = 'normal';
        }
        const botaoSelecionado = document.querySelector(
            `[data-filtro="${tipo === 'normal' ? 'padrao' : tipo}"]`
        );
        if (botaoSelecionado) {
            botaoSelecionado.classList.add('selecionado');
        }
        localStorage.setItem(
            'daltonismo',
            tipo
        );
        salvarConfiguracoes();
    }

    //Tema
    definirTema(tipoTema) {
        const temas = document.querySelectorAll('[data-tema]');
        document.body.classList.remove(
            'tema-claro',
            'tema-escuro'
        );
        temas.forEach(botao => {
            botao.classList.remove('selecionado');
        });
        if (tipoTema === 'escuro') {
            document.body.classList.add(
                'tema-escuro'
            );
        }
        else {
            tipoTema = 'claro';
            document.body.classList.add(
                'tema-claro'
            );
        }
        const botaoSelecionado = document.querySelector(
            `[data-tema="${tipoTema}"]`
        );
        if (botaoSelecionado) {
            botaoSelecionado.classList.add(
                'selecionado'
            );
        }
        localStorage.setItem(
            'tema',
            tipoTema
        );
        salvarConfiguracoes();
    }

    //Restaurar configs
    restaurarConfiguracoes() {
        const fonteSalva =
            localStorage.getItem('fonte') || 'normal';
        const temaSalvo =
            localStorage.getItem('tema') || 'claro';
        const filtroSalvo =
            localStorage.getItem('daltonismo') || 'normal';
        this.definirFonte(
            fonteSalva
        );
        this.definirTema(
            temaSalvo
        );
        this.definirDaltonismo(
            filtroSalvo
        );
    }
}

//Salvar configs
function salvarConfiguracoes() {
    /* Fonte */
    let fonte = 'normal';
    if (
        document.body.classList.contains(
            'fonte-pequena'
        )
    ) {
        fonte = 'pequena';
    }
    else if (
        document.body.classList.contains(
            'fonte-grande'
        )
    ) {
        fonte = 'grande';
    }
    localStorage.setItem(
        'fonte',
        fonte
    );
    /* Daltonismo */
    let daltonismo = 'normal';
    if (
        document.body.classList.contains(
            'filtro-protanopia'
        )
    ) {
        daltonismo = 'protanopia';
    }
    else if (
        document.body.classList.contains(
            'filtro-deuteranopia'
        )
    ) {
        daltonismo = 'deuteranopia';
    }
    else if (
        document.body.classList.contains(
            'filtro-tritanopia'
        )
    ) {
        daltonismo = 'tritanopia';
    }
    localStorage.setItem(
        'daltonismo',
        daltonismo
    );
    /* Tema */
    const tema =
        document.body.classList.contains(
            'tema-escuro'
        )
            ? 'escuro'
            : 'claro';
    localStorage.setItem(
        'tema',
        tema
    );
}

// Iniciar
document.addEventListener(
    'DOMContentLoaded',
    () => {
        new Acessibilidade();
        document.body.classList.add(
            'acessibilidade-pronta'
        );
    }
);