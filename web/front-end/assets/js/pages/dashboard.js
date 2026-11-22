import { apiFetch } from "../api/api.js";

import { authService } from "../services/authService.js";
import { timeService } from "../services/timeService.js";
import { alunoService } from "../services/alunoService.js";
import { competicaoService } from "../services/competicaoService.js";
import { partidaService } from "../services/partidaService.js";

import { renderizarListaPartidas } from "../components/cardPartida.js";

// ELEMENTOS DO DASHBOARD
const nomeUsuario = document.getElementById("nomeCompeticao");
const dataAtual = document.getElementById("dataAtualDashboard");

const totalTimes = document.getElementById("totalTimes");
const totalCompeticoes = document.getElementById("totalCompeticoes");
const totalAtletas = document.getElementById("totalAtletas");
const totalPartidas = document.getElementById("totalPartidas");

const listaPartidas = document.getElementById("listaPartidasDashboard");
const listaRanking = document.getElementById("listaRankingDashboard");
const mensagemCalendario = document.getElementById("mensagemCalendario");

let calendarioDashboard = null;
let partidasDashboard = [];
let partidasExpandidas = false;

const LIMITE_PARTIDAS_INICIAL = 2;
const LIMITE_PARTIDAS_CARREGADAS = 6;

// =========================================================
// TRATAMENTO DAS RESPOSTAS
// =========================================================

function obterDados(resposta) {
    if (resposta?.sucesso === false || resposta?.success === false) {
        throw new Error(
            resposta.mensagem ||
            resposta.message ||
            "Não foi possível carregar os dados."
        );
    }

    return resposta?.data ?? resposta?.dados ?? resposta;
}

function obterLista(dados, chaves = []) {
    if (Array.isArray(dados)) {
        return dados;
    }

    for (const chave of [...chaves, "itens", "resultados", "dados"]) {
        if (Array.isArray(dados?.[chave])) {
            return dados[chave];
        }
    }

    return [];
}

function mostrarMensagem(container, mensagem, classe = "dashboard-estado") {
    if (!container) return;

    const texto = document.createElement("p");
    texto.className = classe;
    texto.textContent = mensagem;

    container.replaceChildren(texto);
}

// =========================================================
// DATAS
// =========================================================

function converterData(valor) {
    if (!valor) return null;

    const texto = String(valor).trim();

    // Trata datas sem horário como data local para evitar mudança de dia por fuso.
    const correspondencia = texto.match(
        /^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2})(?::(\d{2}))?)?$/
    );

    if (correspondencia) {
        const [, ano, mes, dia, hora = "0", minuto = "0", segundo = "0"] =
            correspondencia;

        const data = new Date(
            Number(ano),
            Number(mes) - 1,
            Number(dia),
            Number(hora),
            Number(minuto),
            Number(segundo)
        );

        return Number.isNaN(data.getTime()) ? null : data;
    }

    const data = new Date(texto);
    return Number.isNaN(data.getTime()) ? null : data;
}

function formatarData(valor) {
    const data = converterData(valor);

    if (!data) {
        return "Data não informada";
    }

    const possuiHorario = /[T ]\d{2}:\d{2}/.test(String(valor));

    if (!possuiHorario) {
        return data.toLocaleDateString("pt-BR", {
            day: "2-digit",
            month: "2-digit",
            year: "numeric"
        });
    }

    return data.toLocaleString("pt-BR", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit"
    });
}

function atualizarDataAtual() {
    if (!dataAtual) return;

    const texto = dataAtual.querySelector("span");
    if (!texto) return;

    texto.textContent = new Date().toLocaleDateString("pt-BR", {
        weekday: "long",
        day: "2-digit",
        month: "long",
        year: "numeric"
    });
}

function obterInicioPartida(partida) {
    return (
        partida?.inicio_agendado ??
        partida?.inicio_em ??
        partida?.dt_partida ??
        null
    );
}

function ordenarPartidasPorData(partidas) {
    return [...partidas].sort((a, b) => {
        const dataA = converterData(obterInicioPartida(a))?.getTime() ?? Infinity;
        const dataB = converterData(obterInicioPartida(b))?.getTime() ?? Infinity;

        return dataA - dataB;
    });
}

