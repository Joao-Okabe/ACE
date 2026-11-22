import { apiFetch } from "../api/api.js";

const BASE_URL = "/rankings";

const TIPOS_PERMITIDOS = new Set([
    "equipes",
    "escolas",
    "atletas"
]);

function validarId(valor, descricao = "ID") {
    const id = Number(valor);

    if (!Number.isInteger(id) || id <= 0) {
        throw new Error(`${descricao} inválido.`);
    }

    return encodeURIComponent(String(id));
}

function montarQuery(filtros = {}) {
    const params = new URLSearchParams();

    const camposPermitidos = [
        "competicaoId",
        "modalidadeId",
        "esporteId",
        "escolaId",
        "metricaId",
        "escopo",
        "limite"
    ];

    camposPermitidos.forEach(campo => {
        const valor = filtros[campo];

        if (valor !== undefined && valor !== null && String(valor).trim() !== "") {
            params.set(campo, String(valor));
        }
    });

    return params.toString();
}

export const rankingService = {
    listar(tipo, filtros = {}) {
        if (!TIPOS_PERMITIDOS.has(tipo)) {
            throw new Error("Tipo de ranking inválido.");
        }

        const query = montarQuery(filtros);

        return apiFetch(
            `${BASE_URL}/${tipo}${query ? `?${query}` : ""}`
        );
    },

    listarMetricasAtleta(idEsporte) {
        return apiFetch(
            `/esportes/${validarId(idEsporte, "ID do esporte")}/metricas-atleta`
        );
    },

    listarModalidades() {
        return apiFetch("/modalidades");
    }
};
