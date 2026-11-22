import { apiFetch } from "../api/api.js";

function validarIdPartida(id) {
    if (!Number.isInteger(Number(id)) || Number(id) <= 0) {
        throw new Error("ID da partida inválido.");
    }

    return encodeURIComponent(String(id));
}

export const partidaService = {
    criar(dados) {
        if (!dados || typeof dados !== "object") {
            throw new Error("Dados da partida inválidos.");
        }

        return apiFetch("/partidas", {
            method: "POST",
            body: dados
        });
    },

    atualizarCalendario(id, inicioAgendado) {
        if (!id || !inicioAgendado) {
            throw new Error("Informe a partida, a data e o horário.");
        }

        return apiFetch(`/partidas/${validarIdPartida(id)}/calendario`, {
            method: "PUT",
            body: { inicio_agendado: inicioAgendado }
        });
    },

    atualizarLocal(id, local) {
        if (!id || !local?.trim()) {
            throw new Error("Informe a partida e o local.");
        }

        return apiFetch(`/partidas/${validarIdPartida(id)}/local`, {
            method: "PUT",
            body: { local_partida: local.trim() }
        });
    },

    iniciar(id) {
        return apiFetch(`/partidas/${validarIdPartida(id)}/iniciar`, {
            method: "PATCH"
        });
    },

    buscarAoVivo(id) {
        return apiFetch(`/partidas/${validarIdPartida(id)}/ao-vivo`);
    },

    registrarPontuacao(id, dados) {
        const timeId = Number(dados?.timeId);
        const pontos = Number(dados?.pontos);
        const alunoId = dados?.alunoId == null || dados.alunoId === ""
            ? null
            : Number(dados.alunoId);

        if (
            !Number.isInteger(timeId) || timeId <= 0 ||
            !Number.isInteger(pontos) || pontos <= 0 ||
            (alunoId !== null &&
                (!Number.isInteger(alunoId) || alunoId <= 0))
        ) {
            throw new Error("Dados da pontuação inválidos.");
        }

        return apiFetch(`/partidas/${validarIdPartida(id)}/pontuacoes`, {
            method: "POST",
            body: {
                timeId,
                alunoId,
                pontos,
                minuto: dados.minuto === "" || dados.minuto == null
                    ? null
                    : Number(dados.minuto),
                observacao: dados.observacao?.trim() || null
            }
        });
    },

    registrarPenalidade(id, dados) {
        const tipos = [
            "FALTA",
            "CARTAO_AMARELO",
            "CARTAO_VERMELHO",
            "PENALIDADE"
        ];

        if (
            !Number.isInteger(Number(dados?.timeId)) ||
            Number(dados.timeId) <= 0 ||
            !Number.isInteger(Number(dados?.alunoId)) ||
            Number(dados.alunoId) <= 0 ||
            !tipos.includes(dados.tipo)
        ) {
            throw new Error("Dados da penalidade inválidos.");
        }

        return apiFetch(`/partidas/${validarIdPartida(id)}/penalidades`, {
            method: "POST",
            body: {
                timeId: Number(dados.timeId),
                alunoId: Number(dados.alunoId),
                tipo: dados.tipo,
                minuto: dados.minuto === "" || dados.minuto == null
                    ? null
                    : Number(dados.minuto),
                observacao: dados.observacao?.trim() || null
            }
        });
    },

    registrarResultado(id, placarCasa, placarVisitante) {
        if (
            !Number.isInteger(Number(placarCasa)) ||
            !Number.isInteger(Number(placarVisitante)) ||
            Number(placarCasa) < 0 ||
            Number(placarVisitante) < 0
        ) {
            throw new Error("Informe um placar válido.");
        }

        return apiFetch(`/partidas/${validarIdPartida(id)}/resultado`, {
            method: "PUT",
            body: {
                placarCasa: Number(placarCasa),
                placarVisitante: Number(placarVisitante)
            }
        });
    },

    listarArbitrosElegiveis(id) {
        return apiFetch(
            `/partidas/${validarIdPartida(id)}/arbitragem/usuarios`
        );
    },

    registrarArbitragem(id, arbitroId, funcao) {
        const funcoes = ["PRINCIPAL", "ASSISTENTE", "MESARIO"];

        if (
            !Number.isInteger(Number(arbitroId)) ||
            Number(arbitroId) <= 0 ||
            !funcoes.includes(funcao)
        ) {
            throw new Error("Dados da arbitragem inválidos.");
        }

        return apiFetch(`/partidas/${validarIdPartida(id)}/arbitragem`, {
            method: "POST",
            body: {
                arbitroId: Number(arbitroId),
                funcao
            }
        });
    },

    listarPorTime(idTime, limite = 30) {
        if (!Number.isInteger(Number(idTime)) || Number(idTime) <= 0) {
            throw new Error("ID do time inválido.");
        }

        const params = new URLSearchParams({
            timeId: String(idTime),
            limite: String(limite)
        });

        return apiFetch(`/calendario?${params.toString()}`);
    },

    listarCalendario(filtros = {}) {
        const params = new URLSearchParams();

        if (filtros.timeId) {
            params.set("timeId", filtros.timeId);
        }

        if (filtros.competicaoId) {
            params.set("competicaoId", filtros.competicaoId);
        }

        if (filtros.limite) {
            params.set("limite", filtros.limite);
        }

        return apiFetch(`/calendario?${params.toString()}`);
    }

};
