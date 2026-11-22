
import { competicaoService } from "../../../services/competicaoService.js";
import { idCompeticao } from "./competicao.js";

// RENDERIZAR CHAVEAMENTO
export function renderizarChaveamento(dados = [], formato = 1) {
    const container = document.getElementById("chaveamento-container");
    const mensagem = document.getElementById("mensagemChaveamento");
    const botaoGerar = document.getElementById("btnGerarChaveamento");

<<<<<<< HEAD
=======
    if (!container || !mensagem) return;

>>>>>>> origin/api_v1
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

<<<<<<< HEAD
    // ELIMINATÓRIA DUPLA
    if (Number(formato) === 2) {
        renderizarChaveamentoDupla(dados, rodadas, container);
        return;
    }

    // SISTEMA SUÍÇO
=======
    if (Number(formato) === 2) {
        renderizarChaveamentoDupla(rodadas, container);
        return;
    }

    if (Number(formato) === 3) {
        renderizarChaveamentoGrupos(rodadas, container);
        return;
    }

>>>>>>> origin/api_v1
    if (Number(formato) === 4) {
        renderizarChaveamentoSuico(rodadas, container);
        return;
    }

<<<<<<< HEAD
    // ELIMINATÓRIA SIMPLES
    const quantidadeTimes = contarTimes(dados);
    const quantidadeConfrontosInicial = rodadas[0].confrontos.length;

    const alturaTotal = calcularAlturaRodada(
        quantidadeConfrontosInicial
    );

    const bracket = document.createElement("div");
    bracket.className = "bracket";
=======
    renderizarChaveamentoSimples(dados, rodadas, container);
}

// ELIMINATÓRIA SIMPLES
function renderizarChaveamentoSimples(dados, rodadas, container) {
    const quantidadeTimes = contarTimes(dados);
    const quantidadeConfrontosInicial = rodadas[0].confrontos.length;
    const alturaTotal = calcularAlturaRodada(quantidadeConfrontosInicial);

    const bracket = document.createElement("div");
    bracket.className = "bracket bracket-simples";
>>>>>>> origin/api_v1
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

<<<<<<< HEAD
    container.appendChild(bracket);
}

// RENDERIZAR ELIMINATÓRIA DUPLA
function renderizarChaveamentoDupla(dados, rodadas, container) {
=======
    const ultimaRodada = rodadas.at(-1);

    if (rodadas.length > 1 && ultimaRodada.confrontos.length > 1) {
        console.warn(
            "A última rodada recebida contém vários confrontos. Verifique se a API retornou a final.",
            ultimaRodada
        );
    }

    container.appendChild(bracket);
}

