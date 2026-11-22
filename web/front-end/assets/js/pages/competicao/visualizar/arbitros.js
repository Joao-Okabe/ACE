
import { competicaoService } from "../../../services/competicaoService.js";
import { idCompeticao } from "./competicao.js";
import { criarCardEntidade } from "../../../components/cardEntidade.js";
import { criarSelectMultiplo } from "../../../components/selectMultiplo.js";
import { exibirMensagem } from "../../../utils/mensagens.js";

const lista = document.getElementById("listaArbitros");
const mensagem = document.getElementById("mensagemArbitros");
const formulario = document.getElementById("formAdicionarArbitro");
const containerArbitro = document.getElementById("arbitroDisponivel");
const botaoAdicionar = document.getElementById("botaoAdicionarArbitro");
const mensagemAdicionar = document.getElementById("mensagemAdicionarArbitro");

let selectMultiploArbitros = null;

// CARREGAR USUÁRIOS DISPONÍVEIS
async function carregarUsuariosArbitro() {
    if (!containerArbitro) return;

    try {
        const resposta = await competicaoService.listarUsuariosArbitro(
            idCompeticao
        );

        const usuarios = Array.isArray(resposta)
            ? resposta
            : resposta.usuarios || [];

        const disponiveis = usuarios.filter(usuario => !usuario.arbitro);

        if (selectMultiploArbitros) {
            selectMultiploArbitros.definirItens(disponiveis);
        } else {
            selectMultiploArbitros = criarSelectMultiplo({
                container: containerArbitro,
                itens: disponiveis,
                nomeCampo: "arbitrosSelecionados",
                placeholder: "Selecione os árbitros",
                obterValor: usuario => usuario.id,
                obterNome: usuario => `${usuario.nome} (${usuario.email})`,
                obterImagem: usuario =>
                    usuario.foto_perfil ||
                    usuario.path_ft_usuario ||
                    null
            });
        }

    } catch (erro) {
        console.error("Erro ao carregar usuários:", erro);

        exibirMensagem(
            mensagemAdicionar,
            erro.message || "Não foi possível carregar os usuários.",
            "erro"
        );
    }
}

// RENDERIZAR ÁRBITROS
function renderizarArbitros(arbitros) {
    if (!lista || !mensagem) return;

    lista.replaceChildren();
    lista.classList.add("cards-entidade");

    if (!arbitros.length) {
        exibirMensagem(mensagem, "Nenhum árbitro cadastrado até o momento.", "info");
        return;
    }

    mensagem.replaceChildren();
    mensagem.className = "";

    arbitros.forEach(arbitro => {
        const card = criarCardEntidade({
            foto: arbitro.foto_perfil ||
                  arbitro.path_ft_usuario ||
                  "/assets/img/no-prof-pic.png",
            nome: arbitro.nome || "Usuário sem nome",
            detalhes: [arbitro.email || "E-mail não informado"],
            url: null
        });

        if (window.usuarioVisitante !== true && window.podeGerenciarCompeticao === true) {
            const acoes = document.createElement("div");
            acoes.className = "card-entidade-acoes";

            const botaoRemover = document.createElement("button");
            botaoRemover.type = "button";
            botaoRemover.className = "custom-btn custom-btn-secondary";
            botaoRemover.textContent = "Remover";

            botaoRemover.addEventListener("click", async () => {
                if (!window.confirm(
                    `Deseja remover ${arbitro.nome} da competição?`
                )) return;

                botaoRemover.disabled = true;

                try {
                    await competicaoService.removerArbitro(
                        idCompeticao,
                        arbitro.id
                    );

                    await carregarArbitros();
                    await carregarUsuariosArbitro();

                } catch (erro) {
                    console.error("Erro ao remover árbitro:", erro);
                    window.alert(
                        erro.message || "Não foi possível remover o árbitro.",
                    "erro"
                    );
                    botaoRemover.disabled = false;
                }
            });

            acoes.appendChild(botaoRemover);
            card.appendChild(acoes);
        }

        lista.appendChild(card);
    });
}

// CARREGAR ÁRBITROS DA COMPETIÇÃO
export async function carregarArbitros() {
    if (!lista || !mensagem) return;

    lista.replaceChildren();
    exibirMensagem(mensagem, "Carregando árbitros...", "info");

    try {
        const resposta = await competicaoService.listarArbitros(idCompeticao);

        const arbitros = Array.isArray(resposta)
            ? resposta
            : resposta.arbitros || [];

        renderizarArbitros(arbitros);

    } catch (erro) {
        console.error("Erro ao carregar árbitros:", erro);

        exibirMensagem(
            mensagem,
            erro.message || "Não foi possível carregar os árbitros.",
            "erro"
        );
    }
}

// CONFIGURAR FORMULÁRIO DE ÁRBITROS
export function configurarFormularioArbitros() {
    if (!formulario || !botaoAdicionar || !containerArbitro) return;

    const podeGerenciar = window.usuarioVisitante !== true && window.podeGerenciarCompeticao === true;

    formulario.hidden = !podeGerenciar;
    if (!podeGerenciar) {
        formulario.style.setProperty("display", "none", "important");
    } else {
        formulario.style.removeProperty("display");
    }

    if (!podeGerenciar) return;

    formulario.addEventListener("submit", async evento => {
        evento.preventDefault();

        const selecionados = selectMultiploArbitros
            ?.obterSelecionados()
            .map(Number)
            .filter(id => Number.isInteger(id) && id > 0) || [];

        if (!selecionados.length) {
            exibirMensagem(
                mensagemAdicionar,
                "Selecione pelo menos um árbitro.",
                "erro"
            );
            return;
        }

        botaoAdicionar.disabled = true;
        exibirMensagem(
            mensagemAdicionar,
            "Adicionando árbitros...",
            "info"
        );

        const erros = [];
        let adicionados = 0;

        // ADICIONAR OS ÁRBITROS SELECIONADOS
        for (const usuarioId of selecionados) {
            try {
                await competicaoService.adicionarArbitro(
                    idCompeticao,
                    usuarioId
                );

                adicionados++;

            } catch (erro) {
                console.error(
                    `Erro ao adicionar usuário ${usuarioId}:`,
                    erro
                );

                erros.push(
                    `Usuário ${usuarioId}: ${erro.message || "Erro desconhecido."}`
                );
            }
        }

        try {
            await carregarArbitros();
            await carregarUsuariosArbitro();

            selectMultiploArbitros?.limpar();

            if (erros.length) {
                exibirMensagem(
                    mensagemAdicionar,
                    `${adicionados} árbitro(s) adicionado(s). ` +
                    `${erros.length} não puderam ser adicionados.`,
                    "info"
                );

                console.error("Falhas na inclusão de árbitros:", erros);
            } else {
                exibirMensagem(
                    mensagemAdicionar,
                    `${adicionados} árbitro(s) adicionado(s) com sucesso!`,
                    "sucesso"
                );
            }

        } catch (erro) {
            console.error("Erro ao atualizar árbitros:", erro);
            exibirMensagem(
                mensagemAdicionar,
                "Operação concluída, mas não foi possível atualizar a lista.",
                "erro"
            );
        } finally {
            botaoAdicionar.disabled = false;
        }
    });
}

// INICIALIZAR MÓDULO
export async function inicializarArbitros() {
    await carregarArbitros();

    if (window.podeGerenciarCompeticao === true) {
        configurarFormularioArbitros();
        await carregarUsuariosArbitro();
    } else if (formulario) {
        formulario.hidden = true;
        formulario.style.setProperty("display", "none", "important");
    }
}
