
<<<<<<< HEAD
// IMPORTAÇÕES
import { apiFetch } from "../../../api/api.js";
import { obterIdTime } from "./utilitarios.js";
=======
import { apiFetch } from "../../../api/api.js";
import { estadoTime, obterIdTime } from "./utilitarios.js";
import { criarCardEntidade } from "../../../components/cardEntidade.js";

// NORMALIZAR RESPOSTA DA API
function obterLista(resposta) {
    if (Array.isArray(resposta)) return resposta;
    if (Array.isArray(resposta?.dados)) return resposta.dados;
    return [];
}
>>>>>>> origin/api_v1

// CARREGAR TÉCNICOS DO TIME
export async function carregarTecnicosTime(idTime = obterIdTime()) {
    const lista = document.getElementById("listaTecnico");

<<<<<<< HEAD
    if (!lista) {
        return;
    }

    try {
        const tecnicos = await apiFetch(`/times/${idTime}/tecnicos`);

        lista.replaceChildren();

        if (!tecnicos || !tecnicos.length) {
            lista.innerHTML = '<p class="text-muted">Nenhum técnico cadastrado.</p>';
=======
    if (!lista) return;

    lista.classList.add("cards-entidade");

    try {
        const resposta = await apiFetch(`/times/${idTime}/tecnicos`);
        const tecnicos = obterLista(resposta);

        lista.replaceChildren();

        if (!tecnicos.length) {
            lista.innerHTML =
                '<p class="text-muted">Nenhum técnico cadastrado.</p>';
>>>>>>> origin/api_v1
            return;
        }

        tecnicos.forEach(tecnico => {
            lista.appendChild(criarCardTecnico(tecnico));
        });
<<<<<<< HEAD
    } catch (erro) {
        console.error("Erro ao carregar técnicos:", erro);
=======

    } catch (erro) {
        console.error("Erro ao carregar técnicos:", erro);
        lista.innerHTML =
            '<p class="text-muted">Não foi possível carregar os técnicos.</p>';
>>>>>>> origin/api_v1
    }
}

// CRIAR CARD DO TÉCNICO
export function criarCardTecnico(tecnico) {
<<<<<<< HEAD
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
=======
    return criarCardEntidade({
        foto: tecnico.foto_perfil ||
            tecnico.path_ft_usuario ||
            "/assets/img/no-prof-pic.png",

        nome: tecnico.nm_usuario || "Técnico sem nome",

        detalhes: ["Técnico"],

        url: null
    });
>>>>>>> origin/api_v1
}

// CONFIGURAR MODAL DE TÉCNICO
export function configurarModalTecnico() {
<<<<<<< HEAD
    const modal = document.querySelector('app-modal[titulo="Adicionar técnico"]');
=======
    if (!estadoTime.podeGerenciar) return;
    const modal = document.querySelector(
        'app-modal[titulo="Adicionar técnico"]'
    );

>>>>>>> origin/api_v1
    const botaoAbrir = document.getElementById("abrirModalTecnico");
    const formulario = document.getElementById("formAdicionarTecnico");
    const selectTecnico = document.getElementById("selectTecnico");

    if (!modal || !botaoAbrir || !formulario || !selectTecnico) {
<<<<<<< HEAD
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
=======
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
>>>>>>> origin/api_v1
                String(tecnico.cd_usuario)
            );

            selectTecnico.replaceChildren();

<<<<<<< HEAD
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
=======
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
>>>>>>> origin/api_v1
        }
    });
}
