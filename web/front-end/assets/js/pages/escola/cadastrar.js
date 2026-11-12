import { escolaService } from "../../services/escolaService.js";
import { configurarConsultaCep } from "../../utils/cep.js";

// ELEMENTOS DO CADASTRO
const btnAbrir = document.querySelector("#abrirModalEscola");

const modalCadastro = document.querySelector(
    'app-modal[titulo="Cadastrar escola"]'
);

const formularioCadastro = document.querySelector("#form-escola");

// ABRIR MODAL
if (btnAbrir && modalCadastro) {
    btnAbrir.addEventListener("click", () => {
        modalCadastro.abrir();
    });
}

// PRÉ-VISUALIZAÇÃO DO BRASÃO
const campoImagem = formularioCadastro?.querySelector("#img_logo");
const imagemPreview = formularioCadastro?.querySelector(
    "#fotoPerfil img"
);

if (campoImagem && imagemPreview) {
    campoImagem.addEventListener("change", () => {
        const arquivo = campoImagem.files[0];

        if (!arquivo) return;

        if (!arquivo.type.startsWith("image/")) {
            alert("Selecione um arquivo de imagem válido.");
            campoImagem.value = "";
            return;
        }

        imagemPreview.src = URL.createObjectURL(arquivo);
    });
}

// CONSULTAR CEP PELO VIACEP
if (formularioCadastro) {
    configurarConsultaCep(formularioCadastro);
}

// CADASTRAR ESCOLA
if (formularioCadastro) {
    formularioCadastro.addEventListener("submit", async (evento) => {
        evento.preventDefault();

        if (!formularioCadastro.reportValidity()) return;

        const botaoSalvar = formularioCadastro.querySelector(
            'button[type="submit"]'
        );

        // Captura todos os campos e o arquivo de imagem
        const dados = new FormData(formularioCadastro);

        const imagem = formularioCadastro.querySelector("#img_logo").files[0];

        dados.delete("img_logo");

        if (imagem) {
            dados.append("img_logo_upload", imagem);
        }

        // Valida o nome
        const nome = dados.get("nome")?.trim();

        if (!nome) {
            alert("Informe o nome da escola.");
            return;
        }

        try {
            if (botaoSalvar) {
                botaoSalvar.disabled = true;
                botaoSalvar.textContent = "Cadastrando...";
            }

            console.log("Dados enviados para cadastro:", [...dados.entries()]);

            await escolaService.criar(dados);

            alert("Escola cadastrada com sucesso!");

            window.location.reload();

        } catch (erro) {
            console.error("Erro ao cadastrar escola:", erro);

            alert(
                erro.message ||
                "Não foi possível cadastrar a escola."
            );

        } finally {
            if (botaoSalvar) {
                botaoSalvar.disabled = false;
                botaoSalvar.textContent = "Cadastrar";
            }
        }
    });
}
