import { alunoService } from "../../services/alunoService.js";
import { escolaService } from "../../services/escolaService.js";

// COMPONENTE DE LISTAGEM
class AppLista extends HTMLElement {
    connectedCallback() {
        if (this.inicializado) return;
        this.inicializado = true;

        const itens = document.createDocumentFragment();

        while (this.firstChild) {
            itens.appendChild(this.firstChild);
        }

        this.innerHTML = `
            <div class="listagem-card">
                <div class="filtros lista-filtros">
                    <div class="campo-busca">
                        <input
                            type="search"
                            class="lista-pesquisa"
                            placeholder="${this.getAttribute("placeholder") || "Pesquisar"}"
                            aria-label="Pesquisar na lista"
                        >
                    </div>

                    <select class="campo-filtro lista-ordenacao" aria-label="Ordenar lista">
                        <option value="">Ordem padrão</option>
                        <option value="nome-asc">Nome (A–Z)</option>
                        <option value="nome-desc">Nome (Z–A)</option>
                    </select>

                    <select class="campo-filtro lista-escola" aria-label="Filtrar por escola">
                        <option value="">Todas as escolas</option>
                    </select>

                    <button type="button" class="custom-btn custom-btn-primary lista-buscar">
                        Buscar
                    </button>
                </div>

                <div class="lista-conteudo"></div>
            </div>
        `;

        const conteudo = this.querySelector(".lista-conteudo");
        conteudo.appendChild(itens);

        const pesquisa = this.querySelector(".lista-pesquisa");
        const botaoBuscar = this.querySelector(".lista-buscar");
        const ordenacao = this.querySelector(".lista-ordenacao");
        const filtroEscola = this.querySelector(".lista-escola");

        const filtrarItens = () => {
    const termo = pesquisa.value.trim().toLocaleLowerCase("pt-BR");
    const escolaSelecionada = filtroEscola?.value || "";

    this.querySelectorAll(".lista-item").forEach((item) => {
        const correspondePesquisa = item.textContent
            .toLocaleLowerCase("pt-BR")
            .includes(termo);

        const correspondeEscola =
            !escolaSelecionada ||
            item.dataset.escola === escolaSelecionada;

        item.hidden = !correspondePesquisa || !correspondeEscola;
    });
};
        botaoBuscar.addEventListener("click", filtrarItens);

        pesquisa.addEventListener("keydown", (evento) => {
            if (evento.key === "Enter") filtrarItens();
        });

        filtroEscola?.addEventListener("change", filtrarItens);

        ordenacao.addEventListener("change", () => {
            const ordem = ordenacao.value;
            if (!ordem) return;

            const listaItens = [...conteudo.querySelectorAll(".lista-item")];

            listaItens.sort((a, b) => {
                const nomeA = a.querySelector(".lista-info h2")?.textContent.trim() || "";
                const nomeB = b.querySelector(".lista-info h2")?.textContent.trim() || "";

                return ordem === "nome-asc"
                    ? nomeA.localeCompare(nomeB, "pt-BR")
                    : nomeB.localeCompare(nomeA, "pt-BR");
            });

            listaItens.forEach((item) => conteudo.appendChild(item));
        });
    }
}

if (!customElements.get("app-lista")) {
    customElements.define("app-lista", AppLista);
}

// ELEMENTOS
const lista = document.querySelector("app-lista");

const modalCadastrar = document.querySelector('app-modal[titulo="Cadastrar aluno"]');
const modalEditar = document.querySelector('app-modal[titulo="Editar aluno"]');

const formularioCadastro = document.getElementById("form-aluno");
const formularioEdicao = document.getElementById("form-editar-aluno");

const botaoAbrirCadastro = document.getElementById("abrirModalAluno");

const seletorEscolaCadastro = document.getElementById("escolaAluno");
const seletorEscolaEdicao = document.getElementById("editarEscolaAluno");

// MENSAGENS
function exibirMensagem(conteudo, mensagem) {
    conteudo.innerHTML = "";

    const paragrafo = document.createElement("p");
    paragrafo.className = "lista-vazia";
    paragrafo.textContent = mensagem;

    conteudo.appendChild(paragrafo);
}

