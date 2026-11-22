const ACE_NAV_SW = "/ace-navigation-sw.js";
const ACE_NAV_PARAM = "__ace_nav";
const ACE_GLOBAL_STYLES = new Set([
    "/assets/bootstrap-5.3.8-dist/css/bootstrap-grid.min.css",
    "/assets/css/base.css",
    "/assets/css/layout.css",
    "/assets/css/components.css",
    "/assets/css/modal.css"
]);

let navegacaoAtiva = false;
let numeroNavegacao = 0;
let requisicaoAtual = null;
const scriptsClassicosCarregados = new Set();

function normalizarUrl(valor, base = window.location.href) {
    return new URL(valor, base);
}

function ehEstiloGlobal(link, base = window.location.href) {
    const valor = link.getAttribute?.("href") || link.href;
    const url = normalizarUrl(valor, base);
    return ACE_GLOBAL_STYLES.has(url.pathname);
}

function marcarEstilosAtuais() {
    document.querySelectorAll('link[rel="stylesheet"][href]').forEach(link => {
        if (!ehEstiloGlobal(link)) {
            link.dataset.acePageStyle = "true";
        }
    });
}

function ehPaginaComShell(doc) {
    return Boolean(
        doc.querySelector("app-bar") &&
        doc.querySelector("app-sidebar") &&
        doc.querySelector(".content")
    );
}

function ehLinkElegivel(link, evento) {
    if (!link || evento.defaultPrevented) return false;
    if (evento.button !== 0 || evento.metaKey || evento.ctrlKey ||
        evento.shiftKey || evento.altKey) return false;
    if (link.target && link.target !== "_self") return false;
    if (link.hasAttribute("download") || link.hasAttribute("data-no-pjax")) return false;

    const url = normalizarUrl(link.href);
    if (url.origin !== window.location.origin) return false;
    if (!url.pathname.startsWith("/pages/")) return false;

    const atual = new URL(window.location.href);
    if (url.href === atual.href) return false;
    if (url.pathname === atual.pathname &&
        url.search === atual.search &&
        url.hash) return false;

    return true;
}

function aguardarEventoDeRecurso(elemento, eventoSucesso, tempoLimite = 5000) {
    return new Promise(resolve => {
        let concluido = false;
        const finalizar = sucesso => {
            if (concluido) return;
            concluido = true;
            clearTimeout(timer);
            elemento.removeEventListener(eventoSucesso, sucessoHandler);
            elemento.removeEventListener("error", erroHandler);
            resolve(sucesso);
        };
        const sucessoHandler = () => finalizar(true);
        const erroHandler = () => finalizar(false);
        const timer = setTimeout(() => finalizar(false), tempoLimite);
        elemento.addEventListener(eventoSucesso, sucessoHandler, { once: true });
        elemento.addEventListener("error", erroHandler, { once: true });
    });
}

async function prepararEstilos(doc, urlDocumento) {
    const linksDestino = [...doc.querySelectorAll('link[rel="stylesheet"][href]')];
    const linksAtuais = [...document.querySelectorAll('link[rel="stylesheet"][href]')];
    const porHref = new Map(linksAtuais.map(link => [normalizarUrl(link.href).href, link]));
    const linksOrdenados = [];
    const linksNovos = [];
    const promessasDeEstilo = [];
    const hrefsDestino = new Set();

    for (const linkOrigem of linksDestino) {
        const href = normalizarUrl(
            linkOrigem.getAttribute("href"),
            urlDocumento
        ).href;
        hrefsDestino.add(href);

        let link = porHref.get(href);
        if (!link) {
            link = document.createElement("link");
            link.rel = "stylesheet";
            link.href = href;
            link.media = "print";
            promessasDeEstilo.push(aguardarEventoDeRecurso(link, "load"));
            document.head.appendChild(link);
            linksNovos.push(link);
        }

        if (ehEstiloGlobal(linkOrigem, urlDocumento)) {
            delete link.dataset.acePageStyle;
        } else {
            link.dataset.acePageStyle = "true";
        }

        linksOrdenados.push(link);
    }

    const resultados = await Promise.all(promessasDeEstilo);

    if (resultados.some(carregou => !carregou)) {
        linksNovos.forEach(link => link.remove());
        throw new Error("Não foi possível preparar todos os estilos da próxima página.");
    }

    return {
        ativar() {
            linksOrdenados.forEach(link => {
                if (linksNovos.includes(link)) link.media = "all";
                document.head.appendChild(link);
            });

            document.querySelectorAll('link[rel="stylesheet"][href]').forEach(link => {
                const href = normalizarUrl(link.href).href;
                if (!hrefsDestino.has(href) && !ehEstiloGlobal(link)) {
                    link.remove();
                }
            });
        },
        cancelar() {
            linksNovos.forEach(link => link.remove());
        }
    };
}

