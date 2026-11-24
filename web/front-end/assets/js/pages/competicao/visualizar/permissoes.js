import {
    definirVisibilidade,
    definirVisibilidadeSeletor
} from "../../../utils/permissoesVisuais.js";

// PERMISSÕES VISUAIS DA COMPETIÇÃO
export function configurarPermissoes() {
    const visitante = window.usuarioVisitante === true;
    const podeGerenciar =
        !visitante && window.podeGerenciarCompeticao === true;
    const podeGerarChaveamento =
        !visitante && window.podeGerarChaveamento === true;
    const podeExcluir =
        !visitante && window.podeExcluirCompeticao === true;

    [
        ["#aba-gerir", podeGerenciar],
        ["#formInscreverTime", podeGerenciar],
        ["#removerTimes", podeGerenciar],
        ["#abrirModalPartida", podeGerenciar],
        ["#formAdicionarArbitro", podeGerenciar],
        ["#botaoAbrirModalCadastrarEtapa", podeGerenciar],
        ["#btnGerarChaveamento", podeGerarChaveamento]
    ].forEach(([seletor, visivel]) => {
        definirVisibilidadeSeletor(seletor, visivel);
    });

    const botaoRemoverCompeticao =
        document.getElementById("botaoRemoverCompeticao");
    const grupoRemoverCompeticao =
        botaoRemoverCompeticao?.closest(".competicao-gerenciar-grupo");

    definirVisibilidade(grupoRemoverCompeticao || botaoRemoverCompeticao, podeExcluir);

    // Controles criados dinamicamente depois do carregamento dos dados.
    definirVisibilidadeSeletor("#listaEtapas .etapa-card-acoes", podeGerenciar);
    definirVisibilidadeSeletor("#listaArbitros .card-entidade-acoes", podeGerenciar);
    definirVisibilidadeSeletor("#listaPartidas .partida-card-acoes", podeGerenciar);
    definirVisibilidadeSeletor("#listaTimesCompeticao .time-remocao-checkbox", podeGerenciar);

    if (visitante) {
        [
            "#formInscreverTime",
            "#formCadastrarPeriodo",
            "#formEditarInformacoesCompeticao",
            "#formEditarLocalDatas",
            "#formAdicionarArbitro",
            "#modalCadastrarPartida",
            "#modalEditarPartida",
            "#modalResultadoPartida",
            "#modalPontuacaoPartida",
            "#modalPenalidadePartida",
            "#modalArbitragemPartida",
            "#modalCadastrarEtapa",
            "#modalEditarEtapa"
        ].forEach(seletor => {
            document.querySelector(seletor)?.remove();
        });
    }
}
