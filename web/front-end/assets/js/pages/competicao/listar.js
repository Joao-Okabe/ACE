import { competicaoService } from "../../services/competicaoService.js";
//LISTA
class AppLista extends HTMLElement {
    connectedCallback() {
        if (this.inicializado) return;
        this.inicializado = true;

        // Guarda os itens definidos no HTML da página
        const itens = document.createDocumentFragment();

        while (this.firstChild) {
            itens.appendChild(this.firstChild);
        }

        // Estrutura reutilizável da listagem
        this.innerHTML = `
            <div class="listagem-card">
                <div class="filtros lista-filtros">
                    <div class="campo-busca">
                        <input type="search" class="lista-pesquisa" placeholder="${this.getAttribute("placeholder") || "Pesquisar"}" aria-label="Pesquisar na lista">
                    </div>

                    <select class="campo-filtro lista-ordenacao" aria-label="Ordenar lista">
                        <option value="">Ordem padrão</option>
                        <option value="nome-asc">Nome (A–Z)</option>
                        <option value="nome-desc">Nome (Z–A)</option>
                    </select>

                    <button type="button" class="custom-btn custom-btn-primary lista-buscar"> Buscar </button>
                </div>

                <div class="lista-conteudo"></div>
            </div>
        `;

        const conteudo = this.querySelector(".lista-conteudo");
        conteudo.appendChild(itens);

        const pesquisa = this.querySelector(".lista-pesquisa");
        const botaoBuscar = this.querySelector(".lista-buscar");
        const ordenacao = this.querySelector(".lista-ordenacao");

        // Pesquisa pelos textos de cada item
        const filtrarItens = () => {
            const termo = pesquisa.value.trim().toLocaleLowerCase("pt-BR");

            this.querySelectorAll(".lista-item").forEach((item) => {
                const texto = item.textContent.toLocaleLowerCase("pt-BR");
                item.hidden = !texto.includes(termo);
            });
        };

        botaoBuscar.addEventListener("click", filtrarItens);

        pesquisa.addEventListener("keydown", (evento) => {
            if (evento.key === "Enter") {
                filtrarItens();
            }
        });

        // Ordena os itens pelo texto do nome
        // Ordenação dos itens
ordenacao.addEventListener("change", () => {
    const ordem = ordenacao.value;

    const listaItens = [
        ...conteudo.querySelectorAll(".lista-item")
    ];

    listaItens.sort((a, b) => {
        const nomeA =
            a.querySelector(".lista-info h2")?.textContent.trim() || "";

        const nomeB =
            b.querySelector(".lista-info h2")?.textContent.trim() || "";

        return nomeA.localeCompare(nomeB, "pt-BR", {
            sensitivity: "base"
        }) * (ordem === "nome-desc" ? -1 : 1);
    });

    listaItens.forEach(item => conteudo.appendChild(item));
});
    }
}

customElements.define("app-lista", AppLista);

const modalCadastrar = document.querySelector(
    'app-modal[titulo="Cadastrar competição"]'
);

const modalEditar = document.querySelector(
    'app-modal[titulo="Editar competição"]'
);

const botaoAbrirCadastro = document.getElementById("abrirModalCompeticao");
const lista = document.querySelector("app-lista");
const conteudo = lista?.querySelector(".lista-conteudo");

// ABRIR MODAL DE CADASTRO
botaoAbrirCadastro?.addEventListener("click", () => {
    modalCadastrar?.abrir();
});

// FORMATAR DATA
function formatarData(data) {
    if (!data) return "Não informada";

    const [ano, mes, dia] = String(data).slice(0, 10).split("-");

    if (!ano || !mes || !dia) return "Não informada";

    return `${dia}/${mes}/${ano}`;
}

// CRIAR CARD DA COMPETIÇÃO
function criarCard(competicao) {
    const id = competicao.cd_competicao;

    const item = document.createElement("div");
    item.className = "lista-item";

    const link = document.createElement("a");
    link.href = `visualizar.html?id=${encodeURIComponent(id)}`;
    link.className = "lista-link";

    const brasao = document.createElement("div");
    brasao.className = "lista-brasao";

    const imagem = document.createElement("img");
    imagem.src = "/assets/img/icon.png";
    imagem.alt = "Ícone da competição";

    brasao.appendChild(imagem);

    const info = document.createElement("div");
    info.className = "lista-info";

    const nome = document.createElement("h2");
    nome.textContent = competicao.nm_competicao || "Competição sem nome";

    const detalhes = document.createElement("div");
    detalhes.className = "lista-detalhes";

    const codigo = document.createElement("span");
    codigo.textContent = `Código: ${String(id).padStart(3, "0")} | Criador: ${competicao.cd_criador ?? "Não informado"}`;

    const datas = document.createElement("span");
    datas.textContent =
        `Início: ${formatarData(competicao.dt_inicio ?? competicao.inicio_em)} | ` +
        `Encerramento: ${formatarData(competicao.dt_encerramento ?? competicao.fim_em)}`;

    detalhes.append(codigo, datas);
    info.append(nome, detalhes);

    const seta = document.createElement("span");
    seta.className = "lista-seta";
    seta.textContent = "›";
    seta.setAttribute("aria-hidden", "true");

    link.append(brasao, info, seta);

    const botaoEditar = document.createElement("button");
    botaoEditar.type = "button";
    botaoEditar.className = "btn-editar-lista";
    botaoEditar.textContent = "Editar";
    botaoEditar.dataset.editarCompeticao = "";
    botaoEditar.dataset.id = id;
    botaoEditar.setAttribute("aria-label", "Editar competição");

    item.append(link, botaoEditar);

    return item;
}

