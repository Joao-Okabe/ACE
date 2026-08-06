/*--Sidebar fechada com os ícones--*/
const btn = document.getElementById("menu-btn");
    const sidebar = document.querySelector(".sidebar");
    const navbar = document.querySelector(".navbar");
    const content = document.querySelector(".content");

    btn.addEventListener("click", () => {
        sidebar.classList.toggle("close");
        navbar.classList.toggle("close");
        content.classList.toggle("close");
});


/*--Adicionar preview para inputs de arquivo com label de preview--*/
document.querySelectorAll('input[type="file"]').forEach((fileInput) => {
    fileInput.addEventListener('change', function () {
        const arquivo = this.files && this.files[0];
        if (!arquivo) return;

        // Procura por label[for="inputId"] que funciona como preview
        const inputId = this.id;
        let previewLabel = null;

        if (inputId) {
            previewLabel = document.querySelector('label[for="' + inputId + '"]');
        }

        // fallback para elemento com id fotoPerfil
        if (!previewLabel) {
            previewLabel = document.getElementById('fotoPerfil') || document.getElementById('fotoPerfilUsuario') || document.getElementById('fotoLogoEscola') || document.getElementById('fotoLogoEscolaEditar');
        }

        if (previewLabel) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(arquivo);
            img.alt = 'Preview';
            img.style.maxWidth = '160px';
            img.style.objectFit = 'cover';
            previewLabel.innerHTML = '';
            previewLabel.appendChild(img);
        }
    });
});