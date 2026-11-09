import { apiFetch } from "../api/api.js";
export const authService = {
    login(email, senha) {
        return apiFetch("/auth/login", {
            method: "POST",
            body: { email, senha }
        });
    },
    // @Claralops Ficou faltando esse método, está lá nas rotas da api!
    me() {
        return apiFetch("/auth/me");
    },
    logout() {
        return apiFetch("/auth/logout", {
            method: "POST"
        });
    },
    cadastrar(dados) {
        return apiFetch("/auth/cadastro", {
            method: "POST",
            body: dados
        });
    }
};
