document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('chaveamento-container');

    if (!container || !window.chaveamento) {
        return;
    }

    const dados = window.chaveamento;

    if (!dados.length) {
        container.innerHTML = '<p>Nenhum chaveamento foi gerado.</p>';
        return;
    }

    const rodadas = agruparRodadas(dados);
    const quantidadeTimes = contarTimes(dados);

    let html = `<div class="bracket" data-times="${quantidadeTimes}">`;

    rodadas.forEach((rodada, indice) => {
        html += criarColunaRodada(rodada);

        if (indice < rodadas.length - 1) {
            const proximaRodada = rodadas[indice + 1];
            const classeConexao = criarClasseConexao(rodada, proximaRodada);

            html += `
                <div class="bracket-conexoes ${classeConexao}">
                    ${criarConexoes(proximaRodada.confrontos.length)}
                </div>
            `;
        }
    });

    html += '</div>';

    container.innerHTML = html;

    console.log('Chaveamento carregado:', dados);
    console.log('Rodadas:', rodadas);
    console.log('Times:', quantidadeTimes);
});


function agruparRodadas(dados) {
    const mapa = new Map();

    dados.forEach(item => {
        const numero = Number(item.nr_rodada);

        if (!mapa.has(numero)) {
            mapa.set(numero, {
                numero,
                nome: item.nm_rodada,
                confrontos: new Map()
            });
        }

        const rodada = mapa.get(numero);
        const idConfronto = Number(item.cd_confronto);

        if (!rodada.confrontos.has(idConfronto)) {
            rodada.confrontos.set(idConfronto, {
                id: idConfronto,
                numero: Number(item.nr_confronto),
                status: item.status,
                vencedor: item.cd_vencedor,
                participantes: []
            });
        }

        rodada.confrontos.get(idConfronto).participantes.push(item);
    });

    return Array.from(mapa.values())
        .sort((a, b) => a.numero - b.numero)
        .map(rodada => ({
            ...rodada,
            confrontos: Array.from(rodada.confrontos.values())
                .sort((a, b) => a.numero - b.numero)
        }));
}

function criarColunaRodada(rodada) {
    const classeRodada = classeDaRodada(rodada.nome);

    return `
        <div class="bracket-coluna">

            <div class="round-title">
                ${escapeHtml(rodada.nome)}
            </div>

            <div class="round ${classeRodada}">
                ${rodada.confrontos
                    .map(confronto => criarConfronto(confronto))
                    .join('')}
            </div>

        </div>
    `;
}

function criarConfronto(confronto) {
    const participantes = confronto.participantes;

    const participante1 = participantes.find(p => Number(p.posicao) === 1);
    const participante2 = participantes.find(p => Number(p.posicao) === 2);

    return `
        <div class="partida">
            ${criarParticipante(participante1)}
            ${criarParticipante(participante2)}
        </div>
    `;
}


function criarParticipante(participante) {
    if (!participante) {
        return `
            <div class="time">
                <span>A definir</span>
            </div>
        `;
    }

    let nome = 'A definir';
    let escudo = null;

    if (participante.tipo_origem === 'TIME') {
        nome = participante.nm_time || 'Time';
        escudo = participante.path_escudo;
    }

    if (participante.tipo_origem === 'CONFRONTO') {
        nome = `Vencedor C${participante.origem_confronto}`;
    }

    if (participante.tipo_origem === 'BYE') {
        nome = 'BYE';
    }

    return `
        <div class="time">
            ${escudo ? `
                <img
                    src="${escapeHtml(escudo)}"
                    alt="Escudo do ${escapeHtml(nome)}"
                >
            ` : ''}
            <span>${escapeHtml(nome)}</span>
        </div>
    `;
}


function criarConexoes(quantidade) {
    let conexoes = '';

    for (let i = 0; i < quantidade; i++) {
        conexoes += `
            <div class="conexao">
                <span class="entrada-1"></span>
                <span class="entrada-2"></span>
                <span class="vertical"></span>
                <span class="saida"></span>
            </div>
        `;
    }

    return conexoes;
}


function classeDaRodada(nome) {
    const nomeNormalizado = String(nome || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');

    if (nomeNormalizado.includes('16-avos')) {
        return 'dezesseisavos';
    }

    if (nomeNormalizado.includes('oitavas')) {
        return 'oitavas';
    }

    if (nomeNormalizado.includes('quartas')) {
        return 'quartas';
    }

    if (nomeNormalizado.includes('semifinal')) {
        return 'semifinal';
    }

    if (nomeNormalizado.includes('final')) {
        return 'final';
    }

    return 'rodada';
}

function criarClasseConexao(rodadaAtual, proximaRodada) {
    const atual = classeDaRodada(rodadaAtual.nome);
    const proxima = classeDaRodada(proximaRodada.nome);

    return `${atual}-para-${proxima}`;
}

function contarTimes(dados) {
    const times = new Set();

    dados.forEach(item => {
        if (item.tipo_origem === 'TIME' && item.cd_time) {
            times.add(Number(item.cd_time));
        }
    });

    return times.size;
}

function escapeHtml(valor) {
    return String(valor ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}