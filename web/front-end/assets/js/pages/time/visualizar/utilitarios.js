// ESTADO DO TIME
export const estadoTime = {
    integrantes: [],
    funcoes: [],
    responsaveis: []
};

// OBTER ID DO TIME
export function obterIdTime() {
    const parametros = new URLSearchParams(window.location.search);
    return parametros.get("id");
}
