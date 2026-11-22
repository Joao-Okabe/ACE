// COMPONENTE SELECT MÚLTIPLO
export function criarSelectMultiplo({
    container,
    itens = [],
    nomeCampo = "selecionados",
    placeholder = "Selecione as opções",
    obterValor = item => item.id,
    obterNome = item => item.nome,
    obterImagem = null
}) {
    if (!container) return null;

    container.classList.add("select-multiplos");
    container.replaceChildren();

    //BOTÃO DO SELECT
    const toggle = document.createElement("button");
    toggle.type = "button";
    toggle.className = "select-multiplos-toggle";
    toggle.setAttribute("aria-expanded", "false");

    const texto = document.createElement("span");
    texto.textContent = placeholder;

    const seta = document.createElement("img");
    seta.className = "select-multiplos-seta";
    seta.src = "/assets/icons/seta.svg";
    seta.alt = "";
    seta.setAttribute("aria-hidden", "true");

    toggle.append(texto, seta);

    /*LISTA DE OPÇÕES*/

    const lista = document.createElement("div");
    lista.className = "select-multiplos-lista";
    lista.hidden = true;

    function atualizarTexto() {
        const quantidade = lista.querySelectorAll(
            "input:checked"
        ).length;

        texto.textContent = quantidade
            ? `${quantidade} ${quantidade === 1
                ? "opção selecionada"
                : "opções selecionadas"}`
            : placeholder;
    }

    function definirItens(novosItens = []) {
        lista.replaceChildren();

        if (!novosItens.length) {
            const vazio = document.createElement("p");
            vazio.className = "select-multiplos-vazio";
            vazio.textContent = "Nenhuma opção disponível.";
            lista.appendChild(vazio);
            atualizarTexto();
            return;
        }

        novosItens.forEach(item => {
            const label = document.createElement("label");
            label.className = "select-multiplos-item";

            const checkbox = document.createElement("input");
            checkbox.type = "checkbox";
            checkbox.name = nomeCampo;
            checkbox.value = obterValor(item);

            const nome = document.createElement("span");
            nome.className = "select-multiplos-nome";
            nome.textContent = obterNome(item);

            label.appendChild(checkbox);

            if (obterImagem) {
                const caminho = obterImagem(item);

                if (caminho) {
                    const imagem = document.createElement("img");
                    imagem.className = "select-multiplos-brasao";
                    imagem.src = caminho;
                    imagem.alt = "";
                    label.appendChild(imagem);
                }
            }

            label.appendChild(nome);

            checkbox.addEventListener("change", () => {
                label.classList.toggle(
                    "selecionado",
                    checkbox.checked
                );
                atualizarTexto();
            });

            lista.appendChild(label);
        });

        atualizarTexto();
    }

    function fechar() {
        lista.hidden = true;
        toggle.classList.remove("aberto");
        toggle.setAttribute("aria-expanded", "false");
    }

    toggle.addEventListener("click", () => {
        const abrir = lista.hidden;

        lista.hidden = !abrir;
        toggle.classList.toggle("aberto", abrir);
        toggle.setAttribute("aria-expanded", String(abrir));
    });

    function aoClicarFora(evento) {
        if (!container.contains(evento.target)) {
            fechar();
        }
    }

    document.addEventListener("click", aoClicarFora);

    toggle.addEventListener("keydown", evento => {
        if (evento.key === "Escape") fechar();
    });

    container.append(toggle, lista);

    definirItens(itens);

    return {
        definirItens,

        obterSelecionados() {
            return Array.from(
                lista.querySelectorAll("input:checked"),
                input => input.value
            );
        },

        limpar() {
            lista.querySelectorAll("input:checked")
                .forEach(input => {
                    input.checked = false;
                    input.closest(".select-multiplos-item")
                        ?.classList.remove("selecionado");
                });

            atualizarTexto();
        },

        destruir() {
            document.removeEventListener("click", aoClicarFora);
            container.replaceChildren();
        }
    };
}
