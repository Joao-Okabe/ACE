const parametros = new URLSearchParams(window.location.search);
const idEscola = parametros.get("id");

const elementos = {
    brasao: document.getElementById("brasaoEscola"),
    nome: document.getElementById("nomeEscola"),
    status: document.getElementById("statusEscola"),
    categoria: document.getElementById("categoriaEscola"),
    nomeInfo: document.getElementById("nomeEscolaInfo"),
    categoriaInfo: document.getElementById("categoriaEscolaInfo"),
    email: document.getElementById("emailEscola"),
    telefone: document.getElementById("telefoneEscola"),
    cep: document.getElementById("cepEscola"),
    numero: document.getElementById("numeroEscola"),
    mensagem: document.getElementById("mensagemEscola"),
    botaoStatus: document.getElementById("botaoStatusEscola")
};

function preencherTexto(elemento, valor) {
    if (elemento) {
        elemento.textContent = valor || "—";
    }
}

function exibirMensagem(mensagem) {
    elementos.mensagem.textContent = mensagem;
    elementos.mensagem.hidden = false;
}

function preencherEscola(escola) {
    const nome = escola.nome || "Escola sem nome";
    const categoria = escola.categoria_administrativa || "—";
    const ativa = Boolean(escola.ativa);

    preencherTexto(elementos.nome, nome);
    preencherTexto(elementos.nomeInfo, nome);
    preencherTexto(elementos.categoria, categoria);
    preencherTexto(elementos.categoriaInfo, categoria);
    preencherTexto(elementos.email, escola.email);
    preencherTexto(elementos.telefone, escola.telefone);
    preencherTexto(elementos.cep, escola.cep);
    preencherTexto(elementos.numero, escola.numero);

    preencherTexto(elementos.status, ativa ? "Ativa" : "Inativa");
    elementos.status.classList.toggle("ativo", ativa);
    elementos.status.classList.toggle("inativo", !ativa);

    if (escola.img_logo) {
        elementos.brasao.src = escola.img_logo;
    }
}

async function carregarEscola() {
    if (!idEscola) {
        exibirMensagem("Nenhuma escola foi selecionada.");
        preencherTexto(elementos.nome, "Escola não identificada");
        return;
    }
}

carregarEscola();