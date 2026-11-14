import { timeService } from "../../services/timeService.js";
import { apiFetch } from "../../api/api.js";

let integrantesTime = [];
let funcoesTime = [];
let responsaveisTime = [];


// INICIALIZAÇÃO
document.addEventListener("DOMContentLoaded", () => {
    configurarAbas();
    configurarFormularioEscalacao();
    configurarModalIntegrante();
    configurarModalResponsavel();
    configurarModalTecnico();
    carregarInformacoesTime();
});


// CONFIGURAR ABAS

function configurarAbas() {
    const menu = document.querySelector(".visu-menu");
    const conteudo = document.querySelector(".visu-painel-conteudo");

    if (!menu || !conteudo) {
        console.error("Menu ou conteúdo do time não encontrado.");
        return;
    }

    const botoes = menu.querySelectorAll("[data-aba]");
    const secoes = conteudo.querySelectorAll(".visu-secao");

    botoes.forEach(botao => {
        botao.addEventListener("click", () => {
            const aba = botao.dataset.aba;

            const secaoSelecionada = [...secoes].find(
                secao => secao.id === aba
            );

            if (!secaoSelecionada) {
                console.error("Seção não encontrada:", aba);
                return;
            }

            botoes.forEach(item => {
                const ativo = item === botao;

                item.classList.toggle("active", ativo);
                item.setAttribute("aria-selected", String(ativo));
                item.setAttribute("tabindex", ativo ? "0" : "-1");
            });

            secoes.forEach(secao => {
                secao.hidden = secao !== secaoSelecionada;
            });
        });
    });
}


// OBTER ID DO TIME

function obterIdTime() {
    const parametros = new URLSearchParams(window.location.search);
    return parametros.get("id");
}


// CARREGAR INFORMAÇÕES DO TIME

async function carregarInformacoesTime() {
    const nomeElemento = document.getElementById("nomeTime");
    const escudoElemento = document.getElementById("brasaoEscola");

    const idTime = obterIdTime();

    if (!idTime) {
        nomeElemento.textContent = "Time não encontrado";
        console.error("ID do time não informado na URL.");
        return;
    }

    try {
        const time = await timeService.buscarPorId(idTime);

        if (!time) {
            throw new Error("Time não encontrado.");
        }

        nomeElemento.textContent = time.nm_time || "Time sem nome";

        escudoElemento.src = time.path_escudo || "/assets/img/escudo.jpg";
        escudoElemento.alt = `Escudo do time ${time.nm_time || ""}`;

        escudoElemento.onerror = () => {
            escudoElemento.src = "/assets/img/escudo.jpg";
        };

        integrantesTime = await carregarIntegrantesTime(idTime);
        funcoesTime = await apiFetch(`/times/${idTime}/funcoes`);
        inicializarEscalacao();
        await carregarResponsaveisTime(idTime);
        await carregarTecnicosTime(idTime);

    } catch (erro) {
        console.error("Erro ao carregar informações do time:", erro);

        nomeElemento.textContent = "Não foi possível carregar o time";
        escudoElemento.src = "/assets/img/escudo.jpg";
    }
}


