import { escolaService } from "../../services/escolaService.js";
import { configurarConsultaCep, consultarCep } from "../../utils/cep.js";

// MODAL DE EDIÇÃO
const modalEditarEscola = document.querySelector(
    'app-modal[titulo="Editar escola"]'
);

const listaEscolas = document.querySelector("app-lista");

// Preenche os campos do formulário
function preencherFormulario(formulario, escola) {
    const campos = {
        "#editarIdEscola": escola.cd_escola,
        "#editarNomeEscola": escola.nm_escola ?? escola.nome,
        "#editarCategoriaEscola": escola.categoria_administrativa,
        "#editarTelefoneEscola": escola.telefone,
        "#editarCepEscola": escola.cep,
        "#editarLogradouroEscola": escola.logradouro,
        "#editarNumeroEscola": escola.numero,
        "#editarBairroEscola": escola.bairro,
        "#editarCidadeEscola": escola.cidade,
        "#editarUfEscola": escola.uf
    };

    Object.entries(campos).forEach(([seletor, valor]) => {
        const campo = formulario.querySelector(seletor);

        if (campo) {
            campo.value = valor ?? "";
        }
    });

    // Guarda o caminho atual do brasão
    const campoBrasao = formulario.querySelector("[name='path_brasao']");

    if (campoBrasao) {
        campoBrasao.value = escola.path_brasao ?? escola.img_logo ?? "";
    }

    // Exibe o brasão atual da escola
    const previewBrasao = formulario.querySelector("#editarPreviewBrasao");

    if (previewBrasao) {
        previewBrasao.src = escola.path_brasao || escola.img_logo || "/assets/img/brasao.jpg";
}
}

// Busca os dados da escola ao clicar em Editar
if (listaEscolas) {
    listaEscolas.addEventListener("click", async (evento) => {
        const botao = evento.target.closest("[data-editar-escola]");

        if (!botao) return;

        if (!modalEditarEscola) {
            console.error("Modal de edição não encontrado.");
            return;
        }

        const idEscola = botao.dataset.id;

        try {
            const escola = await escolaService.buscarPorId(idEscola);
            console.table(escola);

            console.log("Dados da escola selecionada:", escola);

            const formulario = modalEditarEscola.querySelector(
                "#form-editar-escola"
            );

            if (!formulario) {
                console.error("Formulário de edição não encontrado.");
                return;
            }

            preencherFormulario(formulario, escola);

            modalEditarEscola.abrir();

            // Consulta o CEP automaticamente ao abrir a edição
            await consultarCep(formulario, false);

        } catch (erro) {
            console.error("Erro ao buscar escola:", erro);
            alert(erro.message || "Não foi possível carregar os dados da escola.");
        }
    });
}

// SALVAR ALTERAÇÕES
const formularioEditarEscola = document.querySelector(
    "#form-editar-escola"
);

// CONSULTAR CEP PELO VIACEP
if (formularioEditarEscola) {
    configurarConsultaCep(formularioEditarEscola);
}

if (formularioEditarEscola) {
    formularioEditarEscola.addEventListener("submit", async (evento) => {
        evento.preventDefault();

        const idEscola = formularioEditarEscola
            .querySelector("#editarIdEscola")?.value;

        if (!idEscola) {
            console.error("ID da escola não encontrado.");
            alert("Não foi possível identificar a escola.");
            return;
        }

        const dados = {
            nome: formularioEditarEscola
                .querySelector("#editarNomeEscola").value.trim(),

            telefone: formularioEditarEscola
                .querySelector("#editarTelefoneEscola").value.trim(),

            cep: formularioEditarEscola
                .querySelector("#editarCepEscola").value.trim(),

            logradouro: formularioEditarEscola
                .querySelector("#editarLogradouroEscola").value.trim(),

            numero: formularioEditarEscola
                .querySelector("#editarNumeroEscola").value.trim(),

            bairro: formularioEditarEscola
                .querySelector("#editarBairroEscola").value.trim(),

            cidade: formularioEditarEscola
                .querySelector("#editarCidadeEscola").value.trim(),

            uf: formularioEditarEscola
                .querySelector("#editarUfEscola").value.trim(),

            categoria_administrativa: formularioEditarEscola
                .querySelector("#editarCategoriaEscola").value
        };

        const botaoSalvar = formularioEditarEscola.querySelector(
            'button[type="submit"]'
        );

        try {
            if (botaoSalvar) {
                botaoSalvar.disabled = true;
                botaoSalvar.textContent = "Salvando...";
            }

            console.log("Dados enviados para atualização:", dados);

            await escolaService.atualizar(idEscola, dados);

            alert("Escola atualizada com sucesso!");

            window.location.reload();

        } catch (erro) {
            console.error("Erro ao atualizar escola:", erro);

            alert(erro.message || "Não foi possível atualizar a escola.");

        } finally {
            if (botaoSalvar) {
                botaoSalvar.disabled = false;
                botaoSalvar.textContent = "Salvar alterações";
            }
        }
    });
}
