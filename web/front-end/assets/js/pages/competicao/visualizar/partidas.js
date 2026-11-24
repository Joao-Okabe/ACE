import { competicaoService } from "../../../services/competicaoService.js";
import { partidaService } from "../../../services/partidaService.js";
import { alunoService } from "../../../services/alunoService.js";
import { vinculoTimeService } from "../../../services/vinculoTimeService.js";
import { idCompeticao } from "./competicao.js";
import { renderizarListaPartidas } from "../../../components/cardPartida.js";
import { timeService } from "../../../services/timeService.js";

// CADASTRAR PARTIDA
export function configurarModalPartida() {
    const botaoAbrir = document.getElementById("abrirModalPartida");
    const modal = document.getElementById("modalCadastrarPartida");
    const tipo = document.getElementById("tipoCadastroPartida");
    const campoConfronto = document.getElementById("campoConfronto");
    const campoTime1 = document.getElementById("campoTime1");
    const campoTime2 = document.getElementById("campoTime2");
    const form = document.getElementById("formCadastrarPartida");

    if (!botaoAbrir || !modal || !tipo || !form || botaoAbrir.dataset.configurado) {
        return;
    }

    botaoAbrir.dataset.configurado = "true";

    // ABRIR MODAL DE CADASTRO
    botaoAbrir.addEventListener("click", () => {
        if (typeof modal.abrir === "function") {
            modal.abrir();
        } else {
            console.error("O componente app-modal não possui o método abrir().");
        }
    });

    // MOSTRAR CAMPOS CONFORME O TIPO DE CADASTRO
    function atualizarCampos() {
        const manual = tipo.value === "MANUAL";

        campoConfronto.hidden = manual;
        campoTime1.hidden = !manual;
        campoTime2.hidden = !manual;

        const selectConfronto = document.getElementById("confrontoPartida");
        const selectTime1 = document.getElementById("timePartida1");
        const selectTime2 = document.getElementById("timePartida2");

        selectConfronto.required = !manual;
        selectTime1.required = manual;
        selectTime2.required = manual;

        selectConfronto.disabled = manual;
        selectTime1.disabled = !manual;
        selectTime2.disabled = !manual;
    }

    tipo.addEventListener("change", atualizarCampos);
    atualizarCampos();

    // CADASTRAR PARTIDA
    form.addEventListener("submit", async evento => {
        evento.preventDefault();

        const mensagem = document.getElementById("mensagemCadastrarPartida");
        const botaoSalvar = document.getElementById("botaoSalvarPartida");
        const manual = tipo.value === "MANUAL";

        const data = document.getElementById("dataPartida").value;
        const horario = document.getElementById("horarioPartida").value;
        const time1 = document.getElementById("timePartida1").value;
        const time2 = document.getElementById("timePartida2").value;

        if (manual && time1 === time2) {
            mensagem.textContent = "Selecione dois times diferentes.";
            return;
        }

        const dados = {
            cd_competicao: Number(idCompeticao),
            cd_confronto: manual
                ? null
                : Number(document.getElementById("confrontoPartida").value),
            cd_times: manual
                ? [Number(time1), Number(time2)]
                : [],
            dt_partida: data,
            inicio_em: horario ? `${data}T${horario}` : null,
            local_partida: document.getElementById("localPartida").value.trim(),
            observacoes: document.getElementById("observacoesPartida").value.trim()
        };

        try {
            botaoSalvar.disabled = true;
            mensagem.textContent = "Cadastrando partida...";

            await partidaService.criar(dados);

            mensagem.textContent = "Partida cadastrada com sucesso!";

            form.reset();
            atualizarCampos();

            if (typeof modal.fechar === "function") {
                modal.fechar();
            }

            await carregarPartidas();

        } catch (erro) {
            console.error("Erro ao cadastrar partida:", erro);
            mensagem.textContent =
                erro.message || "Não foi possível cadastrar a partida.";
        } finally {
            botaoSalvar.disabled = false;
        }
    });
}



