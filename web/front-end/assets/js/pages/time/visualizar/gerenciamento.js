import { timeService } from "../../../services/timeService.js";
import { estadoTime, obterIdTime } from "./utilitarios.js";
import { configurarImagem } from "../../../utils/imagens.js";

// MENSAGENS
function mostrarMensagem(elemento, texto, tipo) {
    if (!elemento) return;

    elemento.textContent = texto;
    elemento.className = `alert alert-${tipo}`;
}

// ATUALIZAR IMAGENS
function atualizarImagem(imagem, caminho) {
    if (!imagem) return;

    configurarImagem(imagem, caminho, "/assets/img/escudo.jpg");
}

// CONFIGURAR GRUPOS EXPANSÍVEIS
function configurarAcordeoes() {
    const botoes = document.querySelectorAll(
        "#gerenciamento .competicao-gerenciar-item"
    );

    botoes.forEach(botao => {
        botao.addEventListener("click", () => {
            const conteudo = botao.nextElementSibling;

            if (!conteudo) return;

            const expandido = botao.getAttribute("aria-expanded") === "true";

            botao.setAttribute("aria-expanded", String(!expandido));
            conteudo.hidden = expandido;
        });
    });
}

// CONFIGURAR PRÉVIA DO ESCUDO
function configurarPreviewEscudo() {
    const campo = document.getElementById("editarEscudoTime");
    const preview = document.getElementById("editarPreviewEscudo");
    if (!campo || !preview) return;

    let urlAnterior = null;

    campo.addEventListener("change", () => {
        const arquivo = campo.files[0];

        if (!arquivo) return;

        const tiposPermitidos = [
            "image/png",
            "image/jpeg",
            "image/webp"
        ];

        if (!tiposPermitidos.includes(arquivo.type)) {
            mostrarMensagem(
                document.getElementById("mensagemInformacoesTime"),
                "Formato de imagem não permitido.",
                "danger"
            );
            campo.value = "";
            return;
        }

        if (arquivo.size > 2 * 1024 * 1024) {
            mostrarMensagem(
                document.getElementById("mensagemInformacoesTime"),
                "A imagem deve ter no máximo 2 MB.",
                "danger"
            );
            campo.value = "";
            return;
        }

        if (urlAnterior) {
            URL.revokeObjectURL(urlAnterior);
        }

        urlAnterior = URL.createObjectURL(arquivo);
        preview.src = urlAnterior;
    });
}

// EDITAR INFORMAÇÕES DO TIME
function configurarFormularioInformacoes() {
    const formulario = document.getElementById(
        "formEditarInformacoesTime"
    );

    if (!formulario) return;

    formulario.addEventListener("submit", async evento => {
        evento.preventDefault();

        const idTime = obterIdTime();
        const nome = document.getElementById("editarNomeTime");
        const escudo = document.getElementById("editarEscudoTime");
        const mensagem = document.getElementById(
            "mensagemInformacoesTime"
        );
        const botao = document.getElementById(
            "botaoSalvarInformacoesTime"
        );

        if (!nome.value.trim()) {
            mostrarMensagem(mensagem, "Informe o nome do time.", "danger");
            return;
        }

        const dados = new FormData();

        dados.append("nm_time", nome.value.trim());

        if (escudo.files[0]) {
            dados.append("path_escudo", escudo.files[0]);
        }

        try {
            botao.disabled = true;

            await timeService.atualizar(idTime, dados);

            const timeAtualizado = await timeService.buscarPorId(idTime);

            nome.value = timeAtualizado.nm_time || "";

            atualizarImagem(
                document.getElementById("editarPreviewEscudo"),
                timeAtualizado.path_escudo || timeAtualizado.imagem
            );

            atualizarImagem(
                document.getElementById("brasaoEscola"),
                timeAtualizado.path_escudo || timeAtualizado.imagem
            );

            const nomeCabecalho = document.getElementById("nomeTime");

            if (nomeCabecalho) {
                nomeCabecalho.textContent = timeAtualizado.nm_time;
            }

            escudo.value = "";

            mostrarMensagem(
                mensagem,
                "Informações do time atualizadas com sucesso!",
                "success"
            );

        } catch (erro) {
            console.error("Erro ao atualizar time:", erro);

            mostrarMensagem(
                mensagem,
                erro.message || "Não foi possível atualizar o time.",
                "danger"
            );

        } finally {
            botao.disabled = false;
        }
    });
}

// REMOVER TIME
function configurarRemocaoTime() {
    const botao = document.getElementById("botaoRemoverTime");
    const mensagem = document.getElementById("mensagemRemoverTime");

    if (!botao) return;

    botao.addEventListener("click", async () => {
        const idTime = obterIdTime();

        const confirmado = window.confirm(
            "Tem certeza de que deseja remover este time? Esta ação não poderá ser desfeita."
        );

        if (!confirmado) return;

        try {
            botao.disabled = true;

            await timeService.excluir(idTime);

            mostrarMensagem(
                mensagem,
                "Time removido com sucesso!",
                "success"
            );

            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = "/pages/time/listar.html";
            }

        } catch (erro) {
            console.error("Erro ao remover time:", erro);

            mostrarMensagem(
                mensagem,
                erro.message || "Não foi possível remover o time.",
                "danger"
            );

            botao.disabled = false;
        }
    });
}

// INICIALIZAR GERENCIAMENTO
export function configurarGerenciamentoTime() {
    if (!estadoTime.podeGerenciar) return;
    configurarAcordeoes();
    configurarPreviewEscudo();
    configurarFormularioInformacoes();
    configurarRemocaoTime();
}
