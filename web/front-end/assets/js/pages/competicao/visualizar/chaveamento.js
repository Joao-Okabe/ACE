
import { competicaoService } from "../../../services/competicaoService.js";
import { idCompeticao } from "./competicao.js";

// RENDERIZAR CHAVEAMENTO
export function renderizarChaveamento(dados = [], formato = 1) {
    const container = document.getElementById("chaveamento-container");
    const mensagem = document.getElementById("mensagemChaveamento");
    const botaoGerar = document.getElementById("btnGerarChaveamento");

    const existeChaveamento = Array.isArray(dados) && dados.length > 0;

    if (botaoGerar) {
        botaoGerar.hidden = !window.podeGerarChaveamento || existeChaveamento;
    }

    container.replaceChildren();
    mensagem.textContent = "";

    const rodadas = agruparRodadas(dados);

    if (!rodadas.length) {
        mensagem.textContent = "O chaveamento ainda não foi gerado.";
        return;
    }

    // ELIMINATÓRIA DUPLA
    if (Number(formato) === 2) {
        renderizarChaveamentoDupla(dados, rodadas, container);
        return;
    }

    // SISTEMA SUÍÇO
    if (Number(formato) === 4) {
        renderizarChaveamentoSuico(rodadas, container);
        return;
    }

    // ELIMINATÓRIA SIMPLES
    const quantidadeTimes = contarTimes(dados);
    const quantidadeConfrontosInicial = rodadas[0].confrontos.length;

    const alturaTotal = calcularAlturaRodada(
        quantidadeConfrontosInicial
    );

    const bracket = document.createElement("div");
    bracket.className = "bracket";
    bracket.dataset.times = quantidadeTimes;

    rodadas.forEach((rodada, indice) => {
        bracket.insertAdjacentHTML(
            "beforeend",
            criarColunaRodada(rodada, alturaTotal, indice)
        );

        if (indice < rodadas.length - 1) {
            const proximaRodada = rodadas[indice + 1];

            bracket.insertAdjacentHTML("beforeend", `
                <div class="bracket-conexoes">
                    ${criarConexoes(
                        proximaRodada.confrontos.length,
                        indice
                    )}
                </div>
            `);
        }
    });

    container.appendChild(bracket);
}

