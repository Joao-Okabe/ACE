import { authService } from "../../services/authService.js";
import { apiFetch } from "../../api/api.js";

const formulario = document.getElementById("formPerfil");
const campoNome = document.getElementById("nomeUsuario");
const campoEmail = document.getElementById("emailUsuario");
const campoImagem = document.getElementById("imagemPerfil");
const fotoAtual = document.getElementById("fotoAtual");
const mensagemPerfil = document.getElementById("mensagemPerfil");
const botaoSalvar = document.getElementById("botaoSalvarPerfil");
const imagemPadrao = "/assets/img/no-prof-pic.png";
const campoPapeis = document.getElementById("papeisUsuario");
const campoEscola = document.getElementById("escolaUsuario");
const campoStatus = document.getElementById("statusUsuario");

const papeis = {
    ADM: "Administrador",
    DIR: "Diretor",
    CRD: "Coordenador",
    PRF: "Professor",
    ARB: "Árbitro",
    ALU: "Aluno",
    VIS: "Visitante"
};

let idUsuario = null;
let papeisUsuario = [];

function exibirMensagem(elemento, texto, tipo) {
    if (!elemento) return;
    elemento.textContent = texto;
    elemento.dataset.tipo = tipo;
}

function formatarEscola(perfil) {
    const escolas = perfil.escolas ?? perfil.escola;

    if (Array.isArray(escolas)) {
        const nomes = escolas
            .map(escola => {
                if (typeof escola === "string") return escola;

                return escola?.nm_escola ?? escola?.nome ?? "";
            })
            .filter(Boolean);

        return nomes.length ? nomes.join(", ") : "Não vinculada";
    }

    if (typeof escolas === "string" && escolas.trim()) {
        return escolas;
    }

    if (escolas && typeof escolas === "object") {
        return escolas.nm_escola ?? escolas.nome ?? "Não vinculada";
    }

    return perfil.nm_escola ?? "Não vinculada";
}

function formatarStatus(perfil) {
    const ativo = perfil.ativo ?? perfil.is_ativo;

    if (ativo === true || ativo === 1 || ativo === "1") {
        return "Ativo";
    }

    if (ativo === false || ativo === 0 || ativo === "0") {
        return "Inativo";
    }

    return "Não informado";
}

function preencherPerfil(perfil) {
    campoNome.value = perfil.nm_usuario || perfil.nome || "";
    campoEmail.value = perfil.email || "";

    // Informações da conta (somente leitura)
    if (campoPapeis) {
        campoPapeis.textContent = formatarCargo(papeisUsuario);
    }

    if (campoEscola) {
        campoEscola.textContent = formatarEscola(perfil);
    }

    if (campoStatus) {
        campoStatus.textContent = formatarStatus(perfil);
    }

    // Imagem de perfil
    const imagem = perfil.foto_perfil || perfil.path_ft_usuario;

    fotoAtual.src =
        typeof imagem === "string" && imagem.trim()
            ? imagem
            : imagemPadrao;

    // Atualiza os dados utilizados pela barra superior
    window.usuarioLogado = {
        ...(window.usuarioLogado || {}),
        id: idUsuario,
        nome: campoNome.value,
        nm_usuario: campoNome.value,
        email: campoEmail.value,
        foto_perfil: imagem || null,
        papeis: papeisUsuario
    };

    document.querySelector("app-bar")?.atualizarUsuario?.();
}

function formatarCargo(valores) {
    if (!Array.isArray(valores) || valores.length === 0) {
        return "Não informado";
    }

    return valores
        .map(valor => {
            const codigo = String(valor);
            return papeis[codigo.toUpperCase()] || codigo;
        })
        .join(", ");
}

fotoAtual.addEventListener("error", () => {
    fotoAtual.src = imagemPadrao;
}, { once: true });

async function carregarPerfil() {
    botaoSalvar.disabled = true;
    exibirMensagem(mensagemPerfil, "Carregando seus dados...", "info");

    try {
        const sessao = await authService.me();
        idUsuario = Number(sessao?.id);
        papeisUsuario = Array.isArray(sessao?.papeis) ? sessao.papeis : [];

        if (!Number.isInteger(idUsuario) || idUsuario <= 0) {
            throw new Error("Não foi possível identificar o usuário autenticado.");
        }

        const perfil = await apiFetch(`/usuarios/${idUsuario}`);
        if (!perfil || typeof perfil !== "object") {
            throw new Error("A API não retornou os dados do perfil.");
        }

        preencherPerfil(perfil);
        exibirMensagem(mensagemPerfil, "", "");
        botaoSalvar.disabled = false;
    } catch (erro) {
        console.error("Erro ao carregar o perfil:", erro);
        exibirMensagem(
            mensagemPerfil,
            erro.message || "Não foi possível carregar os dados do perfil.",
            "erro"
        );
    }
}

