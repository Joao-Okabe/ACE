
import { apiFetch } from "../api/api.js";
import { competicaoService } from "../services/competicaoService.js";
import { esporteService } from "../services/esporteService.js";
import { escolaService } from "../services/escolaService.js";

const selectCompeticao = document.getElementById("competicaoRanking");
const selectEsporte = document.getElementById("esporteRanking");
const selectModalidade = document.getElementById("modalidadeRanking");
const selectEscola = document.getElementById("escolaRanking");
const selectMetrica = document.getElementById("metricaRanking");
const selectEscopo = document.getElementById("escopoRanking");

const campoModalidade = document.getElementById("campoModalidade");
const campoMetrica = document.getElementById("campoMetrica");
const campoEscopo = document.getElementById("campoEscopo");

const btnAvancados = document.getElementById("btnFiltrosAvancados");
const filtrosAvancados = document.getElementById("filtrosAvancados");
const btnLimpar = document.getElementById("btnLimparFiltros");
const formRanking = document.getElementById("formRanking");
const botoesAbas = document.querySelectorAll("[data-ranking]");

const secaoPodio = document.getElementById("secaoPodio");
const podio = document.getElementById("podioRanking");
const cabecalho = document.getElementById("cabecalhoRanking");
const corpoTabela = document.getElementById("corpoRanking");
const titulo = document.getElementById("tituloRanking");
const descricao = document.getElementById("descricaoRanking");
const mensagem = document.getElementById("mensagemRanking");

let tipoAtual = "equipes";
let versaoConsulta = 0;
let versaoMetricas = 0;

// COLUNAS DOS RANKINGS
const colunas = {
    equipes: [
        { titulo: "Posição", campo: "posicao" },
        { titulo: "Equipe", campo: "time" },
        { titulo: "Escola", campo: "escola" },
        { titulo: "Pontos", campo: "pontos" },
        { titulo: "Jogos", campo: "jogos" },
        { titulo: "Vitórias", campo: "vitorias" }
    ],
    escolas: [
        { titulo: "Posição", campo: "posicao" },
        { titulo: "Escola", campo: "escola" },
        { titulo: "Pontos", campo: "pontos" },
        { titulo: "Jogos", campo: "jogos" },
        { titulo: "Vitórias", campo: "vitorias" }
    ],
    atletas: [
        { titulo: "Posição", campo: "posicao" },
        { titulo: "Atleta", campo: "atleta" },
        { titulo: "Equipe", campo: "time" },
        { titulo: "Escola", campo: "escola" },
        { titulo: "Métrica", campo: "metrica_nome" },
        { titulo: "Valor", campo: "valor" },
        { titulo: "Jogos", campo: "jogos" }
    ]
};

// OBTER LISTAS DA API

function obterLista(resposta) {
    if (resposta?.sucesso === false || resposta?.success === false) {
        throw new Error(
            resposta.mensagem ||
            resposta.message ||
            "Não foi possível carregar os dados."
        );
    }

    const dados = resposta?.dados ?? resposta?.data ?? resposta;

    if (Array.isArray(dados)) {
        return dados;
    }

    if (Array.isArray(dados?.itens)) {
        return dados.itens;
    }

    if (Array.isArray(dados?.resultados)) {
        return dados.resultados;
    }

    throw new Error("A API retornou um formato de lista inesperado.");
}


// EXIBIR VALORES
function exibirValor(valor) {
    return valor === null ||
        valor === undefined ||
        valor === ""
        ? "—"
        : String(valor);
}

// PREENCHER SELECT
function preencherSelect(
    select,
    itens,
    campoId,
    campoNome,
    textoInicial
) {
    select.replaceChildren(
        new Option(textoInicial, "")
    );

    itens.forEach(item => {
        const id = item[campoId];
        const nome = item[campoNome];

        if (
            id === null ||
            id === undefined ||
            !nome
        ) {
            return;
        }

        select.add(
            new Option(String(nome), String(id))
        );
    });

    select.disabled = false;
}

// ERRO AO CARREGAR SELECT
function erroSelect(select, texto) {
    select.replaceChildren(
        new Option(texto, "")
    );

    select.disabled = true;
}

// CARREGAMENTO GENÉRICO DOS SERVIÇOS
async function carregarSelectService(
    select,
    metodo,
    campoId,
    campoNome,
    textoInicial
) {
    select.disabled = true;

    select.replaceChildren(
        new Option("Carregando...", "")
    );

    try {
        const resposta = await metodo();
        const itens = obterLista(resposta);

        preencherSelect(
            select,
            itens,
            campoId,
            campoNome,
            textoInicial
        );

    } catch (erro) {
        console.error(
            `Não foi possível carregar ${textoInicial}:`,
            erro
        );

        erroSelect(
            select,
            "Opções indisponíveis"
        );
    }
}

// CARREGAR COMPETIÇÕES
async function carregarCompeticoes() {
    await carregarSelectService(
        selectCompeticao,
        () => competicaoService.listar(),
        "cd_competicao",
        "nm_competicao",
        "Todas as competições"
    );
}

