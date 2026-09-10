/*--Adicionar foto de perfil--*/
const input = document.getElementById("foto_perfil") || document.getElementById("img");
const fotoPerfil = document.getElementById("fotoPerfil");

if (input && fotoPerfil) {
    input.addEventListener("change", function () {
    const arquivo = this.files[0];

    if (arquivo) {
        if (!arquivo.type.startsWith("image/")) {
            this.value = "";
            return;
        }

        const imagem = document.createElement("img");
        imagem.src = URL.createObjectURL(arquivo);
        imagem.alt = "Pré-visualização da foto de perfil";
        imagem.onload = () => URL.revokeObjectURL(imagem.src);

        fotoPerfil.replaceChildren(imagem);
    }
    });
}