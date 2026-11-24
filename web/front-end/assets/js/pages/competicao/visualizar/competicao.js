import { authService } from "../../../services/authService.js";
import { obterPapeisUsuario } from "../../../utils/permissoesVisuais.js";
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
import { configurarCadastroPartida } from "./partidas.js";
import { carregarClassificacaoGrupos } from "./classificacaoGrupos.js";

export const parametros = new URLSearchParams(window.location.search);
export const idCompeticao = parametros.get("id");
export const nomeCompeticao = document.getElementById("nomeCompeticao");

async function verificarPermissaoExclusao(competicao) {
    window.podeExcluirCompeticao = false;

    try {
        const resposta = await authService.me();
        const sessao = resposta?.dados ?? resposta?.data ?? resposta;
        const usuario = sessao?.usuario ?? sessao;

        const idUsuario = Number(
            usuario?.cd_usuario ??
            usuario?.id_usuario ??
            usuario?.id ??
            sessao?.cd_usuario ??
            0
        );

        const idCriador = Number(competicao.cd_criador);
        const papeis = await obterPapeisUsuario();
        const administrador = papeis.includes("ADM");
        const criador = idUsuario > 0 && idCriador > 0 && idUsuario === idCriador;

        window.podeExcluirCompeticao = administrador || criador;
    } catch (erro) {
        console.error("Erro ao verificar permissão de exclusão:", erro);
        window.podeExcluirCompeticao = false;
    }
}

// CARREGAR COMPETIÇÃO
export async function carregarCompeticao() {
    if (
        !idCompeticao ||
        !Number.isInteger(Number(idCompeticao)) ||
        Number(idCompeticao) <= 0
    ) {
        mostrarErro("ID da competição inválido.");
        return;
    }

    try {
        const resposta = await competicaoService.visualizar(idCompeticao);
        const competicao = resposta?.competicao;

        if (!competicao) {
            throw new Error("Os dados da competição não foram encontrados.");
        }

        const formatoCompeticao = Number(competicao.cd_formato);

        window.podeGerenciarCompeticao = resposta.podeGerenciar === true;
        window.podeGerarChaveamento = resposta.podeGerarChaveamento === true;

        // VERIFICAR SE O USUÁRIO PODE EXCLUIR
        await verificarPermissaoExclusao(competicao);

        preencherDadosCompeticao(competicao);
        preencherPeriodoInscricao(resposta.periodoInscricao);
        renderizarTimesCompeticao(resposta.timesInscritos || []);
        preencherTimesDisponiveis(resposta.timesDisponiveis || []);

        // A classificação é consultada uma única vez e reaproveitada nas duas telas.
        let classificacaoGrupos = [];

        if (formatoCompeticao === 3) {
            try {
                classificacaoGrupos = await competicaoService.listarClassificacao(idCompeticao);
                if (!Array.isArray(classificacaoGrupos)) {
                    classificacaoGrupos = [];
                }
            } catch (erro) {
                console.error("Erro ao consultar classificação dos grupos:", erro);
            }
        }

        await renderizarChaveamento(
            resposta.chaveamento || [],
            formatoCompeticao,
            classificacaoGrupos
        );

        await carregarClassificacaoGrupos(
            idCompeticao,
            formatoCompeticao,
            classificacaoGrupos
        );

        configurarCadastroPartida(
            resposta.timesInscritos || [],
            resposta.chaveamento || []
        );

        await carregarEtapas();
        configurarPermissoes();
    } catch (erro) {
        console.error("Erro ao carregar competição:", erro);
        mostrarErro(erro.message || "Não foi possível carregar a competição.");
    }
}

// PREENCHER DADOS DA COMPETIÇÃO
function preencherDadosCompeticao(competicao) {
    if (nomeCompeticao) {
        nomeCompeticao.textContent = competicao.nm_competicao || "Competição sem nome";
    }

    const editarNome = document.getElementById("editarNomeCompeticao");
    if (editarNome) {
        editarNome.value = competicao.nm_competicao || "";
    }

    const dataInicio = document.getElementById("dataInicio");
    if (dataInicio) {
        dataInicio.textContent = formatarData(competicao.inicio_em);
    }

    const dataFim = document.getElementById("dataFim");
    if (dataFim) {
        dataFim.textContent = formatarData(competicao.fim_em);
    }

    const elementoLocal = document.getElementById("localCompeticao");
    if (elementoLocal) {
        elementoLocal.textContent = competicao.local || "Não informado";
    }

    const editarDataInicio = document.getElementById("editarDataInicio");
    if (editarDataInicio) {
        editarDataInicio.value = competicao.inicio_em
            ? String(competicao.inicio_em).slice(0, 10)
            : "";
    }

    const editarDataFim = document.getElementById("editarDataFim");
    if (editarDataFim) {
        editarDataFim.value = competicao.fim_em
            ? String(competicao.fim_em).slice(0, 10)
            : "";
    }
}

// MOSTRAR ERRO
function mostrarErro(mensagem) {
    if (nomeCompeticao) {
        nomeCompeticao.textContent = "Não foi possível carregar a competição.";
    }

    const aviso = document.getElementById("mensagensInscricao");
    if (aviso) {
        aviso.textContent = mensagem;
    }
}