// ELIMINATÓRIA DUPLA
function renderizarChaveamentoDupla(rodadas, container) {
>>>>>>> origin/api_v1
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
<<<<<<< HEAD
        const normalizado = String(nome || "")
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .toLowerCase()
            .replace(/[\s_-]/g, "");
=======
        const normalizado = normalizarNome(nome);
>>>>>>> origin/api_v1

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
<<<<<<< HEAD

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
=======
            partida.innerHTML = criarConteudoConfronto(confronto);
>>>>>>> origin/api_v1

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

<<<<<<< HEAD
    // CAMADA DE CONEXÕES
    const larguraCard = 190;

=======
    // CONEXÕES DA ELIMINATÓRIA DUPLA
    const larguraCard = 190;
>>>>>>> origin/api_v1
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

<<<<<<< HEAD
    // IDENTIFICAR PARTIDAS DE ORIGEM
    rodadas.forEach(rodada => {
        rodada.confrontos.forEach(confronto => {
            const destino = posicoesConfrontos.get(
                String(confronto.id)
            );
=======
    rodadas.forEach(rodada => {
        rodada.confrontos.forEach(confronto => {
            const destino = posicoesConfrontos.get(String(confronto.id));
>>>>>>> origin/api_v1

            if (!destino) return;

            const origens = new Set();

            const idsOrigem = [
                confronto.cd_confronto_anterior_a,
                confronto.cd_confronto_anterior_b
            ];

            confronto.participantes.forEach(participante => {
                idsOrigem.push(
                    participante.cd_confronto_anterior_a,
<<<<<<< HEAD
                    participante.cd_confronto_anterior_b
=======
                    participante.cd_confronto_anterior_b,
                    participante.origem_confronto
>>>>>>> origin/api_v1
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
<<<<<<< HEAD
                    "Verifique a posição das rodadas e a origem do confronto:",
                    confronto.id,
                    partidasOrigem,
                    destino
=======
                    "Verifique a posição das rodadas:",
                    confronto.id
>>>>>>> origin/api_v1
                );
                return;
            }

            const pontosOrigem = partidasOrigem.map(origem => ({
                x: origem.x + larguraCard,
                y: origem.y
            }));

<<<<<<< HEAD
            const maiorSaida = Math.max(
                ...pontosOrigem.map(ponto => ponto.x)
            );

            const meioX = maiorSaida +
                (destino.x - maiorSaida) / 2;
=======
            const maiorSaida = Math.max(...pontosOrigem.map(ponto => ponto.x));
            const meioX = maiorSaida + (destino.x - maiorSaida) / 2;
>>>>>>> origin/api_v1

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
<<<<<<< HEAD

            criarLinha(
                "saida",
                meioX,
                destino.y,
                destino.x,
                destino.y
            );
=======
            criarLinha("saida", meioX, destino.y, destino.x, destino.y);
>>>>>>> origin/api_v1
        });
    });

    container.appendChild(bracket);
}

<<<<<<< HEAD
// RENDERIZAR SISTEMA SUÍÇO
=======
// FASE DE GRUPOS + ELIMINATÓRIA

function renderizarChaveamentoGrupos(rodadas, container) {
    const bracket = document.createElement("div");
    bracket.className = "bracket-grupos";

    rodadas.forEach(rodada => {
        const coluna = document.createElement("section");
        coluna.className = "bracket-grupos-coluna";

        const titulo = document.createElement("h3");
        titulo.className = "round-title";
        titulo.textContent = rodada.nome || `Rodada ${rodada.numero}`;

        const lista = document.createElement("div");
        lista.className = "bracket-grupos-lista";

        rodada.confrontos.forEach(confronto => {
            const partida = document.createElement("div");
            partida.className = "partida";
            partida.dataset.confronto = confronto.id;
            partida.innerHTML = criarConteudoConfronto(confronto);

            lista.appendChild(partida);
        });

        coluna.append(titulo, lista);
        bracket.appendChild(coluna);
    });

    container.appendChild(bracket);
}


// SISTEMA SUÍÇO
>>>>>>> origin/api_v1
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
<<<<<<< HEAD

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
=======
            partida.innerHTML = criarConteudoConfronto(confronto);
>>>>>>> origin/api_v1

            area.appendChild(partida);
        });

<<<<<<< HEAD
        coluna.appendChild(titulo);
        coluna.appendChild(area);
=======
        coluna.append(titulo, area);
>>>>>>> origin/api_v1
        bracket.appendChild(coluna);
    });

    container.appendChild(bracket);
}

<<<<<<< HEAD
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
=======
// CONTEÚDO DE UM CONFRONTO
function criarConteudoConfronto(confronto) {
    const participante1 = confronto.participantes.find(
        participante => Number(participante.posicao) === 1
    );

    const participante2 = confronto.participantes.find(
        participante => Number(participante.posicao) === 2
    );

    return `
        ${criarParticipante(participante1)}
        ${criarParticipante(participante2)}
    `;
}

