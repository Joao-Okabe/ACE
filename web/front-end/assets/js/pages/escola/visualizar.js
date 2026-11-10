import { escolaService } from "../../services/escolaService.js";

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
    if (elementos.mensagem) {
        elementos.mensagem.textContent = mensagem;
        elementos.mensagem.hidden = false;
    }
}

function preencherEscola(escola) {
    const nome = escola.nm_escola || escola.nome || "Escola sem nome";
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

    elementos.status?.classList.toggle("ativo", ativa);
    elementos.status?.classList.toggle("inativo", !ativa);

    const brasao = escola.path_brasao || escola.img_logo;

    if (elementos.brasao) {
        elementos.brasao.src = brasao || "/assets/img/brasao.jpg";
    }
}

async function carregarEscola() {
    if (!idEscola) {
        exibirMensagem("Nenhuma escola foi selecionada.");
        preencherTexto(elementos.nome, "Escola não identificada");
        return;
    }

    try {
        const escola = await escolaService.buscarPorId(idEscola);

        console.log("Escola recebida:", escola);

        if (!escola) {
            exibirMensagem("Escola não encontrada.");
            return;
        }

        preencherEscola(escola);

    } catch (erro) {
        console.error("Erro ao carregar escola:", erro);

        exibirMensagem(
            erro.message || "Não foi possível carregar os dados da escola."
        );
    }
}

carregarEscola();