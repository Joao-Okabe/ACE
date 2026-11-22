// ESTADO DO TIME
export const estadoTime = {
    integrantes: [],
    funcoes: [],
    responsaveis: [],
    podeGerenciar: false,
    permissaoVerificada: false,
    somenteLeitura: true
};

// OBTER ID DO TIME
export function obterIdTime() {
    const parametros = new URLSearchParams(window.location.search);
    return parametros.get("id");
}
