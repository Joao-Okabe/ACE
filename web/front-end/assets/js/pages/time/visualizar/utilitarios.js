// ESTADO DO TIME
export const estadoTime = {
    integrantes: [],
    funcoes: [],
<<<<<<< HEAD
    responsaveis: []
=======
    responsaveis: [],
    podeGerenciar: false,
    permissaoVerificada: false,
    somenteLeitura: true
>>>>>>> origin/api_v1
};

// OBTER ID DO TIME
export function obterIdTime() {
    const parametros = new URLSearchParams(window.location.search);
    return parametros.get("id");
}
