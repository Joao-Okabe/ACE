import { timeService } from "../../../services/timeService.js";
import { apiFetch } from "../../../api/api.js";
import { estadoTime, obterIdTime } from "./utilitarios.js";
import { inicializarEscalacao } from "./escalacao.js";
import { carregarIntegrantesTime } from "./integrantes.js";
import { carregarResponsaveisTime } from "./responsaveis.js";
import { carregarTecnicosTime } from "./tecnicos.js";
import { configurarImagem } from "../../../utils/imagens.js";

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
        const editarNomeTime = document.getElementById("editarNomeTime");

        if (nomeTime) {
            nomeTime.textContent = time.nm_time;
        }

        if (editarNomeTime) {
            editarNomeTime.value = time.nm_time || "";
        }

        configurarImagem(
            document.getElementById("editarPreviewEscudo"),
            [time.path_escudo, time.imagem],
            "/assets/img/escudo.jpg"
        );

        if (brasaoEscola) {
            configurarImagem(
                brasaoEscola,
                [time.brasao_escola, time.path_brasao, time.img_logo],
                "/assets/img/brasao.jpg"
            );
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
