import { apiFetch } from "../../../api/api.js";
import { estadoTime, obterIdTime } from "./utilitarios.js";
import { criarSelectUnico } from "../../../components/selectUnico.js";

// INICIALIZAR ESCALAÇÃO
export function inicializarEscalacao() {
    const lista = document.getElementById("listaFuncoesEscalacao");

    if (!estadoTime.podeGerenciar) {
        if (lista) lista.replaceChildren();
        return;
    }
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
        label.textContent = funcao.nm_funcao;

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
            opcao.dataset.foto =
                integrante.foto_perfil ||
                integrante.path_ft_usuario ||
                "/assets/img/no-prof-pic.png";

            // RECUPERAR TITULAR SALVO NO BANCO
            const ehTitular = ["true", "1", "t"].includes(
                String(integrante.titular).toLowerCase()
            );

            opcao.selected = ehTitular;

            select.appendChild(opcao);
        });

        // ATUALIZAR JOGADOR NA QUADRA
        select.addEventListener("change", () => {
            atualizarJogadorNaQuadra(select, funcao, indice);
        });

        grupo.append(label, select);

        // APLICAR COMPONENTE SELECT ÚNICO
        criarSelectUnico({
            select,
            placeholder: "Selecione um integrante",
            ariaLabel: `Selecionar integrante para ${funcao.nm_funcao}`,
            obterImagem: opcao => opcao.dataset.foto
        });

        lista.appendChild(grupo);

        // RECUPERAR JOGADOR NO CAMPO
        if (select.value) {
            atualizarJogadorNaQuadra(select, funcao, indice);
        }
    });
}

// ATUALIZAR JOGADOR NA QUADRA
export function atualizarJogadorNaQuadra(select, funcao, indice) {
    const campo = document.getElementById("campoEscalacao");

    if (!campo) {
        return;
    }

    // POSIÇÕES NO CAMPO
    const posicoes = {
        25: "goleiro",
        26: "fixo",
        27: "ala-esquerda",
        28: "ala-direita",
        29: "pivo"
    };

    const classePosicao = posicoes[Number(funcao.cd_funcao_integrante)];

    if (!classePosicao) {
        return;
    }

    let jogador = document.getElementById(`jogador-${classePosicao}`);

    if (!jogador) {
        jogador = document.createElement("div");
        jogador.id = `jogador-${classePosicao}`;
        jogador.className = `jogador ${classePosicao}`;
        campo.appendChild(jogador);
    }

    const opcaoSelecionada = select.selectedOptions[0];

    if (!select.value) {
        jogador.style.display = "none";
        return;
    }

    jogador.replaceChildren();

    const imagem = document.createElement("img");
    imagem.src = opcaoSelecionada.dataset.foto || "/assets/img/no-prof-pic.png";

    imagem.onerror = () => {
        imagem.onerror = null;
        imagem.src = "/assets/img/no-prof-pic.png";
    };
    imagem.alt = "";

    const nome = document.createElement("span");
    nome.textContent = opcaoSelecionada.textContent;

    const posicao = document.createElement("small");
    posicao.textContent = funcao.nm_funcao;

    jogador.append(imagem, nome, posicao);
    jogador.style.display = "";
}

// CONFIGURAR FORMULÁRIO DE ESCALAÇÃO
export function configurarFormularioEscalacao() {
    if (!estadoTime.podeGerenciar) return;
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
        )
        .filter(select => select.value)
        .map(select => Number(select.value));;

        try {
            if (botao) {
                botao.disabled = true;
            }

            await apiFetch(`/times/${idTime}/escalacao`, {
                method: "PUT",
                body: { titulares }
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
