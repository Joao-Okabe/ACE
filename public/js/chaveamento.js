document.addEventListener('DOMContentLoaded', () => {

    const container = document.getElementById('chaveamento-container');

    if (!container) {
        return;
    }

    container.innerHTML = `

        <div class="bracket" data-times="<?= count($times) ?>">

            <!-- 16AVOS-->
            <div class="bracket-coluna">

                <div class="round-title">
                    16-AVOS DE FINAL
                </div>

                <div class="round dezesseisavos">
                    ${criarPartida('Time 01', '/img/escudo-time-1.png', 'Time 02', '/img/escudo-time-2.png')}
                    ${criarPartida('Time 03', '/img/escudo-time-3.png', 'Time 04', '/img/escudo-time-4.png')}
                    ${criarPartida('Time 05', '/img/escudo-time-5.png', 'Time 06', '/img/escudo-time-6.png')}
                    ${criarPartida('Time 07', '/img/escudo-time-7.png', 'Time 08', '/img/escudo-time-8.png')}
                    ${criarPartida('Time 09', '/img/escudo-time-9.png', 'Time 10', '/img/escudo-time-10.png')}
                    ${criarPartida('Time 11', '/img/escudo-time-11.png', 'Time 12', '/img/escudo-time-12.png')}
                    ${criarPartida('Time 13', '/img/escudo-time-13.png', 'Time 14', '/img/escudo-time-14.png')}
                    ${criarPartida('Time 15', '/img/escudo-time-15.png', 'Time 16', '/img/escudo-time-16.png')}
                    ${criarPartida('Time 17', '/img/escudo-time-17.png', 'Time 18', '/img/escudo-time-18.png')}
                    ${criarPartida('Time 19', '/img/escudo-time-19.png', 'Time 20', '/img/escudo-time-20.png')}
                    ${criarPartida('Time 21', '/img/escudo-time-21.png', 'Time 22', '/img/escudo-time-22.png')}
                    ${criarPartida('Time 23', '/img/escudo-time-23.png', 'Time 24', '/img/escudo-time-24.png')}
                    ${criarPartida('Time 25', '/img/escudo-time-25.png', 'Time 26', '/img/escudo-time-26.png')}
                    ${criarPartida('Time 27', '/img/escudo-time-27.png', 'Time 28', '/img/escudo-time-28.png')}
                    ${criarPartida('Time 29', '/img/escudo-time-29.png', 'Time 30', '/img/escudo-time-30.png')}
                    ${criarPartida('Time 31', '/img/escudo-time-31.png', 'Time 32', '/img/escudo-time-32.png')}
                </div>

            </div>


            <!-- CONEXÃO 16AVOS > OITAVAS -->
            <div class="bracket-conexoes dezesseisavos-para-oitavas">
                ${criarConexoes(8)}
            </div>


            <!-- OITAVAS -->
            <div class="bracket-coluna">

                <div class="round-title">
                    OITAVAS DE FINAL
                </div>

                <div class="round oitavas">
                    ${criarPartida('Vencedor 16A1', '/img/escudo-time-1.png', 'Vencedor 16A2', '/img/escudo-time-2.png')}
                    ${criarPartida('Vencedor 16A3', '/img/escudo-time-3.png', 'Vencedor 16A4', '/img/escudo-time-4.png')}
                    ${criarPartida('Vencedor 16A5', '/img/escudo-time-5.png', 'Vencedor 16A6', '/img/escudo-time-6.png')}
                    ${criarPartida('Vencedor 16A7', '/img/escudo-time-7.png', 'Vencedor 16A8', '/img/escudo-time-8.png')}
                    ${criarPartida('Vencedor 16A9', '/img/escudo-time-9.png', 'Vencedor 16A10', '/img/escudo-time-10.png')}
                    ${criarPartida('Vencedor 16A11', '/img/escudo-time-11.png', 'Vencedor 16A12', '/img/escudo-time-12.png')}
                    ${criarPartida('Vencedor 16A13', '/img/escudo-time-13.png', 'Vencedor 16A14', '/img/escudo-time-14.png')}
                    ${criarPartida('Vencedor 16A15', '/img/escudo-time-15.png', 'Vencedor 16A16', '/img/escudo-time-16.png')}
                </div>

            </div>


            <!-- CONEXÃO OITAVAS > QUARTAS -->
            <div class="bracket-conexoes oitavas-para-quartas">
                ${criarConexoes(4)}
            </div>


            <!-- QUARTAS -->
            <div class="bracket-coluna">

                <div class="round-title">
                    QUARTAS DE FINAL
                </div>

                <div class="round quartas">
                    ${criarPartida('Vencedor O1', '/img/escudo-time-1.png', 'Vencedor O2', '/img/escudo-time-2.png')}
                    ${criarPartida('Vencedor O3', '/img/escudo-time-3.png', 'Vencedor O4', '/img/escudo-time-4.png')}
                    ${criarPartida('Vencedor O5', '/img/escudo-time-5.png', 'Vencedor O6', '/img/escudo-time-6.png')}
                    ${criarPartida('Vencedor O7', '/img/escudo-time-7.png', 'Vencedor O8', '/img/escudo-time-8.png')}
                </div>

            </div>


            <!-- CONEXÃO QUARTAS > SEMIFINAL -->
            <div class="bracket-conexoes quartas-para-semifinal">
                ${criarConexoes(2)}
            </div>


            <!-- SEMIFINAL -->
            <div class="bracket-coluna">

                <div class="round-title">
                    SEMIFINAL
                </div>

                <div class="round semifinal">
                    ${criarPartida('Vencedor Q1', '/img/escudo-time-1.png', 'Vencedor Q2', '/img/escudo-time-2.png')}
                    ${criarPartida('Vencedor Q3', '/img/escudo-time-3.png', 'Vencedor Q4', '/img/escudo-time-4.png')}
                </div>

            </div>


            <!-- CONEXÃO SEMIFINAL > FINAL-->
            <div class="bracket-conexoes semifinal-para-final">
                ${criarConexoes(1)}
            </div>


            <!-- FINAL= -->
            <div class="bracket-coluna">

                <div class="round-title">
                    FINAL
                </div>

                <div class="round final">
                    ${criarPartida('Vencedor S1', '/img/escudo-time-1.png', 'Vencedor S2', '/img/escudo-time-2.png')}
                </div>

            </div>

        </div>
    `;
});


/* Cria uma partida */
function criarPartida(time1, escudo1, time2, escudo2) {
    return `
        <div class="partida">

            <div class="time">
                <img src="${escudo1}" alt="Escudo do ${time1}">
                <span>${time1}</span>
            </div>

            <div class="time">
                <img src="${escudo2}" alt="Escudo do ${time2}">
                <span>${time2}</span>
            </div>

        </div>
    `;
}


/* Cria conexão */
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

/* Tipo de chaveamento */
document.addEventListener('DOMContentLoaded', () => {
    const bracket = document.querySelector('.bracket');
    if (!bracket) {
        return;
    }
    const quantidadeTimes = Number(
        bracket.dataset.times || 0
    );
    console.log(
        `Chaveamento carregado: ${quantidadeTimes} times`
    );

});