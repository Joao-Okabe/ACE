
// IMPORTAÇÕES
import { apiFetch } from "../../../api/api.js";
import { obterIdTime } from "./utilitarios.js";

// CARREGAR TÉCNICOS DO TIME
export async function carregarTecnicosTime(idTime = obterIdTime()) {
    const lista = document.getElementById("listaTecnico");

    if (!lista) {
        return;
    }

    try {
        const tecnicos = await apiFetch(`/times/${idTime}/tecnicos`);

        lista.replaceChildren();

        if (!tecnicos || !tecnicos.length) {
            lista.innerHTML = '<p class="text-muted">Nenhum técnico cadastrado.</p>';
            return;
        }

        tecnicos.forEach(tecnico => {
            lista.appendChild(criarCardTecnico(tecnico));
        });
    } catch (erro) {
        console.error("Erro ao carregar técnicos:", erro);
    }
}

// CRIAR CARD DO TÉCNICO
export function criarCardTecnico(tecnico) {
    const card = document.createElement("div");
    card.className = "time-inscrito-card";

    const imagem = document.createElement("img");
    imagem.className = "time-inscrito-brasao";
    imagem.src = tecnico.foto_perfil || tecnico.path_ft_usuario || "/assets/img/no-prof-pic.png";
    imagem.alt = `Foto de ${tecnico.nm_usuario}`;

    const nome = document.createElement("span");
    nome.className = "time-inscrito-nome";
    nome.textContent = tecnico.nm_usuario || "Técnico sem nome";

    card.append(imagem, nome);

    return card;
}

// CONFIGURAR MODAL DE TÉCNICO
export function configurarModalTecnico() {
    const modal = document.querySelector('app-modal[titulo="Adicionar técnico"]');
    const botaoAbrir = document.getElementById("abrirModalTecnico");
    const formulario = document.getElementById("formAdicionarTecnico");
    const selectTecnico = document.getElementById("selectTecnico");

    if (!modal || !botaoAbrir || !formulario || !selectTecnico) {
        return;
    }

    botaoAbrir.addEventListener("click", () => {
        modal.setAttribute("aberto", "");
        carregarOpcoesTecnicos();
    });

    async function carregarOpcoesTecnicos() {
        try {
            const [disponiveis, vinculados] = await Promise.all([
                apiFetch(`/times/${obterIdTime()}/tecnicos/disponiveis`),
                apiFetch(`/times/${obterIdTime()}/tecnicos`)
            ]);

            const idsVinculados = (vinculados || []).map(tecnico =>
                String(tecnico.cd_usuario)
            );

            selectTecnico.replaceChildren();

            const opcaoInicial = document.createElement("option");
            opcaoInicial.value = "";
            opcaoInicial.textContent = "Selecione um técnico";
            selectTecnico.appendChild(opcaoInicial);

            (disponiveis || [])
                .filter(tecnico =>
                    !idsVinculados.includes(String(tecnico.cd_usuario))
                )
                .forEach(tecnico => {
                    const opcao = document.createElement("option");
                    opcao.value = tecnico.cd_usuario;
                    opcao.textContent = tecnico.nm_usuario;
                    selectTecnico.appendChild(opcao);
                });
        } catch (erro) {
            console.error("Erro ao carregar técnicos disponíveis:", erro);
        }
    }

    formulario.addEventListener("submit", async evento => {
        evento.preventDefault();

        try {
            const dados = {
                cd_usuario: Number(selectTecnico.value)
            };

            await apiFetch(`/times/${obterIdTime()}/tecnicos`, {
                method: "POST",
                body: JSON.stringify(dados)
            });

            modal.removeAttribute("aberto");
            formulario.reset();

            await carregarTecnicosTime();
        } catch (erro) {
            console.error("Erro ao adicionar técnico:", erro);
        }
    });
}