// CONFIGURAR MODAL DE ESCALAÇÃO DE ÁRBITRO
function configurarModalArbitragemPartida() {
    const modal = document.getElementById("modalArbitragemPartida");
    const form = document.getElementById("formArbitragemPartida");

    if (!modal || !form || form.dataset.configurado) return;

    form.dataset.configurado = "true";

    form.addEventListener("submit", async evento => {
        evento.preventDefault();

        const id = document.getElementById("arbitragemIdPartida").value;
        const arbitroId = document.getElementById("arbitroPartida").value;
        const funcao = document.getElementById("funcaoArbitroPartida").value;

        const mensagem = document.getElementById("mensagemArbitragemPartida");
        const botao = document.getElementById("botaoSalvarArbitragem");

        try {
            botao.disabled = true;
            mensagem.textContent = "Registrando escalação...";

            await partidaService.registrarArbitragem(
                id,
                arbitroId,
                funcao
            );

            mensagem.textContent = "Árbitro escalado com sucesso!";

            form.reset();
            modal.fechar();

            await carregarPartidas();

        } catch (erro) {
            console.error("Erro ao escalar árbitro:", erro);
            mensagem.textContent =
                erro.message || "Não foi possível escalar o árbitro.";
        } finally {
            botao.disabled = false;
        }
    });
}

// CONFIGURAR SELECTS DE TIMES E CONFRONTOS
export function configurarCadastroPartida(times = [], chaveamento = []) {
    if (
        window.usuarioVisitante === true ||
        window.podeGerenciarCompeticao !== true
    ) return;

    const selectTime1 = document.getElementById("timePartida1");
    const selectTime2 = document.getElementById("timePartida2");
    const selectConfronto = document.getElementById("confrontoPartida");

    [selectTime1, selectTime2].forEach(select => {
        select.replaceChildren(new Option("Selecione um time", ""));

        times.forEach(time => {
            select.add(new Option(time.nm_time, time.cd_time));
        });
    });

    selectConfronto.replaceChildren(new Option("Selecione um confronto", ""));

    const confrontos = new Map();

    chaveamento.forEach(item => {
        if (!confrontos.has(item.cd_confronto)) {
            confrontos.set(item.cd_confronto, []);
        }

        confrontos.get(item.cd_confronto).push(item);
    });

    confrontos.forEach((participantes, id) => {
        const definidos = participantes.filter(p =>
            p.tipo_origem === "TIME" &&
            p.status_participante === "DEFINIDO" &&
            p.cd_time
        );

        if (definidos.length !== 2) return;

        const nome = definidos.map(p => p.nm_time).join(" × ");
        const rodada = participantes[0].nm_rodada || "Etapa";

        selectConfronto.add(new Option(
            `${rodada} - ${nome}`,
            id
        ));
    });
}


// CARREGAR ATLETAS DA PARTIDA
let alunosPartidaCache = null;

async function obterAlunosPartida() {
    if (!alunosPartidaCache) {
        alunosPartidaCache = alunoService.listar().then(resposta =>
            Array.isArray(resposta)
                ? resposta
                : resposta?.itens || []
        ).catch(erro => {
            alunosPartidaCache = null;
            throw erro;
        });
    }

    return alunosPartidaCache;
}

async function carregarAtletasDoTime(idTime, selectAtleta, mensagem) {
    selectAtleta.replaceChildren(
        new Option("Carregando atletas...", "")
    );
    selectAtleta.disabled = true;

    if (!idTime) {
        selectAtleta.replaceChildren(
            new Option("Selecione primeiro o time", "")
        );
        return;
    }

    try {
        const [respostaIntegrantes, alunos] = await Promise.all([
            vinculoTimeService.listarIntegrantes(idTime),
            obterAlunosPartida()
        ]);

        const integrantes = Array.isArray(respostaIntegrantes)
            ? respostaIntegrantes
            : respostaIntegrantes?.itens || [];

        const alunosPorUsuario = new Map(
            alunos.map(aluno => [
                String(aluno.cd_usuario),
                aluno
            ])
        );

        selectAtleta.replaceChildren(
            new Option("Selecione um atleta", "")
        );

        integrantes.forEach(integrante => {
            const aluno = alunosPorUsuario.get(
                String(integrante.cd_usuario)
            );

            if (!aluno) return;

            const nome = integrante.nm_usuario ||
                aluno.nm_usuario ||
                aluno.nome ||
                `Aluno ${aluno.cd_aluno}`;

            const camisa = integrante.numero_camisa
                ? ` - Camisa ${integrante.numero_camisa}`
                : "";

            selectAtleta.add(
                new Option(
                    `${nome}${camisa}`,
                    aluno.cd_aluno
                )
            );
        });

        selectAtleta.disabled = false;

        if (selectAtleta.options.length === 1) {
            mensagem.textContent =
                "Nenhum atleta cadastrado neste time.";
        }

    } catch (erro) {
        console.error("Erro ao carregar atletas:", erro);

        selectAtleta.replaceChildren(
            new Option("Erro ao carregar atletas", "")
        );

        mensagem.textContent =
            erro.message || "Não foi possível carregar os atletas.";
    }
}