// RENDERIZAR ELIMINATÓRIA DUPLA
function renderizarChaveamentoDupla(dados, rodadas, container) {
    const bracket = document.createElement("div");
    bracket.className = "bracket bracket-dupla";

    const larguraColuna = 226;
    const altura = 1250;
    const alturaPartida = 60;

    const layout = {
        quartas: {
            coluna: 0,
            titulo: 30,
            partidas: [80, 260, 440, 620]
        },
        semifinal: {
            coluna: 1,
            titulo: 120,
            partidas: [170, 530]
        },
        final: {
            coluna: 2,
            titulo: 300,
            partidas: [350]
        },
        repescagem1: {
            coluna: 1,
            titulo: 790,
            partidas: [850, 1030]
        },
        repescagem2: {
            coluna: 2,
            titulo: 790,
            partidas: [850, 1030]
        },
        repescagem3: {
            coluna: 3,
            titulo: 880,
            partidas: [940]
        },
        repescagem4: {
            coluna: 4,
            titulo: 880,
            partidas: [940]
        },
        granfinal: {
            coluna: 5,
            titulo: 470,
            partidas: [550]
        }
    };

    function identificarRodada(nome) {
        const normalizado = String(nome || "")
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .toLowerCase()
            .replace(/[\s_-]/g, "");

        if (normalizado.includes("granfinal")) return "granfinal";
        if (normalizado.includes("repescagem4")) return "repescagem4";
        if (normalizado.includes("repescagem3")) return "repescagem3";
        if (normalizado.includes("repescagem2")) return "repescagem2";
        if (normalizado.includes("repescagem1")) return "repescagem1";
        if (normalizado.includes("quartas")) return "quartas";
        if (normalizado.includes("semifinal")) return "semifinal";
        if (normalizado === "final") return "final";

        return null;
    }

    const posicoesConfrontos = new Map();

    rodadas.forEach(rodada => {
        const chave = identificarRodada(rodada.nome);
        const configuracao = layout[chave];

        if (!configuracao) {
            console.warn("Rodada sem posição definida:", rodada.nome);
            return;
        }

        const coluna = document.createElement("div");
        coluna.className = "bracket-coluna";
        coluna.style.left = `${configuracao.coluna * larguraColuna}px`;

        const titulo = document.createElement("h3");
        titulo.className = "round-title";
        titulo.textContent = rodada.nome;
        titulo.style.top = `${configuracao.titulo}px`;

        const area = document.createElement("div");
        area.className = "round";
        area.style.height = `${altura}px`;

        rodada.confrontos.forEach((confronto, indice) => {
            const top = configuracao.partidas[indice]
                ?? (configuracao.partidas[0] + indice * 76);

            const partida = document.createElement("div");
            partida.className = "partida";
            partida.style.top = `${top}px`;
            partida.dataset.confronto = confronto.id;

            partida.innerHTML = `
                ${criarParticipante(
                    confronto.participantes.find(
                        p => Number(p.posicao) === 1
                    )
                )}
                ${criarParticipante(
                    confronto.participantes.find(
                        p => Number(p.posicao) === 2
                    )
                )}
            `;

            area.appendChild(partida);

            posicoesConfrontos.set(String(confronto.id), {
                coluna: configuracao.coluna,
                x: configuracao.coluna * larguraColuna,
                y: top + alturaPartida / 2
            });
        });

        coluna.appendChild(titulo);
        coluna.appendChild(area);
        bracket.appendChild(coluna);
    });

    // CAMADA DE CONEXÕES
    const larguraCard = 190;

    const camadaConexoes = document.createElement("div");
    camadaConexoes.className = "bracket-conexoes";
    bracket.appendChild(camadaConexoes);

    function criarLinha(classe, x1, y1, x2, y2) {
        const linha = document.createElement("div");
        linha.className = classe;
        linha.style.left = `${Math.min(x1, x2)}px`;
        linha.style.top = `${y1 - 1}px`;
        linha.style.width = `${Math.abs(x2 - x1)}px`;
        camadaConexoes.appendChild(linha);
    }

    function criarLinhaVertical(x, y1, y2) {
        const linha = document.createElement("div");
        linha.className = "vertical";
        linha.style.left = `${x - 1}px`;
        linha.style.top = `${Math.min(y1, y2)}px`;
        linha.style.height = `${Math.abs(y2 - y1)}px`;
        camadaConexoes.appendChild(linha);
    }

    // IDENTIFICAR PARTIDAS DE ORIGEM
    rodadas.forEach(rodada => {
        rodada.confrontos.forEach(confronto => {
            const destino = posicoesConfrontos.get(
                String(confronto.id)
            );

            if (!destino) return;

            const origens = new Set();

            const idsOrigem = [
                confronto.cd_confronto_anterior_a,
                confronto.cd_confronto_anterior_b
            ];

            confronto.participantes.forEach(participante => {
                idsOrigem.push(
                    participante.cd_confronto_anterior_a,
                    participante.cd_confronto_anterior_b
                );
            });

            idsOrigem.forEach(id => {
                if (id !== null && id !== undefined && id !== "") {
                    origens.add(String(id));
                }
            });

            const partidasOrigem = [...origens]
                .map(id => posicoesConfrontos.get(id))
                .filter(Boolean)
                .sort((a, b) => a.y - b.y);

            if (!partidasOrigem.length) return;

            if (partidasOrigem.some(origem => origem.coluna >= destino.coluna)) {
                console.warn(
                    "Verifique a posição das rodadas e a origem do confronto:",
                    confronto.id,
                    partidasOrigem,
                    destino
                );
                return;
            }

            const pontosOrigem = partidasOrigem.map(origem => ({
                x: origem.x + larguraCard,
                y: origem.y
            }));

            const maiorSaida = Math.max(
                ...pontosOrigem.map(ponto => ponto.x)
            );

            const meioX = maiorSaida +
                (destino.x - maiorSaida) / 2;

            pontosOrigem.forEach((ponto, indice) => {
                criarLinha(
                    indice === 0 ? "entrada-1" : "entrada-2",
                    ponto.x,
                    ponto.y,
                    meioX,
                    ponto.y
                );
            });

            const menorY = Math.min(
                ...pontosOrigem.map(ponto => ponto.y),
                destino.y
            );

            const maiorY = Math.max(
                ...pontosOrigem.map(ponto => ponto.y),
                destino.y
            );

            criarLinhaVertical(meioX, menorY, maiorY);

            criarLinha(
                "saida",
                meioX,
                destino.y,
                destino.x,
                destino.y
            );
        });
    });

    container.appendChild(bracket);
}

