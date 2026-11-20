import { authService } from "../../services/authService.js";
import { importacaoCsvService } from "../../services/importacaoCsvService.js";

const tipo = new URLSearchParams(window.location.search).get("tipo");
const rotasLista = {
    alunos: "/pages/aluno/listar.html",
    escolas: "/pages/escola/listar.html",
};
const configuracoes = {
    alunos: {
        titulo: "Importar alunos por CSV",
        descricao: "Colunas obrigatórias: nome, email, RA e data_nascimento. Data aceita AAAA-MM-DD ou DD/MM/AAAA. Telefone, CEP e sexo são opcionais.",
        campos: [
            { chave: "nome", titulo: "Nome", tipo: "text" },
            { chave: "email", titulo: "E-mail", tipo: "email" },
            { chave: "ra", titulo: "RA", tipo: "text" },
            { chave: "data_nascimento", titulo: "Nascimento", tipo: "date" },
            { chave: "sexo", titulo: "Sexo", tipo: "select", opcoes: ["", "M", "F", "O"] },
            { chave: "telefone", titulo: "Telefone", tipo: "text" },
            { chave: "cep", titulo: "CEP", tipo: "text" },
        ],
    },
    escolas: {
        titulo: "Importar escolas por CSV",
        descricao: "Colunas obrigatórias: nome e categoria_administrativa. Categorias: Escola Municipal, Escola Estadual ou Privada. Telefone, CEP, número e email são opcionais.",
        campos: [
            { chave: "nome", titulo: "Nome", tipo: "text" },
            { chave: "categoria_administrativa", titulo: "Categoria", tipo: "select", opcoes: ["", "Escola Municipal", "Escola Estadual", "Privada"] },
            { chave: "email", titulo: "E-mail", tipo: "email" },
            { chave: "telefone", titulo: "Telefone", tipo: "text" },
            { chave: "cep", titulo: "CEP", tipo: "text" },
            { chave: "numero", titulo: "Número", tipo: "text" },
        ],
    },
};

const elementos = {
    titulo: document.getElementById("tituloImportacao"),
    descricao: document.getElementById("descricaoImportacao"),
    voltar: document.getElementById("voltarImportacao"),
    formulario: document.getElementById("formImportacaoCsv"),
    campoEscola: document.getElementById("campoEscolaImportacao"),
    escola: document.getElementById("escolaImportacao"),
    arquivo: document.getElementById("arquivoImportacao"),
    analisar: document.getElementById("analisarImportacao"),
    mensagem: document.getElementById("mensagemImportacao"),
    previa: document.getElementById("previaImportacao"),
    resumo: document.getElementById("resumoImportacao"),
    cabecalho: document.getElementById("cabecalhoImportacao"),
    linhas: document.getElementById("linhasImportacao"),
    confirmar: document.getElementById("confirmarImportacao"),
    resultado: document.getElementById("resultadoImportacao"),
    mensagemResultado: document.getElementById("mensagemResultado"),
    credenciais: document.getElementById("credenciaisImportacao"),
};

let previaAtual = null;

function mostrarMensagem(texto, erro = false) {
    elementos.mensagem.textContent = texto;
    elementos.mensagem.classList.toggle("erro", erro);
    elementos.mensagem.hidden = !texto;
}

function adicionarCelula(linha, conteudo) {
    const celula = document.createElement("td");
    celula.append(conteudo);
    linha.append(celula);
    return celula;
}

function criarEditor(campo, valor, numeroLinha) {
    const editor = campo.tipo === "select"
        ? document.createElement("select")
        : document.createElement("input");

    editor.dataset.campo = campo.chave;
    editor.setAttribute("aria-label", `${campo.titulo}, registro ${numeroLinha}`);
    editor.className = "form-input importacao-editor";

    if (campo.tipo === "select") {
        campo.opcoes.forEach((opcao) => {
            const option = document.createElement("option");
            option.value = opcao;
            option.textContent = opcao || "Selecione";
            editor.append(option);
        });
    } else {
        editor.type = campo.tipo;
    }

    editor.value = valor ?? "";
    return editor;
}

