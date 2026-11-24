import { configurarImagem } from "../utils/imagens.js";

const STATUS_PARTIDA = {
    AGENDADA: {
        texto: "Agendada",
        classe: "agendada"
    },
    FINALIZADA: {
        texto: "Finalizada",
        classe: "finalizada"
    },
    EM_ANDAMENTO: {
        texto: "Ao vivo",
        classe: "ao-vivo"
    }
};

// CRIAR IMAGEM DO TIME
function criarImagemTime(nome, escudo) {
    const imagem = document.createElement("img");

    imagem.alt = `Escudo de ${nome}`;
    configurarImagem(imagem, escudo, "/assets/img/escudo.jpg");

    return imagem;
}

// CRIAR CARD DA PARTIDA
export function criarCardPartida(partida) {
    const idCompeticao = Number(partida.cd_competicao);

    const card = document.createElement(
        idCompeticao > 0 ? "a" : "article"
    );

    card.className = "partida-card";

    if (idCompeticao > 0) {
        card.href =
            `/pages/competicao/visualizar.html?id=${idCompeticao}`;

        card.classList.add("partida-card--clicavel");
    }

    const status = STATUS_PARTIDA[partida.status] || {
        texto: partida.status || "Não definido",
        classe: ""
    };

    // TOPO
    const topo = document.createElement("div");
    topo.className = "partida-topo";

    const data = document.createElement("span");
    data.className = "partida-data";
    data.textContent = partida.dataTexto || "Data não informada";

    const etiqueta = document.createElement("span");
    etiqueta.className = `partida-status ${status.classe}`;
    etiqueta.textContent = status.texto;

    topo.append(data, etiqueta);

    // CONFRONTO
    const confronto = document.createElement("div");
    confronto.className = "partida-confronto";

    const criarTime = (time) => {
        const elemento = document.createElement("div");
        elemento.className = "partida-time";

        const nomeTime = time.nome || time.nm_time || "Time não informado";

        const imagem = criarImagemTime(
            nomeTime,
            time.escudo || time.path_escudo
        );

        const nome = document.createElement("h3");
        nome.textContent = nomeTime;

        elemento.append(imagem, nome);

        return elemento;
    };

    const timeA = criarTime(partida.timeA || {});
    const timeB = criarTime(partida.timeB || {});

    const placar = document.createElement("div");
    placar.className = "partida-placar";

    if (partida.placarA != null && partida.placarB != null) {
        const resultadoA = document.createElement("strong");
        resultadoA.textContent = partida.placarA;

        const separador = document.createElement("span");
        separador.textContent = "×";

        const resultadoB = document.createElement("strong");
        resultadoB.textContent = partida.placarB;

        placar.append(resultadoA, separador, resultadoB);
    } else {
        const vs = document.createElement("strong");
        vs.textContent = "VS";
        placar.appendChild(vs);
    }

    confronto.append(timeA, placar, timeB);

    // RODAPÉ
    const rodape = document.createElement("div");
    rodape.className = "partida-rodape";

    const local = document.createElement("span");
    local.textContent = partida.local || "Local não informado";

    const competicao = document.createElement("span");
    competicao.textContent =
        partida.competicao || "Competição não informada";

    rodape.append(local, competicao);

    card.append(topo, confronto, rodape);

    return card;
}

// RENDERIZAR LISTA
export function renderizarListaPartidas(container, partidas = []) {
    if (!container) return;

    container.replaceChildren();

    if (!partidas.length) {
        const vazio = document.createElement("p");
        vazio.className = "text-muted";
        vazio.textContent = "Nenhuma partida encontrada.";

        container.appendChild(vazio);
        return;
    }

    partidas.forEach(partida => {
        container.appendChild(criarCardPartida(partida));
    });
}
