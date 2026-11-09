import {apiFetch} from "../api/api.js";

const BASE_URL = "/usuarios";

function validarId(id){
    if (
        id === null ||
        id === undefined ||
        String(id).trim() === "" ||
        !Number.isInteger(Number(id)) ||
        Number(id) <= 0
    ) {
        throw new Error("ID do usuário inválido.");
    }
    return encodeURIComponent(String(id));
}

export const usuarioService = {
    listar(){
        return apiFetch(BASE_URL);
    },

    buscarPorId(id){
        return apiFetch(`${BASE_URL}/${validarId(id)}`);
    },

    criar(dados){
        return apiFetch(BASE_URL,{
            method: "POST",
            body: dados
        });
    },

    atualizar(id, dados){
        return apiFetch(`${BASE_URL}/${validarId(id)}`,{
            method: "PUT",
            body: dados
        });
    },

    excluir(id){
        return apiFetch(`${BASE_URL}/${validarId(id)}`,{
            method: "DELETE"
        });
    }
};
