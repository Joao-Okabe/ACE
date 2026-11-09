import { alunoService } from "../../services/aluno.js";

const parametros = new URLSearchParams(window.location.search);
const idAluno = parametros.get("id");

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

function exibirMensagem(mensagem) {
    if (elementos.mensagem) {
        elementos.mensagem.textContent = mensagem;
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
        elementos.foto.src = aluno.img || "/assets/img/no-prof-pic.png";
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
        exibirMensagem("Não foi possível carregar os dados do aluno.");
    }
}

carregarAluno();