async function carregarScriptClassico(src) {
    const url = normalizarUrl(src).href;
    if (scriptsClassicosCarregados.has(url)) return;

    const script = document.createElement("script");
    script.src = url;
    script.async = false;
    const carregou = aguardarEventoDeRecurso(script, "load", 8000);
    document.head.appendChild(script);
    if (!await carregou) {
        script.remove();
        throw new Error(`Não foi possível carregar o script: ${url}`);
    }
    scriptsClassicosCarregados.add(url);
}

async function executarScriptsDaPagina(doc, identificador) {
    const scripts = [...doc.querySelectorAll("script[src]")];

    for (const scriptOrigem of scripts) {
        const src = scriptOrigem.getAttribute("src");
        if (!src) continue;

        const url = normalizarUrl(src);
        const ehModulo = scriptOrigem.type === "module";

        if (!ehModulo) {
            await carregarScriptClassico(url.href);
            continue;
        }

        if (url.pathname.endsWith("/main.js")) continue;

        url.searchParams.set(ACE_NAV_PARAM, identificador);
        await import(url.href);
    }
}

function importarNosDaPagina(doc) {
    const nos = [];
    for (const no of [...doc.body.childNodes]) {
        if (no.nodeType !== Node.ELEMENT_NODE) continue;
        const tag = no.tagName.toLowerCase();
        if (tag === "app-bar" || tag === "app-sidebar" || tag === "script") continue;
        if (no.id === "ace-carregamento") continue;
        nos.push(document.importNode(no, true));
    }
    return nos;
}

function trocarConteudo(doc) {
    const barra = document.querySelector("app-bar");
    const sidebar = document.querySelector("app-sidebar");
    if (!barra || !sidebar) {
        throw new Error("A estrutura global do ACE não foi encontrada.");
    }

    const novosNos = importarNosDaPagina(doc);

    // Mantém os mesmos Web Components conectados ao documento.
    [...document.body.children].forEach(elemento => {
        if (elemento !== barra && elemento !== sidebar) {
            elemento.remove();
        }
    });

    sidebar.after(...novosNos);
    document.body.className = doc.body.className || "";
    document.title = doc.title || "ACE";

    const menuFechado = Boolean(
        sidebar.querySelector(".sidebar")?.classList.contains("close")
    );
    sidebar.atualizarLayout?.(menuFechado);
    sidebar.configurarPaginaAtual?.();
    window.dispatchEvent(new CustomEvent("ace:page-changed", {
        detail: { url: window.location.href }
    }));
}