// CARREGAR TIMES DA PARTIDA
async function carregarTimesDaPartida(idPartida, selectTime, selectAtleta, mensagem) {
    selectTime.replaceChildren(
        new Option("Carregando times...", "")
    );

    selectTime.disabled = true;

    try {
        const resposta = await partidaService.buscarAoVivo(idPartida);
        const partida = resposta?.partida || resposta;

        const times = Array.isArray(partida?.times)
            ? partida.times
            : [];

        selectTime.replaceChildren(
            new Option("Selecione o time", "")
        );

        times.forEach(time => {
            selectTime.add(
                new Option(time.nm_time, time.cd_time)
            );
        });

        selectTime.disabled = false;

        selectAtleta.replaceChildren(
            new Option("Selecione primeiro o time", "")
        );

        selectAtleta.disabled = true;

        if (times.length === 0) {
            mensagem.textContent =
                "Não foram encontrados times nesta partida.";
        }

    } catch (erro) {
        console.error("Erro ao carregar times:", erro);

        mensagem.textContent =
            erro.message || "Não foi possível carregar os times.";

        selectTime.replaceChildren(
            new Option("Erro ao carregar times", "")
        );
    }
}

// CONFIGURAÇÃO DOS MODAIS DE EVENTOS
function configurarModalEventosPartida() {
    const configuracoes = [
        {
            tipo: "pontuacao",
            modal: "modalPontuacaoPartida",
            form: "formPontuacaoPartida",
            id: "pontuacaoIdPartida",
            time: "timePontuacaoPartida",
            atleta: "atletaPontuacaoPartida",
            minuto: "minutoPontuacaoPartida",
            observacao: "observacaoPontuacaoPartida",
            mensagem: "mensagemPontuacaoPartida",
            botao: "botaoSalvarPontuacao"
        },
        {
            tipo: "penalidade",
            modal: "modalPenalidadePartida",
            form: "formPenalidadePartida",
            id: "penalidadeIdPartida",
            time: "timePenalidadePartida",
            atleta: "atletaPenalidadePartida",
            minuto: "minutoPenalidadePartida",
            observacao: "observacaoPenalidadePartida",
            mensagem: "mensagemPenalidadePartida",
            botao: "botaoSalvarPenalidade"
        }
    ];

    configuracoes.forEach(config => {
        const form = document.getElementById(config.form);
        const selectTime = document.getElementById(config.time);
        const selectAtleta = document.getElementById(config.atleta);

        if (!form || !selectTime || !selectAtleta ||
            form.dataset.configurado) return;

        form.dataset.configurado = "true";

        selectTime.addEventListener("change", () => {
            const mensagem = document.getElementById(config.mensagem);

            mensagem.textContent = "";

            carregarAtletasDoTime(
                selectTime.value,
                selectAtleta,
                mensagem
            );
        });

        form.addEventListener("submit", async evento => {
            evento.preventDefault();

            const id = document.getElementById(config.id).value;
            const timeId = selectTime.value;
            const alunoId = selectAtleta.value;
            const minuto = document.getElementById(config.minuto).value;
            const observacao = document.getElementById(config.observacao).value;

            const mensagem = document.getElementById(config.mensagem);
            const botao = document.getElementById(config.botao);

            const dados = {
                timeId: Number(timeId),
                alunoId: Number(alunoId),
                minuto,
                observacao
            };

            if (config.tipo === "pontuacao") {
                dados.pontos = Number(
                    document.getElementById("pontosPartida").value
                );
            } else {
                dados.tipo = document.getElementById(
                    "tipoPenalidadePartida"
                ).value;
            }

            try {
                botao.disabled = true;
                mensagem.textContent = "Registrando evento...";

                if (config.tipo === "pontuacao") {
                    await partidaService.registrarPontuacao(id, dados);
                } else {
                    await partidaService.registrarPenalidade(id, dados);
                }

                mensagem.textContent = "Evento registrado com sucesso!";

                document.getElementById(config.modal).fechar();

                await carregarPartidas();

            } catch (erro) {
                console.error("Erro ao registrar evento:", erro);

                mensagem.textContent =
                    erro.message || "Não foi possível registrar o evento.";
            } finally {
                botao.disabled = false;
            }
        });
    });
}

