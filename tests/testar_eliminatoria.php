<?php

require_once __DIR__ . '/../config/env.php';

loadEnv(__DIR__ . '/../.env');

require_once __DIR__ . '/../app/Core/Autoloader.php';

Autoloader::register();

try {
    $service = new EliminatoriaSimplesService();

    $service->gerar(
        9, // cd_competicao
        1  // cd_etapa_competicao
    );

    echo "Chaveamento gerado!\n";
} catch (Throwable $e) {
    echo "ERRO AO GERAR CHAVEAMENTO\n";
    echo "Mensagem: {$e->getMessage()}\n";
    echo "Arquivo: {$e->getFile()}\n";
    echo "Linha: {$e->getLine()}\n";
}