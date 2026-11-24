// ABAS SUPERIORES DO TIME - MESMO FUNCIONAMENTO DA COMPETIÇÃO
export function configurarAbas() {
    const menu = document.getElementById("nav-time");
    const painel = document.querySelector(".time-painel-conteudo");
    if (!menu || !painel || menu.dataset.abasConfiguradas) return;
    menu.dataset.abasConfiguradas = "true";
    const botoes = [...menu.querySelectorAll("[data-aba]")];
    const secoes = [...painel.querySelectorAll(":scope > .visu-secao")];

    function abrirAba(botao) {
        if (botao.hidden || botao.getAttribute("aria-hidden") === "true") return;
        const destino = botao.dataset.aba;
        if (!secoes.some(secao => secao.id === destino)) return;
        botoes.forEach(item => {
            const ativa = item === botao;
            item.classList.toggle("active", ativa);
            item.setAttribute("aria-selected", String(ativa));
            item.tabIndex = ativa ? 0 : -1;
        });
        secoes.forEach(secao => {
            secao.hidden = secao.id !== destino;
        });
    }

    botoes.forEach(botao => {
        botao.addEventListener("click", () => abrirAba(botao));
    });
    abrirAba(botoes.find(botao => botao.classList.contains("active")) || botoes[0]);
}
