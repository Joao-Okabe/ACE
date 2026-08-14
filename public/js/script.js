/*--Sidebar fechada com os ícones--*/
const btn = document.getElementById("menu-btn");
    const sidebar = document.querySelector(".sidebar");
    const navbar = document.querySelector(".navbar");
    const content = document.querySelector(".content");

    if (btn && sidebar && navbar && content) {
        btn.addEventListener("click", () => {
            sidebar.classList.toggle("close");
            navbar.classList.toggle("close");
            content.classList.toggle("close");
        });
    }


/*--Adicionar foto de perfil--*/
const input = document.getElementById("img");
const fotoPerfil = document.getElementById("fotoPerfil");

if (input && fotoPerfil) {
    input.addEventListener("change", function () {
        const arquivo = this.files[0];

        if (arquivo) {
            fotoPerfil.innerHTML = `<img src="${URL.createObjectURL(arquivo)}" alt="Foto">`;
        }
    });
}
