import { competicaoService } from "../../../services/competicaoService.js";

// CONFIGURAR REMOÇÃO MÚLTIPLA
export function configurarRemocaoTimes(atualizarCompeticao) {
    const lista = document.getElementById("listaTimesCompeticao");
    const botao = document.getElementById("removerTimes");
    const acoes = document.getElementById("acoesRemocaoTimes");
    const confirmar = document.getElementById("confirmarRemocaoTimes");
    const quantidade = document.getElementById("quantidadeTimesSelecionados");
    const mensagem = document.getElementById("mensagemTimesCompeticao");

    if (!lista || !botao || !acoes || !confirmar || !quantidade) return;

    const selecionados = new Set();
    let selecionando = false;

    // ATUALIZAR SELEÇÃO VISUAL
    function atualizarSelecao() {
        lista.querySelectorAll(".card-entidade[data-id-time]").forEach(card => {
            const marcado = selecionados.has(card.dataset.idTime);
            card.classList.toggle("time-selecionado-remocao", marcado);

            const checkbox = card.querySelector(".time-remocao-checkbox");
            if (checkbox) checkbox.checked = marcado;
        });

        quantidade.textContent = `${selecionados.size} time(s) selecionado(s)`;
        confirmar.disabled = selecionados.size === 0;
    }

    // ENTRAR OU SAIR DO MODO DE SELEÇÃO
    function definirModoSelecao(ativo) {
        selecionando = ativo;
        selecionados.clear();

        lista.classList.toggle("modo-remocao", ativo);
        acoes.hidden = !ativo;

        botao.textContent = ativo ? "Cancelar seleção" : "Remover times";

        atualizarSelecao();
    }

    // ABRIR SELEÇÃO
    botao.addEventListener("click", () => {
        definirModoSelecao(!selecionando);
    });

    // SELECIONAR O CARD SEM ABRIR SUA PÁGINA
    lista.addEventListener("click", evento => {
        if (!selecionando) return;

        const card = evento.target.closest(".card-entidade[data-id-time]");
        if (!card || !lista.contains(card)) return;

        evento.preventDefault();
        evento.stopPropagation();

        const id = card.dataset.idTime;

        if (selecionados.has(id)) {
            selecionados.delete(id);
        } else {
            selecionados.add(id);
        }

        atualizarSelecao();
    }, true);

    // PERMITIR SELEÇÃO COM TECLADO
    lista.addEventListener("keydown", evento => {
        if (!selecionando) return;
        if (evento.key !== "Enter" && evento.key !== " ") return;

        const card = evento.target.closest(".card-entidade[data-id-time]");
        if (!card || !lista.contains(card)) return;

        evento.preventDefault();
        evento.stopPropagation();

        const id = card.dataset.idTime;

        if (selecionados.has(id)) {
            selecionados.delete(id);
        } else {
            selecionados.add(id);
        }

        atualizarSelecao();
    }, true);

    // CONFIRMAR REMOÇÃO DOS TIMES SELECIONADOS
    confirmar.addEventListener("click", async () => {
        if (!selecionados.size) return;

        const total = selecionados.size;

        if (!window.confirm(
            `Deseja remover ${total} time(s) desta competição?`
        )) return;

        const idCompeticao = new URLSearchParams(
            window.location.search
        ).get("id");

        if (!idCompeticao) return;

        confirmar.disabled = true;
        botao.disabled = true;

        const erros = [];
        const ids = [...selecionados];

        // REMOVER CADA INSCRIÇÃO PELA API EXISTENTE
        for (const idTime of ids) {
            try {
                await competicaoService.removerTimeInscrito(
                    idCompeticao,
                    idTime
                );
                selecionados.delete(idTime);
            } catch (erro) {
                erros.push(`Time ${idTime}: ${erro.message}`);
            }
        }

        definirModoSelecao(false);

        try {
            await atualizarCompeticao();
        } catch (erro) {
            console.error("Erro ao atualizar competição:", erro);
        }

        botao.disabled = false;

        if (erros.length) {
            console.error("Erros na remoção:", erros);
            window.alert(
                `${ids.length - erros.length} time(s) removido(s). ` +
                `${erros.length} não puderam ser removidos.\n\n` +
                erros.join("\n")
            );
        } else {
            window.alert("Times removidos da competição com sucesso!");
        }
    });
}
