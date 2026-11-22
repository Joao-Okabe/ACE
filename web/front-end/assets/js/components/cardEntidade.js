import { configurarImagem } from "../utils/imagens.js";

export function criarCardEntidade({
    foto = "/assets/img/no-prof-pic.png",
    nome = "Nome não informado",
    detalhes = [],
    status = null,
    url = null,
    editar = null
} = {}) {
    const card = document.createElement("div");
    card.className = "card-entidade";

    // FOTO OU BRASÃO
    const imagem = document.createElement("img");
    imagem.className = "card-entidade-foto";
    imagem.alt = `Imagem de ${nome}`;
    imagem.loading = "lazy";
    configurarImagem(imagem, foto, "/assets/img/no-prof-pic.png");

    // INFORMAÇÕES
    const info = document.createElement("div");
    info.className = "card-entidade-info";

    const nomeElemento = document.createElement("span");
    nomeElemento.className = "card-entidade-nome";
    nomeElemento.textContent = nome;

    info.appendChild(nomeElemento);

    // DETALHES
    const detalhesValidos = detalhes.filter(
        valor => valor !== null &&
                 valor !== undefined &&
                 valor !== ""
    );

    if (detalhesValidos.length) {
        const detalhesElemento = document.createElement("span");
        detalhesElemento.className = "card-entidade-detalhes";
        detalhesElemento.textContent = detalhesValidos.join(" · ");
        info.appendChild(detalhesElemento);
    }

    // STATUS OPCIONAL
    if (status) {
        const etiqueta = document.createElement("span");
        etiqueta.className = "card-entidade-status";
        etiqueta.textContent = status;
        info.appendChild(etiqueta);
    }

    card.append(imagem, info);

    // AÇÕES
    const acoes = document.createElement("div");
    acoes.className = "card-entidade-acoes";

    // SETA E NAVEGAÇÃO
    if (url) {
        const seta = document.createElement("span");
        seta.className = "card-entidade-seta";
        seta.textContent = "›";
        seta.setAttribute("aria-hidden", "true");

        acoes.appendChild(seta);

        card.setAttribute("role", "link");
        card.setAttribute("tabindex", "0");

        card.addEventListener("click", evento => {
            if (evento.target.closest("button")) return;
            window.location.href = url;
        });

        card.addEventListener("keydown", evento => {
            if (evento.target !== card) return;

            if (evento.key === "Enter" || evento.key === " ") {
                evento.preventDefault();
                window.location.href = url;
            }
        });
    }

    // BOTÃO EDITAR OPCIONAL
    if (editar) {
        const botao = document.createElement("button");
        botao.type = "button";
        botao.className = "card-entidade-editar";
        botao.textContent = "Editar";
        botao.setAttribute("aria-label", `Editar ${nome}`);

        if (editar.dataset) {
            Object.entries(editar.dataset).forEach(([chave, valor]) => {
                botao.dataset[chave] = String(valor);
            });
        }

        acoes.appendChild(botao);
    }

    if (acoes.children.length) {
        card.appendChild(acoes);
    }

    return card;
}
