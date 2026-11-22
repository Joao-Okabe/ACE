import { apiFetch } from "../../../api/api.js";
import { estadoTime, obterIdTime } from "./utilitarios.js";
import { configurarImagem } from "../../../utils/imagens.js";

// INICIALIZAR ESCALAÇÃO
export function inicializarEscalacao() {
    const lista = document.getElementById("listaFuncoesEscalacao");
    const campo = document.getElementById("campoEscalacao");

    if (!lista || !campo) {
        return;
    }

    lista.innerHTML = "";

    campo.querySelectorAll(".jogador").forEach(jogador => {
        jogador.style.display = "none";
    });

    estadoTime.funcoes.forEach((funcao, indice) => {
        const grupo = document.createElement("div");
        grupo.className = "mb-3";

        const label = document.createElement("label");
        label.className = "form-label";
        label.textContent = funcao.nm_funcao_integrante;

        const select = document.createElement("select");
        select.className = "form-select";
        select.dataset.funcao = funcao.cd_funcao_integrante;
        select.dataset.indice = indice;

        const opcaoInicial = document.createElement("option");
        opcaoInicial.value = "";
        opcaoInicial.textContent = "Selecione um integrante";

        select.appendChild(opcaoInicial);

        estadoTime.integrantes.forEach(integrante => {
            if (
                String(integrante.cd_funcao_integrante) !==
                String(funcao.cd_funcao_integrante)
            ) {
                return;
            }

            const opcao = document.createElement("option");
            opcao.value = integrante.cd_usuario;
            opcao.textContent = integrante.nm_usuario;
            opcao.dataset.foto = integrante.foto_perfil || integrante.path_ft_usuario || "";

            select.appendChild(opcao);
        });

        select.addEventListener("change", () => {
            atualizarJogadorNaQuadra(select, funcao, indice);
        });

        grupo.append(label, select);
        lista.appendChild(grupo);
    });
}

// ATUALIZAR JOGADOR NA QUADRA
export function atualizarJogadorNaQuadra(select, funcao, indice) {
    const campo = document.getElementById("campoEscalacao");

    if (!campo) {
        return;
    }

    const chave = String(funcao.nm_funcao_integrante)
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/[^a-z0-9]/g, "");

    let jogador = document.getElementById(`jogador-${chave}`);

    if (!jogador) {
        jogador = document.createElement("div");
        jogador.id = `jogador-${chave}`;
        jogador.className = "jogador";
        campo.appendChild(jogador);
    }

    const opcaoSelecionada = select.selectedOptions[0];

    if (!select.value) {
        jogador.style.display = "none";
        return;
    }

    jogador.replaceChildren();

    const imagem = document.createElement("img");
    imagem.alt = "";
    configurarImagem(
        imagem,
        opcaoSelecionada.dataset.foto,
        "/assets/img/no-prof-pic.png"
    );

    const nome = document.createElement("span");
    nome.textContent = opcaoSelecionada.textContent;

    const posicao = document.createElement("small");
    posicao.textContent = funcao.nm_funcao_integrante;

    jogador.append(imagem, nome, posicao);
    jogador.style.display = "";
}

// CONFIGURAR FORMULÁRIO DE ESCALAÇÃO
export function configurarFormularioEscalacao() {
    const formulario = document.getElementById("formEscalacao");

    if (!formulario) {
        return;
    }

    formulario.addEventListener("submit", async evento => {
        evento.preventDefault();

        const idTime = obterIdTime();
        const botao = formulario.querySelector('[type="submit"]');
        const mensagem = document.getElementById("mensagemEscalacao");

        const titulares = Array.from(
            formulario.querySelectorAll("select[data-funcao]")
        ).filter(select => select.value).map(select => ({
            cd_usuario: Number(select.value),
            cd_funcao_integrante: Number(select.dataset.funcao)
        }));

        try {
            if (botao) {
                botao.disabled = true;
            }

            await apiFetch(`/times/${idTime}/escalacao`, {
                method: "PUT",
                body: JSON.stringify({ titulares })
            });

            if (mensagem) {
                mensagem.textContent = "Escalação salva com sucesso!";
                mensagem.className = "alert alert-success";
            }
        } catch (erro) {
            console.error("Erro ao salvar escalação:", erro);

            if (mensagem) {
                mensagem.textContent = "Não foi possível salvar a escalação.";
                mensagem.className = "alert alert-danger";
            }
        } finally {
            if (botao) {
                botao.disabled = false;
            }
        }
    });
}
