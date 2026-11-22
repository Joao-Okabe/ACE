import { competicaoService } from "../../../services/competicaoService.js";
import { idCompeticao } from "./competicao.js";
import { exibirMensagem, limparMensagem } from "../../../utils/mensagens.js";

let etapasAtuais = [];

// CONFIGURAR CADASTRO DE ETAPA
function configurarFormularioCadastroEtapa() {
    const botaoAbrir = document.getElementById("botaoAbrirModalCadastrarEtapa");
    const modal = document.getElementById("modalCadastrarEtapa");
    const formulario = document.getElementById("formCadastrarEtapa");
    const mensagem = document.getElementById("mensagemCadastrarEtapa");
    const botaoSalvar = document.getElementById("botaoCadastrarEtapa");

    if (!botaoAbrir || !modal || !formulario) return;

    const podeGerenciar =
        window.usuarioVisitante !== true &&
        window.podeGerenciarCompeticao === true;
    botaoAbrir.hidden = !podeGerenciar;
    if (!podeGerenciar) {
        botaoAbrir.style.setProperty("display", "none", "important");
    } else {
        botaoAbrir.style.removeProperty("display");
    }

    if (!botaoAbrir.dataset.configurado) {
        botaoAbrir.dataset.configurado = "true";

        botaoAbrir.addEventListener("click", () => {
            formulario.reset();
            limparMensagem(mensagem);

            const proximaOrdem = etapasAtuais.length
                ? Math.max(...etapasAtuais.map(e => Number(e.ordem) || 0)) + 1
                : 1;

            document.getElementById("ordemEtapaCadastro").value = proximaOrdem;
            modal.abrir();
        });
    }

    if (formulario.dataset.configurado) return;
    formulario.dataset.configurado = "true";

    formulario.addEventListener("submit", async evento => {
        evento.preventDefault();

        const tipo = Number(document.getElementById("tipoEtapaCadastro").value);
        const nome = document.getElementById("nomeEtapaCadastro").value.trim();
        const ordem = Number(document.getElementById("ordemEtapaCadastro").value);
        const descricao = document.getElementById("descricaoEtapaCadastro").value.trim();

        if (
            !Number.isInteger(tipo) || tipo <= 0 ||
            !nome ||
            !Number.isInteger(ordem) || ordem <= 0
        ) {
            exibirMensagem(mensagem, "Preencha corretamente todos os campos obrigatórios.", "erro");
            return;
        }

        const dados = {
            nm_etapa: nome,
            cd_tipo_etapa: tipo,
            ordem,
            descricao: descricao || null
        };

        try {
            botaoSalvar.disabled = true;
            exibirMensagem(mensagem, "Cadastrando etapa...", "info");

            await competicaoService.criarEtapa(idCompeticao, dados);

            formulario.reset();
            modal.fechar();

            await carregarEtapas();
            exibirMensagem(
                document.getElementById("mensagemEtapas"),
                "Etapa cadastrada com sucesso.",
                "sucesso"
            );

        } catch (erro) {
            console.error("Erro ao cadastrar etapa:", erro);
            exibirMensagem(mensagem, erro.message || "Não foi possível cadastrar a etapa.", "erro");
        } finally {
            botaoSalvar.disabled = false;
        }
    });
}

// ABRIR MODAL DE EDIÇÃO DE ETAPA
function abrirModalEdicaoEtapa(etapa) {
    document.getElementById("idEtapaEditar").value = etapa.cd_etapa_competicao;
    document.getElementById("tipoEtapaEditar").value = etapa.cd_tipo_etapa;
    document.getElementById("nomeEtapaEditar").value = etapa.nm_etapa || "";
    document.getElementById("ordemEtapaEditar").value = etapa.ordem || "";
    document.getElementById("descricaoEtapaEditar").value = etapa.descricao || "";
    limparMensagem(document.getElementById("mensagemEditarEtapa"));
    document.getElementById("modalEditarEtapa").abrir();
}

