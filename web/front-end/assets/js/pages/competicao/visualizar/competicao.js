import { competicaoService } from "../../../services/competicaoService.js";
import { preencherPeriodoInscricao } from "./inscricao.js";
<<<<<<< HEAD
import {
    renderizarTimesCompeticao,
    preencherTimesDisponiveis
} from "./times.js";
import { renderizarChaveamento } from "./chaveamento.js";
import { carregarEtapas } from "./etapas.js";
import { configurarPermissoes } from "./permissoes.js";
import { formatarData } from "./utilitarios.js";
=======
import { renderizarTimesCompeticao, preencherTimesDisponiveis } from "./times.js";
import { renderizarChaveamento } from "./chaveamento.js";
import { carregarPartidas, configurarCadastroPartida } from "./partidas.js";
import { carregarEtapas } from "./etapas.js";
import { configurarPermissoes } from "./permissoes.js";
import { formatarData } from "./utilitarios.js";
import { carregarClassificacaoGrupos } from "./classificacaoGrupos.js";
import { exibirMensagem } from "../../../utils/mensagens.js";
import { usuarioEhVisitante } from "../../../utils/permissoesVisuais.js";
>>>>>>> origin/api_v1

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
<<<<<<< HEAD
        const resposta = await competicaoService.visualizar(idCompeticao);
        const competicao = resposta.competicao;
=======
        try {
            window.usuarioVisitante = await usuarioEhVisitante();
        } catch (erroSessao) {
            console.warn("Não foi possível verificar o papel do usuário:", erroSessao);
            window.usuarioVisitante = true;
        }

        const resposta = await competicaoService.visualizar(idCompeticao);
        const competicao = resposta.competicao;
        console.log("DADOS DA COMPETIÇÃO:", competicao);
        console.log("TIMES INSCRITOS:", resposta.timesInscritos);
        console.log("CHAVEAMENTO COMPLETO:", JSON.stringify(resposta.chaveamento, null, 2));
>>>>>>> origin/api_v1

        if (!competicao) {
            throw new Error("Os dados da competição não foram encontrados.");
        }

        const formatoCompeticao = Number(competicao.cd_formato);

        window.podeGerenciarCompeticao = resposta.podeGerenciar === true;
        window.podeGerarChaveamento = resposta.podeGerarChaveamento === true;
<<<<<<< HEAD
=======
        window.podeExcluirCompeticao = resposta.podeExcluir === true;
>>>>>>> origin/api_v1

        preencherDadosCompeticao(competicao);
        preencherPeriodoInscricao(resposta.periodoInscricao);
        renderizarTimesCompeticao(resposta.timesInscritos);
        preencherTimesDisponiveis(resposta.timesDisponiveis);
<<<<<<< HEAD
        renderizarChaveamento(resposta.chaveamento, formatoCompeticao);

        await carregarEtapas();
=======
        configurarCadastroPartida(resposta.timesInscritos, resposta.chaveamento);
        renderizarChaveamento(resposta.chaveamento, formatoCompeticao);
	    await carregarClassificacaoGrupos(idCompeticao, formatoCompeticao);

        await carregarEtapas();
        await carregarPartidas();
>>>>>>> origin/api_v1
        configurarPermissoes();

    } catch (erro) {
        console.error("Erro ao carregar competição:", erro);
        mostrarErro(erro.message || "Não foi possível carregar a competição.");
    }
}

// PREENCHER DADOS DA COMPETIÇÃO
function preencherDadosCompeticao(competicao) {
<<<<<<< HEAD
    nomeCompeticao.textContent = competicao.nm_competicao || "Competição sem nome";
=======
    const nome = competicao.nm_competicao || "";

    // Informações exibidas na página
    nomeCompeticao.textContent = nome || "Competição sem nome";
>>>>>>> origin/api_v1

    document.getElementById("dataInicio").textContent =
        formatarData(competicao.inicio_em);

    document.getElementById("dataFim").textContent =
        formatarData(competicao.fim_em);

<<<<<<< HEAD
    document.getElementById("localCompeticao").textContent =
        competicao.local || "Não informado";

    document.getElementById("editarDataInicio").value =
        competicao.inicio_em ? String(competicao.inicio_em).slice(0, 10) : "";

    document.getElementById("editarDataFim").value =
        competicao.fim_em ? String(competicao.fim_em).slice(0, 10) : "";
}

=======
    // Campos de edição
    document.getElementById("editarNomeCompeticao").value = nome;

    document.getElementById("editarDataInicio").value =
        competicao.inicio_em
            ? String(competicao.inicio_em).slice(0, 10)
            : "";

    document.getElementById("editarDataFim").value =
        competicao.fim_em
            ? String(competicao.fim_em).slice(0, 10)
            : "";
}


>>>>>>> origin/api_v1
// MOSTRAR ERRO
function mostrarErro(mensagem) {
    nomeCompeticao.textContent = "Não foi possível carregar a competição.";

    const aviso = document.getElementById("mensagensInscricao");

    if (aviso) {
<<<<<<< HEAD
        aviso.textContent = mensagem;
=======
        exibirMensagem(aviso, mensagem, "erro");
>>>>>>> origin/api_v1
    }
}
