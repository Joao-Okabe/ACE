import { apiFetch } from "../../../api/api.js";
import { estadoTime, obterIdTime } from "./utilitarios.js";
import { criarCardEntidade } from "../../../components/cardEntidade.js";

// CARREGAR RESPONSÁVEIS DO TIME
export async function carregarResponsaveisTime(idTime = obterIdTime()) {
    const lista = document.getElementById("listaResponsaveis");
    const mensagem = document.getElementById("mensagensResponsaveis");

    if (!lista) {
        return;
    }
    lista.classList.add("cards-entidade");

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
}

// CONFIGURAR MODAL DE RESPONSÁVEL
export function configurarModalResponsavel() {
    if (!estadoTime.podeGerenciar) return;
    const modal = document.querySelector(
        'app-modal[titulo="Adicionar responsável"]'
    );

    const botaoAbrir = document.getElementById("abrirModalResponsavel");
    const formulario = document.getElementById("formAdicionarResponsavel");
    const selectResponsavel = document.getElementById("selectResponsavel");

    if (!modal || !botaoAbrir || !formulario || !selectResponsavel) {
        return;
    }

    botaoAbrir.addEventListener("click", async () => {
        modal.abrir();
        await carregarOpcoesResponsaveis();
    });

    async function carregarOpcoesResponsaveis() {
        try {
            const [disponiveis, vinculados] = await Promise.all([
                apiFetch(`/times/${obterIdTime()}/responsaveis/disponiveis`),
                apiFetch(`/times/${obterIdTime()}/responsaveis`)
            ]);

            const idsVinculados = (vinculados || []).map(responsavel =>
                String(responsavel.cd_responsavel)
            );

            selectResponsavel.replaceChildren();

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
        }
    }

    formulario.addEventListener("submit", async evento => {
        evento.preventDefault();

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
        }
    });
}
