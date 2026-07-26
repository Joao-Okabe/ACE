const cepInput = document.getElementById("cep");

if (cepInput) {
    cepInput.addEventListener("blur", async function () {

    let cep = this.value.replace(/\D/g, "");

    if (cep.length !== 8) {
        return;
    }

    try {

        const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);

        const endereco = await response.json();

        if (endereco.erro) {
            alert("CEP não encontrado.");
            return;
        }

        document.getElementById("logradouro").value = endereco.logradouro;
        document.getElementById("bairro").value = endereco.bairro;
        document.getElementById("cidade").value = endereco.localidade;
        document.getElementById("uf").value = endereco.uf;

    } catch (error) {

        console.error(error);

        alert("Erro ao consultar o CEP.");

    }

    });
}