// ABRIR MODAL DE EVENTO
function abrirModalEventoPartida(tipo, idPartida) {
    const prefixo = tipo === "pontuacao"
        ? "Pontuacao"
        : "Penalidade";

    const modal = document.getElementById(
        `modal${prefixo}Partida`
    );

    const form = document.getElementById(
        `form${prefixo}Partida`
    );

    const selectTime = document.getElementById(
        `time${prefixo}Partida`
    );

    const selectAtleta = document.getElementById(
        `atleta${prefixo}Partida`
    );

    const mensagem = document.getElementById(
        `mensagem${prefixo}Partida`
    );

    form.reset();

    document.getElementById(
        `${prefixo.toLowerCase()}IdPartida`
    ).value = idPartida;

    mensagem.textContent = "";

    selectAtleta.replaceChildren(
        new Option("Selecione primeiro o time", "")
    );

    selectAtleta.disabled = true;

    modal.abrir();

    carregarTimesDaPartida(
        idPartida,
        selectTime,
        selectAtleta,
        mensagem
    );
}

// CACHE DOS TIMES
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
    const valor = partida.inicio_agendado ||
        partida.inicio_em ||
        partida.dt_partida;

    if (!valor) return "Data não informada";

    const texto = String(valor).replace("T", " ");
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

    return `${dataFormatada} · ${texto.split(" ")[1].slice(0, 5)}`;
}

// PREPARAR PARTIDAS PARA O COMPONENTE
async function prepararPartidas(partidas) {
    return Promise.all(partidas.map(async partida => {
        let detalhes = {};

        try {
            detalhes = await partidaService.buscarAoVivo(partida.cd_partida);
        } catch (erro) {
            console.warn("Erro ao buscar detalhes da partida:", erro);
        }

        const dados = detalhes?.partida || detalhes;

        const timesBase = dados?.times?.length
            ? dados.times
            : partida.times || [];

        const times = await Promise.all(timesBase.map(async time => {
            const dadosTime = await buscarTime(time.cd_time);

            return {
                ...time,
                escudo: dadosTime?.path_escudo ||
                    dadosTime?.imagem ||
                    null
            };
        }));

        const possuiPlacar = ["EM_ANDAMENTO", "FINALIZADA"]
            .includes(partida.status);

        return {
            ...partida,
            times,
            timeA: times[0] || {},
            timeB: times[1] || {},
            placarA: possuiPlacar ? dados?.placar_casa ?? null : null,
            placarB: possuiPlacar ? dados?.placar_visitante ?? null : null,
            dataTexto: formatarData(partida),
            local: partida.local_partida,
            competicao: partida.nm_competicao
        };
    }));
}