// CONFIGURAR FORMULÁRIO DE EDIÇÃO
export function configurarFormularioEdicaoEtapa() {
    const formulario = document.getElementById("formEditarEtapa");

    if (!formulario || formulario.dataset.configurado) return;
    formulario.dataset.configurado = "true";

    formulario.addEventListener("submit", async evento => {
        evento.preventDefault();

        const idEtapa = Number(document.getElementById("idEtapaEditar").value);
        const cdTipoEtapa = Number(document.getElementById("tipoEtapaEditar").value);

        const dados = {
            nm_etapa: document.getElementById("nomeEtapaEditar").value.trim(),
            cd_tipo_etapa: cdTipoEtapa,
            ordem: Number(document.getElementById("ordemEtapaEditar").value),
            descricao: document.getElementById("descricaoEtapaEditar").value.trim() || null
        };

        const mensagem = document.getElementById("mensagemEditarEtapa");
        const botao = document.getElementById("botaoSalvarEtapa");

        if (
            !Number.isInteger(idEtapa) || idEtapa <= 0 ||
            !Number.isInteger(cdTipoEtapa) || cdTipoEtapa <= 0 ||
            !dados.nm_etapa ||
            !Number.isInteger(dados.ordem) || dados.ordem <= 0
        ) {
            exibirMensagem(mensagem, "Preencha corretamente todos os campos obrigatórios.", "erro");
            return;
        }

        botao.disabled = true;
        exibirMensagem(mensagem, "Salvando alterações...", "info");

        try {
            await competicaoService.atualizarEtapa(idCompeticao, idEtapa, dados);

            document.getElementById("modalEditarEtapa").fechar();

            await carregarEtapas();
            exibirMensagem(
                document.getElementById("mensagemEtapas"),
                "Etapa atualizada com sucesso.",
                "sucesso"
            );

        } catch (erro) {
            console.error("Erro ao atualizar etapa:", erro);
            exibirMensagem(mensagem, erro.message || "Não foi possível atualizar a etapa.", "erro");
        } finally {
            botao.disabled = false;
        }
    });
}

// CARREGAR ETAPAS DA COMPETIÇÃO
export async function carregarEtapas() {
    const lista = document.getElementById("listaEtapas");
    const mensagem = document.getElementById("mensagemEtapas");

    if (!lista || !mensagem) return;

    configurarFormularioCadastroEtapa();

    lista.replaceChildren();
    lista.classList.add("etapas-grid");
    exibirMensagem(mensagem, "Carregando etapas...", "info");

    try {
        const resposta = await competicaoService.listarEtapas(idCompeticao);

        const etapas = Array.isArray(resposta)
            ? resposta
            : resposta?.etapas || resposta?.dados?.etapas || [];

        etapasAtuais = etapas;

        if (!etapas.length) {
            exibirMensagem(mensagem, "Esta competição ainda não possui etapas cadastradas.", "info");
            return;
        }

        limparMensagem(mensagem);

        etapas.forEach(etapa => {
            const card = document.createElement("div");
            card.className = "etapa-card";

            const cabecalho = document.createElement("div");
            cabecalho.className = "etapa-card-cabecalho";

            const nome = document.createElement("h4");
            nome.className = "etapa-card-nome";
            nome.textContent = etapa.nm_etapa || "Etapa sem nome";

            const ordem = document.createElement("span");
            ordem.className = "etapa-card-ordem";
            ordem.textContent = `Etapa ${etapa.ordem}`;

            cabecalho.append(nome, ordem);

            const tipo = document.createElement("span");
            tipo.className = "etapa-card-tipo";
            tipo.textContent = etapa.nm_tipo_etapa || "Tipo não informado";

            const descricao = document.createElement("p");
            descricao.className = "etapa-card-descricao";
            descricao.textContent = etapa.descricao || "Sem descrição.";

            card.append(cabecalho, tipo, descricao);

            if (window.usuarioVisitante !== true && window.podeGerenciarCompeticao === true) {
                const acoes = document.createElement("div");
                acoes.className = "etapa-card-acoes";

                const botaoEditar = document.createElement("button");
                botaoEditar.type = "button";
                botaoEditar.className = "custom-btn btn-laranja";
                botaoEditar.textContent = "Editar";

                botaoEditar.addEventListener("click", () => {
                    abrirModalEdicaoEtapa(etapa);
                });

                acoes.appendChild(botaoEditar);
                card.appendChild(acoes);
            }

            lista.appendChild(card);
        });

    } catch (erro) {
        console.error("Erro ao carregar etapas:", erro);
        exibirMensagem(mensagem, erro.message || "Não foi possível carregar as etapas.", "erro");
    }
}
