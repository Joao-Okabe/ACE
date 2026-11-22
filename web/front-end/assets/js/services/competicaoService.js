import { apiFetch } from "../api/api.js";

const BASE_URL = "/competicoes";

function validarId(id) {
    if (
        id === null ||
        id === undefined ||
        String(id).trim() === "" ||
        !Number.isInteger(Number(id)) ||
        Number(id) <= 0
    ) {
        throw new Error("ID da competição inválido.");
    }

    return encodeURIComponent(String(id));
}

export const competicaoService = {
    listar(filtros = {}) {
        const params = new URLSearchParams();

        if (filtros.nome) params.set("nome", filtros.nome);
        if (filtros.escola) params.set("escola", filtros.escola);
        if (filtros.ordem) params.set("ordem", filtros.ordem);

        const query = params.toString();

        return apiFetch(`${BASE_URL}${query ? `?${query}` : ""}`);
    },

    buscarPorId(id) {
        return apiFetch(`${BASE_URL}/${validarId(id)}`);
    },

    visualizar(id) {
        return apiFetch(`${BASE_URL}/${validarId(id)}/visualizar`);
    },

    listarClassificacao(id) {
     return apiFetch(`/competicoes/${validarId(id)}/classificacao`);
    },

    gerarChaveamento(id) {
        return apiFetch(`${BASE_URL}/${validarId(id)}/chaveamento`, {
            method: "POST"
        });
    },

    listarPartidas(id) {
        return apiFetch(
            `/calendario?competicaoId=${validarId(id)}`
        );
    },
    
    listarEtapas(id) {
        return apiFetch(`${BASE_URL}/${validarId(id)}/etapas`);
    },


    listarArbitros(id) {
        return apiFetch(`${BASE_URL}/${validarId(id)}/arbitros`);
    },

    listarUsuariosArbitro(id, busca = "") {
        const params = new URLSearchParams();

        if (busca.trim()) {
            params.set("busca", busca.trim());
        }

        const query = params.toString();

        return apiFetch(
            `${BASE_URL}/${validarId(id)}/arbitros/usuarios${query ? `?${query}` : ""}`
        );
    },

    adicionarArbitro(id, usuarioId) {
        return apiFetch(`${BASE_URL}/${validarId(id)}/arbitros`, {
            method: "POST",
            body: {
                usuarioId: Number(usuarioId)
            }
        });
    },

    removerArbitro(id, usuarioId) {
        return apiFetch(
            `${BASE_URL}/${validarId(id)}/arbitros/${validarId(usuarioId)}`,
            {
                method: "DELETE"
            }
        );
    },

    criarEtapa(id, dados) {
        return apiFetch(`${BASE_URL}/${validarId(id)}/etapas`, {
            method: "POST",
            body: dados
        });
    },

    atualizarEtapa(id, idEtapa, dados) {
        return apiFetch(
            `${BASE_URL}/${validarId(id)}/etapas/${validarId(idEtapa)}`,
            {
                method: "PUT",
                body: dados
            }
        );
    },

    inscreverTime(id, idTime) {
        return apiFetch(`${BASE_URL}/${validarId(id)}/inscricao/times`, {
            method: "POST",
            body: {
                id_time: Number(idTime)
            }
        });
    },

    criarInscricao(id, dados) {
        return apiFetch(`${BASE_URL}/${validarId(id)}/inscricao`, {
            method: "POST",
            body: dados
        });
    },

    opcoesCriacao() {
        return apiFetch(`${BASE_URL}/opcoes/criacao`);
    },

    criar(dados) {
        return apiFetch(BASE_URL, {
            method: "POST",
            body: dados
        });
    },

    atualizar(id, dados) {
        return apiFetch(`${BASE_URL}/${validarId(id)}`, {
            method: "PUT",
            body: dados
        });
    },

    excluir(id) {
        return apiFetch(`${BASE_URL}/${validarId(id)}`, {
            method: "DELETE"
        });
    },

    // REMOVER TIME INSCRITO
    removerTimeInscrito(idCompeticao, idTime) {
        return apiFetch(
            `${BASE_URL}/${validarId(idCompeticao)}/inscricao/times/${validarId(idTime)}`,
            { method: "DELETE" }
        );
    },
    
    listarRankingEquipes(id) {
        return apiFetch(`/rankings/equipes?competicaoId=${validarId(id)}`);
    }
};