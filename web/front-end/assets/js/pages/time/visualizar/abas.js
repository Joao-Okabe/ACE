// CONFIGURAR ABAS
export function configurarAbas() {
    const menu = document.querySelector(".visu-menu");
    const paineis = document.querySelectorAll(".visu-painel-conteudo");
    const botoesAbas = document.querySelectorAll("[data-aba]");
    const secoes = document.querySelectorAll(".visu-secao");

    if (!menu || !paineis.length || !botoesAbas.length || !secoes.length) {
        return;
    }

    botoesAbas.forEach(botao => {
        botao.addEventListener("click", () => {
            const abaSelecionada = botao.dataset.aba;

            botoesAbas.forEach(aba => {
                aba.classList.remove("active");
                aba.setAttribute("aria-selected", "false");
                aba.setAttribute("tabindex", "-1");
            });

            botao.classList.add("active");
            botao.setAttribute("aria-selected", "true");
            botao.setAttribute("tabindex", "0");

            secoes.forEach(secao => {
                secao.hidden = secao.id !== abaSelecionada;
            });
        });
    });
}
