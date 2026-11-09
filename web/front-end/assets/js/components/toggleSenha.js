export function configurarToggleSenha() {
    document.querySelectorAll("[data-toggle-senha]").forEach((toggleSenha) => {
        const seletor = toggleSenha.dataset.toggleSenha;
        const senha = document.querySelector(seletor);

        if (!senha) return;

        toggleSenha.addEventListener("click", () => {
            const mostrar = senha.type === "password";

            senha.type = mostrar ? "text" : "password";

            const descricao = mostrar ? "Ocultar senha" : "Mostrar senha";

            toggleSenha.setAttribute("aria-label", descricao);
            toggleSenha.setAttribute("title", descricao);

            const icone = toggleSenha.querySelector(".icon");

            if (icone) {
                icone.src = mostrar
                    ? "/assets/icons/eye-off.svg"
                    : "/assets/icons/eye.svg";
            }
        });
    });
}
