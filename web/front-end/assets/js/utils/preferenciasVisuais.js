(() => {
    "use strict";

    const CHAVE = "acePreferenciasVisuais";
    const PADRAO = {
        tema: "claro",
        adaptacaoCores: "padrao",
        escalaFonte: 1
    };

    const TEMAS = ["claro", "escuro"];
    const ADAPTACOES = [
        "padrao",
        "protanopia",
        "deuteranopia",
        "tritanopia",
        "acromatopsia"
    ];
    const ESCALAS = [0.9, 1, 1.1, 1.2, 1.3];
    let preferenciasAtuais = { ...PADRAO };

    function normalizar(preferencias = {}) {
        if (!preferencias || typeof preferencias !== "object") preferencias = {};
        const tema = TEMAS.includes(preferencias.tema)
            ? preferencias.tema
            : PADRAO.tema;

        const adaptacaoCores = ADAPTACOES.includes(preferencias.adaptacaoCores)
            ? preferencias.adaptacaoCores
            : PADRAO.adaptacaoCores;

        const escalaInformada = Number(preferencias.escalaFonte);
        const escalaFonte = ESCALAS.includes(escalaInformada)
            ? escalaInformada
            : PADRAO.escalaFonte;

        return { tema, adaptacaoCores, escalaFonte };
    }

    function obter() {
        try {
            const salvo = localStorage.getItem(CHAVE);
            return normalizar(salvo ? JSON.parse(salvo) : preferenciasAtuais);
        } catch (erro) {
            console.warn("Não foi possível ler as preferências visuais:", erro);
            return { ...preferenciasAtuais };
        }
    }

    function aplicar(preferencias = obter()) {
        const valores = normalizar(preferencias);
        preferenciasAtuais = valores;
        const raiz = document.documentElement;

        raiz.dataset.tema = valores.tema;
        raiz.dataset.adaptacaoCores = valores.adaptacaoCores;
        raiz.dataset.escalaFonte = String(Math.round(valores.escalaFonte * 100));

        // A variável é usada apenas nas declarações de fonte do CSS.
        // Dimensões, imagens e ícones não recebem zoom de página.
        raiz.style.removeProperty("zoom");
        raiz.style.setProperty("--ace-escala-fonte", String(valores.escalaFonte));

        window.dispatchEvent(new CustomEvent("ace:preferencias-visuais", { detail: valores }));
        return valores;
    }

    function salvar(alteracoes = {}) {
        const atuais = obter();
        const novos = normalizar({ ...atuais, ...alteracoes });

        try {
            localStorage.setItem(CHAVE, JSON.stringify(novos));
        } catch (erro) {
            console.warn("Não foi possível salvar as preferências visuais:", erro);
        }

        aplicar(novos);
        return novos;
    }

    // Aplica antes da renderização do restante da página para evitar
    // que o tema claro apareça rapidamente antes do tema escolhido.
    aplicar(obter());

    window.addEventListener("storage", evento => {
        if (evento.key === CHAVE || evento.key === null) {
            let preferencias = PADRAO;

            try {
                preferencias = evento.newValue
                    ? JSON.parse(evento.newValue)
                    : PADRAO;
            } catch {
                preferencias = PADRAO;
            }

            aplicar(preferencias);
        }
    });

    window.ACEPreferenciasVisuais = {
        obter,
        aplicar,
        salvar,
        escalasDisponiveis: [...ESCALAS]
    };
})();
