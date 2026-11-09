document.querySelectorAll(".form-imagem").forEach((container) => {
    const input = container.querySelector('input[type="file"]');
    const seletor = container.querySelector(".seletor-imagem");

    if (!input || !seletor) return;

    input.addEventListener("change", () => {
        const arquivo = input.files[0];

        if (!arquivo) return;

        // Verifica se o arquivo é uma imagem
        if (!arquivo.type.startsWith("image/")) {
            input.value = "";
            return;
        }

        const leitor = new FileReader();

        leitor.onload = (evento) => {
            // Atualiza a imagem sem remover o botão de adicionar
            let imagem = seletor.querySelector(".imagem-selecionada");

            if (!imagem) {
                imagem = document.createElement("img");
                imagem.classList.add("imagem-selecionada");
                imagem.alt = "Pré-visualização da imagem selecionada";

                seletor.prepend(imagem);
            }

            imagem.src = evento.target.result;
        };

        leitor.readAsDataURL(arquivo);
    });
});