async function navegarPara(url, { historico = true, scrollY = null } = {}) {
    const destino = normalizarUrl(url);
    const sequencia = ++numeroNavegacao;
    const id = `${Date.now()}-${sequencia}`;

    requisicaoAtual?.abort();
    requisicaoAtual = new AbortController();

    try {
        const resposta = await fetch(destino.href, {
            method: "GET",
            credentials: "same-origin",
            headers: {
                "Accept": "text/html",
                "X-Requested-With": "ACE-Navigation"
            },
            signal: requisicaoAtual.signal
        });

        if (!resposta.ok) throw new Error(`HTTP ${resposta.status}`);

        const html = await resposta.text();
        if (sequencia !== numeroNavegacao) return;

        const doc = new DOMParser().parseFromString(html, "text/html");

        // Login, cadastro e páginas sem o shell continuam com navegação normal.
        if (!ehPaginaComShell(doc)) {
            window.location.assign(resposta.url || destino.href);
            return;
        }

        const estilos = await prepararEstilos(doc, destino.href);
        if (sequencia !== numeroNavegacao) {
            estilos.cancelar();
            return;
        }

        const atualizar = () => {
            estilos.ativar();
            trocarConteudo(doc);
        };

        if (historico) {
            history.replaceState({
                ...(history.state || {}),
                aceScrollY: window.scrollY
            }, "");
            history.pushState({
                aceNavigation: true,
                aceScrollY: 0
            }, "", destino.href);
        }

        if (document.startViewTransition) {
            const transicao = document.startViewTransition(atualizar);
            await transicao.updateCallbackDone;
        } else {
            atualizar();
        }

        await executarScriptsDaPagina(doc, id);
        if (sequencia !== numeroNavegacao) return;

        if (scrollY !== null) {
            window.scrollTo(0, scrollY);
        } else if (historico) {
            window.scrollTo(0, 0);
        }
        document.dispatchEvent(new CustomEvent("ace:navigation-complete", {
            detail: { url: destino.href }
        }));
    } catch (erro) {
        if (erro.name === "AbortError") return;
        console.error("Navegação interna do ACE falhou; usando navegação normal:", erro);
        window.location.assign(destino.href);
    }
}

window.aceNavigateTo = url => {
    if (!("serviceWorker" in navigator) || !navigator.serviceWorker.controller) {
        window.location.assign(url);
        return;
    }
    return navegarPara(url);
};

function instalarInterceptacao() {
    if (navegacaoAtiva) return;
    navegacaoAtiva = true;
    marcarEstilosAtuais();

    document.addEventListener("click", evento => {
        const link = evento.target.closest("a[href]");
        if (!ehLinkElegivel(link, evento)) return;

        evento.preventDefault();
        navegarPara(link.href);
    });

    history.scrollRestoration = "manual";
    window.addEventListener("popstate", evento => {
        navegarPara(window.location.href, {
            historico: false,
            scrollY: evento.state?.aceScrollY ?? 0
        });
    });
}

async function ativarServiceWorker() {
    if (!("serviceWorker" in navigator)) return false;

    try {
        await navigator.serviceWorker.register(ACE_NAV_SW, {
            scope: "/",
            updateViaCache: "none"
        });
        await navigator.serviceWorker.ready;

        if (!navigator.serviceWorker.controller) {
            await new Promise(resolve => {
                const timer = setTimeout(resolve, 3000);
                navigator.serviceWorker.addEventListener("controllerchange", () => {
                    clearTimeout(timer);
                    resolve();
                }, { once: true });
            });
        }

        return Boolean(navigator.serviceWorker.controller);
    } catch (erro) {
        console.warn("Navegação sem recarga não foi ativada:", erro);
        return false;
    }
}

[...document.querySelectorAll('script[src]:not([type="module"])')].forEach(script => {
    scriptsClassicosCarregados.add(normalizarUrl(script.src).href);
});

if ("serviceWorker" in navigator) {
    navigator.serviceWorker.addEventListener("controllerchange", instalarInterceptacao);
}

ativarServiceWorker().then(ativo => {
    if (ativo) {
        instalarInterceptacao();
        console.info("Navegação contínua do ACE ativada.");
    } else {
        console.warn("O Service Worker ainda não controla esta página. Se a mensagem continuar após atualizar, verifique o acesso a /ace-navigation-sw.js.");
    }
});
