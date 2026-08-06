<?php

function renderView(string $view, array $data = []): void
{
    $usuario = $_SESSION['usuario'] ?? null;
    extract($data, EXTR_SKIP);
    require __DIR__ . '/../Views/' . $view . '.php';
}
