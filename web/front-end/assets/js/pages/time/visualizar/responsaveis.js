import { apiFetch } from "../../../api/api.js";
import { estadoTime, obterIdTime } from "./utilitarios.js";

// CARREGAR RESPONSÁVEIS DO TIME
export async function carregarResponsaveisTime(idTime = obterIdTime()) {
    const lista = document.getElementById("listaResponsaveis");
    const mensagem = document.getElementById("mensagensResponsaveis");

    if (!lista) {
        return;
    }

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
}

// CONFIGURAR MODAL DE RESPONSÁVEL
export function configurarModalResponsavel() {
    const modal = document.querySelector('app-modal[titulo="Adicionar responsável"]');
    const botaoAbrir = document.getElementById("abrirModalResponsavel");
    const formulario = document.getElementById("formAdicionarResponsavel");
    const selectResponsavel = document.getElementById("selectResponsavel");

    if (!modal || !botaoAbrir || !formulario || !selectResponsavel) {
        return;
    }

    botaoAbrir.addEventListener("click", () => {
        modal.setAttribute("aberto", "");
        carregarOpcoesResponsaveis();
    });

    async function carregarOpcoesResponsaveis() {
        try {
            const [disponiveis, vinculados] = await Promise.all([
                apiFetch(`/times/${obterIdTime()}/responsaveis/disponiveis`),
                apiFetch(`/times/${obterIdTime()}/responsaveis`)
            ]);

            const idsVinculados = (vinculados || []).map(responsavel =>
                String(responsavel.cd_usuario)
            );

            selectResponsavel.replaceChildren();

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
        }
    }

    formulario.addEventListener("submit", async evento => {
        evento.preventDefault();

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
        }
    });
}
