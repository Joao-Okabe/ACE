import { competicaoService } from "../../../services/competicaoService.js";
import { idCompeticao } from "./competicao.js";
import { exibirMensagem } from "../../../utils/mensagens.js";
import { formatarData } from "./utilitarios.js";

// CONFIGURAR INSCRIÇÃO DE TIMES
export function configurarFormularioInscricao(atualizarCompeticao) {
    const formulario = document.getElementById("formInscreverTime");
    const containerSelect = document.getElementById("selectTimesDisponiveis");
    const botao = document.getElementById("botaoInscreverTime");
    const mensagem = document.getElementById("mensagensInscricao");

    if (!formulario || !containerSelect || !botao || !mensagem) {
        console.error("Elementos do formulário de inscrição não encontrados.");
        return;
    }

    formulario.addEventListener("submit", async event => {
        event.preventDefault();

        /* OBTER TIMES SELECIONADOS */
        const selecionados = containerSelect.querySelectorAll(
            'input[name="timesSelecionados"]:checked'
        );

        const idsTimes = Array.from(selecionados).map(
            checkbox => checkbox.value
        );

        if (!idsTimes.length) {
            exibirMensagem(mensagem, "Selecione pelo menos um time.", "erro");
            return;
        }

        /* VALIDAR PERÍODO DE INSCRIÇÃO */
        const inicioInscricao = document.getElementById("inicioInscricao");

        if (
            !inicioInscricao ||
            ["Não definido", "Não definida"].includes(inicioInscricao.textContent.trim())
        ) {
            exibirMensagem(
                mensagem,
                "Cadastre o período de inscrição antes de inscrever times.",
                "info"
            );
            return;
        }

        botao.disabled = true;

        let sucessos = 0;
        let falhas = 0;

        try {
            /* INSCREVER TIMES */
            for (const idTime of idsTimes) {
                try {
                    await competicaoService.inscreverTime(idCompeticao, idTime);
                    sucessos++;
                } catch (erro) {
                    console.error(`Erro ao inscrever o time ${idTime}:`, erro);
                    falhas++;
                }
            }

            await atualizarCompeticao();

            /* EXIBIR RESULTADO */
            if (falhas === 0) {
                exibirMensagem(
                    mensagem,
                    `${sucessos} ${sucessos === 1 ? "time inscrito" : "times inscritos"} com sucesso!`,
                    "sucesso"
                );
            } else {
                exibirMensagem(
                    mensagem,
                    `${sucessos} inscritos com sucesso e ${falhas} não puderam ser inscritos.`,
                    sucessos > 0 ? "info" : "erro"
                );
            }

        } catch (erro) {
            console.error("Erro ao atualizar competição:", erro);

            exibirMensagem(
                mensagem,
                "Não foi possível atualizar os dados da competição.",
                "erro"
            );
        } finally {
            botao.disabled = false;
        }
    });
}


// CONFIGURAR PERÍODO DE INSCRIÇÃO
export function configurarFormularioPeriodo(atualizarCompeticao) {
    const formulario = document.getElementById("formCadastrarPeriodo");
    const botao = document.getElementById("botaoSalvarPeriodo");
    const mensagem = document.getElementById("mensagemPeriodoInscricao");

    formulario.addEventListener("submit", async event => {
        event.preventDefault();

        const dados = {
            dt_inicio_inscricao: formulario.elements.dt_inicio_inscricao.value,
            dt_encerramento_inscricao: formulario.elements.dt_encerramento_inscricao.value || null
        };

        botao.disabled = true;
        exibirMensagem(mensagem, "Salvando período...", "info");

        try {
            await competicaoService.criarInscricao(idCompeticao, dados);

            exibirMensagem(
                mensagem,
                "Período de inscrição cadastrado com sucesso!",
                "sucesso"
            );

            await atualizarCompeticao();

        } catch (erro) {
            console.error("Erro ao cadastrar período:", erro);
            exibirMensagem(
                mensagem,
                erro.message || "Não foi possível cadastrar o período.",
                "erro"
            );
        } finally {
            botao.disabled = false;
        }
    });
}

// PREENCHER PERÍODO DE INSCRIÇÃO
export function preencherPeriodoInscricao(periodo) {
    const inicio = document.getElementById("inicioInscricao");
    const fim = document.getElementById("fimInscricao");

    const campoInicio = document.getElementById("dataInicioInscricao");
    const campoFim = document.getElementById("dataFimInscricao");

    if (!periodo) {
        inicio.textContent = "Não definida";
        fim.textContent = "Não definida";

        campoInicio.value = "";
        campoFim.value = "";
        return;
    }

    const dataInicio = periodo.dt_inicio_inscricao || "";
    const dataFim = periodo.dt_encerramento_inscricao || "";

    inicio.textContent = formatarData(dataInicio);
    fim.textContent = formatarData(dataFim);

    campoInicio.value = dataInicio ? dataInicio.slice(0, 10) : "";
    campoFim.value = dataFim ? dataFim.slice(0, 10) : "";
}