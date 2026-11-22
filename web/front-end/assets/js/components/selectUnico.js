let contadorSelectUnico = 0;

export function criarSelectUnico({
    select,
    placeholder = "Selecione uma opção",
    ariaLabel = "Selecionar opção",
    obterImagem = opcao => opcao.dataset.foto || ""
} = {}) {
    if (!select || select.tagName !== "SELECT" || !select.parentElement) {
        return null;
    }

    //CONTAINER
    const container = document.createElement("div");
    container.className = "select-unico";

    select.parentElement.insertBefore(container, select);
    container.appendChild(select);

    select.classList.add("select-unico-nativo");
    select.setAttribute("aria-hidden", "true");
    select.tabIndex = -1;

    /*BOTÃO DO SELECT*/
    const toggle = document.createElement("button");
    toggle.type = "button";
    toggle.className = "select-unico-toggle";
    toggle.setAttribute("role", "combobox");
    toggle.setAttribute("aria-haspopup", "listbox");
    toggle.setAttribute("aria-expanded", "false");
    toggle.setAttribute("aria-label", ariaLabel);

    const texto = document.createElement("span");
    texto.className = "select-unico-texto";
    texto.textContent = placeholder;

    const seta = document.createElement("img");
    seta.className = "select-unico-seta";
    seta.src = "/assets/icons/seta.svg";
    seta.alt = "";
    seta.setAttribute("aria-hidden", "true");

    toggle.append(texto, seta);

    /*LISTA DE OPÇÕES*/
    const lista = document.createElement("div");
    lista.className = "select-unico-lista";
    lista.id = `select-unico-lista-${++contadorSelectUnico}`;
    lista.setAttribute("role", "listbox");
    lista.hidden = true;

    toggle.setAttribute("aria-controls", lista.id);

    /*ATUALIZAR TEXTO*/
    function atualizar() {
        const selecionada = select.selectedOptions[0];

        texto.textContent = selecionada?.value
            ? selecionada.textContent
            : placeholder;

        lista.querySelectorAll("[role='option']").forEach(opcao => {
            const ativa = opcao.dataset.valor === select.value;

            opcao.setAttribute("aria-selected", String(ativa));
            opcao.classList.toggle("selecionado", ativa);
        });
    }

    /*DEFINIR OPÇÕES*/
    function definirOpcoes() {
        lista.replaceChildren();

        const opcoes = Array.from(select.options).filter(
            opcao => opcao.value !== ""
        );

        if (!opcoes.length) {
            const vazio = document.createElement("p");
            vazio.className = "select-unico-vazio";
            vazio.textContent = "Nenhum integrante disponível.";
            lista.appendChild(vazio);
            atualizar();
            return;
        }

        opcoes.forEach(opcao => {
            const item = document.createElement("button");
            item.type = "button";
            item.className = "select-unico-item";
            item.setAttribute("role", "option");
            item.dataset.valor = opcao.value;

            const imagem = document.createElement("img");
            imagem.className = "select-unico-foto";
            imagem.src = obterImagem(opcao) || "/assets/img/no-prof-pic.png";
            imagem.alt = "";
            imagem.onerror = () => {
                imagem.onerror = null;
                imagem.src = "/assets/img/no-prof-pic.png";
            };

            const nome = document.createElement("span");
            nome.className = "select-unico-nome";
            nome.textContent = opcao.textContent;

            item.append(imagem, nome);

            item.addEventListener("click", () => {
                select.value = opcao.value;

                select.dispatchEvent(
                    new Event("change", { bubbles: true })
                );

                fechar();
                atualizar();
                toggle.focus();
            });

            lista.appendChild(item);
        });

        atualizar();
    }

    /*ABRIR SELECT*/
    function abrir() {
        lista.hidden = false;
        toggle.classList.add("aberto");
        toggle.setAttribute("aria-expanded", "true");
    }

    /*FECHAR SELECT*/
    function fechar() {
        lista.hidden = true;
        toggle.classList.remove("aberto");
        toggle.setAttribute("aria-expanded", "false");
    }

    /*CLIQUE NO BOTÃO*/
    toggle.addEventListener("click", () => {
        if (lista.hidden) {
            abrir();
        } else {
            fechar();
        }
    });

    /*TECLADO*/
    toggle.addEventListener("keydown", evento => {
        if (evento.key === "Escape") {
            fechar();
        }

        if (evento.key === "ArrowDown" || evento.key === "ArrowUp") {
            evento.preventDefault();
            abrir();

            const opcoes = lista.querySelectorAll("[role='option']");

            if (opcoes.length) {
                const indice = evento.key === "ArrowDown"
                    ? 0
                    : opcoes.length - 1;

                opcoes[indice].focus();
            }
        }
    });

    lista.addEventListener("keydown", evento => {
        const opcoes = Array.from(
            lista.querySelectorAll("[role='option']")
        );

        const indice = opcoes.indexOf(evento.target);

        if (evento.key === "Escape") {
            fechar();
            toggle.focus();
        }

        if (evento.key === "ArrowDown" && indice !== -1) {
            evento.preventDefault();
            opcoes[Math.min(indice + 1, opcoes.length - 1)].focus();
        }

        if (evento.key === "ArrowUp" && indice !== -1) {
            evento.preventDefault();
            opcoes[Math.max(indice - 1, 0)].focus();
        }
    });

    /*FECHAR AO CLICAR FORA*/
    function aoClicarFora(evento) {
        if (!container.contains(evento.target)) {
            fechar();
        }
    }

    document.addEventListener("click", aoClicarFora);

    /*ATUALIZAR QUANDO O SELECT ORIGINAL MUDAR*/
    select.addEventListener("change", atualizar);

    container.append(toggle, lista);

    definirOpcoes();

    return {
        atualizar,
        fechar,

        obterSelecionado() {
            return select.value;
        },

        destruir() {
            document.removeEventListener("click", aoClicarFora);
            select.removeEventListener("change", atualizar);

            container.parentElement?.insertBefore(select, container);
            container.remove();

            select.classList.remove("select-unico-nativo");
            select.removeAttribute("aria-hidden");
            select.removeAttribute("tabindex");
        }
    };
}
