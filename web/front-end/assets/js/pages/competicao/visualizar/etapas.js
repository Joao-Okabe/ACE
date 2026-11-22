import { competicaoService } from "../../../services/competicaoService.js";
import { idCompeticao } from "./competicao.js";

// ABRIR MODAL DE EDIÇÃO DE ETAPA
function abrirModalEdicaoEtapa(etapa) {
    document.getElementById("idEtapaEditar").value =
        etapa.cd_etapa_competicao;

    document.getElementById("tipoEtapaEditar").value =
        etapa.cd_tipo_etapa;

    document.getElementById("nomeEtapaEditar").value =
        etapa.nm_etapa || "";

    document.getElementById("ordemEtapaEditar").value =
        etapa.ordem || "";

    document.getElementById("descricaoEtapaEditar").value =
        etapa.descricao || "";

    document.getElementById("mensagemEditarEtapa").textContent = "";

    document.getElementById("modalEditarEtapa").abrir();
}

// CONFIGURAR FORMULÁRIO DE EDIÇÃO
export function configurarFormularioEdicaoEtapa() {
    const formulario = document.getElementById("formEditarEtapa");

    if (!formulario) return;

    formulario.addEventListener("submit", async (evento) => {
        evento.preventDefault();

        const idEtapa = Number(
            document.getElementById("idEtapaEditar").value
        );

        const cdTipoEtapa = Number(
            document.getElementById("tipoEtapaEditar").value
        );

        const dados = {
            nm_etapa: document.getElementById("nomeEtapaEditar").value.trim(),
            cd_tipo_etapa: cdTipoEtapa,
            ordem: Number(document.getElementById("ordemEtapaEditar").value),
            descricao: document.getElementById("descricaoEtapaEditar").value.trim() || null
        };

        const mensagem = document.getElementById("mensagemEditarEtapa");
        const botao = document.getElementById("botaoSalvarEtapa");

        if (
            !Number.isInteger(idEtapa) ||
            idEtapa <= 0 ||
            !Number.isInteger(cdTipoEtapa) ||
            cdTipoEtapa <= 0 ||
            !dados.nm_etapa ||
            !Number.isInteger(dados.ordem) ||
            dados.ordem <= 0
        ) {
            mensagem.textContent = "Preencha corretamente todos os campos obrigatórios.";
            return;
        }

        botao.disabled = true;
        mensagem.textContent = "Salvando alterações...";

        try {
            await competicaoService.atualizarEtapa(
                idCompeticao,
                idEtapa,
                dados
            );

            mensagem.textContent = "Etapa atualizada com sucesso!";

            document.getElementById("modalEditarEtapa").fechar();

            await carregarEtapas();

        } catch (erro) {
            console.error("Erro ao atualizar etapa:", erro);
            mensagem.textContent =
                erro.message || "Não foi possível atualizar a etapa.";
        } finally {
            botao.disabled = false;
        }
    });
}

// CARREGAR ETAPAS DA COMPETIÇÃO
export async function carregarEtapas() {
    const lista = document.getElementById("listaEtapas");
    const mensagem = document.getElementById("mensagemEtapas");

    lista.replaceChildren();
    lista.classList.add("etapas-grid");
    mensagem.textContent = "Carregando etapas...";

    try {
        const resposta = await competicaoService.listarEtapas(idCompeticao);

        const etapas = Array.isArray(resposta)
            ? resposta
            : resposta.etapas || [];

        if (!etapas.length) {
            mensagem.textContent = "Esta competição ainda não possui etapas cadastradas.";
            return;
        }

        mensagem.textContent = "";

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

            if (window.podeGerenciarCompeticao === true) {
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
        mensagem.textContent = erro.message || "Não foi possível carregar as etapas.";
    }
}
