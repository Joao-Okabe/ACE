<<<<<<< HEAD
import { apiFetch } from "../../../api/api.js";
import { estadoTime, obterIdTime } from "./utilitarios.js";
import { inicializarEscalacao } from "./escalacao.js";
=======

import { criarCardEntidade } from "../../../components/cardEntidade.js";
import { apiFetch } from "../../../api/api.js";
import { estadoTime, obterIdTime } from "./utilitarios.js";
import { inicializarEscalacao } from "./escalacao.js";
import { vinculoTimeService } from "../../../services/vinculoTimeService.js";
import { alunoService } from "../../../services/alunoService.js";
>>>>>>> origin/api_v1

// CARREGAR INTEGRANTES DO TIME
export async function carregarIntegrantesTime(idTime = obterIdTime()) {
    const lista = document.getElementById("listaIntegrantes");
    const mensagem = document.getElementById("mensagensIntegrantes");
<<<<<<< HEAD

    if (!lista) {
        return [];
    }

    try {
        const integrantes = await apiFetch(`/times/${idTime}/integrantes`);

        estadoTime.integrantes = integrantes || [];

=======
    if (!lista) return [];

    lista.classList.add("cards-entidade");

    try {
        const integrantes = await apiFetch(`/times/${idTime}/integrantes`);
        estadoTime.integrantes = integrantes || [];

        // Relacionar cd_usuario com cd_aluno
        try {
            const alunos = await alunoService.listar();
            const idsAlunos = new Map(
                (Array.isArray(alunos) ? alunos : []).map(aluno => [
                    String(aluno.cd_usuario),
                    aluno.cd_aluno
                ])
            );

            estadoTime.integrantes = estadoTime.integrantes.map(integrante => ({
                ...integrante,
                cd_aluno: integrante.cd_aluno ??
                    idsAlunos.get(String(integrante.cd_usuario)) ?? null
            }));
        } catch (erro) {
            console.error("Erro ao buscar IDs dos alunos:", erro);
        }

>>>>>>> origin/api_v1
        lista.replaceChildren();

        if (!estadoTime.integrantes.length) {
            lista.innerHTML = '<p class="text-muted">Nenhum integrante cadastrado.</p>';
            return [];
        }

        estadoTime.integrantes.forEach(integrante => {
            lista.appendChild(criarCardIntegrante(integrante));
        });

<<<<<<< HEAD
        if (mensagem) {
            mensagem.textContent = "";
        }

        return estadoTime.integrantes;
    } catch (erro) {
        console.error("Erro ao carregar integrantes:", erro);

        if (mensagem) {
            mensagem.textContent = "Não foi possível carregar os integrantes.";
        }

=======
        if (mensagem) mensagem.textContent = "";
        return estadoTime.integrantes;
    } catch (erro) {
        console.error("Erro ao carregar integrantes:", erro);
        if (mensagem) mensagem.textContent = "Não foi possível carregar os integrantes.";
>>>>>>> origin/api_v1
        return [];
    }
}

<<<<<<< HEAD


// CRIAR CARD DO INTEGRANTE
export function criarCardIntegrante(integrante) {
    const card = document.createElement("div");
    card.className = "time-inscrito-card";

    const imagem = document.createElement("img");
    imagem.className = "time-inscrito-brasao";
    imagem.src = integrante.foto_perfil || integrante.path_ft_usuario || "/assets/img/no-prof-pic.png";
    imagem.alt = `Foto de ${integrante.nm_usuario}`;

    const nome = document.createElement("span");
    nome.className = "time-inscrito-nome";
    nome.textContent = integrante.nm_usuario || "Integrante sem nome";

    const botaoEditar = document.createElement("button");
    botaoEditar.type = "button";
    botaoEditar.className = "btn-editar-integrante";
    botaoEditar.dataset.editarIntegrante = integrante.cd_usuario;
    botaoEditar.setAttribute("aria-label", `Editar ${integrante.nm_usuario}`);
    botaoEditar.innerHTML = '<i class="bi bi-pencil"></i>';

    card.append(imagem, nome, botaoEditar);
=======
// VERIFICAR SE É CAPITÃO
function ehCapitao(integrante) {
    return ["true", "1", "t"].includes(
        String(integrante.capitao).toLowerCase()
    );
}

// CRIAR CARD DO INTEGRANTE
export function criarCardIntegrante(integrante) {
    const idAluno = integrante.cd_aluno;
    const capitao = ehCapitao(integrante);
    const detalhes = [integrante.nm_funcao_integrante || "Posição não informada"];

    if (integrante.numero_camisa !== null &&
        integrante.numero_camisa !== undefined &&
        integrante.numero_camisa !== "") {
        detalhes.push(`Camisa ${integrante.numero_camisa}`);
    }

    const origem = window.location.pathname + window.location.search;
    const card = criarCardEntidade({
        foto: integrante.foto_perfil ||
            integrante.path_ft_usuario ||
            "/assets/img/no-prof-pic.png",
        nome: integrante.nm_usuario || "Integrante sem nome",
        detalhes,
        status: capitao ? "Capitão" : null,
        url: idAluno
            ? `/pages/aluno/visualizar.html?id=${encodeURIComponent(idAluno)}&voltar=${encodeURIComponent(origem)}`
            : null
    });

    if (estadoTime.somenteLeitura) {
        return card;
    }

    let acoes = card.querySelector(".card-entidade-acoes");
    if (!acoes) {
        acoes = document.createElement("div");
        acoes.className = "card-entidade-acoes";
        card.appendChild(acoes);
    }

    // BOTÃO DE CAPITÃO
    const outroCapitao = !capitao && estadoTime.integrantes.some(item =>
        ehCapitao(item) &&
        String(item.cd_usuario) !== String(integrante.cd_usuario)
    );

    const botaoCapitao = document.createElement("button");
    botaoCapitao.type = "button";
    botaoCapitao.className = "card-entidade-editar";
    botaoCapitao.dataset.acaoCapitao = integrante.cd_usuario;
    botaoCapitao.textContent = capitao
        ? "Remover capitão"
        : outroCapitao ? "Já existe capitão" : "Tornar capitão";
    botaoCapitao.disabled = outroCapitao;
    acoes.appendChild(botaoCapitao);

    // BOTÃO REMOVER INTEGRANTE
    const botaoRemover = document.createElement("button");
    botaoRemover.type = "button";
    botaoRemover.className = "card-entidade-remover";
    botaoRemover.dataset.removerIntegrante = integrante.cd_usuario;
    botaoRemover.textContent = "Remover do time";
    botaoRemover.setAttribute(
        "aria-label",
        `Remover ${integrante.nm_usuario} do time`
    );
    acoes.appendChild(botaoRemover);
>>>>>>> origin/api_v1

    return card;
}

<<<<<<< HEAD
=======
// CONFIGURAR AÇÕES DE CAPITÃO
export function configurarAcoesCapitao() {
    const lista = document.getElementById("listaIntegrantes");
    if (!lista || lista.dataset.capitaoConfigurado) return;
    lista.dataset.capitaoConfigurado = "true";

    lista.addEventListener("click", async evento => {
        const botao = evento.target.closest("[data-acao-capitao]");
        if (!botao) return;

        evento.preventDefault();
        evento.stopPropagation();

        const idTime = obterIdTime();
        const idUsuario = botao.dataset.acaoCapitao;
        const integrante = estadoTime.integrantes.find(item =>
            String(item.cd_usuario) === String(idUsuario)
        );
        if (!integrante) return;

        const capitaoAtual = ehCapitao(integrante);
        if (capitaoAtual) {
            const confirmar = confirm("Deseja remover a capitania deste integrante?");
            if (!confirmar) return;
        }

        botao.disabled = true;
        try {
            if (capitaoAtual) {
                await vinculoTimeService.removerCapitao(idTime, idUsuario);
            } else {
                await vinculoTimeService.tornarCapitao(idTime, idUsuario);
            }

            await carregarIntegrantesTime(idTime);
            alert(capitaoAtual
                ? "Capitania removida com sucesso!"
                : "Capitão definido com sucesso!"
            );
        } catch (erro) {
            console.error("Erro ao atualizar capitão:", erro);
            alert(erro.message || "Não foi possível atualizar o capitão.");
        } finally {
            if (botao.isConnected) botao.disabled = false;
        }
    });
}

// CONFIGURAR REMOÇÃO DE INTEGRANTE
export function configurarRemocaoIntegrante() {
    const lista = document.getElementById("listaIntegrantes");
    if (!lista || lista.dataset.remocaoConfigurada) return;
    lista.dataset.remocaoConfigurada = "true";

    lista.addEventListener("click", async evento => {
        const botao = evento.target.closest("[data-remover-integrante]");
        if (!botao) return;

        evento.preventDefault();
        evento.stopPropagation();

        const idTime = obterIdTime();
        const idUsuario = botao.dataset.removerIntegrante;
        const integrante = estadoTime.integrantes.find(item =>
            String(item.cd_usuario) === String(idUsuario)
        );
        if (!integrante) return;

        const confirmar = confirm(
            `Deseja remover ${integrante.nm_usuario} do time?\n\n` +
            "O cadastro do aluno continuará existindo no sistema."
        );
        if (!confirmar) return;

        botao.disabled = true;
        try {
            await vinculoTimeService.removerIntegrante(idTime, idUsuario);
            await carregarIntegrantesTime(idTime);
            inicializarEscalacao();
            alert("Integrante removido do time com sucesso!");
        } catch (erro) {
            console.error("Erro ao remover integrante:", erro);
            alert(erro.message || "Não foi possível remover o integrante.");
        } finally {
            if (botao.isConnected) botao.disabled = false;
        }
    });
}
>>>>>>> origin/api_v1

// CONFIGURAR MODAL DE INTEGRANTE
export function configurarModalIntegrante() {
    const modal = document.querySelector('app-modal[titulo="Adicionar integrante"]');
    const botaoAbrir = document.getElementById("abrirModalIntegrante");
    const formulario = document.getElementById("formAdicionarIntegrante");
<<<<<<< HEAD

    if (!modal || !botaoAbrir || !formulario) {
        return;
    }
=======
    if (!modal || !botaoAbrir || !formulario) return;
>>>>>>> origin/api_v1

    const selectAluno = document.getElementById("integranteAluno");
    const selectFuncao = document.getElementById("integranteFuncao");

    botaoAbrir.addEventListener("click", () => {
<<<<<<< HEAD
        modal.setAttribute("aberto", "");
=======
        modal.abrir();
>>>>>>> origin/api_v1
        carregarOpcoesIntegrantes();
    });

    async function carregarOpcoesIntegrantes() {
        try {
            const [alunos, integrantes] = await Promise.all([
                apiFetch("/alunos"),
                apiFetch(`/times/${obterIdTime()}/integrantes`)
            ]);
<<<<<<< HEAD

=======
>>>>>>> origin/api_v1
            const idsVinculados = (integrantes || []).map(integrante =>
                String(integrante.cd_usuario)
            );

            selectAluno.replaceChildren();
<<<<<<< HEAD

=======
>>>>>>> origin/api_v1
            const opcaoInicial = document.createElement("option");
            opcaoInicial.value = "";
            opcaoInicial.textContent = "Selecione um aluno";
            selectAluno.appendChild(opcaoInicial);

            (alunos || [])
                .filter(aluno => !idsVinculados.includes(String(aluno.cd_usuario)))
                .forEach(aluno => {
                    const opcao = document.createElement("option");
                    opcao.value = aluno.cd_usuario;
                    opcao.textContent = aluno.nm_usuario;
                    selectAluno.appendChild(opcao);
                });

            selectFuncao.replaceChildren();
<<<<<<< HEAD

=======
>>>>>>> origin/api_v1
            const funcaoInicial = document.createElement("option");
            funcaoInicial.value = "";
            funcaoInicial.textContent = "Selecione uma função";
            selectFuncao.appendChild(funcaoInicial);

            estadoTime.funcoes.forEach(funcao => {
                const opcao = document.createElement("option");
                opcao.value = funcao.cd_funcao_integrante;
<<<<<<< HEAD
                opcao.textContent = funcao.nm_funcao_integrante;
=======
                opcao.textContent = funcao.nm_funcao;
>>>>>>> origin/api_v1
                selectFuncao.appendChild(opcao);
            });
        } catch (erro) {
            console.error("Erro ao carregar opções de integrantes:", erro);
        }
    }

    formulario.addEventListener("submit", async evento => {
        evento.preventDefault();
<<<<<<< HEAD

        const idTime = obterIdTime();

        try {
            const dados = {
                cd_usuario: Number(selectAluno.value),
                cd_funcao_integrante: Number(selectFuncao.value)
            };

            await apiFetch(`/times/${idTime}/integrantes`, {
                method: "POST",
                body: JSON.stringify(dados)
            });

            modal.removeAttribute("aberto");
            formulario.reset();

            await carregarIntegrantesTime(idTime);
            inicializarEscalacao();
        } catch (erro) {
            console.error("Erro ao adicionar integrante:", erro);
=======
        const idTime = obterIdTime();
        const botao = formulario.querySelector('[type="submit"]');
        const dados = {
            cd_usuario: Number(selectAluno.value),
            cd_funcao_integrante: Number(selectFuncao.value),
            numero_camisa: document.getElementById("integranteCamisa").value
                ? Number(document.getElementById("integranteCamisa").value)
                : null
        };

        if (!dados.cd_usuario || !dados.cd_funcao_integrante) {
            alert("Selecione um aluno e uma função.");
            return;
        }

        try {
            botao.disabled = true;
            await apiFetch(`/times/${idTime}/integrantes`, {
                method: "POST",
                body: dados
            });
            await carregarIntegrantesTime(idTime);
            inicializarEscalacao();
            formulario.reset();
            modal.fechar();
            alert("Integrante cadastrado com sucesso!");
        } catch (erro) {
            console.error("Erro ao adicionar integrante:", erro);
            alert(erro.message || "Não foi possível adicionar o integrante.");
        } finally {
            botao.disabled = false;
>>>>>>> origin/api_v1
        }
    });
}

<<<<<<< HEAD


=======
>>>>>>> origin/api_v1
// CONFIGURAR EDIÇÃO DE INTEGRANTE
export function configurarEdicaoIntegrante() {
    const lista = document.getElementById("listaIntegrantes");
    const modal = document.querySelector('app-modal[titulo="Editar integrante"]');
    const formulario = document.getElementById("formEditarIntegrante");
<<<<<<< HEAD

    if (!lista || !modal || !formulario) {
        return;
    }
=======
    if (!lista || !modal || !formulario) return;
>>>>>>> origin/api_v1

    const id = document.getElementById("editarIntegranteId");
    const nome = document.getElementById("editarIntegranteNome");
    const funcao = document.getElementById("editarIntegranteFuncao");
    const camisa = document.getElementById("editarIntegranteCamisa");
    const capitao = document.getElementById("editarIntegranteCapitao");

    // ABRIR MODAL E PREENCHER DADOS
    lista.addEventListener("click", evento => {
        const botao = evento.target.closest("[data-editar-integrante]");
<<<<<<< HEAD

        if (!botao) {
            return;
        }
=======
        if (!botao) return;
>>>>>>> origin/api_v1

        const integrante = estadoTime.integrantes.find(item =>
            String(item.cd_usuario) === String(botao.dataset.editarIntegrante)
        );
<<<<<<< HEAD

        if (!integrante) {
            return;
        }
=======
        if (!integrante) return;
>>>>>>> origin/api_v1

        id.value = integrante.cd_usuario;
        nome.value = integrante.nm_usuario || "";
        camisa.value = integrante.numero_camisa || "";
        capitao.checked = integrante.capitao === true ||
            integrante.capitao === 1 ||
            integrante.capitao === "1" ||
            integrante.capitao === "t";

        funcao.replaceChildren();
<<<<<<< HEAD

        estadoTime.funcoes.forEach(item => {
            const opcao = document.createElement("option");
            opcao.value = item.cd_funcao_integrante;
            opcao.textContent = item.nm_funcao_integrante;
            opcao.selected = String(item.cd_funcao_integrante) ===
                String(integrante.cd_funcao_integrante);

            funcao.appendChild(opcao);
        });

        modal.setAttribute("aberto", "");
=======
        estadoTime.funcoes.forEach(item => {
            const opcao = document.createElement("option");
            opcao.value = item.cd_funcao_integrante;
            opcao.textContent = item.nm_funcao;
            opcao.selected = String(item.cd_funcao_integrante) ===
                String(integrante.cd_funcao_integrante);
            funcao.appendChild(opcao);
        });

        modal.abrir();
>>>>>>> origin/api_v1
    });

    // SALVAR ALTERAÇÕES
    formulario.addEventListener("submit", async evento => {
        evento.preventDefault();
<<<<<<< HEAD

        const idTime = obterIdTime();
        const idIntegrante = id.value;
        const botao = formulario.querySelector('[type="submit"]');

=======
        const idTime = obterIdTime();
        const idIntegrante = id.value;
        const botao = formulario.querySelector('[type="submit"]');
>>>>>>> origin/api_v1
        const dados = {
            cd_funcao_integrante: Number(funcao.value),
            numero_camisa: camisa.value ? Number(camisa.value) : null,
            capitao: capitao.checked
        };

        try {
            botao.disabled = true;
<<<<<<< HEAD

=======
>>>>>>> origin/api_v1
            await apiFetch(`/times/${idTime}/integrantes/${idIntegrante}`, {
                method: "PUT",
                body: JSON.stringify(dados)
            });
<<<<<<< HEAD

            modal.removeAttribute("aberto");

            await carregarIntegrantesTime(idTime);
            inicializarEscalacao();

=======
            modal.fechar();
            await carregarIntegrantesTime(idTime);
            inicializarEscalacao();
>>>>>>> origin/api_v1
        } catch (erro) {
            console.error("Erro ao editar integrante:", erro);
        } finally {
            botao.disabled = false;
        }
    });
}
