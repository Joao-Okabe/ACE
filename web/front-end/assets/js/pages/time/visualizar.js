import { inicializarVisualizacao } from "./visualizar/inicializacao.js";

async function iniciarPagina() {
    try {
        await inicializarVisualizacao();
    } catch (erro) {
        console.error("Erro ao inicializar a página:", erro);
    }
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", iniciarPagina, { once: true });
} else {
    iniciarPagina();
}