function configurarPreferenciasVisuais() {
    const preferencias = window.ACEPreferenciasVisuais;
    if (!preferencias) return;

    const atuais = preferencias.obter();
    const escalas = preferencias.escalasDisponiveis;
    const opcoesTema = document.querySelectorAll('input[name="tema"]');
    const adaptacaoCores = document.getElementById("modoDaltonismo");
    const valorFonte = document.getElementById("valorFonte");

    function atualizarControles(valores) {
        opcoesTema.forEach(opcao => {
            opcao.checked = opcao.value === valores.tema;
        });
        if (adaptacaoCores) {
            adaptacaoCores.value = valores.adaptacaoCores;
        }
        if (valorFonte) {
            valorFonte.value = `${Math.round(valores.escalaFonte * 100)}%`;
        }
    }

    atualizarControles(atuais);

    opcoesTema.forEach(opcao => {
        opcao.addEventListener("change", () => {
            if (opcao.checked) {
                atualizarControles(preferencias.salvar({ tema: opcao.value }));
            }
        });
    });

    adaptacaoCores?.addEventListener("change", () => {
        atualizarControles(
            preferencias.salvar({ adaptacaoCores: adaptacaoCores.value })
        );
    });

    function alterarEscalaFonte(direcao) {
        const escalaAtual = preferencias.obter().escalaFonte;
        const indiceAtual = escalas.findIndex(
            escala => Math.abs(escala - escalaAtual) < 0.001
        );
        const indice = indiceAtual < 0 ? 1 : indiceAtual;
        const proximo = Math.min(
            escalas.length - 1,
            Math.max(0, indice + direcao)
        );
        atualizarControles(
            preferencias.salvar({ escalaFonte: escalas[proximo] })
        );
    }

    document.getElementById("diminuirFonte")?.addEventListener(
        "click",
        () => alterarEscalaFonte(-1)
    );
    document.getElementById("aumentarFonte")?.addEventListener(
        "click",
        () => alterarEscalaFonte(1)
    );
    document.getElementById("restaurarFonte")?.addEventListener(
        "click",
        () => atualizarControles(preferencias.salvar({
            tema: "claro",
            adaptacaoCores: "padrao",
            escalaFonte: 1
        }))
    );
}

campoImagem.addEventListener("change", () => {
    const arquivo = campoImagem.files?.[0];
    if (!arquivo) return;

    const tiposPermitidos = ["image/jpeg", "image/png", "image/webp"];
    if (arquivo.size > 2 * 1024 * 1024 || !tiposPermitidos.includes(arquivo.type)) {
        campoImagem.value = "";
        exibirMensagem(
            mensagemPerfil,
            "Escolha uma imagem JPG, PNG ou WEBP de até 2MB.",
            "erro"
        );
        return;
    }

    const leitor = new FileReader();
    leitor.addEventListener("load", () => {
        if (typeof leitor.result === "string") {
            fotoAtual.src = leitor.result;
        }
    });
    leitor.addEventListener("error", () => {
        exibirMensagem(mensagemPerfil, "Não foi possível pré-visualizar a imagem.", "erro");
    });
    leitor.readAsDataURL(arquivo);
});

formulario.addEventListener("submit", async evento => {
    evento.preventDefault();
    if (!formulario.reportValidity() || !idUsuario) return;

    const dados = new FormData();
    dados.set("nm_usuario", campoNome.value.trim());
    dados.set("email", campoEmail.value.trim());

    const imagem = campoImagem.files?.[0];
    if (imagem) dados.set("foto_perfil", imagem);

    botaoSalvar.disabled = true;
    exibirMensagem(mensagemPerfil, "Salvando alterações...", "info");

    try {
        const perfil = await apiFetch(`/usuarios/${idUsuario}`, {
            method: "POST",
            body: dados
        });
        preencherPerfil(perfil);
        campoImagem.value = "";
        exibirMensagem(mensagemPerfil, "Perfil atualizado com sucesso.", "sucesso");
    } catch (erro) {
        console.error("Erro ao atualizar o perfil:", erro);
        exibirMensagem(
            mensagemPerfil,
            erro.message || "Não foi possível atualizar o perfil.",
            "erro"
        );
    } finally {
        botaoSalvar.disabled = false;
    }
});

configurarPreferenciasVisuais();
carregarPerfil();