// CARREGAR ESCOLAS NOS SELETORES
async function carregarEscolas() {
    try {
        const escolas = await escolaService.listar();

        const filtroEscola = lista.querySelector(".lista-escola");

if (filtroEscola) {
    filtroEscola.innerHTML = '<option value="">Todas as escolas</option>';

    escolas
        .filter(escola => escola.ativa === true || escola.ativa === 1)
        .forEach(escola => {
            const opcao = document.createElement("option");
            opcao.value = escola.cd_escola;
            opcao.textContent = escola.nome;
            filtroEscola.appendChild(opcao);
        });
}

        [seletorEscolaCadastro, seletorEscolaEdicao].forEach((seletor) => {
            if (!seletor) return;

            seletor.innerHTML = `
                <option value="" selected disabled>Selecione uma escola</option>
            `;

            escolas
                .filter((escola) => escola.ativa === true || escola.ativa === 1)
                .forEach((escola) => {
                    const opcao = document.createElement("option");

                    opcao.value = escola.cd_escola;
                    opcao.textContent = escola.nome;

                    seletor.appendChild(opcao);
                });
        });
    } catch (erro) {
        console.error("Erro ao carregar escolas:", erro);
        alert("Não foi possível carregar as escolas.");
    }
}

// LISTAR ALUNOS
async function carregarAlunos() {
    const conteudo = lista?.querySelector(".lista-conteudo");

    if (!conteudo) return;

    exibirMensagem(conteudo, "Carregando alunos...");

    try {
        const alunos = await alunoService.listar();

        conteudo.innerHTML = "";

        if (!Array.isArray(alunos) || alunos.length === 0) {
            exibirMensagem(conteudo, "Nenhum aluno cadastrado.");
            return;
        }

        alunos.forEach((aluno) => {
            const item = document.createElement("div");
            item.className = "lista-item";
            item.dataset.escola = String(aluno.cd_escola ?? "");

            const link = document.createElement("a");
            link.href = `visualizar.html?id=${encodeURIComponent(aluno.cd_aluno)}`;
            link.className = "lista-link";

            const foto = document.createElement("div");
            foto.className = "lista-brasao";

            const imagem = document.createElement("img");
            imagem.src = aluno.path_ft_usuario || "/assets/img/no-prof-pic.png";
            imagem.alt = "";

            imagem.onerror = () => {
                imagem.src = "/assets/img/no-prof-pic.png";
            };

            foto.appendChild(imagem);

            const info = document.createElement("div");
            info.className = "lista-info";

            const nome = document.createElement("h2");
            nome.textContent = aluno.nome || aluno.nm_usuario || "Aluno sem nome";

            const detalhes = document.createElement("div");
            detalhes.className = "lista-detalhes";

            const ra = document.createElement("span");
            ra.textContent = `RA: ${aluno.ra || "Não informado"}`;

            const escola = document.createElement("span");
            escola.textContent = aluno.escola || "Escola não informada";

            const telefone = document.createElement("span");
            telefone.textContent = aluno.telefone || "Telefone não informado";

            detalhes.append(ra, escola, telefone);
            info.append(nome, detalhes);

            const seta = document.createElement("span");
            seta.className = "lista-seta";
            seta.textContent = "›";
            seta.setAttribute("aria-hidden", "true");

            link.append(foto, info, seta);

            const botaoEditar = document.createElement("button");
            botaoEditar.type = "button";
            botaoEditar.className = "btn-editar-lista";
            botaoEditar.dataset.editarAluno = "";
            botaoEditar.dataset.id = aluno.cd_aluno;
            botaoEditar.textContent = "Editar";
            botaoEditar.setAttribute(
                "aria-label",
                `Editar ${aluno.nome || "aluno"}`
            );

            item.append(link, botaoEditar);
            conteudo.appendChild(item);
        });

    } catch (erro) {
        console.error("Erro ao carregar alunos:", erro);
        exibirMensagem(conteudo, erro.message || "Não foi possível carregar os alunos.");
    }
}

