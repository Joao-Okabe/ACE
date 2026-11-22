class AppLista extends HTMLElement {
    connectedCallback() {
        if (this.dataset.aceInicializada === "true") return;
        this.dataset.aceInicializada = "true";

        const itens = document.createDocumentFragment();
        while (this.firstChild) itens.appendChild(this.firstChild);

        this.innerHTML = `
            <div class="listagem-card">
                <div class="filtros lista-filtros">
                    <div class="campo-busca">
                        <input type="search" class="lista-pesquisa"
                            placeholder="${this.getAttribute("placeholder") || "Pesquisar"}"
                            aria-label="Pesquisar na lista">
                    </div>
                    <select class="campo-filtro lista-ordenacao" aria-label="Ordenar lista">
                        <option value="">Ordem padrão</option>
                        <option value="nome-asc">Nome (A–Z)</option>
                        <option value="nome-desc">Nome (Z–A)</option>
                    </select>
                    ${this.hasAttribute("filtro-escola") ? `
                        <select class="campo-filtro lista-escola" aria-label="Filtrar por escola">
                            <option value="">Todas as escolas</option>
                        </select>
                    ` : ""}
                    <button type="button" class="custom-btn custom-btn-primary lista-buscar">Buscar</button>
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

            conteudo.querySelectorAll(".lista-item").forEach(item => {
                const correspondePesquisa = item.textContent
                    .toLocaleLowerCase("pt-BR")
                    .includes(termo);
                const correspondeEscola = !escolaSelecionada ||
                    item.dataset.escola === escolaSelecionada;
                item.hidden = !correspondePesquisa || !correspondeEscola;
            });
        };

        botaoBuscar.addEventListener("click", filtrarItens);
        pesquisa.addEventListener("keydown", evento => {
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
                const comparacao = nomeA.localeCompare(nomeB, "pt-BR", {
                    sensitivity: "base"
                });
                return ordem === "nome-desc" ? -comparacao : comparacao;
            });
            listaItens.forEach(item => conteudo.appendChild(item));
        });
    }
}

if (!customElements.get("app-lista")) {
    customElements.define("app-lista", AppLista);
}