// =========================================================
// USUÁRIO
// =========================================================

async function carregarUsuario() {
    if (!nomeUsuario) return;

    try {
        const resposta = await authService.me();
        const usuario = obterDados(resposta);

        const nome = String(
            usuario?.nome ??
            usuario?.nm_usuario ??
            usuario?.usuario?.nome ??
            ""
        ).trim();

        const primeiroNome = nome.split(/\s+/)[0];

        nomeUsuario.textContent = primeiroNome
            ? `Bem-vindo, ${primeiroNome}!`
            : "Bem-vindo!";
    } catch (erro) {
        console.error("Erro ao carregar usuário:", erro);
        nomeUsuario.textContent = "Bem-vindo!";
    }
}

// =========================================================
// INDICADORES
// =========================================================

async function carregarIndicador(servico, elemento, chaves = []) {
    if (!elemento) return;

    try {
        const resposta = await servico();
        const dados = obterDados(resposta);
        const itens = obterLista(dados, chaves);

        const total = dados?.total ?? dados?.total_registros;

        elemento.textContent =
            total !== null &&
            total !== undefined &&
            Number.isFinite(Number(total))
                ? Number(total).toLocaleString("pt-BR")
                : itens.length.toLocaleString("pt-BR");
    } catch (erro) {
        console.error("Erro ao carregar indicador:", erro);
        elemento.textContent = "—";
    }
}

// =========================================================
// ESCUDOS DOS TIMES
// =========================================================

async function carregarEscudos(partidas) {
    const ids = [
        ...new Set(
            partidas.flatMap((partida) =>
                (partida.times ?? [])
                    .filter((time) => !time.path_escudo)
                    .map((time) => Number(time.cd_time))
                    .filter((id) => Number.isInteger(id) && id > 0)
            )
        )
    ];

    const escudos = new Map();

    await Promise.all(
        ids.map(async (id) => {
            try {
                const resposta = await timeService.buscarPorId(id);
                const dados = obterDados(resposta);
                const time = dados?.time ?? dados;

                escudos.set(id, time?.path_escudo ?? null);
            } catch (erro) {
                console.error(`Erro ao carregar escudo do time ${id}:`, erro);
                escudos.set(id, null);
            }
        })
    );

    return partidas.map((partida) => ({
        ...partida,
        times: (partida.times ?? []).map((time) => ({
            ...time,
            path_escudo:
                escudos.get(Number(time.cd_time)) ??
                time.path_escudo ??
                null
        }))
    }));
}

// =========================================================
// PLACARES DAS PARTIDAS
// =========================================================

async function carregarPlacares(partidas) {
    return Promise.all(
        partidas.map(async (partida) => {
            if (!partida.cd_partida) {
                return partida;
            }

            // O endpoint de calendário já entrega os placares em lote.
            // Mantém o fallback para respostas antigas que ainda não os tragam.
            if (
                partida.placar_casa !== null &&
                partida.placar_casa !== undefined &&
                partida.placar_visitante !== null &&
                partida.placar_visitante !== undefined
            ) {
                return {
                    ...partida,
                    placarA: partida.placar_casa,
                    placarB: partida.placar_visitante
                };
            }

            try {
                const resposta = await apiFetch(
                    `/partidas/${partida.cd_partida}`
                );

                const dados = obterDados(resposta);
                const detalhe = dados?.partida ?? dados;

                return {
                    ...partida,
                    placarA: detalhe?.placar_casa ?? null,
                    placarB: detalhe?.placar_visitante ?? null
                };
            } catch (erro) {
                console.error(
                    `Erro ao buscar placar da partida ${partida.cd_partida}:`,
                    erro
                );

                return partida;
            }
        })
    );
}

// =========================================================
// NORMALIZAÇÃO DAS PARTIDAS
// =========================================================

