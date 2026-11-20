import { authService } from "../../services/authService.js";
import { configurarToggleSenha } from "../../components/toggleSenha.js";


document.addEventListener("DOMContentLoaded", () => {
    const formulario = document.querySelector("#form-login");
    const mensagemErro = document.querySelector("#auth-error");
    const botaoEntrar = formulario?.querySelector('button[type="submit"]');

    // Mostrar ou ocultar a senha
    configurarToggleSenha();

    // Esconder erro quando o usuário começar a corrigir
    formulario?.addEventListener("input", () => {
        if (mensagemErro) {
            mensagemErro.hidden = true;
            mensagemErro.textContent = "";
        }
    });

    // Enviar login para a API
    formulario?.addEventListener("submit", async (evento) => {
        evento.preventDefault();

        const email = formulario.elements.email.value.trim();
        const senhaValor = formulario.elements.senha.value;

        if (!email || !senhaValor){
            mostrarErro("Preencha o e-mail e a senha.");
            return;
        }

        if (botaoEntrar){
            botaoEntrar.disabled = true;
            botaoEntrar.textContent = "Entrando...";
        }

        try{
            await authService.login(email, senhaValor);

            // A API deve manter a sessão e confirmar o login.
            window.location.href = "/dashboard";
        } catch (erro){
            mostrarErro(
                erro.message || "Não foi possível entrar. Verifique suas credenciais."
            );
        } finally{
            if (botaoEntrar){
                botaoEntrar.disabled = false;
                botaoEntrar.textContent = "Entrar";
            }
        }
    });

    function mostrarErro(mensagem){
        if (!mensagemErro){
            alert(mensagem);
            return;
        }

        mensagemErro.textContent = mensagem;
        mensagemErro.hidden = false;
    }
});
