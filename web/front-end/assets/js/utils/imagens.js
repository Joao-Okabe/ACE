function normalizarCaminhoImagem(caminho) {
    const valor = caminho.trim().replace(/\\/g, "/");

    if (
        /^(?:https?:|data:|blob:|\/\/)/i.test(valor)
        || valor.startsWith("../")
    ) {
        return valor;
    }

    const caminhoUpload = valor.match(/^\/?(?:storage\/)?uploads?\/(.+)$/i);
    if (caminhoUpload) {
        return `/uploads/${caminhoUpload[1]}`;
    }

    if (valor.startsWith("/")) {
        return valor;
    }

    const caminhoRelativo = valor.replace(/^\.\//, "");
    if (
        !caminhoRelativo.includes("/")
        && /\.(?:jpe?g|png|webp|gif)$/i.test(caminhoRelativo)
    ) {
        return `/uploads/${caminhoRelativo}`;
    }

    return `/${caminhoRelativo}`;
}

export function obterFonteImagem(candidatos, fallback) {
    const opcoes = Array.isArray(candidatos) ? candidatos : [candidatos];
    const caminho = opcoes.find(
        valor => typeof valor === "string" && valor.trim() !== ""
    );

    return caminho
        ? normalizarCaminhoImagem(caminho)
        : normalizarCaminhoImagem(fallback);
}

export function configurarImagem(imagem, candidatos, fallback) {
    if (!imagem) return;

    const caminhoFallback = obterFonteImagem(fallback, fallback);
    imagem.onerror = () => {
        imagem.onerror = null;
        imagem.src = caminhoFallback;
    };
    imagem.src = obterFonteImagem(candidatos, caminhoFallback);
}
