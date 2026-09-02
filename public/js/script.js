/*--Sidebar fechada com os ícones--*/
const btn = document.getElementById("menu-btn");
    const sidebar = document.querySelector(".sidebar");
    const navbar = document.querySelector(".navbar");
    const content = document.querySelector(".content");

    if (btn && sidebar && navbar && content) {
        btn.type = "button";
        btn.setAttribute("aria-label", "Abrir ou fechar menu principal");
        btn.setAttribute("aria-controls", "menu-principal");
        sidebar.id = "menu-principal";
        sidebar.setAttribute("aria-label", "Navegação principal");
        btn.setAttribute("aria-expanded", String(!sidebar.classList.contains("close")));
        btn.addEventListener("click", () => {
            sidebar.classList.toggle("close");
            navbar.classList.toggle("close");
            content.classList.toggle("close");
            btn.setAttribute("aria-expanded", String(!sidebar.classList.contains("close")));
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

/* Melhorias progressivas de acessibilidade */
document.addEventListener("DOMContentLoaded", () => {
    const main = document.querySelector("main, .content, .auth-right");
    if (main) {
        main.id ||= "conteudo-principal";
        if (!main.matches("main")) main.setAttribute("role", "main");
        if (!document.querySelector(".skip-link")) {
            const skip = document.createElement("a");
            skip.className = "skip-link";
            skip.href = `#${main.id}`;
            skip.textContent = "Pular para o conteúdo principal";
            document.body.prepend(skip);
        }
    }

    document.querySelectorAll(".menu-item.active").forEach((item) => item.setAttribute("aria-current", "page"));
    document.querySelectorAll('a[href="..."]').forEach((item) => {
        item.setAttribute("aria-disabled", "true");
        item.setAttribute("title", "Funcionalidade ainda não disponível");
        item.addEventListener("click", (event) => event.preventDefault());
    });
    document.querySelectorAll("img:not([alt])").forEach((img) => {
        img.alt = img.closest("label") ? "Pré-visualização da imagem selecionada" : "";
    });

    document.querySelectorAll("input[required], select[required], textarea[required]").forEach((field) => {
        field.setAttribute("aria-required", "true");
        if (!field.id) return;
        const label = document.querySelector(`label[for="${CSS.escape(field.id)}"]`);
        if (label && !label.querySelector(".required-indicator")) {
            const marker = document.createElement("span");
            marker.className = "required-indicator";
            marker.setAttribute("aria-hidden", "true");
            marker.textContent = " *";
            label.append(marker);
        }
    });

    document.querySelectorAll(".alert, .error").forEach((alert) => {
        alert.setAttribute("role", "alert");
        alert.setAttribute("aria-live", "assertive");
        alert.tabIndex = -1;
    });

    document.querySelectorAll("form").forEach((form) => form.addEventListener("submit", (event) => {
        if (!form.checkValidity()) {
            event.preventDefault();
            const invalid = form.querySelector(":invalid");
            invalid?.setAttribute("aria-invalid", "true");
            invalid?.focus();
            return;
        }
        const submit = form.querySelector('button[type="submit"], input[type="submit"]');
        if (submit) {
            submit.setAttribute("aria-disabled", "true");
            if (submit.tagName === "BUTTON") submit.textContent = "Enviando...";
        }
    }));

    document.querySelectorAll("table").forEach((table) => {
        if (table.querySelector("caption")) return;
        const caption = document.createElement("caption");
        caption.className = "visually-hidden-accessible";
        caption.textContent = "Lista de registros do ACE";
        table.prepend(caption);
    });

    document.querySelectorAll("[data-toggle-password]").forEach((button) => {
        button.addEventListener("click", () => {
            const field = document.getElementById(button.dataset.togglePassword);
            if (!field) return;
            const show = field.type === "password";
            field.type = show ? "text" : "password";
            button.setAttribute("aria-label", show ? "Ocultar senha" : "Mostrar senha");
            const icon = button.querySelector("i");
            if (icon) icon.className = show ? "bi bi-eye-slash" : "bi bi-eye";
        });
    });
});
