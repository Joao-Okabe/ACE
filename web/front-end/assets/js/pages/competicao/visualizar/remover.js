import { competicaoService } from "../../../services/competicaoService.js";
import { idCompeticao } from "./competicao.js";
import { exibirMensagem, mostrarAlerta } from "../../../utils/mensagens.js";

// CONFIGURAR REMOÇÃO DA COMPETIÇÃO
export function configurarRemocaoCompeticao() {
    const botao = document.getElementById("botaoRemoverCompeticao");
    const mensagem = document.getElementById("mensagemRemoverCompeticao");

    if (!botao || !mensagem) return;

    botao.addEventListener("click", async () => {
        if (!idCompeticao) {
            exibirMensagem(mensagem, "ID da competição inválido.", "erro");
            return;
        }

        // CONFIRMAR EXCLUSÃO
        const confirmado = window.confirm(
            "Tem certeza de que deseja remover esta competição? Esta ação não poderá ser desfeita."
        );

        if (!confirmado) return;

        botao.disabled = true;
        exibirMensagem(mensagem, "Removendo competição...", "info");

        try {
            await competicaoService.excluir(idCompeticao);

            mostrarAlerta("Competição removida com sucesso!", "sucesso");

            // VOLTAR PARA A LISTAGEM APÓS EXIBIR O AVISO
            window.setTimeout(() => {
                window.location.href = "/pages/competicao/listar.html";
            }, 900);

        } catch (erro) {
            console.error("Erro ao remover competição:", erro);

            exibirMensagem(
                mensagem,
                erro.message || "Não foi possível remover a competição.",
                "erro"
            );

            botao.disabled = false;
        }
    });
}