// RENDERIZAR TIMES INSCRITOS
export function renderizarTimesInscritos(times = []) {
    const lista = document.getElementById("listaTimesInscritos");
    const mensagem = document.getElementById("mensagemTimesInscritos");

    lista.replaceChildren();

    if (!times.length) {
        mensagem.textContent = "Nenhum time inscrito nesta competição.";
        return;
    }

    mensagem.textContent = "";

    times.forEach(time => {
        const item = document.createElement("div");
        item.className = "lista-item";

        const nome = document.createElement("span");
        nome.textContent = time.nm_time || "Time sem nome";

        item.appendChild(nome);
        lista.appendChild(item);
    });
}


// RENDERIZAR TIMES INSCRITOS NA ABA TIMES
export function renderizarTimesCompeticao(times = []) {
    const lista = document.getElementById("listaTimesCompeticao");
    const mensagem = document.getElementById("mensagemTimesCompeticao");

    lista.replaceChildren();

    if (!times.length) {
        lista.classList.remove("times-inscritos-grid");
        mensagem.textContent = "Nenhum time inscrito nesta competição.";
        return;
    }

    mensagem.textContent = "";
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
        lista.appendChild(card);
    });
}


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
