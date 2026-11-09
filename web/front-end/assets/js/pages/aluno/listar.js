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

customElements.define("app-lista", AppLista);



//MODAL - CADASTRO E EDITAR
const modalCadastrarAluno = document.querySelector('app-modal[titulo="Cadastrar aluno"]');
const modalEditarAluno = document.querySelector('app-modal[titulo="Editar aluno"]');

const botaoAbrirCadastro = document.getElementById("abrirModalAluno");
const botoesEditar = document.querySelectorAll("[data-editar-aluno]");

// Abrir modal de cadastro
botaoAbrirCadastro?.addEventListener("click", () => {
    modalCadastrarAluno?.abrir();
});

// Abrir modal de edição
botoesEditar.forEach((botao) => {
    botao.addEventListener("click", () => {
        if (!modalEditarAluno) return;

        const idAluno = botao.dataset.id;
        const campoId = modalEditarAluno.querySelector("#editarIdAluno");

        if (campoId) {
            campoId.value = idAluno;
        }

        modalEditarAluno.abrir();
    });
});