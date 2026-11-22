import { competicaoService } from "../../../services/competicaoService.js";
import { preencherPeriodoInscricao } from "./inscricao.js";
import {
    renderizarTimesCompeticao,
    preencherTimesDisponiveis
} from "./times.js";
import { renderizarChaveamento } from "./chaveamento.js";
import { carregarEtapas } from "./etapas.js";
import { configurarPermissoes } from "./permissoes.js";
import { formatarData } from "./utilitarios.js";

export const parametros = new URLSearchParams(window.location.search);
export const idCompeticao = parametros.get("id");

export const nomeCompeticao = document.getElementById("nomeCompeticao");

// CARREGAR COMPETIÇÃO
export async function carregarCompeticao() {
    if (!idCompeticao || !Number.isInteger(Number(idCompeticao)) || Number(idCompeticao) <= 0) {
        mostrarErro("ID da competição inválido.");
        return;
    }

    try {
        const resposta = await competicaoService.visualizar(idCompeticao);
        const competicao = resposta.competicao;

        if (!competicao) {
            throw new Error("Os dados da competição não foram encontrados.");
        }

        const formatoCompeticao = Number(competicao.cd_formato);

        window.podeGerenciarCompeticao = resposta.podeGerenciar === true;
        window.podeGerarChaveamento = resposta.podeGerarChaveamento === true;

        preencherDadosCompeticao(competicao);
        preencherPeriodoInscricao(resposta.periodoInscricao);
        renderizarTimesCompeticao(resposta.timesInscritos);
        preencherTimesDisponiveis(resposta.timesDisponiveis);
        renderizarChaveamento(resposta.chaveamento, formatoCompeticao);

        await carregarEtapas();
        configurarPermissoes();

    } catch (erro) {
        console.error("Erro ao carregar competição:", erro);
        mostrarErro(erro.message || "Não foi possível carregar a competição.");
    }
}

// PREENCHER DADOS DA COMPETIÇÃO
function preencherDadosCompeticao(competicao) {
    nomeCompeticao.textContent = competicao.nm_competicao || "Competição sem nome";
    document.getElementById("editarNomeCompeticao").value =
        competicao.nm_competicao || "";

    document.getElementById("dataInicio").textContent =
        formatarData(competicao.inicio_em);

    document.getElementById("dataFim").textContent =
        formatarData(competicao.fim_em);

    document.getElementById("localCompeticao").textContent =
        competicao.local || "Não informado";

    document.getElementById("editarDataInicio").value =
        competicao.inicio_em ? String(competicao.inicio_em).slice(0, 10) : "";

    document.getElementById("editarDataFim").value =
        competicao.fim_em ? String(competicao.fim_em).slice(0, 10) : "";
}

// MOSTRAR ERRO
function mostrarErro(mensagem) {
    nomeCompeticao.textContent = "Não foi possível carregar a competição.";

    const aviso = document.getElementById("mensagensInscricao");

    if (aviso) {
        aviso.textContent = mensagem;
    }
}
