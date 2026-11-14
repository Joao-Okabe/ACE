import {
    formatarDataCompeticao,
    renderizarTimesCompeticao,
    renderizarListaCompeticao
} from "../../utils/competicaoUtils.js";

// Elementos da página
const nomeCompeticao = document.getElementById("nomeCompeticao");

// Inicialização
document.addEventListener("DOMContentLoaded", () => {
    configurarPagina();
});

function configurarPagina() {
    configurarAbas();
}

// CONFIGURAR ABAS
function configurarAbas() {
    const menu = document.querySelector(".visu-menu");
    const conteudo = document.querySelector(".visu-painel-conteudo");

    if (!menu || !conteudo) return;

    const botoes = menu.querySelectorAll("[data-aba]");
    const secoes = conteudo.querySelectorAll(".visu-secao");

    const botaoGerenciar = menu.querySelector("[data-gerenciar]");

    if (botaoGerenciar && !window.podeGerenciarCompeticao) {
        botaoGerenciar.hidden = true;
    }

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
