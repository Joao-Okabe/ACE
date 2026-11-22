import { apiFetch } from "../api/api.js";

const CHAVE_PROMESSA_USUARIO = "__ACE_AUTH_ME_PROMISE__";

function invalidarUsuario() {
    if (typeof window !== "undefined") {
        delete window[CHAVE_PROMESSA_USUARIO];
    }
}

export const authService = {
    login(email, senha) {
        invalidarUsuario();
        return apiFetch("/auth/login", {
            method: "POST",
            body: { email, senha }
        }).finally(invalidarUsuario);
    },

    me() {
        if (typeof window === "undefined") {
            return apiFetch("/auth/me");
        }

        if (!window[CHAVE_PROMESSA_USUARIO]) {
            window[CHAVE_PROMESSA_USUARIO] = apiFetch("/auth/me")
                .then(resposta => {
                    window.usuarioLogado = resposta;
                    return resposta;
                })
                .catch(erro => {
                    invalidarUsuario();
                    throw erro;
                });
        }

        return window[CHAVE_PROMESSA_USUARIO];
    },

    logout() {
        invalidarUsuario();
        return apiFetch("/auth/logout", {
            method: "POST"
        }).finally(invalidarUsuario);
    },

    cadastrar(dados) {
        return apiFetch("/usuarios", {
            method: "POST",
            body: dados
        });
    }
};