// CARREGAR PARTIDAS
export async function carregarPartidas() {
    const podeGerenciar =
        window.usuarioVisitante !== true &&
        window.podeGerenciarCompeticao === true;

    if (podeGerenciar) {
        configurarModalPartida();
        configurarModalEdicaoPartida();
        configurarModalResultadoPartida();
        configurarModalArbitragemPartida();
        configurarModalEventosPartida();
    }

    const lista = document.getElementById("listaPartidas");
    const mensagem = document.getElementById("mensagemPartidas");

    if (!lista || !mensagem) {
        console.error("Elementos da seção de partidas não encontrados.");
        return;
    }

    lista.replaceChildren();
    mensagem.textContent = "Carregando partidas...";

    try {
        const resposta = await competicaoService.listarPartidas(idCompeticao);

        const partidas = Array.isArray(resposta)
            ? resposta
            : resposta?.itens || [];

        if (partidas.length === 0) {
            mensagem.textContent = "Nenhuma partida cadastrada nesta competição.";
            return;
        }

        const partidasCompletas = await prepararPartidas(partidas);

        mensagem.textContent = "";

        partidas.forEach((partida, indice) => {
            
            // BOTÃO REGISTRAR PONTUAÇÃO
            const botaoPontuacao = document.createElement("button");
            botaoPontuacao.type = "button";
            botaoPontuacao.className = "custom-btn custom-btn-primary";
            botaoPontuacao.textContent = "Registrar pontuação";

            botaoPontuacao.hidden = partida.status !== "EM_ANDAMENTO";

            botaoPontuacao.addEventListener("click", () => {
                abrirModalEventoPartida("pontuacao", partida.cd_partida);
            });

            // BOTÃO REGISTRAR PENALIDADE
            const botaoPenalidade = document.createElement("button");
            botaoPenalidade.type = "button";
            botaoPenalidade.className = "custom-btn custom-btn-primary";
            botaoPenalidade.textContent = "Registrar penalidade";

            botaoPenalidade.hidden = partida.status !== "EM_ANDAMENTO";

            botaoPenalidade.addEventListener("click", () => {
                abrirModalEventoPartida("penalidade", partida.cd_partida);
            });

            const titulo = document.createElement("h5");
            titulo.classList.add("card-title");

            const times = Array.isArray(partida.times)
                ? partida.times.map(time => time.nm_time).filter(Boolean)
                : [];

            titulo.textContent = times.length > 0
                ? times.join(" × ")
                : `Partida ${partida.cd_partida}`;

            const data = document.createElement("p");
            data.classList.add("card-text");

            const dataPartida = partida.dt_partida
                ? partida.dt_partida.split("-").reverse().join("/")
                : "Não definida";

            data.textContent = `Data: ${dataPartida}`;

            const horario = document.createElement("p");
            horario.classList.add("card-text");
            horario.textContent =
                `Horário: ${partida.inicio_em || partida.inicio_agendado || "Não definido"}`;

            const local = document.createElement("p");
            local.classList.add("card-text");
            local.textContent =
                `Local: ${partida.local_partida || "Não definido"}`;

            const status = document.createElement("p");
            status.classList.add("card-text");
            status.textContent = `Status: ${partida.status || "Não informado"}`;

            // BOTÃO INICIAR PARTIDA
            const botaoIniciar = document.createElement("button");
            botaoIniciar.type = "button";
            botaoIniciar.className = "custom-btn custom-btn-primary";
            botaoIniciar.textContent = "Iniciar partida";

            if (partida.status === "AGENDADA") {
                botaoIniciar.addEventListener("click", async () => {
                    if (!confirm("Deseja realmente iniciar esta partida?")) return;

                    try {
                        botaoIniciar.disabled = true;
                        botaoIniciar.textContent = "Iniciando...";

                        await partidaService.iniciar(partida.cd_partida);

                        await carregarPartidas();

                    } catch (erro) {
                        console.error("Erro ao iniciar partida:", erro);
                        alert(erro.message || "Não foi possível iniciar a partida.");

                        botaoIniciar.disabled = false;
                        botaoIniciar.textContent = "Iniciar partida";
                    }
                });
            } else {
                botaoIniciar.hidden = true;
            }

            // BOTÃO EDITAR PARTIDA
            const botaoEditar = document.createElement("button");
            botaoEditar.type = "button";
            botaoEditar.className = "custom-btn custom-btn-primary";
            botaoEditar.textContent = "Editar";

            botaoEditar.addEventListener("click", () => {
                const dataHora = partida.inicio_agendado || partida.inicio_em || "";

                document.getElementById("editarIdPartida").value = partida.cd_partida;

                document.getElementById("editarDataPartida").value =
                    dataHora.slice(0, 10) || partida.dt_partida || "";

                document.getElementById("editarHorarioPartida").value =
                    dataHora.length >= 16 ? dataHora.slice(11, 16) : "";

                document.getElementById("editarLocalPartida").value =
                    partida.local_partida || "";

                document.getElementById("mensagemEditarPartida").textContent = "";

                document.getElementById("modalEditarPartida").abrir();
            });

            // BOTÃO REGISTRAR RESULTADO
            const botaoResultado = document.createElement("button");
            botaoResultado.type = "button";
            botaoResultado.className = "custom-btn custom-btn-primary";
            botaoResultado.textContent = "Registrar resultado";

            if (partida.status === "EM_ANDAMENTO") {
                botaoResultado.addEventListener("click", () => {
                    document.getElementById("resultadoIdPartida").value =
                        partida.cd_partida;

                    document.getElementById("placarCasa").value = "";
                    document.getElementById("placarVisitante").value = "";

                    document.getElementById("mensagemResultadoPartida").textContent = "";

                    document.getElementById("modalResultadoPartida").abrir();
                });
            } else {
                botaoResultado.hidden = true;
            }

            // BOTÃO ESCALAR ÁRBITRO
            const botaoArbitragem = document.createElement("button");
            botaoArbitragem.type = "button";
            botaoArbitragem.className = "custom-btn custom-btn-primary";
            botaoArbitragem.textContent = "Escalar árbitro";

            if (["AGENDADA", "EM_ANDAMENTO"].includes(partida.status)) {
                botaoArbitragem.addEventListener("click", async () => {
                    const modal = document.getElementById("modalArbitragemPartida");
                    const form = document.getElementById("formArbitragemPartida");
                    const select = document.getElementById("arbitroPartida");
                    const mensagem = document.getElementById("mensagemArbitragemPartida");

                    form.reset();

                    document.getElementById("arbitragemIdPartida").value =
                        partida.cd_partida;

                    select.replaceChildren(
                        new Option("Carregando árbitros...", "")
                    );

                    select.disabled = true;
                    mensagem.textContent = "";

                    modal.abrir();

                    try {
                        const resposta = await partidaService.listarArbitrosElegiveis(
                            partida.cd_partida
                        );

                        const arbitros = Array.isArray(resposta)
                            ? resposta
                            : resposta?.itens || [];

                        select.replaceChildren(
                            new Option("Selecione um árbitro", "")
                        );

                        if (arbitros.length === 0) {
                            mensagem.textContent =
                                "Não há árbitros disponíveis para esta partida.";
                            return;
                        }

                        arbitros.forEach(arbitro => {
                            select.add(
                                new Option(arbitro.nome, arbitro.id)
                            );
                        });

                        select.disabled = false;

                    } catch (erro) {
                        console.error("Erro ao carregar árbitros:", erro);
                        mensagem.textContent =
                            erro.message || "Não foi possível carregar os árbitros.";
                    }
                });
            } else {
                botaoArbitragem.hidden = true;
            }

            
            
            // CARD VISUAL DA PARTIDA
            const containerInformacoes = document.createElement("div");
            containerInformacoes.className = "partidas-lista";

            renderizarListaPartidas(
                containerInformacoes,
                [partidasCompletas[indice]]
            );

            // BOTÃO GERENCIAR PARTIDA
            const botaoGerenciar = document.createElement("button");
            botaoGerenciar.type = "button";
            botaoGerenciar.className = "partida-botao-gerenciar";
            botaoGerenciar.setAttribute("aria-expanded", "false");

            const textoGerenciar = document.createElement("span");
            textoGerenciar.textContent = "Gerenciar partida";

            const setaGerenciar = document.createElement("img");
            setaGerenciar.src = "/assets/icons/seta.svg";
            setaGerenciar.alt = "";
            setaGerenciar.className = "partida-gerenciar-seta";

            botaoGerenciar.append(textoGerenciar, setaGerenciar);

            // OPÇÕES DE GERENCIAMENTO
            const painelGerenciamento = document.createElement("div");
            painelGerenciamento.className = "partida-gerenciamento";
            painelGerenciamento.hidden = true;

            const acoesPartida = document.createElement("div");
            acoesPartida.className = "partida-gerenciar-acoes";

            // BOTÕES DE GERENCIAMENTO
            acoesPartida.append(
                botaoIniciar,
                botaoEditar,
                botaoArbitragem,
                botaoPontuacao,
                botaoPenalidade,
                botaoResultado
            );

            painelGerenciamento.appendChild(acoesPartida);

            // IMPEDIR QUE O CARD INTERCEPTE OS CLIQUES DO GERENCIAMENTO
            painelGerenciamento.addEventListener("click", evento => {
                evento.stopPropagation();
            });

            botaoGerenciar.addEventListener("click", evento => {
                evento.preventDefault();
                evento.stopPropagation();

                const abrir = painelGerenciamento.hidden;

                painelGerenciamento.hidden = !abrir;
                botaoGerenciar.setAttribute(
                    "aria-expanded",
                    String(abrir)
                );
            });

           // MONTAR CARD SEM NAVEGAÇÃO NOS BOTÕES
            const cardPartida = containerInformacoes.firstElementChild;

            if (!cardPartida) {
                console.warn("Card da partida não encontrado:", partida.cd_partida);
                return;
            }

            // CARD EXTERNO APENAS VISUAL
            const cardCompleto = document.createElement("div");
            cardCompleto.className = "partida-card-completo";

            // ÁREA DE INFORMAÇÕES
            const areaInformacoes = document.createElement("div");
            areaInformacoes.className = "partida-card-informacoes";

            areaInformacoes.appendChild(cardPartida);

            // ÁREA DE GERENCIAMENTO SEPARADA DA NAVEGAÇÃO
            const areaGerenciamento = document.createElement("div");
            areaGerenciamento.className = "partida-card-acoes";

            areaGerenciamento.append(
                botaoGerenciar,
                painelGerenciamento
            );

            // IMPEDIR NAVEGAÇÃO INDESEJADA
            areaGerenciamento.addEventListener("click", evento => {
                evento.stopPropagation();
            });

            // UNIR TUDO VISUALMENTE
            cardCompleto.appendChild(areaInformacoes);

            if (podeGerenciar) {
                cardCompleto.appendChild(areaGerenciamento);
            }

            lista.appendChild(cardCompleto);
        });

    } catch (erro) {
        console.error("Erro ao carregar partidas:", erro);
        mensagem.textContent = "Não foi possível carregar as partidas.";
    }
}

