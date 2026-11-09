import { apiFetch } from "../api/api.js";
export const authService = {
    login(email, senha) {
        return apiFetch("/auth/login", {
            method: "POST",
            body: { email, senha }
        });
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
