import { apiFetch } from "../api/api.js";

const BASE_URL = "/importacoes";

export const importacaoCsvService = {
    escolasDisponiveis() {
        return apiFetch(`${BASE_URL}/escolas-disponiveis`);
    },

    analisar(tipo, arquivo, escolaId = null) {
        const dados = new FormData();
        dados.append("arquivo", arquivo);
        if (escolaId !== null) {
            dados.append("escola_id", String(escolaId));
        }

        return apiFetch(`${BASE_URL}/${encodeURIComponent(tipo)}/csv`, {
            method: "POST",
            body: dados,
        });
    },

    editarLinha(id, linha, dados) {
        return apiFetch(
            `${BASE_URL}/${encodeURIComponent(id)}/linhas/${encodeURIComponent(linha)}`,
            {
                method: "PATCH",
                body: { dados },
            }
        );
    },

    confirmar(id) {
        return apiFetch(`${BASE_URL}/${encodeURIComponent(id)}/confirmar`, {
            method: "POST",
        });
    },
};
