// CONFIGURAR ABAS DA COMPETIÇÃO
export function configurarAbas() {
    const menu = document.getElementById("nav-competicao");
    const conteudo = document.querySelector(".competicao-painel-conteudo");

    if (!menu || !conteudo) return;


/* CONFIGURAR RETORNO AO DASHBOARD */
const parametros = new URLSearchParams(window.location.search);
const voltar = parametros.get("voltar");

if (voltar) {
    try {
        const origem = new URL(voltar, window.location.origin);

        if (
            origem.origin === window.location.origin &&
            origem.pathname === "/pages/dashboard.html"
        ) {
            const botaoVoltar = document.querySelector(".btn-voltar");

            if (botaoVoltar) {
                botaoVoltar.href = "/pages/dashboard.html";
            } else {
                console.warn("Botão voltar da competição não encontrado.");
            }
        }
    } catch (erro) {
        console.error("Erro ao configurar retorno ao dashboard:", erro);
    }
}



    const botoes = [...menu.querySelectorAll("[data-aba]")];
    const secoes = [...conteudo.querySelectorAll(":scope > .visu-secao")];

    const secaoPorBotao = new Map(
        botoes.map(botao => [
            botao,
            secoes.find(secao => secao.id === botao.dataset.aba)
        ])
    );

    const podeAbrir = botao =>
        Boolean(secaoPorBotao.get(botao)) &&
        (!botao.hasAttribute("data-gerenciar") ||
            window.podeGerenciarCompeticao === true);

    function ativarAba(botao) {
        if (!podeAbrir(botao)) return;

        botoes.forEach(item => {
            const ativo = item === botao;

            item.classList.toggle("active", ativo);
            item.setAttribute("aria-selected", String(ativo));
            item.setAttribute("tabindex", ativo ? "0" : "-1");
        });

        secoes.forEach(secao => {
            secao.hidden = secao.id !== botao.dataset.aba;
        });
    }

    botoes.forEach(botao => {
        botao.addEventListener("click", () => ativarAba(botao));
    });

    menu.addEventListener("keydown", evento => {
        if (!["ArrowRight", "ArrowLeft", "Home", "End"].includes(evento.key)) {
            return;
        }

        const navegaveis = botoes.filter(
            botao => !botao.hidden && podeAbrir(botao)
        );

        if (!navegaveis.length) return;

        const atual = navegaveis.indexOf(document.activeElement);
        let proximo = atual;

        if (evento.key === "Home") {
            proximo = 0;
        } else if (evento.key === "End") {
            proximo = navegaveis.length - 1;
        } else if (atual >= 0) {
            const direcao = evento.key === "ArrowRight" ? 1 : -1;
            proximo = (atual + direcao + navegaveis.length) % navegaveis.length;
        } else {
            proximo = 0;
        }

        evento.preventDefault();
        navegaveis[proximo].focus();
        ativarAba(navegaveis[proximo]);
    });

    // SELECIONAR A ABA INICIAL
    const abaUrl = window.location.hash.replace("#", "");

    const inicial =
        botoes.find(botao =>
            botao.dataset.aba === abaUrl && podeAbrir(botao)
        ) ||
        botoes.find(botao =>
            botao.classList.contains("active") && podeAbrir(botao)
        ) ||
        botoes.find(podeAbrir);

    if (inicial) ativarAba(inicial);

}