function renderizarPrevia(previa) {
    previaAtual = previa;
    const configuracao = configuracoes[tipo];
    const linhaCabecalho = document.createElement("tr");
    ["Registro", ...configuracao.campos.map((campo) => campo.titulo), "Erros", "Ação"]
        .forEach((titulo) => {
            const celula = document.createElement("th");
            celula.scope = "col";
            celula.textContent = titulo;
            linhaCabecalho.append(celula);
        });
    elementos.cabecalho.replaceChildren(linhaCabecalho);

    const fragmento = document.createDocumentFragment();
    previa.linhas.forEach((registro) => {
        const linha = document.createElement("tr");
        linha.dataset.linha = String(registro.linha);
        linha.classList.toggle("com-erros", registro.erros.length > 0);

        const numero = document.createElement("th");
        numero.scope = "row";
        numero.textContent = String(registro.linha);
        linha.append(numero);

        configuracao.campos.forEach((campo) => {
            adicionarCelula(
                linha,
                criarEditor(campo, registro.dados[campo.chave], registro.linha)
            );
        });

        const celulaErros = document.createElement("td");
        if (registro.erros.length === 0) {
            celulaErros.textContent = "Sem erros";
        } else {
            const listaErros = document.createElement("ul");
            listaErros.className = "importacao-erros";
            registro.erros.forEach((erro) => {
                const item = document.createElement("li");
                item.textContent = erro.campo
                    ? `${erro.campo}: ${erro.mensagem}`
                    : erro.mensagem;
                listaErros.append(item);
            });
            celulaErros.append(listaErros);
        }
        linha.append(celulaErros);

        const botaoSalvar = document.createElement("button");
        botaoSalvar.type = "button";
        botaoSalvar.className = "custom-btn custom-btn-secondary importacao-salvar-linha";
        botaoSalvar.dataset.salvarLinha = "";
        botaoSalvar.textContent = "Salvar";
        botaoSalvar.setAttribute("aria-label", `Salvar alterações do registro ${registro.linha}`);
        adicionarCelula(linha, botaoSalvar);
        fragmento.append(linha);
    });

    elementos.linhas.replaceChildren(fragmento);
    elementos.resumo.textContent =
        `${previa.total_linhas} registros: ${previa.linhas_validas} válidos e `
        + `${previa.linhas_com_erro} com erros. Corrija e salve as linhas antes de confirmar.`;
    elementos.confirmar.disabled = previa.linhas_com_erro > 0;
    elementos.previa.hidden = false;
}

function mostrarCredenciais(credenciais) {
    if (!Array.isArray(credenciais) || credenciais.length === 0) {
        elementos.credenciais.replaceChildren();
        return;
    }

    const aviso = document.createElement("p");
    aviso.textContent = "As senhas aparecem apenas nesta resposta. Copie e distribua cada uma por um canal seguro.";
    const tabela = document.createElement("table");
    tabela.className = "importacao-tabela importacao-tabela-credenciais";

    const cabecalho = document.createElement("tr");
    ["Aluno", "E-mail", "Senha inicial"].forEach((rotulo) => {
        const celula = document.createElement("th");
        celula.scope = "col";
        celula.textContent = rotulo;
        cabecalho.append(celula);
    });
    const cabecalhoTabela = document.createElement("thead");
    cabecalhoTabela.append(cabecalho);

    const corpoTabela = document.createElement("tbody");
    credenciais.forEach((credencial) => {
        const linha = document.createElement("tr");
        [credencial.nome, credencial.email, credencial.senha_inicial].forEach((valor) => {
            const celula = document.createElement("td");
            celula.textContent = valor ?? "";
            linha.append(celula);
        });
        corpoTabela.append(linha);
    });

    tabela.append(cabecalhoTabela, corpoTabela);
    elementos.credenciais.replaceChildren(aviso, tabela);
}

