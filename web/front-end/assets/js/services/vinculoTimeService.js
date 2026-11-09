import {apiFetch} from "../api/api.js";

const BASE_URL = "/times";

function validarId(id, descricao = "ID"){
    if( id === null ||
        id === undefined ||
        String(id).trim() === "" ||
        !Number.isInteger(Number(id)) ||
        Number(id) <= 0
    ) {
        throw new Error(`${descricao} inválido.`);
    }
    return encodeURIComponent(String(id));
}

export const vinculoTimeService ={
    vincularResponsavel(idTime, idResponsavel){
        return apiFetch(`${BASE_URL}/${validarId(idTime, "ID do time")}/responsaveis`,{
                method: "POST",
                body: {cd_responsavel: idResponsavel}
            }
        );
    },

    vincularIntegrante(idTime, dados){
        return apiFetch(`${BASE_URL}/${validarId(idTime, "ID do time")}/integrantes`,{
                method: "POST",
                body: {
                    cd_usuario: dados.cd_usuario,
                    cd_funcao_integrante: dados.cd_funcao_integrante,
                    numero_camisa: dados.numero_camisa ?? null,
                    capitao: dados.capitao ?? false
                }
            }
        );
    },

    removerIntegrante(idTime, idUsuario){
        return apiFetch(`${BASE_URL}/${validarId(idTime, "ID do time")}/integrantes/${validarId(idUsuario, "ID do usuário")}`,{
                method: "DELETE"
            }
        );
    },

    tornarCapitao(idTime, idUsuario){
        return apiFetch(`${BASE_URL}/${validarId(idTime, "ID do time")}/integrantes/${validarId(idUsuario, "ID do usuário")}/capitao`,{
                method: "PUT"
            }
        );
    },

    removerCapitao(idTime, idUsuario){
        return apiFetch(`${BASE_URL}/${validarId(idTime, "ID do time")}/integrantes/${validarId(idUsuario, "ID do usuário")}/capitao`,{
                method: "DELETE"
            }
        );
    },

    vincularTecnico(idTime, idTecnico){
        return apiFetch(`${BASE_URL}/${validarId(idTime, "ID do time")}/tecnicos`,{
                method: "POST",
                body: {cd_usuario: idTecnico}
            }
        );
    },

    salvarEscalacao(idTime, idsTitulares){
        if (!Array.isArray(idsTitulares)){
            throw new Error("A escalação precisa ser uma lista de IDs.");
        }

        const titulares = {};
        for (const id of idsTitulares){
            titulares[validarId(id, "ID do titular")] = true;
        }
        return apiFetch(`${BASE_URL}/${validarId(idTime, "ID do time")}/escalacao`,{
                method: "PUT",
                body: {titulares}
            }
        );
    }
};
