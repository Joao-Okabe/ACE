// FORMATAR DATA
export function formatarData(data) {
    if (!data) return "Não informada";

    const [ano, mes, dia] = String(data).slice(0, 10).split("-");

    if (
        !ano ||
        !mes ||
        !dia ||
        !Number.isInteger(Number(ano)) ||
        Number(mes) < 1 ||
        Number(mes) > 12 ||
        Number(dia) < 1 ||
        Number(dia) > 31
    ) {
        return "Não informada";
    }

    return `${dia}/${mes}/${ano}`;
}

// ESCAPAR HTML
export function escapeHtml(valor) {
    return String(valor ?? "").replace(/[&<>"']/g, caractere => ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#039;"
    })[caractere]);
}