// RENDERIZAR SISTEMA SUÍÇO
function renderizarChaveamentoSuico(rodadas, container) {
    const bracket = document.createElement("div");
    bracket.className = "bracket bracket-suico";

    rodadas.forEach((rodada, indice) => {
        const coluna = document.createElement("section");
        coluna.className = "bracket-coluna coluna-suica";

        const titulo = document.createElement("h3");
        titulo.className = "round-title";
        titulo.textContent = rodada.nome || `Rodada ${indice + 1}`;

        const area = document.createElement("div");
        area.className = "round";

        rodada.confrontos.forEach(confronto => {
            const partida = document.createElement("div");
            partida.className = "partida";
            partida.dataset.confronto = confronto.id;

            partida.innerHTML = `
                ${criarParticipante(
                    confronto.participantes.find(
                        p => Number(p.posicao) === 1
                    )
                )}
                ${criarParticipante(
                    confronto.participantes.find(
                        p => Number(p.posicao) === 2
                    )
                )}
            `;

            area.appendChild(partida);
        });

        coluna.appendChild(titulo);
        coluna.appendChild(area);
        bracket.appendChild(coluna);
    });

    container.appendChild(bracket);
}

// AGRUPAR CONFRONTOS POR RODADA
function agruparRodadas(dados) {
    const mapaRodadas = new Map();

    dados.forEach(item => {
        const numeroRodada = Number(item.nr_rodada);

        if (!mapaRodadas.has(numeroRodada)) {
            mapaRodadas.set(numeroRodada, {
                numero: numeroRodada,
                nome: item.nm_rodada || `Rodada ${numeroRodada}`,
                confrontos: new Map()
            });
        }

        const rodada = mapaRodadas.get(numeroRodada);

        if (!rodada.confrontos.has(item.cd_confronto)) {
            rodada.confrontos.set(item.cd_confronto, {
                id: item.cd_confronto,
                numero: item.nr_confronto,
                nome: item.nome,
                status: item.status,
                vencedor: item.cd_vencedor,
                participantes: []
            });
        }

        rodada.confrontos.get(item.cd_confronto)
            .participantes.push(item);
    });

    return [...mapaRodadas.values()]
        .sort((a, b) => a.numero - b.numero)
        .map(rodada => ({
            ...rodada,
            confrontos: [...rodada.confrontos.values()]
                .sort((a, b) => Number(a.numero) - Number(b.numero))
        }));
}

// CALCULAR ALTURA DA RODADA
function calcularAlturaRodada(quantidadeConfrontos) {
    const alturaPartida = 76;
    const espacamento = 60;

    return quantidadeConfrontos * (alturaPartida + espacamento);
}

// CRIAR COLUNA DA RODADA
function criarColunaRodada(rodada, alturaTotal, indiceRodada) {
    const classeRodada = classeDaRodada(rodada.nome);
    const fator = Math.pow(2, indiceRodada);
    const espacamento = 76;

    const partidas = rodada.confrontos.map((confronto, indice) => {
        const posicao = (
            indice * fator + (fator - 1) / 2
        ) * espacamento;

        return `
            <div class="partida"
                 style="top: ${posicao}px"
                 data-confronto="${escapeHtml(confronto.id)}">
                ${criarParticipante(confronto.participantes.find(
                    participante => Number(participante.posicao) === 1
                ))}
                ${criarParticipante(confronto.participantes.find(
                    participante => Number(participante.posicao) === 2
                ))}
            </div>
        `;
    }).join("");

    return `
        <div class="bracket-coluna">
            <h3 class="round-title">${escapeHtml(rodada.nome)}</h3>
            <div class="round ${classeRodada}" style="height: ${alturaTotal}px">
                ${partidas}
            </div>
        </div>
    `;
}

// CRIAR CONFRONTO
function criarConfronto(confronto) {
    const participante1 = confronto.participantes.find(
        participante => Number(participante.posicao) === 1
    );

    const participante2 = confronto.participantes.find(
        participante => Number(participante.posicao) === 2
    );

    return `
        <div class="partida" data-confronto="${escapeHtml(confronto.id)}">
            ${criarParticipante(participante1)}
            ${criarParticipante(participante2)}
        </div>
    `;
}

