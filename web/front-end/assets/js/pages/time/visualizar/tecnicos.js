
import { apiFetch } from "../../../api/api.js";
import { estadoTime, obterIdTime } from "./utilitarios.js";
import { criarCardEntidade } from "../../../components/cardEntidade.js";

// NORMALIZAR RESPOSTA DA API
function obterLista(resposta) {
    if (Array.isArray(resposta)) return resposta;
    if (Array.isArray(resposta?.dados)) return resposta.dados;
    return [];
}

// CARREGAR TÉCNICOS DO TIME
export async function carregarTecnicosTime(idTime = obterIdTime()) {
    const lista = document.getElementById("listaTecnico");

    if (!lista) return;

    lista.classList.add("cards-entidade");

    try {
        const resposta = await apiFetch(`/times/${idTime}/tecnicos`);
        const tecnicos = obterLista(resposta);

        lista.replaceChildren();

        if (!tecnicos.length) {
            lista.innerHTML =
                '<p class="text-muted">Nenhum técnico cadastrado.</p>';
            return;
        }

        tecnicos.forEach(tecnico => {
            lista.appendChild(criarCardTecnico(tecnico));
        });

    } catch (erro) {
        console.error("Erro ao carregar técnicos:", erro);
        lista.innerHTML =
            '<p class="text-muted">Não foi possível carregar os técnicos.</p>';
    }
}

// CRIAR CARD DO TÉCNICO
export function criarCardTecnico(tecnico) {
    return criarCardEntidade({
        foto: tecnico.foto_perfil ||
            tecnico.path_ft_usuario ||
            "/assets/img/no-prof-pic.png",

        nome: tecnico.nm_usuario || "Técnico sem nome",

        detalhes: ["Técnico"],

        url: null
    });
}

// CONFIGURAR MODAL DE TÉCNICO
export function configurarModalTecnico() {
    if (!estadoTime.podeGerenciar) return;
    const modal = document.querySelector(
        'app-modal[titulo="Adicionar técnico"]'
    );

    const botaoAbrir = document.getElementById("abrirModalTecnico");
    const formulario = document.getElementById("formAdicionarTecnico");
    const selectTecnico = document.getElementById("selectTecnico");

    if (!modal || !botaoAbrir || !formulario || !selectTecnico) {
        console.error("Elementos do modal de técnico não encontrados.");
        return;
    }

    if (formulario.dataset.configurado) return;
    formulario.dataset.configurado = "true";

    // ABRIR MODAL
    botaoAbrir.addEventListener("click", async () => {
        modal.abrir();
        await carregarOpcoesTecnicos();
    });

    // CARREGAR OPÇÕES
    async function carregarOpcoesTecnicos() {
        try {
            const idTime = obterIdTime();

            const [respostaDisponiveis, respostaVinculados] =
                await Promise.all([
                    apiFetch(`/times/${idTime}/tecnicos/disponiveis`),
                    apiFetch(`/times/${idTime}/tecnicos`)
                ]);

            const disponiveis = obterLista(respostaDisponiveis);
            const vinculados = obterLista(respostaVinculados);

            const idsVinculados = vinculados.map(tecnico =>
                String(tecnico.cd_usuario)
            );

            selectTecnico.replaceChildren();

            const inicial = document.createElement("option");
            inicial.value = "";
            inicial.textContent = "Selecione um técnico";
            selectTecnico.appendChild(inicial);

            const opcoes = disponiveis.filter(tecnico =>
                !idsVinculados.includes(String(tecnico.cd_usuario))
            );

            opcoes.forEach(tecnico => {
                const opcao = document.createElement("option");

                opcao.value = tecnico.cd_usuario;
                opcao.textContent = tecnico.nm_usuario;

                selectTecnico.appendChild(opcao);
            });

            if (!opcoes.length) {
                const vazio = document.createElement("option");
                vazio.value = "";
                vazio.textContent = "Nenhum técnico disponível";
                vazio.disabled = true;
                selectTecnico.appendChild(vazio);
            }

        } catch (erro) {
            console.error("Erro ao carregar técnicos disponíveis:", erro);
            alert("Não foi possível carregar os técnicos disponíveis.");
        }
    }

    // CADASTRAR TÉCNICO
    formulario.addEventListener("submit", async evento => {
        evento.preventDefault();

        const idTime = obterIdTime();
        const idUsuario = Number(selectTecnico.value);

        const botao = formulario.querySelector('[type="submit"]');

        if (!idUsuario) {
            alert("Selecione um técnico.");
            return;
        }

        try {
            if (botao) botao.disabled = true;

            await apiFetch(`/times/${idTime}/tecnicos`, {
                method: "POST",
                body: {
                    cd_usuario: idUsuario
                }
            });

            formulario.reset();
            modal.fechar();

            await carregarTecnicosTime(idTime);

            alert("Técnico vinculado com sucesso!");

        } catch (erro) {
            console.error("Erro ao adicionar técnico:", erro);

            alert(
                erro.message ||
                "Não foi possível adicionar o técnico."
            );

        } finally {
            if (botao) botao.disabled = false;
        }
    });
}
