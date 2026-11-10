
import { escolaService } from "../../services/escolaService.js";

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

        // Ordena os itens pelo nome
        ordenacao.addEventListener("change", () => {
            const ordem = ordenacao.value;

            if (!ordem) return;

            const listaItens = [...conteudo.querySelectorAll(".lista-item")];

            listaItens.sort((a, b) => {
                const nomeA =
                    a.querySelector(".lista-info h2")?.textContent.trim() || "";

                const nomeB =
                    b.querySelector(".lista-info h2")?.textContent.trim() || "";

                return ordem === "nome-asc"
                    ? nomeA.localeCompare(nomeB, "pt-BR")
                    : nomeB.localeCompare(nomeA, "pt-BR");
            });

            listaItens.forEach((item) => conteudo.appendChild(item));
        });
    }
}

customElements.define("app-lista", AppLista);


//LISTAR ESCOLAS
async function carregarEscolas() {
    try {
        const escolas = await escolaService.listar();

        console.log("Escolas recebidas:", escolas);

        const lista = document.querySelector("app-lista");
        const conteudo = lista?.querySelector(".lista-conteudo");

        if (!conteudo) {
            throw new Error("Área de listagem não encontrada.");
        }

        conteudo.innerHTML = "";

        escolas.forEach((escola) => {
            const item = document.createElement("div");
            item.classList.add("lista-item");

            const link = document.createElement("a");
            link.href = `visualizar.html?id=${escola.cd_escola}`;
            link.classList.add("lista-link");

            const brasao = document.createElement("div");
            brasao.classList.add("lista-brasao");

            const imagem = document.createElement("img");
            imagem.src = "/assets/img/brasao.jpg";
            imagem.alt = "";

            brasao.appendChild(imagem);

            const info = document.createElement("div");
            info.classList.add("lista-info");

            const nome = document.createElement("h2");
            nome.textContent = escola.nome || "Nome não informado";

            const detalhes = document.createElement("div");
            detalhes.classList.add("lista-detalhes");

            const codigo = document.createElement("span");
            codigo.textContent = `Código: ${escola.cd_escola}`;

            const telefone = document.createElement("span");
            telefone.textContent = escola.telefone || "Telefone não informado";

            detalhes.append(codigo, telefone);
            info.append(nome, detalhes);

            const seta = document.createElement("span");
            seta.classList.add("lista-seta");
            seta.textContent = "›";
            seta.setAttribute("aria-hidden", "true");

            link.append(brasao, info, seta);

            // Botão de edição
            const botaoEditar = document.createElement("button");
            botaoEditar.type = "button";
            botaoEditar.classList.add("btn-editar-lista");
            botaoEditar.dataset.editarEscola = "";
            botaoEditar.dataset.id = escola.cd_escola;
            botaoEditar.textContent = "Editar";
            botaoEditar.setAttribute(
                "aria-label",
                `Editar ${escola.nome}`
            );

            item.append(link, botaoEditar);
            conteudo.appendChild(item);
        });

    } catch (erro) {
        console.error("Erro ao carregar escolas:", erro);
    }
}

carregarEscolas();


//MODAL DE CADASTRAR ESCOLA
const btnAbrir = document.querySelector("#abrirModalEscola");
const modal = document.querySelector(
    'app-modal[titulo="Cadastrar escola"]'
);

if (btnAbrir && modal) {
    btnAbrir.addEventListener("click", () => {
        modal.abrir();
    });
}


//MODAL DE EDIÇÃO DE ESCOLA
const modalEditarEscola = document.querySelector(
    'app-modal[titulo="Editar escola"]'
);

const listaEscolas = document.querySelector("app-lista");

// Delegação de eventos para os botões criados dinamicamente
if (listaEscolas) {
    listaEscolas.addEventListener("click", async (evento) => {

        const botao = evento.target.closest("[data-editar-escola]");

        if (!botao) return;

        if (!modalEditarEscola) {
            console.error("Modal de edição não encontrado.");
            return;
        }

        const idEscola = botao.dataset.id;

        try {
            // Busca os dados completos da escola
            const escola = await escolaService.buscarPorId(idEscola);

            console.log("Dados da escola selecionada:", escola);

            // Preenche os campos do formulário
            const formulario = modalEditarEscola.querySelector(
                "#form-editar-escola"
            );

            if (!formulario) {
                console.error("Formulário de edição não encontrado.");
                return;
            }

            formulario.querySelector("#editarIdEscola").value =
                escola.cd_escola ?? "";

            formulario.querySelector("#editarNomeEscola").value =
                escola.nm_escola ?? escola.nome ?? "";

            formulario.querySelector("#editarCategoriaEscola").value =
                escola.categoria_administrativa ?? "";

            formulario.querySelector("#editarEmailEscola").value =
                escola.email ?? "";

            formulario.querySelector("#editarTelefoneEscola").value =
                escola.telefone ?? "";

            formulario.querySelector("#editarCepEscola").value =
                escola.cep ?? "";

            formulario.querySelector("#editarNumeroEscola").value =
                escola.numero ?? "";

            // Guarda o caminho atual do brasão, se houver
            const campoBrasao = formulario.querySelector(
                "[name='path_brasao']"
            );

            if (campoBrasao) {
                campoBrasao.value = escola.path_brasao ?? escola.img_logo ?? "";
            }

            // Abre o modal
            modalEditarEscola.abrir();

        } catch (erro) {
            console.error("Erro ao buscar escola:", erro);
        }
    });
}



//Salvar alterações
const formularioEditarEscola = document.querySelector(
    "#form-editar-escola"
);

if (formularioEditarEscola) {
    formularioEditarEscola.addEventListener("submit", async (evento) => {
        evento.preventDefault();

        const idEscola = formularioEditarEscola
            .querySelector("#editarIdEscola").value;

        if (!idEscola) {
            console.error("ID da escola não encontrado.");
            alert("Não foi possível identificar a escola.");
            return;
        }

        // Captura os dados do formulário
        const dados = {
            nome: formularioEditarEscola
                .querySelector("#editarNomeEscola").value.trim(),

            email: formularioEditarEscola
                .querySelector("#editarEmailEscola").value.trim(),

            telefone: formularioEditarEscola
                .querySelector("#editarTelefoneEscola").value.trim(),

            cep: formularioEditarEscola
                .querySelector("#editarCepEscola").value.trim(),

            numero: formularioEditarEscola
                .querySelector("#editarNumeroEscola").value.trim(),

            categoria_administrativa: formularioEditarEscola
                .querySelector("#editarCategoriaEscola").value
            };

        const botaoSalvar = formularioEditarEscola.querySelector(
            'button[type="submit"]'
        );

        try {
            botaoSalvar.disabled = true;
            botaoSalvar.textContent = "Salvando...";

            console.log("Dados enviados para atualização:", dados);

            // Atualiza a escola pela API
            await escolaService.atualizar(idEscola, dados);

            alert("Escola atualizada com sucesso!");

            // Atualiza a página e a listagem
            window.location.reload();

        } catch (erro) {
            console.error("Erro ao atualizar escola:", erro);

            alert(erro.message || "Não foi possível atualizar a escola.");

        } finally {
            botaoSalvar.disabled = false;
            botaoSalvar.textContent = "Salvar alterações";
        }
    });
}
