import { apiFetch } from "../../../api/api.js";
import { estadoTime, obterIdTime } from "./utilitarios.js";
<<<<<<< HEAD
=======
import { criarSelectUnico } from "../../../components/selectUnico.js";
>>>>>>> origin/api_v1

// INICIALIZAR ESCALAÇÃO
export function inicializarEscalacao() {
    const lista = document.getElementById("listaFuncoesEscalacao");
<<<<<<< HEAD
=======

    if (!estadoTime.podeGerenciar) {
        if (lista) lista.replaceChildren();
        return;
    }
>>>>>>> origin/api_v1
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
<<<<<<< HEAD
        label.textContent = funcao.nm_funcao_integrante;
=======
        label.textContent = funcao.nm_funcao;
>>>>>>> origin/api_v1

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
<<<<<<< HEAD
            opcao.dataset.foto = integrante.foto_perfil || integrante.path_ft_usuario || "";
=======
            opcao.dataset.foto =
                integrante.foto_perfil ||
                integrante.path_ft_usuario ||
                "/assets/img/no-prof-pic.png";

            // RECUPERAR TITULAR SALVO NO BANCO
            const ehTitular = ["true", "1", "t"].includes(
                String(integrante.titular).toLowerCase()
            );

            opcao.selected = ehTitular;
>>>>>>> origin/api_v1

            select.appendChild(opcao);
        });

<<<<<<< HEAD
=======
        // ATUALIZAR JOGADOR NA QUADRA
>>>>>>> origin/api_v1
        select.addEventListener("change", () => {
            atualizarJogadorNaQuadra(select, funcao, indice);
        });

        grupo.append(label, select);
<<<<<<< HEAD
        lista.appendChild(grupo);
=======

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
>>>>>>> origin/api_v1
    });
}

// ATUALIZAR JOGADOR NA QUADRA
export function atualizarJogadorNaQuadra(select, funcao, indice) {
    const campo = document.getElementById("campoEscalacao");

    if (!campo) {
        return;
    }

<<<<<<< HEAD
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
=======
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
>>>>>>> origin/api_v1
        campo.appendChild(jogador);
    }

    const opcaoSelecionada = select.selectedOptions[0];

    if (!select.value) {
        jogador.style.display = "none";
        return;
    }

    jogador.replaceChildren();

    const imagem = document.createElement("img");
<<<<<<< HEAD
    imagem.src = opcaoSelecionada.dataset.foto || "/assets/img/default-user.png";
=======
    imagem.src = opcaoSelecionada.dataset.foto || "/assets/img/no-prof-pic.png";

    imagem.onerror = () => {
        imagem.onerror = null;
        imagem.src = "/assets/img/no-prof-pic.png";
    };
>>>>>>> origin/api_v1
    imagem.alt = "";

    const nome = document.createElement("span");
    nome.textContent = opcaoSelecionada.textContent;

    const posicao = document.createElement("small");
<<<<<<< HEAD
    posicao.textContent = funcao.nm_funcao_integrante;
=======
    posicao.textContent = funcao.nm_funcao;
>>>>>>> origin/api_v1

    jogador.append(imagem, nome, posicao);
    jogador.style.display = "";
}

// CONFIGURAR FORMULÁRIO DE ESCALAÇÃO
export function configurarFormularioEscalacao() {
<<<<<<< HEAD
=======
    if (!estadoTime.podeGerenciar) return;
>>>>>>> origin/api_v1
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
<<<<<<< HEAD
        ).filter(select => select.value).map(select => ({
            cd_usuario: Number(select.value),
            cd_funcao_integrante: Number(select.dataset.funcao)
        }));
=======
        )
        .filter(select => select.value)
        .map(select => Number(select.value));;
>>>>>>> origin/api_v1

        try {
            if (botao) {
                botao.disabled = true;
            }

            await apiFetch(`/times/${idTime}/escalacao`, {
                method: "PUT",
<<<<<<< HEAD
                body: JSON.stringify({ titulares })
=======
                body: { titulares }
>>>>>>> origin/api_v1
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