// CARREGAR MODALIDADES
async function carregarModalidades() {
    selectModalidade.disabled = true;

    selectModalidade.replaceChildren(
        new Option("Carregando modalidades...", "")
    );

    try {
        const resposta = await apiFetch("/modalidades");
        const modalidades = obterLista(resposta);

        selectModalidade.replaceChildren(
            new Option("Todas as modalidades", "")
        );

        modalidades.forEach(modalidade => {
            const id = modalidade.cd_modalidade;

            const nome = [
                modalidade.categoria,
                modalidade.sexo
            ]
                .filter(valor =>
                    valor !== null &&
                    valor !== undefined &&
                    valor !== ""
                )
                .join(" - ");

            if (
                id === null ||
                id === undefined ||
                !nome
            ) {
                return;
            }

            selectModalidade.add(
                new Option(nome, String(id))
            );
        });

        selectModalidade.disabled = false;

    } catch (erro) {
        console.error(
            "Erro ao carregar modalidades:",
            erro
        );

        erroSelect(
            selectModalidade,
            "Modalidades indisponíveis"
        );
    }
}

// CARREGAR FILTROS
async function carregarFiltros() {
    await Promise.all([
        carregarSelectService(
            selectEsporte,
            () => esporteService.listar(),
            "cd_esporte",
            "nm_esporte",
            "Todos os esportes"
        ),

        carregarSelectService(
            selectEscola,
            () => escolaService.listar(),
            "cd_escola",
            "nome",
            "Todas as escolas"
        ),

        carregarModalidades()
    ]);
}

// CARREGAR MÉTRICAS DO ESPORTE
async function carregarMetricas() {
    const consulta = ++versaoMetricas;

    selectMetrica.disabled = true;

    if (!selectEsporte.value) {
        selectMetrica.replaceChildren(
            new Option(
                "Selecione primeiro um esporte",
                ""
            )
        );

        return;
    }

    const id = Number(selectEsporte.value);

    if (
        !Number.isInteger(id) ||
        id <= 0
    ) {
        return;
    }

    selectMetrica.replaceChildren(
        new Option("Carregando métricas...", "")
    );

    try {
        const resposta = await apiFetch(
            `/esportes/${id}/metricas-atleta`
        );

        if (consulta !== versaoMetricas) return;

        const metricas = obterLista(resposta);

        preencherSelect(
            selectMetrica,
            metricas,
            "id",
            "nome",
            "Selecione uma métrica"
        );

        if (!metricas.length) {
            erroSelect(
                selectMetrica,
                "Nenhuma métrica disponível"
            );
        }

    } catch (erro) {
        if (consulta !== versaoMetricas) return;

        console.error(
            "Erro ao carregar métricas:",
            erro
        );

        erroSelect(
            selectMetrica,
            "Métricas indisponíveis"
        );
    }
}

// ATUALIZAR CABEÇALHO DA TABELA
function atualizarCabecalho() {
    const linha = document.createElement("tr");

    colunas[tipoAtual].forEach(coluna => {
        const th = document.createElement("th");
        th.textContent = coluna.titulo;

        linha.appendChild(th);
    });

    cabecalho.replaceChildren(linha);
}

// LIMPAR RESULTADOS
function limparResultados() {
    versaoConsulta++;

    corpoTabela.replaceChildren();
    podio.replaceChildren();

    secaoPodio.hidden = true;
}

// MONTAR PARÂMETROS DA API
function montarParametros() {
    const params = new URLSearchParams();

    if (selectCompeticao.value) {
        params.set(
            "competicaoId",
            selectCompeticao.value
        );
    }

    if (selectEsporte.value) {
        params.set(
            "esporteId",
            selectEsporte.value
        );
    }

    if (selectEscola.value) {
        params.set(
            "escolaId",
            selectEscola.value
        );
    }

    if (tipoAtual === "atletas") {
        if (
            !selectEsporte.value ||
            !selectMetrica.value
        ) {
            throw new Error(
                "Selecione um esporte e uma métrica."
            );
        }

        params.set(
            "metricaId",
            selectMetrica.value
        );

        params.set(
            "escopo",
            selectEscopo.value
        );

        if (
            selectEscopo.value === "escola" &&
            !selectEscola.value
        ) {
            alternarFiltrosAvancados(true);

            throw new Error(
                "Selecione uma escola em Mais filtros."
            );
        }

        if (
            selectEscopo.value === "competicao" &&
            !selectCompeticao.value
        ) {
            throw new Error(
                "Selecione uma competição."
            );
        }

    } else if (selectModalidade.value) {
        params.set(
            "modalidadeId",
            selectModalidade.value
        );
    }

    return params;
}

// RENDERIZAR TABELA
function renderizarTabela(itens) {
    corpoTabela.replaceChildren();

    itens.forEach((item, indice) => {
        const linha = document.createElement("tr");

        colunas[tipoAtual].forEach(coluna => {
            const td = document.createElement("td");

            td.textContent =
                coluna.campo === "posicao"
                    ? `${indice + 1}º`
                    : exibirValor(
                        item[coluna.campo]
                    );

            linha.appendChild(td);
        });

        corpoTabela.appendChild(linha);
    });
}

