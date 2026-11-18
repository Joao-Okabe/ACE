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


    listarEtapas(id) {
        return apiFetch(`${BASE_URL}/${validarId(id)}/etapas`);
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
    }
};