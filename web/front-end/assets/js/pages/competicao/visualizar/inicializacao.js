import { carregarCompeticao } from "./competicao.js";
import {
    configurarFormularioInscricao,
    configurarFormularioPeriodo
} from "./inscricao.js";
import { configurarFormularioInformacoes } from "./datas.js";
import { configurarFormularioLocalDatas } from "./localDatas.js";
import { configurarFormularioEdicaoEtapa } from "./etapas.js";
import { configurarBotaoGerarChaveamento } from "./chaveamento.js";
import { configurarAbas } from "./abas.js";
import { inicializarArbitros } from "./arbitros.js";

// INICIALIZAR VISUALIZAÇÃO
export async function inicializarVisualizacao() {
    configurarAbas();
    configurarFormularioInscricao(carregarCompeticao);
    configurarFormularioPeriodo(carregarCompeticao);
    configurarFormularioInformacoes(carregarCompeticao);
    configurarFormularioLocalDatas(carregarCompeticao);
    configurarFormularioEdicaoEtapa();
    configurarBotaoGerarChaveamento(carregarCompeticao);
    configurarMenuGerenciamento();

    await carregarCompeticao();
    await inicializarArbitros();
}

// CONFIGURAR MENU DE GERENCIAMENTO
function configurarMenuGerenciamento() {
    const grupos = document.querySelectorAll(".competicao-gerenciar-grupo");

    grupos.forEach(grupo => {
        const botao = grupo.querySelector(".competicao-gerenciar-item");
        const conteudo = grupo.querySelector(".competicao-gerenciar-conteudo");

        if (!botao || !conteudo) return;

        botao.addEventListener("click", () => {
            const abrir = botao.getAttribute("aria-expanded") !== "true";

            grupos.forEach(item => {
                const outroBotao = item.querySelector(".competicao-gerenciar-item");
                const outroConteudo = item.querySelector(".competicao-gerenciar-conteudo");

                if (!outroBotao || !outroConteudo) return;

                outroBotao.setAttribute("aria-expanded", "false");
                outroConteudo.hidden = true;
            });

            if (abrir) {
                botao.setAttribute("aria-expanded", "true");
                conteudo.hidden = false;
            }
        });
    });
}
