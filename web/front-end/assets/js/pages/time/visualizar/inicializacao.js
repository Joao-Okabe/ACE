import { configurarAbas } from "./abas.js";
import { configurarFormularioEscalacao } from "./escalacao.js";
import {
    configurarModalIntegrante,
    configurarAcoesCapitao,
    configurarRemocaoIntegrante,
    configurarEdicaoIntegrante
} from "./integrantes.js";
import { configurarModalResponsavel } from "./responsaveis.js";
import { configurarModalTecnico } from "./tecnicos.js";
import { carregarInformacoesTime } from "./time.js";
import { carregarPartidasTime } from "./partidas.js";
import { configurarGerenciamentoTime } from "./gerenciamento.js";
import { configurarBotaoVoltar } from "./voltar.js";
import { estadoTime } from "./utilitarios.js";

export async function inicializarVisualizacao() {
    configurarAbas();
    configurarBotaoVoltar();

    // Carrega o time e confirma as permissões antes das ações administrativas.
    await carregarInformacoesTime();

    if (estadoTime.podeGerenciar === true) {
        configurarFormularioEscalacao();
        configurarModalIntegrante();
        configurarEdicaoIntegrante();
        configurarAcoesCapitao();
        configurarRemocaoIntegrante();
        configurarModalResponsavel();
        configurarModalTecnico();
        configurarGerenciamentoTime();
        configurarBotaoVoltar();
    }

    // Reconsulta as partidas no servidor a cada abertura/atualização da página.
    await carregarPartidasTime();
}