// CRIAR PARTICIPANTE
function criarParticipante(participante) {
    if (!participante) {
        return `<div class="time">A definir</div>`;
    }

    let nome = "A definir";
    let imagem = "";

    if (participante.tipo_origem === "TIME") {
        nome = participante.nm_time || "Time sem nome";
        imagem = participante.path_escudo || "";
    } else if (participante.tipo_origem === "CONFRONTO") {
        const resultado = String(
            participante.resultado_confronto || "VENCEDOR"
        ).toUpperCase();

        const tipoResultado = resultado === "PERDEDOR"
            ? "Perdedor"
            : "Vencedor";

        nome = participante.origem_confronto
            ? `${tipoResultado} C${participante.origem_confronto}`
            : `${tipoResultado} a definir`;
    } else if (participante.tipo_origem === "BYE") {
        nome = "BYE";
    }

    return `
        <div class="time">
            ${imagem
                ? `<img src="${escapeHtml(imagem)}" alt="" class="time-escudo">`
                : ""
            }
            <span>${escapeHtml(nome)}</span>
        </div>
    `;
}

// CRIAR CONEXÕES ENTRE RODADAS
function criarConexoes(quantidade, indiceRodada) {
    const fator = Math.pow(2, indiceRodada);
    const espacamento = 76;
    const alturaPartida = 60;
    const alturaTituloRodada = 55;

    const distancia = fator * espacamento;

    return Array.from({ length: quantidade }, (_, indice) => {
        const top =
            (indice * 2 * fator + (fator - 1) / 2) * espacamento
            + alturaTituloRodada;

        const meio = alturaPartida / 2 + distancia / 2;

        return `
            <div
                class="conexao"
                style="
                    top: ${top}px;
                    height: ${distancia + alturaPartida}px;
                    --entrada-1: ${alturaPartida / 2}px;
                    --entrada-2: ${alturaPartida / 2 + distancia}px;
                    --saida-y: ${meio}px;
                    --vertical-h: ${distancia}px;
                "
            >
                <div class="entrada-1"></div>
                <div class="entrada-2"></div>
                <div class="vertical"></div>
                <div class="saida"></div>
            </div>
        `;
    }).join("");
}

// IDENTIFICAR CLASSE DA RODADA
function classeDaRodada(nome) {
    const normalizado = String(nome || "")
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase()
        .replace(/[\s_-]/g, "");

    if (normalizado.includes("16avos") ||
        normalizado.includes("dezesseisavos")) {
        return "dezesseisavos";
    }

    if (normalizado.includes("oitavas")) {
        return "oitavas";
    }

    if (normalizado.includes("quartas")) {
        return "quartas";
    }

    if (normalizado.includes("semifinal")) {
        return "semifinal";
    }

    if (normalizado.includes("final")) {
        return "final";
    }

    return "rodada";
}

// IDENTIFICAR CONEXÃO ENTRE RODADAS
function criarClasseConexao(rodadaAtual, proximaRodada) {
    const classeAtual = classeDaRodada(rodadaAtual.nome);
    const classeProxima = classeDaRodada(proximaRodada.nome);

    return `${classeAtual}-para-${classeProxima}`;
}

// CONTAR TIMES ÚNICOS
function contarTimes(dados) {
    const times = dados
        .filter(item => item.tipo_origem === "TIME" && item.cd_time)
        .map(item => item.cd_time);

    return new Set(times).size;
}

// ESCAPAR HTML
function escapeHtml(valor) {
    return String(valor ?? "").replace(/[&<>"']/g, caractere => ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#039;"
    })[caractere]);
}

// CONFIGURAR BOTÃO DE GERAR CHAVEAMENTO
export function configurarBotaoGerarChaveamento(atualizarCompeticao) {
    const botao = document.getElementById("btnGerarChaveamento");

    if (!botao) return;

    botao.addEventListener("click", async () => {
        if (!confirm("Deseja gerar o chaveamento desta competição?")) {
            return;
        }

        botao.disabled = true;

        try {
            await competicaoService.gerarChaveamento(idCompeticao);
            await atualizarCompeticao();

        } catch (erro) {
            console.error("Erro ao gerar chaveamento:", erro);

            const mensagem = document.getElementById("mensagemChaveamento");

            mensagem.textContent =
                erro.message || "Não foi possível gerar o chaveamento.";

        } finally {
            botao.disabled = false;
        }
    });
}