function normalizarPartida(partida) {
    const times = Array.isArray(partida.times) ? partida.times : [];

    const mandante = times.find((time) =>
        time.mandante === true ||
        time.mandante === "t" ||
        time.mandante === "true" ||
        time.mandante === 1
    ) ?? times[0];

    const visitante =
        times.find((time) => time !== mandante) ??
        times[1];

    const inicio = obterInicioPartida(partida);

    return {
        ...partida,
        dataTexto: formatarData(inicio),

        timeA: {
            nome: mandante?.nm_time ?? "A definir",
            escudo: mandante?.path_escudo ?? null
        },

        timeB: {
            nome: visitante?.nm_time ?? "A definir",
            escudo: visitante?.path_escudo ?? null
        },

        placarA: partida.placarA ?? null,
        placarB: partida.placarB ?? null,

        local: partida.local_partida ?? "Local não informado",
        competicao: partida.nm_competicao ?? "Competição não informada"
    };
}

// =========================================================
// CALENDÁRIO FULLCALENDAR
// =========================================================

function formatarDataCalendario(valor) {
    if (!valor) return null;

    const texto = String(valor).trim();

    if (/^\d{4}-\d{2}-\d{2}$/.test(texto)) {
        return texto;
    }

    return texto.replace(" ", "T");
}

function obterStatusCalendario(status) {
    const valor = String(status ?? "").toUpperCase();

    if (valor === "EM_ANDAMENTO" || valor === "EM ANDAMENTO") {
        return {
            classe: "em-andamento",
            texto: "Em andamento"
        };
    }

    if (
        valor === "FINALIZADA" ||
        valor === "FINALIZADO" ||
        valor === "ENCERRADA" ||
        valor === "ENCERRADO"
    ) {
        return {
            classe: "finalizada",
            texto: "Finalizada"
        };
    }

    if (valor === "CANCELADA" || valor === "CANCELADO") {
        return {
            classe: "cancelada",
            texto: "Cancelada"
        };
    }

    return {
        classe: "agendada",
        texto: "Agendada"
    };
}

function criarEventosCalendario(partidas) {
    return partidas.flatMap((partida) => {
        const inicio = formatarDataCalendario(obterInicioPartida(partida));

        if (!inicio) {
            return [];
        }

        const normalizada = normalizarPartida(partida);
        const status = obterStatusCalendario(partida.status);

        const evento = {
            id: String(partida.cd_partida),
            title: `${normalizada.timeA.nome} x ${normalizada.timeB.nome}`,
            start: inicio,
            allDay: /^\d{4}-\d{2}-\d{2}$/.test(inicio),

            classNames: [
                "evento-partida",
                `evento-partida--${status.classe}`
            ],

            extendedProps: {
                partidaId: partida.cd_partida,
                competicao: normalizada.competicao,
                local: normalizada.local,
                status: status.texto
            }
        };

        const fim = formatarDataCalendario(partida.fim_em);

        if (fim) {
            evento.end = fim;
        }

        return [evento];
    });
}

function abrirDetalhesCalendario(info) {
    const evento = info.event;
    const detalhes = evento.extendedProps;

    const dialog = document.getElementById("modalPartidaCalendario");
    const dataElemento = document.getElementById("modalCalendarioData");

    document.getElementById("modalCalendarioTitulo").textContent =
        evento.title || "Partida";

    if (dataElemento) {
        dataElemento.textContent = evento.start
            ? evento.start.toLocaleString("pt-BR", {
                dateStyle: "short",
                ...(evento.allDay ? {} : { timeStyle: "short" })
            })
            : "Data não informada";
    }

    document.getElementById("modalCalendarioCompeticao").textContent =
        `Competição: ${detalhes.competicao ?? "Não informada"}`;

    document.getElementById("modalCalendarioLocal").textContent =
        `Local: ${detalhes.local ?? "Não informado"}`;

    document.getElementById("modalCalendarioStatus").textContent =
        `Status: ${detalhes.status ?? "Não informado"}`;

    if (dialog?.showModal && !dialog.open) {
        dialog.showModal();
    }
}

function configurarModalCalendario() {
    const dialog = document.getElementById("modalPartidaCalendario");

    const botoesFechar = [
        document.getElementById("fecharModalCalendario"),
        document.getElementById("fecharModalCalendarioSecundario")
    ];

    botoesFechar.forEach((botao) => {
        botao?.addEventListener("click", () => dialog?.close());
    });

    dialog?.addEventListener("click", (evento) => {
        if (evento.target === dialog) {
            dialog.close();
        }
    });
}

