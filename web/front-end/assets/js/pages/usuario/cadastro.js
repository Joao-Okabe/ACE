import { authService } from "../../services/authService.js";
import { usuarioService } from "../../services/usuarioService.js";
import { configurarToggleSenha } from "../../components/toggleSenha.js";


// Elementos
const formCadastro = document.getElementById("form-cadastro");
if (!formCadastro) {
    console.error("Formulário de cadastro não encontrado.");
} else {
    const campoNome = document.getElementById("nome");
    const campoEmail = document.getElementById("email");
    const campoSenha = document.getElementById("senha");
    const btnCadastrar = formCadastro.querySelector('button[type="submit"]');
    const mensagemErro = document.getElementById("auth-error");
    const mensagemSucesso = document.getElementById("auth-success");

    // Mostrar ou ocultar senha
    configurarToggleSenha();

    // Exibir mensagem de erro
    function exibirErro(mensagem) {
        if (mensagemErro) {
            mensagemErro.textContent = mensagem;
            mensagemErro.hidden = false;
        }
        if (mensagemSucesso) {
            mensagemSucesso.hidden = true;
        }
    }

    // Exibir mensagem de sucesso
    function exibirSucesso(mensagem) {
        if (mensagemSucesso) {
            mensagemSucesso.textContent = mensagem;
            mensagemSucesso.hidden = false;
        }
        if (mensagemErro) {
            mensagemErro.hidden = true;
        }
    }

    // Limpar mensagens
    function limparMensagens() {
        if (mensagemErro) {
            mensagemErro.hidden = true;
            mensagemErro.textContent = "";
        }
        if (mensagemSucesso) {
            mensagemSucesso.hidden = true;
            mensagemSucesso.textContent = "";
        }
    }
    // Validar formulário
    function validarFormulario(nome, email, senha) {
        if (!nome || !email || !senha) {
            return "Preencha todos os campos.";
        }
        if (nome.length < 3) {
            return "Digite seu nome corretamente.";
        }
        const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!regexEmail.test(email)) {
            return "Digite um e-mail válido.";
        }
        if (senha.length < 8) {
            return "A senha deve possuir pelo menos 8 caracteres.";
        }
        return null;
    }
    // Enviar cadastro
    formCadastro.addEventListener("submit", async (event) => {
        event.preventDefault();
        limparMensagens();
        const nome = campoNome.value.trim();
        const email = campoEmail.value.trim();
        const senha = campoSenha.value;

        // Validação
        const erro = validarFormulario(nome, email, senha);
        if (erro) {
            exibirErro(erro);
            return;
        }

        // Evitar envio duplicado
        if (btnCadastrar) {
            btnCadastrar.disabled = true;
            btnCadastrar.textContent = "Cadastrando...";
        }
        let cadastroConcluido = false;
        try {
            const dados = { nome, email, senha };
            const resposta = await usuarioService.criar(dados);
            cadastroConcluido = true;
            exibirSucesso(resposta?.mensagem || "Cadastro realizado com sucesso!");
            formCadastro.reset();

            // Redirecionar para login
            window.setTimeout(() => {
                window.location.href = "/pages/usuario/login.html";
            }, 1500);
        } catch (erro) {
            console.error("Erro ao cadastrar usuário:", erro);
            exibirErro(erro.message || "Não foi possível realizar o cadastro. Tente novamente.");

        } finally {
            if (btnCadastrar && !cadastroConcluido) {
                btnCadastrar.disabled = false;
                btnCadastrar.textContent = "Cadastrar";
            }
        }
    });
}
