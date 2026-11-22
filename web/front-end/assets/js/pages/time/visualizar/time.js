
import { timeService } from "../../../services/timeService.js";
import { apiFetch } from "../../../api/api.js";
import { estadoTime, obterIdTime } from "./utilitarios.js";
import { inicializarEscalacao } from "./escalacao.js";
import { carregarIntegrantesTime } from "./integrantes.js";
import { carregarResponsaveisTime } from "./responsaveis.js";
import { carregarTecnicosTime } from "./tecnicos.js";
import { definirVisibilidade } from "../../../utils/permissoesVisuais.js";

// IMAGEM PADRÃO DO TIME
const ESCUDO_PADRAO = "/assets/img/escudo.jpg";

// APLICAR VISIBILIDADE DOS CONTROLES CONFORME A PERMISSÃO REAL
function aplicarPermissoesVisuais(podeGerenciar) {
    const seletores = [
        "#abrirModalIntegrante",
        "#abrirModalResponsavel",
        "#abrirModalTecnico",
        '[data-aba="gerenciamento"]',
        "#formEscalacao",
        "#formEditarInformacoesTime",
        "#botaoRemoverTime",
        "#escalacao .card-posicoes"
    ];

    seletores.forEach(seletor => {
        document.querySelectorAll(seletor).forEach(elemento => {
            definirVisibilidade(elemento, podeGerenciar);
        });
    });

    // A seção continua fechada até a pessoa clicar na aba.
    // Só retiramos o display:none inline que impedia a aba de abrir
    // mesmo para quem tem permissão.
    const secaoGerenciamento = document.getElementById("gerenciamento");

    if (secaoGerenciamento) {
        if (podeGerenciar) {
            secaoGerenciamento.style.removeProperty("display");
            secaoGerenciamento.removeAttribute("aria-hidden");
            secaoGerenciamento.hidden = true;
        } else {
            definirVisibilidade(secaoGerenciamento, false);
        }
    }

    const modais = [
        'app-modal[titulo="Adicionar integrante"]',
        'app-modal[titulo="Editar integrante"]',
        'app-modal[titulo="Adicionar responsável"]',
        'app-modal[titulo="Adicionar técnico"]'
    ];

    modais.forEach(seletor => {
        const modal = document.querySelector(seletor);
        if (!modal) return;

        if (podeGerenciar) {
            modal.hidden = false;
            modal.style.removeProperty("display");
            modal.removeAttribute("aria-hidden");
        } else {
            modal.remove();
        }
    });
}

// DEFINIR ESCUDO DO TIME
function definirEscudo(elemento, imagem) {
    if (!elemento) return;

    const caminhoImagem =
        typeof imagem === "string" && imagem.trim()
            ? imagem.trim()
            : ESCUDO_PADRAO;

    elemento.onerror = () => {
        elemento.onerror = null;
        elemento.src = ESCUDO_PADRAO;
    };

    elemento.src = caminhoImagem;
}

// CARREGAR INFORMAÇÕES DO TIME
export async function carregarInformacoesTime() {
    const idTime = obterIdTime();

    if (!idTime) {
        console.error("ID do time não informado.");
        return;
    }

    try {
        const time = await timeService.buscarPorId(idTime);

        if (!time) {
            console.error("Time não encontrado.");
            return;
        }

        // A permissão é confirmada pela própria API, usando a mesma regra
        // aplicada às operações de gerenciamento do time.
        try {
            await apiFetch(`/times/${idTime}/tecnicos/disponiveis`);
            estadoTime.podeGerenciar = true;
        } catch (erroPermissao) {
            estadoTime.podeGerenciar = false;

            if (![401, 403].includes(erroPermissao?.status)) {
                console.warn(
                    "Não foi possível confirmar a permissão de gerenciamento:",
                    erroPermissao
                );
            }
        }

        estadoTime.permissaoVerificada = true;
        estadoTime.somenteLeitura = !estadoTime.podeGerenciar;

        aplicarPermissoesVisuais(estadoTime.podeGerenciar);

        // PREENCHER INFORMAÇÕES DO GERENCIAMENTO
        const campoNome = document.getElementById("editarNomeTime");
        const previewEscudo = document.getElementById("editarPreviewEscudo");

        if (campoNome) {
            campoNome.value = time.nm_time || "";
        }

        // OBTER IMAGEM DO TIME
        const imagemTime =
            time.path_escudo ||
            time.imagem ||
            ESCUDO_PADRAO;

        // ESCUDO DO GERENCIAMENTO
        definirEscudo(previewEscudo, imagemTime);

        // INFORMAÇÕES PRINCIPAIS
        const nomeTime = document.getElementById("nomeTime");
        const brasaoEscola = document.getElementById("brasaoEscola");

        if (nomeTime) {
            nomeTime.textContent = time.nm_time || "";
        }

        // ESCUDO DA VISUALIZAÇÃO DO TIME
        definirEscudo(brasaoEscola, imagemTime);

        // CARREGAR FUNÇÕES
        const funcoes = await apiFetch(`/times/${idTime}/funcoes`);

        estadoTime.funcoes = funcoes || [];

        // CARREGAR DEMAIS INFORMAÇÕES
        await carregarIntegrantesTime(idTime);

        await Promise.all([
            carregarResponsaveisTime(idTime),
            carregarTecnicosTime(idTime)
        ]);

        // INICIALIZAR ESCALAÇÃO APENAS PARA QUEM PODE ALTERÁ-LA
        if (estadoTime.podeGerenciar) {
            inicializarEscalacao();
        }

    } catch (erro) {
        console.error("Erro ao carregar informações do time:", erro);
    }
}