// INICIALIZAR ESCALAÇÃO
function inicializarEscalacao() {
    const lista = document.getElementById("listaFuncoesEscalacao");

    if (!lista) {
        console.error("Lista de funções da escalação não encontrada.");
        return;
    }

    lista.innerHTML = "";
            document.querySelectorAll("#campoEscalacao .jogador").forEach(jogador => {
        jogador.style.display = "none";
        });

    if (!Array.isArray(funcoesTime) || funcoesTime.length === 0) {
        lista.innerHTML = `
            <p class="section-description">
                Nenhuma função encontrada para este esporte.
            </p>
        `;
        return;
    }

    funcoesTime.forEach((funcao, indice) => {
        const grupo = document.createElement("div");
        grupo.className = "mb-3";

        const label = document.createElement("label");
        label.className = "form-label";
        label.textContent = funcao.nm_funcao;

        const select = document.createElement("select");
        select.className = "form-select form-input";
        select.dataset.funcao = funcao.cd_funcao_integrante;

        const opcaoInicial = document.createElement("option");
        opcaoInicial.value = "";
        opcaoInicial.textContent = "Selecione um integrante";
        select.appendChild(opcaoInicial);

        const integrantesDaFuncao = integrantesTime.filter(
            integrante =>
                Number(integrante.cd_funcao_integrante) ===
                Number(funcao.cd_funcao_integrante)
        );

        integrantesDaFuncao.forEach(integrante => {
            const opcao = document.createElement("option");

            opcao.value = integrante.cd_usuario;
            opcao.textContent = integrante.nm_usuario;

            select.appendChild(opcao);
        });

// Recuperar titular salvo
const titularSalvo = integrantesDaFuncao.find(integrante =>
    integrante.titular === true ||
    integrante.titular === "t" ||
    integrante.titular === 1 ||
    integrante.titular === "1"
);

if (titularSalvo) {
    select.value = titularSalvo.cd_usuario;
}

// Atualizar a quadra com o titular recuperado
if (select.value) {
    atualizarJogadorNaQuadra(select, funcao, indice);
}

select.addEventListener("change", () => {
    atualizarJogadorNaQuadra(select, funcao, indice);
});

        grupo.append(label, select);
        lista.appendChild(grupo);
    });
}


// ATUALIZAR JOGADOR NA QUADRA
function atualizarJogadorNaQuadra(select, funcao, indice) {
    const campo = document.getElementById("campoEscalacao");

    if (!campo) return;

    const nomeFuncao = funcao.nm_funcao
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase()
        .trim();

    // Ajustar nomes das funções do futsal para os IDs existentes
    const apelidos = {
        "alas direito": "ala-direita",
        "ala direito": "ala-direita",
        "alas esquerdo": "ala-esquerda",
        "ala esquerdo": "ala-esquerda",
        "pivo": "pivo",
        "goleiro": "goleiro",
        "fixo": "fixo"
    };

    const chave = apelidos[nomeFuncao] ||
        nomeFuncao.replace(/\s+/g, "-");

    const idJogador = `jogador-${chave}`;

    let jogador = document.getElementById(idJogador);

    // Criar espaço visual caso a função não exista no HTML
    if (!jogador) {
        jogador = document.createElement("div");
        jogador.className = "jogador";
        jogador.id = idJogador;

        jogador.style.top = `${20 + Math.floor(indice / 2) * 25}%`;
        jogador.style.left = indice % 2 === 0 ? "30%" : "70%";

        jogador.innerHTML = `
            <img src="/assets/img/no-prof-pic.png" alt="Foto do jogador">
            <span>Nome do jogador</span>
            <small></small>
        `;

        campo.appendChild(jogador);
    }

    const idUsuario = select.value;

    // Nenhum jogador selecionado
    if (!idUsuario) {
        jogador.style.display = "none";
        return;
    }

    const integrante = integrantesTime.find(
        item => Number(item.cd_usuario) === Number(idUsuario)
    );

    if (!integrante) {
        jogador.style.display = "none";
        return;
    }

    const imagem = jogador.querySelector("img");
    const nome = jogador.querySelector("span");
    const posicao = jogador.querySelector("small");

    imagem.src = integrante.foto_perfil ||
        integrante.path_ft_usuario ||
        "/assets/img/no-prof-pic.png";

    imagem.alt = `Foto de ${integrante.nm_usuario}`;

    imagem.onerror = () => {
        imagem.src = "/assets/img/no-prof-pic.png";
    };

    nome.textContent = integrante.nm_usuario;
    posicao.textContent = funcao.nm_funcao;

    jogador.style.display = "block";
}



 // CONFIGURAR FORMULÁRIO DE ESCALAÇÃO
