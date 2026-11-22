import { partidaService } from "../../../services/partidaService.js";
import { timeService } from "../../../services/timeService.js";
import { renderizarListaPartidas } from "../../../components/cardPartida.js";
import { obterIdTime } from "./utilitarios.js";

// CACHE DOS ESCUDOS
const cacheTimes = new Map();

async function buscarTime(id) {
    if (!cacheTimes.has(id)) {
        cacheTimes.set(
            id,
            timeService.buscarPorId(id).catch(() => null)
        );
    }

    return cacheTimes.get(id);
}

// FORMATAR DATA
function formatarData(partida) {
    const valor =
        partida.inicio_agendado ||
        partida.inicio_em ||
        partida.dt_partida;

    if (!valor) return "Data não informada";

    const texto = String(valor);
    const partes = texto.split(" ")[0].split("-");

    if (partes.length !== 3) return texto;

    const data = new Date(
        Number(partes[0]),
        Number(partes[1]) - 1,
        Number(partes[2])
    );

    const dataFormatada = new Intl.DateTimeFormat("pt-BR", {
        day: "2-digit",
        month: "short",
        year: "numeric"
    }).format(data);

    if (!texto.includes(":")) return dataFormatada;

    const horario = texto.split(" ")[1].slice(0, 5);

    return `${dataFormatada} · ${horario}`;
}

// CARREGAR PARTIDAS DO TIME
export async function carregarPartidasTime() {
    const idTime = obterIdTime();

    const container = document.getElementById("listaPartidas");
    const mensagem = document.getElementById("mensagensPartidas");

    if (!container || !idTime) return;

    try {
        if (mensagem) mensagem.textContent = "Carregando partidas...";

        const resposta = await partidaService.listarPorTime(idTime);

        const partidas = resposta?.itens ||
            (Array.isArray(resposta) ? resposta : []);

        const partidasCompletas = await Promise.all(
            partidas.map(async partida => {
                let detalhes = {};

                // BUSCAR PLACAR E DETALHES DA PARTIDA
                try {
                    detalhes = await partidaService.buscarAoVivo(
                        partida.cd_partida
                    );
                } catch (erro) {
                    console.warn(
                        `Não foi possível buscar detalhes da partida ${partida.cd_partida}:`,
                        erro
                    );
                }

                const timesBase =
                    detalhes?.times?.length
                        ? detalhes.times
                        : partida.times || [];

                // BUSCAR ESCUDOS
                const times = await Promise.all(
                    timesBase.map(async time => {
                        const dadosTime = await buscarTime(time.cd_time);

                        return {
                            ...time,
                            escudo:
                                dadosTime?.path_escudo ||
                                dadosTime?.imagem ||
                                null
                        };
                    })
                );

                const possuiPlacar = [
                    "FINALIZADA",
                    "EM_ANDAMENTO"
                ].includes(partida.status);

                return {
                    ...partida,
                    times,
                    timeA: times[0] || {},
                    timeB: times[1] || {},

                    placarA: possuiPlacar
                        ? detalhes?.placar_casa ?? null
                        : null,

                    placarB: possuiPlacar
                        ? detalhes?.placar_visitante ?? null
                        : null,

                    dataTexto: formatarData(partida),
                    local: partida.local_partida,
                    competicao: partida.nm_competicao
                };
            })
        );

        renderizarListaPartidas(container, partidasCompletas);

        if (mensagem) mensagem.textContent = "";

    } catch (erro) {
        console.error("Erro ao carregar partidas do time:", erro);

        if (mensagem) {
            mensagem.textContent = "Não foi possível carregar as partidas.";
        }
    }
}
