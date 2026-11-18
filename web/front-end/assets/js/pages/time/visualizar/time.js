import { timeService } from "../../../services/timeService.js";
import { apiFetch } from "../../../api/api.js";
import { estadoTime, obterIdTime } from "./utilitarios.js";
import { inicializarEscalacao } from "./escalacao.js";
import { carregarIntegrantesTime } from "./integrantes.js";
import { carregarResponsaveisTime } from "./responsaveis.js";
import { carregarTecnicosTime } from "./tecnicos.js";

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

        // INFORMAÇÕES PRINCIPAIS
        const nomeTime = document.getElementById("nomeTime");
        const brasaoEscola = document.getElementById("brasaoEscola");

        if (nomeTime) {
            nomeTime.textContent = time.nm_time;
        }

        if (brasaoEscola) {
            brasaoEscola.src = time.brasao_escola || "/assets/img/default-school.png";
        }

        // CARREGAR FUNÇÕES
        const funcoes = await apiFetch(`/times/${idTime}/funcoes`);

        estadoTime.funcoes = funcoes || [];

        // CARREGAR DEMAIS INFORMAÇÕES
        await carregarIntegrantesTime(idTime);

        await Promise.all([
            carregarResponsaveisTime(idTime),
            carregarTecnicosTime(idTime)
        ]);

        // INICIALIZAR ESCALAÇÃO
        inicializarEscalacao();

    } catch (erro) {
        console.error("Erro ao carregar informações do time:", erro);
    }
}
