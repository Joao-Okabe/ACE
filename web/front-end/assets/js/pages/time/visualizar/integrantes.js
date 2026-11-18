import { apiFetch } from "../../../api/api.js";
import { estadoTime, obterIdTime } from "./utilitarios.js";
import { inicializarEscalacao } from "./escalacao.js";

// CARREGAR INTEGRANTES DO TIME
export async function carregarIntegrantesTime(idTime = obterIdTime()) {
    const lista = document.getElementById("listaIntegrantes");
    const mensagem = document.getElementById("mensagensIntegrantes");

    if (!lista) {
        return [];
    }

    try {
        const integrantes = await apiFetch(`/times/${idTime}/integrantes`);

        estadoTime.integrantes = integrantes || [];

        lista.replaceChildren();

        if (!estadoTime.integrantes.length) {
            lista.innerHTML = '<p class="text-muted">Nenhum integrante cadastrado.</p>';
            return [];
        }

        estadoTime.integrantes.forEach(integrante => {
            lista.appendChild(criarCardIntegrante(integrante));
        });

        if (mensagem) {
            mensagem.textContent = "";
        }

        return estadoTime.integrantes;
    } catch (erro) {
        console.error("Erro ao carregar integrantes:", erro);

        if (mensagem) {
            mensagem.textContent = "Não foi possível carregar os integrantes.";
        }

        return [];
    }
}



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

    return card;
}


// CONFIGURAR MODAL DE INTEGRANTE
export function configurarModalIntegrante() {
    const modal = document.querySelector('app-modal[titulo="Adicionar integrante"]');
    const botaoAbrir = document.getElementById("abrirModalIntegrante");
    const formulario = document.getElementById("formAdicionarIntegrante");

    if (!modal || !botaoAbrir || !formulario) {
        return;
    }

    const selectAluno = document.getElementById("integranteAluno");
    const selectFuncao = document.getElementById("integranteFuncao");

    botaoAbrir.addEventListener("click", () => {
        modal.setAttribute("aberto", "");
        carregarOpcoesIntegrantes();
    });

    async function carregarOpcoesIntegrantes() {
        try {
            const [alunos, integrantes] = await Promise.all([
                apiFetch("/alunos"),
                apiFetch(`/times/${obterIdTime()}/integrantes`)
            ]);

            const idsVinculados = (integrantes || []).map(integrante =>
                String(integrante.cd_usuario)
            );

            selectAluno.replaceChildren();

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

            const funcaoInicial = document.createElement("option");
            funcaoInicial.value = "";
            funcaoInicial.textContent = "Selecione uma função";
            selectFuncao.appendChild(funcaoInicial);

            estadoTime.funcoes.forEach(funcao => {
                const opcao = document.createElement("option");
                opcao.value = funcao.cd_funcao_integrante;
                opcao.textContent = funcao.nm_funcao_integrante;
                selectFuncao.appendChild(opcao);
            });
        } catch (erro) {
            console.error("Erro ao carregar opções de integrantes:", erro);
        }
    }

    formulario.addEventListener("submit", async evento => {
        evento.preventDefault();

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
        }
    });
}



// CONFIGURAR EDIÇÃO DE INTEGRANTE
export function configurarEdicaoIntegrante() {
    const lista = document.getElementById("listaIntegrantes");
    const modal = document.querySelector('app-modal[titulo="Editar integrante"]');
    const formulario = document.getElementById("formEditarIntegrante");

    if (!lista || !modal || !formulario) {
        return;
    }

    const id = document.getElementById("editarIntegranteId");
    const nome = document.getElementById("editarIntegranteNome");
    const funcao = document.getElementById("editarIntegranteFuncao");
    const camisa = document.getElementById("editarIntegranteCamisa");
    const capitao = document.getElementById("editarIntegranteCapitao");

    // ABRIR MODAL E PREENCHER DADOS
    lista.addEventListener("click", evento => {
        const botao = evento.target.closest("[data-editar-integrante]");

        if (!botao) {
            return;
        }

        const integrante = estadoTime.integrantes.find(item =>
            String(item.cd_usuario) === String(botao.dataset.editarIntegrante)
        );

        if (!integrante) {
            return;
        }

        id.value = integrante.cd_usuario;
        nome.value = integrante.nm_usuario || "";
        camisa.value = integrante.numero_camisa || "";
        capitao.checked = integrante.capitao === true ||
            integrante.capitao === 1 ||
            integrante.capitao === "1" ||
            integrante.capitao === "t";

        funcao.replaceChildren();

        estadoTime.funcoes.forEach(item => {
            const opcao = document.createElement("option");
            opcao.value = item.cd_funcao_integrante;
            opcao.textContent = item.nm_funcao_integrante;
            opcao.selected = String(item.cd_funcao_integrante) ===
                String(integrante.cd_funcao_integrante);

            funcao.appendChild(opcao);
        });

        modal.setAttribute("aberto", "");
    });

    // SALVAR ALTERAÇÕES
    formulario.addEventListener("submit", async evento => {
        evento.preventDefault();

        const idTime = obterIdTime();
        const idIntegrante = id.value;
        const botao = formulario.querySelector('[type="submit"]');

        const dados = {
            cd_funcao_integrante: Number(funcao.value),
            numero_camisa: camisa.value ? Number(camisa.value) : null,
            capitao: capitao.checked
        };

        try {
            botao.disabled = true;

            await apiFetch(`/times/${idTime}/integrantes/${idIntegrante}`, {
                method: "PUT",
                body: JSON.stringify(dados)
            });

            modal.removeAttribute("aberto");

            await carregarIntegrantesTime(idTime);
            inicializarEscalacao();

        } catch (erro) {
            console.error("Erro ao editar integrante:", erro);
        } finally {
            botao.disabled = false;
        }
    });
}