function inicializarCalendario() {
    const elemento = document.getElementById("calendarioDashboard");

    if (!elemento) return;

    if (!window.FullCalendar?.Calendar) {
        if (mensagemCalendario) {
            mensagemCalendario.textContent =
                "Não foi possível carregar o calendário. Atualize a página e tente novamente.";
        }

        return;
    }

    const telaPequena = window.matchMedia("(max-width: 650px)").matches;

    calendarioDashboard = new window.FullCalendar.Calendar(elemento, {
        locale: "pt-br",
        initialView: "dayGridMonth",

        height: "auto",
        contentHeight: "auto",
        expandRows: true,
        fixedWeekCount: false,
        showNonCurrentDates: true,
        firstDay: 1,

        headerToolbar: {
            left: "prev,next today",
            center: "title",
            right: telaPequena
                ? "dayGridMonth,listWeek"
                : "dayGridMonth,timeGridWeek,listWeek"
        },

        buttons: {
            today: { text: "Hoje", display: "text" },
            dayGridMonth: { text: "Mês", display: "text" },
            timeGridWeek: { text: "Semana", display: "text" },
            listWeek: { text: "Agenda", display: "text" }
        },

        dayMaxEvents: 3,
        eventDisplay: "block",
        displayEventTime: false,

        dayCellTopContent(info) {
            return String(info.date.getDate());
        },

        nowIndicator: true,

        eventTimeFormat: {
            hour: "2-digit",
            minute: "2-digit",
            hour12: false
        },

        events: [],
        eventClick: abrirDetalhesCalendario,

        eventDidMount(info) {
            info.el.title = info.event.title;
        },

        noEventsContent: "Nenhuma partida neste período",

        popoverClass: "dashboard-calendario-popover",

        popoverCloseClass: "dashboard-calendario-popover-close",

        dayHeaderClass(info) {
            return info.inPopover
                ? "dashboard-calendario-popover-header"
                : "";
        },

        dayCellClass(info) {
            return info.inPopover
                ? "dashboard-calendario-popover-body"
                : "";
        },
    });

    calendarioDashboard.render();
    configurarModalCalendario();
}

// =========================================================
// PRÓXIMAS PARTIDAS
// =========================================================

function renderizarPartidasDashboard() {
    if (!listaPartidas) return;

    const partidasVisiveis = partidasExpandidas
        ? partidasDashboard
        : partidasDashboard.slice(0, LIMITE_PARTIDAS_INICIAL);

    renderizarListaPartidas(listaPartidas, partidasVisiveis);

    if (partidasDashboard.length <= LIMITE_PARTIDAS_INICIAL) {
        return;
    }

    const botao = document.createElement("button");
    botao.type = "button";
    botao.className = "dashboard-mostrar-mais";
    botao.setAttribute("aria-expanded", String(partidasExpandidas));

    const icone = document.createElement("i");
    icone.className = partidasExpandidas
        ? "bi bi-chevron-up"
        : "bi bi-chevron-down";
    icone.setAttribute("aria-hidden", "true");

    const texto = document.createElement("span");
    texto.textContent = partidasExpandidas
        ? "Mostrar menos"
        : "Mostrar mais";

    botao.append(icone, texto);
    botao.addEventListener("click", () => {
        partidasExpandidas = !partidasExpandidas;
        renderizarPartidasDashboard();
    });

    listaPartidas.appendChild(botao);
}