function configurarFormularioEscalacao() {
    const form = document.getElementById("formEscalacao");

    if (!form) {
        console.error("Formulário de escalação não encontrado.");
        return;
    }

    form.addEventListener("submit", async (evento) => {
        evento.preventDefault();

        const idTime = obterIdTime();
        const mensagem = document.getElementById("mensagemEscalacao");
        const botao = form.querySelector('button[type="submit"]');

        if (!idTime) {
            mensagem.textContent = "Não foi possível identificar o time.";
            return;
        }

        // Montar lista de integrantes titulares
        const titulares = {};

        form.querySelectorAll("select[data-funcao]").forEach(select => {
            if (select.value) {
                titulares[select.value] = true;
            }
        });

        try {
            botao.disabled = true;
            botao.textContent = "Salvando...";

            await apiFetch(`/times/${idTime}/escalacao`, {
                method: "PUT",
                body: {
                    titulares: titulares
                }
            });

            mensagem.textContent = "Escalação salva com sucesso!";
            mensagem.style.color = "green";

        } catch (erro) {
            console.error("Erro ao salvar escalação:", erro);

            mensagem.textContent =
                erro.message || "Não foi possível salvar a escalação.";

            mensagem.style.color = "red";

        } finally {
            botao.disabled = false;
            botao.textContent = "Salvar escalação";
        }
    });
}




// CARREGAR INTEGRANTES DO TIME
async function carregarIntegrantesTime(idTime) {
    const lista = document.getElementById("listaIntegrantes");
    const mensagens = document.getElementById("mensagensIntegrantes");

    if (!lista) {
        console.error("Lista de integrantes não encontrada.");
        return;
    }

    lista.innerHTML = "";

    if (mensagens) {
        mensagens.textContent = "Carregando integrantes...";
    }

    try {
        const integrantes = await apiFetch(`/times/${idTime}/integrantes`);

        if (!Array.isArray(integrantes) || integrantes.length === 0) {
            if (mensagens) {
                mensagens.textContent = "Este time ainda não possui integrantes.";
            }
            return [];
        }

        if (mensagens) {
            mensagens.textContent = "";
        }

        integrantes.forEach(integrante => {
            const card = criarCardIntegrante(integrante);
            lista.appendChild(card);
        });
        return integrantes;

    } catch (erro) {
        console.error("Erro ao carregar integrantes:", erro);

        if (mensagens) {
            mensagens.textContent = "Não foi possível carregar os integrantes.";
        }
        return [];
    }
}


// CRIAR CARD DO INTEGRANTE
function criarCardIntegrante(integrante) {
    const coluna = document.createElement("div");
    coluna.className = "col-md-4";

    const card = document.createElement("div");
    card.className = "time-card";

    const topo = document.createElement("div");
    topo.className = "pessoa-topo";

    const avatar = document.createElement("div");
    avatar.className = "pessoa-avatar";

    const imagem = document.createElement("img");
    imagem.src = integrante.foto_perfil || "/assets/img/no-prof-pic.png";
    imagem.alt = `Foto de ${integrante.nm_usuario || "integrante"}`;

    imagem.onerror = () => {
        imagem.src = "/assets/img/no-prof-pic.png";
    };

    avatar.appendChild(imagem);

    const info = document.createElement("div");
    info.className = "pessoa-info";

    const nome = document.createElement("h3");
    nome.textContent = integrante.nm_usuario || "Nome não informado";

    const detalhes = document.createElement("p");

    const funcao = integrante.nm_funcao_integrante || "Integrante";
    const numero = integrante.numero_camisa;

    detalhes.textContent = numero
        ? `${funcao} • Camisa: ${numero}`
        : funcao;

    info.append(nome, detalhes);

    if (integrante.capitao) {
        const etiqueta = document.createElement("span");
        etiqueta.className = "pessoa-tag";
        etiqueta.textContent = "Capitão";
        info.appendChild(etiqueta);
    }

    topo.append(avatar, info);
    card.appendChild(topo);
    coluna.appendChild(card);

    return coluna;
}



// CARREGAR RESPONSÁVEIS DO TIME

