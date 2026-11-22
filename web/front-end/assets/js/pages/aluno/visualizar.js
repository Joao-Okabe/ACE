import { alunoService } from "../../services/alunoService.js";
import { exibirMensagem as exibirFeedback } from "../../utils/mensagens.js";
// E os import @Claralops?

const parametros = new URLSearchParams(window.location.search);
const idAluno = parametros.get("id");

console.log("ID do aluno recebido pela URL:", idAluno);
console.log("URL atual:", window.location.href);

const elementos = {
    foto: document.getElementById("fotoAluno"),
    nome: document.getElementById("nomeAluno"),
    status: document.getElementById("statusAluno"),
    ra: document.getElementById("raAluno"),
    nomeInfo: document.getElementById("nomeAlunoInfo"),
    raInfo: document.getElementById("raAlunoInfo"),
    dataNascimento: document.getElementById("dataNascimentoAluno"),
    sexo: document.getElementById("sexoAluno"),
    email: document.getElementById("emailAluno"),
    telefone: document.getElementById("telefoneAluno"),
    escola: document.getElementById("escolaAluno"),
    cep: document.getElementById("cepAluno"),
    mensagem: document.getElementById("mensagemAluno")
};

function preencherTexto(elemento, valor) {
    if (elemento) {
        elemento.textContent = valor || "—";
    }
}

function exibirMensagem(mensagem, tipo = "info") {
    if (elementos.mensagem) {
        exibirFeedback(elementos.mensagem, mensagem, tipo);
        elementos.mensagem.hidden = false;
    }
}

function preencherAluno(aluno) {
    if (!aluno) return;

    const nome = aluno.nome || "Aluno sem nome";

    preencherTexto(elementos.nome, nome);
    preencherTexto(elementos.nomeInfo, nome);
    preencherTexto(elementos.ra, aluno.ra);
    preencherTexto(elementos.raInfo, aluno.ra);
    preencherTexto(elementos.dataNascimento, aluno.data_nascimento);
    preencherTexto(elementos.sexo, aluno.sexo);
    preencherTexto(elementos.email, aluno.email);
    preencherTexto(elementos.telefone, aluno.telefone);
    preencherTexto(elementos.escola, aluno.escola);
    preencherTexto(elementos.cep, aluno.cep);

    if (elementos.foto) {
        elementos.foto.src = aluno.path_ft_usuario || aluno.img || "/assets/img/no-prof-pic.png";
        elementos.foto.alt = `Foto de ${nome}`;

        elementos.foto.onerror = () => {
            elementos.foto.src = "/assets/img/no-prof-pic.png";
        };
    }

    if (elementos.status) {
        const ativo = Boolean(aluno.ativo);

        preencherTexto(elementos.status, ativo ? "Ativo" : "Inativo");

        elementos.status.classList.toggle("ativo", ativo);
        elementos.status.classList.toggle("inativo", !ativo);
    }
}

async function carregarAluno() {
    if (!idAluno) {
        exibirMensagem("Nenhum aluno foi selecionado.");
        preencherTexto(elementos.nome, "Aluno não identificado");
        return;
    }

    try {
        exibirMensagem("Carregando informações do aluno...");

        const aluno = await alunoService.buscarPorId(idAluno);

        preencherAluno(aluno);

        if (elementos.mensagem) {
            elementos.mensagem.hidden = true;
        }

    } catch (erro) {
        console.error("Erro ao carregar aluno:", erro);
        exibirMensagem("Não foi possível carregar os dados do aluno.", "erro");
    }
}


// CONFIGURAR BOTÃO VOLTAR
function configurarBotaoVoltar() {
    const botao = document.getElementById("botaoVoltarAluno");
    if (!botao) {
        console.error("Botão voltar do aluno não encontrado no HTML.");
        return;
    }

    const destinoPadrao = "/pages/aluno/listar.html";
    const parametros = new URLSearchParams(window.location.search);
    const origem = parametros.get("voltar") || document.referrer;

    botao.href = destinoPadrao;

    if (!origem) return;

    try {
        const url = new URL(origem, window.location.origin);
        if (url.origin !== window.location.origin) return;

        const caminhosPermitidos = [
            "/pages/time/visualizar.html",
            "/pages/aluno/listar.html"
        ];

        if (caminhosPermitidos.includes(url.pathname)) {
            botao.href = url.pathname + url.search + url.hash;
        }

        console.log("Destino do botão voltar:", botao.href);
    } catch (erro) {
        console.error("Erro ao configurar botão voltar:", erro);
    }
}


configurarBotaoVoltar();
carregarAluno();