// RENDERIZAR PÓDIO
function renderizarPodio(itens) {
    podio.replaceChildren();

    const colocacoes = [
        {
            indice: 1,
            medalha: "🥈",
            classe: "segundo"
        },
        {
            indice: 0,
            medalha: "🥇",
            classe: "primeiro"
        },
        {
            indice: 2,
            medalha: "🥉",
            classe: "terceiro"
        }
    ];

    colocacoes.forEach(({
        indice,
        medalha,
        classe
    }) => {
        const item = itens[indice];

        if (!item) return;

        const nome =
            tipoAtual === "equipes"
                ? item.time
                : tipoAtual === "escolas"
                    ? item.escola
                    : item.atleta;

        const card = document.createElement("div");

        card.className =
            `ranking-podio-card ${classe}`;

        const icone = document.createElement("div");
        icone.className = "ranking-medalha";
        icone.textContent = medalha;

        const nomeCard = document.createElement("h3");
        nomeCard.textContent = exibirValor(nome);

        const detalhes = document.createElement("p");

        detalhes.textContent =
            tipoAtual === "atletas"
                ? `${exibirValor(item.valor)} ${item.metrica_nome || ""}`.trim()
                : `${exibirValor(item.pontos)} pontos`;

        card.append(
            icone,
            nomeCard,
            detalhes
        );

        podio.appendChild(card);
    });

    secaoPodio.hidden =
        podio.children.length === 0;
}

// CARREGAR RANKING
async function carregarRanking() {
    limparResultados();

    const consulta = versaoConsulta;
    const tipo = tipoAtual;

    mensagem.textContent =
        "Carregando ranking...";

    try {
        const params = montarParametros();

        const resposta = await apiFetch(
            `/rankings/${tipo}?${params.toString()}`
        );

        if (consulta !== versaoConsulta) return;

        const itens = obterLista(resposta);

        renderizarTabela(itens);
        renderizarPodio(itens);

        mensagem.textContent = itens.length
            ? ""
            : "Nenhum resultado encontrado para os filtros selecionados.";

    } catch (erro) {
        if (consulta !== versaoConsulta) return;

        console.error(
            "Erro ao carregar ranking:",
            erro
        );

        mensagem.textContent =
            erro.message ||
            "Não foi possível carregar o ranking.";
    }
}

// SELECIONAR TIPO DE RANKING
function selecionarTipo(tipo) {
    tipoAtual = tipo;

    limparResultados();

    botoesAbas.forEach(botao => {
        const ativo =
            botao.dataset.ranking === tipo;

        botao.classList.toggle(
            "ativo",
            ativo
        );

        botao.setAttribute(
            "aria-pressed",
            String(ativo)
        );
    });

    const atletas = tipo === "atletas";

    campoModalidade.hidden = atletas;
    campoMetrica.hidden = !atletas;
    campoEscopo.hidden = !atletas;

    titulo.textContent =
        `Ranking de ${tipo}`;

    descricao.textContent = atletas
        ? "Desempenho dos atletas por métrica esportiva."
        : tipo === "escolas"
            ? "Classificação das escolas pelas métricas dos grupos."
            : "Classificação das equipes pelas métricas dos grupos.";

    atualizarCabecalho();

    mensagem.textContent = atletas
        ? "Selecione um esporte e uma métrica para consultar."
        : "Aplique os filtros para consultar o ranking.";
}

// ABRIR E FECHAR FILTROS AVANÇADOS
function alternarFiltrosAvancados(aberto) {
    filtrosAvancados.hidden = !aberto;

    btnAvancados.setAttribute(
        "aria-expanded",
        String(aberto)
    );
}

// LIMPAR FILTROS
function limparFiltros() {
    formRanking.reset();

    ++versaoMetricas;

    selectMetrica.disabled = true;

    selectMetrica.replaceChildren(
        new Option(
            "Selecione primeiro um esporte",
            ""
        )
    );

    limparResultados();

    mensagem.textContent =
        "Filtros limpos. Aplique os filtros para consultar.";
}

// EVENTOS DAS ABAS
botoesAbas.forEach(botao => {
    botao.addEventListener("click", () => {
        selecionarTipo(
            botao.dataset.ranking
        );
    });
});

// EVENTO DOS FILTROS AVANÇADOS
btnAvancados.addEventListener("click", () => {
    alternarFiltrosAvancados(
        filtrosAvancados.hidden
    );
});

// EVENTO LIMPAR FILTROS
btnLimpar.addEventListener(
    "click",
    limparFiltros
);

// EVENTO DO ESPORTE
selectEsporte.addEventListener(
    "change",
    carregarMetricas
);

// EVENTO APLICAR FILTROS
formRanking.addEventListener(
    "submit",
    evento => {
        evento.preventDefault();
        carregarRanking();
    }
);

// INICIALIZAÇÃO
selecionarTipo("equipes");
alternarFiltrosAvancados(false);

carregarCompeticoes();
carregarFiltros();
