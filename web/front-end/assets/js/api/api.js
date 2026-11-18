const API_URL = "/api"; 

//classe para organizar os erros da api
export class ApiError extends Error{
    constructor(message, {status = 0, code = "API_ERROR"} = {} ){
        super(message);
        this.name = "ApiError";
        this.status = status;
        this.code = code;
    }
}

function normalizarCamposImagem(valor) {
    if (Array.isArray(valor)) {
        return valor.map(normalizarCamposImagem);
    }

    if (!valor || typeof valor !== "object") {
        return valor;
    }

    const item = { ...valor };
    const imagem =
        item.imagem ??
        item.logo ??
        item.img_logo ??
        item.path_brasao ??
        item.path_escudo ??
        item.path_ft_usuario ??
        item.foto_perfil ??
        item.profile_photo ??
        null;

    if (typeof imagem === "string" && imagem.trim() !== "") {
        if (!item.imagem) item.imagem = imagem;
        if (!item.img_logo && !item.path_brasao) item.img_logo = imagem;
        if (!item.path_escudo) item.path_escudo = imagem;
        if (!item.path_ft_usuario && !item.foto_perfil) item.path_ft_usuario = imagem;
        if (!item.foto_perfil) item.foto_perfil = imagem;
        if (!item.logo) item.logo = imagem;
    }

    return item;
}

let csrfTokenPromise = null;

async function obterCsrfToken() {
    if (!csrfTokenPromise) {
        csrfTokenPromise = fetch(`${API_URL}/csrf`, { credentials: "same-origin" })
            .then(res => res.json())
            .then(dados => dados?.dados?.csrf_token || "")
            .catch(() => "");
    }
    return csrfTokenPromise;
}

//envia o pedido para a api
export async function apiFetch(endpoint, options = {}){
    const headers = new Headers(options.headers || {});
    headers.set("Accept", "application/json");

    const metodo = (options.method || "GET").toUpperCase();
    if (["POST", "PUT", "PATCH", "DELETE"].includes(metodo) && !headers.has("X-CSRF-Token")) {
        const token = await obterCsrfToken();
        if (token) {
            headers.set("X-CSRF-Token", token);
        }
    }

    let body = options.body; 
    if(body !== undefined && 
       body !== null &&
       !(body instanceof FormData)  
    ){
        headers.set("Content-Type", "application/json"); 
        body = JSON.stringify(body); 
    }

    let resposta;

    try{
        //envia o pedido e mantém os cookies
        resposta = await fetch (`${API_URL}${endpoint}`,{
        ...options,
        headers,
        body,
        credentials:"same-origin"
        });
        
    }catch{
        throw new ApiError("Não foi possível conectar ao servidor.",{
            code: "NETWORK_ERROR"
        });
    }
    
    if (resposta.status === 204) {
        if (!resposta.ok) {
            throw new ApiError("A requisição não foi concluída.", {
                status: resposta.status,
                code: "HTTP_ERROR"
            });
        }

        return null;
    }

    // Lê o corpo uma única vez para suportar JSON ou texto.
    const texto = await resposta.text();
    let dados = null;

    if (texto.trim()) {
        try {
            dados = JSON.parse(texto);
        } catch {
            dados = texto;
        }
    }

    // Trata erros HTTP retornados pelo back.
    if (!resposta.ok) {
        const mensagem =
            typeof dados === "string"
                ? dados
                : dados?.mensagem ||
                  dados?.message ||
                  dados?.error?.message ||
                  dados?.erro ||
                  `Erro na requisição (${resposta.status}).`;

        const codigo =
            dados?.error?.code ||
            dados?.code ||
            "HTTP_ERROR";

        throw new ApiError(mensagem, {
            status: resposta.status,
            code: codigo
        });
    }
    // Retorna os dados recebidos da api (se o backend envelopou em { sucesso, dados }, desempacota dados)
    if (dados && typeof dados === "object" && "dados" in dados) {
        return normalizarCamposImagem(dados.dados);
    }
    if (dados && typeof dados === "object" && dados.success === true && "data" in dados) {
        return normalizarCamposImagem(dados.data);
    }

    return normalizarCamposImagem(dados);
}