// ABRIR MODAL DE CADASTRO
botaoAbrirCadastro?.addEventListener("click", () => {
    modalCadastrar?.abrir();
});

// ABRIR MODAL DE EDIÇÃO
lista?.addEventListener("click", async (evento) => {
    const botao = evento.target.closest("[data-editar-aluno]");

    if (!botao || !modalEditar) return;

    const id = botao.dataset.id;

    try {
        const aluno = await alunoService.buscarPorId(id);
        console.log("Dados recebidos do aluno:", aluno);

        formularioEdicao.reset();

            // Carregar foto atual do aluno
            const imagemEdicao = formularioEdicao.querySelector(".icone-camera");
            imagemEdicao.src = aluno.path_ft_usuario || "/assets/img/no-prof-pic.png";
            imagemEdicao.onerror = () => {
            imagemEdicao.src = "/assets/img/no-prof-pic.png";
        };

        document.getElementById("editarIdAluno").value = aluno.cd_aluno;
        document.getElementById("editarNomeAluno").value = aluno.nome || "";
        document.getElementById("editarRaAluno").value = aluno.ra || "";
        document.getElementById("editarDataNascimentoAluno").value = aluno.data_nascimento || "";
        document.getElementById("editarSexoAluno").value = aluno.sexo || "";
        document.getElementById("editarTelefoneAluno").value = aluno.telefone || "";
        document.getElementById("editarCepAluno").value = aluno.cep || "";

        seletorEscolaEdicao.value = String(aluno.cd_escola ?? "");

        modalEditar.abrir();

    } catch (erro) {
        console.error("Erro ao buscar aluno:", erro);
        alert(erro.message || "Não foi possível carregar os dados do aluno.");
    }
});


// CADASTRAR ALUNO
formularioCadastro?.addEventListener("submit", async (evento) => {
    evento.preventDefault();

    if (!formularioCadastro.reportValidity()) return;

    const botao = formularioCadastro.querySelector('button[type="submit"]');

    try {
        botao.disabled = true;
        botao.textContent = "Cadastrando...";

        const dados = new FormData(formularioCadastro);

        const foto = document.getElementById("img_aluno").files[0];

        dados.delete("img");

        if (foto) {
            dados.append("foto_perfil", foto);
        }

        await alunoService.criar(dados);

        alert("Aluno cadastrado com sucesso!");

        formularioCadastro.reset();
        modalCadastrar?.fechar();

        await carregarAlunos();

    } catch (erro) {
        console.error("Erro ao cadastrar aluno:", erro);
        alert(erro.message || "Não foi possível cadastrar o aluno.");
    } finally {
        botao.disabled = false;
        botao.textContent = "Cadastrar";
    }
});

// EDITAR ALUNO
formularioEdicao?.addEventListener("submit", async (evento) => {
    evento.preventDefault();

    if (!formularioEdicao.reportValidity()) return;

    const id = document.getElementById("editarIdAluno").value;
    const botao = formularioEdicao.querySelector('button[type="submit"]');

    const dados = new FormData(formularioEdicao);

dados.set(
    "telefone",
    document.getElementById("editarTelefoneAluno").value.replace(/\D/g, "")
);

dados.set(
    "cep",
    document.getElementById("editarCepAluno").value.replace(/\D/g, "")
);

const foto = document.getElementById("editar_img_aluno").files[0];

dados.delete("img");

if (foto) {
    dados.append("foto_perfil", foto);
}

    try {
        botao.disabled = true;
        botao.textContent = "Salvando...";

        await alunoService.atualizar(id, dados);

        alert("Aluno atualizado com sucesso!");

        modalEditar?.fechar();

        await carregarAlunos();

    } catch (erro) {
        console.error("Erro ao atualizar aluno:", erro);
        alert(erro.message || "Não foi possível atualizar o aluno.");
    } finally {
        botao.disabled = false;
        botao.textContent = "Confirmar";
    }
});

// INICIALIZAÇÃO
carregarEscolas();
carregarAlunos();
