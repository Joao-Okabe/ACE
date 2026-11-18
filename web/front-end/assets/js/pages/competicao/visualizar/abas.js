// CONFIGURAR ABAS
export function configurarAbas() {
    const menu = document.querySelector(".visu-menu");
    const conteudo = document.querySelector(".visu-painel-conteudo");

    if (!menu || !conteudo) return;

    const botoes = menu.querySelectorAll("[data-aba]");
    const secoes = conteudo.querySelectorAll(".visu-secao");

    botoes.forEach(botao => {
        botao.addEventListener("click", () => {
            const aba = botao.dataset.aba;

            if (aba === "gerir" && !window.podeGerenciarCompeticao) {
                return;
            }

            botoes.forEach(item => {
                const ativo = item === botao;

                item.classList.toggle("active", ativo);
                item.setAttribute("aria-selected", String(ativo));
                item.setAttribute("tabindex", ativo ? "0" : "-1");
            });

            secoes.forEach(secao => {
                secao.hidden = secao.id !== aba;
            });
        });
    });
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