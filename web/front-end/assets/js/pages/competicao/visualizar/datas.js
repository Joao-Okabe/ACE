import { competicaoService } from "../../../services/competicaoService.js";
import { idCompeticao } from "./competicao.js";
import { exibirMensagem } from "../../../utils/mensagens.js";

// EDITAR INFORMAÇÕES DA COMPETIÇÃO
export function configurarFormularioInformacoes(atualizarCompeticao) {
    const formulario = document.getElementById("formEditarInformacoesCompeticao");
    const campoNome = document.getElementById("editarNomeCompeticao");
    const botao = document.getElementById("botaoSalvarInformacoesCompeticao");
    const mensagem = document.getElementById("mensagemInformacoesCompeticao");

    if (!formulario || !campoNome || !botao || !mensagem) return;

    formulario.addEventListener("submit", async (event) => {
        event.preventDefault();

        const nome = campoNome.value.trim();

        if (!nome) {
            exibirMensagem(mensagem, "Informe o nome da competição.", "erro");
            return;
        }

        const dados = {
            nm_competicao: nome,
            dt_inicio: document.getElementById("editarDataInicio")?.value || null,
            dt_encerramento: document.getElementById("editarDataFim")?.value || null
        };
        botao.disabled = true;
        exibirMensagem(mensagem, "Salvando informações...", "info");

        try {
            await competicaoService.atualizar(idCompeticao, dados);

            exibirMensagem(
                mensagem,
                "Nome da competição atualizado com sucesso!",
                "sucesso"
            );

            if (typeof atualizarCompeticao === "function") {
                await atualizarCompeticao();
            }
        } catch (erro) {
            console.error("Erro ao atualizar informações:", erro);

            exibirMensagem(
                mensagem,
                erro.message || "Não foi possível atualizar as informações.",
                "erro"
            );
        } finally {
            botao.disabled = false;
        }
    });
}

// EDITAR DATAS DA COMPETIÇÃO
export function configurarFormularioDatas(atualizarCompeticao) {
    const formulario = document.getElementById("formEditarLocalDatas");
    const botao = document.getElementById("botaoSalvarLocalDatas");
    const mensagem = document.getElementById("mensagemLocalDatas");

    if (!formulario || !botao || !mensagem) return;

    formulario.addEventListener("submit", async (event) => {
        event.preventDefault();

        // DADOS PARA ATUALIZAR AS DATAS
        const dados = {
            nm_competicao: document.getElementById("editarNomeCompeticao").value.trim(),
            dt_inicio: document.getElementById("editarDataInicio").value,
            dt_encerramento: document.getElementById("editarDataFim").value
        };
        botao.disabled = true;
        exibirMensagem(mensagem, "Salvando datas...", "info");

        try {
            await competicaoService.atualizar(idCompeticao, dados);

            exibirMensagem(
                mensagem,
                "Datas atualizadas com sucesso!",
                "sucesso"
            );

            if (typeof atualizarCompeticao === "function") {
                await atualizarCompeticao();
            }
        } catch (erro) {
            console.error("Erro ao atualizar datas:", erro);

            exibirMensagem(
                mensagem,
                erro.message || "Não foi possível atualizar as datas.",
                "erro"
            );
        } finally {
            botao.disabled = false;
        }
    });
}
