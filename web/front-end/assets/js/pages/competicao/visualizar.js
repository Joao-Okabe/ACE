import { inicializarVisualizacao } from "./visualizar/inicializacao.js";

document.addEventListener("DOMContentLoaded", async () => {
    try {
        await inicializarVisualizacao();
    } catch (erro) {
        console.error("Erro ao inicializar a página:", erro);
    }
});
