import { configurarAbas } from "./abas.js";
import { configurarFormularioEscalacao } from "./escalacao.js";
import {
    configurarModalIntegrante,
    configurarAcoesCapitao,
    configurarRemocaoIntegrante
} from "./integrantes.js";
import { configurarModalResponsavel } from "./responsaveis.js";
import { configurarModalTecnico } from "./tecnicos.js";
import { carregarInformacoesTime } from "./time.js";
import { carregarPartidasTime } from "./partidas.js";
import { configurarGerenciamentoTime } from "./gerenciamento.js";
import { configurarBotaoVoltar } from "./voltar.js";
import { estadoTime } from "./utilitarios.js";

// INICIALIZAR VISUALIZAÇÃO
export async function inicializarVisualizacao() {
    configurarAbas();
    configurarBotaoVoltar();

    // carregarInformacoesTime consulta a permissão real na API.
    // Só depois dessa resposta os controles de edição são configurados.
    await carregarInformacoesTime();

    if (estadoTime.podeGerenciar === true) {
        configurarFormularioEscalacao();
        configurarModalIntegrante();
        configurarAcoesCapitao();
        configurarRemocaoIntegrante();
        configurarModalResponsavel();
        configurarModalTecnico();
        configurarGerenciamentoTime();
    }

    await carregarPartidasTime();
}