async function carregarResponsaveisTime(idTime) {
    const lista = document.getElementById("listaResponsaveis");
    const mensagens = document.getElementById("mensagensResponsaveis");

    if (!lista) {
        console.error("Lista de responsáveis não encontrada.");
        return;
    }

    lista.innerHTML = "";

    if (mensagens) {
        mensagens.textContent = "Carregando responsáveis...";
    }

    try {
        const responsaveis = await apiFetch(`/times/${idTime}/responsaveis`);
        responsaveisTime = Array.isArray(responsaveis) ? responsaveis : [];

        if (responsaveisTime.length === 0) {
            if (mensagens) {
                mensagens.textContent = "Este time ainda não possui responsáveis.";
            }
            return;
        }

        if (mensagens) {
            mensagens.textContent = "";
        }

        responsaveisTime.forEach(responsavel => {
            const card = criarCardResponsavel(responsavel);
            lista.appendChild(card);
        });

    } catch (erro) {
        console.error("Erro ao carregar responsáveis:", erro);

        if (mensagens) {
            mensagens.textContent = "Não foi possível carregar os responsáveis.";
        }
    }
}


// CRIAR CARD DO RESPONSÁVEL

function criarCardResponsavel(responsavel) {
    const card = document.createElement("div");
    card.className = "time-card";

    const topo = document.createElement("div");
    topo.className = "pessoa-topo";

    const avatar = document.createElement("div");
    avatar.className = "pessoa-avatar";

    const imagem = document.createElement("img");
    imagem.src = responsavel.foto_perfil || "/assets/img/no-prof-pic.png";
    imagem.alt = `Foto de ${responsavel.nm_usuario || "responsável"}`;

    imagem.onerror = () => {
        imagem.src = "/assets/img/no-prof-pic.png";
    };

    avatar.appendChild(imagem);

    const info = document.createElement("div");
    info.className = "pessoa-info";

    const nome = document.createElement("h3");
    nome.textContent = responsavel.nm_usuario || "Nome não informado";

    const tipo = document.createElement("p");
    tipo.textContent = "Responsável";

    info.append(nome, tipo);
    topo.append(avatar, info);
    card.appendChild(topo);

    return card;
}


// CARREGAR TÉCNICOS DO TIME

async function carregarTecnicosTime(idTime) {
    const lista = document.getElementById("listaTecnico");

    if (!lista) {
        console.error("Lista de técnicos não encontrada.");
        return;
    }

    lista.innerHTML = "<p class='section-description'>Carregando técnicos...</p>";

    try {
        const tecnicos = await apiFetch(`/times/${idTime}/tecnicos`);

        lista.innerHTML = "";

        if (!Array.isArray(tecnicos) || tecnicos.length === 0) {
            lista.innerHTML = `
                <p class="section-description">
                    Este time ainda não possui técnicos.
                </p>
            `;
            return;
        }

        tecnicos.forEach(tecnico => {
            lista.appendChild(criarCardTecnico(tecnico));
        });

    } catch (erro) {
        console.error("Erro ao carregar técnicos:", erro);

        lista.innerHTML = `
            <p class="section-description">
                Não foi possível carregar os técnicos.
            </p>
        `;
    }
}


// CRIAR CARD DO TÉCNICO

function criarCardTecnico(tecnico) {
    const card = document.createElement("div");
    card.className = "time-card";

    const topo = document.createElement("div");
    topo.className = "pessoa-topo";

    const avatar = document.createElement("div");
    avatar.className = "pessoa-avatar";

    const imagem = document.createElement("img");
    imagem.src = tecnico.foto_perfil || "/assets/img/no-prof-pic.png";
    imagem.alt = `Foto de ${tecnico.nm_usuario || "técnico"}`;

    imagem.onerror = () => {
        imagem.src = "/assets/img/no-prof-pic.png";
    };

    avatar.appendChild(imagem);

    const info = document.createElement("div");
    info.className = "pessoa-info";

    const nome = document.createElement("h3");
    nome.textContent = tecnico.nm_usuario || "Nome não informado";

    const tipo = document.createElement("p");
    tipo.textContent = "Técnico";

    info.append(nome, tipo);
    topo.append(avatar, info);
    card.appendChild(topo);

    return card;
}



