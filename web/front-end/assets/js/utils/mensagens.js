const TIPOS_MENSAGEM = {
    sucesso: "sucesso",
    success: "sucesso",
    erro: "erro",
    error: "erro",
    info: "info",
    warning: "info",
    aviso: "info"
};

function normalizarTipo(tipo = "info") {
    return TIPOS_MENSAGEM[String(tipo).toLowerCase()] || "info";
}

export function exibirMensagem(elemento, mensagem, tipo = "info") {
    if (!elemento) return;

    const tipoNormalizado = normalizarTipo(tipo);

    elemento.className = `mensagem-feedback mensagem-${tipoNormalizado}`;
    elemento.textContent = String(mensagem ?? "");
    elemento.setAttribute("role", tipoNormalizado === "erro" ? "alert" : "status");
    elemento.setAttribute("aria-live", tipoNormalizado === "erro" ? "assertive" : "polite");
}

export function limparMensagem(elemento) {
    if (!elemento) return;

    elemento.classList.remove(
        "mensagem-feedback",
        "mensagem-sucesso",
        "mensagem-erro",
        "mensagem-info"
    );
    elemento.textContent = "";
}

function inferirTipoAlerta(mensagem) {
    const texto = String(mensagem).toLowerCase();

    if (/erro|falha|não foi possível|nao foi possivel|inválid|inval|sem permissão|sem permissao|não encontrado|nao encontrado/.test(texto)) {
        return "erro";
    }

    if (/sucesso|conclu[ií]d|cadastrad[oa]|salv[oa]|atualizad[oa]|removid[oa]|adicionad[oa]|inscrit[oa]/.test(texto)) {
        return "sucesso";
    }

    return "info";
}

export function mostrarAlerta(mensagem, tipo = "info") {
    const tipoNormalizado = normalizarTipo(tipo);
    let container = document.getElementById("ace-alertas");

    if (!container) {
        container = document.createElement("div");
        container.id = "ace-alertas";
        container.setAttribute("aria-live", "polite");
        container.setAttribute("aria-relevant", "additions");
        document.body.appendChild(container);
    }

    const alerta = document.createElement("div");
    alerta.className = `ace-alerta ace-alerta-${tipoNormalizado}`;
    alerta.setAttribute("role", tipoNormalizado === "erro" ? "alert" : "status");

    const texto = document.createElement("span");
    texto.className = "ace-alerta-texto";
    texto.textContent = String(mensagem ?? "");

    const fechar = document.createElement("button");
    fechar.type = "button";
    fechar.className = "ace-alerta-fechar";
    fechar.textContent = "Fechar";
    fechar.setAttribute("aria-label", "Fechar aviso");

    let temporizador;

    const remover = () => {
        if (temporizador) window.clearTimeout(temporizador);
        alerta.remove();

        if (!container.childElementCount) {
            container.remove();
        }
    };

    fechar.addEventListener("click", remover);
    alerta.append(texto, fechar);
    container.appendChild(alerta);

    temporizador = window.setTimeout(remover, 7000);
}

export function instalarAlertaPadrao() {
    if (window.__aceAlertaPadraoInstalado) return;

    window.alert = mensagem => {
        mostrarAlerta(mensagem, inferirTipoAlerta(mensagem));
    };

    window.__aceAlertaPadraoInstalado = true;
}
