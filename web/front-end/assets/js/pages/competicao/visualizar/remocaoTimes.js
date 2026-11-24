
import { competicaoService } from "../../../services/competicaoService.js";

// CONFIGURAR REMOÇÃO MÚLTIPLA
export function configurarRemocaoTimes(atualizarCompeticao) {
    const lista = document.getElementById("listaTimesCompeticao");
    const botao = document.getElementById("removerTimes");
    const acoes = document.getElementById("acoesRemocaoTimes");
    const confirmar = document.getElementById("confirmarRemocaoTimes");
    const quantidade = document.getElementById("quantidadeTimesSelecionados");

    if (!lista || !botao || !acoes || !confirmar || !quantidade) return;

    const selecionados = new Set();
    let selecionando = false;

    function obterIdTime(card) {
        const url = new URL(card.href, window.location.origin);
        return url.searchParams.get("id");
    }

    function atualizarSelecao() {
        lista.querySelectorAll(".time-inscrito-card").forEach(card => {
            const id = obterIdTime(card);
            const marcado = selecionados.has(id);

            card.classList.toggle("time-selecionado-remocao", marcado);
            card.setAttribute("aria-selected", String(marcado));
        });

        quantidade.textContent = `${selecionados.size} time(s) selecionado(s)`;
        confirmar.disabled = selecionados.size === 0;
    }

    function definirModoSelecao(ativo) {
        selecionando = ativo;
        selecionados.clear();

        lista.classList.toggle("modo-remocao", ativo);
        acoes.hidden = !ativo;
        botao.textContent = ativo ? "Cancelar seleção" : "Remover times";

        atualizarSelecao();
    }

    function alternarSelecao(card) {
        const id = obterIdTime(card);
        if (!id) return;

        if (selecionados.has(id)) {
            selecionados.delete(id);
        } else {
            selecionados.add(id);
        }

        atualizarSelecao();
    }

    botao.addEventListener("click", () => {
        definirModoSelecao(!selecionando);
    });

    lista.addEventListener("click", evento => {
        if (!selecionando) return;

        const card = evento.target.closest(".time-inscrito-card");
        if (!card || !lista.contains(card)) return;

        evento.preventDefault();
        evento.stopPropagation();

        alternarSelecao(card);
    }, true);

    lista.addEventListener("keydown", evento => {
        if (!selecionando) return;
        if (evento.key !== "Enter" && evento.key !== " ") return;

        const card = evento.target.closest(".time-inscrito-card");
        if (!card || !lista.contains(card)) return;

        evento.preventDefault();
        evento.stopPropagation();

        alternarSelecao(card);
    }, true);

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

        const ids = [...selecionados];
        const erros = [];

        for (const idTime of ids) {
            try {
                await competicaoService.removerTimeInscrito(
                    idCompeticao,
                    idTime
                );
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
