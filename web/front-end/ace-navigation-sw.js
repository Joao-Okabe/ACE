const ACE_NAV_PARAM = "__ace_nav";

self.addEventListener("install", evento => {
    evento.waitUntil(self.skipWaiting());
});

self.addEventListener("activate", evento => {
    evento.waitUntil(self.clients.claim());
});

function adicionarVersaoAoModulo(especificador, urlModulo, identificador) {
    if (!especificador.startsWith(".") && !especificador.startsWith("/")) {
        return especificador;
    }

    const destino = new URL(especificador, urlModulo);
    if (destino.origin !== self.location.origin) return especificador;

    destino.searchParams.set(ACE_NAV_PARAM, identificador);
    return destino.href;
}

function reescreverImportacoes(codigo, urlModulo, identificador) {
    const linhas = codigo.split("\n");

    for (let indice = 0; indice < linhas.length; indice++) {
        if (!/^[ \t]*(?:import|export)\b/.test(linhas[indice])) continue;

        let fim = indice;
        while (fim < linhas.length && !linhas[fim].includes(";")) fim++;
        if (fim >= linhas.length) continue;

        const declaracao = linhas.slice(indice, fim + 1).join("\n");
        let correspondencia = declaracao.match(/\bfrom\s+(["'])([^"']+)\1/);

        if (!correspondencia && /^[ \t]*import\s+["']/.test(declaracao)) {
            correspondencia = declaracao.match(/^[ \t]*import\s+(["'])([^"']+)\1/);
        }

        if (!correspondencia) continue;

        const especificador = correspondencia[2];
        const novoEspecificador = adicionarVersaoAoModulo(
            especificador,
            urlModulo,
            identificador
        );

        if (novoEspecificador === especificador) continue;

        const declaracaoAtualizada = declaracao.replace(
            correspondencia[0],
            correspondencia[0].replace(especificador, novoEspecificador)
        );

        const novasLinhas = declaracaoAtualizada.split("\n");
        linhas.splice(indice, fim - indice + 1, ...novasLinhas);
        indice += novasLinhas.length - 1;
    }

    return linhas.join("\n");
}

self.addEventListener("fetch", evento => {
    const url = new URL(evento.request.url);
    const identificador = url.searchParams.get(ACE_NAV_PARAM);

    if (!identificador || !url.pathname.endsWith(".js")) {
        return;
    }

    evento.respondWith((async () => {
        const urlOriginal = new URL(url.href);
        urlOriginal.searchParams.delete(ACE_NAV_PARAM);

        const resposta = await fetch(urlOriginal.href, {
            credentials: "same-origin",
            cache: "default"
        });

        if (!resposta.ok) return resposta;

        const codigo = await resposta.text();
        const reescrito = reescreverImportacoes(
            codigo,
            urlOriginal.href,
            identificador
        );

        const cabecalhos = new Headers(resposta.headers);
        cabecalhos.delete("content-length");
        cabecalhos.delete("content-encoding");
        cabecalhos.set("content-type", "text/javascript; charset=utf-8");
        cabecalhos.set("cache-control", "no-store");

        return new Response(reescrito, {
            status: resposta.status,
            statusText: resposta.statusText,
            headers: cabecalhos
        });
    })());
});