// MODAL ADICIONAR INTEGRANTE
function configurarModalIntegrante() {
    const modal = document.querySelector(
        'app-modal[titulo="Adicionar integrante"]'
    );

    const botaoAbrir = document.getElementById("abrirModalIntegrante");
    const formulario = document.getElementById("formAdicionarIntegrante");

    const selectAluno = document.getElementById("integranteAluno");
    const selectFuncao = document.getElementById("integranteFuncao");

    if (!modal || !botaoAbrir || !formulario) {
        console.error("Elementos do modal de integrante não encontrados.");
        return;
    }

    // Abrir modal e carregar opções
    botaoAbrir.addEventListener("click", async () => {
        const idTime = obterIdTime();

        try {
            selectAluno.innerHTML = '<option value="">Carregando alunos...</option>';
            selectFuncao.innerHTML = '<option value="">Carregando funções...</option>';

            modal.abrir();

            const alunos = await apiFetch("/alunos");

            selectAluno.innerHTML = '<option value="">Selecione um aluno</option>';

            const alunosDisponiveis = alunos.filter(aluno =>
                !integrantesTime.some(integrante =>
                    Number(integrante.cd_usuario) === Number(aluno.cd_usuario)
                )
            );

            alunosDisponiveis.forEach(aluno => {
                const opcao = document.createElement("option");
                opcao.value = aluno.cd_usuario;
                opcao.textContent = aluno.nm_usuario;
                selectAluno.appendChild(opcao);
            });

            if (alunosDisponiveis.length === 0) {
                selectAluno.innerHTML =
                    '<option value="">Nenhum aluno disponível</option>';
            }

            // Usa as funções já carregadas para este time
            selectFuncao.innerHTML =
                '<option value="">Selecione uma função</option>';

            funcoesTime.forEach(funcao => {
                const opcao = document.createElement("option");
                opcao.value = funcao.cd_funcao_integrante;
                opcao.textContent = funcao.nm_funcao;
                selectFuncao.appendChild(opcao);
            });

        } catch (erro) {
            console.error("Erro ao carregar opções do integrante:", erro);
            selectAluno.innerHTML =
                '<option value="">Erro ao carregar alunos</option>';
        }
    });

    // Enviar cadastro
    formulario.addEventListener("submit", async evento => {
        evento.preventDefault();

        const idTime = obterIdTime();

        const dados = {
            cd_usuario: Number(selectAluno.value),
            cd_funcao_integrante: Number(selectFuncao.value),
            numero_camisa: document.getElementById("integranteCamisa").value
                ? Number(document.getElementById("integranteCamisa").value)
                : null,
            capitao: document.getElementById("integranteCapitao").checked
        };

        if (!dados.cd_usuario || !dados.cd_funcao_integrante) {
            alert("Selecione um aluno e uma função.");
            return;
        }

        try {
            await apiFetch(`/times/${idTime}/integrantes`, {
                method: "POST",
                body: dados
            });

            integrantesTime = await carregarIntegrantesTime(idTime);
            inicializarEscalacao();

            formulario.reset();
            modal.fechar();

        } catch (erro) {
            console.error("Erro ao adicionar integrante:", erro);
            alert(erro.message || "Não foi possível adicionar o integrante.");
        }
    });
}


