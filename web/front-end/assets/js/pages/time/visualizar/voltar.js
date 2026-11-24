
/* CONFIGURAR BOTÃO VOLTAR */
export function configurarBotaoVoltar() {
    const botao = document.getElementById("botaoVoltarTime");
    if (!botao) return;

    const destinoPadrao = "/pages/time/listar.html";
    const parametros = new URLSearchParams(window.location.search);

    // PRIORIDADE 1: ORIGEM INFORMADA NA URL
    let origem = parametros.get("voltar");

    // PRIORIDADE 2: PÁGINA ANTERIOR DO NAVEGADOR
    if (!origem && document.referrer) {
        origem = document.referrer;
    }

    botao.href = destinoPadrao;

    if (!origem) return;

    try {
        const url = new URL(origem, window.location.origin);

        // ACEITAR APENAS PÁGINAS INTERNAS
        if (url.origin !== window.location.origin) return;

        // VOLTAR À COMPETIÇÃO NA ABA TIMES
        if (url.pathname.includes("/competicao/visualizar")) {
            botao.href = `${url.pathname}${url.search}#times`;
            return;
        }

        // VOLTAR PARA OUTRAS PÁGINAS INTERNAS
        if (url.pathname !== window.location.pathname) {
            botao.href = url.pathname + url.search + url.hash;
        }
    } catch (erro) {
        console.error("Erro ao configurar botão voltar:", erro);
    }
}
