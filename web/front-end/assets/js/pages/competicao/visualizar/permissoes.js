// PERMISSÕES
export function configurarPermissoes() {
    const botaoGerenciar = document.querySelector("[data-gerenciar]");
    const formularioInscricao = document.getElementById("formInscreverTime");

    const podeGerenciar = window.podeGerenciarCompeticao === true;

    if (botaoGerenciar) {
        botaoGerenciar.hidden = !podeGerenciar;
    }

    if (formularioInscricao) {
        formularioInscricao.hidden = !podeGerenciar;
    }
}