async function carregarPartidas() {
    if (!listaPartidas) return;

    try {
        const resposta = await partidaService.listarCalendario({
            limite: 100
        });

        const dados = obterDados(resposta);
        const partidas = ordenarPartidasPorData(
            obterLista(dados, ["partidas"])
        );

        // Alimenta o calendário com as partidas recebidas.
        if (calendarioDashboard) {
            calendarioDashboard.removeAllEvents();
            calendarioDashboard.addEventSource(
                criarEventosCalendario(partidas)
            );
        }

        if (mensagemCalendario) {
            mensagemCalendario.textContent = "";
        }

        if (totalPartidas) {
            const total = dados?.total ?? dados?.total_registros;

            totalPartidas.textContent =
                total !== null && total !== undefined
                    ? Number(total).toLocaleString("pt-BR")
                    : partidas.length.toLocaleString("pt-BR");
        }

        if (!partidas.length) {
            mostrarMensagem(
                listaPartidas,
                "Nenhuma partida encontrada no momento."
            );
            return;
        }

        const partidasSelecionadas = partidas.slice(
            0,
            LIMITE_PARTIDAS_CARREGADAS
        );

        const [partidasComEscudos, partidasComPlacares] =
            await Promise.all([
                carregarEscudos(partidasSelecionadas),
                carregarPlacares(partidasSelecionadas)
            ]);

        const placaresPorId = new Map(
            partidasComPlacares.map((partida) => [
                Number(partida.cd_partida),
                partida
            ])
        );

        partidasDashboard = partidasComEscudos.map((partida) => {
            const detalhes = placaresPorId.get(
                Number(partida.cd_partida)
            );

            return normalizarPartida({
                ...partida,
                placarA: detalhes?.placarA ?? null,
                placarB: detalhes?.placarB ?? null
            });
        });

        partidasExpandidas = false;
        renderizarPartidasDashboard();
    } catch (erro) {
        console.error("Erro ao carregar partidas:", erro);

        if (totalPartidas) {
            totalPartidas.textContent = "—";
        }

        if (mensagemCalendario) {
            mensagemCalendario.textContent =
                "Não foi possível carregar as partidas do calendário.";
        }

        mostrarMensagem(
            listaPartidas,
            "Não foi possível carregar as partidas."
        );
    }
}

// =========================================================
// RANKING
// =========================================================

function criarItemRanking(equipe, indice) {
    const item = document.createElement("div");
    item.className = "dashboard-ranking-item";

    const posicao = document.createElement("span");
    posicao.className = "dashboard-ranking-posicao";
    posicao.textContent = `${indice + 1}º`;

    const informacoes = document.createElement("div");
    informacoes.className = "dashboard-ranking-info";

    const nome = document.createElement("strong");
    nome.textContent =
        equipe.time ??
        equipe.nm_time ??
        equipe.nome ??
        "Equipe não informada";

    const escola = document.createElement("span");
    escola.textContent =
        equipe.escola ??
        equipe.nm_escola ??
        "Escola não informada";

    informacoes.append(nome, escola);

    const pontos = document.createElement("strong");
    pontos.className = "dashboard-ranking-pontos";
    pontos.textContent = `${equipe.pontos ?? equipe.total_pontos ?? 0} pts`;

    item.append(posicao, informacoes, pontos);

    return item;
}

async function carregarRanking() {
    if (!listaRanking) return;

    try {
        const resposta = await apiFetch("/rankings/equipes?limite=3");
        const dados = obterDados(resposta);
        const equipes = obterLista(dados, ["equipes", "ranking"]);

        listaRanking.replaceChildren();

        if (!equipes.length) {
            mostrarMensagem(
                listaRanking,
                "Nenhuma equipe classificada no momento."
            );
            return;
        }

        equipes.slice(0, 3).forEach((equipe, indice) => {
            listaRanking.appendChild(
                criarItemRanking(equipe, indice)
            );
        });
    } catch (erro) {
        console.error("Erro ao carregar ranking:", erro);

        mostrarMensagem(
            listaRanking,
            "Não foi possível carregar o ranking."
        );
    }
}

// =========================================================
// INICIALIZAÇÃO
// =========================================================

async function inicializarDashboard() {
    atualizarDataAtual();
    inicializarCalendario();

    await Promise.all([
        carregarUsuario(),

        carregarIndicador(
            () => timeService.listar(),
            totalTimes,
            ["times"]
        ),

        carregarIndicador(
            () => competicaoService.listar(),
            totalCompeticoes,
            ["competicoes"]
        ),

        carregarIndicador(
            () => alunoService.listar(),
            totalAtletas,
            ["alunos"]
        ),

        carregarPartidas(),
        carregarRanking()
    ]);
}

inicializarDashboard();
