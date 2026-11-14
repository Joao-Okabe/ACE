import { timeService } from "../../services/timeService.js";
import { escolaService } from "../../services/escolaService.js";
import { esporteService } from "../../services/esporteService.js";

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

        const filtrarItens = () => {
            const termo = pesquisa.value.trim().toLocaleLowerCase("pt-BR");

            conteudo.querySelectorAll(".lista-item").forEach(item => {
                const texto = item.textContent.toLocaleLowerCase("pt-BR");
                item.hidden = !texto.includes(termo);
            });
        };

        botaoBuscar.addEventListener("click", filtrarItens);

        pesquisa.addEventListener("keydown", evento => {
            if (evento.key === "Enter") {
                filtrarItens();
            }
        });

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

            listaItens.forEach(item => conteudo.appendChild(item));
        });
    }
}

if (!customElements.get("app-lista")) {
    customElements.define("app-lista", AppLista);
}

// ========================================
// ELEMENTOS DA PÁGINA
// ========================================

const lista = document.querySelector("app-lista");
const modalCadastrarTime = document.querySelector(
    'app-modal[titulo="Cadastrar time"]'
);
const modalEditarTime = document.querySelector(
    'app-modal[titulo="Editar time"]'
);

const botaoAbrirCadastro = document.getElementById("abrirModalTime");

// ========================================
// FUNÇÕES AUXILIARES
// ========================================

function escaparHTML(valor) {
    return String(valor ?? "").replace(/[&<>"']/g, caractere => ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#039;"
    })[caractere]);
}

function timeEstaAtivo(valor) {
    return valor === true ||
        valor === 1 ||
        valor === "1" ||
        valor === "t" ||
        valor === "true";
}

// ========================================
// MONTAR CARD
// ========================================

function criarCardTime(time) {
    const id = escaparHTML(time.cd_time);
    const nome = escaparHTML(time.nm_time);
    const escudo = escaparHTML(
        time.path_escudo || "/assets/img/escudo.jpg"
    );

    const situacao = timeEstaAtivo(time.ativo)
        ? "Ativo"
        : "Inativo";

    return `
        <div class="lista-item">
            <a href="visualizar.html?id=${encodeURIComponent(time.cd_time)}" class="lista-link">
                <div class="lista-brasao">
                    <img src="${escudo}" alt="Escudo do time ${nome}">
                </div>

                <div class="lista-info">
                    <h2>${nome}</h2>

                    <div class="lista-detalhes">
                        <span>Código: ${id}</span>
                        <span>Situação: ${situacao}</span>
                    </div>
                </div>

                <span class="lista-seta" aria-hidden="true">›</span>
            </a>

            <button
                type="button"
                class="btn-editar-lista"
                data-editar-time
                data-id="${id}"
                aria-label="Editar time ${nome}"
            >
                Editar
            </button>
        </div>
    `;
}

// ========================================
// BUSCAR E EXIBIR TIMES
// ========================================

async function carregarTimes() {
    const conteudo = lista?.querySelector(".lista-conteudo");

    if (!conteudo) return;

    conteudo.innerHTML = "<p>Carregando times...</p>";

    try {
        const times = await timeService.listar();

        console.log("Resposta recebida:", times);
        console.log("É um array?", Array.isArray(times));

        if (!Array.isArray(times) || times.length === 0) {
            conteudo.innerHTML = "<p>Nenhum time cadastrado.</p>";
            return;
        }

        conteudo.innerHTML = times.map(criarCardTime).join("");

        // Imagem padrão caso algum escudo não seja encontrado.
        conteudo.querySelectorAll(".lista-brasao img").forEach(imagem => {
            imagem.addEventListener("error", () => {
                imagem.src = "/assets/img/escudo.jpg";
            }, { once: true });
        });

    } catch (erro) {
        console.error("Erro ao carregar times:", erro);

        conteudo.innerHTML = `
            <p>Não foi possível carregar os times. Tente novamente.</p>
        `;
    }
}

// ========================================
// MODAL DE CADASTRO
// ========================================

botaoAbrirCadastro?.addEventListener("click", () => {
    modalCadastrarTime?.abrir();
});

// ========================================
// MODAL DE EDIÇÃO
// ========================================

// Delegação: funciona também com cards criados dinamicamente.
lista?.addEventListener("click", evento => {
    const botao = evento.target.closest("[data-editar-time]");

    if (!botao) return;

    evento.preventDefault();
    evento.stopPropagation();

    if (!modalEditarTime) return;

    const campoId = modalEditarTime.querySelector("#editarIdTime");

    if (campoId) {
        campoId.value = botao.dataset.id;
    }

    modalEditarTime.abrir();
});


// ========================================
// CARREGAR ESCOLAS NO SELECT
// ========================================

async function carregarEscolas() {
    const selectEscola = document.getElementById("escolaTime");

    if (!selectEscola) return;

    try {
        const escolas = await escolaService.listar();

        if (!Array.isArray(escolas)) {
            throw new Error("Resposta inválida ao carregar escolas.");
        }

        selectEscola.innerHTML = `
            <option value="" selected disabled>
                Selecione uma escola
            </option>
        `;

        escolas.forEach(escola => {
            const option = document.createElement("option");

            option.value = escola.cd_escola;
            option.textContent = escola.nome;

            selectEscola.appendChild(option);
        });

    } catch (erro) {
        console.error("Erro ao carregar escolas:", erro);

        selectEscola.innerHTML = `
            <option value="" selected disabled>
                Não foi possível carregar as escolas
            </option>
        `;
    }
}