async function iniciar() {
    const configuracao = configuracoes[tipo];
    if (!configuracao) {
        window.location.replace(rotasLista.alunos);
        return;
    }

    elementos.titulo.textContent = configuracao.titulo;
    elementos.descricao.textContent = configuracao.descricao;
    elementos.voltar.href = rotasLista[tipo];
    document.title = configuracao.titulo;
    elementos.campoEscola.hidden = tipo !== "alunos";
    elementos.escola.disabled = tipo !== "alunos";

    let usuario;
    try {
        usuario = await authService.me();
    } catch {
        window.location.replace("/pages/usuario/login.html");
        return;
    }

    const papeis = Array.isArray(usuario?.papeis) ? usuario.papeis : [];
    const autorizado = tipo === "escolas"
        ? papeis.includes("ADM")
        : papeis.some((papel) => ["ADM", "DIR"].includes(papel));
    if (!autorizado) {
        window.location.replace(rotasLista[tipo]);
        return;
    }

    if (tipo === "alunos") {
        try {
            const escolas = await importacaoCsvService.escolasDisponiveis();
            elementos.escola.replaceChildren();
            escolas.forEach((escola) => {
                const opcao = document.createElement("option");
                opcao.value = String(escola.cd_escola);
                opcao.textContent = escola.nome;
                elementos.escola.append(opcao);
            });
            if (escolas.length === 0) {
                mostrarMensagem("Não há escolas ativas disponíveis para importação.", true);
                elementos.analisar.disabled = true;
            }
        } catch (erro) {
            mostrarMensagem(erro.message || "Não foi possível carregar as escolas.", true);
            elementos.analisar.disabled = true;
            return;
        }
    }
}

elementos.formulario.addEventListener("submit", async (evento) => {
    evento.preventDefault();
    previaAtual = null;
    elementos.previa.hidden = true;
    elementos.confirmar.disabled = true;
    elementos.resultado.hidden = true;

    const arquivo = elementos.arquivo.files?.[0];
    if (!arquivo) {
        mostrarMensagem("Selecione um arquivo CSV.", true);
        return;
    }
    if (arquivo.size > 5 * 1024 * 1024) {
        mostrarMensagem("O tamanho máximo do CSV é 5 MB.", true);
        return;
    }

    elementos.analisar.disabled = true;
    elementos.analisar.textContent = "Validando...";
    mostrarMensagem("");
    try {
        const previa = await importacaoCsvService.analisar(
            tipo,
            arquivo,
            tipo === "alunos" ? elementos.escola.value : null
        );
        renderizarPrevia(previa);
    } catch (erro) {
        mostrarMensagem(erro.message || "Não foi possível analisar o CSV.", true);
    } finally {
        elementos.analisar.disabled = false;
        elementos.analisar.textContent = "Carregar prévia";
    }
});

elementos.linhas.addEventListener("click", async (evento) => {
    const botao = evento.target.closest("[data-salvar-linha]");
    if (!botao || !previaAtual) return;

    const linha = botao.closest("tr");
    const numeroLinha = Number(linha.dataset.linha);
    const dados = Object.fromEntries(
        [...linha.querySelectorAll("[data-campo]")].map((editor) => [
            editor.dataset.campo,
            editor.value,
        ])
    );

    botao.disabled = true;
    botao.textContent = "Salvando...";
    mostrarMensagem("");
    try {
        const previa = await importacaoCsvService.editarLinha(
            previaAtual.id,
            numeroLinha,
            dados
        );
        renderizarPrevia(previa);
        mostrarMensagem(`Registro ${numeroLinha} atualizado.`);
    } catch (erro) {
        mostrarMensagem(erro.message || "Não foi possível salvar as alterações.", true);
        botao.disabled = false;
        botao.textContent = "Salvar";
    }
});

elementos.confirmar.addEventListener("click", async () => {
    if (!previaAtual || previaAtual.linhas_com_erro > 0) return;
    if (!window.confirm("Confirma o cadastro de todos os registros desta prévia?")) return;

    elementos.confirmar.disabled = true;
    elementos.confirmar.textContent = "Confirmando...";
    mostrarMensagem("");
    try {
        const resultado = await importacaoCsvService.confirmar(previaAtual.id);
        elementos.previa.hidden = true;
        elementos.resultado.hidden = false;
        elementos.mensagemResultado.textContent =
            `${resultado.registros_importados} registros foram importados com sucesso.`;
        mostrarCredenciais(resultado.credenciais);
    } catch (erro) {
        mostrarMensagem(erro.message || "Não foi possível confirmar a importação.", true);
        elementos.confirmar.disabled = false;
    } finally {
        elementos.confirmar.textContent = "Confirmar importação";
    }
});

iniciar();
