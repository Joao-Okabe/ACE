// CONFIGURAR ABAS
export function configurarAbas() {
    const menu = document.getElementById("nav-competicao");
    const conteudo = document.querySelector(".competicao-painel-conteudo");

    if (!menu || !conteudo) return;

    const botoes = [...menu.querySelectorAll("[data-aba]")];
    const secoes = conteudo.querySelectorAll(".visu-secao");

    const secaoPorBotao = new Map(
        botoes.map(botao => [
            botao,
            [...secoes].find(secao => secao.id === botao.dataset.aba)
        ])
    );

    const podeAbrir = botao =>
        secaoPorBotao.get(botao)
        && (!botao.hasAttribute("data-gerenciar") || window.podeGerenciarCompeticao);

    const ativarAba = botao => {
        if (!podeAbrir(botao)) return;

        botoes.forEach(item => {
            const ativo = item === botao;
            item.classList.toggle("active", ativo);
            item.setAttribute("aria-selected", String(ativo));
            item.setAttribute("tabindex", ativo ? "0" : "-1");
            secaoPorBotao.get(item)?.toggleAttribute("hidden", !ativo);
        });
    };

    botoes.forEach(botao => {
        botao.addEventListener("click", () => ativarAba(botao));
    });

    menu.addEventListener("keydown", evento => {
        if (!["ArrowRight", "ArrowLeft", "Home", "End"].includes(evento.key)) return;

        const navegaveis = botoes.filter(botao => !botao.hidden && podeAbrir(botao));
        if (navegaveis.length === 0) return;

        const atual = navegaveis.indexOf(document.activeElement);
        let proximo = atual;
        if (evento.key === "Home") proximo = 0;
        else if (evento.key === "End") proximo = navegaveis.length - 1;
        else if (atual >= 0) {
            const direcao = evento.key === "ArrowRight" ? 1 : -1;
            proximo = (atual + direcao + navegaveis.length) % navegaveis.length;
        }

        evento.preventDefault();
        navegaveis[proximo].focus();
        ativarAba(navegaveis[proximo]);
    });

    const inicial = botoes.find(botao => botao.classList.contains("active") && podeAbrir(botao))
        || botoes.find(podeAbrir);
    if (inicial) ativarAba(inicial);
}

// CONFIGURAR MENU DE GERENCIAMENTO
function configurarMenuGerenciamento() {
    const grupos = document.querySelectorAll(".competicao-gerenciar-grupo");

    grupos.forEach(grupo => {
        const botao = grupo.querySelector(".competicao-gerenciar-item");
        const conteudo = grupo.querySelector(".competicao-gerenciar-conteudo");

        botao.addEventListener("click", () => {
            const abrir = botao.getAttribute("aria-expanded") !== "true";

            grupos.forEach(item => {
                item.querySelector(".competicao-gerenciar-item")
                    .setAttribute("aria-expanded", "false");

                item.querySelector(".competicao-gerenciar-conteudo").hidden = true;
            });

            if (abrir) {
                botao.setAttribute("aria-expanded", "true");
                conteudo.hidden = false;
            }
        });
    });
}