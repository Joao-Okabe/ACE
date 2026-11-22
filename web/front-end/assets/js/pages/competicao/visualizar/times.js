import { criarCardEntidade } from "../../../components/cardEntidade.js";
import { criarSelectMultiplo } from "../../../components/selectMultiplo.js";

// RENDERIZAR TIMES INSCRITOS
export function renderizarTimesInscritos(times = []) {
    const lista = document.getElementById("listaTimesInscritos");
    const mensagem = document.getElementById("mensagemTimesInscritos");

    if (!lista || !mensagem) return;

    lista.replaceChildren();
    lista.classList.add("cards-entidade");
    lista.classList.remove("times-inscritos-grid");

    if (!times.length) {
        mensagem.textContent = "Nenhum time inscrito nesta competição.";
        return;
    }

    mensagem.textContent = "";

    times.forEach(time => {
        const card = criarCardEntidade({
            foto: time.brasao || "/assets/img/escudo.jpg",
            nome: time.nm_time || "Time sem nome",
            detalhes: [],
            url: `/pages/time/visualizar.html?id=${encodeURIComponent(time.cd_time)}&voltar=${encodeURIComponent(window.location.pathname + window.location.search)}`
        });

        lista.appendChild(card);
    });
}

// RENDERIZAR TIMES INSCRITOS NA ABA TIMES
export function renderizarTimesCompeticao(times = []) {
    const lista = document.getElementById("listaTimesCompeticao");
    const mensagem = document.getElementById("mensagemTimesCompeticao");

    if (!lista || !mensagem) return;

    lista.replaceChildren();
    lista.classList.remove("cards-entidade", "times-inscritos-grid");
    lista.classList.add("cards-entidade-grid");

    if (!times.length) {
        mensagem.textContent = "Nenhum time inscrito nesta competição.";
        return;
    }

    mensagem.textContent = "";

    console.log("TIMES DA COMPETIÇÃO:", times);

   times.forEach(time => {
        const card = criarCardEntidade({
            foto: time.path_escudo || "/assets/img/escudo.jpg",
            nome: time.nm_time || "Time sem nome",
            detalhes: [],
            url: `/pages/time/visualizar.html?id=${encodeURIComponent(time.cd_time)}&voltar=${encodeURIComponent(window.location.pathname + window.location.search)}`
        });

        // IDENTIFICAR O TIME
        card.dataset.idTime = String(time.cd_time);

        // O controle de remoção só existe para quem pode gerenciar a competição.
        if (
            window.usuarioVisitante !== true &&
            window.podeGerenciarCompeticao === true
        ) {
            const checkbox = document.createElement("input");
            checkbox.type = "checkbox";
            checkbox.className = "time-remocao-checkbox";
            checkbox.setAttribute("aria-label", `Selecionar ${time.nm_time || "time"}`);
            card.prepend(checkbox);
        }

        lista.appendChild(card);
    });
}

// PREENCHER LISTA DE TIMES DISPONÍVEIS
let selectTimes = null;

export function preencherTimesDisponiveis(times = []) {
    const container = document.getElementById("selectTimesDisponiveis");

    if (!container) {
        console.error("Select de times não encontrado.");
        return;
    }

    if (!selectTimes) {
        selectTimes = criarSelectMultiplo({
            container,
            itens: times,
            nomeCampo: "timesSelecionados",
            placeholder: "Selecione os times",
            obterValor: time => time.cd_time,
            obterNome: time => time.nm_time || "Time sem nome",
            obterImagem: time => time.path_escudo || "/assets/img/escudo.jpg"
        });

        return;
    }

    selectTimes.definirItens(times);
}