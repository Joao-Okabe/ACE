import { competicaoService } from "../../../services/competicaoService.js";

const container = document.getElementById("classificacao-grupos-container");
const mensagem = document.getElementById("mensagemClassificacaoGrupos");

const colunas = [
    ["JOGOS", "J"],
    ["VITORIAS", "V"],
    ["EMPATES", "E"],
    ["DERROTAS", "D"],
    ["GOLS_PRO", "GP"],
    ["GOLS_CONTRA", "GC"],
    ["SALDO", "SG"],
    ["PONTOS", "PTS"]
];

function obterEstatisticas(valor) {
    if (typeof valor === "string") {
        try {
            return JSON.parse(valor);
        } catch {
            return {};
        }
    }

    return valor && typeof valor === "object" ? valor : {};
}

function obterValorEstatistica(estatisticas, chave) {
    const valor = estatisticas?.[chave];
    if (valor === null || valor === undefined || valor === "") return 0;
    return valor;
}

export async function carregarClassificacaoGrupos(
    idCompeticao,
    formato,
    classificacaoRecebida = null
) {
    if (!container || !mensagem) return;

    container.replaceChildren();
    container.hidden = true;
    mensagem.hidden = true;
    mensagem.textContent = "";

    if (Number(formato) !== 3) return;

    try {
        const dados = Array.isArray(classificacaoRecebida)
            ? classificacaoRecebida
            : await competicaoService.listarClassificacao(idCompeticao);

        if (!Array.isArray(dados) || dados.length === 0) {
            mensagem.textContent = "A classificação ainda não está disponível.";
            mensagem.hidden = false;
            return;
        }

        const grupos = new Map();

        dados.forEach(time => {
            const idGrupo = String(time.cd_grupo ?? "");
            if (!idGrupo) return;

            if (!grupos.has(idGrupo)) {
                grupos.set(idGrupo, {
                    nome: time.nm_grupo || `Grupo ${grupos.size + 1}`,
                    times: []
                });
            }

            grupos.get(idGrupo).times.push(time);
        });

        if (grupos.size === 0) {
            mensagem.textContent = "Não há grupos cadastrados para esta competição.";
            mensagem.hidden = false;
            return;
        }

        const titulo = document.createElement("h3");
        titulo.className = "visu-subtitulo";
        titulo.textContent = "Classificação dos grupos";
        container.appendChild(titulo);

        grupos.forEach(grupo => {
            const secao = document.createElement("section");
            secao.className = "classificacao-grupo";

            const nome = document.createElement("h4");
            nome.className = "classificacao-grupo-titulo";
            nome.textContent = grupo.nome;
            secao.appendChild(nome);

            const area = document.createElement("div");
            area.className = "classificacao-tabela-wrapper";

            const tabela = document.createElement("table");
            tabela.className = "classificacao-tabela";

            const cabecalho = document.createElement("thead");
            const linhaCabecalho = document.createElement("tr");

            ["Pos.", "Time", ...colunas.map(coluna => coluna[1])].forEach(texto => {
                const th = document.createElement("th");
                th.textContent = texto;
                linhaCabecalho.appendChild(th);
            });

            cabecalho.appendChild(linhaCabecalho);
            tabela.appendChild(cabecalho);

            const corpo = document.createElement("tbody");

            grupo.times
                .sort((a, b) => Number(a.posicao) - Number(b.posicao))
                .forEach(time => {
                    const linha = document.createElement("tr");
                    const estatisticas = obterEstatisticas(time.estatisticas);
                    const valores = [
                        `${time.posicao ?? "-"}º`,
                        time.nm_time || "Time sem nome",
                        ...colunas.map(([chave]) => obterValorEstatistica(estatisticas, chave))
                    ];

                    valores.forEach((valor, indice) => {
                        const celula = document.createElement("td");
                        celula.textContent = String(valor);

                        if (indice === 1) {
                            celula.className = "classificacao-nome-time";
                        }

                        linha.appendChild(celula);
                    });

                    corpo.appendChild(linha);
                });

            tabela.appendChild(corpo);
            area.appendChild(tabela);
            secao.appendChild(area);
            container.appendChild(secao);
        });

        container.hidden = false;
    } catch (erro) {
        console.error("Erro ao carregar classificação:", erro);
        mensagem.textContent = erro.message || "Não foi possível carregar a classificação.";
        mensagem.hidden = false;
    }
}
