import { authService } from "./services/authService.js";

import "./components/appBar.js";
import "./components/appSidebar.js";
import "./components/imagemPerfil.js";
import "./components/modal.js";

document.addEventListener("DOMContentLoaded", async () => {
    try {
        const resposta = await authService.me();

        window.usuarioLogado = resposta;

        document.querySelector("app-bar")?.atualizarUsuario?.();

    } catch (erro) {
        console.error("Erro ao carregar usuário logado:", erro);
    }
});