// ========================================
// CADASTRAR TIME
// ========================================

const formTime = document.getElementById("form-time");

formTime?.addEventListener("submit", async evento => {
    evento.preventDefault();

    const selectEsporte = document.getElementById("esporteTime");

    // Aguardando a disponibilização da rota de esportes.
    if (!selectEsporte || selectEsporte.options.length <= 1) {
        alert("A lista de esportes ainda não está disponível.");
        return;
    }

    const dados = new FormData(formTime);

    try {
        const botaoSubmit = formTime.querySelector(
            'button[type="submit"]'
        );

        botaoSubmit.disabled = true;
        botaoSubmit.textContent = "Cadastrando...";

        await timeService.criar(dados);

        alert("Time cadastrado com sucesso!");

        formTime.reset();

        modalCadastrarTime
            ?.querySelector("[data-fechar-modal]")
            ?.click();

        await carregarTimes();

    } catch (erro) {
        console.error("Erro ao cadastrar time:", erro);

        alert(
            erro.message || "Não foi possível cadastrar o time."
        );

    } finally {
        const botaoSubmit = formTime.querySelector(
            'button[type="submit"]'
        );

        if (botaoSubmit) {
            botaoSubmit.disabled = false;
            botaoSubmit.textContent = "Cadastrar";
        }
    }
});


// ========================================
// MODAL DE EDIÇÃO
// ========================================

lista?.addEventListener("click", async evento => {
    const botao = evento.target.closest("[data-editar-time]");

    if (!botao) return;

    evento.preventDefault();
    evento.stopPropagation();

    if (!modalEditarTime) return;

    const id = botao.dataset.id;
    const campoId = modalEditarTime.querySelector("#editarIdTime");
    const campoNome = modalEditarTime.querySelector("#editarNomeTime");
    const campoEscudo = modalEditarTime.querySelector("#editarEscudoTime");
    const imagem = modalEditarTime.querySelector(".icone-camera");

    try {
        const time = await timeService.buscarPorId(id);

        campoId.value = time.cd_time;
        campoNome.value = time.nm_time || "";

        if (imagem) {
            imagem.src = time.path_escudo || "/assets/img/escudo.jpg";
        }

        if (campoEscudo) {
            campoEscudo.value = "";
        }

        modalEditarTime.abrir();

    } catch (erro) {
        console.error("Erro ao buscar time:", erro);

        alert("Não foi possível carregar os dados do time.");
    }
});


// ========================================
// PRÉ-VISUALIZAÇÃO DO ESCUDO NA EDIÇÃO
// ========================================

const editarEscudo = document.getElementById("editarEscudoTime");

editarEscudo?.addEventListener("change", () => {
    const arquivo = editarEscudo.files[0];

    if (!arquivo) return;

    const imagem = modalEditarTime.querySelector(".icone-camera");

    if (imagem) {
        imagem.src = URL.createObjectURL(arquivo);
    }
});


// ========================================
// ATUALIZAR TIME
// ========================================

const formEditarTime = document.getElementById("form-editar-time");

formEditarTime?.addEventListener("submit", async evento => {
    evento.preventDefault();

    const id = document.getElementById("editarIdTime").value;

    if (!id) {
        alert("ID do time não encontrado.");
        return;
    }

    const dados = new FormData(formEditarTime);

    const botao = formEditarTime.querySelector(
        'button[type="submit"]'
    );

    try {
        botao.disabled = true;
        botao.textContent = "Salvando...";

        await timeService.atualizar(id, dados);

        alert("Time atualizado com sucesso!");

        formEditarTime.reset();

        modalEditarTime
            ?.querySelector("[data-fechar-modal]")
            ?.click();

        await carregarTimes();

    } catch (erro) {
        console.error("Erro ao atualizar time:", erro);

        alert(
            erro.message || "Não foi possível atualizar o time."
        );

    } finally {
        botao.disabled = false;
        botao.textContent = "Confirmar";
    }
});


// ========================================
// CARREGAR ESPORTES NO SELECT
// ========================================

async function carregarEsportes() {
    const selectEsporte = document.getElementById("esporteTime");

    if (!selectEsporte) return;

    try {
        const esportes = await esporteService.listar();

        if (!Array.isArray(esportes)) {
            throw new Error("Resposta inválida ao carregar esportes.");
        }

        selectEsporte.innerHTML = `
            <option value="" selected disabled>
                Selecione um esporte
            </option>
        `;

        esportes.forEach(esporte => {
            const option = document.createElement("option");

            option.value = esporte.cd_esporte;
            option.textContent = esporte.nm_esporte;

            selectEsporte.appendChild(option);
        });

    } catch (erro) {
        console.error("Erro ao carregar esportes:", erro);

        selectEsporte.innerHTML = `
            <option value="" selected disabled>
                Não foi possível carregar os esportes
            </option>
        `;
    }
}

carregarTimes();
carregarEscolas();
carregarEsportes();