class AppModal extends HTMLElement {
    connectedCallback() {
        if (this.inicializado) return;
        this.inicializado = true;

        const conteudo = document.createDocumentFragment();

        while (this.firstChild) {
            conteudo.appendChild(this.firstChild);
        }

        this.innerHTML = `
            <div class="modal-overlay">
                <div class="modal-container" role="dialog" aria-modal="true" aria-labelledby="${this.id}-titulo" tabindex="-1">
                    <header class="modal-header">
                        <h2 id="${this.id}-titulo"></h2>

                        <button type="button" class="modal-fechar" aria-label="Fechar modal">
                            <img src="/assets/icons/close.svg" alt="">
                        </button>
                    </header>

                    <div class="modal-body"></div>
                </div>
            </div>
        `;

        this.querySelector(".modal-body").appendChild(conteudo);

        this.overlay = this.querySelector(".modal-overlay");
        this.container = this.querySelector(".modal-container");

        this.querySelector(`#${this.id}-titulo`).textContent =
            this.getAttribute("titulo") || "Formulário";

        const botaoFechar = this.querySelector(".modal-fechar");

        botaoFechar.addEventListener("click", () => this.fechar());

        this.overlay.addEventListener("click", (evento) => {
            if (evento.target === this.overlay) {
                this.fechar();
            }
        });

        this.addEventListener("keydown", (evento) => {
            if (evento.key === "Escape") {
                this.fechar();
            }
        });

        this.querySelectorAll("[data-fechar-modal]").forEach((botao) => {
            botao.addEventListener("click", () => this.fechar());
        });
    }

    abrir() {
        this.overlay.classList.add("ativo");
        this.container.focus();
    }

    fechar() {
        this.overlay.classList.remove("ativo");
    }
}

customElements.define("app-modal", AppModal);