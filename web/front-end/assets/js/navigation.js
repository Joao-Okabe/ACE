// Navegação por documento: cada página inicializa seus módulos uma única vez.
let temporizadorEntrada = null;
let temporizadorLimite = null;

function finalizarCarregamento() {
    clearTimeout(temporizadorEntrada);
    clearTimeout(temporizadorLimite);
    document.documentElement.classList.remove("ace-navegando");
    document.querySelector(".content")?.removeAttribute("aria-busy");
    document.getElementById("ace-carregamento")?.remove();
}

function iniciarCarregamento() {
    finalizarCarregamento();
    temporizadorEntrada = setTimeout(() => {
        const aviso = document.createElement("div");
        aviso.id = "ace-carregamento";
        aviso.setAttribute("role", "status");
        aviso.setAttribute("aria-live", "polite");
        aviso.textContent = "Carregando página…";
        document.body.appendChild(aviso);
        document.documentElement.classList.add("ace-navegando");
        document.querySelector(".content")?.setAttribute("aria-busy", "true");
    }, 150);
    // Cancelamento ou aviso de formulário não deixa a tela bloqueada.
    temporizadorLimite = setTimeout(finalizarCarregamento, 15000);
}

function ehDestinoInterno(valor) {
    try {
        const url = new URL(valor, window.location.href);
        const atual = new URL(window.location.href);
        return url.origin === atual.origin &&
            ["http:", "https:"].includes(url.protocol) &&
            !(url.pathname === atual.pathname && url.search === atual.search);
    } catch {
        return false;
    }
}

window.aceNavigateTo = valor => {
    const destino = new URL(valor, window.location.href);
    if (ehDestinoInterno(destino.href)) iniciarCarregamento();
    // DOMContentLoaded e os módulos funcionam no primeiro acesso e no retorno.
    window.location.assign(destino.href);
};

document.addEventListener("click", evento => {
    const link = evento.target.closest?.("a[href]");
    if (!link || evento.button !== 0 || evento.metaKey || evento.ctrlKey ||
        evento.shiftKey || evento.altKey || link.hasAttribute("download") ||
        (link.target && link.target !== "_self") ||
        !ehDestinoInterno(link.href)) return;
    queueMicrotask(() => {
        if (!evento.defaultPrevented) iniciarCarregamento();
    });
});

document.addEventListener("submit", evento => {
    const form = evento.target;
    if (form.target && form.target !== "_self") return;
    if (new URL(form.action || window.location.href).origin !== window.location.origin) return;
    queueMicrotask(() => {
        if (!evento.defaultPrevented) iniciarCarregamento();
    });
});

window.addEventListener("pageshow", finalizarCarregamento);
window.addEventListener("pagehide", finalizarCarregamento);