// EDITAR PARTIDA
function configurarModalEdicaoPartida() {
    const modal = document.getElementById("modalEditarPartida");
    const form = document.getElementById("formEditarPartida");

    if (!modal || !form || form.dataset.configurado) return;

    form.dataset.configurado = "true";

    form.addEventListener("submit", async evento => {
        evento.preventDefault();

        const id = document.getElementById("editarIdPartida").value;
        const data = document.getElementById("editarDataPartida").value;
        const horario = document.getElementById("editarHorarioPartida").value;
        const local = document.getElementById("editarLocalPartida").value.trim();

        const mensagem = document.getElementById("mensagemEditarPartida");
        const botao = document.getElementById("botaoSalvarEdicaoPartida");

        if (!data || !horario || !local) {
            mensagem.textContent = "Preencha todos os campos.";
            return;
        }

        try {
            botao.disabled = true;
            mensagem.textContent = "Salvando alterações...";

            await partidaService.atualizarCalendario(
                id,
                `${data}T${horario}`
            );

            await partidaService.atualizarLocal(id, local);

            mensagem.textContent = "Partida atualizada com sucesso!";

            modal.fechar();

            await carregarPartidas();

        } catch (erro) {
            console.error("Erro ao editar partida:", erro);

            mensagem.textContent =
                erro.message || "Não foi possível atualizar a partida.";
        } finally {
            botao.disabled = false;
        }
    });
}

// REGISTRAR RESULTADO
function configurarModalResultadoPartida() {
    const modal = document.getElementById("modalResultadoPartida");
    const form = document.getElementById("formResultadoPartida");

    if (!modal || !form || form.dataset.configurado) return;

    form.dataset.configurado = "true";

    form.addEventListener("submit", async evento => {
        evento.preventDefault();

        const id = document.getElementById("resultadoIdPartida").value;
        const placarCasa = document.getElementById("placarCasa").value;
        const placarVisitante = document.getElementById("placarVisitante").value;

        const mensagem = document.getElementById("mensagemResultadoPartida");
        const botao = document.getElementById("botaoSalvarResultado");

        try {
            botao.disabled = true;
            mensagem.textContent = "Registrando resultado...";

            await partidaService.registrarResultado(
                id,
                placarCasa,
                placarVisitante
            );

            mensagem.textContent = "Resultado registrado e partida finalizada!";

            modal.fechar();

            await carregarPartidas();

        } catch (erro) {
            console.error("Erro ao registrar resultado:", erro);

            mensagem.textContent =
                erro.message || "Não foi possível registrar o resultado.";
        } finally {
            botao.disabled = false;
        }
    });
}
