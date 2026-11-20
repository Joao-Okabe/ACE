import {apiFetch} from "../api/api.js";

const BASE_URL = "/times";

function validarId(id, nome = "ID"){
    if( id === null ||
        id === undefined ||
        String(id).trim() === "" ||
        !Number.isInteger(Number(id)) ||
        Number(id) <= 0
    ) {
        throw new Error(`${nome} inválido.`);
    }
    return encodeURIComponent(String(id));
}

export const timeService = {
    listar(){
        return apiFetch(BASE_URL);
    },

    buscarPorId(id){
        return apiFetch(`${BASE_URL}/${validarId(id, "ID do time")}`);
    },

    criar(dados){
        return apiFetch(BASE_URL,{
            method: "POST",
            body: dados
        });
    },

    atualizar(id, dados){
        return apiFetch(`${BASE_URL}/${validarId(id, "ID do time")}`,{
            method: "PUT",
            body: dados
        });
    },

    excluir(id){
        return apiFetch(`${BASE_URL}/${validarId(id, "ID do time")}`,{
            method: "DELETE"
        });
    },

};

