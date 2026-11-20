<?php

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';
Autoloader::register();

$expectedFiles = [
    'Usuario' => dirname(__DIR__) . '/app/Modules/Usuario/Models/UsuarioModel.php',
    'VinculoUsuarioEscola' => dirname(__DIR__) . '/app/Modules/VinculoUsuarioEscola/Models/VinculoUsuarioEscolaModel.php',
];

foreach ($expectedFiles as $className => $expectedFile) {
    $loadedFile = (new ReflectionClass($className))->getFileName();

    if (realpath($loadedFile) !== realpath($expectedFile)) {
        throw new RuntimeException(
            sprintf('%s was loaded from the wrong module.', $className)
        );
    }
}

if (!method_exists('VinculoUsuarioEscola', 'escolasAtivasPorPapeis')) {
    throw new RuntimeException('School-link model is missing active school lookup.');
}

echo "Autoloader module precedence checks passed.\n";
