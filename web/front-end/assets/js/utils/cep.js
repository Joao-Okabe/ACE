// CONSULTAR CEP PELO VIACEP
export async function consultarCep(formulario, avisar = true) {
    const campoCep = formulario.querySelector('[name="cep"]');

    if (!campoCep) return;

    const cep = campoCep.value.replace(/\D/g, "");

    if (!cep) return;

    if (cep.length !== 8) {
        if (avisar) alert("Digite um CEP válido com 8 números.");
        return;
    }

    try {
        campoCep.disabled = true;

        const resposta = await fetch(
            `https://viacep.com.br/ws/${cep}/json/`
        );

        if (!resposta.ok) {
            throw new Error("Não foi possível consultar o CEP.");
        }

        const endereco = await resposta.json();

        if (endereco.erro) {
            if (avisar) alert("CEP não encontrado.");
            return;
        }

        formulario.querySelector('[name="logradouro"]').value =
            endereco.logradouro || "";

        formulario.querySelector('[name="bairro"]').value =
            endereco.bairro || "";

        formulario.querySelector('[name="cidade"]').value =
            endereco.localidade || "";

        formulario.querySelector('[name="uf"]').value =
            endereco.uf || "";

    } catch (erro) {
        console.error("Erro ao consultar CEP:", erro);

        if (avisar) {
            alert("Não foi possível consultar o CEP. Preencha o endereço manualmente.");
        }

    } finally {
        campoCep.disabled = false;
    }
}

// CONFIGURAR CONSULTA AUTOMÁTICA AO SAIR DO CAMPO
export function configurarConsultaCep(formulario) {
    const campoCep = formulario.querySelector('[name="cep"]');

    if (!campoCep) return;

    campoCep.addEventListener("blur", () => {
        consultarCep(formulario);
    });
}