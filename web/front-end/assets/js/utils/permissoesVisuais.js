import { authService } from "../services/authService.js";

function normalizarCodigoPapel(papel) {
    if (typeof papel === "string") return papel.trim().toUpperCase();

    return String(
        papel?.sigla ??
        papel?.sg_papel ??
        papel?.codigo ??
        papel?.nm_papel ??
        papel?.nome ??
        ""
    ).trim().toUpperCase();
}

export async function obterPapeisUsuario() {
    const resposta = await authService.me();

    if (resposta?.sucesso === false) {
        throw new Error(resposta.mensagem || "Não foi possível verificar o usuário.");
    }

    // apiFetch já desempacota a propriedade "dados" do retorno da API.
    const sessao = resposta?.dados ?? resposta?.data ?? resposta;
    const papeis = Array.isArray(sessao?.papeis) ? sessao.papeis : [];

    return [...new Set(papeis.map(normalizarCodigoPapel).filter(Boolean))];
}

export async function usuarioEhVisitante() {
    return (await obterPapeisUsuario()).includes("VIS");
}

export function definirVisibilidade(elemento, visivel) {
    if (!elemento) return;

    elemento.hidden = !visivel;

    if (visivel) {
        elemento.style.removeProperty("display");
        elemento.removeAttribute("aria-hidden");
    } else {
        elemento.style.setProperty("display", "none", "important");
        elemento.setAttribute("aria-hidden", "true");
    }
}

export function definirVisibilidadeSeletor(seletor, visivel, raiz = document) {
    raiz.querySelectorAll(seletor).forEach(elemento => {
        definirVisibilidade(elemento, visivel);
    });
}