// CARREGAR COMPETIÇÕES
async function carregarCompeticoes() {
    if (!conteudo) return;

    try {
        const competicoes = await competicaoService.listar();

        conteudo.innerHTML = "";

        if (!Array.isArray(competicoes) || competicoes.length === 0) {
            conteudo.textContent = "Nenhuma competição cadastrada.";
            return;
        }

        competicoes.forEach((competicao) => {
            conteudo.appendChild(criarCard(competicao));
        });

    } catch (erro) {
        console.error("Erro ao carregar competições:", erro);

        conteudo.textContent = "Não foi possível carregar as competições.";
    }
}

// ABRIR MODAL DE EDIÇÃO
lista?.addEventListener("click", (evento) => {
    const botao = evento.target.closest("[data-editar-competicao]");

    if (!botao || !modalEditar) return;

    const campoId = modalEditar.querySelector("#editarIdCompeticao");

    if (campoId) {
        campoId.value = botao.dataset.id;
    }

    modalEditar.abrir();
});



// PREENCHER OS SELECTS DO CADASTRO
async function carregarOpcoesCadastro() {
    const form = document.getElementById("form-competicao");

    if (!form) return;

    try {
        const opcoes = await competicaoService.opcoesCriacao();

        function preencherSelect(id, itens, campoId, obterNome, textoInicial) {
            const select = form.querySelector(`#${id}`);

            if (!select) return;

            select.innerHTML = "";

            const opcaoInicial = document.createElement("option");
            opcaoInicial.value = "";
            opcaoInicial.textContent = textoInicial;
            select.appendChild(opcaoInicial);

            (Array.isArray(itens) ? itens : []).forEach((item) => {
                const option = document.createElement("option");

                option.value = item[campoId];
                option.textContent = obterNome(item);

                select.appendChild(option);
            });
        }

        preencherSelect(
            "escolaCompeticao",
            opcoes.escolas,
            "cd_escola",
            (escola) => escola.nome,
            "Selecione uma escola"
        );

        preencherSelect(
            "formatoCompeticao",
            opcoes.formatos,
            "cd_formato",
            (formato) => formato.nm_formato,
            "Selecione um formato"
        );

        preencherSelect(
            "esporteCompeticao",
            opcoes.esportes,
            "cd_esporte",
            (esporte) => esporte.nm_esporte,
            "Selecione um esporte"
        );

        preencherSelect(
            "modalidadeCompeticao",
            opcoes.modalidades,
            "cd_modalidade",
            (modalidade) => {
                const sexos = {
                    M: "Masculino",
                    F: "Feminino",
                    MISTO: "Misto"
                };

                return `${sexos[modalidade.sexo] || modalidade.sexo} — ${modalidade.categoria}`;
            },
            "Selecione uma modalidade"
        );

        // Escola só pode ser escolhida diretamente pelo administrador.
        const campoEscola = form.querySelector("#escolaCompeticao")
            ?.closest(".campo-form");

        if (campoEscola) {
            campoEscola.hidden = !opcoes.ehAdministrador;
        }

        const selectEscola = form.querySelector("#escolaCompeticao");

        if (selectEscola) {
            selectEscola.required = Boolean(opcoes.ehAdministrador);
        }

    } catch (erro) {
        console.error("Erro ao carregar opções da competição:", erro);
        alert("Não foi possível carregar as opções do cadastro.");
    }
}



// CADASTRAR COMPETIÇÃO
const formCadastro = document.getElementById("form-competicao");

formCadastro?.addEventListener("submit", async (evento) => {
    evento.preventDefault();

    const dados = Object.fromEntries(
        new FormData(formCadastro).entries()
    );

    try {
        await competicaoService.criar(dados);

        alert("Competição cadastrada com sucesso!");

        formCadastro.reset();
        await carregarCompeticoes();

    } catch (erro) {
        console.error("Erro ao cadastrar competição:", erro);

        alert(
            erro.message || "Não foi possível cadastrar a competição."
        );
    }
});


// INICIALIZAÇÃO
carregarCompeticoes();
carregarOpcoesCadastro();
