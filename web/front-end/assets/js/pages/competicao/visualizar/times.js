<<<<<<< HEAD
=======
import { criarCardEntidade } from "../../../components/cardEntidade.js";
import { criarSelectMultiplo } from "../../../components/selectMultiplo.js";

>>>>>>> origin/api_v1
// RENDERIZAR TIMES INSCRITOS
export function renderizarTimesInscritos(times = []) {
    const lista = document.getElementById("listaTimesInscritos");
    const mensagem = document.getElementById("mensagemTimesInscritos");

<<<<<<< HEAD
    lista.replaceChildren();
=======
    if (!lista || !mensagem) return;

    lista.replaceChildren();
    lista.classList.add("cards-entidade");
    lista.classList.remove("times-inscritos-grid");
>>>>>>> origin/api_v1

    if (!times.length) {
        mensagem.textContent = "Nenhum time inscrito nesta competição.";
        return;
    }

    mensagem.textContent = "";

    times.forEach(time => {
<<<<<<< HEAD
        const item = document.createElement("div");
        item.className = "lista-item";

        const nome = document.createElement("span");
        nome.textContent = time.nm_time || "Time sem nome";

        item.appendChild(nome);
        lista.appendChild(item);
    });
}


=======
        const card = criarCardEntidade({
            foto: time.brasao || "/assets/img/escudo.jpg",
            nome: time.nm_time || "Time sem nome",
            detalhes: [],
            url: `/pages/time/visualizar.html?id=${encodeURIComponent(time.cd_time)}&voltar=${encodeURIComponent(window.location.pathname + window.location.search)}`
        });

        lista.appendChild(card);
    });
}

>>>>>>> origin/api_v1
// RENDERIZAR TIMES INSCRITOS NA ABA TIMES
export function renderizarTimesCompeticao(times = []) {
    const lista = document.getElementById("listaTimesCompeticao");
    const mensagem = document.getElementById("mensagemTimesCompeticao");

<<<<<<< HEAD
    lista.replaceChildren();

    if (!times.length) {
        lista.classList.remove("times-inscritos-grid");
=======
    if (!lista || !mensagem) return;

    lista.replaceChildren();
    lista.classList.remove("cards-entidade", "times-inscritos-grid");
    lista.classList.add("cards-entidade-grid");

    if (!times.length) {
>>>>>>> origin/api_v1
        mensagem.textContent = "Nenhum time inscrito nesta competição.";
        return;
    }

    mensagem.textContent = "";
<<<<<<< HEAD
    lista.classList.add("times-inscritos-grid");

    
    times.forEach(time => {
        const card = document.createElement("a");
        card.className = "time-inscrito-card";
        card.href = `/pages/time/visualizar.html?id=${encodeURIComponent(time.cd_time)}`;
        card.style.textDecoration = "none";

        const brasao = document.createElement("img");
        brasao.className = "time-inscrito-brasao";
        brasao.alt = "";
        brasao.src = time.brasao || "";

        const nome = document.createElement("span");
        nome.className = "time-inscrito-nome";
        nome.textContent = time.nm_time || "Time sem nome";

        card.append(brasao, nome);
=======

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

>>>>>>> origin/api_v1
        lista.appendChild(card);
    });
}

<<<<<<< HEAD

// PREENCHER LISTA DE TIMES DISPONÍVEIS
export function preencherTimesDisponiveis(times = []) {
    const lista = document.getElementById("listaTimesDisponiveis");
    const textoSelecionados = document.getElementById("textoTimesSelecionados");

    lista.replaceChildren();

    textoSelecionados.textContent = "Selecione os times";

    if (!times.length) {
        const mensagem = document.createElement("p");
        mensagem.className = "select-multiplos-vazio";
        mensagem.textContent = "Nenhum time disponível.";
        lista.appendChild(mensagem);
        return;
    }

    times.forEach(time => {
        const item = document.createElement("label");
        item.className = "select-multiplos-item";

        const checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.name = "timesSelecionados";
        checkbox.value = time.cd_time;

        const brasao = document.createElement("img");
        brasao.className = "select-multiplos-brasao";
        brasao.alt = "";

        // Ajustar o campo conforme o retorno da API.
        brasao.src = time.brasao || "";

        const nome = document.createElement("span");
        nome.className = "select-multiplos-nome";
        nome.textContent = time.nm_time || "Time sem nome";

        item.append(checkbox, brasao, nome);
        lista.appendChild(item);

        checkbox.addEventListener("change", () => {
            const selecionados = lista.querySelectorAll(
                'input[name="timesSelecionados"]:checked'
            );

            const quantidade = selecionados.length;

            textoSelecionados.textContent = quantidade
                ? `${quantidade} ${quantidade === 1 ? "time selecionado" : "times selecionados"}`
                : "Selecione os times";

            item.classList.toggle("selecionado", checkbox.checked);
        });
    });
}
=======
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
>>>>>>> origin/api_v1
