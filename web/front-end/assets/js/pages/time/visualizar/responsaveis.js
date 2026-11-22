import { apiFetch } from "../../../api/api.js";
import { estadoTime, obterIdTime } from "./utilitarios.js";
<<<<<<< HEAD
=======
import { criarCardEntidade } from "../../../components/cardEntidade.js";
>>>>>>> origin/api_v1

// CARREGAR RESPONSÁVEIS DO TIME
export async function carregarResponsaveisTime(idTime = obterIdTime()) {
    const lista = document.getElementById("listaResponsaveis");
    const mensagem = document.getElementById("mensagensResponsaveis");

    if (!lista) {
        return;
    }
<<<<<<< HEAD
=======
    lista.classList.add("cards-entidade");
>>>>>>> origin/api_v1

    try {
        const responsaveis = await apiFetch(`/times/${idTime}/responsaveis`);

        estadoTime.responsaveis = responsaveis || [];

        lista.replaceChildren();

        if (!estadoTime.responsaveis.length) {
            lista.innerHTML = '<p class="text-muted">Nenhum responsável cadastrado.</p>';
            return;
        }

        estadoTime.responsaveis.forEach(responsavel => {
            lista.appendChild(criarCardResponsavel(responsavel));
        });

        if (mensagem) {
            mensagem.textContent = "";
        }
    } catch (erro) {
        console.error("Erro ao carregar responsáveis:", erro);

        if (mensagem) {
            mensagem.textContent = "Não foi possível carregar os responsáveis.";
        }
    }
}

<<<<<<< HEAD
// CRIAR CARD DO RESPONSÁVEL
export function criarCardResponsavel(responsavel) {
    const card = document.createElement("div");
    card.className = "time-inscrito-card";

    const imagem = document.createElement("img");
    imagem.className = "time-inscrito-brasao";
    imagem.src = responsavel.foto_perfil || responsavel.path_ft_usuario || "/assets/img/no-prof-pic.png";
    imagem.alt = `Foto de ${responsavel.nm_usuario}`;

    const nome = document.createElement("span");
    nome.className = "time-inscrito-nome";
    nome.textContent = responsavel.nm_usuario || "Responsável sem nome";

    card.append(imagem, nome);

    return card;
=======

/*CRIAR CARD DO RESPONSÁVEL*/
export function criarCardResponsavel(responsavel) {
    return criarCardEntidade({
        foto: responsavel.foto_perfil ||
              responsavel.path_ft_usuario ||
              "/assets/img/no-prof-pic.png",

        nome: responsavel.nm_usuario || "Responsável sem nome",

        detalhes: ["Responsável"],

        url: null,
        editar: null
    });
>>>>>>> origin/api_v1
}

// CONFIGURAR MODAL DE RESPONSÁVEL
export function configurarModalResponsavel() {
<<<<<<< HEAD
    const modal = document.querySelector('app-modal[titulo="Adicionar responsável"]');
=======
    if (!estadoTime.podeGerenciar) return;
    const modal = document.querySelector(
        'app-modal[titulo="Adicionar responsável"]'
    );

>>>>>>> origin/api_v1
    const botaoAbrir = document.getElementById("abrirModalResponsavel");
    const formulario = document.getElementById("formAdicionarResponsavel");
    const selectResponsavel = document.getElementById("selectResponsavel");

    if (!modal || !botaoAbrir || !formulario || !selectResponsavel) {
        return;
    }

<<<<<<< HEAD
    botaoAbrir.addEventListener("click", () => {
        modal.setAttribute("aberto", "");
        carregarOpcoesResponsaveis();
=======
    botaoAbrir.addEventListener("click", async () => {
        modal.abrir();
        await carregarOpcoesResponsaveis();
>>>>>>> origin/api_v1
    });

    async function carregarOpcoesResponsaveis() {
        try {
            const [disponiveis, vinculados] = await Promise.all([
                apiFetch(`/times/${obterIdTime()}/responsaveis/disponiveis`),
                apiFetch(`/times/${obterIdTime()}/responsaveis`)
            ]);

            const idsVinculados = (vinculados || []).map(responsavel =>
<<<<<<< HEAD
                String(responsavel.cd_usuario)
=======
                String(responsavel.cd_responsavel)
>>>>>>> origin/api_v1
            );

            selectResponsavel.replaceChildren();

<<<<<<< HEAD
            const opcaoInicial = document.createElement("option");
            opcaoInicial.value = "";
            opcaoInicial.textContent = "Selecione um responsável";
            selectResponsavel.appendChild(opcaoInicial);

            (disponiveis || [])
                .filter(responsavel =>
                    !idsVinculados.includes(String(responsavel.cd_usuario))
                )
                .forEach(responsavel => {
                    const opcao = document.createElement("option");
                    opcao.value = responsavel.cd_usuario;
                    opcao.textContent = responsavel.nm_usuario;
                    selectResponsavel.appendChild(opcao);
                });
        } catch (erro) {
            console.error("Erro ao carregar responsáveis disponíveis:", erro);
=======
            const inicial = document.createElement("option");
            inicial.value = "";
            inicial.textContent = "Selecione um responsável";

            selectResponsavel.appendChild(inicial);

            (disponiveis || [])
                .filter(responsavel =>
                    !idsVinculados.includes(
                        String(responsavel.cd_responsavel)
                    )
                )
                .forEach(responsavel => {
                    const opcao = document.createElement("option");

                    opcao.value = responsavel.cd_responsavel;
                    opcao.textContent = responsavel.nm_usuario;

                    selectResponsavel.appendChild(opcao);
                });

        } catch (erro) {
            console.error("Erro ao carregar responsáveis:", erro);
            alert("Não foi possível carregar os responsáveis.");
>>>>>>> origin/api_v1
        }
    }

    formulario.addEventListener("submit", async evento => {
        evento.preventDefault();

<<<<<<< HEAD
        try {
            const dados = {
                cd_usuario: Number(selectResponsavel.value)
            };

            await apiFetch(`/times/${obterIdTime()}/responsaveis`, {
                method: "POST",
                body: JSON.stringify(dados)
            });

            modal.removeAttribute("aberto");
            formulario.reset();

            await carregarResponsaveisTime();
        } catch (erro) {
            console.error("Erro ao adicionar responsável:", erro);
=======
        const idTime = obterIdTime();
        const idResponsavel = Number(selectResponsavel.value);
        const botao = formulario.querySelector('[type="submit"]');

        if (!idResponsavel) {
            alert("Selecione um responsável.");
            return;
        }

        try {
            botao.disabled = true;

            await apiFetch(`/times/${idTime}/responsaveis`, {
                method: "POST",
                body: {
                    cd_responsavel: idResponsavel
                }
            });

            formulario.reset();
            modal.fechar();

            await carregarResponsaveisTime(idTime);

            alert("Responsável vinculado com sucesso!");

        } catch (erro) {
            console.error("Erro ao adicionar responsável:", erro);
            alert(erro.message || "Não foi possível adicionar o responsável.");

        } finally {
            botao.disabled = false;
>>>>>>> origin/api_v1
        }
    });
}
