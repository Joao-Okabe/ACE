// Navegação entre documentos: mantém o comportamento nativo do navegador.
// A transição visual entre páginas é feita por View Transitions quando suportada.
// Em navegadores sem suporte, a navegação continua funcionando normalmente.

function ehDestinoInterno(valor) {
    try {
        const destino = new URL(valor, window.location.href);
        const atual = new URL(window.location.href);

        return destino.origin === atual.origin &&
            ["http:", "https:"].includes(destino.protocol) &&
            !(destino.pathname === atual.pathname &&
              destino.search === atual.search &&
              destino.hash === atual.hash);
    } catch {
        return false;
    }
}

window.aceNavigateTo = valor => {
    const destino = new URL(valor, window.location.href);

    if (!ehDestinoInterno(destino.href)) {
        window.location.assign(destino.href);
        return;
    }

    window.location.assign(destino.href);
};
