import { competicaoService } from "../../../services/competicaoService.js";
import { idCompeticao, nomeCompeticao } from "./competicao.js";

// CONFIGURAR EDIÇÃO DE LOCAL E DATAS
export function configurarFormularioLocalDatas(atualizarCompeticao) {
    const linkEditar = document.getElementById("editarLocalDatas");
    const formularioContainer = document.getElementById("formularioLocalDatas");
    const formulario = document.getElementById("formEditarLocalDatas");
    const botao = document.getElementById("botaoSalvarLocalDatas");
    const mensagem = document.getElementById("mensagemLocalDatas");

    if (!formulario || !botao || !mensagem) return;

    if (linkEditar && formularioContainer) {
        linkEditar.addEventListener("click", event => {
            event.preventDefault();
            formularioContainer.hidden = false;
        });
    }

    formulario.addEventListener("submit", async event => {
        event.preventDefault();

        const dados = {
            nm_competicao: nomeCompeticao.textContent,
            dt_inicio: document.getElementById("editarDataInicio").value,
            dt_encerramento: document.getElementById("editarDataFim").value
        };

        botao.disabled = true;
        mensagem.textContent = "";

        try {
            await competicaoService.atualizar(idCompeticao, dados);

            mensagem.textContent = "Datas atualizadas com sucesso!";

            if (typeof atualizarCompeticao === "function") {
                await atualizarCompeticao();
            }

        } catch (erro) {
            console.error("Erro ao atualizar datas:", erro);
            mensagem.textContent =
                erro.message || "Não foi possível atualizar as datas.";
        } finally {
            botao.disabled = false;
        }
    });
}
