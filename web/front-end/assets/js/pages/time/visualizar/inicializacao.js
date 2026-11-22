import { configurarAbas } from "./abas.js";
import { configurarFormularioEscalacao } from "./escalacao.js";
import {
    configurarModalIntegrante,
    configurarEdicaoIntegrante
} from "./integrantes.js";
import { configurarModalResponsavel } from "./responsaveis.js";
import { configurarModalTecnico } from "./tecnicos.js";
import { carregarInformacoesTime } from "./time.js";

// INICIALIZAR VISUALIZAÇÃO
export async function inicializarVisualizacao() {
    configurarAbas();
    configurarFormularioEscalacao();
    configurarModalIntegrante();
     configurarEdicaoIntegrante();
    configurarModalResponsavel();
    configurarModalTecnico();

    await carregarInformacoesTime();
}