// AGRUPAR CONFRONTOS POR RODADA
function agruparRodadas(dados) {
    const mapaConfrontos = new Map();

    dados.forEach(item => {
        const id = Number(item.cd_confronto);

        if (!Number.isInteger(id) || id <= 0) return;

        if (!mapaConfrontos.has(id)) {
            mapaConfrontos.set(id, {
                id,
                numero: item.nr_confronto,
                nome: item.nome,
                status: item.status,
                vencedor: item.cd_vencedor
                    ? Number(item.cd_vencedor)
                    : null,
                numeroRodada: Number(item.nr_rodada),
                idRodada: item.cd_rodada ?? null,
                idEtapa: item.cd_etapa_competicao ?? null,
                nomeRodada: item.nm_rodada,
                cd_confronto_anterior_a: item.cd_confronto_anterior_a,
                cd_confronto_anterior_b: item.cd_confronto_anterior_b,
>>>>>>> origin/api_v1
                participantes: []
            });
        }

<<<<<<< HEAD
        rodada.confrontos.get(item.cd_confronto)
            .participantes.push(item);
=======
        mapaConfrontos.get(id).participantes.push(item);
    });

    // RESOLVER PARTICIPANTES DE CONFRONTOS ANTERIORES
    function resolverParticipante(participante, visitados = new Set()) {
        if (!participante) return participante;

        if (participante.tipo_origem === "TIME") {
            return participante;
        }

        if (participante.tipo_origem !== "CONFRONTO") {
            return participante;
        }

        const idOrigem = Number(participante.origem_confronto);

        if (!idOrigem || visitados.has(idOrigem)) {
            return participante;
        }

        const origem = mapaConfrontos.get(idOrigem);

        if (!origem || !origem.vencedor) {
            return participante;
        }

        const novosVisitados = new Set(visitados);
        novosVisitados.add(idOrigem);

        const times = origem.participantes
            .map(item => resolverParticipante(item, novosVisitados))
            .filter(item => item?.cd_time);

        const resultado = String(
            participante.resultado_confronto || "VENCEDOR"
        ).toUpperCase();

        const selecionado = resultado === "PERDEDOR"
            ? times.find(time => Number(time.cd_time) !== origem.vencedor)
            : times.find(time => Number(time.cd_time) === origem.vencedor);

        if (!selecionado) return participante;

        return {
            ...participante,
            cd_time: selecionado.cd_time,
            nm_time: selecionado.nm_time,
            path_escudo: selecionado.path_escudo,
            time_resolvido: true
        };
    }

    const mapaRodadas = new Map();

    mapaConfrontos.forEach(confronto => {
        const numero = confronto.numeroRodada;

        const chave = confronto.idRodada != null
            ? `rodada:${confronto.idRodada}`
            : confronto.idEtapa != null
                ? `etapa:${confronto.idEtapa}:rodada:${numero}`
                : `numero:${numero}`;

        if (!mapaRodadas.has(chave)) {
            mapaRodadas.set(chave, {
                numero,
                nome: confronto.nomeRodada || `Rodada ${numero}`,
                confrontos: []
            });
        }

        mapaRodadas.get(chave).confrontos.push({
            id: confronto.id,
            numero: confronto.numero,
            nome: confronto.nome,
            status: confronto.status,
            vencedor: confronto.vencedor,
            cd_confronto_anterior_a: confronto.cd_confronto_anterior_a,
            cd_confronto_anterior_b: confronto.cd_confronto_anterior_b,
            participantes: confronto.participantes.map(
                participante => resolverParticipante(participante)
            )
        });
>>>>>>> origin/api_v1
    });

    return [...mapaRodadas.values()]
        .sort((a, b) => a.numero - b.numero)
        .map(rodada => ({
            ...rodada,
<<<<<<< HEAD
            confrontos: [...rodada.confrontos.values()]
                .sort((a, b) => Number(a.numero) - Number(b.numero))
=======
            confrontos: rodada.confrontos.sort(
                (a, b) => Number(a.numero) - Number(b.numero)
            )
>>>>>>> origin/api_v1
        }));
}

// CALCULAR ALTURA DA RODADA
function calcularAlturaRodada(quantidadeConfrontos) {
    const alturaPartida = 76;
    const espacamento = 60;

    return quantidadeConfrontos * (alturaPartida + espacamento);
}

