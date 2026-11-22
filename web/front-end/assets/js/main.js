import { authService } from "./services/authService.js";
import { instalarAlertaPadrao } from "./utils/mensagens.js";

import "./components/appBar.js";
import "./components/appSidebar.js";
import "./components/appLista.js";
import "./components/imagemPerfil.js";
import "./components/modal.js";
import "./navigation.js";

instalarAlertaPadrao();

document.addEventListener("ace:logout", async () => {
    try {
        await authService.logout();
    } catch (erro) {
        console.error("Não foi possível encerrar a sessão:", erro);
    } finally {
        window.location.assign("/pages/usuario/login.html");
    }
});

document.addEventListener("ace:open-settings", () => {
    if (typeof window.aceNavigateTo === "function") {
        window.aceNavigateTo("/pages/usuario/configuracoes.html");
    } else {
        window.location.assign("/pages/usuario/configuracoes.html");
    }
});

authService.me()
    .then(resposta => {
        window.usuarioLogado = resposta;
        document.querySelector("app-bar")?.atualizarUsuario?.();
    })
    .catch(erro => {
        console.error("Erro ao carregar usuário logado:", erro);
    });
