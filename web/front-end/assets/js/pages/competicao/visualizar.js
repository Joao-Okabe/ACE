import { inicializarVisualizacao } from "./visualizar/inicializacao.js";

<<<<<<< HEAD
document.addEventListener("DOMContentLoaded", async () => {
=======
async function iniciarPagina() {
>>>>>>> origin/api_v1
    try {
        await inicializarVisualizacao();
    } catch (erro) {
        console.error("Erro ao inicializar a página:", erro);
    }
<<<<<<< HEAD
});
=======
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", iniciarPagina, { once: true });
} else {
    iniciarPagina();
}
>>>>>>> origin/api_v1