<<<<<<< HEAD
// CRIAR COLUNA DA RODADA
=======
// CRIAR COLUNA DA ELIMINATÓRIA SIMPLES
>>>>>>> origin/api_v1
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
<<<<<<< HEAD
                ${criarParticipante(confronto.participantes.find(
                    participante => Number(participante.posicao) === 1
                ))}
                ${criarParticipante(confronto.participantes.find(
                    participante => Number(participante.posicao) === 2
                ))}
=======
                ${criarConteudoConfronto(confronto)}
>>>>>>> origin/api_v1
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

<<<<<<< HEAD
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

=======
>>>>>>> origin/api_v1
// CRIAR PARTICIPANTE
function criarParticipante(participante) {
    if (!participante) {
        return `<div class="time">A definir</div>`;
    }

    let nome = "A definir";
    let imagem = "";

<<<<<<< HEAD
    if (participante.tipo_origem === "TIME") {
=======
    if (
        participante.tipo_origem === "TIME" ||
        participante.time_resolvido
    ) {
>>>>>>> origin/api_v1
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

<<<<<<< HEAD
// CRIAR CONEXÕES ENTRE RODADAS
=======
// CONEXÕES DA ELIMINATÓRIA SIMPLES
>>>>>>> origin/api_v1
function criarConexoes(quantidade, indiceRodada) {
    const fator = Math.pow(2, indiceRodada);
    const espacamento = 76;
    const alturaPartida = 60;
    const alturaTituloRodada = 55;
<<<<<<< HEAD

=======
>>>>>>> origin/api_v1
    const distancia = fator * espacamento;

    return Array.from({ length: quantidade }, (_, indice) => {
        const top =
            (indice * 2 * fator + (fator - 1) / 2) * espacamento
            + alturaTituloRodada;

        const meio = alturaPartida / 2 + distancia / 2;

        return `
<<<<<<< HEAD
            <div
                class="conexao"
                style="
=======
            <div class="conexao"
                 style="
>>>>>>> origin/api_v1
                    top: ${top}px;
                    height: ${distancia + alturaPartida}px;
                    --entrada-1: ${alturaPartida / 2}px;
                    --entrada-2: ${alturaPartida / 2 + distancia}px;
                    --saida-y: ${meio}px;
                    --vertical-h: ${distancia}px;
<<<<<<< HEAD
                "
            >
=======
                 ">
>>>>>>> origin/api_v1
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
<<<<<<< HEAD
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
=======
    const normalizado = normalizarNome(nome);

    if (
        normalizado.includes("16avos") ||
        normalizado.includes("dezesseisavos")
    ) {
        return "dezesseisavos";
    }

    if (normalizado.includes("oitavas")) return "oitavas";
    if (normalizado.includes("quartas")) return "quartas";
    if (normalizado.includes("semifinal")) return "semifinal";
    if (normalizado.includes("final")) return "final";
>>>>>>> origin/api_v1

    return "rodada";
}

<<<<<<< HEAD
// IDENTIFICAR CONEXÃO ENTRE RODADAS
function criarClasseConexao(rodadaAtual, proximaRodada) {
    const classeAtual = classeDaRodada(rodadaAtual.nome);
    const classeProxima = classeDaRodada(proximaRodada.nome);

    return `${classeAtual}-para-${classeProxima}`;
=======
// NORMALIZAR NOMES
function normalizarNome(valor) {
    return String(valor || "")
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase()
        .replace(/[\s_-]/g, "");
>>>>>>> origin/api_v1
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
<<<<<<< HEAD

=======
>>>>>>> origin/api_v1
        } catch (erro) {
            console.error("Erro ao gerar chaveamento:", erro);

            const mensagem = document.getElementById("mensagemChaveamento");

<<<<<<< HEAD
            mensagem.textContent =
                erro.message || "Não foi possível gerar o chaveamento.";

=======
            if (mensagem) {
                mensagem.textContent =
                    erro.message || "Não foi possível gerar o chaveamento.";
            }
>>>>>>> origin/api_v1
        } finally {
            botao.disabled = false;
        }
    });
}