// MODAL ADICIONAR RESPONSÁVEL
function configurarModalResponsavel() {
    const modal = document.querySelector(
        'app-modal[titulo="Adicionar responsável"]'
    );

    const botaoAbrir = document.getElementById("abrirModalResponsavel");
    const formulario = document.getElementById("formAdicionarResponsavel");
    const select = document.getElementById("selectResponsavel");

    if (!modal || !botaoAbrir || !formulario || !select) {
        console.error("Elementos do modal de responsável não encontrados.");
        return;
    }

    // Abrir modal e carregar responsáveis disponíveis
    botaoAbrir.addEventListener("click", async () => {
        const idTime = obterIdTime();

        modal.abrir();
        select.innerHTML = '<option value="">Carregando responsáveis...</option>';

        try {
            const responsaveis = await apiFetch(
                `/times/${idTime}/responsaveis/disponiveis`
            );

            const disponiveis = responsaveis.filter(responsavel =>
                !responsaveisTime.some(vinculado =>
                    Number(vinculado.cd_responsavel) ===
                    Number(responsavel.cd_responsavel)
                )
            );

            select.innerHTML =
                '<option value="">Selecione um responsável</option>';

            disponiveis.forEach(responsavel => {
                const opcao = document.createElement("option");

                opcao.value = responsavel.cd_responsavel;
                opcao.textContent = responsavel.nm_usuario;

                select.appendChild(opcao);
            });

            if (disponiveis.length === 0) {
                select.innerHTML =
                    '<option value="">Nenhum responsável disponível</option>';
            }

        } catch (erro) {
            console.error("Erro ao carregar responsáveis disponíveis:", erro);

            select.innerHTML =
                '<option value="">Erro ao carregar responsáveis</option>';
        }
    });

    // Salvar responsável no time
    formulario.addEventListener("submit", async evento => {
        evento.preventDefault();

        const idTime = obterIdTime();
        const idResponsavel = Number(select.value);

        if (!idResponsavel) {
            alert("Selecione um responsável.");
            return;
        }

        try {
            await apiFetch(`/times/${idTime}/responsaveis`, {
                method: "POST",
                body: {
                    cd_responsavel: idResponsavel
                }
            });

            await carregarResponsaveisTime(idTime);

            formulario.reset();
            modal.fechar();

        } catch (erro) {
            console.error("Erro ao adicionar responsável:", erro);

            alert(
                erro.message || "Não foi possível adicionar o responsável."
            );
        }
    });
}


// MODAL ADICIONAR TÉCNICO
function configurarModalTecnico() {
    const modal = document.querySelector(
        'app-modal[titulo="Adicionar técnico"]'
    );

    const botaoAbrir = document.getElementById("abrirModalTecnico");
    const formulario = document.getElementById("formAdicionarTecnico");
    const select = document.getElementById("selectTecnico");

    if (!modal || !botaoAbrir || !formulario || !select) {
        console.error("Elementos do modal de técnico não encontrados.");
        return;
    }

    // Abrir modal e carregar técnicos disponíveis
    botaoAbrir.addEventListener("click", async () => {
        const idTime = obterIdTime();

        modal.abrir();
        select.innerHTML = '<option value="">Carregando técnicos...</option>';

        try {
            const tecnicos = await apiFetch(
                `/times/${idTime}/tecnicos/disponiveis`
            );

            select.innerHTML =
                '<option value="">Selecione um técnico</option>';

            tecnicos.forEach(tecnico => {
                const opcao = document.createElement("option");

                opcao.value = tecnico.cd_usuario;
                opcao.textContent = tecnico.nm_usuario;

                select.appendChild(opcao);
            });

            if (tecnicos.length === 0) {
                select.innerHTML =
                    '<option value="">Nenhum técnico disponível</option>';
            }

        } catch (erro) {
            console.error("Erro ao carregar técnicos disponíveis:", erro);

            select.innerHTML =
                '<option value="">Erro ao carregar técnicos</option>';
        }
    });

    // Salvar técnico no time
    formulario.addEventListener("submit", async evento => {
        evento.preventDefault();

        const idTime = obterIdTime();
        const idUsuario = Number(select.value);

        if (!idUsuario) {
            alert("Selecione um técnico.");
            return;
        }

        try {
            await apiFetch(`/times/${idTime}/tecnicos`, {
                method: "POST",
                body: {
                    cd_usuario: idUsuario
                }
            });

            await carregarTecnicosTime(idTime);

            formulario.reset();
            modal.fechar();

        } catch (erro) {
            console.error("Erro ao adicionar técnico:", erro);

            alert(
                erro.message || "Não foi possível adicionar o técnico."
            );
        }
    });
}